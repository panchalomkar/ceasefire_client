<?php

class block_courses_statistics_edit_form extends block_edit_form {
    protected function specific_definition($mform) {
        global $CFG;
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('header_settings', 'block_courses_statistics'));
    
        $mform->addElement('select', 'config_numdata', get_string('text_settings', 'block_courses_statistics'),[get_string('select', 'block_courses_statistics'),'1','2','3','4','5','6','7','8','9','10']);
    }

    function set_data($defaults) {
        parent::set_data($defaults);
    }
}
