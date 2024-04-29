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
 * Local helper functions.
 *
 * @package    local_my_calendar
 * @author     Uvais
 * @copyright  2012 Isuru Madushanka Weerarathna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once 'lib.php';

class local_my_calendar {

    /**
     * Array of colunms to be displayed in table.
     *
     * @var array
     */
    public function get_event($id) {
        global $DB;
        $SQL = "SELECT e.id, e.name, e.description, e.timestart, e.instance, e.modulename, e.courseid, c.fullname 
            FROM mdl_event e
                INNER JOIN mdl_course c ON c.id = e.courseid
                WHERE e.id = $id";
        return $DB->get_record_sql($SQL);
    }

    public function my_calendar($department) {
        global $DB, $USER;

        if (is_siteadmin()) {
            $condition = '';
        } else {
            $my_courses = enrol_get_users_courses($USER->id);
            $temp = array(1);
            foreach ($my_courses as $course) {
                $temp[] = $course->id;
            }
            $courseids = implode(',', $temp);
            $condition = " AND (userid = $USER->id || FIND_IN_SET(courseid, '$courseids'))";
        }

        $department_course = array();
        if ($department != '') {
            $SQL = "SELECT id FROM {course} WHERE department = '$department'";
            $crecords = $DB->get_records_sql($SQL);
            foreach ($crecords as $val) {
                $department_course[] = $val->id;
            }
            $courseids = implode(',', $department_course);
            $condition = " AND (FIND_IN_SET(courseid, '$courseids'))";
        }
        
         $SQL = "SELECT id, DATE_FORMAT(FROM_UNIXTIME(timestart), '%Y-%m-%d') AS start,
            DATE_FORMAT(FROM_UNIXTIME(timestart), '%Y-%m-%d') AS end,
               name AS title,
              '#3CB371' AS color FROM mdl_event
              WHERE 1=1 $condition";
        return $DB->get_records_sql($SQL);
    }

}
