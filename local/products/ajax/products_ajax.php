<?php
/**
 * @package     local_products
 * @author      Jayesh T
*/

global $CFG, $USER;
require_once('../../../config.php');
require_once("{$CFG->dirroot}/local/products/lib.php");

define('AJAX_SCRIPT', true);

$action = optional_param('action','', PARAM_TEXT);

switch($action){
    case 'addtocart':
        $course = required_param('course', PARAM_INT);
        $response = addtocart($USER->id, $course);
        break;

    case 'removefromcart':
        $course = required_param('course', PARAM_INT);
        $response = removefromcart($USER->id, $course);
        break;
    case 'checkout':
        
        $response = checkout();
        break;
   
    default:
    # code...
    break;
}

echo json_encode($response);