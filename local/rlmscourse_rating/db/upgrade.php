<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_rlmscourse_rating_upgrade($oldversion = 0) {
     if ($oldversion < 2019071700) {
        // Code here
        upgrade_plugin_savepoint(true, 2019071700, 'local', 'rlmscourse_rating');
    }
    
}

