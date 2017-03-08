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
 * Multi tenant management library.
 *
 * @package    local_mtadmintools
 * @copyright  2017
 * @author Manu Peño
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot.'/local/mtadmintools/lib/multitenant_db.php');

/**
 * To import into each plugin for multi-tenant management purpouses which need to switch on the DB schemes associated
 * with tenants based on a given base domain.
 */
class multi_tenant_management {

    public static $maxhistmonths = 24;
    private $_mtdbmanager;

    public static function get_currencies() {
        // 3-character ISO-4217:
        // https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_currency_codes.
        $codes = array(
            'AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY',
            'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'RUB', 'SEK', 'SGD', 'THB', 'TRY', 'TWD', 'USD');
        $currencies = array();
        foreach ($codes as $c) {
            $currencies[$c] = new lang_string($c, 'core_currencies');
        }

        return $currencies;
    }

    public static function ws_url_req_params($action) {
        global $mtadminyear, $mtadminmonth, $mtadmincourse, $mtadminuser;

        switch ($action) {
            case 'disk_gral_data':
            case 'disk_detailed_data':
            case 'bill_history':
            case 'bill_history_detailed_data':
                break;
            case 'active_users_data':
                $mtadminyear = optional_param('year', 0, PARAM_INT);
                $mtadminmonth = optional_param('month', 0, PARAM_INT);
                break;
            case 'adminmt_bill_history':
                if (!is_siteadmin()) {
                    throw new moodle_exception('nopermissions', 'admin');
                }
                break;
            default:
                throw new moodle_exception('invalidarguments');
        }
    }

    // Prints data in JSON format.
    public static function ws_print_data($action) {
        global $mtadminyear, $mtadminmonth;

        switch ($action) {
            case 'disk_gral_data':
                local_mtadmintools\task\calc_bill::csv_disk_consumption_data('general');
                break;
            case 'disk_detailed_data':
                local_mtadmintools\task\calc_bill::csv_disk_consumption_data('detailed');
                break;
            case 'active_users_data':
                local_mtadmintools\task\calc_bill::get_active_users(true, $mtadminyear, $mtadminmonth);
                break;
            case 'bill_history':
                local_mtadmintools\task\calc_bill::get_bill_history();
                break;
            case 'bill_history_detailed_data':
                local_mtadmintools\task\calc_bill::get_bill_history_detail();
                break;
            case 'adminmt_bill_history':
                self::alltenants_billhist();
                break;
            default:
                throw new moodle_exception('invalidarguments');
        }
    }

    public static function alltenants_billhist($printresult=true) {
        global $CFG, $DB;

        $mtdbmanager = new multi_tenant_db_manager($CFG->dbname);
        $alltenants = $mtdbmanager->get_alltenants_dbs();
        $mtdbmanager->restore_originaldb();

        \local_mtadmintools\task\calc_bill::make_tmp_dir();
        $filealltenantsbillhist = 'alltenants_billhist-' . mt_rand(100, 900) . '.csv';

        $filep1 = \local_mtadmintools\task\calc_bill::create_file($filealltenantsbillhist, 'w');
        if (!($filep1 === false)) {
            // Creating CSV.
            $header = array(
                'tenant' => get_string('tenant', 'local_mtadmintools'),
                'year' => get_string('year'),
                'month' => get_string('month'),
                'activeusers' => get_string('month-actives', 'local_mtadmintools'),
                'diskgb' => get_string('disk_in_gb', 'local_mtadmintools'),
                'pricebyuser' => get_string('cost_by_user', 'local_mtadmintools'),
                'pricebygb' => get_string('disk_cost', 'local_mtadmintools'),
                'bill' => get_string('charge', 'local_mtadmintools'),
                'currency' => get_string('currency', 'enrol_paypal'),
                'sendedbillnotif' => 'sendedbillnotif'
            );
            fwrite($filep1, implode(';', array_values($header)) . PHP_EOL);
            foreach ($alltenants as $dbtenant) {
                $mtdbmanager->new_db_connection($dbtenant);
                $vdata = $DB->get_records('local_mtadmintools_bill_hist', null, 'year DESC, month DESC',
                    'id,year,month,activeusers,diskgb,pricebyuser,pricebygb,bill,currency,sendedbillnotif');
                $vdata = array_reverse($vdata);
                foreach ($vdata as $row) {
                    unset($row->id);
                    fwrite($filep1, $dbtenant . ';' . implode(';', (array)$row) . PHP_EOL);
                }
            }
            fclose($filep1);
            $mtdbmanager->restore_originaldb();

            $jsonlinks = json_encode(
            array('status' => 'ok',
                  'link' => $CFG->wwwroot.'/local/mtadmintools/ws/file.php?f='.$filealltenantsbillhist));
        } else {
            $jsonlinks = json_encode(
            array('status' => 'nok',
                  'msg' => $CFG->wwwroot.'/local/mtadmintools/ws/file.php?f='.$filealltenantsbillhist));
        }

        echo $jsonlinks;
    }
}

class client_data_form extends moodleform {

