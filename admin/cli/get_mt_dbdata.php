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
 * This script recover all DB tenants for command line purpouses
 *
 * @package    core
 * @subpackage cli
 * @copyright  2015 Manu peño
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
Calling samples:
  >sudo -u apache /usr/bin/php /var/www/html/moodle_mt/admin/cli/get_mt_dbdata.php --tenant=tenant1.mydomain.com
   # prints all databases in the server accesible for the $CFG->dbuser and passwrod matching with the base domain, eg:
   #tenant1.mydomain.com,tenant2.mydomain.com,tenant2.mydomain.com
*/

// Force OPcache reset if used, we do not want any stale caches
// when detecting if upgrade necessary or when running upgrade.
if (function_exists('opcache_reset') and !isset($_SERVER['REMOTE_ADDR'])) {
    opcache_reset();
}

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);

require(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/local/mtadmintools/lib/multitenant_db.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(
    array(
        'help'              => false,
        'tenant'            => false
    ),
    array(
        'h' => 'help'
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Command line Moodle upgrade.
Please note you must execute this script with the same uid as apache!

Site defaults may be changed via local/defaults.php.

Options:
-h, --help            Print out this help
--tenant              One tenant is needed for the first db connection

Example:
\$sudo -u www-data /usr/bin/php admin/cli/get_mt_databases.php --tenant=tenant1.mydomain.com
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

try {
    $mtdbmanager = new multi_tenant_db_manager($moodle_db);
    $alltenantsdbs = $mtdbmanager->get_alltenants_dbs();
    echo implode(',',$alltenantsdbs);
} catch (Exception $ex) {
    exit(1); // error
}
exit(0); // 0 means success
