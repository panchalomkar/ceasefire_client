<?php
class notify_on_course_overdue{
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
    /**
     * @description string with tags in format {"course"_tag} will be search in the array given and it will replace the tags for the value
     * @author Daniel Carmona <daniel.carmona@rlmssolutions.com>
     * @since 03-26-2018
     * @param sttring $string String with tags to be replaced
     * @param array $settings Array with the next setup ['course' => (array)$data,...,n]
     * @return string String without tags
     */
    function replace_tags($string,$settings = []){
        if(!empty($settings)){
            /*we get all the tags string*/
            $data_tags = $this->get_string_between($string, '{', '}');
            foreach ($data_tags as $tag) {
                $pos = strpos($tag, '_');
                $model = '';
                $property = '';
                if($pos !== false){
                    $property = substr($tag, $pos+1, strlen($tag));
                    $model = substr($tag, 0, $pos);
                    if($model == 'user' && $property == 'fullname'){
                        $settings[$model][$property] = $settings[$model]['firstname'].' '.$settings[$model]['lastname'];
                    }
                }
                if(!empty($model) && !empty($property) && array_key_exists($model, $settings)){
                    $string = str_replace('{'.$tag.'}', $settings[$model][$property] , $string);
                }
            }
        }
        
        return $string;
    }
    
    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        
        $array_tags = [];
        if ($ini == 0) return $array_tags;
        
        $exist_data = $ini;
        while($exist_data != ''){
            $ini += strlen($start);
            $len = strpos($string, $end, $ini) - $ini;
            $str_tmp = substr($string, $ini, $len);
            $array_tags[] = $str_tmp;
            $string = str_replace($start.$str_tmp.$end, '', $string);
            $exist_data = strpos($string, $start);
        }
        
        return $array_tags;
    }
    
    function send_mail_student($row, $user) {
        global $USER;
        if ($user->email) {
            $config = [];
            if(property_exists($row, 'config')){
                $config = json_decode($row->config,true);
            }
            $this->course->link = $row->link;
            
            $email_subject = $this->render_mail($row, (array_key_exists('notify_email_subject', $config) && !empty($config['notify_email_subject']))?$this->replace_tags($config['notify_email_subject'],
                    ['course' => (array)$this->course,'user' => (array)$user]):get_string($row->name, 'block_rlms_notifications'));
            
            // Render template
            $body = $this->render_mail($row, $this->render_mail( $row, $this->replace_tags($row->template, ['course' => (array)$this->course,'user' => (array)$user])) );
            try {
                $site = get_site();
                $mail = email_to_user($user, $site->shortname,$email_subject,$body,$body);
            } catch (Exception $e) {
                $this->store_log( "error mail student ".$e->getMessage() );            
            }
            //message_post_message($USER, $user, $body, FORMAT_MOODLE);
            $row->settingid  = $row->id;            
            $row->userid       = $user->id;
            $row->courseid     = $this->course->id;
            if(!$mail) {
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
        /*Set the course*/
        $this->_set_course( $record->course_id );
        require_once("{$CFG->libdir}/completionlib.php");
        /*Validate if course is active*/
        if(!$this->is_course_active()){
            return;
        }
        
        /*context*/
        $context = context_course::instance($record->course_id);
        
        /*Get enrolled users of the course*/
        $users = get_enrolled_users($context);
        /*get the users and its roles*/
        $user_roles_tmp = $DB->get_records('role_assignments', array('contextid' => $context->id));
        foreach ($user_roles_tmp as $value) {
            if(property_exists($value, roleid)){
                $users[$value->userid]->roleid = $value->roleid; 
            }
        }
        
        $rolenames = role_get_names($context, ROLENAME_ALIAS);
        
        if (count($users) > 0) {
            /*iterate users*/
            $course_object = $DB->get_record('course', array('id'=> $record->course_id));  
            $cinfo = new completion_info($course_object);
            foreach ($users as $user) {
                $obj_role = $rolenames[$user->roleid];
                $is_overdue = false;
                if($enddate = $this->get_course_enddate()){
                    if(time() > $enddate){
                        $is_overdue = true;
                    }
                }
                
                $iscomplete = $cinfo->is_course_complete($user->id);	
                $status = "";
                if ($iscomplete) {
                    $status =  'Completed';
                } 
                if($obj_role && $obj_role->shortname == 'student' && $is_overdue && $status ==""){
                    /*Only if the user is student and the course is incomplete we send notification*/
                    $decoded = json_decode($record->config, true);
                    $last_run = $this->last_run_success($record->id);
                    $diff = date_diff(date_create($last_run->created_on),date_create(date('Y-m-d h:i:s')))->format("%a");
                    
                    /**
                     * @description: add record values for render body and subject
                     * @author Hugo S.
                     * @since May 24 of 2018
                     * @rlms
                     */
                    
                    if($decoded && (empty($last_run) || 1 >= $diff)) {
                        // add link to current course
                        $domain = $CFG->wwwroot;
                        if($companyid = $DB->get_field('company_users', 'companyid', array('userid'=>$user->id))){
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
                        $record->coursename = $this->course->fullname;
                        $record->fullname = $user->firstname.' '.$users->lastname;
                            if($last_run->status != 1){
                                $this->send_mail_student($record,$user);
                            }
                        
                    }
                }
            }
        }
    }
    
    function get_course_enddate(){
        global $DB;
        $enddate = '';
        $enddate = $DB->get_record('course',['id' => $this->course->id])->enddate;
        return $enddate;
    }
    
    function last_run_success($config_id){
        global $DB;
        return $DB->get_record('block_rlms_ntf_log',['settings_id' => $config_id, 'status' => 1]);
    }
}