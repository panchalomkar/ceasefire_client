<?php
require_once('../../config.php');
global $DB;
$dbtype = 'mariadb';
$dblibrary = 'native';
$database = 'oppo_production';
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = 'root@123';
$dbprefix = 'mdl_';
$dboptions = array(
    'dbpersist' => 0,
    'dbport' => 3308,
    'dbsocket' => '',
    'dbcollation' => 'utf8mb4_unicode_ci',
);
$remotedb = moodle_database::get_driver_instance($dbtype, $dblibrary);
try {
    $remotedb->connect($dbhost, $dbuser, $dbpass, $database, $dbprefix, $dboptions);
    //print_object($remotedb);
} 
catch (moodle_exception $e) {
    print_object($e->getMessage());
}