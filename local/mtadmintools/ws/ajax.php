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
define('AJAX_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot.'/local/mtadmintools/lib/mt_management.php');

$action = required_param('action', PARAM_ALPHAEXT);

multi_tenant_management::ws_url_req_params($action);

$context = context_system::instance();
$PAGE->set_context($context);

require_login();
$showpage = has_any_capability(
    array('local/mtadmintools:manageclientsettings', 'local/mtadmintools:readclientsettings'), $context);

$OUTPUT->header();

multi_tenant_management::ws_print_data($action);
die();
