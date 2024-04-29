<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require('../../config.php');

require "lib.php";

//Globalized required vars
global $CFG, $OUTPUT, $PAGE,$DB,$COURSE, $THEME;

require_login();
$PAGE->requires->css(new moodle_url('/local/system_notifications/css/style.css'));
//$PAGE->requires->js(new moodle_url('/local/system_notifications/js/system_notifications.js'));
//$PAGE->requires->js(new moodle_url('/local/system_notifications/js/system_notifications_custom.js'));

// To give access to teacher role for editing ‘Course Notification’ (Ref. PSE doc)
$courseid = required_param('id', PARAM_INT);
require_capability('moodle/site:approvecourse', context_course::instance($courseid));

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('default_plugins');
$PAGE->set_title(get_string('notifications','local_system_notifications'));
$PAGE->set_url('/local/system_notifications/system_notifications.php');
require_once($CFG->libdir.'/adminlib.php');

$plugins = core_plugin_manager::instance()->get_plugins_of_type('block');
$i = 0;
$_data = array();

// If the config plugin "rlms_notifications" is installed we can configure the templates
if (array_key_exists('rlms_notifications', $plugins)) {
    /*Get all the data*/
    $data[$i]['tab'] = $plugins['rlms_notifications']->displayname;
    $content = get_tab_content('rlms_notifications');
    $data[$i]['content'] = $content;
    $data[$i]['active'] = true;
    $data[$i]['id'] = $plugins['rlms_notifications']->name;
    $i++;
}

$templatecontext = [
    'output' => $OUTPUT,
    'data' => $data
];
echo $OUTPUT->render_from_template('local_system_notifications/main',$templatecontext);