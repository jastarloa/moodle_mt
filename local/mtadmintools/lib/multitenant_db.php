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
 * Class for switching DBs from a specific tenant.
 *
 * @package    local_mtadmintools
 * @copyright  2017
 * @author Manu Peño
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * To import into each plugin for multi-tenant management purpouses which need to switch on the DB schemes associated
 * with tenants based on a given base domain.
 */
class multi_tenant_db_manager {
    private $originaldb;
    private $alltenants;
    private $basedomainunderscore;

    public function __construct($mtunderscoredomain='tenant1_mydomain_com') {
        global $CFG;
        $this->originaldb = $CFG->dbname;
        $this->basedomainunderscore = $this->extract_subdomain($mtunderscoredomain);
        $this->obtain_all_tenants();
    }

    private function obtain_all_tenants() {
        global $DB;
        $obj = $DB->get_records_sql("show databases;");

        $tenants = array();
        foreach ($obj as $value) {
            $ind = core_text::strpos($value->database, $this->basedomainunderscore);
            if ($ind !== false && (core_text::substr($value->database, $ind) == $this->basedomainunderscore)) {
                $this->new_db_connection($value->database);
                $authrow = $DB->get_record('config', array('name' => 'auth'));
                if ($authrow !== false && !empty($authrow)) {
                    array_push($tenants, $value->database);
                }
            }
        }
        $this->alltenants = $tenants;
    }

    public function new_db_connection($dbname) {
        global $CFG, $DB;
        $CFG->dbname = "{$dbname}";
        $DB->connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $dbname, $CFG->prefix, $CFG->dboptions);
    }

    public function get_alltenants_dbs() {
        return $this->alltenants;
    }

    public function restore_originaldb() {
        $this->new_db_connection($this->originaldb);
    }

    public function extract_subdomain($mtunderscoredomain) {
        $firstind = core_text::strpos($mtunderscoredomain, '_');
        $secondind = core_text::strpos($mtunderscoredomain, '_', $firstind + 1);
        if (($secondind !== false && $secondind > ($firstind + 1)) || ($firstind !== false && !$secondind)) {
            return core_text::substr($mtunderscoredomain, $firstind + 1);
        }
        return $this->extract_subdomain(substr($mtunderscoredomain, $firstind + 1));
    }
}