    private $_defaults;

    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;
        $custdata = $this->_customdata;

        $velems = array();

        $mform->addElement('header', 'contactdata', get_string('contact_data', 'local_mtadmintools'));
        $velems[] = $mform->addElement('text', 'clifullname', get_string('fullname'));
        $mform->addRule('clifullname', null, 'required', null, 'client');
        $mform->setType('clifullname', PARAM_TEXT);
        $velems[] = $mform->addElement('text', 'cliemail', get_string('contactemail', 'hub'));
        $mform->setType('cliemail', PARAM_EMAIL);
        $mform->addRule('cliemail', null, 'required', null, 'client');
        $velems[] = $mform->addElement('text', 'cliemailconfirm',
            get_string('contactemailconfirm', 'local_mtadmintools'));
        $mform->setType('cliemailconfirm', PARAM_EMAIL);
        $mform->setDefault('cliemailconfirm', '');
        $velems[] = $mform->addElement('text', 'cliaddress', get_string('address'));
        $mform->setType('cliaddress', PARAM_TEXT);
        $mform->addRule('cliaddress', null, 'required', null, 'client');
        $velems[] = $mform->addElement('text', 'clicity', get_string('city'));
        $mform->setType('clicity', PARAM_TEXT);
        $mform->addRule('clicity', null, 'required', null, 'client');
        $velems[] = $mform->addElement('text', 'clistate', get_string('state', 'local_mtadmintools'));
        $mform->setType('clistate', PARAM_TEXT);
        $mform->addRule('clistate', null, 'required', null, 'client');
        $velems[] = $mform->addElement('text', 'clipostcode', get_string('postaladdress', 'hub'));
        $mform->setType('clipostcode', PARAM_ALPHANUMEXT);
        $mform->addRule('clipostcode', null, 'required', null, 'client');
        $mform->addRule('clipostcode', null, 'numeric', null, 'client');
        $velems[] = $mform->addElement('select', 'clicountry',
            get_string('country'), get_string_manager()->get_list_of_countries());
        $mform->setDefault('clicountry', $USER->country);
        $mform->addRule('clicountry', null, 'required', null, 'client');
        $mform->setType('clicountry', PARAM_ALPHANUMEXT);
        $velems[] = $mform->addElement('text', 'cliphone', get_string('phone'), 'maxlength="20"');
        $mform->setType('cliphone', PARAM_TEXT);
        $mform->addRule('cliphone', null, 'required', null, 'client');

        $mform->addElement('header', 'specialsettings', get_string('extrasettings', 'local_mtadmintools'));
        $bckzones = array('course' => get_string('course'),
            'moodledef' => get_string('moodledef', 'local_mtadmintools'));
        $velems[] = $mform->addElement('select', 'tenantdefbackupzone',
            get_string('tenantdefbackupzone', 'local_mtadmintools'), $bckzones);
        $mform->setType('tenantdefbackupzone', PARAM_ALPHANUMEXT);
        $mform->setDefault('tenantdefbackupzone', $CFG->backup_general_filearaea);

