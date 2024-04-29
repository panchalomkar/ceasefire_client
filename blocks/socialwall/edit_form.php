<?php
 
class block_socialwall_edit_form extends block_edit_form {
 
    protected function specific_definition($mform) {
    	
        global $SESSION;
 
	    // Section header title according to language file.
	    $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));
	 
			// Title for the block.
	    $mform->addElement('text', 'config_title', get_string('titleblock', 'block_socialwall'));
	    $mform->setDefault('config_title', 'Social Wall');
	    $mform->setType('config_title', PARAM_MULTILANG);
			
			// Title for the block.
	    $mform->addElement('text', 'config_whatdoyouthink', get_string('whatdoyouthink', 'block_socialwall'));
	    $mform->setDefault('config_whatdoyouthink', 'What do you want to share?');
	    $mform->setType('config_whatdoyouthink', PARAM_MULTILANG);
			
			// Width for iFrame.
	    $mform->addElement('text', 'config_iframewidth', get_string('iframewidth', 'block_socialwall'));
	    $mform->setDefault('config_iframewidth', '100%');
	    $mform->setType('config_iframewidth', PARAM_TEXT);
			
			// Height for iFrame.
	    $mform->addElement('text', 'config_iframeheight', get_string('iframeheight', 'block_socialwall'));
	    $mform->setDefault('config_iframeheight', '700px');
	    $mform->setType('config_iframeheight', PARAM_TEXT);
			
			// Show scroll bars?
	    $mform->addElement('advcheckbox', 'config_showscrollbars', get_string('showscrollbars', 'block_socialwall'),null);

        $company = isset($SESSION->currenteditingcompany) ? $SESSION->currenteditingcompany : 0;
        
        $mform->addElement('hidden', 'config_tenant', $company );
        $mform->setType('config_tenant', PARAM_INT);
        $mform->setDefault('config_tenant', $company);
    }
}