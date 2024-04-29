<?php

class notify_on_course_completion_student {

    var $course;
    var $user;
    var $log;
    var $email;    

    public function __construct() {
        $this->course       = 0;
        $this->user         = 0;
        $this->log          = false;
        // declare if you need receive all emails when execute a test  
        $this->email    = '';        
    }

    function set_course( $course )  { $this->course = $course; }
    function get_course()           { return $this->course; }
    function set_user( $user )      { $this->user = $user; }
    function get_user()             { return $this->user; }
    function set_log( $log=false )  { $this->log = $log; }
    function store_log( $msg ) {
        if ( $this->log === true) { error_log( $msg.PHP_EOL,3,"./log_".date('Y-m-d').".txt"); }
    }

    function add_object_vars( $po_target, $po_data ) {
        if ( is_object($po_data) && is_object($po_target) ) {
            foreach ( array_keys(get_object_vars($po_data)) as $lc_attribute ) {
                $po_target->{$lc_attribute} = $po_data->{$lc_attribute};
            }
        }
        return $po_target;
    }

    function render_mail( $data, $template ) {
        if ( preg_match_all("/\{([a-z]{1,})\}/i",$template,$la_result) ) {
            if ( count($la_result) > 0 ) {
                foreach ( array_values($la_result[1]) as $lc_Attribute ) {
                    if ( property_exists($data,$lc_Attribute) ) {
                        $template = preg_replace("/\{".$lc_Attribute."\}/",$data->{$lc_Attribute},$template);
                    }
                }
            }
        }
        return $template;
    }

    function notifications_log( $setting ) {
        GLOBAL $DB;

        $data = new stdClass();
        $data->settings_id  = $setting->settingid;
        $data->status       = $setting->status; // 1 (ok) 2 (error) 
        $data->created_on   = date('Y-m-d H:i:s');
        $data->userid       = $setting->userid;
        $data->courseid     = $setting->courseid;

        try {
            //$DB->update_record('block_rlms_ntf_log', array('id' =>$id_notification, 'status' => 2));
            $DB->insert_record('block_rlms_ntf_log', $data);
        } catch(Exception $e) {
            $this->store_log( "error saving notification log ".$e->getMessage() );
        }
    }

    
    function send_mail_student( $row ) {
        try {
            $mail = get_mailer();
        } catch (Exception $e) {
            $this->store_log( "error mail student ".$e->getMessage() );            
        }

        if ( isset($mail) ) {
            $supportuser    = core_user::get_support_user();
            $mail->Sender   = $supportuser->email;
            $mail->From     = $supportuser->email;
            
            $mail->Subject  = get_string($row->name, 'block_rlms_notifications');
                            
            $mail->isHTML(true);
            $mail->Encoding = 'quoted-printable';

            // render template        
            $mail->Body = $this->render_mail( $row, $row->template );
            $mail->AddAddress( $row->email );
            
            //$mail->send();
            if( !$mail->send() ) {
                $row->status = 2;
                $this->notifications_log( $row );
            } else {
                $row->status = 1;
                $this->notifications_log( $row );
            }
        }
    }

