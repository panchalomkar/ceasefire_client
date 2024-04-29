<?php

class notify_student_before_course_expiration {

    var $course;
    var $user;
    var $log;

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

    function attribute_exists($pc_table="", $pc_attribute="") {
        global $DB;

        $lb_boolean = false;
        if ( $pc_table != "" && $pc_attribute != "" ) {
            /*
             * Code Commented and Changed by Shivkumar Y.
             * on 03-04-2019 
             */
            //$sql     = "describe ".$pc_table;
            $sql       = "select shortname from ".$pc_table;
            $la_data = $DB->get_records_sql($sql);
            if ( gettype($la_data) == 'array' && isset($la_data[ $pc_attribute ]) ) { $lb_boolean = true; }
        }
        return $lb_boolean;
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

            // Render template
            $mail->Body = $this->render_mail( $row, $row->template );
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

    function run($record) {
        GLOBAL $DB, $CFG;

        // Validate certperiod attribute into mdl_course table
       /*
        * query fixed by Shivkumar Y.
        * added mdl_course_info_field instead of mdl_course to check certperiod
        * on 03-04-2019 
        */
            $this->set_course( $record->course_id );

            // get number of days before expiration
            $decoded = @json_decode($record->config, true);
            
            if ( $decoded && isset($decoded['days_before_expiration']) && $decoded['days_before_expiration'] > 0 ) {
               
                /**
                 * first check for certperiod, if not available then get user based on course enddate 
                 * @author Dnyaneshwar K
                 * @Ticket 09
                 * @since 06-06-2019
                 * 
                 */
                $fieldId="";
                if($this->attribute_exists("mdl_course_info_field","certperiod") === true){
                    $fieldId =  $DB->get_record('course_info_field',['shortname' => 'certperiod'])->id;
                }
                if ( $fieldId !=="" && $this->checkadditionData($fieldId,$record->course_id) ) {
                    $sql = "SELECT concat(u.id,'-',c.id) as pk, s.id as settingid, s.config, s.template, n.name,u.id as userid, c.id as courseid, c.fullname as coursename, u.email, u.firstname, u.lastname, CONCAT(u.firstname,' ',u.lastname) as fullname, DATE_FORMAT(FROM_UNIXTIME(cc.timecompleted), '%Y-%m-%d') AS 'finalizado', DATE_FORMAT(FROM_UNIXTIME(cc.timecompleted) + INTERVAL ( cid.data - ".$decoded['days_before_expiration']." ) DAY, '%Y-%m-%d') AS 'date_notification', cid.data, '".$decoded['days_before_expiration']."' as days FROM {block_rlms_ntf_settings} AS s
                        INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id
                        INNER JOIN {course} AS c ON s.course_id = c.id
                        INNER JOIN {course_completions} as cc ON c.id = cc.course
                        INNER JOIN {user} as u ON cc.userid = u.id 
                        LEFT JOIN {course_info_data} cid ON c.id = cid.courseid 
                        LEFT JOIN {course_info_field} cif ON cid.fieldid = cif.id and cif.shortname='certperiod'
                        WHERE  cc.timecompleted > 0 and c.id = {$record->course_id} AND s.enabled = 1 AND s.id = {$record->id} AND CURDATE() = DATE_FORMAT(FROM_UNIXTIME(cc.timecompleted) + INTERVAL ( cid.data - ".$decoded['days_before_expiration']." ) DAY, '%Y-%m-%d') AND CONCAT(c.id,'-',u.id) NOT IN (SELECT CONCAT(courseid,'-',userid) FROM {block_rlms_ntf_log} WHERE courseid = {$record->course_id} AND settings_id = {$record->id} AND status = 1 ) ";

                    $this->store_log( $sql );
                    $user_record = $DB->get_records_sql($sql);

                }
                else{
                    $sql = "SELECT concat(u.id,'-',c.id) as pk, s.id as settingid, s.config, s.template, n.name,u.id as userid, c.id as courseid, c.fullname as coursename, u.email, u.firstname, u.lastname, CONCAT(u.firstname,' ',u.lastname) as fullname, DATE_FORMAT(FROM_UNIXTIME(c.enddate), '%Y-%m-%d') AS 'finalizado', DATE_FORMAT(FROM_UNIXTIME(c.enddate) - INTERVAL ".$decoded['days_before_expiration']." DAY, '%Y-%m-%d') AS 'date_notification', '".$decoded['days_before_expiration']."' as days FROM {block_rlms_ntf_settings} AS s
                        INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id
                        INNER JOIN {course} AS c ON s.course_id = c.id
                        INNER JOIN {course_completions} as cc ON c.id = cc.course
                        INNER JOIN {user} as u ON cc.userid = u.id 
                        WHERE c.enddate > 0 and cc.timecompleted > 0 and c.id = {$record->course_id} AND s.enabled = 1 AND s.id = {$record->id} AND CURDATE() = DATE_FORMAT(FROM_UNIXTIME(c.enddate) - INTERVAL ".$decoded['days_before_expiration']."  DAY, '%Y-%m-%d') AND CONCAT(c.id,'-',u.id) NOT IN (SELECT CONCAT(courseid,'-',userid) FROM {block_rlms_ntf_log} WHERE courseid = {$record->course_id} AND settings_id = {$record->id} AND status = 1 ) ";
                    
                    $this->store_log( $sql );
                    $user_record = $DB->get_records_sql($sql); 
                }
                
                if( count($user_record) > 0 ) {
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
                        $record->link = $domain.'/course/view.php?id='.$record->course_id;
                        $new_row = $this->add_object_vars( $record, $lo_record );
                        $this->send_mail_student( $new_row );
                    }
                }
            }
    }
    
   /**
    * Check given field id(certperiod) has data
    * @author Dnyaneshwar K
    * @Ticket 09
    * @since 06-06-2019
    * 
    */
    function checkadditionData($fieldId,$courseid){
        global $DB;
        $data = false;
        if($DB->get_record('course_info_data',['fieldid' => $fieldId,'courseid' => $courseid])->data !=""){
            $data = true;
        }
        return $data;
    }

}

