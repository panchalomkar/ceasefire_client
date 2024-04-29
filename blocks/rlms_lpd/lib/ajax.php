<?php
define('AJAX_SCRIPT', true);
require_once('../../../config.php');
require_once("../lib/lib.php");


global $PAGE, $USER, $CFG;


$action = optional_param('action', '', PARAM_TEXT);

if($action == 'getLpDetail') {

    if(optional_param('action', '', PARAM_TEXT) ) {

        $learningPath       = required_param('learningPath', PARAM_INT);
        $page = optional_param('page', 0, PARAM_INT);
        $lpid_selected = optional_param('lpid_selected', 0, PARAM_INT);
        $ispagetypelocal = optional_param('ispagetypelocal', false, PARAM_BOOL);
        
        $view               = displayLearningPathsViewDetail($learningPath,$USER->id,$page,$lpid_selected,$ispagetypelocal);

        $arrayView['view']  = $view;
        $jsonObj            = json_encode($arrayView);
        echo $jsonObj;
    }
}
exit();
