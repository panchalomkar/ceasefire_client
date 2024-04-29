<?php

defined('MOODLE_INTERNAL') || die();

class block_rlms_myprogress extends block_base {

	function init() {
		$this -> title = get_string('pluginname', 'block_rlms_myprogress');
	}

	function instance_allow_multiple() {
		return true;
	}

	function has_config() {
		return true;
	}

	function applicable_formats() {
		return array('all' => true);
	}

	function instance_allow_config() {
		return true;
	}

	public function specialization() {
		if (empty( $this->config->title )) {
			$this->title = get_string('pluginname', 'block_rlms_myprogress');
		} else {
			$this->title = $this -> config -> title;
		}
	}

	function get_content() {
		global $COURSE, $USER, $CFG, $DB, $OUTPUT, $PAGE;
		
		$this->content = new stdClass();

		$PAGE->requires->css(new moodle_url('/blocks/rlms_myprogress/css/my_progress.css'));
		//$PAGE -> requires -> js(new moodle_url('/blocks/rlms_myprogress/js/common.js'));
		$PAGE->requires->js(new moodle_url('/blocks/rlms_myprogress/js/highcharts.js'));

		//get user
		$uid = $USER->id;
		$user = $DB->get_record('user', array('id' => $USER -> id));

		if ( isset($this->config) && ! isset($this->config->numberofrecords) ) {
			$this->config->numberofrecords = 2;
		}

		// Get courses percent : not started, in progress and completed
		//get courses enrolled for this user
		$courses = enrol_get_users_courses($USER -> id);

		/**
		* Validating if user has progress
		* @author Deyby G.
		* @since April 08 of 2017
		* @ticket 916
		* @rlms
		*/
		if (count($courses) > 0){

			$ccompleted = 0;
			$cnoyetstarted = 0;
			$cinprogress = 0;
            require_once($CFG->dirroot . '/lib/completionlib.php');
			foreach ($courses as $course) {
				// Load course.
				$course = $DB->get_record('course', array('id' => $course -> id), '*', MUST_EXIST);

				// Load completion data.
				$info = new completion_info($course);
				
				/**
				* Jump course from iteration if course dont have 
				* course completion tracking
				* @author Esteban E. 
				* @since June 28 2017
				* @rlms
				*/

				if(!$info->is_enabled())
					continue ;

				// Is course complete?
				$coursecomplete = $info->is_course_complete($uid);

				// Has this user completed any criteria?
				$criteriacomplete = $info->count_course_user_data($uid);

				// Load course completion.
				$params = array('userid' => $uid, 'course' => $course -> id);

				$ccompletion = new completion_completion($params);

				if ($coursecomplete) {
					$ccompleted++;
				} else if (!$criteriacomplete && !$ccompletion -> timestarted) {
					$cnoyetstarted++;
				} else {
					$cinprogress++;
				}
			}
			//===================================================

			//get user access info
			$sql = 'select * from {log} where userid=?';
			$logins = $DB -> get_records_sql($sql, array($uid));
			$logins = count($logins);
			//number of access for this user

			//get courses enrolled for this user
			$courses = count(enrol_get_users_courses($uid));

			if ($courses > 0) {
				$progress = ($ccompleted / $courses) * 100;
			} else {
				$progress = 0;
			}

			//user data array
			$userdata = array();

			$userdata[] = $OUTPUT->user_picture($user, array('size' => 50));

			//user info
			$userdata[] = '<a target="_blank" href="' . new moodle_url('/user/profile.php', array('id' => $user -> id)) . '">' . $user -> firstname . ' ' . $user -> lastname . '</a>';

			//data
			$per1 = number_format(($ccompleted / $courses) * 100, 2);
			$per2 = number_format(($cnoyetstarted / $courses) * 100, 2);
			$per3 = number_format(($cinprogress / $courses) * 100, 2);

			//color
			$color0 = get_config('block_rlms_myprogress', 'backgroundcolor');
			$color1 = get_config('block_rlms_myprogress', 'completedcolor');
			$color2 = get_config('block_rlms_myprogress', 'notyetstartedcolor');
			$color3 = get_config('block_rlms_myprogress', 'inprogresscolor');

			/**
			* replace by default my progrress legends bar color
			* @author: Miguel @rlms
			* @since: Dec 2016
			*/
			if($color1 == '#04EB62' && $color2 == '#C60300' && $color3 == '#FF950A'){
				$sql = "UPDATE {config_plugins} SET value = '#8fb644' WHERE plugin = 'block_rlms_myprogress' AND name = 'completedcolor' ";
	            $DB->execute($sql);
				$color1 = '#8fb644';	
				$sql = "UPDATE {config_plugins} SET value = '#cf6284' WHERE plugin = 'block_rlms_myprogress' AND name = 'notyetstartedcolor' ";
	            $DB->execute($sql);
				$color2 = '#cf6284';		
				$sql = "UPDATE {config_plugins} SET value = '#3f9ddb' WHERE plugin = 'block_rlms_myprogress' AND name = 'inprogresscolor' ";
	            $DB->execute($sql);
	            $color3 = '#3f9ddb';
			}

			$user_id = $USER->id;
			$PAGE->requires->jquery();
			$this -> content -> text = '';
			$this -> content -> text .= "<script>$(function () {

	    	var colors = Highcharts.getOptions().colors,
	        categories = ['', '', ''],
	        data = [{
					y: $per1,
					url: M.cfg.wwwroot + '/blocks/rlms_myprogress/list_completed.php?uid=$user_id',
					color: '" . $color1 . "',
					drilldown: {
						name: 'Completed',
						categories: ['".get_string('completed','block_rlms_myprogress').$ccompleted."'],
						data: [$per1],
						color: '" . $color1 . "'
					}
					}, {
					y: $per2,
					url: M.cfg.wwwroot + '/blocks/rlms_myprogress/list_notstarted.php?uid=$user_id',
					color: '" . $color2 . "',
					drilldown: {
						name: 'not started',
						categories: ['".get_string('notyetstarted','block_rlms_myprogress').$cnoyetstarted."'],
						data: [$per2],
						color: '" . $color2 . "'
					}
					}, {
					y: $per3,
					url: M.cfg.wwwroot + '/blocks/rlms_myprogress/list_inprogress.php?uid=$user_id',
					color: '" . $color3 . "',
					drilldown: {
						name: 'In progress',
						categories: ['".get_string('inprogress','block_rlms_myprogress').$cinprogress."'],
						data: [$per3],
						color: '" . $color3 . "'
					}
					}
	        ],
	        browserData = [],
	        versionsData = [],
	        i,
	        j,
	        dataLen = data.length,
	        drillDataLen,
	        brightness;


	    // Build the data arrays
	    for (i = 0; i < dataLen; i += 1) {

	        // add browser data
	        browserData.push({
	            name: categories[i],
	            y: data[i].y,
	            color: data[i].color
	        });

	        // add version data
	        drillDataLen = data[i].drilldown.data.length;
	        for (j = 0; j < drillDataLen; j += 1) {
	            brightness =  (j / drillDataLen) / 5;
	            versionsData.push({
	                name: data[i].drilldown.categories[j],
	                y: data[i].drilldown.data[j],
	                color: Highcharts.Color(data[i].color).brighten(brightness).get(),
	                url: data[i].url
	            });
	        }
	    }

	    // Create the chart
	    $('#plms-my-progress-container').highcharts({
	        chart: {
	            type: 'pie',
				backgroundColor: 'transparent'

	        },
			legend: {
				align: 'right',
				backgroundColor: '#ff0000',
				itemDistance: 20,
				enabled: false
			},
	        title: {
	            text: ''
	        },
	        yAxis: {
	            title: {
	                text: ''
	            }
	        },
	        plotOptions: {
	            pie: {
	                shadow: false,
					plotBackgroundColor: 'rgba(255, 0, 0, .9)',
	                center: ['50%', '50%']
	            }
	        },
	        tooltip: {
	            valueSuffix: '%'
	        },
	        series: [ {
	            name: '".get_string('progress','block_rlms_myprogress')."',
	            data: versionsData,
	            size: '100%',
	            innerSize: '70%',	
	            dataLabels: {
	                formatter: function () {
	                    // display only if larger than 1 Dani Otelch comment the last line hide the legends
	                   // return this.y > 1 ? '<b>' + this.point.name + ':</b> ' + this.y + '%'  : null;
	                }
	            },
	            point:{
		            events:{
		                click: function (event) {
		                	location.href = this.url;
		                }
		            }
		        },
	            cursor: 'pointer'
	        }]
	    });
	});</script>";

