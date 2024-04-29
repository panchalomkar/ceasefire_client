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

/**
 * Displays different views of the logs.
 *
 * @package    block_logreport
 * @copyright  2018 onwards Naveen kumar(naveen@eabyas.in)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class block_logreport extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_logreport');
    }
        

    public function get_content() {
        global $CFG,$OUTPUT;
        if ($this->content !== null) {
            return $this->content;
        }
        
        $this->page->requires->css('/blocks/logreport/style/datatables.min.css');
        $this->page->requires->css('/blocks/logreport/style/select2.min.css');
        $this->page->requires->jquery_plugin('ui-css');
        $this->page->requires->js_call_amd('block_logreport/logreport', 'Init');
        $this->page->requires->js_call_amd('block_logreport/logreport', 'ProcessFilter');
        $this->page->requires->js_call_amd('block_logreport/logreport', 'InitDatatable');

 $tabshourly = (new \block_logreport\dataprovider)->hourly_chart();
 $tabsdaily = (new \block_logreport\dataprovider)->daily_chart();
 $tabsmonthly = (new \block_logreport\dataprovider)->monthly_chart();

        $this->content = new stdClass;
        $this->content->text = '';
        $content = '
<div class="row mt-3">
<div class="col-12 ">
<div class="row">
<div class="col-12 col-lg-12 col-xl-12">
<div class="card" >
<div class="card-body">
<h5 style="color:black !important; text-align: center;">
 Site Traffic Overview
<hr style="background-color:black;">
</h5>
<ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="today-tab" data-toggle="tab" href="#today" role="tab" aria-controls="today" aria-selected="true">Hourly</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="upcoming-tab" data-toggle="tab" href="#upcoming" role="tab" aria-controls="upcoming" aria-selected="false">Daily</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" id="completed-tab" data-toggle="tab" href="#Completed" role="tab" aria-controls="Completed" aria-selected="false">Monthly</a>
      </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="today" role="tabpanel" aria-labelledby="today-tab">'.$tabshourly.'</div>
                                <div class="tab-pane fade" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">'.$tabsdaily.' </div>
                                
                                <div class="tab-pane fade" id="Completed" role="tabpanel" aria-labelledby="completed-tab">'.$tabsmonthly.'</div>
                                </div>
                            </div>
                         </div>
                    </div>
                </div>';
$content .=  html_writer::link($CFG->wwwroot . '/blocks/logreport/index.php',get_string('viewreport',  'block_logreport'),['id' => 'viewreport']);
           $content .= '</div><!--end row-->
        </div>
    </div>';
        $this->content->text .= $content;
        // $this->content->text .= html_writer::link($CFG->wwwroot . '/blocks/logreport/index.php',get_string('viewreport',  'block_logreport'),['id' => 'viewreport']);

        return $this->content;
    }
}
