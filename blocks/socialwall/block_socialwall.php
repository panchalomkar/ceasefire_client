<?php

class block_socialwall extends block_base {

	public function init() {
		$this -> title = get_string('socialwall', 'block_socialwall');
	}

	public function get_content() {
		global $CFG;
		global $PAGE;
		global $COURSE;
		global $DB;


            $course = $DB->get_record('course', array('id' => $COURSE->id));
            if ($course->id == SITEID) {
                $context = context_system::instance();
            } else {
                $context = context_course::instance($course->id);
            }

		// current course id
		$courseid = $context->instanceid;

		if ($this -> content !== null) {
			return $this -> content;
		}

		/*----------------------------------------*/
		$region = $this -> instance -> region;
		if ('side-pre' == $region || 'side-post' == $region) {
			$this -> content = new stdClass;
			$this -> content -> text = 'This block is only available to be displayed on Center region.';

			return $this -> content;
		}

		//get config settings
		$iframewidth = $this -> config -> iframewidth;
		$iframeheight = $this -> config -> iframeheight;
		$whatdoyouthink = get_string($this -> config -> whatdoyouthink, 'block_socialwall');
		$url = $CFG -> wwwroot . '/wall/index.php';
		$scroll = 'auto';

		//parametters
		$url .= '?Order=0';
		$url .= '&CourseId=' . $courseid;
		$url .= '&Id=' . $courseid;
		$url .= '&Likes=1';
		$url .= '&AdminOnly=0';
		$url .= '&Email=0';
		//$url .= '&Desc=' . $whatdoyouthink;

		$iframe = '
			<iframe 
			width="' . '100%' . '" 
			height="' . '650px' . '" 
			src="' . $url . '"
			frameborder="0" 
			marginwidth="0" 
			marginheight="0"
			scrolling="' . $scroll . '"></iframe>';

		$this -> content = new stdClass;
		$this -> content -> text = $iframe;
		$this -> content -> footer = '';

		return $this -> content;
	}

	public function specialization() {
		if (!empty($this -> config -> title)) {
			$this -> title =  get_string($this -> config -> title,'block_socialwall');
		} else {
			$this -> config -> title = get_string('Social Wall','block_socialwall');
		}
	}

	public function instance_allow_multiple() {
		return true;
	}

	function has_config() {
		return false;
	}

	function applicable_formats() {
		return array('all' => true);
	}

    public function instance_create() {
        
        global $SESSION;
        
        $company = isset($SESSION->currenteditingcompany) ? $SESSION->currenteditingcompany : 0;
        
        $config = (object)array(
            'tenant' => $company,
        );
        
        $this->instance_config_save($config);

        return true;
    }

}
