<?php

require_once ('../../config.php');
require_once ($CFG->dirroot . '/my/lib.php');
require_once ($CFG->dirroot . '/tag/lib.php');
require_once ($CFG->dirroot . '/user/profile/lib.php');
require_once ($CFG->libdir . '/filelib.php');
//require_once ($CFG->dirroot . '/user/profile/field/multiselect/field.class.php');
require_once ($CFG->dirroot . '/grade/querylib.php');
global $COURSE, $USER, $CFG, $DB, $OUTPUT, $PAGE;

require_login();

//get id user from url
$uid = optional_param('uid','', PARAM_INT);

//get user object
$user = $user = $DB ->get_record('user', array('id' => $uid));

$context = context_system::instance();

$PAGE->set_url(new moodle_url('/blocks/rlms_myprogress/list_notstarted.php'));
$PAGE->set_context($context);

// base, standard, course, mydashboard
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('enrolledcourses', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));
$PAGE->set_heading(get_string('enrolledcourses', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));
$PAGE->requires->css(new moodle_url('/blocks/rlms_myprogress/css/my_progress.css'));
$PAGE->requires->js(new moodle_url('/blocks/rlms_myprogress/js/common.js'));
$PAGE->navbar->add(get_string('enrolledcourses', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('enrolledcourses', 'block_rlms_myprogress', ($user->firstname.' '.$user->lastname)));

//Build the table with the list of users
$table = new html_table();
$table ->head = array(
		get_string('coursefullname', 'block_rlms_myprogress'), 
		get_string('enrollmentddate', 'block_rlms_myprogress')
	   );

$userid = $uid ;
if(!$userid) $userid = $USER->id ;
//get courses enrolled for this user


$sql = "SELECT c.id as courseid , DATE_FORMAT(FROM_UNIXTIME(ue.timecreated), '%Y-%m-%d') as user_enrolment_date 
	FROM {course} as c 
	INNER JOIN {enrol} as e ON c.id = e.courseid 
	INNER JOIN {user_enrolments} as ue ON e.id = ue.enrolid 
	INNER JOIN {user} as u ON ue.userid = u.id WHERE u.id = ".$userid ." AND c.visible=1 " ;
/*
	$sql ="SELECT attr_e.courseid , DATE_FORMAT(FROM_UNIXTIME(attr_ue.timecreated), '%Y-%m-%d') as user_enrolment_date 
			FROM {user_enrolments} as attr_ue 
			JOIN {enrol} as attr_e ON attr_ue.enrolid = attr_e.id AND attr_ue.status = ".ENROL_USER_ACTIVE." AND attr_e.status = ".ENROL_INSTANCE_ENABLED." 
			JOIN {course} as attr_c ON attr_e.courseid = attr_c.id AND attr_c.visible = 1 
			WHERE attr_ue.userid =".$userid;*/

	$CEnrolment = $DB ->get_records_sql($sql);



foreach ($CEnrolment as $key => $enrollment ) {
	$data=array();
	$course = $DB ->get_record('course', array('id' => $enrollment->courseid), '*', MUST_EXIST);
	$data[] = html_writer::link(new moodle_url('/course/view.php',array('id'=>$course->id)),$course->fullname,array('target'=>'_blank'));
	$data[] = $enrollment->user_enrolment_date;
	$table->data[] = $data;

	
}
echo html_writer::table($table);
	if ($enrollment == ''){
		echo html_writer::start_tag('div');
			echo html_writer::tag('span', get_string('no_result', 'theme_rlms'));
		echo html_writer::end_tag('div');
	}
echo $OUTPUT->footer();