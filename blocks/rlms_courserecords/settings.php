<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
	
      /** 
       * Setting to select date format to show 
       * @author Miguel p  
       * @since 03/06/2016
       * @rlms
      **/
      $name = 'select_date_format_courserecords';
      $title = get_string('date_format_title', 'block_rlms_courserecords');
      $description = get_string('date_format_desc', 'block_rlms_courserecords');
      $default = '1';
      $choices = array(
          '1' => get_string('dateformat1', 'block_rlms_courserecords'),
          '2' => get_string('dateformat2', 'block_rlms_courserecords'),
      );
      $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
      $settings->add($setting);      

}