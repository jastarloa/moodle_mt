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
 * Like simple upgrade but only for external plugins. This script will not deploy a global installation nor upgrade.
 *
 * @package    core
 * @subpackage cli
 * @copyright  2015 Manu Peño
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
Calling samples:
  >sudo -u apache /usr/bin/php /var/www/html/moodle_mt/admin/cli/upgrade_noncore.php --tenant=tenant1.mydomain.com
*/

// Force OPcache reset if used, we do not want any stale caches
// when detecting if upgrade necessary or when running upgrade.
if (function_exists('opcache_reset') and !isset($_SERVER['REMOTE_ADDR'])) {
    opcache_reset();
}

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);

require(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');       // various admin-only functions
require_once($CFG->libdir.'/upgradelib.php');     // general upgrade/install related functions
require_once($CFG->libdir.'/clilib.php');         // cli only functions
require_once($CFG->libdir.'/environmentlib.php');

// now get cli options
$lang = isset($SESSION->lang) ? $SESSION->lang : $CFG->lang;
list($options, $unrecognized) = cli_get_params(
    array(
        'help'              => false,
        'tenant'              => false
    ),
    array(
        'h' => 'help'
    )
);

echo "-----------------------------------------------"."\n";
echo $options['tenant']."\n";
echo "-----------------------------------------------"."\n";

if (isset($options['lang'])) {
    $SESSION->lang = $options['lang'];
}

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
--lang=CODE           Set preferred language for CLI output. Defaults to the
                      site language if not set. Defaults to 'en' if the lang
                      parameter is invalid or if the language pack is not
                      installed.
-h, --help            Print out this help
--tenant              Tenant domain in a multi-tenant installation

Example:
\$sudo -u www-data /usr/bin/php admin/cli/upgrade.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

if (empty($CFG->version)) {
    cli_error(get_string('missingconfigversion', 'debug'));
}

require("$CFG->dirroot/version.php");       // defines $version, $release, $branch and $maturity
$CFG->target_release = $release;            // used during installation and upgrades

if ($version < $CFG->version) {
    cli_error(get_string('downgradedcore', 'error'));
}

$oldversion = "$CFG->release ($CFG->version)";
$newversion = "$release ($version)";

if (!moodle_needs_upgrading()) {
    cli_error(get_string('cliupgradenoneed', 'core_admin', $newversion), 0);
}

// Test environment first.
list($envstatus, $environment_results) = check_moodle_environment(normalize_version($release), ENV_SELECT_RELEASE);
if (!$envstatus) {
    $errors = environment_get_errors($environment_results);
    cli_heading(get_string('environment', 'admin'));
    foreach ($errors as $error) {
        list($info, $report) = $error;
        echo "!! $info !!\n$report\n\n";
    }
    exit(1);
}

// Test plugin dependencies.
$failed = array();
if (!core_plugin_manager::instance()->all_plugins_ok($version, $failed)) {
    cli_problem(get_string('pluginscheckfailed', 'admin', array('pluginslist' => implode(', ', array_unique($failed)))));
    cli_error(get_string('pluginschecktodo', 'admin'));
}


// unconditionally upgrade
upgrade_noncore(true);

// log in as admin - we need doanything permission when applying defaults
\core\session\manager::set_user(get_admin());

// apply all default settings, just in case do it twice to fill all defaults
admin_apply_default_settings(NULL, false);
admin_apply_default_settings(NULL, false);

echo get_string('cliupgradefinished', 'admin')."\n";
exit(0); // 0 means success
