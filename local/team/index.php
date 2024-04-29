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
require_once('../../config.php');
require($CFG->dirroot . '/local/team/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$contextid = optional_param('contextid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$searchquery = optional_param('search', '', PARAM_RAW);
$download = optional_param('download', '', PARAM_RAW);
$download_cohortid = optional_param('id', 0, PARAM_INTEGER);
$departmentid = optional_param('departmentid', 0, PARAM_INTEGER);
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/team/index.php'));
$PAGE->requires->css('/local/team/styles.css');
require_login();

if ($context->contextlevel != CONTEXT_COURSECAT and $context->contextlevel != CONTEXT_SYSTEM) {
    print_error('invalidcontext');
}

$category = null;
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = $DB->get_record('course_categories', array('id' => $context->instanceid), '*', MUST_EXIST);
}

$manager = has_capability('local/team:manage', $context);

$canassign = has_capability('local/team:assign', $context);
if (!$manager) {
    require_capability('local/team:view', $context);
}

if(!empty($download)){
    global $DB;

    $sql = "SELECT cm.*, 
            co.name as cohortname, 
            co.component, 
            lt.cohortcreator, 
            lt.mentor,
            lt.companyid, 
            lt.departmentid,
            u.firstname,
            u.lastname,
            u.username,
            u.email,
            u.city,
           (CASE WHEN u.suspended = 1 THEN 'Suspended' ELSE 'Active' END) as accountstatus,
            c.name as company_name,
            d.name as department_name
            FROM {cohort_members} AS cm
            INNER JOIN {cohort} as co ON co.id = cm.cohortid
            INNER JOIN {local_team} as lt ON lt.cohortid = cm.cohortid AND lt.cohortid = co.id
            INNER JOIN {user} as u ON u.id = cm.userid
            INNER JOIN {company_users} cu ON cu.userid = u.id
            INNER JOIN {company} c ON c.id =cu.companyid
            INNER JOIN {department} d ON d.id = cu.departmentid
            WHERE cm.cohortid = $download_cohortid /*AND lt.id = $download_cohortid*/";
    
    $cohortusers = $DB->get_records_sql($sql,null);
    cohort_members_export_csv($cohortusers,$download_cohortid, 'cohortmembers');
}
$strcohorts = get_string('teams', 'local_team');

$PAGE->navbar->add(get_string('teammanagement', 'local_team'), new moodle_url('/local/team/index.php', array()));
$heading = get_string('team', 'local_team');
$PAGE->set_heading($heading);
$PAGE->set_title($heading);

$PAGE->navbar->add($heading);
// Set the companyid
$PAGE->requires->js_call_amd('block_iomad_company_admin/department_select', 'init', array('departmentid', 1, optional_param('departmentid', 0, PARAM_INT)));

$companyid = iomad::get_my_companyid($context);
$output = $PAGE->get_renderer('block_iomad_company_admin');


// Work out department level.
$company = new company($companyid);
$parentlevel = company::get_company_parentnode($company->id);
$companydepartment = $parentlevel->id;
echo $OUTPUT->header();
$userlevel = $company->get_userlevel($USER);
$userhierarchylevel = $userlevel->id;
if ($departmentid == 0) {
    $departmentid = $userhierarchylevel;
}

$cohorts = team_get_teams($context->id, $page, 25, $searchquery, $companyid, $departmentid);



$count = '';
if ($cohorts['allteams'] > 0) {
    if ($searchquery === '') {
        $count = ' (' . $cohorts['allteams'] . ')';
    } else {
        $count = ' (' . $cohorts['totalteams'] . '/' . $cohorts['allteams'] . ')';
    }
}

//echo $OUTPUT->heading(get_string('teamsin', 'local_team', '') . $count);
// Get the appropriate list of departments.

