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

require_once($CFG->dirroot . '/blocks/overview/lib.php');
use html_writer;
class block_overview extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_overview');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $PAGE, $CFG;
      //  $PAGE->requires->css(new moodle_url('/blocks/overview/assets/app-style.css'));
             $PAGE->requires->js(new moodle_url('/blocks/overview/assets/js/app-script.js'), true);
            if ($this->content !== null) {
                return $this->content;
            }
    
            if (empty($this->instance)) {
                $this->content = '';
                return $this->content;
            }
            $company = get_current_editing_company_overview();
            $companyname = '';
            if(!empty($company) && $company != ''){
                $companyname = '('.$company->name.')';
            }
    
            $this->title = get_string('pluginname', 'block_overview').$companyname;
            $data = [];
    
            $brand_primary = '#407386';
            // Variables
            $totalusers = (int)get_users_by_company_overview(true);
            $numtotalusers = number_format($totalusers,0,"",",");
            $enrolled = get_enrolled_users_company_overview();
            $numenrolled = number_format($enrolled,0,"",",");
            $totalcourses = (int)get_courses_company_overview();
            $numtotalcourses = number_format($totalcourses,0,"",",");
            $organisation = (int)get_count_company_overview();
            $img1 = $CFG->wwwroot."/blocks/overview/assets/user.png";
            $img2 = $CFG->wwwroot."/blocks/overview/assets/client.png";
            $img3 = $CFG->wwwroot."/blocks/overview/assets/course.png";
            $img4 = $CFG->wwwroot."/blocks/overview/assets/activeuser.png";
    
            $urltcourses = new moodle_url('/course/index.php');
            $urltusers = new moodle_url('/admin/user.php');
            $urltorganisations = new moodle_url('/blocks/iomad_company_admin/editcompanies.php');
    
                        
                        $html = '<div class="row">
                        <div class="totalblock pinkborder col ">
                            <div class="pink"><img class = "stts" src="'.$img2.'" title="Total Organizations"></div>
                            <div class="text"><p>
                            <a href="'.$urltorganisations.'">'.get_string('total_organisation','block_overview').'</a><br />
                            <span>'.$organisation.'</span>
                            </p></div>
                        </div>
                        <div class="totalblock blueborder col "">
                            <div class="yellow"><img class = "stts" src="'.$img3.'" title="Total Courses"></div>
                            <div class="text">
                            <p>
                            <a href="'.$urltcourses.'">'.get_string('totalcourses','block_overview').'</a><br />
                                <span>'.$totalcourses.'</span>  
                            </p></div>
                        </div>
                        <div class="totalblock darkblueborder col ">
                            <div class="blue"><img class = "stts" src="'.$img1.'" title="Total Users"></div>
                            <div class="text"><p>
                            <a href="'.$urltusers.'">'.get_string('totalusers','block_overview').'</a><br />
                            <span>'.$numtotalusers.'</span>  
                        </p></div>
                        </div>
                        <div class="totalblock greenborder col ">
                            <div class="green"><img class = "stts" src="'.$img1.'" title="Active Users"></div>
                            <div class="text">
                            <p>
                            <a href="#">'.get_string('enrolled','block_overview').'</a><br />
                            <span>'.$numenrolled.'</span>
                           </p>
                           </div>
                        </div>
                    </div>';
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
            $this->title = get_string('pluginname', 'block_overview');
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
