<?php

class notify_on_course_incomplete {

    var $course;
    var $user;
    var $log;

    public function __construct() {
        $this->course_id       = 0;
        $this->course       = new stdClass();
        $this->user         = 0;
        $this->log          = false;
    }

    function _set_course( $course_id )  {
        global $DB;
        $this->course_id = $course_id;
        $this->course = $DB->get_record('course',['id' => $course_id]);
    }

    function is_course_active(){
        return ($this->course->visible)?true:false;
    }

    function _get_course()           { return $this->course; }
    function set_user( $user )      { $this->user = $user; }
    function get_user()             { return $this->user; }
    function set_log( $log=false )  { $this->log = $log; }
    function store_log( $msg )      { if ( $this->log === true) { error_log( $msg.PHP_EOL,3,"./log_".date('Y-m-d').".txt"); } }

    function add_object_vars( $po_target, $po_data ) {
        if ( is_object($po_data) && is_object($po_target) ) {
            foreach ( array_keys(get_object_vars($po_data)) as $lc_attribute ) {
                $po_target->{$lc_attribute} = $po_data->{$lc_attribute};
            }
        }
        return $po_target;
    }

    function render_mail( $data, $template ) {
        $la_result = array();
        if ( preg_match_all("/\{([a-z\_]{1,})\}/i",$template,$la_result) ) {
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
        global $CFG;
        try {
            $mail = get_mailer();
        } catch (Exception $e) {
            $this->store_log( "error mail student ".$e->getMessage() );
        }

        if ( isset($mail) ) {
            $supportuser    = core_user::get_support_user();
            $mail->Sender   = $supportuser->email;
            $mail->From     = $supportuser->email;
            $mail->Subject  = get_string('course_incomplete','block_rlms_notifications'). $row->coursename;
            $mail->isHTML(true);
            $mail->Encoding = 'quoted-printable';
            $mail->FromName = 'Admin User';
            // render template
            $mail->Body = $this->render_mail( $row, $row->template );
            $mail->AddAddress( $row->email );
	     
            if( !$mail->send() ) {
                $row->status = 2;
                $this->notifications_log( $row );
            } else {
                $row->status = 1;
                $this->notifications_log( $row );
            }
        }
    }

    function run($record) {
        GLOBAL $DB, $CFG;        
	    mtrace( "===== incomplete ".gettype($record) );
	    if ( gettype($record) == 'object' ) {
		 
            $decoded = @json_decode($record->config, true);
            $last_run = $this->last_run_success($record->id);
            $diff = date_diff(date_create($last_run->created_on),date_create(date('Y-m-d h:i:s')))->format("%a");
            if ( $decoded && isset($decoded['days_after_incomplete']) && $decoded['days_after_incomplete'] > 0 && (empty($last_run) || abs($decoded['days_after_incomplete']) >= $diff)) {
            //if ( $decoded && isset($decoded['days_after_incomplete']) && $decoded['days_after_incomplete'] > 0 ) {
                // Current date
                $current_date = date('Y-m-d');
                $sql   = "select u.id as userid, c.id as courseid, c.fullname as coursename, "
                        . "c.fullname as course_fullname, u.firstname, u.lastname, CONCAT(u.firstname,' ',u.lastname) as user_fullname, "
                        . "CONCAT(u.firstname,' ',u.lastname) as fullname, u.email FROM {course} as c INNER JOIN {course_completions} as cc "
                        . "ON c.id = cc.course INNER JOIN {user} as u ON cc.userid = u.id WHERE c.id = ".$record->course_id." "
                        . "AND cc.timecompleted IS NULL AND DATE('".$current_date."') > DATE_FORMAT(FROM_UNIXTIME(cc.timestarted) + INTERVAL ".$decoded['days_after_incomplete']." DAY, '%Y-%m-%d') AND"
                        . " CONCAT(c.id,'-',u.id,'-',".$record->id.") NOT IN (SELECT CONCAT(courseid,'-',userid,'-',".$record->id.") "
                        . "FROM {block_rlms_ntf_log} WHERE courseid = {$record->course_id} AND status = 1 AND settings_id = ".$record->id.""
                        . " AND DATE_FORMAT(created_on,'%Y-%m-%d') = DATE('".$current_date."') ) ";
                $user_record 	= $DB->get_records_sql($sql);

                if( count($user_record) > 0 ) {
                    mtrace("===== ".count($user_record)." records incomplete ");

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
                        $record->course_link = $domain.'/course/view.php?id='.$record->course_id;

                        $student_id = $lo_record->userid;
                        $context = context_course::instance($record->course_id, MUST_EXIST);
                        $is_enrolled = is_enrolled($context, $student_id);
                        if($is_enrolled && $ntfs = $DB->get_field('block_rlms_ntf_settings', 'id', array('course_id'=>$record->course_id, 'notification_id'=>5, 'enabled'=> 1))){
                            $logssql = "SELECT created_on
                                        FROM {block_rlms_ntf_log}
                                        WHERE settings_id = ".$ntfs." AND courseid = ".$record->course_id." AND userid = ".$lo_record->userid." ORDER BY id DESC LIMIT 1";

                            if($ntflogtime = $DB->get_field_sql($logssql)){
                                $timeafter24hr = strtotime($ntflogtime) + (24 * 60 * 60);
                                $currentdate = time();
                                if($timeafter24hr > $currentdate){
                                    mtrace("===== User ".$lo_record->userid." Enrolled in ".$record->course_id." within 24 hrs");
                                    continue;
                                }
                            }
                        }
                        
                        if($is_enrolled && empty($last_run)){
                            $new_row = $this->add_object_vars( $record, $lo_record );

                            // settings
                            // Modified $record->notification_id; to $record->id; to fix the expiration notification 
                            $new_row->settingid = $record->id; 
                            $new_row->name      = $record->name;
                            $new_row->template  = $record->template;
                            $this->send_mail_student( $new_row );
                        }
                    }
                } else {
                    mtrace("===== not records incomplete ");
                }
            }
        }

    }
    
    function last_run_success($config_id){
        global $DB;
        return $DB->get_record('block_rlms_ntf_log',['settings_id' => $config_id, 'status' => 1]);
    }

}
