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

defined('MOODLE_INTERNAL') || die();

function xmldb_local_team_upgrade($oldversion) {
    global $CFG, $DB;

    $result = true;
    $dbman = $DB->get_manager();
    if ($oldversion < 2019101703) {
            // Define field expirysent to be added to local_vedific_track.
            $table = new xmldb_table('local_team');
            $field = new xmldb_field('cohortcreator', XMLDB_TYPE_INTEGER, '20', null, null, null, null, 'cohortid');
            // Conditionally launch add field expirysent.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            $sql = "SELECT co.*,log.userid 
                    FROM {cohort} AS co
                    INNER JOIN {logstore_standard_log} AS log ON co.id = log.objectid
                    WHERE eventname LIKE '%cohort%' AND action = 'created'";
            $ccreators = $DB->get_records_sql($sql,null);
            $timenow = time();

            foreach ($ccreators as $creator) {
                $trackrecord->id = $creator->id;
                $trackrecord->cohortcreator = $creator->userid;
                $trackrecord->timemodified = $timenow;
                $DB->update_record('local_team', $trackrecord);
            }

            upgrade_plugin_savepoint(true, 2019101703, 'local', 'team');
    }

    return $result;
}
