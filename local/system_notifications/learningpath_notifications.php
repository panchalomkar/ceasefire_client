<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();

    require_once($CFG->dirroot . '/lib/formslib.php');

    class learningpathnotificationform extends moodleform {

        function definition() {
            global $USER, $CFG, $COURSE;

            $mform =& $this->_form;

            $strgeneral  = get_string('general');
            $strrequired = get_string('required');

        /// Add some extra hidden fields
            $mform->addElement('hidden', 's', 'ntf');
            $mform->setType('s', PARAM_TEXT);
            $mform->addElement('hidden', 'section', 'admn');
            $mform->setType('section', PARAM_TEXT);

            $mform->addElement('header', 'notifications', get_string('notificationssettings', 'local_elisprogram'));

            $classenrol = array();
            $classenrol[] =& $mform->createElement('checkbox', 'notify_classenrol_user', '', get_string('notifications_notifyuser', 'local_elisprogram'));
            $a = '"'.get_string('notify_classenrol', 'local_elisprogram').'"';
            $classenrol[] =& $mform->createElement('checkbox', 'notify_classenrol_role', '', get_string('notifications_notifyrole', 'local_elisprogram', $a));
            $classenrol[] =& $mform->createElement('checkbox', 'notify_classenrol_supervisor', '', get_string('notifications_notifysupervisor', 'local_elisprogram', $a));
            $mform->addGroup($classenrol, 'classenrol', get_string('notify_classenrol', 'local_elisprogram'), '<br />', false);

            $mform->addElement('textarea', 'notify_classenrol_message', get_string('notifyclassenrolmessage', 'local_elisprogram'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_classenrol_message', PARAM_CLEAN);
            $mform->setDefault('notify_classenrol_message', get_string('notifyclassenrolmessagedef', 'local_elisprogram'));

            $mform->addElement('static', 'spacer', '', '');

            $classcompl = array();
            $classcompl[] =& $mform->createElement('checkbox', 'notify_classcompleted_user', '', get_string('notifications_notifyuser', 'local_elisprogram'));
            $a = '"'.get_string('notify_classcomplete', 'local_elisprogram').'"';
            $classcompl[] =& $mform->createElement('checkbox', 'notify_classcompleted_role', '', get_string('notifications_notifyrole', 'local_elisprogram', $a));
            $classcompl[] =& $mform->createElement('checkbox', 'notify_classcompleted_supervisor', '', get_string('notifications_notifysupervisor', 'local_elisprogram', $a));
            $mform->addGroup($classcompl, 'classcompl', get_string('notify_classcomplete', 'local_elisprogram'), '<br />', false);

            $mform->addElement('textarea', 'notify_classcompleted_message', get_string('notifyclasscompletedmessage', 'local_elisprogram'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_classcompleted_message', PARAM_CLEAN);
            $mform->setDefault('notify_classcompleted_message', get_string('notifyclasscompletedmessagedef', 'local_elisprogram'));

            $mform->addElement('static', 'spacer', '', '');

            $classnotst = array();
            $classnotst[] =& $mform->createElement('checkbox', 'notify_classnotstarted_user', '', get_string('notifications_notifyuser', 'local_elisprogram'));
            $a = '"'.get_string('notify_classnotstart', 'local_elisprogram').'"';
            $classnotst[] =& $mform->createElement('checkbox', 'notify_classnotstarted_role', '', get_string('notifications_notifyrole', 'local_elisprogram', $a));
            $classnotst[] =& $mform->createElement('checkbox', 'notify_classnotstarted_supervisor', '', get_string('notifications_notifysupervisor', 'local_elisprogram', $a));
            $mform->addGroup($classnotst, 'classnotst', get_string('notify_classnotstart', 'local_elisprogram'), '<br />', false);

            $mform->addElement('textarea', 'notify_classnotstarted_message', get_string('notifyclassnotstartedmessage', 'local_elisprogram'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_classnotstarted_message', PARAM_CLEAN);
            $mform->setDefault('notify_classnotstarted_message', get_string('notifyclassnotstartedmessagedef', 'local_elisprogram'));

            $mform->addElement('text', 'notify_classnotstarted_days', get_string('notifyclassnotstarteddays', 'local_elisprogram'), 'size="4"');
            $mform->setType('notify_classnotstarted_days', PARAM_INT);
            $mform->setDefault('notify_classnotstarted_days', 10);

            $mform->addElement('static', 'spacer', '', '');

            $classnotcm = array();
            $classnotcm[] =& $mform->createElement('checkbox', 'notify_classnotcompleted_user', '', get_string('notifications_notifyuser', 'local_elisprogram'));
            $a = '"'.get_string('notify_classnotcomplete', 'local_elisprogram').'"';
            $classnotcm[] =& $mform->createElement('checkbox', 'notify_classnotcompleted_role', '', get_string('notifications_notifyrole', 'local_elisprogram', $a));
            $classnotcm[] =& $mform->createElement('checkbox', 'notify_classnotcompleted_supervisor', '', get_string('notifications_notifysupervisor', 'local_elisprogram', $a));
            $mform->addGroup($classnotcm, 'classnotcm', get_string('notify_classnotcomplete', 'local_elisprogram'), '<br />', false);

            $mform->addElement('textarea', 'notify_classnotcompleted_message', get_string('notifyclassnotcompletedmessage', 'local_elisprogram'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_classnotcompleted_message', PARAM_CLEAN);
            $mform->setDefault('notify_classnotcompleted_message', get_string('notifyclassnotcompletedmessagedef', 'local_elisprogram'));

            $mform->addElement('text', 'notify_classnotcompleted_days', get_string('notifyclassnotcompleteddays', 'local_elisprogram'), 'size="4"');
            $mform->setType('notify_classnotcompleted_days', PARAM_INT);
            $mform->setDefault('notify_classnotcompleted_days', 10);

            $mform->addElement('static', 'spacer', '', '');

            $currcompl = array();
            $currcompl[] =& $mform->createElement('checkbox', 'notify_curriculumcompleted_user', '', get_string('notifications_notifyuser', 'local_elisprogram'));
            $a = '"'.get_string('notify_curriculumcomplete', 'local_elisprogram').'"';
            $currcompl[] =& $mform->createElement('checkbox', 'notify_curriculumcompleted_role', '', get_string('notifications_notifyrole', 'local_elisprogram', $a));
            $currcompl[] =& $mform->createElement('checkbox', 'notify_curriculumcompleted_supervisor', '', get_string('notifications_notifysupervisor', 'local_elisprogram', $a));
            $mform->addGroup($currcompl, 'currcompl', get_string('notify_curriculumcomplete', 'local_elisprogram'), '<br />', false);

            $mform->addElement('textarea', 'notify_curriculumcompleted_message', get_string('notifycurriculumcompletedmessage', 'local_elisprogram'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_curriculumcompleted_message', PARAM_CLEAN);
            $mform->setDefault('notify_curriculumcompleted_message', get_string('notifycurriculumcompletedmessagedef', 'local_elisprogram'));

            $mform->addElement('static', 'spacer', '', '');


            $currncompl = array();
            $currncompl[] =& $mform->createElement('checkbox', 'notify_curriculumnotcompleted_user', '', get_string('notifications_notifyuser', 'local_elisprogram'));
            $a = '"'.get_string('notify_curriculumnotcomplete', 'local_elisprogram').'"';
            $currncompl[] =& $mform->createElement('checkbox', 'notify_curriculumnotcompleted_role', '', get_string('notifications_notifyrole', 'local_elisprogram', $a));
            $currncompl[] =& $mform->createElement('checkbox', 'notify_curriculumnotcompleted_supervisor', '', get_string('notifications_notifysupervisor', 'local_elisprogram', $a));
            $mform->addGroup($currncompl, 'currncompl', get_string('notify_curriculumnotcomplete', 'local_elisprogram'), '<br />', false);

            $mform->addElement('textarea', 'notify_curriculumnotcompleted_message', get_string('notifycurriculumnotcompletedmessage', 'local_elisprogram'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_curriculumnotcompleted_message', PARAM_CLEAN);
            $mform->setDefault('notify_curriculumnotcompleted_message', get_string('notifycurriculumnotcompletedmessagedef', 'local_elisprogram'));

            $mform->addElement('text', 'notify_curriculumnotcompleted_days', get_string('notifycurriculumnotcompleteddays', 'local_elisprogram'), 'size="4"');
            $mform->setType('notify_curriculumnotcompleted_days', PARAM_INT);
            $mform->setDefault('notify_curriculumnotcompleted_days', 10);

            $mform->addElement('static', 'spacer', '', '');


            $trackassign = array();
            $trackassign[] =& $mform->createElement('checkbox', 'notify_trackenrol_user', '', get_string('notifications_notifyuser', 'local_elisprogram'));
            $a = '"'.get_string('notify_trackenrol', 'local_elisprogram').'"';
            $trackassign[] =& $mform->createElement('checkbox', 'notify_trackenrol_role', '', get_string('notifications_notifyrole', 'local_elisprogram', $a));
            $trackassign[] =& $mform->createElement('checkbox', 'notify_trackenrol_supervisor', '', get_string('notifications_notifysupervisor', 'local_elisprogram', $a));
            $mform->addGroup($trackassign, 'trackassign', get_string('notify_trackenrol', 'local_elisprogram'), '<br />', false);

            $mform->addElement('textarea', 'notify_trackenrol_message', get_string('notifytrackenrolmessage', 'local_elisprogram'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_trackenrol_message', PARAM_CLEAN);
            $mform->setDefault('notify_trackenrol_message', get_string('notifytrackenrolmessagedef', 'local_elisprogram'));

            $mform->addElement('static', 'spacer', '', '');

            $courserecur = array();
            $courserecur[] =& $mform->createElement('checkbox', 'notify_courserecurrence_user', '', get_string('notifications_notifyuser', 'local_elisprogram'));
            $a = '"'.get_string('notify_courserecurrence', 'local_elisprogram').'"';
            $courserecur[] =& $mform->createElement('checkbox', 'notify_courserecurrence_role', '', get_string('notifications_notifyrole', 'local_elisprogram', $a));
            $courserecur[] =& $mform->createElement('checkbox', 'notify_courserecurrence_supervisor', '', get_string('notifications_notifysupervisor', 'local_elisprogram', $a));
            $mform->addGroup($courserecur, 'courserecur', get_string('notify_courserecurrence', 'local_elisprogram'), '<br />', false);

            $mform->addElement('textarea', 'notify_courserecurrence_message', get_string('notifycourserecurrencemessage', 'local_elisprogram'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_courserecurrence_message', PARAM_CLEAN);
            $mform->setDefault('notify_courserecurrence_message', get_string('notifycourserecurrencemessagedef', 'local_elisprogram'));

            $mform->addElement('text', 'notify_courserecurrence_days', get_string('notifycourserecurrencedays', 'local_elisprogram'), 'size="4"');
            $mform->setType('notify_courserecurrence_days', PARAM_INT);
            $mform->setDefault('notify_courserecurrence_days', 10);

            $mform->addElement('static', 'spacer', '', '');

            $currrecur = array();
            $currrecur[] =& $mform->createElement('checkbox', 'notify_curriculumrecurrence_user', '', get_string('notifications_notifyuser', 'local_elisprogram'));
            $a = '"'.get_string('notify_curriculumrecurrence', 'local_elisprogram').'"';
            $currrecur[] =& $mform->createElement('checkbox', 'notify_curriculumrecurrence_role', '', get_string('notifications_notifyrole', 'local_elisprogram', $a));
            $currrecur[] =& $mform->createElement('checkbox', 'notify_curriculumrecurrence_supervisor', '', get_string('notifications_notifysupervisor', 'local_elisprogram', $a));
            $mform->addGroup($currrecur, 'curriculumrecur', get_string('notify_curriculumrecurrence', 'local_elisprogram'), '<br />', false);

            $mform->addElement('textarea', 'notify_curriculumrecurrence_message', get_string('notifycurriculumrecurrencemessage', 'local_elisprogram'),
                               'wrap="virtual" rows="5" cols="40"');
            $mform->setType('notify_curriculumrecurrence_message', PARAM_CLEAN);
            $mform->setDefault('notify_curriculumrecurrence_message', get_string('notifycurriculumrecurrencemessagedef', 'local_elisprogram'));

            $mform->addElement('text', 'notify_curriculumrecurrence_days', get_string('notifycurriculumrecurrencedays', 'local_elisprogram'), 'size="4"');
            $mform->setType('notify_curriculumrecurrence_days', PARAM_INT);
            $mform->setDefault('notify_curriculumrecurrence_days', 10);

            $mform->addElement('static', 'spacer', '', '');

            $notifyusers = array();
            $notifyusers[] =& $mform->createElement('advcheckbox', 'notify_addedtowaitlist_user', '', get_string('notifications_waitlist_added', 'local_elisprogram'));
            $notifyusers[] =& $mform->createElement('advcheckbox', 'notify_enroledfromwaitlist_user', '', get_string('notifications_waitlist_enroled', 'local_elisprogram'));
            $notifyusers[] =& $mform->createElement('advcheckbox', 'notify_incompletecourse_user', '', get_string('notifications_coursenotcomplete', 'local_elisprogram'));
            $mform->addGroup($notifyusers, 'notifyusers', get_string('notify_users', 'local_elisprogram'), '<br />', false);
            $mform->setDefault('notify_addedtowaitlist_user', true);
            $mform->setDefault('notify_enroledfromwaitlist_user', true);
            $mform->setDefault('notify_incompletecourse_user', true);

            $mform->addElement('static', 'spacer', '', '');

            $this->add_action_buttons();
        }
    }
