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
 * This hack is intended for clustered sites that do not want
 * to use shared cachedir for component cache.
 *
 * This file needs to be called after any change in PHP files in dataroot,
 * that is before upgrade and install.
 *
 * @package   core
 * @copyright 2013 Petr Skoda (skodak)  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
THIS IS A SCRIPT TO UNINSTALL PLUGINS OF TYPE block, enroll, report, local, format and / or theme FAST WAY AND FROM
COMMAND LINE. IT IS ADAPTED TO SPECIFY OUR TENANT CONCEPT, TO SPECIFY A FILE WITH THE PLUGINS TO UNINSTALL (line
format = plugintype / pluginname) AND TO SPECIFY IF YOU WANT TO DELETE THE FOLDER AFTER YOU UNINSTALLED IT OR NOT.

Calling sample:
  >sudo -u apache /usr/bin/php /var/www/html/moodle_mt/admin/cli/uninstall_plugins.php \
     --tenant=tenant1.mydomain.com --filepath=/temp/plugins.csv

Content sample for --filepath (eg.: /temp/plugins.csv), a file wit plugins to be uninstalled, separated by '\n':
    block/myblock
    local/mylocal
    mod/mod_mymod
    theme/mytheme
    report/myreport
    enrol/myenrol

IMPORTANT1: Order is important. You must delete first plugins not required by others.
IMPORTANT2: In a front cluster, please, don't use deletefiles param from a node. Instead this, you must do a logical
unistalling and file deletion must be done externally, with a multi instance tool (eg: Chef, Puppet, AWS SMS...)
*/

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once("$CFG->libdir/clilib.php");
require_once("$CFG->libdir/adminlib.php");
// NexT needed for uninstalling mods.
require_once("$CFG->dirroot/course/lib.php");
list($options, $unrecognized) = cli_get_params(array('enable' => false, 'enablelater' => 0, 'enableold' => false,
        'disable' => false, 'help' => false, 'tenant' => false, 'filepath' => false, 'deletefiles' => false),
        array('h' => 'help'));

echo "PROCESSING TENANT $moodle_host\n";

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}
if ($options['filepath'] != false && file_exists($options['filepath'])) {
    $fp = fopen($options['filepath'], "r");
    $linenum = 1;
    while (!feof($fp)) {
        $linea = fgets($fp);
        $pieces = explode("/", $linea);
        switch ($pieces[0]) {
            case 'mod':
                $pluginman = core_plugin_manager::instance();
                $pluginfo = $pluginman->get_plugin_info(trim($pieces[1]));
                // We will not check dependencies nor delete folders after uninstallation because, being multi-tenant,
                // we must continue with the massive uninstallation in the next tenant.
                // When the uninstallation is completed successfully, you can proceed to delete the folders of the
                // plugins that are already uninstalled.
                if (is_null($pluginfo)) {
                    throw new moodle_exception('err_uninstalling_unknown_plugin', 'core_plugin', '', array('plugin' => $uninstall),
                        'core_plugin_manager::get_plugin_info() returned null for the plugin to be uninstalled');
                }
                $pluginname = $pluginman->plugin_name($pluginfo->component);
                $progress = new progress_trace_buffer(new text_progress_trace(), false);
                echo $OUTPUT->heading(trim($pieces[1]));
                $pluginman->uninstall_plugin($pluginfo->component, $progress);
                $progress->finished();
                echo $OUTPUT->notification(get_string('success'), 'notifysuccess');
                if ($options['deletefiles'] != false) {
                    delete_folder_dir("$CFG->dirroot/$pieces[0]/".trim($pieces[1]));
                }
                echo "$pieces[0]/$pieces[1] was sucssessfully removed\n";
                break;
            case 'enrol':
            case 'block':
            case 'local':
            case 'report':
            case 'theme':
            case 'format':
                uninstall_plugin($pieces[0], trim($pieces[1]));

                // Some plugins, by mistake, writes its settings with the [type]_[plugin-folder]/[setting] structure
                // instead of [plugin-folder]/[setting]. This uninstall function only delete setting into the
                // mdl_config_plugins table, so we force deleting also from mdl_config.
                unset_all_config_for_plugin(trim($pieces[1]));

                if ($options['deletefiles'] != false) {
                    delete_folder_dir("$CFG->dirroot/$pieces[0]/" . trim($pieces[1]));
                }
                echo "$pieces[0]/$pieces[1] was sucssessfully removed\n";
                break;
            default:
                echo "ignorada la linea $linenum (no cumple ningun match) = $pieces[0]\n";
                break;
        }
        $linenum++;
    }
    fclose($fp);
} else {
    echo "No existe el fichero";
}

function delete_folder_dir($carpeta) {
    try {
        foreach (glob($carpeta . "/*") as $folder_files) {
            echo $folder_files . "\n";

            if (is_dir($folder_files)) {
                delete_folder_dir($folder_files);
            } else {
                unlink($folder_files);
            }
        }
        rmdir($carpeta);
    } catch (Exception $exc) {
        echo "no existe la carpeta $carpeta";
    }
}
