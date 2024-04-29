<?php

class backup_rlms_notifications_block_structure_step extends backup_block_structure_step {
    protected function define_structure() {
        global $DB;

        $params = array('courseid' => $this->get_courseid());
        $context = context_course::instance( $params['courseid']);

        $settings = new backup_nested_element('settings', array('id'), array(
            'enabled', 'config','template','notification_id'));

        $sql = 'SELECT * FROM {block_rlms_ntf_settings} WHERE course_id = '.$this->get_courseid();
        $settings->set_source_sql( $sql,array() );


        $settings->annotate_files('rlms_notifications', 'intro', null, $contextid = null);

        return $this->prepare_block_structure($settings);

    }
}
