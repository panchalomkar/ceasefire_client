<?php

class notify_on_enrollment_expire {

    public $course;
    public $user;
    public $log;

    public function __construct() {
        $this->course       = 0;
        $this->user         = 0;
        $this->log          = false;
    }

    function set_course( $course )  { $this->course = $course; }
    function get_course()           { return $this->course; }
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
        if ( preg_match_all("/\{([a-z_]{1,})\}/i",$template,$la_result) ) {
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
        $data->courseid     = $setting->course_id;

        try {
            $DB->insert_record('block_rlms_ntf_log', $data);
        } catch(Exception $e) {
            $this->store_log( "error saving notification log ".$e->getMessage() );
        }
    }
    
    function send_mail_student( $row ) {
        global $DB;
        $user = $DB->get_record('user', array('id'=>$row->userid));
        
        $supportuser    = core_user::get_support_user();
        $mailsubject  = get_string($row->name, 'block_rlms_notifications');

        // Render template
        $mailbody = $this->render_mail( $row, $row->template );
        
        try {
            $mail = email_to_user($user, $supportuser, $mailsubject, $mailbody, text_to_html($mailbody));
            $row->status = 1;
            $this->notifications_log( $row );
            mtrace('===== Enrollment expire notification sent for '.$row->userid.' - '.$row->user_fullname.'<br>' );
        } catch (Exception $e) {
            $row->status = 2;
            $this->notifications_log( $row );
            $this->store_log( "error mail student ".$e->getMessage() );            
        }
        message_post_message($supportuser, $user, $mailbody, FORMAT_MOODLE);
    }

    function run($record) {
        GLOBAL $DB, $CFG;
        
        $this->set_course( $record->course_id );
        $record->course_link = $CFG->wwwroot.'/course/view.php?id='.$record->course_id;
        // decoded config
        $decoded = @json_decode($record->config, true);

        if ( $decoded && isset($decoded['days_before_enrollment_expiration']) && $decoded['days_before_enrollment_expiration'] > 0 ){
            $record->days = $decoded['days_before_enrollment_expiration'];
            $ntid = $DB->get_field('block_rlms_ntf', 'id', array('name'=>'notify_on_enrollment_expire'));
            
            $sql = "SELECT
                        ue.id,
                        ue.enrolid,
                        e.enrol,
                        e.courseid,
                        ue.userid,
                        ue.timestart,
                        ue.timeend,
                        ns.id AS settingid,
                        CONCAT(u.firstname,' ',u.lastname) AS user_fullname,
                        c.fullname AS course_fullname
                    FROM {enrol} AS e
                    INNER JOIN {user_enrolments} AS ue ON e.id = ue.enrolid
                    INNER JOIN {block_rlms_ntf_settings} AS ns ON ns.course_id = e.courseid
                    INNER JOIN {user} AS u ON u.id = ue.userid
                    INNER JOIN {course} AS c ON c.id = e.courseid
                    WHERE e.courseid = :courseid
                    AND u.deleted = 0
                    AND u.suspended = 0
                    AND ue.timeend <> 0
                    AND ns.enabled = 1
                    AND ns.notification_id = :notification_id";
            $params = array('courseid'=>$record->course_id, 'notification_id'=>$ntid);
            $students = $DB->get_records_sql($sql, $params);
            $count = 0;
            foreach($students as $student){
                if(!$ntflogtime = $DB->get_record('block_rlms_ntf_log', array('settings_id'=>$student->settingid, 'courseid'=>$record->course_id, 'userid'=>$student->userid, 'status'=>1))){
                    $enddate = new DateTime(userdate($student->timeend));
                    $currentdate = new DateTime(userdate(time()));

                    $interval = $currentdate->diff($enddate);
                    if($interval){
                        if(!$interval->invert && $interval->days == $decoded['days_before_enrollment_expiration']){
                            $new_row = $this->add_object_vars( $record, $student );
                            $this->send_mail_student( $new_row );
                            $count++;
                        }
                    }
                }
            }
            mtrace('===== Enrollment expiration notification sent for '.$count.' users<br>' );
        }
    }
}