			$this->content->text .= '<div id="plms-my-progress-container" style="width:100%; height:170px;"></div>';
        /**
        * add a class and change the column (4) to (12)
        * @autor Hugo S.
        * @since 18/06/2018
        * @rlms
        * @ticket 21
        */
       
        $comletedtxt = get_string('completed','block_rlms_myprogress').$ccompleted;
        $inprogresstxt = get_string('inprogress','block_rlms_myprogress').$cinprogress;
        $notyetstartedtxt = get_string('notyetstarted','block_rlms_myprogress').$cnoyetstarted;

        $this->content->text.='<div id="myprogress-legend" class="col-sm-12">';
	       	
	       	$this->content->text.='<div class="contend-legend">';
	       		$this->content->text.='<a href="'.new moodle_url('/blocks/rlms_myprogress/list_completed.php',array('uid'=>$USER->id)).'" style="background-color:'.$color1.'">';
	       		$this->content->text.='</a>';
	       		$this->content->text.='<div class="legend_pie">';
	       			$this->content->text.= $comletedtxt;
	       		$this->content->text.='</div>';
	       	$this->content->text.='</div>';

	       	$this->content->text.='<div class="contend-legend">';
	       		$this->content->text.='<a href="'.new moodle_url('/blocks/rlms_myprogress/list_inprogress.php',array('uid'=>$USER->id)).'" style="background-color:'.$color3.'"></a>';
	       		$this->content->text.='<div class="legend_pie">';
	       			$this->content->text.= $inprogresstxt;
	       		$this->content->text.='</div>';
	       	$this->content->text.='</div>';

	       	$this->content->text.='<div class="contend-legend">';
	       		$this->content->text.='<a href="'.new moodle_url('/blocks/rlms_myprogress/list_notstarted.php',array('uid'=>$USER->id)).'" style="background-color:'.$color2.'"></a>';
	       		$this->content->text.='<div class="legend_pie">';
	       			$this->content->text.= $notyetstartedtxt;
	       		$this->content->text.='</div>';
	       	$this->content->text.='</div>';

       	$this->content->text.='</div>';
       	
			#$this -> content -> text .= "c $per1 n  $per2 p $per3";
			//===============================================
		}
	}

}
