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
        //if land manager
       $bunits = get_business_units();
        
        $bu[1] = 'Select department';

        foreach ($bunits as $key => $value) {
            if ($value->department == ''){
                continue;
            }
           // print_object($value);die;
            $bu[$key] = $value->department;
        }
        $mform->addElement('select', 'department', 'Select Department', $bu, array());
        $mform->setType('department', PARAM_RAW);
        $mform->setDefault('department', '');


        //get my courses
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('submit', LANGUAGE));
//        $buttonarray[] = $mform->createElement('cancel');
        $buttonarray[] = $mform->createElement('button', 'cancel', html_writer::link(new moodle_url('index.php', array()), get_string('clear', LANGUAGE)));

        $mform->addGroup($buttonarray, 'buttonar', '', ' ', false);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }

}
