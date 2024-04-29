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
use html_writer;
class block_landingpage extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_landingpage');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $PAGE,$USER, $CFG;
                if ($this->content !== null) {
                    return $this->content;
                }
        
                if (empty($this->instance)) {
                    $this->content = '';
                    return $this->content;
                }
        
                $this->title = get_string('pluginname', 'block_landingpage');

$html = '<section class="bg-light pt-5 pb-5 shadow-sm" style="border-radius:16px !important;">
<div class="Rounded-Rectangle-569">
                <span class="Hi-Robert-Miller">
                Hi '.$USER->firstname.' '.$USER->lastname.' !
              </span>
                <div class="row linkdata">
                <div class="circle"><img src= "http://localhost/ceasefire/blocks/landingpage/img/component-15.png"
                class="Component-15">
                </div>
                <div class="author">
                <span class="">
                <a class="My-Trainings" href= '.$CFG->wwwroot.'/course/index.php>My Trainings</a>
                </span>
                </div>
                <div class="all"><img src= "http://localhost/ceasefire/blocks/landingpage/img/all.png"
                class="Component-15">
                </div>
                <div class="author">
                <span class="">
               <a class="My-Trainings" href= '.$CFG->wwwroot.'/course/index.php>All Courses</a>
               </span>
               </div>
               <div class="computerimage">
               <img src="http://localhost/ceasefire/blocks/landingpage/img/group-2329.png"
               class="Group-2329" style="
               width: 300px;
               height: 200px;">
               </div>
               </div>
                </div>
                </section>
 
</br>
<section class="bg-light pt-5 pb-5 shadow-sm" style="border-radius:16px !important;">
  <div class="container">
    <div class="row pt-5">
      <div class="col-12">
        <h3 class="text-uppercase border-bottom mb-4" style="box-shadow: 0 0 3px 0 rgba(0, 0, 0, 0.1);
        background-color: #5e646f;color: #fff;margin-top: -33px;
        border-radius: 7px;"><img style="width: 21px;
        height: 21px;"src="http://localhost/ceasefire/blocks/landingpage/img/vector-smart-object-11.png">&nbspCourse Trends</h3>
      </div>
    </div>
    <div class="row">
      <!--ADD CLASSES HERE d-flex align-items-stretch-->
      <div class="col-lg-3 mb-3 d-flex align-items-stretch">
        <div class="card trendcourse">
          <img src="https://i.postimg.cc/28PqLLQC/dotonburi-canal-osaka-japan-700.jpg" class="card-img-top" alt="Card Image">
          <div class="card-body d-flex flex-column">
          <h5 class="card-title"><a href="#" class="mt-auto align-self-start">Course1</a></h5>
          </div>
        </div>
      </div>
      <!--ADD CLASSES HERE d-flex align-items-stretch-->
      <div class="col-lg-3 mb-3 d-flex align-items-stretch">
        <div class="card trendcourse">
          <img src="https://i.postimg.cc/4xVY64PV/porto-timoni-double-beach-corfu-greece-700.jpg" class="card-img-top" alt="Card Image">
          <div class="card-body d-flex flex-column">
          <h5 class="card-title"><a href="#" class="mt-auto align-self-start">Course1</a></h5>
          </div>
        </div>
      </div>
      <!--ADD CLASSES HERE d-flex align-items-stretch-->
      <div class="col-lg-3 mb-3 d-flex align-items-stretch">
        <div class="card trendcourse">
          <img src="https://i.postimg.cc/TYyLPJWk/tritons-fountain-valletta-malta-700.jpg" class="card-img-top" alt="Card Image">
          <div class="card-body d-flex flex-column">
          <h5 class="card-title"><a href="#" class="mt-auto align-self-start">Course1</a></h5>
          </div>
        </div>
      </div>
      <!--ADD CLASSES HERE d-flex align-items-stretch-->
      <div class="col-lg-3 mb-3 d-flex align-items-stretch">
        <div class="card trendcourse">
          <img src="https://i.postimg.cc/TYyLPJWk/tritons-fountain-valletta-malta-700.jpg" class="card-img-top" alt="Card Image">
          <div class="card-body d-flex flex-column">
            <h5 class="card-title"><a href="#" class="mt-auto align-self-start">Course1</a></h5>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
  </br>
  </br>
  <section class="bg-light pt-5 pb-5 shadow-sm" style="border-radius:16px !important;">
  <div class="server_live_status">
  <div class="progress-row">
      <div class="usage-header">
          <label class="progress-name"><i class="fa fa-microchip" aria-hidden="true"></i>Total Trainers</label>
      </div>
      <div class="usage-details">
          <label class="progress-label" id="esm_memory_usage_label">15</label>
          <div class="progress-wrap progress">
              <div class="usage-progress-bar progress-bar red" id="esm_memory_usage_bar" role="progressbar" style="width: 100%;"></div>
          </div>
      </div>
  </div>
  <div class="progress-row">
      <div class="usage-header">
          <label class="progress-name"><i class="fa fa-hdd-o" aria-hidden="true"></i>Total Courses</label>
      </div>
      <div class="usage-details">
          <label class="progress-label" id="esm_storage_usage_label">5</label>
          <div class="progress-wrap progress">
              <div class="usage-progress-bar progress-bar {{storagecolor}}" id="esm_storage_usage_bar" role="progressbar" style="width: 100%;"></div>
          </div>
      </div>
  </div>
  <div class="progress-row users">
      <div class="usage-header">
          <label><i class="fa fa-users" aria-hidden="true"></i>Users</label>
      </div>
      <div class="usage-details">
          <div class="legends">
              <div class="activeusers legend">
                  <div class="color bg-success"></div>
                  <label>Active(10)</label>
              </div>
              <div class="suspendedusers legend">
                  <div class="color bg-warning"></div>
                  <label>Suspended(5)</label>
              </div>
              <div class="deletedusers legend">
                  <div class="color bg-danger"></div>
                  <label>Deleted(2)</label>
              </div>
          </div>
          <div class="progress-wrap progress">
              <div class="usage-progress-bar progress-bar bg-success" role="progressbar" id="esm_users_active_bar" style="width: 92%;"></div>
              <div class="usage-progress-bar progress-bar bg-warning" role="progressbar" id="esm_users_suspended_bar" style="width: 5%;"></div>
              <div class="usage-progress-bar progress-bar bg-danger" role="progressbar" id="esm_users_deleted_bar" style="width: 3%;"></div>
          </div>
      </div>
  </div>
  <div class="progress-row">
      <div class="usage-header">
          <label><i class="fa fa-user-circle" aria-hidden="true"></i>Live Users</label>
      </div>
      <div class="usage-details">
          <label id="esm_live_users_label">1</label>
      </div>
  </div>
</div>
  </section> 
   
   ';
    $this->content = new stdClass();
    $this->content->items = array();
    $this->content->link_images = array();
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
            $this->title = get_string('pluginname', 'block_landingpage');
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
        return array('all' => true, 'site-index' => true, 'course-view' => false);
    }
}
