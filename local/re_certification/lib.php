<?PHP
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

require_once($CFG->libdir.'/enrollib.php');

function get_course_certperiod($course) {
	global $DB;
        $course_info_field =  $DB->get_record_sql("Select id from {course_info_field} Where shortname = ? ", array('certperiod'));     
        $fieldid = $course_info_field->id;
        $course_info_data =  $DB->get_record_sql("Select data from {course_info_data} Where fieldid = ?  and courseid = ? ", array($fieldid,$course));
        $certperiod = $course_info_data->data;
	return $certperiod;
}

function get_course_completiondate($course) {
	global $DB;
	$complePeriod = $DB->get_field('course_completions','timecompleted', array('course' => $course));
	return $complePeriod;
}

function get_enrol_by_id($id) {
	global $DB;
	
	$enrol = $DB->get_record('enrol', array('id' => $id));
	
	return $enrol;
}


