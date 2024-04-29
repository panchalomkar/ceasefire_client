<?php


namespace local_re_certification\task;

require_once($CFG->libdir . '/enrollib.php');

class recertification extends \core\task\scheduled_task {

    public function get_name() {
        // Shown in admin screens
        return get_string('pluginname', 'local_re_certification');
    }

    public function execute() {
        
	    global $DB, $CFG;
	    include_once("{$CFG->dirroot}/local/re_certification/lib.php");

	    echo "Starting local_recertification Plugin...\n";

	    $query = "SELECT ue.*, c.id as courseid, cid.data, DATE_FORMAT(FROM_UNIXTIME(cc.timecompleted) + INTERVAL cid.data DAY,'%Y-%m-%d' ) as date_recertificate, (FROM_UNIXTIME(cc.timecompleted) + INTERVAL cid.data DAY) as enddate, e.enrol 
	    			FROM mdl_course_completions as cc 
	    				JOIN mdl_enrol as e ON cc.course = e.courseid 
	    				JOIN mdl_course as c ON e.courseid = c.id AND cc.course = c.id 
	    				JOIN mdl_course_info_data cid ON c.id=cid.courseid 
	    				JOIN mdl_course_info_field cif ON cid.fieldid = cif.id AND cif.shortname='certperiod' 
	    				JOIN mdl_user_enrolments as ue ON ue.userid = cc.userid 
	    				AND ue.enrolid = e.id JOIN mdl_user as u ON ue.userid = u.id 
	    			WHERE c.visible = 1 AND u.deleted = 0 AND u.suspended = 0 
	    			AND DATE_FORMAT(FROM_UNIXTIME(cc.timecompleted) + INTERVAL cid.data DAY, '%Y-%m-%d' ) <= DATE_FORMAT(NOW(), '%Y-%m-%d') 
	    			AND TRIM(cid.data) != '' "; 
	    
	    $user_enrolments = $DB->get_records_sql($query);
	    
	    echo "About to iterate through new user_enrolments records...\n";

	    foreach($user_enrolments as $user_enrolment) {
	    	$course = $DB->get_record('course', array('id'=>$user_enrolment->courseid));
	    	echo "<b>User: $user_enrolment->userid to course : $course->fullname </b>\n";
			// store aditional information
			$enrol =  new \stdClass();
			$enrol->courseid 		= $user_enrolment->courseid;
			$enrol->enddate 		= $user_enrolment->enddate;
			$enrol->date_recertificate 	= $user_enrolment->date_recertificate;
			$enrol->recertificate_days 	= $user_enrolment->certperiod;

			$enddate = strtotime($enrol->enddate);

			unset($user_enrolment->courseid);
			unset($user_enrolment->enddate);
			unset($user_enrolment->date_recertificate);
			unset($user_enrolment->certperiod);

			try	{
				$transaction = $DB->start_delegated_transaction();

				$history_id = $DB->get_field('user_enrolmentshistory', 'id', array('oldid' => $user_enrolment->id));
				if($history_id > 0)	{
					$update_record = $user_enrolment;
					$update_record->action = 2; // Update action
					$update_record->id = $history_id;
					$update_record->timeend = $enddate;
					$DB->update_record('user_enrolmentshistory', $update_record);
				} else {
					// TODO: Insert
					$insert_record = $user_enrolment;
					$insert_record->oldid = $insert_record->id;
					$insert_record->action = 1; // Insert action
					$insert_record->timeend = $enddate;
					unset($insert_record->id);
					$user_enrol_id = $DB->insert_record('user_enrolmentshistory', $insert_record) or die(mysql_error()) ;
				}
				
				echo "Getting course_completions...\n";
				$course_completions = $DB->get_records('course_completions', array('userid' => $user_enrolment->userid,'course' => $enrol->courseid));

				foreach($course_completions as $cc) {
					$cc_history_id	= $DB->get_field('course_completions_history', 'id', array('oldid' => $cc->id));
					$delete_id		= $cc->id;

					$fg_query = "SELECT gg.finalgrade, gg.timecreated
						  FROM mdl_grade_grades AS gg
				    INNER JOIN mdl_grade_items AS gi
					        ON gi.id = gg.itemid
						 WHERE gg.userid = ?
						   AND gi.courseid = ?
						   AND gi.itemtype = 'course'
					";

					$fg_result = $DB->get_record_sql($fg_query, array($user_enrolment->userid, $enrol->courseid));

					if ($cc_history_id > 0)	{
						$update_cc = new \stdClass();
						$update_cc->oldid = $cc->id;
						$update_cc->status = 2;
						$update_cc->id = $cc_history_id;
						$update_cc->userid = $cc->userid;
						$update_cc->course = $cc->course;
						$update_cc->timeenrolled = $cc->timeenrolled;
						$update_cc->timestarted = $cc->timestarted;

						if(is_null($cc->timecompleted)){
							$update_cc->timecompleted = time();
						}else{
						    $update_cc->timecompleted = $cc->timecompleted;
						}

						if ($fg_result->finalgrade > 0) {
							$update_cc->finalgrade = $fg_result->finalgrade;
						}

						echo "Updating new course_completions_history...\n";
						$DB->update_record('course_completions_history', $update_cc) ;
					} else {
						// Insert
						$insert_cc = new \stdClass();
						$insert_cc->oldid = $cc->id;
						$insert_cc->status = 1;

						$insert_cc->userid = $cc->userid;
						$insert_cc->course = $cc->course;
						$insert_cc->timeenrolled = $cc->timeenrolled;
						$insert_cc->timestarted = $cc->timestarted;
						if(is_null($cc->timecompleted)){
							$insert_cc->timecompleted = time();
						}else{
							$insert_cc->timecompleted = $cc->timecompleted;
						}
						$insert_cc->userenrolid = ($history_id > 0 ? $history_id : $user_enrol_id);

						if ($fg_result && $fg_result->finalgrade > 0) {
							$insert_cc->finalgrade = $fg_result->finalgrade;
						}

						$DB->insert_record('course_completions_history', $insert_cc);
					}

					if ($DB->record_exists('course_completions', array('id' => $delete_id))) {
						if($DB->delete_records('course_completions', array('id' => $delete_id))) { echo "Record deleted from course_completions\n"; }
					}

					if ($DB->record_exists('course_completion_crit_compl', array('userid'=>$user_enrolment->userid,'course' => $enrol->courseid))) {
						if($DB->delete_records('course_completion_crit_compl', array('userid'=>$user_enrolment->userid,'course' => $enrol->courseid)) or die(mysql_error())){ echo "Record deleted from course_completion_crit_compl\n"; }
					}
				}

				$coursemodules = $DB->get_records('course_modules', array('course' => $enrol->courseid));
				foreach ($coursemodules as $cm)	{
					echo "Deleting course module completions\n";
					$DB->delete_records('course_modules_completion',array('coursemoduleid'=>$cm->id,'userid'=>$user_enrolment->userid));
				}

				echo "Getting quiz records for course: ".$enrol->courseid."...\n";
				$quizes = $DB->get_records('quiz', array('course' => $enrol->courseid));

				foreach ($quizes as $quiz) {
					echo "Deleting quiz_arrempts for quiz: ".$quiz->id." and user: ".$user_enrolment->userid."...\n";
					$DB->delete_records('quiz_attempts', array('quiz' => $quiz->id, 'userid' => $user_enrolment->userid));
				}

				echo "Getting certificate records for course: ".$enrol->courseid."...\n";
				$certificates = $DB->get_records('certificate', array('course' => $enrol->courseid));

				foreach ($certificates as $cert) {
					echo "Deleting certificate_issues for certificate: ".$cert->id." and user: ".$user_enrolment->userid."...\n";
					$DB->delete_records('certificate_issues', array('certificateid' => $cert->id, 'userid' => $user_enrolment->userid));
				}

				echo "Getting grade items records for course: ".$enrol->courseid."...\n";
				$grade_items = $DB->get_records('grade_items', array('courseid' => $enrol->courseid));

				foreach ($grade_items as $gi) {
					echo "Deleting grade_grades for itemid: ".$gi->id." and user: ".$user_enrolment->userid."...\n";
					$DB->delete_records('grade_grades', array('itemid' => $gi->id, 'userid' => $user_enrolment->userid));
				}

				// remove old user enrolment id
				unset($user_enrolment->id);

				//unenroll user
	            $plugin = enrol_get_plugin($user_enrolment->enrol);
	            $instanceid = new \stdClass();
	            $instanceid->id = $user_enrolment->enrolid;
	            $instanceid->enrol = $user_enrolment->enrol;
	            $instanceid->courseid = $enrol->courseid;
	                    
	            if ($DB->record_exists('user_enrolments', array('userid' => $user_enrolment->userid, 'enrolid'=>$user_enrolment->enrolid))){
	                    $plugin->unenrol_user($instanceid, $user_enrolment->userid);
	                    echo "Deleted user enrollments...\n";

	                // re-enroll user
	                $plugin->enrol_user($instanceid, $user_enrolment->userid, $roleid = $user_enrolment->roleid, $timestart = time(), $timeend = '', $status = null, $recovergrades = null);
	                echo "Re-enrol user...\n";
	            }                                    

	            //course completion                                                    
	            $new_user_cc = new \stdClass();
	            $new_user_cc->userid = $user_enrolment->userid;
	            $new_user_cc->course = $enrol->courseid;
	            $new_user_cc->timeenrolled = time();
	            $DB->insert_record('course_completions', $new_user_cc);
		
				$transaction->allow_commit();
			}
			catch(Exception $e) {
				$transaction->rollback($e);
			}
		}
		echo "Done with new user_enrolments records iteration...\n";
	}
}
