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
 * mtadmintools renderers.
 *
 * @package    local_mtadmintools
 * @copyright  2015
 * @author Manu Peño
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Renderer for displaying charts.
 *
 * @copyright  2015
 * @author Manu Peño
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_mtadmintools_renderer extends plugin_renderer_base {

    /**
     * HTML piece for import the Google Chart loader
     * @return string HTML to output.
     */
    public function get_google_charts() {
        $importjscript = '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
        return $importjscript;
    }

    public function get_charts_data() {
        global $CFG;

        $datascript = '<script type="text/javascript">';
        $datascript .= "mt_wsurl = '" . $CFG->wwwroot . "/local/mtadmintools/ws/ajax.php';";
        $datascript .= "mt_filesurl = '" . $CFG->wwwroot . "/local/mtadmintools/ws/file.php';";
        $datascript .= "chart_disk_opts = " .
            "{'title':'" . get_string('chart_disk_title', 'local_mtadmintools') . "', " .
            "'width':300, " .
            "'height':200, " .
            "pieHole:0.25};";
        $datascript .= "chart_active_users_opts = " .
            "{'title':'" . get_string('chart_active_users_title', 'local_mtadmintools') . "', " .
            "'width':300, " .
            "'height':200, " .
            "pieHole:0.25, " .
            "legend:{position:'none'}, " .
            "isStacked: 'percent'};";
        $datascript .= "chart_history_opts = { " .
            "title : '" . get_string('chart_history_title', 'local_mtadmintools',
                local_mtadmintools\task\calc_bill::$billhistmonths) . "', " .
            "vAxis: {title: '" . get_string('charge', 'local_mtadmintools') . "',viewWindow:{min:0}}, " .
            "hAxis: {title: '" . get_string('month') . "'}, " .
            "legend:{position:'none'} " .
            "};";
        $datascript .= "usersstr = '" . core_text::strtolower(get_string('users')) . "';";
        $datascript .= "userstr = '" . core_text::strtolower(get_string('user')) . "';";
        $datascript .= '</script>';
        return $datascript;
    }

    public function get_adminpage_data() {
        global $CFG;

        $datascript = '<script type="text/javascript">';
        $datascript .= "mt_wsurl = '" . $CFG->wwwroot . "/local/mtadmintools/ws/ajax.php';";
        $datascript .= "mt_filesurl = '" . $CFG->wwwroot . "/local/mtadmintools/ws/file.php';";
        $datascript .= '</script>';
        return $datascript;
    }
}
