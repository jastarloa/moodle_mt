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
 * This file processes AJAX requests for charts. If success, responds one JSON with a link to the CSV info, which
 * is allocated into this plugin temp folder from moodledata.
 *
 * @package local_mtadmintools
 * @copyright 2017
 * @author Manu Peño
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/local/mtadmintools/lib/mt_management.php');

$filename = required_param('f', PARAM_RAW);

$fullpath = local_mtadmintools\task\calc_bill::get_tmp_dir($filename);

require_login();
$showpage = has_any_capability(
    array('local/mtadmintools:manageclientsettings', 'local/mtadmintools:readclientsettings'),
    context_system::instance());

$fp = fopen("$fullpath", "r");
if (!$fp) {
    http_response_code(404);
    echo "Not found";
    die;
}
$size = filesize($fullpath);

header('Content-type: text/csv');
header('Last-Modified: '. gmdate('D, d M Y H:i:s', time()) .' GMT');
header('Accept-Ranges: none');
header("Content-disposition: attachment;filename=$filename.csv");
header('Content-Length: '.$size);
header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0, no-transform');
header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
header('Pragma: no-cache');

while (($line = fgets($fp)) !== false) {
    echo $line;
}
if (!feof($fp)) {
    echo "Error: fallo inesperado de fgets()\n";
}
fclose($fp);
