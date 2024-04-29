<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require('../../config.php');
require('lib.php');

global $CFG,$USER,$DB;

		$name = optional_param('name', '', PARAM_TEXT);
		$plugin = optional_param('plugin', '', PARAM_TEXT);
		$eventname = optional_param('eventname', '', PARAM_TEXT);
		$curid = optional_param('curid', '', PARAM_INT);
		$description = optional_param('description', '', PARAM_TEXT);
		$frequency = optional_param('frequency', '', PARAM_TEXT);
		$minutes = optional_param('minutes', '', PARAM_TEXT);
		$template = optional_param('template', '', PARAM_INT);
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