        if (isset($custdata['allowedit']) && !empty($custdata['allowedit'])) {
            $this->add_action_buttons(false, get_string('submit'));
        } else {
            foreach ($velems as $elem) {
                $elem->freeze();
            }
        }
        $this->_defaults = $this->get_stored_data();
        if (isset($this->_defaults) && !empty($this->_defaults)) {
            $this->set_data($this->_defaults);
        }
        if (!isset($this->_defaults->cliemail) || empty($this->_defaults->cliemail)) {
            $mform->addRule('cliemailconfirm', null, 'required', null, 'client');
        }
    }

    /**
     * Recover the stored values from mdl_config_plugins table
     * @return array list with the stored values for each form field
     */
    private function get_stored_data() {
        return get_config('local_mtadmintools');
    }

    /**
     * Stores values into mdl_config_plugins table.
     */
    public function store_data() {
        $data = (array)$this->get_data();
        // The confirmation mail will not be stored.
        unset($data['cliemailconfirm']);
        foreach ($data as $field => $val) {
            switch ($field) {
                case 'tenantdefbackupzone':
                    set_config('backup_general_filearaea', $val);
                    break;
                default:
                    set_config($field, $val, 'local_mtadmintools');
                    break;
            }
        }
    }

    // Perform some extra validation.
    public function validation($data, $files) {
        $errors = array();

        $yetdata = get_config('local_mtadmintools');

        // Contact email and Confirmation email must be the same.
        if ((!isset($yetdata->cliemail) || !empty($data['cliemailconfirm'])) && ($data['cliemail'] != $data['cliemailconfirm'])) {
            $errors['cliemailconfirm'] = get_string('contactemailconfirm_err', 'local_mtadmintools');
        }
        return $errors;
    }
}

class billing_data_form extends moodleform {

    private $_defaults;

    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;

        // Billing data (read only).
        $confs = get_config('local_mtadmintools');
        $mform->addElement('hidden', 'currency', $confs->currency);
        $mform->setType('currency', PARAM_RAW);
        $mform->addElement('static', 'disk_cost', get_string('disk_cost', 'local_mtadmintools'),
            "<span data-diskcost='$confs->disk_cost'>" . $confs->disk_cost . ' ' . $confs->currency . '</span>');
        $mform->addElement('static', 'cost_by_user', get_string('cost_by_user', 'local_mtadmintools'),
            "<span data-usercost='$confs->cost_by_user'>" .$confs->cost_by_user . ' ' . $confs->currency . '</span>');
        // Current.
        $mform->addElement('static', 'monthbalance', get_string('monthbalance', 'local_mtadmintools',
            userdate(time(), get_string('strftimedate', 'langconfig'), 99, false)));
    }
}

class adminmt_data_form extends moodleform {

    private $_alltenantdbs;
    private $_mtdbmanager;

