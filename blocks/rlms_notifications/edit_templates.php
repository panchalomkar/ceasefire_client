<?php
require_once('../../config.php');

require_once($CFG->dirroot.'/blocks/rlms_notifications/edit_templates_form.php');

require_login();

//$newform = new block_rlms_notifications_edit_form() ;
$PAGE->set_title(get_string('pluginname', 'block_rlms_notifications'));
$curid = optional_param('id','',PARAM_INT);
$sesskey =  optional_param('sesskey','',PARAM_ALPHA);
$submitsave =  optional_param('editnotify','',PARAM_INT);

if( sesskey() )
{

if($submitsave <> '')
{
  $updatedone =0;
$data = new stdClass();
foreach ($_REQUEST as $key => $value)
{
  $data->$key = $value;
}


   $records2 = $DB->get_records('block_rlms_ntf');
    foreach($records2 as $record)
    {

        $data2 = new stdClass();
        $data2->course_id = $curid;
        $data2->notification_id = $record->id;
       
        $elementenabled_ = 'enabled_'.$record->name ;

        if( property_exists($data,$elementenabled_) ) {
          $data2->enabled = $data->{$elementenabled_}; /* enable */
        } else {
          $data2->enabled = 0 ;
        }

        $elementtemplate_ = 'template_'.$record->name ;
        $templateArray = $data->{$elementtemplate_} ;
        $data2->template = $templateArray['text']  ;

        $elementconfig_ = 'config_'.$record->name;

        if(!empty($data->{$elementconfig_}))
        {
          switch ($record->id) {
            // case 1:
            //
            //   break;
            case 2:
              $configJson= '{"notify_email_subject":"'.$data->{$elementconfig_}.'"}';
            break;
            case 3:
              $configJson= '{"days_after_enrolled":'.$data->{$elementconfig_}.'}';
            break;

            case 4:
            $configJson= '{"days_before_expiration":'.$data->{$elementconfig_}.'}';
            break;

            case 5:
            $configJson= '{"notify_email_subject":"'.$data->{$elementconfig_}.'"}';
            break;

          }
          $data2->config = $configJson ; /*input text*/
        }


        if(!$data2->template)
        {
          $data2->template = get_string('default_template_'.$record->name,'block_rlms_notifications' );
        }

        $settingdata = $DB->get_record('block_rlms_ntf_settings',array('course_id'=>$data->id,'notification_id'=>$record->id));
        $data2->id = $settingdata->id ;

        if($DB->update_record('block_rlms_ntf_settings', $data2)) {
            $updatedone = 1;
        }
    }
  }
}

// call form when data updated or new call
$newform = new block_rlms_notifications_edit_form() ;

echo $OUTPUT->header();

if(isset($curid) && trim($curid) != "" && isset($sesskey)) {
  $coursedata = $DB->get_record('course', array('id'=>$curid));
  echo "<h2>".get_string('pluginname', 'block_rlms_notifications').": ".$coursedata->fullname."</h2>";
  if( isset($updatedone) && $updatedone == 1 )
  {
    echo '<div class="notifysuccess alert course-notify-saved">'.get_string('changessaved').'</div>';
  }
  echo html_writer::start_tag('div',array('id'=>'edit_template_course_notifications'));
    $newform->display();
  echo html_writer::end_tag('div');
} else {
    header('Location: '.$CFG->wwwroot.'/course/explore_courses.php');
    exit(0); 
}


echo $OUTPUT->footer();