    function send_mail_teacher( $row ) {   
        GLOBAL $DB;        

        /**
        * Get User group, if group get all teachers of the group, if no group get all teachers that do not belong to a group,
        * then send the notification.
        * @author Yesid V.
        * @since Sep 01, 2017
        * @rlms
        *
        */

        $student_group_sql = "SELECT gm.groupid,
                                     gm.userid,
                                     g.courseid,
                                     g.name
                              FROM {groups_members}    gm
                                    INNER JOIN {groups} g
                                    ON gm.groupid = g.id AND gm.userid = ".$row->student." AND g.courseid = ".$this->course;

        $student_group = $DB->get_record_sql($student_group_sql);

        $sql = "SELECT u.id AS userid,
                       c.id AS courseid,
                       u.firstname,
                       u.lastname,
                       email,
                       (SELECT count(*)
                        FROM {block_rlms_ntf_log}
                        WHERE userid = u.id AND courseid = c.id)
                          AS sent
                FROM {course}    c
                     INNER JOIN {context} ct ON c.id = ct.instanceid
                     INNER JOIN {role_assignments} ra ON ra.contextid = ct.id
                     INNER JOIN {user} u ON u.id = ra.userid
                     INNER JOIN {role} r ON r.id = ra.roleid";

                     if($student_group->groupid){
                        $sql .= " INNER JOIN {groups_members} gm ON u.id = gm.userid";
                     }

        $sql .= " WHERE     r.shortname IN ('editingteacher', 'teacher')
                      AND c.id = ".$this->course;            

                      if($student_group->groupid){
                        $sql .= " AND gm.groupid = ".$student_group->groupid;
                      }else{
                        $sql .= " AND u.id NOT IN
                                         (SELECT userid
                                          FROM {groups_members} gm INNER JOIN {groups} g
                                          WHERE gm.groupid = g.id AND g.courseid = ".$this->course.")";
                      }

        $sql .= " GROUP BY u.id";

        $studentname = $row->fullname;

        // teachers data
        $teachers = array();
        $records2 = $DB->get_records_sql($sql);

        foreach($records2 as $record2) { 
            // if email has not been sent
            //if ( $record2->sent == 0 ) {
                $teacher    = new stdClass();
                $teacher->userid    = $record2->userid;
                $teacher->email     = $record2->email;
                $teacher->firstname = $record2->firstname;
                $teacher->lastname  = $record2->lastname;
                $teacher->fullname  = $record2->firstname." ".$record2->lastname;

                $teachers[] = $teacher;
            //}
        }

        try {
            $mail = get_mailer();
        } catch (Exception $e) {
            $this->store_log( "error mailer plugin ".$e->getMessage() );            
        }
        
        if ( isset($mail) ) {
            $supportuser = core_user::get_support_user();
            
            if( count($teachers) ) {
                $mail->Sender = $supportuser->email;
                $mail->From = $supportuser->email;
                
                $mail->Subject = ( !isset($row->subject) || trim($row->subject) == "" ) ? get_string($row->name, 'block_rlms_notifications') : $row->subject;
                                
                $mail->isHTML(true);
                $mail->Encoding = 'quoted-printable';

                foreach ( array_keys($teachers) as $li_index ) { 
                    // declare email if you need execute test
                    $teachers[$li_index]->email = ( $this->email != "" ) ? $this->email : $teachers[$li_index]->email;                    
                    $mail->AddAddress( $teachers[$li_index]->email );
                    // add teacher data to course data
                    $new_row    = $this->add_object_vars( $row, $teachers[ $li_index ] ); 
                    //  setting fullname as student name
                    $new_row->fullname = $studentname;
                    $mail->Body = $this->render_mail( $new_row, $row->template );
                    
                    if( !$mail->send() ) {
                        ob_start();print_r(error_get_last());$response_text=ob_get_contents();ob_end_clean();
                        $row->status = 0;
                        $this->notifications_log($new_row);
                    } else {
                        $row->status = 1;
                        $this->notifications_log($new_row);
                    }
                }
            }
        }
    }

