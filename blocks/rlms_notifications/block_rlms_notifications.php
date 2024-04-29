<?php

class block_rlms_notifications extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_rlms_notifications');
    }

    function has_config() {
        return true;
    }

    function after_install() {
        
    }

    function before_delete() {
        
    }

    function applicable_formats() {
        return array('course-view' => true);
    }

    //***************************************************
    // Configurations
    //***************************************************
    function specialization() {
        global $COURSE;
        return;
        $Course = new Course();

        // if the course has not been registered so far
        // then register the course and set the starting time
        // for notifications
        if (!$Course->is_registered($COURSE->id)) {
            $Course->register($COURSE->id, time());
        }

        // intialize logs; perform this operation just once
        if (!$Course->log_exists($COURSE->id)) {
            $Course->initialize_log($COURSE->id);
        }
    }

    function instance_allow_config() {
        return true;
    }

    function instance_config_save($data, $nolongerused = false) {
        global $COURSE, $DB;

        $settings = array();

        // lets find all the values for the settings
        foreach ($_POST as $key => $value) {

            // the name of the field is like this: enabled_1, enabled_2, enabled_3 (the number is the id of the row in the block_rlms_ntf table)
            if ('enabled_' == substr($key, 0, 8)) {
                $id = str_replace('enabled_', '', $key);
                $settings[$id]['enabled'] = $value;
            }

            // the name of the field is like this: template_1, template_2, template_3 (the number is the id of the row in the block_rlms_ntf table)
            if ('template_' == substr($key, 0, 9)) {
                $id = str_replace('template_', '', $key);
                $settings[$id]['template'] = $value['text'];
            }

            // the name of the field is like this: 'config_days_after_enroled_3, (the number is the id of the row in the block_rlms_ntf table)
            if ('config_' == substr($key, 0, 7)) {
                $config = str_replace('config_', '', $key);
                $exploded = explode('_', $config);
                $id = end($exploded);
                array_pop($exploded);

                $configKey = implode('_', $exploded);

                $settings[$id]['config'][$configKey] = $value;
            }
        }

        foreach ($settings as $key => $item) {
            $data = new stdClass();
            $data->id = $key;
            $data->enabled = $item['enabled'];
            $data->template = $item['template'];

            if ($item['config']) {
                $data->config = json_encode($item['config']);
            }

            try {
                $DB->update_record('block_rlms_ntf_settings', $data);
            } catch (exception $e) {
                ;
            }
        }

        return true;
    }

    function get_content() {
        global $COURSE;
        global $USER;
        global $PAGE;
        global $CFG;
        global $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $canEdit = is_siteadmin();
        $this->content = new stdClass();
        if ($COURSE->id == "1") {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($COURSE->id);
        }

        // if not a site admin, then check if the user have teacher role
        // within the current course
        if (!$canEdit) {

            if ($roles = get_user_roles($context, $USER->id)) {
                foreach ($roles as $role) {
                    $canEdit = ('editingteacher' == $role->shortname);
                    $canEdit = $canEdit || in_array($role->shortname, array('teacher','manager','companymanager'));

                    // if canEdit == true then we don't need to keep the loop
                    if ($canEdit) {
                        break;
                    }
                }
            }
        }

        if ($canEdit && substr($PAGE->pagetype, 0, 11) == 'course-view') {

            /**
             * @description: Show notifications per rlms settings
             * @author Carlos Alcaraz.
             * @since March 09 of 2017
             * @rlms
             */
            $sql = "SELECT n.name, s.id
            FROM {block_rlms_ntf_settings} AS s INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id 
            WHERE s.course_id = '{$COURSE->id}' AND s.enabled = 1";

            // get enable settings
            $settings = $DB->get_records_sql($sql);
            $la_index = array();
            // number of record per setting
            $li_records_per_setting = 15;

            // last notification info
            $this->content->text = '';

            if (count($settings) > 0) {
                $this->content->text .= '<div style="font-size:16px"><strong>' . get_string('last_notifications', 'block_rlms_notifications') . '</strong></div><br>';

                foreach ($settings as $setting) {
                    $sql = "SELECT l.id, CONCAT(u.firstname,' ',u.lastname) as fullname, l.*,n.name
                    FROM {block_rlms_ntf_log} AS l
                    INNER JOIN {block_rlms_ntf_settings} AS s ON s.id = l.settings_id AND l.courseid = s.course_id
                    INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id 
                    INNER JOIN {user} AS u ON l.userid = u.id WHERE s.course_id = '{$COURSE->id}' AND s.id = {$setting->id} ORDER BY l.id DESC LIMIT " . $li_records_per_setting;

                    $records = $DB->get_records_sql($sql);

                    if (count($records)) {
                        $this->content->text .= '<dl style="font-size:13px" class="col-lg-12" >';
                        $this->content->text .= '<dt>' . get_string($setting->name, 'block_rlms_notifications') . '</dt>';

                        foreach ($records as $record) {
                            $status = (1 == $record->status) ? '<span class="label label-success">success</span>' : '<span class="label label-danger">failed</span>';
                            $this->content->text .= '<dd><div>' . $record->fullname . '</div>' . date("M j, Y @ h:i:s a", strtotime($record->created_on)) . ' - ' . $status . '<hr style="color:#fff;padding:0px;margin:4px 0px;"></dd>';
                        }
                        $this->content->text .= '</dl>';
                    }
                }
            } else {
                $this->content->text .= '<span class="label label-info">No records found.</span>';
            }

            $this->content->text .= '<br /><br /><div style="clear:both;display:block;"></div>';
            //

            $url = $CFG->wwwroot . "/local/system_notifications/system_notifications.php?id=" . $COURSE->id;
            
            $plugins = core_plugin_manager::instance()->get_plugins_of_type('local');
            // If the config plugin "system_notifications" is installed we can configure the templates
            if (array_key_exists('system_notifications', $plugins)) {
                $content = html_writer::tag('i', '', ['class' => 'fa fa-cog']) . get_string('edit_template_notification', 'block_rlms_notifications');
                $this->content->text .= html_writer::tag('button', $content, ['class' => 'btn btn-primary btn-round edit-button', 'type' => 'button', 'onclick' => 'window.location.href = "' . $url . '"']);
                $this->content->text .= html_writer::end_tag('button');
                // $this->content->text.= ": ".date("j M Y G:i:s", $course_registration->last_notification_time);
                $this->content->text .= '<br />';
            }else{
            $this->content->text .= get_string('sys_not_installed','block_rlms_notifications');
            }
        } else {
            $this->title = '';
            return $this->content = "";
        }
        $this->content->footer = '';
        return $this->content;
    }


}
