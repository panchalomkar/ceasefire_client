<?php

class notify_user_not_logged_in {

    var $course;
    var $user;
    var $log;
    var $email;

    public function __construct() {
        $this->course   = 0;
        $this->user     = 0;
        // true if you need generate log file
        $this->log      = false;
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
                    if ( property_exists($data,$lc_Attribute) ) {  $template = preg_replace("/\{".$lc_Attribute."\}/",$data->{$lc_Attribute},$template); }
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
            $DB->insert_record('block_rlms_ntf_log', $data);
        } catch(Exception $e) {
            $this->store_log( "error saving notification log ".$e->getMessage() );
        }
    }

    
    function send_mail_student( $row ) {
        global $USER;
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
            // declare email if you need execute test
            $row->email = ( $this->email != "" ) ? $this->email : $row->email;

            $mail->AddAddress( $row->email );
            
            if( !$mail->send() ) {
                $row->status = 2;
                $this->notifications_log( $row );
            } else {
                $row->status = 1;
                $this->notifications_log( $row );
            }
            message_post_message($USER, $row, $mail->Body, FORMAT_MOODLE);
        }
    }

    function send_mail_teacher( $row ) {   
        GLOBAL $DB, $USER;        

        $sql = "SELECT u.id as userid,
            c.id as courseid, 
            u.firstname,
            u.lastname,
            email, (select count(*) FROM {block_rlms_ntf_log} WHERE userid = u.id AND courseid = c.id ) as sent
        FROM {course} c
        JOIN {context} ct ON c.id = ct.instanceid
        JOIN {role_assignments} ra ON ra.contextid = ct.id
        JOIN {user} u ON u.id = ra.userid
        JOIN {role} r ON r.id = ra.roleid
        WHERE 
            r.shortname IN('editingteacher', 'teacher')
        AND c.id = {$this->course} GROUP BY u.id ";

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
                
                $mail->Subject = get_string($row->name, 'block_rlms_notifications');
                                
                $mail->isHTML(true);
                $mail->Encoding = 'quoted-printable';

                foreach ( array_keys($teachers) as $li_index ) { 
                    // declare email if you need execute test
                    $teachers[$li_index]->email = ( $this->email != "" ) ? $this->email : $teachers[$li_index]->email;
                    $mail->AddAddress( $teachers[$li_index]->email );
                    // add teacher data to course data
                    $new_row    = $this->add_object_vars( $row, $teachers[ $li_index ] ); 
                    $mail->Body = $row->template;
                    
                    if( !$mail->send() ) {
                        ob_start();print_r(error_get_last());$response_text=ob_get_contents();ob_end_clean();
                        $this->store_log( $response_text );
                        $row->status = 2;
                        $this->notifications_log($new_row);
                    } else {
                        $row->status = 1;
                        $this->notifications_log($new_row);
                    }
                    message_post_message($USER, $teachers[$li_index], $mail->Body, FORMAT_MOODLE);
                }
            }
        }
    }

    function run($record) {
        GLOBAL $DB, $CFG;
        
        $this->set_course( $record->course_id );
        ob_start();print_r( $record );$response_text = ob_get_contents(); ob_end_clean();
        $this->store_log( $response_text );

        // get the configuration for this notification
        $decoded = @json_decode($record->config, true);
        if( $decoded && isset($decoded['days_after_enrolled']) && $decoded['days_after_enrolled'] > 0      ) {

            $sql = "SELECT concat(c.id,'-',u.id) as pk, c.id as courseid, c.fullname as coursename, u.id as userid, u.username, u.firstname, u.lastname, CONCAT(u.firstname,' ',u.lastname) as fullname, u.email, DATE_FORMAT(FROM_UNIXTIME(ue.timecreated) + INTERVAL ".$decoded['days_after_enrolled']." DAY, '%Y-%m-%d') as report_date, u.lastlogin FROM mdl_course as c INNER JOIN mdl_enrol as e ON c.id = e.courseid INNER JOIN mdl_user_enrolments as ue ON e.id = ue.enrolid INNER JOIN mdl_user as u ON ue.userid = u.id WHERE c.id = '{$record->course_id}' AND CURDATE() = DATE_FORMAT(FROM_UNIXTIME(ue.timecreated) + INTERVAL ".$decoded['days_after_enrolled']." DAY, '%Y-%m-%d') AND (u.lastlogin = 0 OR u.lastlogin < ue.timecreated ) AND CONCAT(c.id,'-',u.id,'-',".$record->id.") NOT IN (SELECT CONCAT(courseid,'-',userid,'-',".$record->id.") FROM {block_rlms_ntf_log} WHERE courseid = '{$record->course_id}' AND status = 1 ) ";

            $user_record = $DB->get_records_sql($sql);

            $this->store_log( $sql." - records ".count($user_record) );

            if( count($user_record) > 0 ) {
                // define settingid
                $record->settingid  = $record->id;

                ob_start();print_r( $user_record );$response_text = ob_get_contents(); ob_end_clean();
                $this->store_log( $response_text );

                // obtain the first index of result array
                $la_index = array_keys($user_record);
                // create html to teacher
                $html  = "Course: $course".$user_record[ $la_index[0] ]->course_fullname;
                $html .= '<table border="1">';
                $html .= '<tr>';
                $html .= '<th>User</th>';
                $html .= '<th>Email</th>';
                $html .= '</tr>';

                foreach ( $user_record as $lo_record ) {
                    // add link to current course
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
                    // create link
                    $record->link       = $domain.'/course/view.php?id='.$record->course_id;
                    
                    // create record with each student
                    $html .= '<tr>';
                    $html .= "<td>{$lo_record->firstname} {$lo_record->lastname}</td>";
                    $html .= "<td>{$lo_record->email}</td>";
                    $html .= '</tr>';

                    // add user attribute to record info 
                    $new_row = $this->add_object_vars( $record, $lo_record );
                    // send mail to student
                    $this->send_mail_student( $new_row );
                }
                $html .= '</table>';

                $this->store_log( $html );

                // redefine template to teacher
                $record->template = $html;
                // send teacher template
                $this->send_mail_teacher( $record );
            }
        }
    }
}

