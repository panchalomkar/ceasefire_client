<?php

require_once($CFG->dirroot.'/blocks/rlms_notifications/classes/notify_on_course_completion_student.php');

class notify_on_course_completion_teacher extends notify_on_course_completion_student {

    function run($record) {
    	parent::run($record);
    }
}