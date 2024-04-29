<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Return Html of training sessions
 */
function printtrainingsession($eventlist = []) {
    $output = "";


    $output .= html_writer::start_div('div', ['class' => 'text-left']);
    $output .= html_writer::tag('lebel', get_string('workingrule', 'local_system_notifications'), array('class' => 'addrule'));
    // $output .= html_writer::tag('hr',array('class'=>'addrule'));
    $output .= html_writer::end_tag('div');

    if (count($eventlist) != 0) {
        $output .= html_writer::start_div('div', ['class' => 'text-right']);
        $output .= html_writer::tag('a', html_writer::tag('i', '', ['class' => 'fa fa-plus-circle']), ['id' => 'add-event-rule', 'href' => '#', 'data-target' => "#new-event-popup", "data-toggle" => "modal"]);
        $output .= html_writer::end_tag('div');


        foreach ($eventlist as $tk) {
            $output .= html_writer::start_tag('div', ['class' => 'event-rule mar-btm bord-all', 'data-id' => $tk->id,]);
            $output .= html_writer::start_tag('div', ['class' => 'col-sm-6']);
            $output .= html_writer::tag('b', $tk->name);
            $output .= html_writer::end_tag('div');

            $output .= html_writer::start_tag('div', ['class' => 'col-sm-6 text-right']);
            $output .= html_writer::link('#', html_writer::tag('i', '', ['class' => 'fa fa-cog']), ['class' => 'edit-ruleevent tooltipelement', 'title' => 'Configure event rule', 'data-id' => $tk->id, 'data-action' => 'edit']);
            $output .= html_writer::link('#', html_writer::tag('i', '', ['class' => 'fa fa-trash-o']), ['class' => 'delete-eventrule tooltipelement', 'title' => 'Delete event rule', 'data-id' => $tk->id, 'data-action' => 'delete']);
            $output .= html_writer::end_tag('div');


            $output .= html_writer::tag('div', '', ['class' => 'clearfix']);
            $output .= html_writer::end_tag('div');
        }
    } else {

        $output .= html_writer::start_div('div', ['class' => 'text-center']);
        $output .= html_writer::tag('button', get_string('addnewrule', 'local_system_notifications'), ['class' => 'btn btn-primary', 'data-target' => "#new-event-popup", "data-toggle" => "modal"]);
        $output .= html_writer::end_tag('div');
    }

    return $output;
}

function eventRulestatus($status) {
    global $OUTPUT;
    $output = "";
    $courseid = optional_param('courseid', 0, PARAM_INT);
    $help = new help_icon('enablehelp', 'tool_monitor');
    // Display option to enable/disable the plugin.
    if ($status) {
        if (has_capability('tool/monitor:managetool', context_system::instance())) {
            // We don't need to show enabled status to everyone.
            $output .= html_writer::tag('span', get_string('monitorenabled', 'tool_monitor'), array('class' => 'link-info'));
            $disableurl = new moodle_url("", array('courseid' => $courseid, 'action' => 'changestatus', 'status' => 0, 'sesskey' => sesskey()));
            $output .= '   ' . html_writer::link($disableurl, get_string('disable'), array('class' => 'link-info', 'id' => 'eventmonitorenable', 'courseid' => $courseid, 'action' => 'changestatus', 'status' => 0, 'sesskey' => sesskey()));
            $output .= $OUTPUT->render($help);
        }
    } else {
        $output .= html_writer::tag('span', get_string('monitordisabled', 'tool_monitor'), array('class' => 'link-info'));
        if (has_capability('tool/monitor:managetool', context_system::instance())) {
            $enableurl = new moodle_url("", array('courseid' => $courseid, 'action' => 'changestatus', 'status' => 1, 'sesskey' => sesskey()));
            $output .= '   ' . html_writer::link($enableurl, get_string('enable'), array('class' => 'link-info', 'id' => 'eventmonitordisable', 'courseid' => $courseid, 'action' => 'changestatus', 'status' => 1, 'sesskey' => sesskey()));
            $output .= $OUTPUT->render($help);
        } else {
            $output .= '  ' . get_string('contactadmin', 'tool_monitor');
        }
    }
    return $output;
}

