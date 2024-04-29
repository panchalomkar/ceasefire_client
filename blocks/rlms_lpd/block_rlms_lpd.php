<?php


defined('MOODLE_INTERNAL') || die();
/*if(empty($PAGE->nologin) && ! isloggedin()) {
    redirect($CFG->wwwroot);
}*/
require_once("lib/lib.php");

class block_rlms_lpd extends block_base {

    public function init() {
        $this->title = get_string('pluginname', 'block_rlms_lpd');
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function has_config() {
        return true;
    }

    public function instance_allow_config() {
        return true;
    }

    public function applicable_formats() {
        return array(
            'admin' => false,
            'site-index' => true,
            'course-view' => true,
            'mod' => false,
            'my' => true
        );
    }

    public function specialization() {
        global $SESSION,$DB;

        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_rlms_lpd');
        } else {
            $this->title = $this->config->title;
        }
    }

    public function get_content() {
        global $USER, $PAGE, $CFG;
        $page = optional_param('page', 0, PARAM_INT);
        $lp_id = optional_param('lp_id', 0, PARAM_INT);
        
        if ($this->content !== null) {
            return $this->content;
        }
        
        if (empty($this->config)) {
            $this->config = new stdClass();
        }
        
        //Create empty content
        $this->content = new stdClass();
        $this->content->text = '';
        
        //$PAGE->requires->js(new moodle_url('/blocks/rlms_lpd/js/rlmslpd.js'), array());
        $PAGE->requires->js(new moodle_url("{$CFG->wwwroot}/blocks/rlms_lpd/js/functions.js"));
        $PAGE->requires->js_call_amd('block_rlms_lpd/rlmslpd', 'init');
        try {
            $config = get_config('block_rlms_lpd');
            if (isset($this->config->learningpath)) {
                $this->content->text .= displayLearningPathsViewDetail($this->config->learningpath, $USER->id, $page );
            } else {
                $this->content->text .= displayLearningPathsView($config->evaluate_progress, $USER->id, $page);
            }
        } catch (Exception $e) {
            throw $e;
        }
        
        return $this->content;
    }
    

}
