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


require_once($CFG->dirroot . '/blocks/courses_statistics/lib.php');


class block_courses_statistics extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_courses_statistics');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $CFG,$OUTPUT;
        $brandprimary = get_config('theme_remui','brandprimary');
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }
        $company = get_current_editing_company_cs();
        $companyname = '';
        if(!empty($company) && $company != ''){
            $companyname = '('.$company->name.')';
        }

        $this->title = get_string('pluginname', 'block_courses_statistics').$companyname;
        // Variables
        $totalusers = (int)get_users_by_company_cs(true);
        $enrolled = get_enrolled_users_company_cs();
        $notenrolled = $totalusers - $enrolled;
        $totalcourses = get_courses_company_cs();
        
        $percentage_enrolled = 0;
        if($totalusers > 0){
           $percentage_enrolled = round($enrolled * 100 / $totalusers,1);
        }

        $percentage_noenrolled = 0;
        if($totalusers > 0){
            $percentage_noenrolled = round($notenrolled * 100 / $totalusers,1);
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        
        $numdata = isset($this->config->numdata) ? $this->config->numdata : 5;
        
        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            
            $companyid = isset($company->id) ? $company->id :'';
            $get_top_enrolled = get_top_enrolled($numdata,$companyid);

            $get_top_viewed = get_top_viewed($numdata,$companyid);
       
            $text = '';
            $text .= html_writer::start_tag('div', array('class' => 'row course-overview'));
                
                $text .= html_writer::start_tag('div', array('id' => 'first-section','class' => 'col-xs-12 col-sm-12 col-md-6 col-lg-6 left pl-0'));
                    
                    $url = new moodle_url('/blocks/configurable_reports/viewreport.php',['id'=>11]);

                    $text .= html_writer::start_tag('div', array('class' => 'element-groups row first-section'));
                        $text .= html_writer::start_tag('div', array('class' => 'col-xs-12 col-sm-12 col-md-12 col-lg-12 pl-0 first-section'));
                            $text .= html_writer::start_tag('div', array('class' => 'mb-2'));
                                $text .= html_writer::tag('a',get_string('most_viewed', 'block_courses_statistics'), array('class' => 'view-course' , 'href' => '#'));
                            $text .= html_writer::end_tag('div');
                                
                                $top_viewc = array();
                                 
                                foreach($get_top_viewed as $p){
                                    $top_viewc[$p->course] = $p->views;
                                }

                                $chart = new \core\chart_pie();
                                $chart->set_doughnut(true);
                                $serie1 = new core\chart_series('Number of views', array_values($top_viewc));
                                $chart->add_series($serie1);
                                $chart->set_labels(array_keys($top_viewc));
                             $text .= $OUTPUT->render_chart($chart, true);
                        $text .= html_writer::end_tag('div');//End col-12
                    $text .= html_writer::end_tag('div');//End element-id

                $text .= html_writer::end_tag('div');//End group of elements

                $text .= html_writer::start_tag('div', array('id' => 'second-section' ,'class' => 'col-xs-12 col-sm-12 col-md-6 col-lg-6'));
                    $text .= html_writer::start_tag('div', array('class' => 'element-groups row'));
                        $text .= html_writer::start_tag('div', array('class' => 'col-sm-12 col-md-12 col-lg-12 pr-0 secont-content'));
                            
                        $text .= html_writer::start_tag('div', array('class' => 'mb-2'));
                            $text .= html_writer::tag('a', get_string('most_enrolled', 'block_courses_statistics') , array('class' => 'view-course' , 'href' => '#'));
                        $text .= html_writer::end_tag('div');
                          $most_enrolled = array();
                            foreach($get_top_enrolled as $p){
                                    $most_enrolled[$p->course] = $p->enrolled;
                                }
                                $chart = new \core\chart_pie();
                                $chart->set_doughnut(true);
                                $serie1 = new core\chart_series('Number of enrolled users', array_values($most_enrolled));
                            $chart->add_series($serie1);
                            $chart->set_labels(array_keys($most_enrolled));
                            $text .= $OUTPUT->render_chart($chart, true);
                        
                    $text .= html_writer::end_tag('div');//End col-12
                $text .= html_writer::end_tag('div');//End element-id
                $text .= html_writer::end_tag('div');

            $text .= html_writer::end_tag('div');
            $this->content->text = $text;
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
            $this->title = get_string('pluginname', 'block_courses_statistics');
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