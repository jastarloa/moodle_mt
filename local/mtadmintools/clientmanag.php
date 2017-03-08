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
 * @copyright 2015
 * @author Manu Peño
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/local/mtadmintools/lib/mt_management.php');

$PAGE->set_url('/local/mtadmintools/clientmanag.php');
require_login();

$context = context_system::instance();
$showpage = has_any_capability(
    array('local/mtadmintools:manageclientsettings', 'local/mtadmintools:readclientsettings'), $context);
$allowedit = has_capability('local/mtadmintools:manageclientsettings', $context);

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$pagetitle = get_string('mtclientaccsettings', 'local_mtadmintools');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->requires->js(new moodle_url('/local/mtadmintools/js/charts.js'));

echo $OUTPUT->header();

// Manage submitted data.
$clientdataform = new client_data_form(null, array('allowedit' => $allowedit));
if ($clientdataform->get_data()) {
    if ($allowedit) {
        $clientdataform->store_data();
        echo $OUTPUT->notification(get_string('success'), 'success');
    } else {
        echo $OUTPUT->notification(get_string('nopermissions', 'error',
                get_string('mtadmintools:manageclientsettings', 'local_mtadmintools')), 'error');
    }
}

// Prints window sections.
$output = $PAGE->get_renderer('local_mtadmintools');
echo $output->get_google_charts();
echo $output->get_charts_data();

echo $OUTPUT->heading(get_string('clientsettingssection', 'local_mtadmintools'), 2);
$clientdataform->display();

echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');
echo $OUTPUT->heading(get_string('billinginfo', 'local_mtadmintools'), 2);

$billingform = new billing_data_form();
$billingform->display();

echo html_writer::empty_tag('br');
echo $OUTPUT->heading(get_string('clientconsumsection', 'local_mtadmintools'), 2);
echo html_writer::start_tag('div', array('id' => 'mt-graphs'));

// Disk files CSV.
$icon = $OUTPUT->pix_icon('f/calc-64', 'CSV', 'moodle', array());
$icon .= html_writer::tag('text', 'CSV', array('style' => 'display:block;'));
$editaction = html_writer::tag('text', get_string('files'), array('style' => 'display:block;'));
$editaction .= $OUTPUT->action_link(
    'javascript:void(0)', $icon, null, array('id' => 'mtadmintoolsfilescsv'));
echo html_writer::tag('div', $editaction, array('class' => 'mt-csv-icon'));

// Disk graph.
echo html_writer::start_tag('div', array('class' => 'mt-chart'));
echo html_writer::tag('div', '', array('id' => 'chart_disk'));
echo html_writer::end_tag('div');

// Users graph.
echo html_writer::tag('div', '', array('id' => 'chart_active_users', 'class' => 'mt-chart'));

echo html_writer::empty_tag('br');

// Billing hist.
$icon = $OUTPUT->pix_icon('f/calc-64', get_string('billinghist', 'local_mtadmintools'), 'moodle', array());
$icon .= html_writer::tag('text', 'CSV', array('style' => 'display:block;'));
$editaction = html_writer::tag(
    'text', get_string('billinghist', 'local_mtadmintools'), array('style' => 'display:block;'));
$editaction .= $OUTPUT->action_link(
    'javascript:void(0)', $icon, null, array('id' => 'mtadmintoolsfilesbillhist'));
echo html_writer::tag('div', $editaction, array('class' => 'mt-csv-icon'));

// Month history graph.
echo html_writer::tag('div', '', array('id' => 'chart_history', 'class' => 'mt-chart'));

echo html_writer::end_tag('div');



echo $OUTPUT->footer();