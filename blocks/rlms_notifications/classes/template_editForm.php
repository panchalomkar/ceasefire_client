<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class system_notifications_edit_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $COURSE;
        $course_id = $this->_customdata['course_id'];

        $mform = $this->_form;
        // first step, lets look for the configuration for this particular course

        $sql = "SELECT s.*, n.name FROM {block_rlms_ntf_settings} AS s 
                INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id WHERE s.course_id = ?";
        $records = $DB->get_records_sql($sql, [$course_id]);


        // no records found, then we have to insert default values
        if (!$records) {
            //Get Course notifications
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
                
                $namesql = "SELECT name FROM {block_rlms_ntf} WHERE id = ".$record->id;
                $name = $DB->get_record_sql($namesql);
                $template = get_string('default_template_' . $name->name, 'block_rlms_notifications');
                $data->template = $template;
       
                $DB->insert_record('block_rlms_ntf_settings', $data);
            }
            $records = $DB->get_records_sql($sql, [$course_id]);
        } else {
            $new_notify = $DB->get_record('block_rlms_ntf', array('name' => 'notify_on_course_enroll_student'), '*', MUST_EXIST);
            if ($new_notify) {
                $no_setting_new_notify = $DB->get_record('block_rlms_ntf_settings', array('notification_id' => $new_notify->id), '*');

                if (!$no_setting_new_notify) {
                    $data2 = new stdClass();
                    $data2->course_id = $course_id;
                    $data2->notification_id = $new_notify->id;
                    $data2->enabled = 0;
                    $data2->config = $new_notify->config;
                    $data2->template = $new_notify->template;
                    $DB->insert_record('block_rlms_ntf_settings', $data2);
                    $records = $DB->get_records_sql($sql);
                }
            }
        }

        //$mform->addElement( 'html', '<style>.collapsible-actions{display:none;}</style><a href="#" id="header-legend-template" class="fheader" role="button" aria-controls="" aria-expanded="true" id=""><legend class="ftoggler"><span class="ftoggler fa fa-caret-right" id="header-legend-template-id"></span>Templates</legend></a><div class="collapsed_templates_course_notification">');
        $mform->addElement('hidden', 'id', $course_id, array('id' => 'cid'));
        $mform->addElement('hidden', 'editnotify', '1');
        $mform->addElement('header', 'hidde-id-element', 'hidde', 'block_rlms_notifications');
        
        foreach ($records as $record) {
            // Fields for editing HTML block title and contents.
            $prefix = md5($record->name);
            $BlockName = str_replace(' ', '_', $record->name);
            /**
             * 
             * Disable the Enrollment Reminder Notification to Student tab
             * @author Dnyaneshwar K
             * @since 10-06-2019
             * 
             */
            if ($BlockName == 'notify_user_not_logged_in') {
                continue;
            }
            
            $mform->addElement('header', 'configheader' . $record->name, get_string($record->name, 'block_rlms_notifications'), array('class' => 'header-notification'));

            if ($BlockName == 'notify_on_course_completion_student') {
                $helptext = "Course completion notification to student";
            } elseif ($BlockName == 'notify_on_course_completion_teacher') {
                $helptext = "Course completion notification to teacher";
            } elseif ($BlockName == 'notify_user_not_logged_in') {
                $helptext = "Enrollment Reminder Notification to Student";
            } elseif ($BlockName == 'notify_student_before_course_expiration') {
                $helptext = "Notification to student before course expires";
            } elseif ($BlockName == 'notify_on_course_enroll_student') {
                $helptext = "Course Enroll Notification to Student";
            } elseif ($BlockName == 'notify_on_enrollment_expire') {
                $helptext = get_string('notify_on_enrollment_expire', 'block_rlms_notifications');
            }
            $checked = false;
            if ($record->enabled) {
                $checked = true;
            }
            
            $checkbox = \theme_remui\widget::checkbox(get_string('enabled', 'block_rlms_notifications'), $checked, 'id_enabled_' . $BlockName, 'enabled_' . $BlockName, false, ['class' => 'col-md-6']);
            $mform->addElement('html', $checkbox);

            // the specific configuration for this notifications
            if (!empty($record->config)) {
                $config = @json_decode($record->config, true);
                if ($config) {
                    foreach ($config as $key => $value) {
                        $attributes = array();
                        $element = $mform->addElement('text', "config_{$BlockName}_{$key}", get_string($key, 'block_rlms_notifications'), $attributes);
                        $mform->addHelpButton("config_{$BlockName}", 'text_input_' . $BlockName, 'block_rlms_notifications');
                        $element->setValue($value);
                    }
                }
            }

            $element = $mform->addElement('editor', "template_{$BlockName}", get_string('template', 'block_rlms_notifications'));

            /**
             * @description: Add validation to allow show the help button only
             * on enrol notification
             *
             * @author Esteban E.
             * @since Dic 29 of 2015
             * @rlms
             */
            $mform->addHelpButton("template_{$BlockName}", 'email_template_notify_' . $BlockName, 'block_rlms_notifications');

            /**
             * @description: if template saved in DB it's NO TEMPLATE YET
             * then replace that text with default templates.
             * @author Esteban E.
             * @since March 31 of 2016
             * @rlms
             */
            if ($record->template == 'NO TEMPLATE YET') {
                $templatevalue = get_string('default_template_' . $BlockName, 'block_rlms_notifications');
            } else {
                $templatevalue = $record->template;
            }

            $mform->setDefault("template_{$BlockName}", array('text' => $templatevalue));
        }

        $backtocourse = $CFG->wwwroot . '/course/view.php?id=' . $course_id;
        $this->add_action_buttons(true, get_string('savechanges'), $backtocourse);
    }

    public function add_action_buttons($cancel = true, $submitlabel = false, $urlback = null) {
        $mform = & $this->_form;
        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        $buttonarray[] = &$mform->createElement('button', 'cancelbutton', get_string('back_to_course', 'block_rlms_notifications'), array('onclick' => "location.href='" . $urlback . "'"));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

}