$params = array('page' => $page);
if ($contextid) {
    $params['contextid'] = $contextid;
}
if ($searchquery) {
    $params['search'] = $searchquery;
}
// Department tree
$baseurl = new moodle_url('/local/team/index.php', $params);
$userlevel = $company->get_userlevel($USER);
$userhierarchylevel = $userlevel->id;
if ($departmentid == 0) {
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
echo html_writer::start_tag('div', array('calss' => 'col-xs-12 col-md-12', 'style' => 'display:none'));
echo $fwselectoutput;
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', array('class' => 'vedificclear', 'style' => 'padding-top: 5px;'));
echo html_writer::end_tag('div');

// End of tree

//Tab controls for the index.php page
if ($editcontrols = team_edit_controls($context, $baseurl)) {
    echo $output->render($editcontrols);
}

// Add search form.
$search = html_writer::start_tag('form', array('id' => 'searchcohortquery', 'method' => 'get', 'class' => 'form-inline search-cohort'));
$search .= html_writer::start_div('m-b-1');
$search .= html_writer::label(get_string('searchteam', 'local_team'), 'team_search_q', true, array('class' => 'm-r-1')); // No : in form labels!
$search .= html_writer::empty_tag('input', array('id' => 'team_search_q', 'type' => 'text', 'name' => 'search',
            'value' => $searchquery, 'class' => 'form-control m-r-1'));
$search .= html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('search', 'local_team'),
            'class' => 'btn btn-secondary'));
$search .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'contextid', 'value' => $contextid));
//$search .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'showall', 'value' => $showall));
$search .= html_writer::end_div();
$search .= html_writer::end_tag('form');
echo $search;

