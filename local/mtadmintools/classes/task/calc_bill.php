<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A scheduled task for mtadmintools billing calc.
 *
 * @package    local_mtadmintools
 * @copyright  2017 Manu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_mtadmintools\task;

defined('MOODLE_INTERNAL') || die();

/**
 * A scheduled task class for mtadmintools billing calc.
 *
 * @copyright  2017 Manu
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class calc_bill extends \core\task\scheduled_task {

    public static $relativetmpfolder = 'mtadmintools';
    public static $bytesbygb = 1073741824;
    public static $billhistmonths = 5;

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('calcbilltask', 'local_mtadmintools');
    }

    /**
     * Run billing calc.
     */
    public function execute() {
        global $CFG, $DB;

        $confs = get_config('local_mtadmintools');
        $year = date("Y");
        $month = date("m");
        $day = date("d");

        $usersjson = json_decode(self::get_active_users(false, $year, $month));
        $diskjson = json_decode(self::csv_disk_consumption_data('general', false));

        $result = $DB->get_record('local_mtadmintools_bill_hist',
            array('year' => $year, 'month' => $month), '*');
        if ($result) {
            $result->activeusers = $usersjson->totals->actives;
            $result->pricebyuser = $confs->cost_by_user;
            $result->diskgb = $diskjson->totals->bytes / self::$bytesbygb;
            $result->pricebygb = $confs->disk_cost;
            $result->bill = self::apply_formula(
                $result->diskgb, $usersjson->totals->actives, $confs->disk_cost, $confs->cost_by_user);
            $result->currency = $confs->currency;
            $DB->update_record('local_mtadmintools_bill_hist', $result);
        } else {
            $diskgb = $usersjson->totals->bytes / self::$bytesbygb;
            $rowobj = array('year' => $year, 'month' => $month,
                'activeusers' => $usersjson->totals->actives, 'diskgb' => $diskgb,
                'currency' => $confs->currency, 'pricebyuser' => $confs->cost_by_user, 'pricebygb' => $confs->disk_cost,
                'bill' => self::apply_formula(
                    $diskgb, $usersjson->totals->actives, $confs->disk_cost, $confs->cost_by_user),
                'sendedbillnotif' => false, 'sendedthresholdnotif' => false);
            $DB->insert_record('local_mtadmintools_bill_hist', $rowobj);
        }

        self::send_mail_prev_month($year, $month, $day);
    }

    public static function create_user_from_client_data() {
        $confs = get_config('local_mtadmintools');

        if ( !isset($confs->clifullname) || empty($confs->clifullname) ||
             !isset($confs->cliemail) || empty($confs->cliemail) ) {
            return false;
        }

        $emailuser = new \stdClass();
        $emailuser->firstname = $confs->clifullname;
        $emailuser->email = $confs->cliemail;
        $emailuser->id = -99;
        $emailuser->maildisplay = true;
        $emailuser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
        $emailuser->lastname = $emailuser->firstnamephonetic = $emailuser->lastnamephonetic = '';
        $emailuser->middlename = $emailuser->alternatename = '';

        return $emailuser;
    }

    /**
     * Sends a billing notification mail for the last month if not yet sended.
     * Only executes during the first day of each month.
     * @param type $year current year (1970-YYYY)
     * @param type $month current month (1-12)
     * @param type $day current day (1-31)
     */
    public static function send_mail_prev_month($year, $month, $day) {
        global $CFG, $DB;

        // We only send email when we have arrived the next month.
        if ($day == 1) {
            $prevmonth = $month - 1;
            $prevyear = $year;
            if ($month == 1) {
                $prevmonth = 12;
                $prevyear = $year - 1;
            }
            $result = $DB->get_record('local_mtadmintools_bill_hist',
                array('year' => $prevyear, 'month' => $prevmonth), '*');
            if ($result && !$result->sendedbillnotif) {
                $touser = self::create_user_from_client_data();
                if ($touser) {
                    $supportuser = \core_user::get_support_user();
                    $confs = get_config('local_mtadmintools');
                    $msgobj = clone $result;
                    $msgobj->servicetitle = $confs->servicetitle;
                    $msgobj->url = "$CFG->wwwroot/local/mtadmintools/clientmanag.php";
                    $msgobj->supportemail = $CFG->noreplyaddress;
                    $subject = get_string('monthbill_subjetc', 'local_mtadmintools', $msgobj);
                    $message = get_string('monthbill_body', 'local_mtadmintools', $msgobj);
                    if (email_to_user($touser, $supportuser, $subject, $message)) {
                        $result->sendedbillnotif = true;
                        $DB->update_record('local_mtadmintools_bill_hist', $result);
                    }
                }
            }
        }
    }

    public static function apply_formula($gb, $numusers, $costbygb, $costbyusr) {
        return ($gb * $costbygb) + ($numusers * $costbyusr);
    }

    public static function get_tmp_dir($filename='') {
        global $CFG;

        $fullpath = $CFG->dataroot . '/temp/' . self::$relativetmpfolder;
        if ($filename) {
            $fullpath .= '/' . $filename;
        }
        return $fullpath;
    }

    public static function make_tmp_dir() {
        global $CFG;

        if (!is_dir($CFG->dataroot . '/temp/' . self::$relativetmpfolder)) {
            mkdir($CFG->dataroot . '/temp/' . self::$relativetmpfolder, $CFG->directorypermissions, true);
        }
    }

    public static function create_file($filename, $mode='w', $deleteprevious=true) {
        global $CFG;

        $filepath = $CFG->dataroot . '/temp/' . self::$relativetmpfolder . '/' . $filename;
        if (is_file($filepath) && $deleteprevious) {
            unlink($filepath);
        } else if (is_file($filepath) && !$deleteprevious) {
            return false;
        }
        return fopen($filepath, $mode);
    }

    public static function get_disk_consumption_sql($getjsontype = 'detailed') {
        global $CFG;

        if ($getjsontype == 'detailed') {
            $header = array(
                'contenthash' => 'contenthash',
                'filename' => get_string('filename', 'repository'),
                'component' => get_string('component', 'local_mtadmintools'),
                'filesize' => get_string('size'),
                'mimetype' => get_string('mimetype', 'local_mtadmintools'),
                'timecreated' => get_string('timecreated', 'local_mtadmintools'),
                'contextlevel' => get_string('contextlevel', 'local_mtadmintools'));
            $sql = "SELECT
                        " . implode(', ', array_keys($header)) . "
                    FROM
                        {files} f join {context} c on f.contextid = c.id
                    WHERE
                        f.filename != '.' and f.filearea not in ('draft')
                        and not (c.contextlevel in (20,30) and f.userid in ($CFG->siteadmins))
                    GROUP BY contenthash
                    ORDER BY f.filename ASC";
        } else {
            $sql = "SELECT
                        contenthash, contextlevel, component, SUM(filesize) as bytes
                    FROM
                        {files} f join {context} c on f.contextid = c.id
                    WHERE
                        f.filename != '.' and f.filearea not in ('draft')
                        and not (c.contextlevel in (20,30) and f.userid in ($CFG->siteadmins))
                    GROUP BY contenthash";
        }

        return $sql;
    }

    /**
     * Get JSON for Coogle Chart with global disk consumption info.
     * @global object $DB
     * @global object $CFG
     * @param string $getjsontype Type of info: detailed (creates a CSV).
     * @param bool $printresult If true, echoes the JSON, if false, return the JSON if true.
     * @return string JSON for Google Chart if $getjsontype!=detailed; JSCON with link to CSV if $getjsontype=detailed.
     */
    public static function csv_disk_consumption_data($getjsontype = 'detailed', $printresult=true) {
        global $DB, $CFG;

        self::make_tmp_dir();
        $filedetaileddisk = $CFG->dbname . '-today_detailed_consumption-' . mt_rand(100, 900) . '.csv';

        // 1. Detailed list of files consuming disk.
        if ($getjsontype == 'detailed') {
            $filep1 = self::create_file($filedetaileddisk, 'w');
            if (!($filep1 === false)) {
                $sql = self::get_disk_consumption_sql($getjsontype);
                $vdata = $DB->get_recordset_sql($sql);
                // Creating CSV.
                $header = array(
                    'contenthash' => 'contenthash',
                    'filename' => get_string('filename', 'repository'),
                    'component' => get_string('component', 'local_mtadmintools'),
                    'filesize' => get_string('size'),
                    'mimetype' => get_string('mimetype', 'local_mtadmintools'),
                    'timecreated' => get_string('timecreated', 'local_mtadmintools'),
                    'contextlevel' => get_string('contextlevel', 'local_mtadmintools'));
                fwrite($filep1, implode(';', array_values($header)).PHP_EOL);
                foreach ($vdata as $row) {
                    fwrite($filep1, implode(';', (array)$row).PHP_EOL);
                }
                $vdata->close();
                fclose($filep1);
            } else {
                $jsonlinks = json_encode(
                array('status' => 'nok',
                      'msg' => $CFG->wwwroot.'/local/mtadmintools/ws/file.php?f='.$filedetaileddisk));
            }
        }

        // 2. General disk consumption info.
        if ($getjsontype != 'detailed') {

            $vmeasurements = array(
                'course backup zone' => 0, 'user backup zone' => 0, 'course' => 0, 'user' => 0, 'other' => 0);
            $sql = self::get_disk_consumption_sql($getjsontype);
            $vdata = $DB->get_recordset_sql($sql);
            // Creating JSON structure.
            foreach ($vdata as $row) {
                switch ($row->contextlevel) {
                    case CONTEXT_COURSE:
                    case CONTEXT_MODULE:
                        if ($row->component == 'backup') {
                            $vmeasurements['course backup zone'] += $row->bytes;
                        } else {
                            $vmeasurements['course'] += $row->bytes;
                        }
                        break;
                    case CONTEXT_USER:
                        if ($row->component == 'backup') {
                            $vmeasurements['user backup zone'] += $row->bytes;
                        } else {
                            $vmeasurements['user'] += $row->bytes;
                        }
                        break;
                    default:
                        $vmeasurements['other'] += $row->bytes;
                        break;
                }
            }
            $vdata->close();
        }

        // Returns JSON with link to CSV.
        if ($getjsontype == 'detailed') {
            if (isset($filep1) && $filep1) {
                $jsonlinks = json_encode(
                array('status' => 'ok',
                      'link' => $CFG->wwwroot.'/local/mtadmintools/ws/file.php?f='.$filedetaileddisk));
            }
        } else {
            // Convert rows to array of arrays.
            $rows = array();
            $totalbytes = 0;
            foreach ($vmeasurements as $ind => $val) {
                $totalbytes += (float) $val;
                $temp = array();
                $temp[] = array('v' => (string) $ind);
                $temp[] = array('v' => (float) $val);
                $rows[] = array('c' => $temp);
            }
            // Create chart options.
            $vmeasurements = array(
                'cols' => array(
                    array('label' => get_string('head-title-area', 'local_mtadmintools'), 'type' => 'string'),
                    array('label' => get_string('head-title-bytes', 'local_mtadmintools'), 'type' => 'number')
                ),
                'rows' => $rows,
                'totals' => array('bytes' => $totalbytes, 'format' => display_size($totalbytes)));
            $jsonlinks = json_encode($vmeasurements);
        }

        if ($printresult) {
            echo $jsonlinks;
        } else {
            return $jsonlinks;
        }
    }

    public static function get_active_users_sql($mtadminyear=null, $mtadminmonth=null) {
        global $CFG;

        if (!isset($mtadminyear) || empty($mtadminyear)) {
            $year = date("Y");
        } else {
            $year = $mtadminyear;
        }
        if (!isset($mtadminmonth) || empty($mtadminmonth)) {
            $month = date("m");
        } else {
            $month = $mtadminmonth;
        }
        $startmonthtimestamp = strtotime("01-$month-$year 00:00");
        $sql = "(select 'month-actives', count(id) as num from {user} " .
                 "where id not in ($CFG->siteadmins) and deleted != 1 and " .
                    "lastaccess >= $startmonthtimestamp) " .
                "union " .
                "(select 'total-usrs', count(id) as num from {user} where id not in ($CFG->siteadmins) and " .
                    "deleted != 1 )";
        return $sql;
    }

    public static function get_active_users($printresult=true, $mtadminyear=null, $mtadminmonth=null) {
        global $DB, $CFG;

        $sql = self::get_active_users_sql($mtadminyear, $mtadminmonth);
        $vdata = $DB->get_recordset_sql($sql);

        $actives = 0;
        $totalusrs = 0;
        $rows = array();
        $temp = array(array('v' => ''));
        foreach ($vdata as $ind => $obj) {
            $temp[] = array('v' => (float) $obj->num);
            if ($ind == 'month-actives') {
                $actives = (float) $obj->num;
            } else if ($ind == 'total-usrs') {
                $totalusrs = (float) $obj->num;
            }
        }
        $vdata->close();
        $rows[] = array('c' => $temp);

        // Create chart options.
        $vmeasurements = array(
            'cols' => array(
                array('label' => get_string('chart_active_users_title', 'local_mtadmintools'), 'type' => 'string'),
                array('label' => get_string('month-actives', 'local_mtadmintools'), 'type' => 'number'),
                array('label' => get_string('total-usrs', 'local_mtadmintools'), 'type' => 'number')
            ),
            'rows' => $rows,
            'totals' => array('actives' => $actives, 'users' => $totalusrs));
        $jsonlinks = json_encode($vmeasurements);
        if ($printresult) {
            echo $jsonlinks;
        } else {
            return $jsonlinks;
        }
    }

    public static function get_bill_history($printresult=true) {
        global $DB, $CFG;

        $config = get_config('local_mtadmintools');
        $vdata = $DB->get_records('local_mtadmintools_bill_hist', null,
            'year DESC, month DESC', 'id,year,month,bill', 0, self::$billhistmonths);
        $vdata = array_reverse($vdata);

        $rows = array();
        foreach ($vdata as $ind => $val) {
            $temp = array();
            $temp[] = array('v' => $val->year . '/' . $val->month);
            $temp[] = array('v' => (float) $val->bill);
            $rows[] = array('c' => $temp);
        }
        // Create chart options.
        $vmeasurements = array(
            'cols' => array(
                array('label' => get_string('month'), 'type' => 'string'),
                array('label' => get_string('billing', 'local_mtadmintools') . ' ' .
                    $config->currency, 'type' => 'number')
            ),
            'rows' => $rows);
        $jsonlinks = json_encode($vmeasurements);
        if ($printresult) {
            echo $jsonlinks;
        } else {
            return $jsonlinks;
        }
    }

    public static function get_bill_history_detail($printresult=true) {
        global $DB, $CFG;

        self::make_tmp_dir();
        $filedetailedbillhist = $CFG->dbname . '-billing_history-' . mt_rand(100, 900) . '.csv';

        $filep1 = self::create_file($filedetailedbillhist, 'w');
        if (!($filep1 === false)) {
            $config = get_config('local_mtadmintools');
            $vdata = $DB->get_records('local_mtadmintools_bill_hist', null, 'year DESC, month DESC',
                'id,year,month,activeusers,diskgb,pricebyuser,pricebygb,bill,currency');
            $vdata = array_reverse($vdata);
            // Creating CSV.
            $header = array(
                'year' => get_string('year'),
                'month' => get_string('month'),
                'activeusers' => get_string('month-actives', 'local_mtadmintools'),
                'diskgb' => get_string('disk_in_gb', 'local_mtadmintools'),
                'pricebyuser' => get_string('cost_by_user', 'local_mtadmintools'),
                'pricebygb' => get_string('disk_cost', 'local_mtadmintools'),
                'bill' => get_string('charge', 'local_mtadmintools'),
                'currency' => get_string('currency', 'enrol_paypal'));
            fwrite($filep1, implode(';', array_values($header)).PHP_EOL);
            foreach ($vdata as $row) {
                unset($row->id);
                fwrite($filep1, implode(';', (array)$row).PHP_EOL);
            }
            fclose($filep1);

            $jsonlinks = json_encode(
            array('status' => 'ok',
                  'link' => $CFG->wwwroot.'/local/mtadmintools/ws/file.php?f='.$filedetailedbillhist));
        } else {
            $jsonlinks = json_encode(
            array('status' => 'nok',
                  'msg' => $CFG->wwwroot.'/local/mtadmintools/ws/file.php?f='.$filedetailedbillhist));
        }

        if ($printresult) {
            echo $jsonlinks;
        } else {
            return $jsonlinks;
        }
    }
}