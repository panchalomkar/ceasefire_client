<?php

/**
* Rendere for learning paths local plugin
*
* @package    local_learningpaths_renderer
* @author     Andres
*
*/
defined('MOODLE_INTERNAL') || die;

require_once "{$CFG->dirroot}/local/learningpaths/classes/forms/LearningPathForm.php";
require_once "{$CFG->dirroot}/local/learningpaths/classes/forms/AddCoursesForm.php";
require_once "{$CFG->dirroot}/local/learningpaths/classes/forms/ManageCoursesForm.php";
require_once "{$CFG->dirroot}/local/learningpaths/classes/forms/ManageCoursesPositionForm.php";
require_once "{$CFG->dirroot}/local/learningpaths/classes/forms/ManageCohortsForm.php";
//require_once "{$CFG->dirroot}/local/rlmslms/class/plms_form.php";
require_once "{$CFG->dirroot}/local/learningpaths/class/plms_form.php";
require_once "{$CFG->dirroot}/local/learningpaths/classes/forms/notifications_form.php";
require_once "{$CFG->dirroot}/local/learningpaths/lib.php";


class local_learningpaths_renderer extends plugin_renderer_base
{
    /*
    * Build a html with the general data of learning path like name, description, etc
    *
    * @param (data) general learning path record data from database
    */
    public function learningpath_view_tab($data)
    {
        // Require file library, this will used for get the learningpath image if exist
        global $CFG,$PAGE;
        require_once("{$CFG->dirroot}/lib/filelib.php");

        if (is_null($data->startdate))
            $data->startdate = get_string('startdate_undefined', 'local_learningpaths');

        if (is_null($data->enddate))
            $data->enddate = get_string('enddate_undefined', 'local_learningpaths');

        // Get files related with current learningpath
        $fs = get_file_storage();
        $files = $fs->get_area_files(1, 'local_learningpaths', 'image', $data->id);

        // Build HTML of this tab
        $output = "";
        $output .= html_writer::start_tag('div', array('class' => 'overview'));
           // $output .= html_writer::tag('h4', $data->name, array('class' => 'title'));

            // Name, image and description
            $output .= html_writer::start_tag('div', array('class' => 'row description'));
                $output .= html_writer::start_tag('div', array('class' => 'col-sm-4 img-course'));
                    $output .= html_writer::start_tag('div', array('class' => 'conten-img-lp'));    
                        if ($data->image)
                            $output .= html_writer::empty_tag('img', array('class' => 'img-responsive lp_img_description', 'src' => "{$CFG->wwwroot}/local/learningpaths/pluginfile.php?learningpathid={$data->id}&t=" . $data->image->get_timemodified()));
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
                
                //sales date
                $output .= html_writer::start_tag('div', array('class' => 'col-sm-8 conent-descriptionlp'));
                    $output .= html_writer::start_tag('div', array('class' => 'propierty-lp'));
                        $output .= html_writer::start_tag('div', array('class' => 'row propierty-first'));

                            $output .= html_writer::start_tag('div', array('class' => 'col-sm-3'));
                                $output .= html_writer::start_tag('span');
                                    $output .= html_writer::tag('i', '', array('class' => 'wid wid-datelp header-txtmen i-search'));
                                    $output .= html_writer::tag("p", get_string('startdate', 'local_learningpaths'));
                                    $startdate = ((int)$data->startdate)?date('m/d/Y', $data->startdate):get_string('notset', 'local_learningpaths') ;
                                    $endate = ((int)$data->enddate)?date('m/d/Y', $data->enddate):get_string('notset', 'local_learningpaths');
                                $output .= html_writer::end_tag('span');
                                $output .= html_writer::tag("span",  $startdate, array());
                            $output .= html_writer::end_tag('div');

                           $output .= html_writer::start_tag('div', array('class' => 'col-sm-3'));
                                $output .= html_writer::start_tag('span');
                                    $output .= html_writer::tag('i', '', array('class' => 'wid wid-datelp header-txtmen i-search'));
                                    $output .= html_writer::tag("p", get_string('enddate', 'local_learningpaths'));
                                $output .= html_writer::end_tag('span');
                                $output .= html_writer::tag("span",  $endate, array());
                            $output .= html_writer::end_tag('div');

                            $output .= html_writer::start_tag('div', array('class' => 'col-sm-3'));
                                $output .= html_writer::start_tag('span');
                                    $output .= html_writer::tag('i', '', array('class' => 'wid wid-courselp header-txtmen i-search'));
                                    $output .= html_writer::tag("p", get_string('total_users_', 'local_learningpaths'));
                                $output .= html_writer::end_tag('span');
                                $output .= html_writer::tag("span", $data->total_users, array());
                            $output .= html_writer::end_tag('div');

                            $output .= html_writer::start_tag('div', array('class' => 'col-sm-3'));
                                $output .= html_writer::start_tag('span');
                                    $output .= html_writer::tag('i', '', array('class' => 'wid wid-courselp header-txtmen i-search'));
                                    $output .= html_writer::tag("p", get_string('total_credits_', 'local_learningpaths'));
                                $output .= html_writer::end_tag('span');
                                $output .= html_writer::tag("span", $data->credits, array());
                            $output .= html_writer::end_tag('div');

                        $output .= html_writer::end_tag('div');    
                        
                        $output .= html_writer::start_tag('div', array('class' => 'row propierty-second'));

                            $output .= html_writer::start_tag('div', array('class' => 'col-sm-3'));
                                $output .= html_writer::start_tag('span');
                                    $output .= html_writer::tag('i', '', array('class' => 'wid wid-startlp header-txtmen i-search'));
                                    $output .= html_writer::tag("p", get_string('total_courses_', 'local_learningpaths'));
                                $output .= html_writer::end_tag('span');
                                $output .= html_writer::tag("span", $data->total_courses, array());
                            $output .= html_writer::end_tag('div');

                             $output .= html_writer::start_tag('div', array('class' => 'col-sm-3'));
                                $output .= html_writer::start_tag('span');
                                    $output .= html_writer::tag('i', '', array('class' => 'wid wid-icon-phbook header-txtmen i-search'));
                                    $output .= html_writer::tag("p", get_string('total_cohorts', 'local_learningpaths'));
                                $output .= html_writer::end_tag('span');
                                $output .= html_writer::tag("span", $data->total_cohorts, array());
                            $output .= html_writer::end_tag('div');

                            $output .= html_writer::start_tag('div', array('class' => 'col-sm-3'));
                                $output .= html_writer::start_tag('span');
                                    $output .= html_writer::tag('i', '', array('class' => 'wid wid-exclamation-circle header-txtmen i-search'));
                                    $output .= html_writer::tag("p", get_string('required_', 'local_learningpaths'));
                                $output .= html_writer::end_tag('span');
                                $output .= html_writer::tag("span", $data->total_courses_required, array());
                            $output .= html_writer::end_tag('div'); 

                        $output .= html_writer::end_tag('div');

                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
            
            $startdate = ((int)$data->startdate)?date('m/d/Y', $data->startdate):get_string('notset', 'local_learningpaths') ;
            $endate = ((int)$data->enddate)?date('m/d/Y', $data->enddate):get_string('notset', 'local_learningpaths');

            // Learningpath stats
            $output .= html_writer::start_tag('div', array('class' => 'col-sm-12 conent-descriptionlp'));
                $output .= html_writer::tag("h4", get_string('description', 'local_learningpaths'));
                $description = json_decode($data->description);
                $output .= html_writer::tag("div", $description->text, array('class' => 'description'));
            $output .= html_writer::end_tag('div');

            $output .= html_writer::start_tag('div', array('class' => 'row propierty-lp'));
                $output .= get_learningpath_custom_properties_html($data->id);
            $output .= html_writer::end_tag('div');
            
            // Create an instance of learningpath block
            $blockinstance = block_instance('rlms_lpd');
            $blockinstance->config = new stdClass();
            $blockinstance->config->learningpath = required_param('id', PARAM_INT);

            // Learningpath block
            $output .= html_writer::start_tag('div', array('class' => 'row'));
                // Block Title
                $output .= html_writer::start_tag('div', array('class' => 'col-sm-12 title-block'));
                    $output .= html_writer::tag('h4',get_string('dashboard', 'local_learningpaths'));
                    $output .= html_writer::start_tag('span', array('class' => 'edit'));
                        $icon = html_writer::tag('i', '', array('class' => 'fa fa-edit'));
                        $output .= html_writer::link('#learningpath-courses-tab', $icon, ['id' => 'courses-tab-button', 'data-toggle' => 'tab']);
                    $output .= html_writer::end_tag('span');
                $output .= html_writer::end_tag('div');

                // Block Content
                $output .= html_writer::start_tag('div', array('class' => 'block-lpd col-sm-12'));
                    $output .= html_writer::start_tag('div', array('id' => 'block_rlms_lpd_content','class' => 'block_rlms_lpd_content'));
                        $output .= $blockinstance->get_content()->text;
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        return $output;
    }

