<?php

require_once $CFG->dirroot . '/blocks/rlms_notifications/backup/moodle2/backup_rlms_notifications_stepslib.php';

/**
 * Default block task to backup blocks that haven't own DB structures to be added
 * when one block is being backup
 *
 * TODO: Finish phpdocs
 */
class backup_rlms_notifications_block_task extends backup_block_task {
    // Nothing to do, it's just the backup_block_task in action
    // with required methods doing nothing special

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
        $this->add_step(new backup_rlms_notifications_block_structure_step('rlms_notifications', 'rlms_notifications.xml'));
    }

    public function get_fileareas() {
        return array();
    }

    public function get_configdata_encoded_attributes() {
        return array();
    }

    static public function encode_content_links($content) {
        return $content;
    }
}

