<?php

function xmldb_block_rlms_notifications_install() {
    global $DB;
    $dbman = $DB->get_manager();
    $records = array(
        array(
            'name' => 'notify_on_course_completion_student'
            , 'template' => 'NO TEMPLATE YET'
            , 'config' => ''
        )
        , array(
            'name' => 'notify_on_course_completion_teacher'
            , 'template' => 'NO TEMPLATE YET'
            , 'config' => ''
        )
        , array(
            'name' => 'notify_user_not_logged_in'
            , 'template' => 'NO TEMPLATE YET'
            , 'config' => '{"days_after_enrolled":3}'
        )
        , array(
            'name' => 'notify_student_before_course_expiration'
            , 'template' => 'NO TEMPLATE YET'
            , 'config' => '{"days_before_expiration":3}'
        )
    );

    try {
        foreach ($records as $record) {
            $DB->insert_record('block_rlms_ntf', $record);
        }
    } catch (exception $e) {
        
    }

    // Version 2015122908
    $record = new stdClass();
    $record->name = 'notify_on_course_enroll_student';
    $record->template = 'NO TEMPLATE YET';
    $record->config = '{"notify_email_subject":"Enroll Notification: {coursename}"}';
    $record->type = 'cron';

    /**
     * Add extra validation if the record already exist dont insert
     * @author Esteban E
     * @since April 08 of 2016
     * @rlms
     */
    $block_rlms_ntf = $DB->get_record_sql('SELECT * FROM {block_rlms_ntf} WHERE name = ?', array($record->name));
    if (!$block_rlms_ntf) {
        $DB->insert_record('block_rlms_ntf', $record);
    }

    // Version 2017050801
    $table = new xmldb_table('block_rlms_ntf_log');
    $filters = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED);
    $filters2 = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED);

    // Add field if it doesn't already exist.
    if (!$dbman->field_exists($table, $filters)) {
        $dbman->add_field($table, $filters);
    }

    // Add field if it doesn't already exist.
    if (!$dbman->field_exists($table, $filters2)) {
        $dbman->add_field($table, $filters2);
    }

    // Version 2017060901
    $record = new stdClass();
    $record->name = 'notify_on_course_completion_teacher';
    $record->template = 'NO TEMPLATE YET';
    $record->config = '{"notify_email_subject":"Course completion: {coursename}"}';
    $record->type = 'cron';

    $block_rlms_ntf = $DB->get_record_sql('SELECT * FROM {block_rlms_ntf} WHERE name = ?', array($record->name));
    if (!$block_rlms_ntf) {
        $lastinsertid = $DB->insert_record('block_rlms_ntf', $record);
    } else {
        $record->id = $block_rlms_ntf->id;
        $lastinsertid = $DB->update_record('block_rlms_ntf', $record);
    }
    
    // Version 2018032302
    
    $table = new xmldb_table('block_rlms_ntf');

    $record1 = new stdClass();
    $record1->name = 'notify_on_course_incomplete';
    $record1->template = 'NO TEMPLATE YET';
    $record1->config = '{"notify_email_subject":"Course incomplete: {course_name}" , "days_after_incomplete":3}';
    $record1->type = 'cron';

    $record2 = new stdClass();
    $record2->name = 'notify_on_course_overdue';
    $record2->template = 'NO TEMPLATE YET';
    $record2->config = '{"notify_email_subject":"Course overdue: {course_name}"}';
    $record2->type = 'cron';

    $record3 = new stdClass();
    $record3->name = 'notify_on_quiz_reminder';
    $record3->template = 'NO TEMPLATE YET';
    $record3->config = '{"notify_email_subject":"Quiz reminder on {course_name}", "days_before_reminder":3}';
    $record3->type = 'cron';

    $records = array($record1, $record2, $record3);
    foreach ($records as $record) {
        $block_rlms_ntf = $DB->get_record_sql('SELECT * FROM {block_rlms_ntf} WHERE name = ?', array($record->name));
        if (!$block_rlms_ntf) {
            $lastinsertid = $DB->insert_record('block_rlms_ntf', $record);
        } else {
            $record->id = $block_rlms_ntf->id;
            $lastinsertid = $DB->update_record('block_rlms_ntf', $record);
        }
    }

    return true;
}
