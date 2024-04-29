<?php

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");
require_once 'locallib.php';

class filter_form extends moodleform {

    //set report id
    public function __construct() {
        
        parent::__construct();
    }

    //Add elements to form
    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form; // Don't forget the underscore! 
        //get report archtype
        $bunits = get_business_units();
        $bu[''] = 'Select department';

        foreach ($bunits as $value) {
            $bu[$value->data] = $value->data;
        }
        $mform->addElement('select', 'department', get_string('selectdepartment'), $bu, array());
        $mform->setType('department', PARAM_RAW);
        $mform->setDefault('department', '');
        
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit', LANGFILE));
//        $buttonarray[] = $mform->createElement('cancel');
        $buttonarray[] = $mform->createElement('button', 'cancel', html_writer::link(new moodle_url('index.php', array()), get_string('clear', LANGFILE)));

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

}
