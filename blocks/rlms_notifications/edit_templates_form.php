<?php
/////////////////////////////////////////////////////
// COURSE SETTINGS
////////////////////////////////////////////////////
class block_rlms_notifications_edit_form extends moodleform
{

    public function definition() {
        global $CFG , $DB, $COURSE;

        $course_id = optional_param('id','',PARAM_INT);

        //ddd( $course_id );

        $mform = $this->_form;
        // first step, lets look for the configuration for this particular course
        $sql = "
        SELECT
            s.*
            ,n.name
            ,n.config as defaultvalue
        FROM
            {block_rlms_ntf_settings} AS s
            INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id
        WHERE s.course_id = '".$course_id."'";
        $records = $DB->get_records_sql($sql);

        // no records found, then we have to insert default values
        if(!$records)
        {
            $records2 = $DB->get_records('block_rlms_ntf');
            foreach($records2 as $record)
            {
                $data = new stdClass();
                $data->course_id = $course_id;
                $data->notification_id = $record->id;
                $data->enabled = 0;
                $data->config = $record->config;
                $data->template = $record->template;

                $DB->insert_record('block_rlms_ntf_settings', $data);
            }

            $records = $DB->get_records_sql($sql);
        }
        else
        {
            // @author Carlos Alcaraz
            // Define notify_on_course_enroll_student as string, not as variable
            // @rlms
            $new_notify = $DB->get_record('block_rlms_ntf', array('name'=>'notify_on_course_enroll_student'), '*', MUST_EXIST);
            if($new_notify)
            {

                $no_setting_new_notify = $DB->get_record('block_rlms_ntf_settings', array('notification_id'=>$new_notify->id), '*');

                if(!$no_setting_new_notify)
                {
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
        $mform->addElement('hidden', 'id', $course_id);
        $mform->addElement('hidden', 'editnotify', '1');
        $mform->addElement( 'header', 'hidde-id-element', 'hidde', 'block_rlms_notifications');
        foreach($records as $record)
        {
            // Fields for editing HTML block title and contents.
            $prefix = md5($record->name);
            $BlockName = str_replace(' ','_',$record->name);
            $mform->addElement( 'header', 'configheader'.$record->name, get_string($record->name, 'block_rlms_notifications'));

          //  $mform->addElement('static', 'description', '', get_string("{$record->name}_desc", 'block_rlms_notifications'));
                $attr = array();
                $checked = '' ;
                if( $record->enabled == 1 ){
                    $checked = 'checked';
                }
                
                $mform->addElement('html', '
                    <div id="fitem_id_enabled_'.$BlockName.'" class="form_element col-sm-6 fitem_fcheckbox ">

                        <div class="fitemtitle">
                        <label for="id_enabled_notify_on_course_completion_student">'.get_string('enabled', 'block_rlms_notifications').'</label>
                            <span class="helptooltip">
                                <a href="'.$CFG->wwwroot.'/help.php?component=block_rlms_notifications&amp;identifier=check_enable_notifi_'.$BlockName.'&amp;lang=en" title="Help with Course completion notification to student" aria-haspopup="true" target="_blank">
                                
                                <img src="'.$CFG->wwwroot.'/theme/image.php/rlmslmsfull/core/1468006252/help" alt="Help with Course completion notification to student" class="iconhelp">
                                </a>
                            </span>
                        </div>

                        <div class="felement fcheckbox">
                            <span>
                                <label class="form-checkbox form-normal form-icon-text form-plms">

                                    <input type="checkbox" id="id_enabled_'.$BlockName.'" name="enabled_'.$BlockName.'" value="1" '.$checked.' >

                                </label>
                            </span>
                        </div>

                    </div>

            ');    

  
            // the specific configuration for this notifications
            if(!empty($record->defaultvalue))
            {
                $config = ( $record->config == "" ) ? @json_decode($record->defaultvalue, true) : @json_decode($record->config, true);

                if($config)
                {
                    foreach($config as $key => $value)
                    {
                        $attributes = array();
                        $element = $mform->addElement('text', "config_{$BlockName}", get_string($key, 'block_rlms_notifications'), $attributes);
                        $mform->addHelpButton("config_{$BlockName}",'text_input_'.$BlockName, 'block_rlms_notifications');
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
              $mform->addHelpButton("template_{$BlockName}",'email_template_notify_'.$BlockName, 'block_rlms_notifications');

              /**
             * @description: if template saved in DB it's NO TEMPLATE YET
             * then replace that text with default templates.
             * @author Esteban E.
             * @since March 31 of 2016
             * @rlms
             */
              if($record->template == 'NO TEMPLATE YET')
              {
                 $templatevalue = get_string('default_template_'.$BlockName,'block_rlms_notifications' ) ;
              }
              else
              {
                $templatevalue = $record->template ;
              }

            $mform->setDefault("template_{$BlockName}", array('text' => $templatevalue));

        }
        //$mform->addElement( 'html', '</div>');


        $backtocourse=$CFG->wwwroot.'/course/view.php?id='.$course_id;
        $this->add_action_buttons(true, get_string('savechanges'),$backtocourse);

    }

    public function add_action_buttons($cancel = true, $submitlabel = null ,$urlback=null) {
        $mform =& $this->_form;
        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
        $buttonarray[] = &$mform->createElement('button', 'cancelbutton', get_string('back_to_course', 'block_rlms_notifications'), array('onclick'=>"location.href='".$urlback."'"));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}
