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
 * @package    report_custom_report
 * @author     Uvais
 * @copyright  2012 Isuru Madushanka Weerarathna
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');

require_once 'filter_form.php';
require_once 'locallib.php';
require "$CFG->libdir/tablelib.php";
require_once 'tableview.php';

require_login();

$heading = get_string('pluginname', LANGFILE);
$context = context_system::instance();
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);

$PAGE->set_title($heading);
$PAGE->set_heading($heading);
$PAGE->requires->js_call_amd('tool_datatables/init', 'init', array('.datatable', array()));
//$download = optional_param('download', '', PARAM_ALPHA);
$department = optional_param('department', '', PARAM_ALPHA);


$PAGE->navbar->add($heading, new moodle_url('index.php'));
$PAGE->set_url($CFG->wwwroot . '/local/my_courses/index.php');

echo $OUTPUT->header();

//Instantiate simplehtml_form 
$mform = new filter_form();
if (is_landmanager()) {
    $mform->display();
}
echo '<hr>';
$table = new tableview('uniqueid');
//$table->is_downloading($download, 'test', 'testing123');
//Display list of reports
$table->display_report_list($department);
$table->define_baseurl($CFG->wwwroot . BASEURL . '/index.php');

$table->out(10, false);
//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    redirect('index.php');
//    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {

//    //In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
    $mform->set_data($department);
    //displays the form
}


echo $OUTPUT->footer();
?>