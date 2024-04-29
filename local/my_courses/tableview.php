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
 * Library functions.
 *
 * @package    report_custom_report
 * @author     Uvais
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */
require_once 'locallib.php';

class tableview extends table_sql {

    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    function __construct($uniqueid) {
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('coursename', 'startdate', 'department', 'coursestatus');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(
            get_string('coursename', LANGFILE),
            get_string('startdate', LANGFILE),
            get_string('department', LANGFILE),
            get_string('status', LANGFILE)
        );
        $this->define_headers($headers);
        $this->set_attribute('class', 'generaltable generalbox datatable');
    }

    /**
     * This function is called for each data row to allow processing of the
     * username value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return username with link to profile or username only
     *     when downloading.
     */
    function col_startdate($values) {
        global $CFG;
        if ($values->startdate > 0) {
            return date('d-m-Y H:i', $values->startdate);
        }
        return null;
    }

    function col_coursename($values) {
        global $CFG;
        return '<a href="' . $CFG->wwwroot . '/course/view.php?id=' . $values->id . '">' . $values->coursename . '</a>';
    }

    function col_coursestatus($values) {
        global $CFG;
        if ($values->timecompleted > 0) {
            return get_string('completed', LANGFILE);
        } if ($values->timestarted > 0) {
            return get_string('inprogress', LANGFILE);
        } else {
            return get_string('notstarted', LANGFILE);
        }
    }

    /**
     * This function is called for each data row to allow processing of
     * columns which do not have a *_cols function.
     * @return string return processed value. Return NULL if no change has
     *     been made.
     */
    function other_cols($colname, $value) {
        // For security reasons we don't want to show the password hash.
        return null;
    }

    function display_report_list($department = '') {
        global $DB, $USER;
        
        $condition = '';
        if ($department != '') {
            $condition = " AND c.department = '$department' ";
        } else if (!is_landmanager() && !is_siteadmin()) {

            //get current user department
            $userdepartment = get_current_user_department();
            $condition = " AND c.department = '$userdepartment' ";
        }

        $fields = "c.id, c.fullname AS coursename, c.startdate, c.department, '' AS coursestatus,
                 ccm.timecompleted, ccm.timestarted";
        $from = "{course} c LEFT JOIN {course_completions} ccm ON ccm.course = c.id AND ccm.id = $USER->id";
        $where = "c.category > 0 $condition";
        $this->set_sql($fields, $from, $where);
    }

}
