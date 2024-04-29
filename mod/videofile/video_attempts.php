<?php
//define('AJAX_SCRIPT', true);
require_once('../../config.php');

$attempt_id = optional_param('id', null, PARAM_INT);
$countStatus = optional_param('hasUpdate', null, PARAM_INT);

//Add the seek query
if(optional_param('seek', '', PARAM_TEXT) != ''){
    global $DB, $USER;
    $cmid = optional_param('cmid', null, PARAM_INT);
    $query = $DB->get_record_sql('SELECT second FROM {video_attempts} WHERE userid = ' . $USER->id . ' and cmid = ' . $cmid . ' ORDER BY second DESC LIMIT 0, 1');

    $seek  = new stdClass();
    if(intval(optional_param('seek', '', PARAM_TEXT)) > $query->second){
        $seek->result = $query->second;
    } else {
        $seek->result = optional_param('seek', '', PARAM_TEXT);
    }
    echo json_encode($seek);
} else {
    if(is_null($attempt_id)){
        //Create one new record in the database
        global $DB, $USER, $PAGE;
        $cmid = optional_param('cmid', null, PARAM_INT);

        $to_return = new stdClass();
        $last_attempt = $DB->get_records_sql('SELECT * FROM {video_attempts} WHERE cmid = ? AND userid = ? ORDER BY second DESC LIMIT 1', array($cmid, $USER->id));

        $li_second = 0;

        if(count($last_attempt) > 0){
            $json_returned = false;
            foreach ($last_attempt as $key => $value) {
                if ( $last_attempt[ $key ]->second > $li_second ) { $li_second = $last_attempt[ $key ]->second; }
                $to_return->last =  $value;
            }

        }

        $attempt = new stdClass();
        $attempt->userid = $USER->id;
        $attempt->cmid = $cmid;
        $attempt->second = $li_second;
        $attempt->percentage = 0;
        $attempt->timecreated = date('U');
        $attempt->timemodified = date('U');
        $record_attempt = $DB->insert_record('video_attempts', $attempt);

        $to_return->current_id = $record_attempt;
        echo json_encode($to_return);

    } else {
        global $DB;

        //Get the attempt
        $attempt = $DB->get_record('video_attempts', array('id' => optional_param('id', null, PARAM_INT)));

        //get the percentage
        $percentage = optional_param('percent', 0, PARAM_INT);

        //Get the second
        $second = optional_param('second', 0, PARAM_INT);

        //Completion of the video

        //Get the cm
        $cm = get_coursemodule_from_id('videofile', optional_param('cmid', 0, PARAM_INT), 0, true);
        $videofile = $DB->get_record('videofile', array('id' => $cm->instance));
        $jsonArr = array();
        $status = "";
        $imgUrl ="";
        $count = 0 ;
        //If the current percentage is > to percentage configure, complete the activity
        if($percentage > 0 && $videofile->videoprogress <= $percentage && $countStatus){
            
            //Get the completion library, Get the course of the activity and Get the completion info for the course
            require_once($CFG->dirroot . '/lib/completionlib.php');
            $course = get_course(optional_param('course', 0, PARAM_INT));
            $completioninfo = new completion_info($course);
            $completion = $completioninfo->is_enabled($cm);
            $jsonArr = array();
            if($completion == COMPLETION_TRACKING_AUTOMATIC){
               global $USER;
               $completioninfo->update_state($cm, COMPLETION_COMPLETE, $USER->id);
               $status = "completed";
               $imgUrl = 'theme/image.php/remui/core/1564492370/i/completion-manual-y';
            }
        }

        //Update the percetage of the attemp
        $attempt->second = $second;
        $attempt->percentage = optional_param('percent', 0, PARAM_INT);

        $record = $DB->update_record('video_attempts', $attempt);
        $return ="";
        if( $record ) {
            $return = true;
        }

         $return = false;
        
        $jsonArr = array(
            'status' => $status ,
            'img' => $imgUrl,
            'return' => $return
        );
        echo json_encode($jsonArr);
        // END
    }
}

?>
