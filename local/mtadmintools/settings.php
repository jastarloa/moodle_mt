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
 * Paypal enrolments plugin settings and presets.
 *
 * @package    local_mtadmintools
 * @copyright  2017
 * @author     Manu Peño
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    /* Define global settings */
    require_once($CFG->dirroot.'/local/mtadmintools/lib/mt_management.php');

    $defcurr = 'USD';
    $defdiskcost = 0.5;
    $defusercost = 0.2;
    $defservicetitle = "Moodle MT";

    $settings = new admin_settingpage('local_mtadmintools', get_string('pluginname', 'local_mtadmintools'));
    $settings->add(
        new admin_setting_heading('local/tenant_pricing',
                get_string('tenant_pricing', 'local_mtadmintools'), ''));
    $settings->add(
        new admin_setting_configtext('local_mtadmintools/servicetitle', get_string('servicetitle', 'local_mtadmintools'),
                get_string('servicetitle_help', 'local_mtadmintools'), $defservicetitle, PARAM_RAW, 20));
    $currencies = multi_tenant_management::get_currencies();
    $settings->add(
        new admin_setting_configselect('local_mtadmintools/currency', get_string('currency', 'enrol_paypal'),
                '', $defcurr, $currencies));
    $settings->add(
        new admin_setting_configtext('local_mtadmintools/disk_cost', get_string('disk_cost', 'local_mtadmintools'),
                get_string('disk_cost_help', 'local_mtadmintools'), $defdiskcost, PARAM_FLOAT, 4));
    $settings->add(
        new admin_setting_configtext('local_mtadmintools/cost_by_user',
                get_string('cost_by_user', 'local_mtadmintools'),
                get_string('cost_by_user_help', 'local_mtadmintools'), $defusercost, PARAM_FLOAT, 4));

    $ADMIN->add('localplugins', $settings);
}