    public function definition() {
        global $CFG, $USER;

        $mform = $this->_form;
        $custdata = $this->_customdata;

        $this->_mtdbmanager = new multi_tenant_db_manager($CFG->dbname);
        $options = $this->_mtdbmanager->get_alltenants_dbs();
        $this->_mtdbmanager->restore_originaldb();
        $this->_alltenantdbs = array_combine($options, $options);
        $confs = get_config('local_mtadmintools');

        $velems = array();

        $mform->addElement('header', 'adminmtdata', get_string('mtadminmngmnt', 'local_mtadmintools'));

        $alltenantdbs = $mform->addElement('select', 'alltenantdbs', get_string('clients', 'local_mtadmintools'),
            $this->_alltenantdbs);
        $alltenantdbs->setMultiple(true);
        $velems[] = $alltenantdbs;
        $mform->setType('alltenantdbs', PARAM_RAW);

        $velems[] = $mform->addElement('text', 'servicetitle', get_string('servicetitle', 'local_mtadmintools'));
        $mform->setDefault('servicetitle', $confs->servicetitle);
        $mform->setType('servicetitle', PARAM_RAW);

        $currencies = multi_tenant_management::get_currencies();
        $velems[] = $mform->addElement('select', 'currency', get_string('currency', 'enrol_paypal'), $currencies);
        $mform->setDefault('currency', $confs->currency);
        $mform->setType('currency', PARAM_ALPHANUMEXT);

        $velems[] = $mform->addElement('text', 'disk_cost', get_string('disk_cost', 'local_mtadmintools'));
        $mform->setDefault('disk_cost', $confs->disk_cost);
        $mform->setType('disk_cost', PARAM_FLOAT);

        $velems[] = $mform->addElement('text', 'cost_by_user', get_string('cost_by_user', 'local_mtadmintools'));
        $mform->setDefault('cost_by_user', $confs->cost_by_user);
        $mform->setType('cost_by_user', PARAM_FLOAT);

        $this->add_action_buttons(false, get_string('submit'));
    }

    /**
     * Stores values into mdl_config_plugins table.
     */
    public function store_data() {
        global $DB;

        $confs = get_config('local_mtadmintools');

        $data = (array)$this->get_data();
        if (isset($data['alltenantdbs']) && !empty($data['alltenantdbs']) && is_array($data['alltenantdbs'])) {
            foreach ($data['alltenantdbs'] as $dbtenant) {
                $billinfoupdated = array();
                $this->_mtdbmanager->new_db_connection($dbtenant);
                $configs = $DB->get_records("config_plugins",
                    array('plugin' => 'local_mtadmintools'), '', 'name,value');

                if (!empty($data['servicetitle']) && $configs['servicetitle']->value != $data['servicetitle']) {
                    $billinfoupdated['servicetitle'] = array('prev' => $configs['servicetitle']->value,
                        'curr' => $data['servicetitle']);
                    set_config('servicetitle', $data['servicetitle'], 'local_mtadmintools');
                }
                if (!empty($data['currency']) && $configs['currency']->value != $data['currency']) {
                    $billinfoupdated['currency'] = array('prev' => $configs['currency']->value,
                        'curr' => $data['currency']);
                    set_config('currency', $data['currency'], 'local_mtadmintools');
                }
                if (!empty($data['disk_cost']) && $configs['disk_cost']->value != $data['disk_cost']) {
                    $billinfoupdated['disk_cost'] = array('prev' => $configs['disk_cost']->value,
                        'curr' => $data['disk_cost']);
                    set_config('disk_cost', $data['disk_cost'], 'local_mtadmintools');
                }
                if (!empty($data['cost_by_user']) && $configs['cost_by_user']->value != $data['cost_by_user']) {
                    $billinfoupdated['cost_by_user'] = array('prev' => $configs['cost_by_user']->value,
                        'curr' => $data['cost_by_user']);
                    set_config('cost_by_user', $data['cost_by_user'], 'local_mtadmintools');
                }
                if (!empty($billinfoupdated)) {
                    $touser = \local_mtadmintools\task\calc_bill::create_user_from_client_data();
                    if ($touser) {
                        $supportuser = \core_user::get_support_user();
                        $subject = get_string('billchanges_subject', 'local_mtadmintools', $confs);
                        $bodymsg = get_string('billchanges_bodyhead', 'local_mtadmintools');
                        foreach ($billinfoupdated as $key => $changed) {
                            $bodymsg .= "   " .
                                get_string($key, 'local_mtadmintools') . ": " .
                                    $changed['prev'] . " => " . $changed['curr'];
                        }
                        $bodymsg .= get_string('billchanges_bodyfoot', 'local_mtadmintools', $confs);
                        email_to_user($touser, $supportuser, $subject, $bodymsg);
                    }
                }
            }
            $this->_mtdbmanager->restore_originaldb();
        }
    }
}