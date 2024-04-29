<?php

$string['pluginname'] = 'Course Notification';
$string['notifications'] = 'Course Notification';
$string['enabled'] = 'Enabled';
$string['template'] = 'Email template';
$string['last_notifications'] = 'Latest notifications';

$string['notify_on_course_completion_student'] = 'Course Completion Notification to Student';
$string['notify_on_course_completion_student_desc'] = '';

$string['notify_on_course_completion_teacher'] = 'Course Completion Notification to Teacher';
$string['notify_on_course_completion_teacher_desc'] = '';

$string['notify_user_not_logged_in'] = 'Enrollment Reminder Notification to Student';
$string['notify_user_not_logged_in_desc'] = '';
$string['days_after_enrolled'] = 'Days after Enrollment';

$string['notify_student_before_course_expiration'] = 'Course Expiration Notification to Student';
$string['notify_student_before_course_expiration_desc'] = '';
$string['days_before_expiration'] = 'Days Before Course Expires';
$string['days_before_enrollment_expiration'] = 'Days Before Enrollment Expires';

/*Course Incomplete*/
$string['course_incomplete'] = 'Course incomplete: ';
$string['notify_on_course_incomplete'] = 'Notify when Course is incomplete';
$string['days_after_incomplete'] = 'Days after incomplete';
$string['days_after_incomplete_help'] = 'Number of days to notify after a course in incomplete';
$string['check_enable_notifi_notify_on_course_incomplete'] = 'Notifications when a course is incomplete.';
$string['check_enable_notifi_notify_on_course_incomplete_help'] = 'Enable notifications when a course is incomplete.';
$string['text_input_notify_on_course_incomplete_help'] = 'Subjet of email to be sent when a course is incomplete';
$string['email_template_notify_notify_on_course_incomplete'] = 'Template of the body message to be sent.';
$string['email_template_notify_notify_on_course_incomplete_help'] = 'You can create your own notification template that will be sent when the course is incomplete:
													<br/><br/>You can also use the following tags, in your template.
													<br/>User Full Name : <strong>{fullname}</strong>
													<br/>Course Name: <strong>{coursename}</strong>
													<br/>Course Link: <strong>{link}</strong>';
$string['days_after_reminder'] = 'Days after reminder.';
$string['days_before_reminder'] = 'Days before reminder.';
$string['days_after_reminder_help'] = 'Number of days before a Quiz is going to be closed.';
$string['notify_on_course_overdue'] = 'Notify when Course is overdue';
$string['notify_on_quiz_reminder'] = 'Quiz reminder';

/*Overdue*/
$string['check_enable_notifi_notify_on_course_overdue'] = 'Notifications when a course is overdue.';
$string['check_enable_notifi_notify_on_course_overdue_help'] = 'Enable notifications when a course is overdue.';

/*Quiz reminder*/
$string['check_enable_notifi_notify_on_quiz_reminder'] = 'Notifications on quiz reminder.';
$string['check_enable_notifi_notify_on_quiz_reminder_help'] = 'Enable notifications on quiz reminder.';

/*User not logged in*/
$string['check_enable_notifi_notify_user_not_logged_in'] = 'Notifications on user inactivity.';

/**
 * @description: New block was added to handle the notification when a user its enrolled
 * in a course.
 * @author Esteban E.
 * @since Dic 29 of 2015
 * @rlms
 */

$string['notify_on_course_enroll_student'] = 'Course Enroll Notification to Student ';
$string['notify_on_course_enroll_student_desc'] = '';

$string['email_template_notify_notify_on_course_enroll_student'] = 'Email notification template' ;
$string['email_template_notify_notify_on_course_enroll_student_help'] = 'You can create your own notification template that will be sent to the user enrolled in this course:
													<br/><br/>You can also use the following tags, in your template.
													<br/>User Full Name : <strong>{fullname}</strong>
													<br/>Course Name: <strong>{coursename}</strong>
													<br/>Course Link: <strong>{link}</strong>' ;

