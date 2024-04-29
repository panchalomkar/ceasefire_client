<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define('AJAX_SCRIPT', true);

include_once '../../config.php';
global $DB, $USER, $CFG;
include_once $CFG->dirroot . '/local/mydashboard/lib.php';


$action = $_REQUEST['action'];

switch ($action) {

    case 'SCRATCHCARD';
        $scid = $_POST['scid'];
        $point = $_POST['spoint'];
        if ($record = $DB->get_record('user_scratchcard', array('userid' => $USER->id, 'id' => $scid))) {
            if (add_point_log($USER->id, 'course_scratchcard', 'added', $_POST['spoint'])) {
                $update = new stdClass();
                $update->id = $scid;
                $update->redeemed = 1;
                $DB->update_record('user_scratchcard', $update);
                if ($point <= 0) {
                    $url = '<img src="' . $CFG->wwwroot . '/local/mydashboard/sunil/images/0.jpg" width="200">';
                } else if ($point <= 10) {
                    $url = '<img src="' . $CFG->wwwroot . '/local/mydashboard/images/pop/1-10.gif" width="200">';
                } else if ($point > 10 && $point <= 20) {
                    $url = '<img src="' . $CFG->wwwroot . '/local/mydashboard/images/pop/10-20.gif" width="200">';
                } else if ($point > 20) {
                    $url = '<img src="' . $CFG->wwwroot . '/local/mydashboard/images/pop/21-50.gif" width="200">';
                }
                echo json_encode(array($point, $url));
            }
        }
        break;
}
