<?php
require_once('../../../config.php');

global $CFG,$USER,$DB;
define('AJAX_SCRIPT', true);

require_login();
require_once($CFG->dirroot . '/local/rlmscourse_rating/lib.php');
$user = optional_param('userid', 0,  PARAM_INT);
$course = optional_param('courseid', 0, PARAM_INT);
$rating = optional_param('rating',0, PARAM_INT);
$action = optional_param('action',null, PARAM_TEXT);
$return_arr = array();
//$action = required_param('action', PARAM_TEXT);
if(isset($rating)){
    $ratings = new stdClass();
    $ratings->id = '';
    $ratings->courseid = $course;
    $ratings->userid = $user;
    $ratings->rating = $rating;
    $ratings->timestamp = time();
    
    $success = $DB->insert_record("local_rlmscourse_rating", $ratings); 
    if($success > 0){
        $return_arr[]  = array("status" => "success","id" => $success);
    }
    echo json_encode($return_arr);
}