// Output pagination bar.
echo $OUTPUT->paging_bar($cohorts['totalteams'], $page, 25, $baseurl);
$data = array();
$editcolumnisempty = true;
foreach ($cohorts['teams'] as $cohort) {
    $cohort_creator_data = get_complete_user_data('id',$cohort->cohortcreator);
    $team = $DB->get_record('local_team', array('cohortid' => $cohort->id), '*', MUST_EXIST);
    $line = array();
    $cohortcontext = context::instance_by_id($cohort->contextid);
    if (!has_capability('local/team:manageallteam', $cohortcontext) && !$cohort->visible) {
        continue;
    }
    $cohort->description = file_rewrite_pluginfile_urls($cohort->description, 'pluginfile.php', $cohortcontext->id, 'cohort', 'description', $cohort->id);
    /* if ($showall) {
      if ($cohortcontext->contextlevel == CONTEXT_COURSECAT) {
      $line[] = html_writer::link(new moodle_url('/cohort/index.php' ,
      array('contextid' => $cohort->contextid)), $cohortcontext->get_context_name(false));
      } else {
      $line[] = $cohortcontext->get_context_name(false);
      }
      } */
    //$tmpl = new \core_cohort\output\cohortname($cohort);
    //$line[] = $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
    $line[] = s($cohort->name);
    $line[] = s(fullname($cohort_creator_data));
    //$tmpl = new \core_cohort\output\cohortidnumber($cohort);
    $line[] = s($cohort->idnumber);
    //$line[] = $OUTPUT->render_from_template('core/inplace_editable', $tmpl->export_for_template($OUTPUT));
    $line[] = format_text($cohort->description, $cohort->descriptionformat);
    $line[] = $DB->count_records_sql('SELECT COUNT(u.id) as totalrecord FROM {user} u JOIN {cohort_members} ltm ON ltm.userid = u.id'
            . ' WHERE ltm.cohortid = :cohortid AND u.deleted = 0 AND u.suspended=0', array('cohortid' => $cohort->id));
    $line[] = $DB->count_records_sql('SELECT COUNT(u.id) as totalrecord FROM {user} u JOIN {cohort_members} ltm ON ltm.userid = u.id'
            . ' WHERE ltm.cohortid = :cohortid AND u.deleted = 0 and u.suspended=1', array('cohortid' => $cohort->id));

    $buttons = array();
    $showmemberonly = false;
    $cohortmanager = has_capability('local/team:manage', $cohortcontext);
    $cohortcanassign = has_capability('local/team:assign', $cohortcontext);
    $urlparams = array('id' => $cohort->id, 'returnurl' => $baseurl->out_as_local_url());
    $urlparams['departmentid'] = $departmentid;
    $showhideurl = new moodle_url('/local/team/edit.php', $urlparams + array('sesskey' => sesskey()));
    if (($cohortmanager) or has_capability('local/team:manageallteam', $cohortcontext)) {
        if (has_capability('local/team:manageallteam', $cohortcontext)) {
            if ($cohort->visible) {
                $buttons[] = "<div class='tbl_action_list' style='display: flex; flex-wrap: nowrap;'>";
                $showhideurl->param('hide', 1);
                $visibleimg = $OUTPUT->pix_icon('t/hide', get_string('hide'));
                $buttons[] = html_writer::link($showhideurl, $visibleimg, array('title' => get_string('hide')));
            } else {
                $buttons[] = "<div class='tbl_action_list' style='display: flex; flex-wrap: nowrap;'>";
                $showhideurl->param('show', 1);
                $visibleimg = $OUTPUT->pix_icon('t/show', get_string('show'));
                $buttons[] = html_writer::link($showhideurl, $visibleimg, array('title' => get_string('show')));
            }
        }
        $buttons[] = html_writer::link(new moodle_url('/local/team/edit.php', $urlparams + array('delete' => 1)), $OUTPUT->pix_icon('t/delete', get_string('delete')), array('title' => get_string('delete')));
        $buttons[] = html_writer::link(new moodle_url('/local/team/edit.php', $urlparams), $OUTPUT->pix_icon('t/edit', get_string('edit')), array('title' => get_string('edit')));
        $editcolumnisempty = false;
    }
    if ((($cohortcanassign && is_allowed_to_assign($cohort->id)) || has_capability('local/team:manageallteam', $cohortcontext))) {
        $buttons[] = html_writer::link(new moodle_url('/local/team/assign.php', $urlparams), $OUTPUT->pix_icon('i/users', get_string('assign', 'local_team')), array('title' => get_string('assign', 'local_team')));
        $buttons[] = html_writer::link(new moodle_url('/local/team/index.php?download=csv', $urlparams), '<i class="fa fa-download" aria-hidden="true"></i>', array('title' => get_string('download', 'local_team')), array('class'=>'operation'));
        $editcolumnisempty = false;
    } else {
        $editcolumnisempty = false;
        $showmemberonly = true;
        $buttons[] = html_writer::link(new moodle_url('/local/team/members.php', $urlparams), $OUTPUT->pix_icon('i/users', get_string('viewmembers', 'local_team')), array('title' => get_string('viewmembers', 'local_team')));
    }
    $buttons[] = "</div>";
    $line[] = implode(' ', $buttons);

    $data[] = $row = new html_table_row($line);
    if (!$cohort->visible) {
        $row->attributes['class'] = 'dimmed_text';
    }
}
$table = new html_table();
$table->head = array(get_string('name', 'local_team'), 'Team Creator', get_string('idnumber', 'local_team'), get_string('description', 'local_team'),
    get_string('memberscount', 'local_team'),get_string('suspendedmemberscount', 'local_team'));
$table->colclasses = array('leftalign name', 'leftalign id', 'leftalign description', 'leftalign size', 'centeralign source');
/* if ($showall) {
  array_unshift($table->head, get_string('category'));
  array_unshift($table->colclasses, 'leftalign category');
  } */
if (!$editcolumnisempty) {

    $table->head[] = !$showmemberonly ? get_string('edit') : get_string('viewmembers', 'local_team');
    $table->colclasses[] = 'centeralign action';
} else {
    // Remove last column from $data.
    foreach ($data as $row) {
        array_pop($row->cells);
    }
}
$table->id = 'teams';
$table->attributes['class'] = 'admintable generaltable';
$table->data = $data;
echo "<div class='team_table'>";
echo html_writer::table($table);
echo $OUTPUT->paging_bar($cohorts['totalteams'], $page, 25, $baseurl);
echo "</div>";
echo $OUTPUT->footer();
