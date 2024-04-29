<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
defined('MOODLE_INTERNAL') || die();
    require_once($CFG->dirroot . '/lib/formslib.php');
    class system_notifications_form extends moodleform {
        function definition() {
            global $USER, $CFG, $COURSE;
            $mform =& $this->_form;
        /// Add some extra hidden fields
            $mform->addElement('hidden', 's', 'ntf');
            $mform->setType('s', PARAM_TEXT);
            $mform->addElement('hidden', 'section', 'admn');
            $mform->setType('section', PARAM_TEXT);
            $mform->addElement('header', 'notifications', get_string('notificationssettings', 'local_system_notifications'));
            $classenrol = array();
            $classenrol[] =& $mform->createElement('checkbox', 'enable_notify_not_logged_users', '', get_string('enable_notification','local_system_notifications'));
            $mform->addGroup($classenrol, 'sysnotify1', get_string('notify_sysnotify1', 'local_system_notifications'), '<br />', false);
            $mform->addElement('text', 'notify_not_logged_users_days', get_string('notify_not_logged_users_days', 'local_system_notifications'));
            $mform->setType('notify_not_logged_users_days', PARAM_CLEAN);
            $mform->setDefault('notify_not_logged_users_days', 15);
            
            $mform->addElement('editor', 'notify_not_logged_users_message', get_string('notify_not_logged_users_message', 'local_system_notifications'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_not_logged_users_message', PARAM_CLEAN);
            $mform->setDefault("notify_not_logged_users_message", array('text' => get_string('notify_not_logged_users_message_def', 'local_system_notifications')));
            
            $mform->addElement('static', 'spacer', '', '');
            $this->add_action_buttons();
        }
    }