    /**
    * Edit learningpath tab form
    *
    * @param (data) general data of learning path
    */
    public function learningpath_edit_tab($data)
    {
        // Create learning path form
        $mform = new LearningPathForm(null, (array) $data);
        $form_data = [];
        $form_data['learningpath_image'] = load_image_to_draft($data->image);
        $mform->set_data($form_data);
        return $mform->render();
    }

    /**
    * Learning paths course administration tab
    *
    * @param (data) general data with learning paths courses
    */
    public function learningpath_courses_tab($data)
    {       

        // Button searchbox the learning path
        $output = html_writer::start_tag('div', array('class' => 'content-courses'));
            $output .= html_writer::start_tag('div', array('class' => 'row'));

                $output .=html_writer::start_tag('div', array('class' => 'col-sm-6'));
                    $output .= html_writer::start_tag('div', array('id' => 'searchbox', 'class' => 'searchbox', 'role' => 'search'));
                        $output .= html_writer::start_tag('div', array('class' => 'input-group mb-3 mt-search input-group custom-search-form'));
                            $output .= html_writer::start_tag('div', array('class' => 'input-group-btn'));
                                $output .= \theme_remui\widget::input('', '','search-courses','search-courses',false, array('class' => 'form-control', 'type' => 'text', 'placeholder' => get_string('search_courses', 'local_learningpaths'), 'aria-label' => '', 'onkeyup' => '','value' => optional_param('coursename', '', PARAM_TEXT)));
                                
                                $output .= html_writer::start_tag('span', array('class' => 'input-group-btn search-lp'));
                                    $output .= html_writer::start_tag('button', array('class' => '', 'type' => 'button', 'onclick' => '', 'aria-label' =>'','id' => 'btn-search-courses'));
                                        $output .= html_writer::tag('i', '', array('class' => 'men men-search-phx fa fa-search header-txt i-search'));
                                    $output .= html_writer::end_tag('button');
                                $output .= html_writer::end_tag('span');

                            $output .= html_writer::end_tag('div');
                        $output .= html_writer::end_tag('div');
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
                
            if (has_capability('local/learningpaths:add_courses_learning_path', context_system::instance())) {
                // Button to add courses in the learning path
                $output .= html_writer::start_tag('div', array('class' => 'col-sm-6 text-right new-course'));
                    $output .=html_writer::start_tag('span' , array('class' => 'course'));
                        $output .= html_writer::start_tag('a', array('href' => '#','id' => 'add-courses-lp','class' => 'btn btn-primary new-button', 'data-toggle' => 'modal', 'data-target' => '#courses-popup')); 
                            $output .= html_writer::tag('i', '', array('class' => 'fa fa-plus-circle tooltipelement_left', 'title' => get_string('add_courses','local_learningpaths')));
                            $output .= html_writer::tag('span',   get_string('add_courses','local_learningpaths')  );
                        $output .= html_writer::end_tag('a');
                    $output .=html_writer::end_tag('span');
                $output .= html_writer::end_tag('div');
            }
            $output .= html_writer::end_tag('div');

            $output .= html_writer::start_tag('div', array('id' => 'learningpath-courses-list', 'class' => ''));
                $output .= $this->courses_list($data->courses, $data->id);
            $output .= html_writer::end_tag('div');
          
        $output .= html_writer::end_tag('div');
        return $output;
    }

    public function learningpath_notifications_tab($data)
    {
        global $DB;
        $object =  new lp_notifications1 ();
        if ($object->is_cancelled()) {
            redirect($returnurl);
        } else if ($data1 = $object->get_data()) {
            $system_config =  (array)$data1;

            //enrol
            $enrol = array();
            $enrol['cron'] = $system_config['cron_lp_enrollment'];
            $enrol['enrollment_editor_checkbox1'] = $system_config['enrollment_editor_checkbox1'];
            $enrollment_editor = array();
            $enrollment_editor['text'] = $system_config['enrollment_editor']['text'];
            $enrollment_editor['format'] = $system_config['enrollment_editor']['format'];
            $enrol['enrollment_editor'] = $enrollment_editor;

            $enrolconfig = new stdClass();
            $enrolconfig->config = json_encode($enrol);
            $enrolconfig->learningpathid = $data->id;
            $enrolconfig->type = 'enrollment';
            $enrolconfig->cron = $data1->cron_lp_enrollment;
            save_notification($enrolconfig);
            //end enrol

            //expiration
            $expiration = array(); 
            $expiration['cron'] = $system_config['cron_lp_expiration'];
            $expiration['expiration_editor_checkbox1'] = $system_config['expiration_editor_checkbox1'];
            $expiration_editor= array();
            $expiration_editor['text'] = $system_config['expiration_editor']['text'];
            $expiration['expiration_editor'] = $expiration_editor;

            $expirationconfig = new stdClass();
            $expirationconfig->config = json_encode($expiration);
            $expirationconfig->learningpathid = $data->id;
            $expirationconfig->type = 'expiration';
            $expirationconfig->cron = $data1->cron_lp_expiration;
            save_notification($expirationconfig);
            //end expiration
           
            //enreminder
            $enreminder = array(); 
            $enreminder['cron'] = $system_config['cron_lp_enreminder'];
            $enreminder['enreminder_editor_checkbox1'] = $system_config['enreminder_editor_checkbox1'];
            $enreminder['enreminder_editor_text'] = $system_config['enreminder_editor_text'];
            $enreminder_editor= array();
            $enreminder_editor['text'] = $system_config['enreminder_editor']['text'];
            $enreminder['enreminder_editor'] = $enreminder_editor;

            $enreminderconfig = new stdClass();
            $enreminderconfig->config = json_encode($enreminder);
            $enreminderconfig->learningpathid = $data->id;
            $enreminderconfig->type = 'enreminder';
            $enreminderconfig->cron = $data1->cron_lp_enreminder;
            save_notification($enreminderconfig);
            //end enreminder

            //exreminder
            $exreminder = array();
            $exreminder['cron'] = $system_config['cron_lp_exreminder'];
            $exreminder['exreminder_editor_checkbox1'] = $system_config['exreminder_editor_checkbox1'];
            $exreminder['exreminder_editor_text'] = $system_config['exreminder_editor_text'];
            $exreminder_editor = array();
            $exreminder_editor['text'] = $system_config['exreminder_editor']['text'];
            $exreminder_editor['format'] = $system_config['exreminder_editor']['format'];
            $exreminder['exreminder_editor'] = $exreminder_editor;

            $exreminderconfig = new stdClass();
            $exreminderconfig->config = json_encode($exreminder);
            $exreminderconfig->learningpathid = $data->id;
            $exreminderconfig->type = 'exreminder';
            $exreminderconfig->cron = $data1->cron_lp_exreminder;
            save_notification($exreminderconfig);
            //end nexreminder

            //completion_reminder
            $completion_reminder = array();
            $completion_reminder['cron'] = $system_config['cron_lp_completion_reminder'];
            $completion_reminder['completion_reminder_editor_checkbox'] = $system_config['completion_reminder_editor_checkbox'];
            $completion_reminder['completion_reminder_editor_text'] = $system_config['completion_reminder_editor_text'];
            $completion_reminder_editor = array();
            $completion_reminder_editor['text'] = $system_config['completion_reminder_editor']['text'];
            $completion_reminder_editor['format'] = $system_config['completion_reminder_editor']['format'];
            $completion_reminder['completion_reminder_editor'] = $completion_reminder_editor;

            $completion_reminderconfig = new stdClass();
            $completion_reminderconfig->config = json_encode($completion_reminder);
            $completion_reminderconfig->learningpathid = $data->id;
            $completion_reminderconfig->type = 'completion_reminder';
            $completion_reminderconfig->cron = $data1->cron_lp_completion_reminder;
            save_notification($completion_reminderconfig);
            //end completion_reminder

            //notifications
            $notifications = array();
            $notifications['cron'] = $system_config['cron_lp_notifications'];
            $notifications['notifications_editor_checkbox1'] = $system_config['notifications_editor_checkbox1'];
            $notifications_editor = array();
            $notifications_editor['text'] = $system_config['notifications_editor']['text'];
            $notifications_editor['format'] = $system_config['notifications_editor']['format'];
            $notifications['notifications_editor'] = $notifications_editor;

            $notificationsconfig = new stdClass();
            $notificationsconfig->config = json_encode($notifications);
            $notificationsconfig->learningpathid = $data->id;
            $notificationsconfig->type = 'path_com';
            $notificationsconfig->cron = $data1->cron_lp_notifications;
            save_notification($notificationsconfig);
            //end notifications

        }else{
            $types = get_types($data->id);
            foreach ($types as $type) {
                $config = get_config_notification($data->id, $type);
                $object->set_data($config);
            }
        }
        $var = $object->render();

            $output .= html_writer::start_tag('div', array('class' => 'panel-body'));
                $output .= html_writer::start_tag('div', array('class' => 'container-fluid'));
                    $output .= html_writer::start_tag('div', array('class' => 'row'));
                        $output .= html_writer::start_tag('div', array('class' => 'col-sm-12 check'));
                            $output .= $var;
                        $output .= html_writer::end_tag('div');
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');   
        return $output;
    }

