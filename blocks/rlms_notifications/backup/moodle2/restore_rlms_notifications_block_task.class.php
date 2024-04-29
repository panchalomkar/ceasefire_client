<?php
/**
 * choice restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */

require_once($CFG->dirroot . '/blocks/rlms_notifications/backup/moodle2/restore_rlms_notifications_stepslib.php'); // Because it exists (must)

class restore_rlms_notifications_block_task extends restore_block_task {

    /**
     * Return the old course context ID.
     * @return int
     */
    public function get_old_course_contextid() {
        return $this->plan->get_info()->original_course_contextid;
    }

    /**
     * Define my settings.
     */
    protected function define_my_settings() {
    }

    /**
     * Define my steps.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_rlms_notifications_block_structure_step('rlms_notifications', 'rlms_notifications.xml'));
    }

    /**
     * File areas.
     * @return array
     */
    public function get_fileareas() {
        return array();
    }

    /**
     * Config data.
     */
    public function get_configdata_encoded_attributes() {
    }

    /**
     * Define decode contents.
     * @return array
     */
    public static function define_decode_contents() {
        return array();
    }

    /**
     * Define decode rules.
     * @return array
     */
    public static function define_decode_rules() {
        return array();
    }

    /**
     * Encore content links.
     * @param  string $content The content.
     * @return string
     */
    public static function encode_content_links($content) {
        return $content;
    }
}
