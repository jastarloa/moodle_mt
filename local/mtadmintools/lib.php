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
 * @package   local_mtadmintools
 * @copyright 2017
 * @author    Manu Peño
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/mtadmintools/lib/mt_management.php');

/**
 * Remove delicated navigation nodes to non-admin users.
 * @param array $nodeskeyandtype eg.: array(key => integer_node_tye)
 */
function remove_nav_nodes(settings_navigation $settingsnav, array $nodeskeyandtype) {
    foreach ($nodeskeyandtype as $key => $type) {
        $nodesec = $settingsnav->find($key, navigation_node::TYPE_SETTING);
        if ($nodesec) {
            $nodesec->remove();
        }
    }
}

function local_mtadmintools_extend_settings_navigation(settings_navigation $settingsnav, context $context) {
    global $USER, $PAGE, $CFG;

    /* Extends admin menu */
    if ($settingsnav && isloggedin()) {
        $nodeadminmanag = is_siteadmin($USER->id);
        $nodeclientmanag = has_capability('local/mtadmintools:manageclientsettings', context_system::instance()) ||
            has_capability('local/mtadmintools:readclientsettings', context_system::instance());
        $urlclientmanag = new moodle_url('/local/mtadmintools/clientmanag.php');
        $urladminmanag = new moodle_url('/local/mtadmintools/adminmanag.php');
        $settingicon = new pix_icon('t/preferences', '', 'moodle', array('title' => '', 'class' => 'smallicon'));
        $branchroottitle = get_string('mtclientzone', 'local_mtadmintools');

        if ($nodeadminmanag || (isset($nodeclientmanag) && !empty($nodeclientmanag))) {
            $mtnoderoot = $settingsnav->add($branchroottitle, null, $settingsnav::NODETYPE_BRANCH, null, 'mtadminroot');
            if (isset($nodeclientmanag)) {
                $mtclientnode = $mtnoderoot->add(get_string('mtclientaccsettings', 'local_mtadmintools'),
                    $urlclientmanag, $settingsnav::TYPE_CUSTOM, null, 'mtclientmanag', $settingicon);
                if ($PAGE->url->compare($urlclientmanag, URL_MATCH_BASE)) {
                    $mtclientnode->make_active();
                }
                // Add link to purge caches because next 'if' removes the option 'development'.
                $rootsetings = $settingsnav->find('root', navigation_node::TYPE_SITE_ADMIN);
                if ($rootsetings) {
                    $rootsetings->add(get_string('purgecaches', 'admin'), "$CFG->wwwroot/$CFG->admin/purgecaches.php",
                    navigation_node::TYPE_SETTING, null, 'secondpurge', $settingicon);
                }
            }
            if ($nodeadminmanag) {
                $mtadminnode = $mtnoderoot->add(get_string('mtadminmngmnt', 'local_mtadmintools'), $urladminmanag,
                    $settingsnav::TYPE_CUSTOM, null, 'mtadminmanag', $settingicon);
                if ($PAGE->url->compare($urladminmanag, URL_MATCH_BASE)) {
                    $mtadminnode->make_active();
                }
            }
        }
        // Hide some delicate options, only visible for super admins.
        if (!is_siteadmin() && ($hassiteconfig = has_capability('moodle/site:config', context_system::instance())) ) {
            $nodes2remove = array(
                // Gral items.
                'unsupported' => navigation_node::TYPE_SETTING, 'search' => navigation_node::TYPE_SETTING,
                // Devel.
                'development' => navigation_node::TYPE_SETTING,
                // Reports.
                'toolspamcleaner' => navigation_node::TYPE_SETTING, 'toolmonitorrules' => navigation_node::TYPE_SETTING,
                'reportstats' => navigation_node::TYPE_SETTING, 'reportsecurity' => navigation_node::TYPE_SETTING,
                'reportperformance' => navigation_node::TYPE_SETTING,
                'reporteventlists' => navigation_node::TYPE_SETTING,
                // Mnet (if enabled into Advanced opts).
                'mnet' => navigation_node::TYPE_SETTING,
                // Server.
                'systempaths' => navigation_node::TYPE_SETTING, 'sessionhandling' => navigation_node::TYPE_SETTING,
                'stats' => navigation_node::TYPE_SETTING, 'http' => navigation_node::TYPE_SETTING,
                'cleanup' => navigation_node::TYPE_SETTING, 'environment' => navigation_node::TYPE_SETTING,
                'phpinfo' => navigation_node::TYPE_SETTING, 'performance' => navigation_node::TYPE_SETTING,
                'adminregistration' => navigation_node::TYPE_SETTING, 'email' => navigation_node::TYPE_SETTING,
                'scheduledtasks' => navigation_node::TYPE_SETTING,
                // Security.
                'ipblocker' => navigation_node::TYPE_SETTING, 'httpsecurity' => navigation_node::TYPE_SETTING,
                'notifications' => navigation_node::TYPE_SETTING,
                // Appearance.
                'ajax' => navigation_node::TYPE_SETTING,
                // Plugins.
                'pluginsoverview' => navigation_node::TYPE_SETTING, 'tools' => navigation_node::TYPE_SETTING,
                'antivirussettings' => navigation_node::TYPE_SETTING, 'cache' => navigation_node::TYPE_SETTING,
                'localplugins' => navigation_node::TYPE_SETTING, 'logging' => navigation_node::TYPE_SETTING,
                'reportplugins' => navigation_node::TYPE_SETTING, 'searchplugins' => navigation_node::TYPE_SETTING,
                // Notifs, registrs.
                'adminnotifications' => navigation_node::TYPE_SETTING,
                'registrationmoodleorg'  => navigation_node::TYPE_SETTING,
                'registrationhub' => navigation_node::TYPE_SETTING, 'registrationhubs' => navigation_node::TYPE_SETTING,
                'siteregistrationconfirmed' => navigation_node::TYPE_SETTING,
                'upgradesettings' => navigation_node::TYPE_SETTING
            );
            remove_nav_nodes($settingsnav, $nodes2remove);
        }
    }
}