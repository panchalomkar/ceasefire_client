<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    
	
	$settings->add(new admin_setting_configcolourpicker('block_rlms_myprogress/backgroundcolor',
        get_string('backgroundcolor', 'block_rlms_myprogress'),
        get_string('backgroundcolor', 'block_rlms_myprogress'), '#000', null )
    );
	
	$settings->add(new admin_setting_configcolourpicker('block_rlms_myprogress/completedcolor',
        get_string('completedcolor', 'block_rlms_myprogress'),
        get_string('completedcolor', 'block_rlms_myprogress'), '#7cd5ec', null )
    );	
	$settings->add(new admin_setting_configcolourpicker('block_rlms_myprogress/notyetstartedcolor',
        get_string('notyetstartedcolor', 'block_rlms_myprogress'),
        get_string('notyetstartedcolor', 'block_rlms_myprogress'), '#434348', null )
    );
	$settings->add(new admin_setting_configcolourpicker('block_rlms_myprogress/inprogresscolor',
        get_string('inprogresscolor', 'block_rlms_myprogress'),
        get_string('inprogresscolor', 'block_rlms_myprogress'), '#90ed7d', null )
    );

}


