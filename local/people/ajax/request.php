<?php
require_once('../../../config.php');

global $CFG,$USER,$DB;
define('AJAX_SCRIPT', true);

require_login();
require_once($CFG->dirroot . '/local/people/lib.php');
$form = optional_param('form', '', PARAM_TEXT);
$users = optional_param('users', '', PARAM_TEXT);
$getter = optional_param('getter', false, PARAM_BOOL);
$action = optional_param('action', '', PARAM_TEXT);
$data = optional_param('modaldata', '', PARAM_TEXT);
$response = [
    'form' => ''
];
if($getter && !empty($form) && is_array($users) && !empty($users) && function_exists('get_'.$form.'_form')){
    $response = call_user_func('get_'.$form.'_form', $users);
}elseif($action){
    $data = json_decode($data,true);
    $response = call_user_func('save_'.$action, $data);
}
echo json_encode($response);
exit();

