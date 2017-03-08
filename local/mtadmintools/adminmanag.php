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
 * This file is responsible for saving the results of a users survey and displaying
 * the final message.
 *
 * @package   local_mtadmintools
 * @copyright 2017
 * @author Manu Peño
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/local/mtadmintools/lib/mt_management.php');

$PAGE->set_url('/local/mtadmintools/adminmanag.php');
require_login();

$context = context_system::instance();
if (!is_siteadmin()) {
    print_error('nopermissions', 'admin');
}

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$pagetitle = get_string('mtadminmngmnt', 'local_mtadmintools');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->requires->js(new moodle_url('/local/mtadmintools/js/adminrep.js'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('adminmngmntsection', 'local_mtadmintools'), 2);

$output = $PAGE->get_renderer('local_mtadmintools');
echo $output->get_adminpage_data();

$adminmtdataform = new adminmt_data_form();

if ($adminmtdataform->get_data()) {
    $adminmtdataform->store_data();
    echo $OUTPUT->notification(get_string('success'), 'success');
}

$adminmtdataform->display();

echo $OUTPUT->heading(get_string('billinginfo', 'local_mtadmintools'), 2);

// Billing hist.
$icon = $OUTPUT->pix_icon('f/calc-64', get_string('billinghist', 'local_mtadmintools'), 'moodle', array());
$icon .= html_writer::tag('text', 'CSV', array('style' => 'display:block;'));
$editaction = html_writer::tag(
    'text', get_string('billinghist', 'local_mtadmintools'), array('style' => 'display:block;'));
$editaction .= $OUTPUT->action_link(
    'javascript:void(0)', $icon, null, array('id' => 'mtalltenantsbillhist'));
echo html_writer::tag('div', $editaction, array('class' => 'mt-csv-icon'));

echo $OUTPUT->footer();
