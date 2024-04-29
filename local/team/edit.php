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
 * Cohort related management functions, this file needs to be included manually.
 *
 * @package    core_team
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require($CFG->dirroot . '/course/lib.php');
require($CFG->dirroot . '/local/team/lib.php');
require($CFG->dirroot . '/local/team/edit_form.php');
require_once($CFG->dirroot.'/local/iomad/lib/iomad.php');
require_once($CFG->dirroot.'/local/iomad/lib/company.php');
$id = optional_param('id', 0, PARAM_INT);
//$contextid = optional_param('contextid', 0, PARAM_INT);
$contextid = SITEID;
$delete = optional_param('delete', 0, PARAM_BOOL);
$show = optional_param('show', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$departmentid = optional_param('departmentid', 0, PARAM_INTEGER);
require_login();
global $CFG, $PAGE, $USER, $OUTPUT;

$category = null;
$team = new stdClass();
$team->id = 0;
if ($id) {
    $cohort = $DB->get_record('cohort', array('id' => $id), '*', MUST_EXIST);
    $team = $DB->get_record('local_team', array('cohortid' => $id), '*', MUST_EXIST);
    $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
} else {
    $context = context::instance_by_id($contextid, MUST_EXIST);
    if ($context->contextlevel != CONTEXT_COURSECAT and $context->contextlevel != CONTEXT_SYSTEM) {
        print_error('invalidcontext');
    }
    $cohort = new stdClass();
    $cohort->id = 0;
    $cohort->contextid = $context->id;
    $cohort->name = '';
    $cohort->description = '';
}
$companyid = iomad::get_my_companyid($context);
$company = new company($companyid);
$userlevel = $company->get_userlevel($USER);
$userhierarchylevel = $userlevel->id;
if ($departmentid == 0 ) {
    $departmentid = $userhierarchylevel;
}

if ($id && !is_allowed_user($cohort->id)) {
    print_error('teammanageerror', 'local_team');
}
if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url('/local/team/index.php', array('contextid' => $context->id));
}

if (!empty($cohort->component)) {
    // We can not manually edit teams that were created by external systems, sorry.
    //redirect($returnurl);
}

$PAGE->set_context($context);
$baseurl = new moodle_url('/local/team/edit.php', array('contextid' => $context->id, 'id' => $cohort->id));
$PAGE->set_url($baseurl);
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');

if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = $DB->get_record('course_categories', array('id' => $context->instanceid), '*', MUST_EXIST);
    navigation_node::override_active_url(new moodle_url('/local/team/index.php', array('contextid' => $cohort->contextid)));
} else {
    navigation_node::override_active_url(new moodle_url('/local/team/index.php', array()));
}

if ($delete and $cohort->id) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        if (has_capability('local/team:manage', $context)) {
            team_delete_team($cohort);
        }
        redirect($returnurl);
    }
    $strheading = get_string('delteam', 'local_team');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    $PAGE->set_heading($COURSE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    
    $yesurl = new moodle_url('/local/team/edit.php', array('id' => $cohort->id, 'delete' => 1,
        'confirm' => 1, 'sesskey' => sesskey(), 'returnurl' => $returnurl->out_as_local_url()));
    $message = get_string('delconfirm', 'local_team', format_string($cohort->name));
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

if ($show && $cohort->id && confirm_sesskey()) {
    if (!$cohort->visible) {
        $record = (object) array('id' => $cohort->id, 'visible' => 1, 'contextid' => $cohort->contextid,'setvisiblity'=>1);      
        team_update_team($record);
    }
    redirect($returnurl);
}

if ($hide && $cohort->id && confirm_sesskey()) {
    if ($cohort->visible) {
        $record = (object) array('id' => $cohort->id, 'visible' => 0, 'contextid' => $cohort->contextid,'setvisiblity'=>1);
        team_update_team($record);
    }
    redirect($returnurl);
}

$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
    'maxbytes' => $SITE->maxbytes, 'context' => $context);
