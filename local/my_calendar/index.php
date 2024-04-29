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
 * This file contains functionality.
 *
 * @package    local_my_calendar
 * @author     Uvais
 * @copyright  2012 Isuru Madushanka Weerarathna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');

require_once 'filter_form.php';
require_once 'locallib.php';

require_login();

$heading = get_string('pluginname', LANGUAGE);
$context = context_system::instance();
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
//$PAGE->requires->js_call_amd('tool_datatables/init', 'init', array('.datatable', array()));
//$download = optional_param('download', '', PARAM_ALPHA);
$department = optional_param('department', '', PARAM_ALPHA);


$PAGE->navbar->add($heading, new moodle_url('index.php'));
$PAGE->set_url($CFG->wwwroot . '/local/my_calendar/index.php');

echo $OUTPUT->header();
//Instantiate simplehtml_form 
$mform = new filter_form();
if (is_landmanager() or is_siteadmin()) {
    $mform->display();
}

if ($mform->is_cancelled()) {
    redirect('index.php');
} else if ($fromform = $mform->get_data()) {
    $department = $fromform->department;
    $mform->set_data($fromform);
} else {

    $mform->set_data($department);
}

echo '<hr>';
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset='utf-8' />
        <link href='packages/core/main.css' rel='stylesheet' />
        <link href='packages/daygrid/main.css' rel='stylesheet' />
        <link href='packages/timegrid/main.css' rel='stylesheet' />
        <link href='packages/list/main.css' rel='stylesheet' />
        <script src='packages/core/main.js'></script>
        <script src='packages/interaction/main.js'></script>
        <script src='packages/daygrid/main.js'></script>
        <script src='packages/timegrid/main.js'></script>
        <script src='packages/list/main.js'></script>
        <script
            src="https://code.jquery.com/jquery-3.4.1.min.js"
            integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
        <script>
            $(document).ready(function () {
                $('body').on('click', '.gettitle', function (e) {
                    e.preventDefault()
                    var id = $(this).attr('id');

                    $('#modal-table').html('')
                    $.ajax({
                        url: "ajax.php",
                        async: false,
                        type: "POST",
                        dataType: 'json',
                        global: false,
                        data: {action: 'CALENDAR_DETAIL', id: id},

                        success: function (data) {
                            $('#modal-table').html(data)
                        }
                    });
                });
            });
            var date = new Date();
            document.addEventListener('DOMContentLoaded', function () {

                var return_first = function () {
                    var tmp = null;
                    var department = "<?php echo $department; ?>";
                    $.ajax({
                        url: "ajax.php",
                        async: false,
                        type: "POST",
                        global: false,
                        data: {action: 'GETCALENDAR', department: department},

                        success: function (data) {
                            tmp = data;
                        }
                    });
                    return tmp;
                }();

//                return_first = JSON.parse(return_first);
                var calendarEl = document.getElementById('calendar');

                var calendar = new FullCalendar.Calendar(calendarEl, {

                    plugins: ['interaction', 'dayGrid', 'timeGrid', 'list'],
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek'
                    },
                    eventRender: function (info) {
                        info.el.querySelector('.fc-title').innerHTML = '<a href="#" class="gettitle" data-toggle="modal" data-target="#myModal" id=' + info.event.id + '>' + info.event.title + '</a>';
                    },
                    defaultDate: date,
                    navLinks: true, // can click day/week names to navigate views
                    businessHours: true, // display business hours
                    editable: true,
                    legend: true,
                    events: return_first

                });

                calendar.render();
            });

        </script>
        <style>

            /*body { 
                margin: 40px 10px;
                padding: 0;
                font-family: Arial, Helvetica Neue, Helvetica, sans-serif;
                font-size: 14px;
            }*/

            /*#calendar {
                max-width: 900px;
                margin: 0 auto;
            }*/
            .gettitle{
                color:#fff !important;
            }
            .gettitle:hover{
                color: #fff !important;
            }
        </style>
    </head>
    <body>
        <!--        <div class="row" style="margin-left: 5px; margin-bottom: 2rem;">
                    <div class="col-md-1" style="background-color:#3498db;width: 1rem;height: 3rem;border-radius: 5px;">
                </div>
                 <div class="col-md-1">
                    <h4>Training</h4>
                </div>
                <div class="col-md-1" style="background-color:#ff9f89;width: 1rem;height: 3rem;border-radius: 5px;">
                </div>
                 <div class="col-md-1">
                    <h4>Travel</h4>
                </div>
                    <div class="col-md-1" style="background-color:#3CB371;width: 1rem;height: 3rem;border-radius: 5px;">
                </div>
                 <div class="col-md-1">
                    <h4>Trainee</h4>
                </div>
                </div>
        -->
        <div id='calendar'></div>

        <!-- Modal -->

        <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Details</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-responsive table-bordered table-striped">
                                    <tbody id="modal-table">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>

            </div>
        </div>

    </body>
</html>

<?php
echo $OUTPUT->footer();
?>