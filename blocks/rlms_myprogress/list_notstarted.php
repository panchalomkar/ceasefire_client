<?php

require_once ('../../config.php');
require_once ($CFG -> dirroot . '/my/lib.php');
require_once ($CFG -> dirroot . '/tag/lib.php');
require_once ($CFG -> dirroot . '/user/profile/lib.php');
require_once ($CFG -> libdir . '/filelib.php');

//require_once ($CFG -> dirroot . '/user/profile/field/multiselect/field.class.php');
require_once ($CFG -> dirroot . '/grade/querylib.php');
global $COURSE, $USER, $CFG, $DB, $OUTPUT, $PAGE;

require_login();

//get id user from url
$uid = required_param('uid', PARAM_INT);

//get user object
$user = $user = $DB -> get_record('user', array('id' => $uid));

$context = context_system::instance();

$PAGE -> set_url(new moodle_url('/blocks/rlms_myprogress/list_notstarted.php'));
$PAGE -> set_context($context);

// base, standard, course, mydashboard
$PAGE -> set_pagelayout('report');
$PAGE -> set_title(get_string('listnotstartedcourses', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));
$PAGE -> set_heading(get_string('listnotstartedcourses', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));
$PAGE -> requires -> css(new moodle_url('/blocks/rlms_myprogress/css/my_progress.css'));
$PAGE -> requires -> js(new moodle_url('/blocks/rlms_myprogress/js/common.js'));
$PAGE -> navbar -> add(get_string('listnotstartedcourses', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));

echo $OUTPUT -> header();

echo $OUTPUT -> heading(get_string('listnotstartedcourses', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));

//Build the table with the list of users
$table = new html_table();
$table -> head = array(
									get_string('coursefullname', 'block_rlms_myprogress'), 
									get_string('enrollmentddate', 'block_rlms_myprogress'));

//get courses enrolled for this user
$courses = enrol_get_users_courses($uid);

//completed courses array
$completed = array();
$data = array();
foreach ($courses as $course) {

	$course = $DB -> get_record('course', array('id' => $course -> id), '*', MUST_EXIST);

	$sql = "SELECT 
		DATE_FORMAT(FROM_UNIXTIME(ue.timecreated), '%Y-%m-%d') as user_enrolment_date 
		FROM {course} as c 
		INNER JOIN {enrol} as e ON c.id = e.courseid 
		INNER JOIN {user_enrolments} as ue ON e.id = ue.enrolid 
		INNER JOIN {user} as u ON ue.userid = u.id WHERE c.id = ".$course->id." and u.id = ".$USER->id;

	$CEnrolment = $DB -> get_record_sql($sql);

	// Load completion data.
	$info = new completion_info($course);
	
	/**
	* Jump course from iteration if course dont have 
	* course completion tracking
	* @author Esteban E. 
	* @since June 28 2017
	* @rlms
	*/

	if(!$info->is_enabled())
	continue ;
	
	// Is course complete?
	$coursecomplete = $info -> is_course_complete($uid);
	
	// Has this user completed any criteria?
	$criteriacomplete = $info -> count_course_user_data($uid);
	
	// Load course completion.
	$params = array('userid' => $uid, 'course' => $course -> id);
	$ccompletion = new completion_completion($params);
	
	//is not the current course net started 
	if(!$criteriacomplete && !$ccompletion->timestarted && !$coursecomplete){
		$data=array();
		$data[] = html_writer::link(new moodle_url('/course/view.php',array('id'=>$course->id)),$course->fullname,array('target'=>'_blank'));
		if($ccompletion->timeenrolled > 0)
		{
		 	$data[] = date('Y-m-d',$ccompletion->timeenrolled);
		}else 
		{
			$data[]=$CEnrolment->user_enrolment_date;
		}
		$table->data[] = $data;
	}
}

echo html_writer::table($table);

echo $OUTPUT -> footer();