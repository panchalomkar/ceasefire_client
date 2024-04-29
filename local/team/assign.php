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
require_once($CFG->dirroot . '/local/team/locallib.php');
require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/local/team/lib.php');
$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$departmentid = optional_param('departmentid', 0, PARAM_INT);
require_login();

$cohort = $DB->get_record('cohort', array('id' => $id), '*', MUST_EXIST);
$team = $DB->get_record('local_team', array('cohortid' => $cohort->id));
if(!$team){
    print_error('invalidcohort','local_team',new moodle_url('/local/team/index.php'));
}
$context = context::instance_by_id($cohort->contextid, MUST_EXIST);
$companyid = iomad::get_my_companyid($context);
$company = new company($companyid);
$parentlevel = company::get_company_parentnode($company->id);
$companydepartment = $parentlevel->id;
$userlevel = $company->get_userlevel($USER);
$userhierarchylevel = $userlevel->id;
if ($departmentid == 0) {
    $departmentid = $userhierarchylevel;
}

if (!is_allowed_to_assign($cohort->id) && !has_capability('local/team:manageallteam', $context)) {
    print_error('assignerror', 'local_team');
}
if (!has_capability('local/team:manageallteam', $context)) {
    require_capability('local/team:assign', $context);
}

$PAGE->set_context($context);
$PAGE->set_url('/local/team/assign.php', array('id' => $id));
$PAGE->set_pagelayout('admin');

if ($returnurl) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = new moodle_url('/local/team/index.php');
}

/* if (!empty($team->component)) {
  // We can not manually edit teams that were created by external systems, sorry.
  redirect($returnurl);
  } */

if (optional_param('cancel', false, PARAM_BOOL)) {
    redirect($returnurl);
}

if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = $DB->get_record('course_categories', array('id' => $context->instanceid), '*', MUST_EXIST);
    navigation_node::override_active_url(new moodle_url('/local/team/index.php'));
} else {
    navigation_node::override_active_url(new moodle_url('/local/team/index.php', array()));
}
$PAGE->navbar->add(get_string('teammanagement', 'local_team'), new moodle_url('/local/team/index.php', array()));
$PAGE->navbar->add(format_string($cohort->name));
$PAGE->navbar->add(get_string('assign', 'local_team'));

$PAGE->set_title(get_string('assignteams', 'local_team'));
$PAGE->set_heading($COURSE->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('assignto', 'local_team', format_string($cohort->name)));

echo $OUTPUT->notification(get_string('removeuserwarning', 'local_team'),'warning');
//echo $OUTPUT->notification(get_string('removementorwarning', 'local_team'),'danger');

// Get the user_selector we will need.

$potentialuserselector = new team_candidate_selector('addselect', array('companyid'=>$companyid,'departmentid'=>$departmentid,'cohortid' => $team->cohortid, 'accesscontext' => $context,'extrafields'=>array('username','email')));
$existinguserselector = new team_existing_selector('removeselect', array('companyid'=>$companyid,'departmentid'=>$departmentid,'cohortid' => $team->cohortid, 'accesscontext' => $context,'extrafields'=>array('username','email')));

// Process incoming user assignments to the team

if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $potentialuserselector->get_selected_users();
    if (!empty($userstoassign)) {

        foreach ($userstoassign as $adduser) {
            cohort_add_member($cohort->id, $adduser->id);
        }

        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

// Process removing user assignments to the team
if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoremove = $existinguserselector->get_selected_users();
    if (!empty($userstoremove)) {
        foreach ($userstoremove as $removeuser) {
            cohort_remove_member($cohort->id, $removeuser->id);
        }
        $potentialuserselector->invalidate_selected_users();
        $existinguserselector->invalidate_selected_users();
    }
}

// Print the form.
?>
<form id="assignform" method="post" action="<?php echo $PAGE->url ?>"><div>
        <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
        <input type="hidden" name="returnurl" value="<?php echo $returnurl->out_as_local_url() ?>" />

        <table summary="" class="generaltable generalbox boxaligncenter" cellspacing="0">
            <tr>
                <td id="existingcell">
                    <p><label for="removeselect"><?php print_string('currentusers', 'local_team'); ?></label></p>
                    <?php $existinguserselector->display() ?>
                </td>
                <td id="buttonscell">
                    <div id="addcontrols">
                        <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow() . '&nbsp;' . s(get_string('add')); ?>" title="<?php p(get_string('add')); ?>" /><br />
                    </div>

                    <div id="removecontrols">
                        <input name="remove" id="remove" type="submit" value="<?php echo s(get_string('remove')) . '&nbsp;' . $OUTPUT->rarrow(); ?>" title="<?php p(get_string('remove')); ?>" />
                    </div>
                </td>
                <td id="potentialcell">
                    <p><label for="addselect"><?php print_string('potusers', 'local_team'); ?></label></p>
                    <?php $potentialuserselector->display() ?>
                </td>
            </tr>
            <tr><td colspan="3" id='backcell'>
                    <input type="submit" name="cancel" value="<?php p(get_string('backtoteams', 'local_team')); ?>" />
                </td></tr>
        </table>
    </div></form>

<?php
echo $OUTPUT->footer();
