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
 * This file processes AJAX enrolment actions and returns JSON
 *
 * The general idea behind this file is that any errors should throw exceptions
 * which will be returned and acted upon by the calling AJAX script.
 *
 * @package    local_my_calendar
 */
define('AJAX_SCRIPT', true);

include_once '../../config.php';
include_once 'locallib.php';
global $DB, $USER, $CFG;
$localObj = new local_my_calendar();

$action = $_POST['action'];
$training_id = '';
if (isset($_POST['training_id']) && $_POST['training_id'] != '') {
    $training_id = $_POST['training_id'];
}


switch ($action) {
    case 'GETCALENDAR':
        
        $department = '';
        if (isset($_POST['department']) && $_POST['department'] != '') {
            $department = $_POST['department'];
        }

        $output = array();
        //get my calendar
        $records = $localObj->my_calendar($department);

        foreach ($records as $row) {
            $array = array();
            $array['title'] = $row->title;
            $array['start'] = $row->start;
            $array['end'] = $row->end;
            $array['color'] = $row->color;
//            $array['rendering'] = $row->background;
            $array['id'] = $row->id;
            $output[] = $array;
        }
        //today
        $output[] = array('title' => 'Today', 'start' => date('Y-m-d'), 'color' => '#000', 'id' => '');

        echo json_encode($output, true);
        break;

    case 'CALENDAR_DETAIL':
        $echo = '';
        if (isset($_POST['id']) && $_POST['id'] != '') {
            $id = $_POST['id'];
            if ($record = $localObj->get_event($id)) {
                $echo .= '<tr>
                        <th>Event</th>
                        <td class="vmcategoryname">' . $record->name . '</td>
                        <th scope="row">Description</th>
                        <td class="vmcategoryname">' . $record->description . '</td>
                      </tr>
                      <tr>
                        <th scope="row">Time</th>
                        <td class="vmcategoryname">' . date('D, d M Y H:i A', $record->timestart) . '</td>';
                if ($record->courseid > 1) {
                    $courseurl = $CFG->wwwroot . '/course/view.php?id=' . $record->courseid;
                    $echo .= '<th scope="row">Course Name</th>
                        <td class="vmcategoryname"><a href="' . $courseurl . '">' . $record->fullname . '</a></td>';
                } else {
                    $echo .= '<th scope="row"></th>
                        <td class="vmcategoryname"></td>';
                }
                $echo .= '</tr>';
                $echo .= '<tr>
                        <th>Activity</th>';
                if ($record->modulename != '') {
                    $moduleid = $DB->get_field('modules', 'id', array('name' => $record->modulename));
                    $cmid = $DB->get_field('course_modules', 'id', array('course' => $record->courseid, 'module' => $moduleid, 'instance' => $record->instance));
                    $modulelink = $CFG->wwwroot . '/mod/' . $record->modulename . '/view.php?id=' . $cmid;
                    $echo .= '<td class="vmcategoryname"><a href="' . $modulelink . '">Go to activity</a></td>';
                } else {
                    $echo .= '<td class="vmcategoryname">' . $record->modulename . '</td>';
                }
                $echo .= '<th scope="row"></th>
                        <td class="vmcategoryname">' . $record->description . '</td>
                      </tr>';
            } else {
                $echo .= 'No record found';
            }
        }
        echo json_encode($echo, true);
        break;
}