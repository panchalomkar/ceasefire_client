<?php
/**
 * This is local People plugin file to manage upgrade things
 * 
 * @author Sandeep B
 * @since 26-11-2019
 * @rlms
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_local_people_upgrade($oldversion = 0) {
    global $DB, $CFG;

    $result = true;

    $dbman = $DB->get_manager();

    /**
     * Added new capability
     * 
     * @author Sandeep B
     * @since 26-11-2019
     * @ticket #770
     * 
     */ 
    if ($oldversion < 2019112600) {        

        upgrade_plugin_savepoint(true, 2019112600, 'local', 'people');
    }
    
}