if ($cohort->id) {
    // Edit existing.
    $cohort = file_prepare_standard_editor($cohort, 'description', $editoroptions, $context, 'cohort', 'description', $cohort->id);
    $strheading = get_string('editteam', 'local_team');
} else {
    // Add new.
    $cohort = file_prepare_standard_editor($cohort, 'description', $editoroptions, $context, 'cohort', 'description', null);
    $strheading = get_string('addteam', 'local_team');
}

$PAGE->set_title($strheading);
$PAGE->set_heading($COURSE->fullname);
$PAGE->navbar->add(get_string('teammanagement', 'local_team'), new moodle_url('/local/team/index.php', array()));
$PAGE->navbar->add($strheading);
$PAGE->requires->js_call_amd('block_iomad_company_admin/department_select', 'init', array('departmentid', 1, optional_param('departmentid', 0, PARAM_INT)));

$editform = new team_edit_form(null, array('editoroptions' => $editoroptions, 'data' => $cohort, 'returnurl' => $returnurl, 'teamdata' => $team,'companyid'=>$companyid,'departmentid'=>$departmentid));

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $oldcontextid = $context->id;
    $editoroptions['context'] = $context = context::instance_by_id($data->contextid);
    $data->mentor = 0;
    if ($data->id) {
        if ($data->contextid != $oldcontextid) {
            // Cohort was moved to another context.
            get_file_storage()->move_area_files_to_new_context($oldcontextid, $context->id, 'cohort', 'description', $data->id);
        }
        $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $context, 'cohort', 'description', $data->id);
        team_update_team($data);
        $team = $DB->get_record('local_team', array('cohortid' => $cohort->id), '*', MUST_EXIST);
        //print_object($data);
        //die;
        // team_update_icon($team->id, $data, $editform);
    } else {
        $data->descriptionformat = $data->description_editor['format'];
        $data->description = $description = $data->description_editor['text'];
        $data->id = team_add_team($data,$companyid,$departmentid);
        //print_object($data);
        //die;
        //team_update_icon($data->id, $data, $editform);
        $editoroptions['context'] = $context = context::instance_by_id($data->contextid);
        $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $context, 'cohort', 'description', $data->id);
        if ($description != $data->description) {
            $updatedata = (object) array('id' => $data->id,
                        'description' => $data->description, 'contextid' => $context->id);
            team_update_team($updatedata);
        }
    }

    if ($returnurl->get_param('showall') || $returnurl->get_param('contextid') == $data->contextid) {
        // Redirect to where we were before.
        redirect($returnurl);
    } else {
        // Use new context id, it has been changed.
        redirect(new moodle_url('/local/team/index.php', array('contextid' => $data->contextid)));
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
$baseurl = new moodle_url('/local/team/edit.php');
$output = $PAGE->get_renderer('block_iomad_company_admin');

$companyid = iomad::get_my_companyid($context);
$company = new company($companyid);
$userlevel = $company->get_userlevel($USER);
$userhierarchylevel = $userlevel->id;
if ($departmentid == 0 ) {
    $departmentid = $userhierarchylevel;
}

$userdepartment = $company->get_userlevel($USER);
$departmenttree = company::get_all_subdepartments_raw($userdepartment->id);
$treehtml = $output->department_tree($departmenttree, optional_param('departmentid', 0, PARAM_INT));
$subhierarchieslist = company::get_all_subdepartments($userhierarchylevel);
$select = new single_select($baseurl, 'departmentid', $subhierarchieslist, $departmentid);
$select->label = get_string('department', 'block_iomad_company_admin');
$select->formid = 'choosedepartment';
$fwselectoutput = html_writer::tag('div', $output->render($select), array('id' => 'vedific_department_selector', 'style' => 'display: none'));

// Display the tree selector thing.
echo html_writer::start_tag('div', array('class' => 'row'));
echo html_writer::start_tag('div', array('class' => 'col-xs-12 col-md-12'));
echo $treehtml;
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('calss'=>'col-xs-12 col-md-12','style' => 'display:none'));
echo $fwselectoutput;
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('class' => 'vedificclear', 'style' => 'padding-top: 5px;'));
echo html_writer::end_tag('div');

// End of tree
if (!$id && ($editcontrols = team_edit_controls($context, $baseurl))) {
    echo $OUTPUT->render($editcontrols);
}

echo $editform->display();
echo $OUTPUT->footer();