    function run($record) {

        GLOBAL $DB, $CFG, $USER;

        // activate lo
        $this->set_log( false );
        $this->set_course( $record->course_id );

        // select all users that complete courses and not in table rlms_log
        $sql = "SELECT u.id as userid, c.id as courseid, c.fullname as coursename, u.firstname, u.lastname, CONCAT(u.firstname,' ',u.lastname) as fullname, u.email, IFNULL( (SELECT CONCAT(pns.id,'::',pn.name,'::',pns.template) FROM {block_rlms_ntf_settings} as pns INNER JOIN {block_rlms_ntf} as pn ON pns.notification_id = pn.id AND pn.name = 'notify_on_course_completion_student' WHERE pns.course_id = c.id AND pns.enabled = 1 LIMIT 1 ), 0) as notify_student, IFNULL( (SELECT CONCAT(pns.id,'::',pn.name,'::',pns.template,'::', case when pns.config is null then '' else pns.config end ) FROM {block_rlms_ntf_settings} as pns INNER JOIN {block_rlms_ntf} as pn ON pns.notification_id = pn.id AND pn.name = 'notify_on_course_completion_teacher' WHERE pns.course_id = c.id AND pns.enabled = 1 LIMIT 1 ), 0 ) as notify_teacher 
        FROM {course} as c INNER JOIN {course_completions} as cc ON c.id = cc.course INNER JOIN {user} as u ON cc.userid = u.id WHERE c.id = {$record->course_id} AND cc.timecompleted > 0 AND CONCAT(c.id,'-',u.id,'-',".$record->id.") NOT IN (SELECT CONCAT(courseid,'-',userid,'-',".$record->id.") FROM {block_rlms_ntf_log} WHERE courseid = {$record->course_id} AND settings_id = {$record->id} AND status = 1 )";

        $this->store_log( PHP_EOL."blocks/rlms_notifications/classes/notify_on_course_completion_student.php ".$sql );

        /**
        * Get current student user in order to know if belong to a group.
        * @author Yesid V.
        * @since Sep 01, 2017
        * @rlms
        *
        */

        $user_record = $DB->get_records_sql($sql);

        if( count($user_record) > 0 ) {
            foreach ( $user_record as $lo_record ) {

                $domain = $CFG->wwwroot;
                if($companyid = $DB->get_field('company_users', 'companyid', array('userid'=>$lo_record->userid))){
                    $companyhost = $DB->get_field('company', 'hostname', array('id'=>$companyid));
                    if($companyhost){
                        if(substr($companyhost, -1) == '/'){
                            $domain = rtrim($companyhost, "/");
                        } else {
                            $domain = $companyhost;
                        }
                    }
                }
                // add link to current course
                $record->link = $domain.'/course/view.php?id='.$record->course_id;

                $rcdsql = "SELECT * FROM {block_rlms_ntf_log} WHERE userid =? AND courseid =? AND settings_id =?";
              
                $student_id = $lo_record->userid;

                $new_row = $this->add_object_vars( $record, $lo_record );

                // if configured sending email to student 
                if ( $lo_record->notify_student != 0 ) {

                    $la_setting = explode("::",$lo_record->notify_student );
                    $rcd = $DB->get_record_sql($rcdsql,array($lo_record->userid, $lo_record->courseid,$la_setting[0])); 
                    $last_run = $this->last_run_success($la_setting[0]);
                    if(!$rcd && empty($last_run))
                    {
                        // settings
                        $new_row->settingid = $la_setting[0];
                        $new_row->name      = $la_setting[1];
                        $new_row->template  = $la_setting[2];
                        
                        $this->send_mail_student( $new_row );
                      //  message_post_message($USER, $lo_record, $la_setting[2], FORMAT_MOODLE);
                    }
                }

                if ( $lo_record->notify_teacher != 0 ) { 

                    // special case
                    // If teacher must be receive email but not student, mark student as sent
                    // to avoid repeat email with this student
                    if ( $lo_record->notify_student == 0 ) {
                        $data_setting = new stdClass();
                        $data_setting->settingid    = 0; // not know id of student notification setting
                        $data_setting->status       = 1;
                        $data_setting->created_on   = date('Y-m-d H:i:s');
                        $data_setting->userid       = $lo_record->userid;
                        $data_setting->courseid     = $lo_record->courseid;
                        $this->notifications_log( $data_setting );
                    }

                    $la_setting = explode("::",$lo_record->notify_teacher );
                    $rcd = $DB->get_record_sql($rcdsql,array($lo_record->userid, $lo_record->courseid,$la_setting[0])); 
                    $last_run = $this->last_run_success($la_setting[0]);
                    if(!$rcd && empty($last_run))
                    {
                        // settings
                        $new_row->settingid = $la_setting[0];
                        $new_row->name      = $la_setting[1];
                        $new_row->template  = $la_setting[2];
                        // subject 
                        $lo_subject = @json_decode($la_setting[3], true);
                        $new_row->subject   = $this->render_mail($new_row,$lo_subject['notify_email_subject']);

                        /**
                        * Add student id to $new_roe object and call send_mail_teacher function
                        * @author Yesid V.
                        * @since Sep 01, 2017
                        * @rlms
                        *
                        */

                        $new_row->student   = $student_id;
                        
                        $this->send_mail_teacher( $new_row );
                       // message_post_message($USER, $lo_record, $la_setting[2], FORMAT_MOODLE);
                    }
                }
            }
        }
    }
    
    function last_run_success($config_id){
        global $DB;
        return $DB->get_record('block_rlms_ntf_log',['settings_id' => $config_id, 'status' => 1]);
    }
}

