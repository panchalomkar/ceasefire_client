<?php
/////////////////////////////////////////////////////
// COURSE SETTINGS
////////////////////////////////////////////////////
class block_rlms_notifications_edit_form extends block_edit_form
{

    protected function specific_definition( $mform )
    {

        global $CFG;
        global $COURSE;
        global $DB;

        // first step, lets look for the configuration for this particular course

        // $sql = "
        // SELECT
        //     s.*
        //     ,n.name
        // FROM
        //     {block_rlms_ntf_settings} AS s
        //     INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id
        // WHERE s.course_id = '{$COURSE->id}'
        // ";
        // $records = $DB->get_records_sql($sql);

        // no records found, then we have to insert default values
        // if(!$records)
        // {
        //     $records2 = $DB->get_records('block_rlms_ntf');
        //     foreach($records2 as $record)
        //     {
        //         $data = new stdClass();
        //         $data->course_id = $COURSE->id;
        //         $data->notification_id = $record->id;
        //         $data->enabled = 0;
        //         $data->config = $record->config;
        //         $data->template = $record->template;
        //
        //         $DB->insert_record('block_rlms_ntf_settings', $data);
        //     }
        //
        //     $records = $DB->get_records_sql($sql);
        // }
        // else
        // {
        //     $new_notify = $DB->get_record('block_rlms_ntf', array('name'=>notify_on_course_enroll_student), '*', MUST_EXIST);
        //     if($new_notify)
        //     {
        //
        //         $no_setting_new_notify = $DB->get_record('block_rlms_ntf_settings', array('notification_id'=>$new_notify->id), '*');
        //
        //         if(!$no_setting_new_notify)
        //         {
        //             $data2 = new stdClass();
        //             $data2->course_id = $COURSE->id;
        //             $data2->notification_id = $new_notify->id;
        //             $data2->enabled = 0;
        //             $data2->config = $new_notify->config;
        //             $data2->template = $new_notify->template;
        //             $DB->insert_record('block_rlms_ntf_settings', $data2);
        //             $records = $DB->get_records_sql($sql);
        //
        //         }
        //     }
        // }
        //
        // $mform->addElement( 'html', '<style>.collapsible-actions{display:none;}</style><a href="#" id="header-legend-template" class="fheader" role="button" aria-controls="" aria-expanded="true" id=""><legend class="ftoggler"><span class="ftoggler fa fa-caret-right" id="header-legend-template-id"></span>Templates</legend></a><div class="collapsed_templates_course_notification">');
        // $mform->addElement( 'header', 'hidde-id-element', 'hidde', 'block_rlms_notifications');
        // foreach($records as $record)
        // {
            // Fields for editing HTML block title and contents.
            // $prefix = md5($record->name);
            // $BlockName = str_replace(' ','_',$record->name);
            // $mform->addElement( 'header', 'configheader'.$record->name, get_string($record->name, 'block_rlms_notifications'));

          //  $mform->addElement('static', 'description', '', get_string("{$record->name}_desc", 'block_rlms_notifications'));

            // {
            //     $attr = array();
            //     $attr['value'] = 1;
            //
            //     $checkbox = $mform->addElement('advcheckbox', "enabled_{$BlockName}", get_string('enabled', 'block_rlms_notifications'), null, $attr, array(0, 1));
            //     $mform->addHelpButton("enabled_{$BlockName}",'check_enable_notifi_'.$BlockName, 'block_rlms_notifications');
            //     if($record->enabled)
            //         $checkbox->setChecked(true);
            // }

            // the specific configuration for this notifications
            // if(!empty($record->config))
            // {
            //     $config = @json_decode($record->config, true);
            //
            //     if($config)
            //     {
            //         foreach($config as $key => $value)
            //         {
            //             $attributes = array();
            //             $element = $mform->addElement('text', "config_{$BlockName}", get_string($key, 'block_rlms_notifications'), $attributes);
            //             $mform->addHelpButton("config_{$BlockName}",'text_input_'.$BlockName, 'block_rlms_notifications');
            //             $element->setValue($value);
            //         }
            //     }
            // }
            //
            // $element = $mform->addElement('editor', "template_{$BlockName}", get_string('template', 'block_rlms_notifications'));

            /**
             * @description: Add validation to allow show the help button only
             * on enrol notification
             *
             * @author Esteban E.
             * @since Dic 29 of 2015
             * @rlms
             */
            // if($record->name == 'notify_on_course_enroll_student')
            // {
                // $mform->addHelpButton("template_{$BlockName}",'email_template_notify_'.$BlockName, 'block_rlms_notifications');
          //  }

            // $mform->setDefault("template_{$BlockName}", array('text' => get_string('default_template_'.$BlockName,'block_rlms_notifications' )));
        // }
        // $mform->addElement( 'html', '</div>');

    }
}
