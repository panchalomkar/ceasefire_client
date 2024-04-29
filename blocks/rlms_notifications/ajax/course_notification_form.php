<?php

require_once('../../../config.php');
require_once($CFG->dirroot.'/blocks/rlms_notifications/classes/template_editForm.php');

global $CFG, $USER, $DB;

$course_id = required_param('id', PARAM_TEXT);

//Get Course notifications
$sql = "SELECT s.*, n.name FROM {block_rlms_ntf_settings} AS s INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id WHERE s.course_id = ?";
$records = $DB->get_records_sql($sql, [$course_id]);
$records2 = $DB->get_records('block_rlms_ntf');
$keys = array_keys($records2);
if ($records) {
    foreach ($records as $key => $recordR) {
        if (in_array($recordR->notification_id, $keys)) {
            unset($records2[$recordR->notification_id]);
        }
    }
}
foreach ($records2 as $record) {
    $data = new stdClass();
    $data->course_id = $course_id;
    $data->notification_id = $record->id;
    $data->enabled = 0;
    $data->config = $record->config;
    $data->template = $record->template;
    $DB->insert_record('block_rlms_ntf_settings', $data);
}
$records = $DB->get_records_sql($sql, [$course_id]);
//Prepare values to return
$return = [];
foreach ($records as $record) {
    $return[] = (array) $record;
}

echo json_encode($return);