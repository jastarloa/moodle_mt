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
 * Strings for component 'local_mtadmintools', language 'en'
 *
 * @package   local_mtadmintools
 * @copyright  2017
 * @autor   Manu Peño
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Multi-tenant admin tools';

$string['mtadmintools:manageclientsettings'] = "Manage client account settings";
$string['mtadmintools:readclientsettings'] = "Read client account settings";
$string['calcbilltask'] = "Moodle mt - Calc tenant billing by month";
$string['cleantemptask'] = "Moodle mt - Clean temporal reports";

$string['mtclientzone'] = 'Client zone';
$string['mtclientaccsettings'] = 'Client account settings';
$string['mtadminmngmnt'] = 'Multi-tenant management';

$string['tenant'] = 'Tenant';
$string['selectdbtenant'] = 'Select one or more tenants';

$string['clients'] = 'Clients';
$string['clientsettingssection'] = 'Account settings';
$string['adminmngmntsection'] = 'Admin management';
$string['billinginfo'] = 'Billing info';
$string['billing'] = 'Billing';
$string['billinghist'] = 'Billing history';
$string['monthbalance'] = 'Balance of the month, until {$a}';
$string['clientconsumsection'] = 'Consumption data';
$string['tenantdefbackupzone'] = 'Backups zone';
$string['forcecourse'] = 'Forced to course';
$string['moodledef'] = 'Moodle defined';
$string['contact_data'] = 'Contact data';
$string['extrasettings'] = 'Extra settings';
$string['update'] = 'Update';
$string['contactemailconfirm'] = 'Confirm email';
$string['contactemailconfirm_err'] = 'Confirm email not match';
$string['state'] = 'State/Province';
$string['servicetitle'] = 'Service title';
$string['servicetitle_help'] = 'Name of the service that appears as the issuer of invoices';
$string['tenant_pricing'] = 'Tenant pricing';
$string['currency'] = 'Currency';
$string['disk_in_gb'] = 'Disk consumption (in GB)';
$string['disk_cost'] = 'Price per disk GB';
$string['disk_cost_help'] = 'Client files and images will be taken into account (stored backups, images attached to ' .
    'text editors, documents uploaded to courses, etc.).' .
    'Please use dor as decimal mark.';
$string['cost_by_user'] = 'Price per active user';
$string['cost_by_user_help'] = 'An active user will be one who has logged into the application throughout the month';

$string['component'] = 'Component';
$string['mimetype'] = 'Mime type';
$string['timecreated'] = 'Creation date';
$string['contextlevel'] = 'Context';

$string['chart_disk_title'] = 'Disk consumption';
$string['chart_active_users_title'] = 'Users';
$string['total-usrs'] = 'Total number of users';
$string['month-actives'] = 'Month active users';
$string['chart_history_title'] = 'Consumption history (last {$a} months)';
$string['head-title-area'] = 'Area';
$string['head-title-bytes'] = 'Bytes';
$string['charge'] = 'Charge';

$string['billchanges_subject'] = 'Pricing changes on your {$a->servicetitle} account';
$string['billchanges_bodyhead'] = '
The following billing parameters have been changed:

';
$string['billchanges_bodyfoot'] = '

Thank you for using {$a->servicetitle}.

Sincerely,

{$a->servicetitle}';

$string['monthbill_subjetc'] = '{$a->servicetitle} Billing Statement Available';
$string['monthbill_body'] = 'Greetings from {$a->servicetitle},

This e-mail confirms that your latest billing statement is available.
Your account will be charged the following:

Total: {$a->bill}

You can see more details on your client area ({$a->url}) or by asking to {$a->supportemail}

Thank you for using {$a->servicetitle}.

Sincerely,

{$a->servicetitle}';