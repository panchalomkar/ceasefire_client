<?php

class blocks_rlms_notifications_observer
{

    /**
     * Triggered when 'course_completed' event is triggered.
     *
     * @param \core\event\course_completed $event
     */
    public static function notify_course_completed(\core\event\course_completed $event)
    {
        global $DB, $CFG, $USER;
        
        require_once($CFG->dirroot . '/user/profile/lib.php');
        require_once($CFG->dirroot . '/user/lib.php');

        $userid = $event->relateduserid;
        $courseid = $event->courseid;
        
        // check if the notification is enabled
        $sql = "SELECT 
        s.*
        ,n.name
        FROM {block_rlms_ntf_settings} AS s
        INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id
        WHERE s.enabled = 1 AND n.type = 'event' AND course_id = {$courseid}";
        
        $records = $DB->get_records_sql($sql);
        foreach($records as $row)
        {
            $settingsId = $row->id;
            
            if('notify_on_course_completion_student' == $row->name)
            {
                // get the user information
                $sql = "SELECT * FROM {user} WHERE id = {$userid}";
                $record = $DB->get_record_sql($sql);
                $user = $record;
                $email = $record->email;
                
                // get course information
                $sql = "SELECT * FROM {course} WHERE id = {$courseid}";
                $record = $DB->get_record_sql($sql);
                
                $courseName = ($record->fullname) ? $record->fullname : $record->shortname;
                
                $mail = get_mailer();
                $supportuser = core_user::get_support_user();
                
                $mail->Sender = $supportuser->email;
                $mail->From = $supportuser->email;
                
                $mail->Subject = get_string($row->name, 'block_rlms_notifications');
                                
                $mail->isHTML(true);
                $mail->Encoding = 'quoted-printable';
                $mail->Body =  $row->template;
                
                $mail->AddAddress($email);
                $mail->send();
                
                rlms_notifications_log($settingsId, 1);
                message_post_message($USER, $user, $mail->Body, FORMAT_MOODLE);
            }
            elseif('notify_on_course_completion_teacher' == $row->name)
            {
                // get the list of teachers for the given course
                $sql = "
                SELECT 
                    u.id
                    ,u.firstname
                    ,u.lastname
                    ,email
                FROM mdl_course c
                JOIN mdl_context ct ON c.id = ct.instanceid
                JOIN mdl_role_assignments ra ON ra.contextid = ct.id
                JOIN mdl_user u ON u.id = ra.userid
                JOIN mdl_role r ON r.id = ra.roleid
                WHERE 
                    r.shortname IN('editingteacher', 'teacher')
                AND c.id = '{$record->course_id}'";
                
                $to = array();
                $records2 = $DB->get_records_sql($sql);
                foreach($records2 as $record2)
                {
                    $to[] = $record2->email;
                    message_post_message($USER, $record2, $row->template, FORMAT_MOODLE);
                }
                
                $mail = get_mailer();
                $supportuser = core_user::get_support_user();
                
                $mail->Sender = $supportuser->email;
                $mail->From = $supportuser->email;
                
                $mail->Subject = get_string($row->name, 'block_rlms_notifications');
                                
                $mail->isHTML(true);
                $mail->Encoding = 'quoted-printable';
                $mail->Body =  $row->template;
                
                if(count($to))
                {
                    foreach ($to as $email)
                        $mail->AddAddress($email);
                
                    $mail->send();
                }
                
                rlms_notifications_log($settingsId, 1);
            }
        }
    }
}
