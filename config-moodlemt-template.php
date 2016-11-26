<?php

// Moodle configuration file (for LINUX systems and MySQL DBs only)

unset($CFG);
global $CFG;
$CFG = new stdClass();

// CODE FOR ALLOW CALLS FROM SHELL
// We must do multi-tenant upgrades from shell
require_once('lib/clilib.php');      // cli only functions
list($options, $unrecognized) = cli_get_params(
    array('help' => false, 'tenant' => ''),
	array('h' => 'help')
);
// We do not allow more protocols than HTTPS
$protoc = 'https';
if (isset($options) && !empty($options)) {
    if (isset($options['tenant']) && $options['tenant']!='') {
        $moodle_host = $options['tenant'];
        $isclicall = true;
    }
}
// CODE FOR ALLOW CALLS FROM WEB
if (!isset($moodle_host)) {
    $moodle_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
}

// MySQL DB names must not have points ('.') nor Hyphen-minus ('-')
$moodle_db = str_replace(".","_",$moodle_host);
$moodle_db = str_replace("-","__",$moodle_db);
$moodle_data_path = "[moodedatas-path]/{$moodle_db}";

/// www. prefix not allowed
if (strpos($moodle_host, 'www.')===0){
	$moodle_host = substr($moodle_host, 4);
	header("Location: ".$protoc."://".$moodle_host);
	die();
}

$CFG->dbtype = 'mysqli';
$CFG->dblibrary = 'native';
$CFG->dbhost = '[db-host]'; // TODO Tratar de que esto recupere una variable de sesion de Apache o de PHP
$CFG->dbname = "{$moodle_db}";
$CFG->dbuser = '[db-user]'; // TODO Tratar de que esto recupere una variable de sesion de Apache o de PHP
$CFG->dbpass = '[db-user-pass]'; // TODO Tratar de que esto recupere una variable de sesion de Apache o de PHP
$CFG->prefix = 'mdl_';
$CFG->dboptions = array(
    'dbpersist' => 0,
    'dbsocket' => 0,
);

$CFG->wwwroot = $protoc."://$moodle_host";
$CFG->dataroot = $moodle_data_path;
$CFG->admin = 'admin';

$CFG->directorypermissions = 0750;
$CFG->disableupdateautodeploy = true;
$CFG->disableupdatenotifications = true;

if (!file_exists($CFG->dataroot)) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1>";
    echo "The page you have requested could not be found. <div style='display:none'>Trz2: " . $CFG->dataroot ."<div>";
    die();
}

require_once(dirname(__FILE__) . '/lib/setup.php');

// There is no php closing tag in this file,
// it is intentional because it prevents trailing whitespace problems!