function get_tab_content(string $blockname = ''): string {
    global $DB, $CFG, $PAGE;
    $PAGE->requires->js_call_amd('local_system_notifications/course', 'init');
    // Requires
    require_once($CFG->dirroot.'/local/system_notifications/system_notifications_form.php');
    require_once($CFG->dirroot.'/blocks/rlms_notifications/classes/template_editForm.php');

    // Params
    $courseid = optional_param('id',0,PARAM_INT);
    $submitsave =  optional_param('editnotify','',PARAM_INT);
    $curid = $courseid;
    $sesskey =  optional_param('sesskey','',PARAM_ALPHA);
    
    
    
    /**
     * Get all the courses with or without notification settings 
     * @author Hugo S.
     * @since May 29 of 2018
     * @ticket 1626
     * @rlms
     */
    $sql = "
            SELECT
                c.id as course_id
                ,c.fullname,c.id
            FROM
                {course} AS c
            WHERE c.id != 1
            ORDER BY sortorder";
    $courses = $DB->get_records_sql($sql);
    
    $options = [];
    $selected = '';
    foreach ($courses as $course) {
        $options[$course->course_id] = $course->fullname;
        
        if ($course->course_id == $courseid) {
            $selected = $course->course_id;
        }
    }
    
    $output .= html_writer::start_div('row');
    $output .= \theme_remui\widget::select2(get_string('select_course', 'local_system_notifications'), $options, 'menucourse-selection', $selected, 'course-selection', false,['class' => 'course-dropdown']);
    $output .= html_writer::end_tag('div');

    $output .= html_writer::end_tag('br');


    $updatedone = 0;
    if (sesskey() && $submitsave <> '') {
        $data = new stdClass();
        foreach ($_REQUEST as $key => $value) {
            $data->$key = $value;
        }

        $records2 = $DB->get_records('block_rlms_ntf');


        foreach ($records2 as $record) {

            $data2 = new stdClass();
            $data2->course_id = $curid;
            $data2->notification_id = $record->id;


            $elementenabled_ = 'enabled_' . $record->name;
            if ($data->$elementenabled_) {
                $data2->enabled = 1; /* enable */
            } else {
                $data2->enabled = 0;
            }

            $elementtemplate_ = 'template_' . $record->name;
            $templateArray = $data->$elementtemplate_;
            $data2->template = $templateArray['text'];

            $elementconfig_ = 'config_' . $record->name;
            $object_vars = array_keys((array) $data);


            $_config_data = [];
            foreach ($object_vars as $var) {
                if (strpos($var, $elementconfig_) !== false) {
                    $attr = str_replace($elementconfig_ . "_", '', $var);
                    $_config_data[$attr] = $data->$var;
                }
            }


            if (!empty($_config_data)) {
                $data2->config = json_encode($_config_data); /* input text */
            }


            if (!$data2->template) {
                $data2->template = get_string('default_template_' . $record->name, 'block_rlms_notifications');
            }

            $settingdata = $DB->get_record('block_rlms_ntf_settings', array('course_id' => $data->id, 'notification_id' => $record->id));
            $data2->id = $settingdata->id;
            /**
              description:was changed redirect of /local/system_notifications/system_notifications.php for same with id
              @author Jorge M.
              @since May 02 of 2017
              @ticket 874
              @rlms
             */
            if ($DB->update_record('block_rlms_ntf_settings', $data2)) {

                if ($courseid > 0) {
                    $url = new moodle_url('/local/system_notifications/system_notifications.php', array('id' => $courseid));
                } else {
                    $url = new moodle_url('/local/system_notifications/system_notifications.php');
                }


                $updatedone = 1;
                $echo = '<div class="notifysuccess alert course-notify-saved">' . get_string('changessaved') . '</div>';
            }
        }
        /**
          description:was changed to save the settings
          @author Sunita
          @since May 11 of 2017
          @ticket #889
          @rlms
         */
        redirect($url, $echo);
    }


    if (isset($curid) && isset($sesskey)) {
        
        $newform =  new system_notifications_edit_form(null, array("course_id"=>$curid));
        $coursedata = $DB->get_record('course', array('id' => $curid));
        if ($updatedone == 1) {
            echo '<div class="notifysuccess alert course-notify-saved">' . get_string('changessaved') . '</div>';
        }

        $output .= html_writer::start_tag('div', array('id' => 'edit_template_course_notifications', 'class' => 'hide'));
        $output .= $newform->render();
        $output .= html_writer::end_tag('div');
    }
    return $output;
}
