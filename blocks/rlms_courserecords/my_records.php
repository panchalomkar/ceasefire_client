<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

#ini_set('display_errors', 1);
#error_reporting(E_ALL);

$pluginName = 'rlms_courserecords';

require_login();

$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$header = $SITE->shortname;

// Start setting up the page
$params = array();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/blocks/' . $pluginName . '/index.php', $params);
$PAGE->set_pagelayout('mydashboard');
$PAGE->set_title($header);
$PAGE->set_heading($header);

$PAGE->navbar->add(get_string('learning_records', "block_$pluginName"));

echo $OUTPUT->header();

echo '<div class="title">';
echo '<h2>' . get_string('learning_records', "block_$pluginName") . '</h2>';
echo rlms_courserecords_myrecords($USER->id);  

echo '</div>';

echo $OUTPUT->footer();