$string['email_template_notify_notify_on_course_overdue'] = 'Template when a course is overdue' ;
$string['email_template_notify_notify_on_course_overdue_help'] = 'You can create your own notification template that will be sent to the user when the course is overdue:
													<br/><br/>You can also use the following tags, in your template.
													<br/>User Full Name : <strong>{fullname}</strong>
													<br/>Course Name: <strong>{coursename}</strong>
													<br/>Course Link: <strong>{link}</strong>' ;

$string['email_template_notify_notify_on_quiz_reminder'] = 'Template on quiz reminder' ;
$string['email_template_notify_notify_on_quiz_reminder_help'] = 'You can create your own notification template that will be sent to the user on quiz reminder:
													<br/><br/>You can also use the following tags, in your template.
													<br/>User Full Name : <strong>{fullname}</strong>
													<br/>Course Name: <strong>{coursename}</strong>
													<br/>Course Link: <strong>{link}</strong>' ;

$string['notify_email_subject']= 'Email notification subject';
$string['edit_template_notification'] ='Edit templates';

$string['email_template_notify_notify_on_course_completion_student'] = 'Course completion template to student.' ;
$string['email_template_notify_notify_on_course_completion_student_help'] ='Here you can create your own email template to be sent to the student when end the course.
<br/><br/>You can also use the following tags, in your template.
													<br/>User Full Name : <strong>{fullname}</strong>
													<br/>Course Name: <strong>{coursename}</strong>
													<br/>Course Link: <strong>{link}</strong>';

$string['email_template_notify_notify_on_course_completion_teacher'] = 'Course completion template to teacher' ;
$string['email_template_notify_notify_on_course_completion_teacher_help'] ='Here you can create your own email template to be sent to the teacher when a user end the course.
<br/><br/>You can also use the following tags, in your template.
													<br/>User Full Name : <strong>{fullname}</strong>
													<br/>Course Name: <strong>{coursename}</strong>
													<br/>Course Link: <strong>{link}</strong>';

$string['email_template_notify_notify_user_not_logged_in'] = 'Inactivity notification template.' ;
$string['email_template_notify_notify_user_not_logged_in_help'] ='Here you can create your own email template to be sent to the user for inactivity.
<br/><br/>You can also use the following tags, in your template.
													<br/>User Full Name : <strong>{fullname}</strong>
													<br/>Course Name: <strong>{coursename}</strong>
													<br/>Course Link: <strong>{link}</strong>';

$string['email_template_notify_notify_student_before_course_expiration'] = 'Course expiration template.' ;
$string['email_template_notify_notify_student_before_course_expiration_help'] ="Here you can create your own email template to be sent to the user when a course it's about to expire.
<br/><br/>You can also use the following tags, in your template.
													<br/>User Full Name : <strong>{fullname}</strong>
													<br/>Course Name: <strong>{coursename}</strong>
													<br/>Course Link: <strong>{link}</strong>";

$string['check_enable_notifi_notify_on_course_completion_student'] = 'Course completion notification to student.' ;
$string['check_enable_notifi_notify_on_course_completion_teacher'] = 'Course completion notification to teacher.' ;
$string['check_enable_notifi_notify_on_course_enroll_student'] = 'Inactivity notification.' ;
$string['check_enable_notifi_notify_student_before_course_expiration'] = 'Course expiration notification.' ;
$string['check_enable_notify_on_course_enroll_student'] = 'Course Enroll notification.' ;

$string['check_enable_notifi_notify_on_course_completion_student_help'] = 'Enable Course completion notification to student.' ;
$string['check_enable_notifi_notify_on_course_completion_teacher_help'] = 'Enable Course completion notification to teacher.' ;
$string['check_enable_notifi_notify_user_not_logged_in_help'] = 'Enable Enrollment reminder notification to student.<br/>If the user login at least one time, this notification will be inactive' ;
$string['check_enable_notifi_notify_student_before_course_expiration_help'] = 'Enable Course expiration notification.' ;
$string['check_enable_notifi_notify_on_course_enroll_student_help'] = 'Enable Course Enroll notification to user.' ;

