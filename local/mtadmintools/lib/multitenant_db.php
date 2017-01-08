<?php

require_once(__DIR__.'/../../../config.php');

class multi_tenant_db_manager {
    private $originaldb;
    private $alltenants;
    private $basedomainunderscore;
    
    function __construct($mtunderscoredomain='tenant1_mydomain_com') {
        global $CFG;
        $this->originaldb = $CFG->dbname;
        $this->basedomainunderscore = $this->extract_subdomain($mtunderscoredomain);
        $this->obtain_all_tenants();
    }
    
    private function obtain_all_tenants(){
        global $DB;
        $obj = $DB->get_records_sql("show databases;");
        
        $tenants = array();
        foreach ($obj as $value) {
            $ind = core_text::strpos($value->database,$this->basedomainunderscore);
            if ($ind !== false && (core_text::substr($value->database,$ind) == $this->basedomainunderscore)) {
                $this->new_db_connection($value->database);
                $authrow = $DB->get_record('config', array('name'=>'auth'));
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
        $DB->connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $dbname, $CFG->prefix,$CFG->dboptions);
    }
    
    public function get_alltenants_dbs(){
        return $this->alltenants;
    }
    
    public function restore_originaldb(){
        $this->new_db_connection($this->originaldb);
    }
    
    public function extract_subdomain($mtunderscoredomain) {
        $firstind = core_text::strpos($mtunderscoredomain,'_');
        $secondind = core_text::strpos($mtunderscoredomain,'_',$firstind+1);
        if (($secondind !== false && $secondind > ($firstind+1)) || ($firstind !== false && !$secondind)) {
            return core_text::substr($mtunderscoredomain,$firstind+1);
        }
        return $this->extract_subdomain(substr($mtunderscoredomain,$firstind+1));
    }
}