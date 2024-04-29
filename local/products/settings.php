<?php


defined('MOODLE_INTERNAL') || die;

if ( $hassiteconfig ){
 
	// Create the new settings page
	// - in a local plugin this is not defined as standard, so normal $settings->methods will throw an error as
	// $settings will be NULL
	$settings = new admin_settingpage( 'local_products', 'Local Product Settings Page ' );
 
	// Create 
	$ADMIN->add( 'localplugins', $settings );
 
	// Add a setting field to the settings for this page
	
        $settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_products/merchantid',
 
		// This is the friendly title for the config, which will be displayed
		'Billdesk Merchent ID',
 
		// This is helper text for this config field
		'Billdesk Merchent ID',
 
		// This is the default value
		'',
 
		// This is the type of Parameter this config is
		PARAM_TEXT
 
	) );
 
        $settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_products/securityid',
 
		// This is the friendly title for the config, which will be displayed
		'Billdesk Security ID',
 
		// This is helper text for this config field
		'Billdesk Security ID',
 
		// This is the default value
		'',
 
		// This is the type of Parameter this config is
		PARAM_TEXT
 
	) );
         $settings->add( new admin_setting_configtext(
 
		// This is the reference you will use to your configuration
		'local_products/checksum',
 
		// This is the friendly title for the config, which will be displayed
		'Billdesk Checksum ',
 
		// This is helper text for this config field
		'Billdesk Checksum ',
 
		// This is the default value
		'',
 
		// This is the type of Parameter this config is
		PARAM_TEXT
 
	) );
        
        
 
 
}