$string['text_input_notify_on_course_enroll_student'] = 'Inactivity notification.' ;
$string['text_input_notify_student_before_course_expiration'] = 'Expiration course notification.' ;
$string['text_input_notify_on_course_enroll_student'] = 'Enroll course notification.' ;

$string['text_input_notify_user_not_logged_in_help'] = 'This will be the number of days that the system will wait after send the reminder to student.' ;
$string['text_input_notify_user_not_logged_in'] = 'Days after Enrollment.' ;
$string['text_input_notify_student_before_course_expiration_help'] = 'This will set the number of days before the course expire to send the notification. ' ;
$string['text_input_notify_on_course_enroll_student_help'] = 'This will set the subject to the email that will be sent to the user enrolled.' ;


$string['default_template_notify_on_course_enroll_student']='Hi: <strong>{fullname}</strong>, <br/> You are enrolled in Course <strong>{coursename}</strong>.<br />link: <strong>{link}</strong>';
$string['templates']='Templates';
$string['back_to_course'] = 'Back to course';


/**
 * @description: Default templates for notifications
 * in a course.
 * @author Esteban E.
 * @since March 31 of 2016
 * @rlms
 */

$string['default_template_notify_on_course_completion_student'] =
"Hello <strong>{fullname}</strong>,<br /> 
You have successfully completed the course: <strong>{coursename}</strong>.<br />
Course Link<strong>{link}</strong>.<br />
Thank you for participating in the course !";

$string['default_template_notify_on_course_completion_teacher'] =
"Hello,<br />  
User <strong>{fullname}</strong> has successfully completed the course <strong>{coursename}</strong>.<br /> 
Link for the course is <strong>{link}</strong>.<br />" ;

$string['default_template_notify_on_course_enroll_student'] =
"Hello <strong>{fullname}</strong>,<br /> 
You have been enrolled to the course <strong>{coursename}</strong>.<br />
Please click on this link to complete the course  <strong>{link}</strong>.<br />
Thank you for participating in this course !";

$string['default_template_notify_student_before_course_expiration'] = 
"Hello <strong>{fullname}</strong>,<br /> 
You have been enrolled into the course <strong>{coursename}</strong>. Please complete the course before it expires.<br />
Link for the course is <strong>{link}</strong>.<br />
Thank you very much.";

$string['default_template_notify_user_not_logged_in'] = 
"Hello <strong>{fullname}</strong>,<br /> 
This is the reminder about your enrollment into the course: <strong>{coursename}</strong>.<br />
Course link: <strong>{link}</strong>.<br />
Thank you.";

$string['default_template_notify_on_course_overdue'] = 
"Hello <strong>{user_fullname}</strong>,<br /> 
Your course <strong>{course_fullname}</strong> is overdue.";
$string['default_template_notify_on_quiz_reminder'] = 
"Hello <strong>{user_fullname}</strong>,<br /> 
This is the reminder about your quiz {quiz_name} into the course: <strong>{course_fullname}</strong>.<br />
Course link: <strong>{course_link}</strong>.<br />
Thank you.";
$string['default_template_notify_on_course_incomplete'] = 
"Hello <strong>{user_fullname}</strong>,<br /> 
Your course <strong>{course_fullname}</strong> is incomplete.<br />
Course link: <strong>{course_link}</strong>.<br />Thank you.";
$string['sys_not_installed'] = 'The plugin System notifications is not installed.';
$string['notify_on_enrollment_expire'] = 'Enrollment Expiration Notification to Student';
$string['default_template_notify_on_enrollment_expire'] = 
"Hello <strong>{user_fullname}</strong>,<br /> 
Your enrollment for a course <strong>{course_fullname}</strong> will be expire in {days} days.<br />
Course link: <strong>{course_link}</strong>.<br />Thank you.";