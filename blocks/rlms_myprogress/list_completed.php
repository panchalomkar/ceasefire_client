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

$PAGE -> set_url(new moodle_url('/blocks/rlms_myprogress/list_completed.php'));
$PAGE -> set_context($context);

// base, standard, course, mydashboard
$PAGE -> set_pagelayout('report');
$PAGE -> set_title(get_string('listcoursecompleted', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));
$PAGE -> set_heading(get_string('listcoursecompleted', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));
$PAGE -> requires -> css(new moodle_url('/blocks/rlms_myprogress/css/my_myprogress.css'));
$PAGE -> requires -> js(new moodle_url('/blocks/rlms_myprogress/js/common.js'));
$PAGE -> navbar -> add(get_string('listcoursecompleted', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));

echo $OUTPUT -> header();

echo $OUTPUT -> heading(get_string('listcoursecompleted', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));

//Build the table with the list of users
$table = new html_table();
$table -> head = array(
									get_string('coursefullname', 'block_rlms_myprogress'), 
									get_string('enrollmentddate', 'block_rlms_myprogress'),
									get_string('timestarted', 'block_rlms_myprogress'),  
									get_string('completiondate','block_rlms_myprogress') 
									/*get_string('finalgrade','block_rlms_myprogress')**/);

//get courses enrolled for this user
$courses = enrol_get_users_courses($uid);

//completed courses array
$completed = array();
$data = array();
foreach ($courses as $course) {

	// Load course.
	$course = $DB -> get_record('course', array('id' => $course -> id), '*', MUST_EXIST);

	// Load completion data.
	$info = new completion_info($course);

	if(!$info->is_enabled())
	continue ;

	// Is course complete?
	if($info -> is_course_complete($uid)){
		// Load course completion.
		$params = array('userid' => $uid, 'course' => $course -> id);
		$completion = new completion_completion($params);
		
		$data = array();
		

		$data[] = html_writer::link(new moodle_url('/course/view.php',array('id'=>$course->id)),$course->fullname,array('target'=>'_blank'));
		$data[] = date('Y-m-d h:i',$completion->timeenrolled);
		$data[] = date('Y-m-d h:i', ( $completion->timestarted ? $completion->timestarted : $course->startdate ) );
		$data[] = date('Y-m-d h:i',$completion->timecompleted);
		//$data[] = round(grade_get_course_grade($uid, $course->id)->grade,2);
		$table->data[] = $data;
	}

}

echo html_writer::table($table);

echo $OUTPUT -> footer();