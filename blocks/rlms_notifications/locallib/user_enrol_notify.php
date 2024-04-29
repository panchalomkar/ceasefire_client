<?php

defined('MOODLE_INTERNAL') || die();


function notifications_log( $setting ) {
	global $DB;

	$lb_response = true;
    try {
        $DB->insert_record('block_rlms_ntf_log', $setting );
    } catch(Exception $e) {
        //store_log( "error saving notification log ".$e->getMessage() );
        $lb_response = false;
    }
    return $lb_response;
}

function user_enroll_notification_event($eventdata){
	global $DB, $CFG, $USER, $SESSION;
	$courseid 	= $eventdata->courseid ;
	$userid 	= $eventdata->relateduserid ;

	$block_rlms_ntf = $DB->get_record('block_rlms_ntf', array('name'=>notify_on_course_enroll_student));

	$enrol_ntf_setting = $DB->get_record('block_rlms_ntf_settings', array('notification_id'=>$block_rlms_ntf->id,'course_id'=>$courseid));

	if ( $enrol_ntf_setting->enabled == 1 ) {
    	$user_admin = core_user::get_support_user();
		$user 		= $DB->get_record('user', array('id'=>$userid));
		$course 	= $DB->get_record('course', array('id'=>$courseid));
		$email_msg 	=  $enrol_ntf_setting->template;

		$config_subject = @json_decode($enrol_ntf_setting->config, true);

		foreach($config_subject as $key => $value) { $subjString=$value; }
		$domain = $CFG->wwwroot;
		if(!empty($SESSION->currenteditingcompany)){
			$company = $SESSION->currenteditingcompany;
			$companyhost = $DB->get_field('company', 'hostname', array('id'=>$company));
			if($companyhost){
				if(substr($companyhost, -1) == '/'){
					$domain = rtrim($companyhost, "/");
				} else {
					$domain = $companyhost;
				}
			}
		}
		if(!empty($email_msg)) {
			$msj = $email_msg ;
			$msg=str_replace('{fullname}', $user->firstname.' '.$user->lastname, $msj);
			$msg=str_replace('{link}', $domain.'/course/view.php?id='.$courseid , $msg);
			$msg=str_replace('{coursename}',$course->fullname , $msg);
		} else {
			$msj=get_string('msg','enrol_manual');
			$msg=str_replace('{fullname}', $user->firstname.' '.$user->lastname, $msj);
			$msg=str_replace('{link}', $domain.'/course/view.php?id='.$courseid , $msg);
			$msg=str_replace('{coursename}',$course->fullname , $msg);
		}
		$subject = str_replace('{coursename}',$course->fullname , $subjString);
		$email_to = email_to_user($user,$user_admin,$subject,$msg, text_to_html($msg));
		
		// save notification
		$data = new stdClass();
    	$data->settings_id  = $enrol_ntf_setting->id;
    	$data->created_on   = date('Y-m-d H:i:s');
    	$data->userid       = $userid;
    	$data->courseid     = $courseid;

    	if( !$email_to ) {
            $data->status = 2;
        } else {
            $data->status = 1;
        }

		$lb_response = notifications_log( $data );

		return $email_to ;
	}
}

