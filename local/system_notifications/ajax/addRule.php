<?php
require_once('../../../config.php');


global $CFG,$USER,$DB;

//Validations
if(!isset($CFG->rlms_allow_pm) || $CFG->rlms_allow_pm == false)
    print_error('only_freemium');


require('../lib.php');


 $action = required_param('action', PARAM_TEXT);

switch ($action) {
	//Add new training session
	case 'add-event-rule':
		global $DB;
		$name = optional_param('name', '', PARAM_TEXT);
		$plugin = optional_param('plugin', '', PARAM_TEXT);
		$eventname = optional_param('eventname', '', PARAM_TEXT);
		$curid = optional_param('curid', '', PARAM_INT);
		$description = optional_param('description', '', PARAM_TEXT);
		$frequency = optional_param('frequency', '', PARAM_TEXT);
		$minutes = optional_param('minutes', '', PARAM_TEXT);
		$template = optional_param('template', '', PARAM_INT);

		//Get and convert startdate to timestamp


		//Create a new training session / track
		$rule = $DB->insert_record('tool_monitor_rules', [
			'courseid' => $curid,
			'name' => $name,
			'description' => $description,
			'descriptionformat' => 1,
			'userid' => $USER->id,
			'plugin' => $plugin,
			'eventname' => $eventname,
			'template' => $template,
			'templateformat' => 1,
			'frequency' => $frequency,
			'timewindow' => $minutes,
			'timecreated' => time(),
			'timemodified' => time()
		], true);

		if ($rule) {
			//$return = ["url" => "{$CFG->wwwroot}/admin/tool/monitor/index.php?ruleid={$rule}"];
			$records = $DB->get_records('tool_monitor_rules', ['curid'=> $curid]);
			$return["html"] = printtrainingsession($records);
			echo json_encode($return);
		}
		break;

	//Return a existing training session
	case 'edit-ruleevent':
		$id = required_param('id', PARAM_INT);
		$record = $DB->get_record('tool_monitor_rules', ['id'=> $id]);
		echo json_encode($record);
		break;
	
	//Update a training session
	case 'update-event-rule':
		$id = required_param('id', PARAM_INT);
		$curid = 0;// required_param('curid', PARAM_INT);

		$record = $DB->get_record('tool_monitor_rules', ['id'=> $id]);
		$record->name = required_param('name', PARAM_TEXT);
		$record->description = required_param('description', PARAM_TEXT);
		$record->descriptionformate = 1;
		$record->template = required_param('template', PARAM_TEXT);
		$record->templateformat = 1;
		$record->plugin = required_param('plugin', PARAM_TEXT);
		$record->eventname = required_param('eventname', PARAM_TEXT);
		$record->frequency = required_param('frequency', PARAM_TEXT);
		$record->timewindow = required_param('timewindow', PARAM_TEXT)*60;
		$record->courseid = $curid;
		$record->userid = required_param('userid', PARAM_TEXT);
		$record = $DB->update_record('tool_monitor_rules', $record);
		if ($record) {
			//Create class instances
			$return = ["url" => ""];
			$records = $DB->get_records('tool_monitor_rules');
			$return["html"] = printtrainingsession($records);
			echo json_encode($return);
		}
		break;

	case 'delete-eventrule':
        global $DB;
		$DB->delete_records('tool_monitor_rules', ['id' => required_param('id', PARAM_INT)]);
		$records = $DB->get_records('tool_monitor_rules');
		echo printtrainingsession($records);
		break;


	case 'get-event-rule':
		$curid = 0;//optional_param('curid', '', PARAM_INT);
		$records = $DB->get_records('tool_monitor_rules');
		$return["html"] = printtrainingsession($records);
		echo json_encode($return);		
		break;

	default:
		# code...
		break;
}