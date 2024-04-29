<?php

class notify_cron {

    public function __construct() {
        
    }

    function render_mail($data, $template) {
        if (preg_match_all("/\{([a-z]{1,})\}/i", $template, $la_result)) {
            if (count($la_result) > 0) {
                foreach (array_values($la_result[1]) as $lc_Attribute) {
                    if (property_exists($data, $lc_Attribute)) {
                        $template = preg_replace("/\{" . $lc_Attribute . "\}/", $data->{$lc_Attribute}, $template);
                    }
                }
            }
        }
        return $template;
    }


    function replace_tags($string, $settings = []) {
        global $DB;
        if (!empty($settings)) {
            /* we get all the tags string */
            $data_tags = $this->get_string_between($string, '{', '}');
            foreach ($data_tags as $tag) {
                $pos = strpos($tag, '_');
                $model = '';
                $property = '';
                if ($pos !== false) {
                    $property = strip_tags(substr($tag, $pos + 1, strlen($tag))); 
                    $model = strip_tags(substr($tag, 0, $pos)); 
                    if ($model == 'user' && $property == 'fullname') {
                        $settings[$model][$property] = $settings[$model]['firstname'] . ' ' . $settings[$model]['lastname'];
                    } elseif ($model == 'learningpath' && ($property == 'startdate' || $property == 'enddate')) {
                        $settings[$model][$property] = isset($settings[$model][$property]) ? userdate($settings[$model][$property], '%m/%d/%Y') : get_string('notset', 'local_learningpaths');
                    } elseif ($model == 'learningpath' && $property == 'name') {
                        $settings[$model][$property] = $settings[$model][$property];
                    } elseif ($model == 'learningpath' && $property == 'link') {
                        $link =new moodle_url('/local/learningpaths/view.php', array('id' => $settings['learningpath']['id']));
                        $settings[$model][$property] = $link;
                    } elseif ($model == 'learningpath' && $property == 'coursesrequired') {
                        $settings[$model][$property] = $DB->count_records_sql('SELECT count(*) FROM {course}
                                    INNER JOIN {learningpath_courses}
                                    ON {learningpath_courses}.courseid = {course}.id
                                    WHERE {learningpath_courses}.learningpathid = ? AND {learningpath_courses}.required = 1
                                ', [$settings[$model]['id']]);
                    }
                }
                if (!empty($model) && !empty($property) && array_key_exists($model, $settings)) {
                    $string = str_replace('{' . $tag . '}', $settings[$model][$property], $string);
                }
            }
        }
        return $string;
    }

    function get_string_between($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        $array_tags = [];
        if ($ini == 0)
            return $array_tags;
        $exist_data = $ini;
        while ($exist_data != '') {
            $ini += strlen($start);
            $len = strpos($string, $end, $ini) - $ini;
            $str_tmp = substr($string, $ini, $len);
            $array_tags[] = $str_tmp;
            $string = str_replace($start . $str_tmp . $end, '', $string);
            $exist_data = strpos($string, $start);
        }
        return $array_tags;
    }


    function send_mail_user($body = null, $subject = null, $obj = array()) {
        global $USER;
        $res = false;
        if (array_key_exists('user', $obj) && $obj['user']['email']) {
            // Render template
            $body = $this->replace_tags($body, $obj);
            $subject = (empty($subject)) ? get_string('default_lp_notification_subject', 'local_learningpaths') : $subject;
            try {
                $site = get_site();
                $res = email_to_user((object) $obj['user'], $site->shortname, $subject, $body, $body);
                message_post_message($USER, (object) $obj['user'], $body, FORMAT_MOODLE);
            } catch (Exception $e) {
                ddd($e);
            }
        }
        return $res;
    }

    function run($data) {
        // Call the method related with the type
        if (method_exists($this, $data->type)) {
            call_user_func(array($this, $data->type), $data);
        }
    }


    function enreminder($data) {
        global $DB, $CFG, $USER;
        require_once "{$CFG->dirroot}/local/learningpaths/classes/objects/LearningPath.php";
        // Get the config of the notification
        $config = json_decode($data->config, true);
        // Student reminder enabled
        $enable_reminder = (array_key_exists('enreminder_editor_checkbox1', $config) && $config['enreminder_editor_checkbox1']);
        // Days to send notification if greater than 0
        $enable_days = (array_key_exists('enreminder_editor_text', $config) && (int) $config['enreminder_editor_text'] > 0);
        $learningpath = new LearningPath($data->learningpathid);

        if ($enable_reminder && $enable_days) {
            // Get the days
            $daysafter = (int) $config['enreminder_editor_text'];

            // Get the users where the notification has not been sent
            $users = $this->get_users_enrolled($data->learningpathid);
            foreach ($users as $key => $user) {
                // Check if the notification has been sent to this user -> this notification.
                $noti_log = $DB->get_record('learningpath_noti_log', array('notificationid' => $data->id, 'userid' => $key));
                $exists = false;
                if ($noti_log) {
                    if (property_exists($noti_log, 'sent') && $noti_log->sent) {
                        continue;
                    }
                    $exists = true;
                }
                // Check if the enrollment date is not empty to validate with the $daysafter

                if (!empty($user->enrollment_date)) {
                    // Get the difference between the enroll time and the current time
                    $datediff = date_diff(date_create(date('Y-m-d h:i:s', $user->enrollment_date)), date_create(date('Y-m-d h:i:s')))->format("%R%a");
                    // If the difference is greater or equal to the days set in the form, send notification.
                    if ($datediff >= $daysafter) {
                        $sent = $this->send_mail_user($config['enreminder_editor']['text'], get_string('enreminder_subject', 'local_learningpaths', $learningpath->data), ['user' => (array) $user,'learningpath' => (array) $learningpath->data]);
                        message_post_message($USER, (array) $user, $config['enreminder_editor']['text'], FORMAT_MOODLE);
                        $noti_log->notificationid = $data->id;
                        $noti_log->userid = $key;
                        $noti_log->sent = (int) $sent;
                        $noti_log->datesent = ($sent) ? time() : '';
                        if ($exists) {
                            $DB->update_record('learningpath_noti_log', $noti_log);
                        } else {
                            $DB->insert_record('learningpath_noti_log', $noti_log);
                        }
                    }
                }
            }
        }
    }

    /**
     * Method related with the Expiration reminder
     *
     * Iterate all the users in the learning path and sends them the notification in case it hasn't been sent already
     *
     * @author    Daniel C
     * @since    12-04-2018
     * @param1    object $data Object with the info of the record stored in the {learningpath_notifications}.
     * @return    none
     * @rlms
     */
    function expiration($data) {
        global $DB, $CFG, $USER;
        require_once "{$CFG->dirroot}/local/learningpaths/classes/objects/LearningPath.php";
        // Get the config of the notification
        $config = json_decode($data->config, true);
        // Student reminder enabled
        $enable = (array_key_exists('expiration_editor_checkbox1', $config) && $config['expiration_editor_checkbox1']);
        // Check if the Learning Path is expired
        $learningpath = new LearningPath($data->learningpathid);
        $enddate = (int) $learningpath->data->enddate;
        if ($enable && $enddate > 0 && time() >= $enddate) {
            // Get the users
            $users = $this->get_users_enrolled($data->learningpathid);
            foreach ($users as $key => $user) {
                // Check if the notification has been sent to this user -> this notification.
                $noti_log = $DB->get_record('learningpath_noti_log', array('notificationid' => $data->id, 'userid' => $key));
                $exists = false;
                if ($noti_log) {
                    if (property_exists($noti_log, 'sent') && $noti_log->sent) {
                        continue;
                    }
                    $exists = true;
                }
                $sent = $this->send_mail_user($config['expiration_editor']['text'], get_string('expiration_subject', 'local_learningpaths', $learningpath->data), ['user' => (array) $user,'learningpath' => (array) $learningpath->data]);
                message_post_message($USER, (array) $user, $config['expiration_editor']['text'], FORMAT_MOODLE);
                $noti_log->notificationid = $data->id;
                $noti_log->userid = $key;
                $noti_log->sent = (int) $sent;
                $noti_log->datesent = ($sent) ? time() : '';
                if ($exists) {
                    $DB->update_record('learningpath_noti_log', $noti_log);
                } else {
                    $DB->insert_record('learningpath_noti_log', $noti_log);
                }
            }
        }
    }

    /**
     * Method related with the Expiration reminder
     *
     * Iterate all the users in the learning path and sends them the notification in case it hasn't been sent already
     *
     * @author    Daniel C
     * @since    16-04-2018
     * @param1    object $data Object with the info of the record stored in the {learningpath_notifications}.
     * @return    none
     * @rlms
     */
    function exreminder($data) {
        global $DB, $CFG, $USER;
        require_once "{$CFG->dirroot}/local/learningpaths/classes/objects/LearningPath.php";
        // Get the config of the notification
        $config = json_decode($data->config, true);
        // Student reminder enabled
        $enable = (array_key_exists('exreminder_editor_checkbox1', $config) && $config['exreminder_editor_checkbox1']);
        // Check if the Learning Path is expired
        $learningpath = new LearningPath($data->learningpathid);
        $enddate = (int) $learningpath->data->enddate;
        $enable_days = (array_key_exists('exreminder_editor_text', $config) && (int) $config['exreminder_editor_text'] > 0);
        $datediff = date_diff(date_create(date('Y-m-d h:i:s', $enddate)), date_create(date('Y-m-d h:i:s')))->format("%R%a");
        if ($enable && $enable_days && $enddate > 0 && $datediff >= (int) $config['exreminder_editor_text']) {
            // Get the users
            $users = $this->get_users_enrolled($data->learningpathid);
            foreach ($users as $key => $user) {
                // Check if the notification has been sent to this user -> this notification.
                $noti_log = $DB->get_record('learningpath_noti_log', array('notificationid' => $data->id, 'userid' => $key));
                $exists = false;
                if ($noti_log) {
                    if (property_exists($noti_log, 'sent') && $noti_log->sent) {
                        continue;
                    }
                    $exists = true;
                }
                $sent = $this->send_mail_user($config['exreminder_editor']['text'], get_string('exreminder_subject', 'local_learningpaths', $learningpath->data), ['user' => (array) $user,'learningpath' => (array) $learningpath->data]);
                message_post_message($USER, (array) $user, $config['exreminder_editor']['text'], FORMAT_MOODLE);
                $noti_log->notificationid = $data->id;
                $noti_log->userid = $key;
                $noti_log->sent = (int) $sent;
                $noti_log->datesent = ($sent) ? time() : '';
                if ($exists) {
                    $DB->update_record('learningpath_noti_log', $noti_log);
                } else {
                    $DB->insert_record('learningpath_noti_log', $noti_log);
                }
            }
        }
    }

    /**
     * Method related with the Expiration reminder
     *
     * Iterate all the users in the learning path and sends them the notification in case it hasn't been sent already
     *
     * @author    Daniel C
     * @since    16-04-2018
     * @param1    object $data Object with the info of the record stored in the {learningpath_notifications}.
     * @return    none
     * @rlms
     */
    function path_com($data) {
        global $DB, $CFG, $USER;
        require_once "{$CFG->dirroot}/blocks/rlms_lpd/lib/lib.php";
        require_once "{$CFG->dirroot}/local/learningpaths/classes/objects/LearningPath.php";
        // Get the config of the notification
        $config_block = get_config('block_rlms_lpd');
        $option = $config_block->evaluate_progress;
        $config = json_decode($data->config, true);
        // Student reminder enabled
        $enable = (array_key_exists('notifications_editor_checkbox1', $config) && $config['notifications_editor_checkbox1']);
        $learningpath = new LearningPath($data->learningpathid);
        if ($enable) {
            // Get the users
            $users = $this->get_users_enrolled($data->learningpathid);
            foreach ($users as $key => $user) {
                // Check if learning path is completed
                $lp_complete = (getLpProgress($data->learningpathid, $option, $user->id) == 100) ? true : false;
                if ($lp_complete) {
                    // Check if the notification has been sent to this user -> this notification.
                    $noti_log = $DB->get_record('learningpath_noti_log', array('notificationid' => $data->id, 'userid' => $key));
                    $exists = false;
                    if ($noti_log) {
                        if (property_exists($noti_log, 'sent') && $noti_log->sent) {
                            continue;
                        }
                        $exists = true;
                    }
                    $sent = $this->send_mail_user($config['notifications_editor']['text'], get_string('path_com_subject', 'local_learningpaths', $learningpath->data), ['user' => (array) $user,'learningpath' => (array) $learningpath->data]);
                    message_post_message($USER, (array) $user, $config['notifications_editor']['text'], FORMAT_MOODLE);
                    $noti_log->notificationid = $data->id;
                    $noti_log->userid = $key;
                    $noti_log->sent = (int) $sent;
                    $noti_log->datesent = ($sent) ? time() : '';
                    if ($exists) {
                        $DB->update_record('learningpath_noti_log', $noti_log);
                    } else {
                        $DB->insert_record('learningpath_noti_log', $noti_log);
                    }
                }
            }
        }
    }

    /**
     * List of users enrolled to a learningpath depending of its type {all, manual, cohort}
     *
     * @author    Daniel C
     * @since    10-04-2018
     * @param1    int $learningpathid ID of the learning path
     * @param2    string $type type of search all = users enrolled by themselves or manual and users of the cohort, manual = Only the users enrolled by themselves and manual, cohort = only users of the cohorts.
     * @return    array List of users
     * @rlms
     */
    function get_users_enrolled($learningpathid = null, $type = 'all') {
        $users = array();
        switch ($type) {
            case 'all':
                // Array merge
                $users = $this->get_enrolled_users_type($learningpathid, 'manual') + $this->get_enrolled_users_type($learningpathid, 'cohort');
                break;
            case 'manual':
                // Get manual users
                $users = $this->get_enrolled_users_type($learningpathid, 'manual');
                break;
            case 'cohort':
                // Get cohort users
                $users = $this->get_enrolled_users_type($learningpathid, 'cohort');
                break;
            default:
                break;
        }
        return $users;
    }

    /**
     * Get the list of users depending of the type
     *
     * @author    Daniel C
     * @since    10-04-2018
     * @param1    int $learningpathid ID of the learning path
     * @param2    string $type type of search all = users enrolled by themselves or manual and users of the cohort, manual = Only the users enrolled by themselves and manual, cohort = only users of the cohorts.
     * @return    array List of users
     * @rlms
     */
    function get_enrolled_users_type($lpid = null, $type = null) {
        global $DB;
        $data = array();
        $where = array('learningpathid' => $lpid);
        if ($lpid > 0 && $type != '') {
            $usersid = array();
            switch ($type) {
                case 'manual':
                    $enrollmentdate = '';
                    $lpusers = $DB->get_records('learningpath_users', $where);
                    if (!empty($lpusers)) {
                        foreach ($lpusers as $value) {
                            $usersid[] = $value->userid;
                            $enrollmentdate = $value->enrollment_date;
                        }
                        $usersid = implode(',', $usersid);
                        $sql = "SELECT U.*, $enrollmentdate as `enrollment_date` FROM {user} U WHERE id IN ($usersid)";
                        $data = $DB->get_records_sql($sql);
                    }
                    break;
                case 'cohort':
                    $cohorts = $DB->get_records('learningpath_cohorts', $where);
                    $enrollmentdate = '';
                    if (!empty($cohorts)) {
                        foreach ($cohorts as $key => $cohort) {
                            $enrollmentdate = $cohort->enrollment_date;
                            // Get cohort users
                            $sql = "SELECT u.*, $enrollmentdate as `enrollment_date`
                                FROM {cohort_members} cm
                                INNER JOIN {user} u ON cm.userid = u.id
                                WHERE cm.cohortid = ?";
                            $cohortmembers = $DB->get_records_sql($sql, array($cohort->cohortid));
                            if (empty($data)) {
                                $data = $cohortmembers;
                            } else {
                                foreach ($cohortmembers as $key => $value) {
                                    if (!array_key_exists($key, $data)) {
                                        $data[$key] = $value;
                                    }
                                }
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        }
        return $data;
    }

    function completion_reminder($data){
        global $DB, $CFG, $USER;
        require_once "{$CFG->dirroot}/local/learningpaths/classes/objects/LearningPath.php";
        // Get the config of the notification
        $config = json_decode($data->config, true);
        $config_block = get_config('block_rlms_lpd');
        $option = $config_block->evaluate_progress;
        // Student reminder enabled
        $enable = (array_key_exists('completion_reminder_editor_checkbox', $config) && $config['completion_reminder_editor_checkbox']);
        // Check if the Learning Path is expired
        $learningpath = new LearningPath($data->learningpathid);
        $enddate = (int) $learningpath->data->enddate;
        $enable_days = (array_key_exists('completion_reminder_editor_text', $config) && (int) $config['completion_reminder_editor_text'] > 0);
        if ($enable && $enable_days && $enddate > 0 && time() >= $enddate) {
            // Get the users
            $users = $this->get_users_enrolled($data->learningpathid);
            foreach ($users as $key => $user) {
                $lp_complete = (getLpProgress($data->learningpathid, $option, $user->id) == 100) ? true : false;
                if (!$lp_complete) {
                    // Check if the notification has been sent to this user -> this notification.
                    $noti_log = $DB->get_record('learningpath_noti_log', array('notificationid' => $data->id, 'userid' => $key));
                    $exists = false;
                    if ($noti_log) {
                        $datediff = date_diff(date_create(date('Y-m-d h:i:s', (int) $noti_log->datesent)), date_create(date('Y-m-d h:i:s')))->format("%R%a");
                        if (property_exists($noti_log, 'sent') && $noti_log->sent && $datediff < (int) $config['completion_reminder_editor_text']) {
                            continue;
                        }
                        $exists = true;
                    }
                    $sent = $this->send_mail_user($config['completion_reminder_editor']['text'], get_string('completion_reminder_subject', 'local_learningpaths', $learningpath->data), ['user' => (array) $user,'learningpath' => (array) $learningpath->data]);
                    message_post_message($USER, (array) $user, $config['completion_reminder_editor']['text'], FORMAT_MOODLE);
                    $noti_log->notificationid = $data->id;
                    $noti_log->userid = $key;
                    $noti_log->sent = (int) $sent;
                    $noti_log->datesent = ($sent) ? time() : '';
                    if ($exists) {
                        $DB->update_record('learningpath_noti_log', $noti_log);
                    } else {
                        $DB->insert_record('learningpath_noti_log', $noti_log);
                    }
                }
            }
        }
    }

}
