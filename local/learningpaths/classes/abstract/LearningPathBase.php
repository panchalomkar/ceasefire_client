<?php
defined('MOODLE_INTERNAL') || die;
class LearningPathBase {
    protected $db;
    protected $page;

    /*
     * Contruct learning Path Base object
     */
    public function __construct() {
        // Assign moodle objects like learning path object attributes for internal usage.
        global $DB, $PAGE;
        $this->db = $DB;
        $this->page = $PAGE;
    }


}