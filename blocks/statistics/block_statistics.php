<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once($CFG->dirroot . '/blocks/statistics/lib.php');
use html_writer;
class block_statistics extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_statistics');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $PAGE, $CFG;
$PAGE->requires->js(new moodle_url('/blocks/statistics/assets/js/jquery.min.js'), true);
$PAGE->requires->js(new moodle_url('/blocks/statistics/assets/js/jquery.easypiechart.min.js'), true);

$PAGE->requires->js(new moodle_url('/blocks/statistics/assets/js/circle.js'), true);
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }
        $company = get_current_editing_company();
        $companyname = '';
        if(!empty($company) && $company != ''){
            $companyname = '('.$company->name.')';
        }

        $this->title = get_string('pluginname', 'block_statistics').$companyname;
        $data = [];

        $brand_primary = '#407386';
        // Variables
        $totalusers = (int)get_users_by_company(true);
        $numtotalusers = number_format($totalusers,0,"",",");
        $enrolled = get_enrolled_users_company();
        $numenrolled = number_format($enrolled,0,"",",");
        $notenrolled = $totalusers - $enrolled;
        $numnotenrolled = number_format($notenrolled,0,"",",");
        $totalcourses = (int)get_courses_company();
        $numtotalcourses = number_format($totalcourses,0,"",",");

        
        $percentage_enrolled = 0;
        if($totalusers > 0){
           $percentage_enrolled = round($enrolled * 100 / $totalusers,1);
        }

        $percentage_noenrolled = 0;
        if($totalusers > 0){
            $percentage_noenrolled = round($notenrolled * 100 / $totalusers,1);
        }

        $urltcourses = new moodle_url('/course/index.php');
        $urltusers = new moodle_url('/admin/user.php');

        $html = '<div class="row">
              <div class="col-md-6 col-sm-12 col-lg-6 col-xl-3 graph">
                <div class="chart" data-percent="100" data-scale-color="#76838f"><span>'.$totalusers.'</span></div>
                <a href="'.$urltusers.'">Total users</a>
              </div>
              <div class="col-md-6 col-sm-12 col-lg-6 col-xl-3 graph">
                <div class="chart" data-percent="'.$enrolled.'" data-scale-color="#ffb400" title = "'.$percentage_enrolled.'% of users enrolled."><span>'.$enrolled.'</span></div>
                <a href="#">Enrolled users</a>
              </div>
              <div class="col-md-6 col-sm-12 col-lg-6 col-xl-3 graph">
                <div class="chart" data-percent="'.$notenrolled.'" data-scale-color="#ffb400" title = "'.$percentage_noenrolled.'% of users not enrolled."><span>'.$notenrolled.'</span></div>
                <a href="#">Not active users</a>
              </div>
              <div class="col-md-6 col-sm-12 col-lg-6 col-xl-3 graph">
                <div class="chart" data-percent="100" data-scale-color="#ffb400"><span>'.$totalcourses.'</span></div>
                <a href="'.$urltcourses.'">Total courses</a>
              </div>
            </div>';
            //         $html = '<div class="container">
            //   <div class="graph">
            //     <div class="chart" data-percent="100" data-scale-color="#76838f">'.$totalusers.'</div>
            //     <a href="'.$urltusers.'">Total</a>
            //   </div>
            //   <div class="graph">
            //     <div class="chart" data-percent="'.$enrolled.'" color="#ffb400">'.$enrolled.'<div>
            //     <a href="#">Total</a>
            //   </div>
            //   <div class="graph">
            //     <div class="chart" data-percent="'.$notenrolled.'" color="#ffb400">'.$notenrolled.'</div>
            //     <a href="#">total</a>
            //   </div>
            //   <div class="graph">
            //     <div class="chart" data-percent="100" data-scale-color="#ffb400">'.$totalcourses.'</div>
            //     <a href="'.$urltcourses.'">Total</a>
            //   </div>
            // </div>';
        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $this->content->text = $html;
        }

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediatly after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_statistics');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array('all' => false, 'site-index' => true, 'course-view' => false);
    }
}
