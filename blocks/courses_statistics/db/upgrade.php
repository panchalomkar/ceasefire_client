<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_block_courses_statistics_upgrade($oldversion) {
    global $DB, $CFG;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018092002) {
       
        
        $table = new xmldb_table('logstore_standard_log');
        $index = new xmldb_index('idx_eventname', XMLDB_INDEX_NOTUNIQUE, array('eventname','action','target'));
        
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
       // $dbman->rename_table($table, 'block_configurable_reports');
        upgrade_plugin_savepoint(true, 2018092002, 'block', 'courses_statistics');
    }    

}   