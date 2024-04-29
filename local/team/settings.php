<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig){
    // Create the new settings page
    $settings = new admin_settingpage('local_team', 'Team Management Settings');    
    // Create 
	$ADMIN->add( 'localplugins', $settings );
    $settings->add(new admin_setting_heading('local_team', get_string('enrol_required_team', 'local_team'), ''));
    $settings->add(new admin_setting_configtext('enrolment_capacity','enrolment_capacity',get_string('enrolment_allowed_text','local_team'), 3000, PARAM_NOTAGS));
}