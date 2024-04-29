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
 * This file contains the Activity modules block.
 *
 * @package    block_scratch_card
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/filelib.php');

class block_scratch_card extends block_list {

    function init() {
        $this->title = get_string('pluginname', 'block_scratch_card');
    }

    function get_content() {
        global $CFG, $DB, $OUTPUT, $USER;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        $course = $this->page->course;
//        $this->page->require->requirejs('/blocks/myblock/local.js');
        $cards = $this->get_my_scratchcard($USER->id, $course->id);
        $scounter = $this->get_quizscratch_counter($USER->id);

        $output['scratchcards'] = $cards[0];

        list($scratch1, $scratch2, $scratch3) = $cards[1];
        list($n1, $n2, $n3) = $cards[2];


        $this->content->items[] = '<label>Available scratch card : <strong>' . $scounter . '</strong></label>';
        $this->content->items[] = '<script type="text/javascript" src="' . $CFG->wwwroot . '/local/mydashboard/external/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="' . $CFG->wwwroot . '/local/mydashboard/external/wScratchPad.js"></script>';

        $this->content->items[] = $cards[0];
        $this->content->items[] = '<style>
    .scratchpad {
        width: 15%;
        height: 160px;
        border: solid 1px;
        display: inline-block;
    }
</style>';

        $this->content->items[] = '<script>
                           var cnt1 = 0;
    var cnt2 = 0;
    var cnt3 = 0;
    $("#demo1").wScratchPad({
        fg: "' . $n1 . '",
        bg: "' . $scratch1 . '",

        scratchMove: function (e, percent) {

            if (percent > 50) {

                this.clear();
                var spoint = $("#demo1").attr("point");
                var scid = $("#demo1").attr("scid");
               
                    if (cnt1 == 0) {
                      cnt1 = parseInt(cnt1) + 1;
                        $.ajax({
                            url: "' . $CFG->wwwroot . '/blocks/scratch_card/ajax.php",
                            type: "post",
                            dataType: "html",
                            data: {action: "SCRATCHCARD", spoint: spoint, scid: scid},
                            success: function (res) {
                            var res = JSON.parse(res)
                                $("#spinpoint").html(spoint);
                                    $("#popupimage").html(res[1]);
                                $("#spindWheel").modal("show");//now its working
                            }
                        });
              
                } else {
                    cnt1 = parseInt(cnt1) + 1;
                   
                }
            }
        }
    });
    

 $("#demo2").wScratchPad({
        fg: "' . $n2 . '",
        bg: "' . $scratch2 . '",

        scratchMove: function (e, percent) {

            if (percent > 50) {

                this.clear();
                var spoint = $("#demo2").attr("point");
                var scid = $("#demo2").attr("scid");
                
                    if (cnt2 == 0) {
                     cnt2 = parseInt(cnt2) + 1;
                        $.ajax({
                            url: "' . $CFG->wwwroot . '/blocks/scratch_card/ajax.php",
                            type: "post",
                            dataType: "html",
                            data: {action: "SCRATCHCARD", spoint: spoint, scid: scid},
                            success: function (res) {
                            var res = JSON.parse(res)
                                $("#spinpoint").html(spoint);
                                    $("#popupimage").html(res[1]);
                                $("#spindWheel").modal("show");//now its working
                            }
                        });
                    
                } else {
                    cnt2 = parseInt(cnt2) + 1;
                   
                }
            }
        }
    });
    


 $("#demo3").wScratchPad({
        fg: "' . $n3 . '",
        bg: "' . $scratch3 . '",

        scratchMove: function (e, percent) {

            if (percent > 50) {

                this.clear();
                var spoint = $("#demo3").attr("point");
                var scid = $("#demo3").attr("scid");
             
                    if (cnt3 == 0) {
                   cnt3 = parseInt(cnt3) + 1;
                        $.ajax({
                            url: "' . $CFG->wwwroot . '/blocks/scratch_card/ajax.php",
                            type: "post",
                            dataType: "html",
                            data: {action: "SCRATCHCARD", spoint: spoint, scid: scid},
                            success: function (res) {
                            var res = JSON.parse(res)
                                $("#spinpoint").html(spoint);
                                    $("#popupimage").html(res[1]);
                                $("#spindWheel").modal("show");//now its working
                            }
                        });
                        
               
                } else {
                   cnt3 = parseInt(cnt3) + 1;
                  
                }
            }
        }
    });
  
    
   </script>';

        $this->content->items[] = '<div class="modal fade" id="spindWheel" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width:300px !important;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">You have won <strong id="spinpoint"></strong> point(s)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div> 
                <div class="modal-body" id="popupimage">
                    
                    
                </div>
               
            </div>
        </div>
    </div>';
        return $this->content;
    }

    /**
     * Returns the role that best describes this blocks contents.
     *
     * This returns 'navigation' as the blocks contents is a list of links to activities and resources.
     *
     * @return string 'navigation'
     */
    function get_my_scratchcard($userid, $courseid) {
        global $DB, $CFG;

        $quizes = $DB->get_records('quiz', array('course' => $courseid));

        $itemid = [];
        foreach ($quizes as $q) {
            $itemid[] = $q->id;
        }
        $itemid = implode(',', $itemid);

        $SQL = "SELECT * FROM {user_scratchcard} WHERE userid = $userid AND redeemed = 0 AND 
                card_type = 'quiz' AND FIND_IN_SET(itemid, '$itemid') LIMIT 3";
        $records = $DB->get_records_sql($SQL);

        $card = '';
        $i = 1;
        $points = array();
        $number = array();
        foreach ($records as $row) {
            $card .= '&nbsp;&nbsp;&nbsp;<div id="demo' . $i . '" class="scratchpad" scid="' . $row->id . '" point="' . $row->point . '"></div>&nbsp;&nbsp;&nbsp;';
            $i++;
            $points[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/' . $row->point . '.jpg';

            if ($row->point <= 20) {
                $number[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/s1-20.jpg';
            } else if ($row->point > 20 && $row->point <= 35) {
                $number[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/s25-35.jpg';
            } else if ($row->point > 35 && $row->point <= 50) {
                $number[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/s40-50.jpg';
            } else {
                $number[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/s1-20.jpg';
            }
        }
        return array($card, $points, $number);
    }

    function get_quizscratch_counter($userid) {
        global $DB;

        $SQL = "SELECT * FROM {user_scratchcard} WHERE userid = $userid AND redeemed = 0 AND card_type = 'quiz' LIMIT 3";
        $records = $DB->get_records_sql($SQL);

        $count = count($records);

        if ($count > 3) {
            return "3 / $count";
        }
        return "$count / $count";
    }

}