    /**
    * Learning path popup standart to use in popup without write all necesarry html again
    *
    * @param (name) popup name
    * @param (title) popup header
    * @param (content) popup content
    */
    public function learningpath_popup_standart($name, $title = "", $content = "", $class = '')
    {
        $output = html_writer::start_tag('div', array('id' => $name, 'class' => "modal fade {$class}", 'role' => 'dialog', 'aria-hidden' => 'false'));
            $output .= html_writer::start_tag('div', array('class' => 'modal-dialog'));
                $output .= html_writer::start_tag('div', array('class' => 'modal-content'));
                    $output .= html_writer::start_tag('div', array('class' => 'modal-header'));
                        $output .= html_writer::tag('h4', $title, array('class' => 'modal-title'));
                        $output .= html_writer::start_tag('a', array('class' => 'close', 'data-dismiss' => 'modal'));
                            $output .= html_writer::tag('i', '', array('class' => 'fa fa-times-circle-o'));
                        $output .= html_writer::end_tag('a');
                    $output .= html_writer::end_tag('div');
                    
                    $output .= html_writer::start_tag('div', array('id' => 'parent_scrollable'));
                        $output .= html_writer::start_tag('div', array('class' => 'modal-body'));

                            // Add courses to learningpath form
                            $output .= html_writer::start_tag('div', ['id' => "{$name}-content"]);
                                $output .= $content;
                            $output .= html_writer::end_tag('div');

                        $output .= html_writer::end_tag('div');
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
    * Function to get learning paths popups like add courses, add users, etc
    *
    * @param (data) learning path general data
    */
    public function popups($data)
    {
        // Popup for add new courses to learning path
        $output = $this->learningpath_popup_standart("courses-popup", get_string('courses', 'local_learningpaths'), $this->add_courses_form($data));

        // Popup for add new users to learning path
        $users_form = new ManageUsersForm(null, ['users' => $data->available_users, 'learningpath' => $data->id]);
        $output .= $this->learningpath_popup_standart("users-popup", get_string('users'), $users_form->render());

        // Popup for add new cohort to learning path
        $cohorts_form = new ManageCohortsForm(null, ['cohorts' => $data->available_cohorts, 'learningpath' => $data->id]);
        $output .= $this->learningpath_popup_standart('cohorts-popup', get_string('add_cohorts', 'local_learningpaths'), $cohorts_form->render());

        return $output;
    }

    /**
    * Return html to add courses in a learningpath
    * @param (data) object with (available_courses) which is a learningpath courses list and (id) which is learningpath id
    */
    public function add_courses_form($data)
    {
        $courses_form = new AddCoursesForm(null, ['courses' => $data->available_courses, 'learningpath' => $data->id]);
        return $courses_form->render();
    }

    /**
    * Learning path users administration tab
    *
    * @param (data) general data with learning path users
    */
    public function learningpath_users_tab($data)
    {
        // Button to searchbox in the learning path (user)
        $output = html_writer::start_tag('div', array('class' => 'content-search')); 
            $output .= html_writer::start_tag('div', array('class' => 'row searchbox-add'));

                $output .=html_writer::start_tag('div', array('class' => 'col-sm-6'));
                    $output .= html_writer::start_tag('div', array('id' => 'searchbox', 'class' => 'searchbox', 'role' => 'search'));
                        $output .= html_writer::start_tag('div', array('class' => 'mt-search input-group custom-search-form'));
                            $output .= html_writer::start_tag('span', array('class' => 'input-group-btn search-lp'));
                                $output .= html_writer::start_tag('button', array('class' => '', 'type' => 'button', 'onclick' => '', 'aria-label' =>'','id' => 'btn-search-users'));
                                    $output .= html_writer::tag('i', '', array('class' => 'men men men-search-phx fa fa-search header-txt i-search'));
                                $output .= html_writer::end_tag('button');
                            $output .= html_writer::end_tag('span');
                            $output .= html_writer::start_tag('div', array('class' => ''));
                                $output .= \theme_remui\widget::input('', '','search-users','search-users',false, array('class' => 'form-control', 'type' => 'text', 'placeholder' => get_string('search_users', 'local_learningpaths'), 'aria-label' => '', 'onkeyup' => '','value' => optional_param('user', '', PARAM_TEXT)));
                            $output .= html_writer::end_tag('div');
                        $output .= html_writer::end_tag('div');
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');

                $output .= html_writer::start_tag('div', array('class' => 'content-addusers col-sm-6'));
                    $output .= html_writer::start_tag('div', array('class' => 'row select-add right-row'));
                        /*$output .=html_writer::start_tag('div', array('class' => 'col-xs-10 col-sm-6'));
                   
                            //select users-per-page in the learning path
                            $output .= html_writer::start_tag('div', array('class' => 'items-per-page')); 
                                $visible_items = [get_string('items_per_page', 'local_learningpaths'), 10, 20, 50, 100];
                                $select .= html_writer::start_tag('select', ['id' => "users-per-page"]);
                                    foreach ($visible_items as $visible) {                
                                        $attrs = ($visible == $users_per_page) ? ['value' => (is_int($visible)) ? $visible : $users_per_page, 'active' => 'active'] : ['value' => (is_int($visible)) ? $visible : $users_per_page];
                                        $select .= html_writer::tag('option', $visible, $attrs);
                                    }
                                $select .= html_writer::end_tag('select');

                                $output .= $select;
                            $output .= html_writer::end_tag('div'); 
                    
                        $output .= html_writer::end_tag('div');*/

                        // Button to add courses in the learning path
                        $output .= html_writer::start_tag('div', array('class' => 'new-course'));
                            if (has_capability('local/learningpaths:delete_learning_path', context_system::instance()) && count($data->users) > 0 ) {
                                $output .=html_writer::start_tag('span' , array('class' => 'category'));
                                    $output .= html_writer::start_tag('a', array('href' => '#','id' => 'learningpath-remove-users','class' => 'btn btn-primary new-button')); 
                                        $output .= html_writer::tag('i', '', array('class' => 'fa fa-times-circle-o tooltipelement_left',  'title' => get_string('add_users','local_learningpaths')));
                                        $output .= html_writer::tag('span',   get_string('delete_users','local_learningpaths')  );
                                    $output .= html_writer::end_tag('a');
                                $output .=html_writer::end_tag('span');
                            }
                            $output .=html_writer::start_tag('span' , array('class' => 'course'));
                                $output .= html_writer::start_tag('a', array('href' => '#','id' => 'add-users-lp','class' => 'btn btn-primary new-button', 'data-toggle' => 'modal', 'data-target' => '#users-popup')); 
                                    $output .= html_writer::tag('i', '', array('class' => 'fa fa-plus-circle tooltipelement_left',  'title' => get_string('add_users','local_learningpaths')));
                                    $output .= html_writer::tag('span',   get_string('enroll_users','local_learningpaths')  );
                                $output .= html_writer::end_tag('a');
                            $output .=html_writer::end_tag('span');

                        $output .= html_writer::end_tag('div');
                    $output .= html_writer::end_tag('div');    

                $output .= html_writer::end_tag('div');
                
            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        // Button select all users in the learning path
        
        $output .= $this->users_list($data->users, $data->id, $data->total_users, optional_param('items', 10, PARAM_INT), $data->users_page);
        return $output;
    }   
    
    /*
    * Build a html with courses list of a learning path
    *
    * @param (courses) learning paths courses array
    */
    public function courses_list($courses = [], $learningpath)
    {   
        global $USER , $OUTPUT;
        $page = optional_param('page_course', 0, PARAM_INT);
        $dashboard_per_page = optional_param('courseperpage', 10, PARAM_INT);
        $coursestmp = getCoursesInfo($learningpath,false,$USER->id,null,null);
        $courseKey = array_keys($coursestmp);

        if($courses){

            $output = html_writer::start_tag('ul', array('class' => 'header lpd-courses'));
                $output .= html_writer::start_tag('li', array('class' => 'header course-description ui-sortable-handle row'));
                    
                    $output .= html_writer::start_tag('div', array('class' => 'col-8 col-sm-8 col-md-4 course_name'));
                        $output .= html_writer::tag('span', get_string('course_name', 'local_learningpaths'),array('class' => ''));
                    $output .= html_writer::end_tag('div');

                    $output .= html_writer::start_tag('div', array('class' => 'col-sm-3 credits'));
                        $output .= html_writer::tag('span', get_string('credits', 'local_learningpaths'),array('class' => ''));
                    $output .= html_writer::end_tag('div');

                    $output .= html_writer::start_tag('div', array('class' => 'col-4 col-sm-4 col-md-3 required'));
                        $output .= html_writer::tag('span', get_string('required', 'local_learningpaths'),array('class' => ''));
                    $output .= html_writer::end_tag('div');

                    $output .= html_writer::start_tag('div', array('class' => 'col-sm-2 actions'));
                        $output .= html_writer::tag('span', get_string('actions', 'local_learningpaths'),array('class' => ''));
                    $output .= html_writer::end_tag('div');
                    
                $output .= html_writer::end_tag('li');
            $output .= html_writer::end_tag('ul');

            $showsortable = '';
            if (has_capability('local/learningpaths:add_courses_learning_path', context_system::instance())) {
                $showsortable = ' showsortable';
            }
            $output .= html_writer::start_tag('ul', array('id' => 'list-course','class' => 'card-box list-courses' . $showsortable )); 
            
                $li_totals = count($courses);
                $la_index  = array_keys($courses);

                $la_pag_learninpath = array();

                if( $dashboard_per_page >10) $page = 0;

                for( $record=($page * $dashboard_per_page); $record < (( $page * $dashboard_per_page ) + $dashboard_per_page) ; $record++ ) {
                   if($courses[ $la_index[$record] ]) $la_pag_learninpath[ $la_index[$record] ] = $courses[ $la_index[$record] ];
                }
                $pages = count($courses) / $dashboard_per_page;
                $active_page = 1;

                foreach ($la_pag_learninpath as $course) {
                    if(in_array($course->data->courseid, $courseKey)){
                        $credits = ($coursestmp[$course->data->courseid]->credits > 0 )?$coursestmp[$course->data->courseid]->credits:0;
                    }
                    $checked = ($course->data->required == 1) ? 'checked' : '';
                    $required = ($course->data->required) ? get_string('is_required', 'local_learningpaths') : get_string('is_not_required', 'local_learningpaths');
                
                    // Action buttons for edit prerequisites and remove course
                    $edit_icon = html_writer::tag('i', '', array('class' => 'fa fa-pencil tooltipelement string_class', 'data-placement' => 'bottom',  'title' => get_string('settings_lpcourse','local_learningpaths'),'aria-hidden' => 'true'));
                    $delete_icon = html_writer::tag('i', '', array('class' => 'wid wid-deleteicon fa fa-trash tooltipelement', 'data-placement' => 'bottom',  'title' => get_string('delete','local_learningpaths'),'aria-hidden' => 'true','data-item' => $learningpath->id));
                    //$delete_url = new moodle_url("/local/learningpaths/actions.php?action=remove_course&item={$course->data->id}&learningpath={$learningpath}&sesskey={$USER->sesskey}");
                    $attrs = ['class' => 'edit-course', 'data-toggle' => 'modal', 'data-toggle' => 'modal', 'data-target' => "#prerequisites-popup-{$course->data->id}"];
                    
                    if (has_capability('local/learningpaths:delete_courses_learning_path', context_system::instance())) {
                        $deletecourse = html_writer::link('#', $delete_icon,['class' => 'delete-course-learning-path']);
                    }else{
                        $deletecourse = '';
                    }
                    
                    if (has_capability('local/learningpaths:add_courses_learning_path', context_system::instance())) {
                        $course->data->actions = html_writer::link('#', $edit_icon, $attrs) . $deletecourse;
                    }
                    
                    // Icon
                    $prerequisites = $course->get_prerequisites();
                    if (count($prerequisites) > 0) {
                        $title = "";
                        foreach ($prerequisites as $prerequisite) {
                            $title .= "• "."{$prerequisite->coursename} ";
                        }
                        $title .= "";
                        $icon = html_writer::tag('i', '', array('class' => 'men men-icon-phcircle fa fa-arrows icons_lp','data-toggle' => 'tooltip', 'data-placement' => 'right','title'=> $title, 'data-original-title' => $title));
                        
                        $icon = "<i class=\"tooltipelement_html wid wid-icon-phprerquisites fa fa-lock icons_lp\" title=\"{$title}\" ></i>";
                    } else {
                        $icon = '<i class="tooltipelement_html men men-icon-phcircle fa fa-arrows icons_lp"></i>';   
                    }

                    $coursename = (strlen($course->data->coursename) >= 40)?substr($course->data->coursename, 0, 40).'...':$course->data->coursename;
                    // Build HTML
                    $output .= html_writer::start_tag('li', array('class' => 'course-description ui-sortable-handle', 'data-id' => $course->data->id));
                        $output .= html_writer::tag('div', $icon . $coursename, array('class' => 'col-8 col-sm-8 col-md-4 course_name'));
                        $output .= html_writer::tag('div',$credits, array('class' => 'col-sm-3 credits'));
                        $output .= html_writer::start_tag('div', array('class' => 'col-4 col-sm-4 col-md-3 required'));
                            //$output .= html_writer::empty_tag('input', array('class' => 'course-switch', 'type' => 'checkbox', $checked => $checked, 'data-courseid' => $course->data->id));
                            $output .= html_writer::start_tag('div', array('class' => 'togglebutton'));
                            
                             if (has_capability('local/learningpaths:add_courses_learning_path', context_system::instance())) {
                                $output .= html_writer::start_tag('label', array('class' => 'switch'));
                                    $output .= html_writer::empty_tag('input', array('class' => 'course-switch', 'type' => 'checkbox', $checked => $checked, 'data-courseid' => $course->data->id ));
                                    $output .= html_writer::start_tag('span', array('class' => 'slider round'));
                                    $output .= html_writer::end_tag('span');
                                $output .= html_writer::end_tag('label');
                            }
                            $output .= html_writer::end_tag('div');

                        $output .= html_writer::end_tag('div');

                        $output .= html_writer::tag('div', $course->data->actions, array('class' => 'col-sm-2 actions'));

                        $output .= $this->add_prerequisites_popup($course);

                    $output .= html_writer::end_tag('li');
                }
                
                if ($li_totals > 10){
                    $output .= html_writer::start_tag('div', array('class'=>' mar-btm col-sm-12 mar-no pad-no mar-btm pagination_lp'));
                        $output .= html_writer::start_tag('ul');
                            $output .= html_writer::start_tag('li');
                                $output .= html_writer::start_tag('form',array('method'=>'POST'));

                                   /* $output .= html_writer::start_tag('div', array('style'=>'float:left;padding-top: 7px;margin-right: 10px;'));
                                        $output .= html_writer::tag('span',get_string('recordsperpage','local_people'), array());
                                    $output .= html_writer::end_tag('div');

                                    $output .= html_writer::start_tag('div', array('style'=>'float:left;'));
                                        /*$output .= html_writer::start_tag('select',array('id'=>'id_courseperpage','name'=>'courseperpage','class'=>'form-control lp_pagination','style'=>'width:70px;'));
                                            $vals = array(10,20,30,40,50,60,70,80,90,100);
                                            foreach ($vals  as $key) {
                                                $selectedperpage = '';
                                                if($dashboard_per_page == $key ) $selectedperpage = 'selected' ;
                                                $output .= html_writer::tag('option',$key, array($selectedperpage=>$selectedperpage));
                                            }
                                        $output .= html_writer::end_tag('select');
                                    $output .= html_writer::end_tag('div');*/
                                $output .= html_writer::end_tag('form');
                            $output .= html_writer::end_tag('li');

                            $output .= html_writer::start_tag('div', array('style'=>'float:right;margin-right: 10px;'));
                            
                            $output .= html_writer::end_tag('div');
                
                            $output .= html_writer::start_tag('li');
                                if ($pages > 1)
                                $lpid = $course->data->learningpathid;
                                $output .= $OUTPUT->paging_bar(count($courses), $page, $dashboard_per_page,'?id='.$lpid.'&tab=courses','page_course');

                            $output .= html_writer::end_tag('li');
                        $output .= html_writer::end_tag('ul');
                    $output .= html_writer::end_tag('div');//Close pagination div
                
                }

            $output .= html_writer::end_tag('ul');
        }else{
            $message = get_string('no_records', 'local_learningpaths');
            $output .= html_writer::tag('div', $message, array('class' => 'col-12 col-sm-12','id' => 'loction_no_record_courses'));
        }
        
        
        return $output;
    }

    public function learningpath_cohorts_tab($data)
    {
        // Button to searchbox in the learning path (cohorts)
        $output = html_writer::start_tag('div', array('class' => 'content-search')); 
            $output .= html_writer::start_tag('div', array('class' => 'row searchbox-add'));
                $output .=html_writer::start_tag('div', array('class' => 'col-sm-6'));
                //Start
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        // Button select all cohorts in the learning path
        $output .= html_writer::start_tag('div', array('class' => 'content-addcohorts'));
            $output .= html_writer::start_tag('div', array('class' => 'row select-add'));
                $output .=html_writer::start_tag('div', array('class' => 'col-xs-12 col-sm-8 col-md-6 col-lg-6'));
                   
                $output .= html_writer::start_tag('div', array('id' => 'searchbox', 'class' => 'searchbox', 'role' => 'search'));
                         
                        $output .= html_writer::start_tag('div', array('class' => 'mt-search input-group custom-search-form'));
                            
                            $output .= html_writer::start_tag('span', array('class' => 'input-group-btn search-lp'));
                                $output .= html_writer::start_tag('button', array('class' => '', 'type' => 'button', 'onclick' => '', 'aria-label' =>'','id' => 'btn-search-cohorts'));
                                    $output .= html_writer::tag('i', '', array('class' => 'men men men-search-phx fa fa-search header-txt i-search'));
                                $output .= html_writer::end_tag('button');
                            $output .= html_writer::end_tag('span');
                            
                            $output .= html_writer::start_tag('div', array('class' => ''));
                                $output .= \theme_remui\widget::input('', '','search-cohorts','search-cohorts',false, array('class' => 'form-control', 'type' => 'text', 'placeholder' => get_string('search_cohorts', 'local_learningpaths'), 'aria-label' => '', 'onkeyup' => '','value' => optional_param('cohort', '', PARAM_TEXT)));
                            $output .= html_writer::end_tag('div');

                        $output .= html_writer::end_tag('div');
                    
                    $output .= html_writer::end_tag('div');//End
    
                $output .= html_writer::end_tag('div');

                // Button to add cohorts in the learning path
                $output .= html_writer::start_tag('div', array('class' => 'col-xs-12 col-sm-4 col-md-6 col-lg-6 new-cohort'));
                if (has_capability('local/learningpaths:add_courses_learning_path', context_system::instance()) && count($data->cohorts) > 0 ) {
                    $output .=html_writer::start_tag('span' , array('class' => 'category'));
                        $output .= html_writer::start_tag('a', array('href' => '#','id' => 'learningpath-remove-cohorts','class' => 'btn btn-primary new-button')); 
                            $output .= html_writer::tag('i', '', array('class' => 'fa fa-times-circle-o tooltipelement_left',  'title' => get_string('add_users','local_learningpaths')));
                            $output .= html_writer::tag('span',   get_string('delete','local_learningpaths')  );
                        $output .= html_writer::end_tag('a');
                    $output .=html_writer::end_tag('span');
                }
                if (has_capability('local/learningpaths:add_courses_learning_path', context_system::instance())) {
                    $output .=html_writer::start_tag('span' , array('class' => 'course'));
                        $output .= html_writer::start_tag('a', array('href' => '#','id' => 'add-cohorts-lp','class' => 'btn btn-primary new-button', 'data-toggle' => 'modal', 'data-target' => '#cohorts-popup')); 
                            $output .= html_writer::tag('i', '', array('class' => 'fa fa-plus-circle tooltipelement_left',  'title' => get_string('add_users','local_learningpaths')));
                            $output .= html_writer::tag('span',   get_string('add_cohorts','local_learningpaths')  );
                        $output .= html_writer::end_tag('a');
                    $output .=html_writer::end_tag('span');
                }
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        $output .= $this->cohorts_list($data->cohorts, $data->id);
        return $output;
    }

    /**
    * Build html for a course add prerequisites popup
    * @param (course) learningpath course object
    */
    public function add_prerequisites_popup($course)
    {
        return $this->learningpath_popup_standart("prerequisites-popup-{$course->data->id}", get_string('add_prerequisites', 'local_learningpaths'), $this->prerequistes_form($course, $course->data->learningpathid), 'prerequisites-popup');
    }

    /*
    * Learning path prerequisites form, also this allows mark course as requirement for learning path completion
    *
    * @param (courses) learning paths courses array
    * @param (course) current course
    * @param (learningPath) learning path id
    */
    public function prerequistes_form($course, $learningpath)
    {   
        $prerequisites = [];
        foreach ($course->get_prerequisites() as $prerequisite) {
            $prerequisites[] = $prerequisite->prerequisite;
        }

        $mform = new ManageCoursesForm(null, [
            'learningpath_courses' => $course->get_learningpath_courses(),
            'learningpath' => $learningpath,
            'courseid' => $course->data->id,
            'prerequisites' => $prerequisites,
            'does_not_prerequisites' => array_keys($course->get_does_not_as_prerequisites()),
            'required' => $course->data->required
        ]);
        return $mform->render();
    }

    /*
    * Build html code for users list of a learning path
    *
    * @param (courses) learning path courses array
    */
    public function users_list($users, $learningPath, $total_users, $users_per_page = 10, $active_page = 1)
    {
        global $USER , $OUTPUT;
        $page = optional_param('page', 0, PARAM_INT);
        $dashboard_per_page = optional_param('userperpage', 10, PARAM_INT);
       
        $output = html_writer::start_tag("div", array("class" => "col-12 hidden", "id" => 'lpstatus'));
            $output .= html_writer::tag('div', '', array('class'=> 'alert'));
        $output .= html_writer::end_tag('div');

        $output .= html_writer::start_tag("div", array("class" => "content-table table-responsive"));

        if($users){

        
            $output .= html_writer::start_tag("table", array("id" => "table_users", "class" => "table table_users card-box"));

                // Users Table header
                $output .= html_writer::start_tag('thead');                    
                    $output .= html_writer::start_tag('tr', array('class' => 'users head'));

                        $output .= html_writer::start_tag('td', array('class' => 'allcheck'));
                            $plmsform = new plms_form();
                            $checkbox_head = $plmsform->fieldGeneralCheckbox('all_users', '', '', '');
                            $output.= $checkbox_head ;
                        $output .= html_writer::end_tag('td');
                        
                        $output .= html_writer::tag('td', get_string("first_name", "local_learningpaths"), array('class' => 'firstname table_head'));
                        $output .= html_writer::tag('td', get_string("lastname", "local_learningpaths"), array('class' => 'lastname table_head'));
                        $output .= html_writer::tag('td', get_string("enrollmentdate", "local_learningpaths"), array('class' => 'date table_head '));
                        $output .= html_writer::tag('td', get_string("progress", "local_learningpaths"), array('class' => 'progresso table_head'));
                    $output .= html_writer::end_tag('tr');
                $output .= html_writer::end_tag('thead');

                // List of users
                foreach ($users as $user) {
                            
                    $courses_completed = $user->completed_courses();
                    $output .= html_writer::start_tag('tr', array('class' => 'users user-'.$user->data->id, 'id' => 'user-'.$user->data->id));

                        $progress = html_writer::start_tag('div', array('class' => 'progress'));
                            $progress .= html_writer::tag('div','', array('class' => 'progress-bar bg-success','role' => 'progressbar', 'style' => "width: {$courses_completed->percentage}%;"));
                        $progress .= html_writer::end_tag('div');

                        $progress .= html_writer::tag('p' , $courses_completed->percentage.'%' , array('class' => 'porc_line') );

                        $checkbox = html_writer::start_tag('div', array('class' => 'form-check'));
                            $checkbox .= html_writer::start_tag('label', array('class' => 'form-check-label'));
                                $checkbox .= html_writer::empty_tag('input', array('class' => 'learningpath-user form-check-input ', 'type' => 'checkbox', 'data-userid' => $user->data->id));
                                $checkbox .= html_writer::start_tag('span', array('class' => 'form-check-sign'));
                                    $checkbox .= html_writer::tag('span','', array('class' => 'check'));
                                $checkbox .= html_writer::end_tag('span');
                            $checkbox .= html_writer::end_tag('label');
                        $checkbox .= html_writer::end_tag('div');

                        $output .= html_writer::tag('td', $checkbox, array('class' => 'td-checkbox center'));
                        $output .= html_writer::tag('td', $user->data->firstname, array('class' => 'firstname'));
                        $output .= html_writer::tag('td', $user->data->lastname, array('class' => 'lastname'));
                        $output .= html_writer::tag('td', date('M - d - Y', $user->data->enrollment_date), array('class' => 'date'));
                        $output .= html_writer::tag('td', $progress, array('class' => 'progresso'));
                        $output .= html_writer::end_tag('tr');
                }
            $output .= html_writer::end_tag("table");
        }else{
            $message = get_string('no_records', 'local_learningpaths');
            $output .= html_writer::tag('div', $message, array('class' => 'col-12 col-sm-12','id' => 'loction_no_record_user'));
        }
            
            // Calculate number of pages and get html of pagination
            /*$pages = ceil($total_users / $users_per_page);
                if ($pages > 1)
                $output .= $this->learningpaths_users_pagination($learningPath, $pages, "users", $active_page, $users_per_page);*/

            $pages = $total_users / $dashboard_per_page;
            if($total_users > 10){

                $output .= html_writer::start_tag('div', array('class'=>' mar-btm col-sm-12 mar-no pad-no mar-btm pagination_lp'));
                    $output .= html_writer::start_tag('ul');
                        $output .= html_writer::start_tag('li');
                            $output .= html_writer::start_tag('form',array('method'=>'POST'));
                                $output .= html_writer::start_tag('div', array('style'=>'float:left;padding-top: 7px;margin-right: 10px;'));
                                    $output .= html_writer::tag('span',get_string('recordsperpage','local_people'), array());
                                $output .= html_writer::end_tag('div');

                                $output .= html_writer::start_tag('div', array('style'=>'float:left;'));
                                    $output .= html_writer::start_tag('select',array('type'=>'text','id'=>'id_userperpage','name'=>'userperpage','class'=>'form-control','style'=>'width:70px;'));
                                        $vals = array(10,20,30,40,50,60,70,80,90,100);
                                        foreach ($vals  as $key) {
                                            $selectedperpage = '';
                                            if($dashboard_per_page == $key ) $selectedperpage = 'selected' ;
                                            $output .= html_writer::tag('option',$key, array($selectedperpage=>$selectedperpage));
                                        }
                                    $output .= html_writer::end_tag('select');
                                $output .= html_writer::end_tag('div');

                            $output .= html_writer::end_tag('form');
                        $output .= html_writer::end_tag('li');
                        
                        $output .= html_writer::start_tag('li');
                            if ($pages > 1)
                            $output .= $OUTPUT->paging_bar($total_users, $page, $dashboard_per_page,'?id='.$learningPath.'&tab=users');
                        $output .= html_writer::end_tag('li');
                    $output .= html_writer::end_tag('ul');
                $output .= html_writer::end_tag('div');
            } 

        $output .= html_writer::end_tag("div");
        return $output;
    }

    /*
    * Build html code for users list of a learning path
    *
    * @param (courses) learning path courses array
    */
    public function cohorts_list($cohorts, $learningpath) {
        global $USER;
        $output = '';
        $output .= html_writer::start_tag("div", array("class" => "col-12 hidden", "id" => 'cohortst'));
            $output .= html_writer::tag('div', '', array('class'=> 'alert'));
        $output .= html_writer::end_tag('div');
        
        if( count($cohorts) > 0){
        $output .= html_writer::start_tag("div", array("class" => "content-table table-responsive"));
            $output .= html_writer::start_tag("table", array("id" => "table_cohorts", "class" => "table mt-table card-box"));
                $output .= html_writer::start_tag("tr",array('class' => 'header'));
                    $output .= html_writer::tag("th",'');
                    
                    $output .= html_writer::start_tag('th', array('id' => 'cohort-','class' => 'form-check check_all'));
                        $plmsform = new plms_form();
                        $checkbox = $plmsform->fieldGeneralCheckbox('all_cohorts', '', '', '');
                        $output.= $checkbox ;
                        $output .= html_writer::tag("span", get_string("name_cohort", "local_learningpaths"));
                    $output .= html_writer::end_tag('th');
                    
                    $output .= html_writer::tag("th", get_string("enrollmentdate", "local_learningpaths"));
                    $output .= html_writer::tag("th", get_string("number_users", "local_learningpaths"), array('class' => 'center'));
                $output .= html_writer::end_tag("tr");

                foreach ($cohorts as $cohort) {
                    $output .= html_writer::start_tag('tr', array('class' => 'cohort', 'id' => 'cohort-'.$cohort->data->id ) );
                        $url = new moodle_url("/local/learningpaths/actions.php?action=remove_cohort&item={$cohort->data->id}&learningpath={$learningpath}&sesskey={$USER->sesskey}");
                        $cohort->actions = html_writer::link($url, "remove");

                        $checkbox  = html_writer::start_tag('label', array('class' => 'form-checkbox form-normal form-icon-text form-plms'));
                        $checkbox .= html_writer::empty_tag('input', array('class' => 'learningpath-cohort', 'type' => 'checkbox', 'data-cohortid' => $cohort->data->id));
                        $checkbox .= html_writer::end_tag('label');

                        $output .= html_writer::tag('td', $checkbox, array('class' => 'td-checkbox pl-0'));
                        $output .= html_writer::tag('td', $cohort->data->name);
                        $output .= html_writer::tag('td', date('M - d - Y', $cohort->data->enrollment_date));
                        $output .= html_writer::tag('td', $cohort->data->total_users, array('class' => 'center'));
                    $output .= html_writer::end_tag('tr');
                }
            $output .= html_writer::end_tag("table");
            $output .= html_writer::end_tag("div");
        }else{
            $message = get_string('no_records','local_learningpaths');
            $output .= html_writer::tag('div', $message, array('class' => 'col-12 col-sm-12','id' => 'loction_no_record_cohort'));
        }
        return $output;
    }

    /**
    * Return a list of tabs for learning path
    */
    private function get_tabs_list()
    {
        $tabs = [];
        $tabs[] = 'view';
        $tabs[] = 'courses';
        $tabs[] = 'users';
        $tabs[] = 'cohorts';

        // if (!rlmslms_get_current_editing_company()) {
        //     $tabs[] = 'cohorts';
        // }

        $tabs[] = 'notifications';
        return $tabs;
    }

    
    // Build navigation tab code for learning path
    
    public function navigation_tabs()
    {
        $active = optional_param('tab', 'view', PARAM_TEXT);

        // Getting tabs list
        $tabs = $this->get_tabs_list();

        // Open tabs list
        $output = html_writer::start_tag('ul', array('class' => 'nav nav-tabs w-100'));
            // Build tabs
            foreach ($tabs as $tab) {
                $itemClasses = ($active == $tab) ? 'active' : '';
                $output .= html_writer::start_tag('li', ['id' => "{$tab}-button", 'class' => 'nav-item']);
                    $output .= html_writer::link("#learningpath-{$tab}-tab", get_string($tab, 'local_learningpaths'), array('data-toggle' => 'tab', 'class' => 'nav-link '.$itemClasses));
                $output .= html_writer::end_tag('li');
            }
        $output .= html_writer::end_tag('ul');
        return $output;
    }

    /*
    * Build tabs code for learning path
    */
    public function tabs($data)
    {
        $active = optional_param('tab', 'view', PARAM_TEXT);

        // Getting tabs list
        $tabs = $this->get_tabs_list();

        // Open tabs container
        $output = "";
        foreach ($tabs as $tab) {
            // Define current tab classes
            $itemClasses = ($active == $tab) ? 'tab-pane fade active in' : 'tab-pane';

            // Build tab
            $output .= html_writer::start_tag('div', array("id" => "learningpath-{$tab}-tab", "class" => "{$itemClasses} pad-all"));
                $tabFullName = "learningpath_{$tab}_tab";
                if (method_exists($this, $tabFullName)) {
                    $output .= $this->$tabFullName($data);
                } else {
                    // throw new Exception("Coding Error detected. An undefined tab was called", 1);
                }
            $output .= html_writer::end_tag('div');
        }

        // print popups
        $output .= $this->popups($data);
        return $output;
    }

    /**
    * Printing Learningpaths Dashboard where those are going to be listed for management
    * @param (learningpaths) objects list with learning paths
    */
    function dashboard($learningpaths)
    {       
        global $OUTPUT;
        $page = optional_param('page', 0, PARAM_INT);
        $dashboard_per_page = optional_param('userperpage', 10, PARAM_INT);

        $output = html_writer::start_tag('div', array('class' => 'plms-learningpaths p-4'));
            $output .=html_writer::start_tag('div', array('class' => 'row add-learning'));
                $output .=html_writer::start_tag('div', array('class' => 'col-sm-4 search-course-learning')); 
                    // Button to searchbox in the learning path

                    $output .= html_writer::start_tag('form', array('method'=>'GET','action' => $_SERVER['PHP_SELF'] , 'class' => ' ' ,'id'=>'search_lp_form'));
                        $output .= html_writer::start_tag('div', array('id' => 'searchbox', 'class' => 'searchbox', 'role' => 'search'));
                            $output .=html_writer::start_tag('div', array('class' => 'input-group mb-3'));
                                
                                $output .=html_writer::start_tag('div', array('class' => 'input-group-btn'));
                                    $output .= html_writer::start_tag('button', array('class' => '', 'type' => '', 'onclick' => "document.getElementById('search_lp_form').submit();", 'aria-label' =>''));
                                        $output .= html_writer::tag('i', '', array('class' => 'men men men-search-phx header-txt i-search men men men-search-phx header-txt i-search fa fa-search '));
                                    $output .= html_writer::end_tag('button');
                                $output .= html_writer::end_tag('div');

                                $output .= \theme_remui\widget::input('', '','id_search','search_lp',false, array('class' => 'form-control', 'type' => 'text', 'placeholder' => get_string('search_find','local_learningpaths'), 'aria-label' =>''));
                            $output .= html_writer::end_tag('div');
                        $output .= html_writer::end_tag('div');
                    $output .= html_writer::end_tag('form');  //Hasta Aqki              
                $output .= html_writer::end_tag('div');

                if (has_capability('local/learningpaths:create_learning_path', context_system::instance())) {
                    $output .= $this->add_learningpath_popup();
                }
                if( count($learningpaths) <= 0 )
                    return $output .= get_string('no_records', 'local_learningpaths');

                $output .= html_writer::start_tag('div' , array('class' => 'row col-xs-12 col-sm-12 title_lp_index'));
                    
                    $output .= html_writer::start_tag('div' , array('class' => 'lpt-name col-xs-4 col-sm-4'));
                        $output .= html_writer::tag('span',get_string('learning_pathname','local_learningpaths'), array());
                    $output .= html_writer::end_tag('div');

                    $output .= html_writer::start_tag('div' , array('class' => 'start-date col-xs-4 col-sm-3'));
                        $output .=html_writer::tag('span' , get_string('startdate_','local_learningpaths'), array());
                    $output .= html_writer::end_tag('div');

                    $output .= html_writer::start_tag('div' , array('class' => 'end-date col-xs-3 col-sm-3'));
                        $output .=html_writer::tag('span' , get_string('enddate_','local_learningpaths'), array());
                    $output .= html_writer::end_tag('div');

                    $output .= html_writer::start_tag('div' , array('class' => 'col-xs-3 col-sm-2 learningpaths-actions'));
                        $output .=html_writer::tag('span' , get_string('actions','local_learningpaths'), array());
                    $output .= html_writer::end_tag('div');
                           
                $output .= html_writer::end_tag('div');
            
            $output .= html_writer::end_tag('div');

            $li_totals = count($learningpaths);
            $la_index  = array_keys($learningpaths);

            $la_pag_learninpath = array();

            for( $record=($page * $dashboard_per_page); $record < (( $page * $dashboard_per_page ) + $dashboard_per_page) ; $record++ ) {
               if($learningpaths[ $la_index[$record] ]) $la_pag_learninpath[ $la_index[$record] ] = $learningpaths[ $la_index[$record] ];
            }
        
            $output .= $this->learningpaths_list($la_pag_learninpath);
            $pages = count($learningpaths) / $dashboard_per_page;
            $active_page = 1;
            $output .= html_writer::start_tag('div', array('class'=>' mar-btm col-sm-12 mar-no pad-no mar-btm pagination_lp'));
                $output .= html_writer::start_tag('ul', array('class'=>'count_select'));
                    if ($li_totals > 10){
                        $output .= html_writer::start_tag('li');
                            $output .= html_writer::start_tag('form',array('method'=>'POST'));
                                
                                $output .= html_writer::start_tag('div', array('style'=>'float:left;padding-top: 7px;margin-right: 10px;'));
                                    $output .= html_writer::tag('span',get_string('recordsperpage','local_people'), array());
                                $output .= html_writer::end_tag('div');

                                $output .= html_writer::start_tag('div', array('class'=>'page_num'));
                                    
                                    $output .= html_writer::start_tag('select',array('type'=>'text','id'=>'id_userperpage','name'=>'userperpage','class'=>'form-control','style'=>'width:70px;'));
                                        $vals = array(10,20,30,40,50,60,70,80,90,100);
                                       foreach ($vals  as $key) {
                                       $selectedperpage = '';
                                        if($dashboard_per_page == $key ) $selectedperpage = 'selected' ;
                                            $output .= html_writer::tag('option',$key, array($selectedperpage=>$selectedperpage));
                                        }
                                    $output .= html_writer::end_tag('select');
                                
                                $output .= html_writer::end_tag('div');

                            $output .= html_writer::end_tag('form');
                        $output .= html_writer::end_tag('li');
                    }
                    $output .= html_writer::start_tag('li');
                        if ($pages > 1)
                        $output .= $OUTPUT->paging_bar(count($learningpaths), $page, $dashboard_per_page,'');
                    $output .= html_writer::end_tag('li');
                $output .= html_writer::end_tag('ul');

            $output .= html_writer::end_tag('div');
            $output .= html_writer::tag('div', '', ['class' => 'clearfix']);
        
        $output .= html_writer::end_tag('div');
       
        return $output;
    }

    public function add_learningpath_popup(){
        // Create a new learning path form object for that, we need to have a learningpath object
        global $CFG;
        require_once "{$CFG->dirroot}/local/learningpaths/classes/objects/LearningPath.php";
        $learningpath = new LearningPath();

        // Add new learningpath container
        $output = html_writer::start_tag('div', array('class' => 'col-xs-4 col-sm-8 new-learning'));

            // Add learningpath button
            $output .= html_writer::start_tag('div' , array('class' => 'create-buttons cbtn_lp'));
                $output .=html_writer::start_tag('span' , array('class' => 'course'));
                    $output .= html_writer::start_tag('a', array('href' => '#','class' => 'btn btn-primary new-button', 'data-toggle' => 'modal', 'data-target' => '#largeModal'));
                        $output .= html_writer::tag('i', '', array('class' => 'fa fa-plus-circle tooltipelement_left'));
                        $output .= html_writer::tag('span',   get_string('add_learning','local_learningpaths')  );
                    $output .= html_writer::end_tag('a');
                $output .=html_writer::end_tag('span');
            $output .= html_writer::end_tag('div');
         
            // Modal add learning path. 
            $attrs = array('class' => 'modal fade add-learningspath', 'id' => 'largeModal', 'aria-labelledby' => 'largeModal', 'tabindex' => '-1', 'role' => 'dialog', 'aria-hidden' => 'true');
            $output .= html_writer::start_tag('div', $attrs);
                $output .= html_writer::start_tag('div', array('class' => 'modal-dialog modal-lg'));
                    $output .= html_writer::start_tag('div', array('class' => 'modal-content'));

                        // Popup header
                        $output .= html_writer::start_tag('div', array('class' => 'modal-header'));
                            $output .= html_writer::tag('h4',get_string('create', 'local_learningpaths'), array('class' => 'modal-title', 'id' => 'yModalLabel'));
                            $output .= html_writer::start_tag('span', array('class' => 'close', 'data-dismiss' => 'modal', 'aria-hidden' => 'true'));
                                $output .= html_writer::tag('i', '', array('class' => 'fa fa-times-circle-o'));
                            $output .= html_writer::end_tag('span');  
                        $output .= html_writer::end_tag('div');

                        // New learningpath form
                        $output .= html_writer::start_tag('div', array('class' => 'modal-body'));
                            $output .= $learningpath->render_form();
                        $output .= html_writer::end_tag('div');

                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
    * Return HTML list of learningpath
    */
    public function learningpaths_list($learningpaths)
    {
        // Global objects
        global $USER, $CFG, $DB, $SESSION;

        $output = "";
        
        foreach ($learningpaths as $learningpath) {

            $output .= html_writer::start_tag('div', array('class' => 'row plms-learningpath mar-btm'));
                
                /**
                 * Add company name label , if company's LP
                 * @author Manisha M.
                 * @since  26-11-2019
                 * @rlms
                */
                $companylabel = '';
                if($learningpath->companyid > 0 &&  is_siteadmin() && empty($SESSION->currenteditingcompany)){
                    $companyname = $DB->get_record('company', ['id' => $learningpath->companyid]);
                    $companylabel = '<span class="badge badge-primary companylabel">'.$companyname->name.'</span>';
                }

                $output .= html_writer::start_tag('div', array('class' => 'lpt-name col-xs-4 col-sm-4'));
                    $output .= html_writer::link(new moodle_url("/local/learningpaths/view.php?id={$learningpath->id}"), $learningpath->name . $companylabel);
                $output .= html_writer::end_tag('div');

                $startdate = ($learningpath->startdate)?date('m/d/Y', $learningpath->startdate):get_string('notset', 'local_learningpaths');
                $endate = ($learningpath->enddate)?date('m/d/Y', $learningpath->enddate):get_string('notset', 'local_learningpaths');

                $output .= html_writer::start_tag('div', array('class' => 'start-date col-xs-4 col-sm-3'));
                    $output .= html_writer::tag('span', $startdate);
                $output .= html_writer::end_tag('div');

                $output .= html_writer::start_tag('div', array('class' => 'end-date col-xs-3 col-sm-3'));
                    $output .= html_writer::tag('span', $endate);
                $output .= html_writer::end_tag('div');
                
                if (has_capability('local/learningpaths:delete_learning_path', context_system::instance())) {
                $output .= html_writer::start_tag('div', array('class' => 'col-xs-3 col-sm-2 learningpaths-actions'));
                    $output .= html_writer::start_tag('a', array('class' => 'settings-learning-path edit-learning-path', 'href' => "{$CFG->wwwroot}/local/learningpaths/edit.php?id={$learningpath->id}"));
                        $output .= html_writer::tag('i','', array('class' => 'wid wid-editicon fa fa-pencil', 'data-placement' => 'bottom',  'title' => get_string('settings','local_learningpaths')));
                    $output .= html_writer::end_tag('a'); 

                    $javascriptDelete = <<<JS
                        require(['jquery','local_learningpaths/bootbox'],function($,bootbox){
                            bootbox.confirm(M.util.get_string('delete_msg', 'local_learningpaths'), function(result){
                                if(result){
                                    window.location = "{$CFG->wwwroot}/local/learningpaths/actions.php?item={$learningpath->id}&action=delete_learningpath&sesskey={$USER->sesskey}";
                                }
                            });
                        });
                            
JS;

                    $output .= html_writer::link("#", html_writer::tag('i', '', array('class' => 'wid wid-deleteicon fa fa-trash')), array('class' => 'delete-learning-path tooltipelement', 'data-placement' => 'bottom',  'title' => get_string('delete','local_learningpaths'), 'data-item' => $learningpath->id,
                            'onclick' => $javascriptDelete));
                $output .= html_writer::end_tag('div');
                }
                $output .= html_writer::tag('div', '', array('class' => 'clearfix'));
            $output .= html_writer::end_tag('div');
        }
        return $output;
    }

    /**
     * Return html pagination for lists like users, cohorts, etc
     * @param (pages) number of pages
     * @param (class) it's class of object who is using this pagination, for example users, cohorts
     */
    function learningpaths_users_pagination($learningpath, $pages, $pagination, $active_page = 1, $users_per_page = 10)
    {
        global $CFG;
        $output = $select = $jump_to = "";
        $search_user = optional_param('user', '', PARAM_TEXT);
        
        // Buttons
        $output .= html_writer::start_tag('div', ['class' => 'row pagination-users center']);
            // Calculate first and last page that have to be visible for user
            $first_page = ($active_page > 5) ? $active_page - 5 : 1;
            if ($active_page >= ($pages - 5)) {
                $last_page = $pages;
                $first_page = $last_page - 10;
            } else {
                $last_page = $first_page + 10;
            }

            $first_page = ($first_page > 0) ? $first_page : 1;

            // Add first page if it isn't being shown
            $pages_list = '';
            /*
            if ($first_page != 1) {
                $href = "{$CFG->wwwroot}/local/learningpaths/view.php?id={$learningpath}&tab=users&page=1&items={$users_per_page}";
                if ($search_user != '')
                    $href .= "&user={$search_user}";
                $link = html_writer::tag('a', 1 . "...", ['href' => $href]);
                $pages_list .= html_writer::tag('li', "{$link}", ['class' => 'page-number']);
            }*/

            $href_base = "{$CFG->wwwroot}/local/learningpaths/view.php?id={$learningpath}&tab=users&page={%page%}&items={$users_per_page}";
            if ($search_user != '')
            $href_base .= "&user={$search_user}";

            
            //  previous button
            ($active_page == $first_page) ? $previous_page = $first_page : $previous_page = $active_page - 1;

            $href_prev = str_replace("{%page%}", $previous_page, $href_base);

            $link = html_writer::tag('a', '<i class ="wid wid-icon-previous"></i>', ['href' => $href_prev]);
            $pages_list .= html_writer::tag('li', $link, ['class' => 'page-number previous', 'data-page' => $previous_page, 'data-pagination' => $pagination]);

            // Create list of pages
            for ($i = $first_page; $i < $last_page; $i++) {
            
                $href = str_replace("{%page%}", $i, $href_base);                

                $link = ($active_page == $i) ? html_writer::tag('span', $i, ['class' => 'current-page']) : html_writer::tag('a', $i, ['href' => $href]);
                $pages_list .= html_writer::tag('li', $link, ['class' => 'page-number listing-pagination ', 'data-page' => $i, 'data-pagination' => $pagination]);
            }

            //  next button
            ($active_page == $last_page) ? $next_page = $last_page : $next_page = $active_page + 1;
            
            $href_next = str_replace("{%page%}", $next_page, $href_base);

            $link = html_writer::tag('a', '<i class="wid wid-icon-next"></i>', ['href' => $href_next]);
            $pages_list .= html_writer::tag('li', $link, ['class' => 'page-number next', 'data-page' => $i, 'data-pagination' => $pagination]);

            /*
            // Add last page if it isn't being shown
            if ($last_page != $pages) {
                $href = "{$CFG->wwwroot}/local/learningpaths/view.php?id={$learningpath}&tab=users&page={$pages}&items={$users_per_page}";
                if ($search_user != '')
                    $href .= "&user={$search_user}";
                $link = html_writer::tag('a', "... {$pages}", ['href' => $href]);
                $pages_list .= html_writer::tag('li', "{$link}", ['class' => 'page-number']);
            }*/

            // Pagination items
            $output .= html_writer::start_tag('ul', ['class' => 'pagination pagination-rlms']);
                $output .= $pages_list;
                $output .= html_writer::tag('li', $select);
                $output .= html_writer::tag('li', $jump_to);
            $output .= html_writer::end_tag('ul');
        $output .= html_writer::end_tag('div');
        return $output;
    }
}
