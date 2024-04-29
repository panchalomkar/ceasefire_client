<?php
/**
 * Structure step to restore one choice activity
 */
class restore_rlms_notifications_block_structure_step extends restore_structure_step {

    /**
     * Execution conditions.
     *
     * @return bool
     */
    protected function execute_condition() {
        global $DB;

        // No restore on the front page.
        if ($this->get_courseid() == SITEID) {
            return false;
        }

        return true;
    }

    /**
     * Define structure.
     */
    protected function define_structure() {
        $paths = array();

        $paths[] = new restore_path_element('rlms_notifications', '/block/settings');

        return $paths;
    }

    public function process_rlms_notifications($data){
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        unset($data->id);
        $data->course_id = $this->get_courseid();

        // insert the choice record
        $newitemid = $DB->insert_record('block_rlms_ntf_settings', $data);

        // immediately after inserting "activity" record, call this
        //$this->apply_block_instance($newitemid);
    }


    /**
     * After execute.
     */
    protected function after_execute() {
        $this->add_related_files('block_rlms_notifications', 'intro', null, $contextid = null);
    }

}


