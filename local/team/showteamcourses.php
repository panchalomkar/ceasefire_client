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

require_once(dirname(__FILE__) . '/../../config.php'); // Creates $PAGE.
require_once($CFG->dirroot . '/blocks/vedific_company_admin/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/email/lib.php');
require_once($CFG->dirroot . "/enrol/cohort/lib.php");
require_once('lib.php');

class company_ccu_courses_form extends company_moodleform {

    protected $context = null;
    protected $selectedcompany = 0;
    protected $potentialcourses = null;
    protected $currentcourses = null;
    protected $departmentid = 0;
    protected $subhierarchieslist = null;
    protected $companydepartment = 0;
    protected $selectedcourse = 0;
    protected $company = null;
    protected $courses = array();

    public function __construct($actionurl, $context, $companyid, $departmentid, $selectedcourse, $parentlevel) {
        global $DB, $USER;
        $this->selectedcompany = $companyid;
        $this->company = new company($companyid);
        $this->context = $context;
        $this->departmentid = $departmentid;
        $this->selectedcourse = $selectedcourse;

        $options = array('context' => $this->context,
            'multiselect' => false,
            'companyid' => $this->selectedcompany,
            'departmentid' => $departmentid,
            'subdepartments' => $this->subhierarchieslist,
            'parentdepartmentid' => $parentlevel,
            'licenses' => false,
            'shared' => false);
        $this->currentcourses = new current_company_course_selector('currentcourses', $options);
        $this->currentcourses->set_rows(1);
        $this->courses = $this->company->get_menu_courses(true, true);
        parent::__construct($actionurl);
    }

    public function definition() {
        $this->_form->addElement('hidden', 'companyid', $this->selectedcompany);
        $this->_form->setType('companyid', PARAM_INT);
        $this->_form->addElement('hidden', 'deptid', $this->departmentid);
        $this->_form->setType('deptid', PARAM_INT);
    }

    public function definition_after_data() {
        $mform = & $this->_form;
        // Adding the elements in the definition_after_data function rather than in the definition
        // function so that when the currentcourses or potentialcourses get changed in the process
        // function, the changes get displayed, rather than the lists as they are before processing.
        //$courses = $this->currentcourses->find_courses('');
        if ($this->courses) {

            // We are going to cheat and be lazy here.
            $autooptions = array('setmultiple' => false,
                'noselectionstring' => get_string('selectenrolmentcourse', 'block_iomad_company_admin'),
                'onchange' => 'this.form.submit()');
            $mform->addElement('autocomplete', 'selectedcourse', get_string('selectcourse','local_team'), $this->courses, $autooptions);
        } else {
            $mform->addElement('html', '<div class="alert alert-warning">' . get_string('nocourses', 'block_iomad_company_admin') . '</div>');
        }

        // Disable the onchange popup.
        $mform->disable_form_change_checker();
    }

    public function get_data() {
        $data = parent::get_data();

        if ($data !== null && $this->currentcourses) {
            $data->selectedcourses = $this->currentcourses->get_selected_courses();
        }

        return $data;
    }

}

/*class company_course_team_form extends moodleform {

    protected $context = null;
    protected $selectedcompany = 0;
    protected $selectedcourse = 0;
    protected $potentialusers = null;
    protected $currentusers = null;
    protected $course = null;
    protected $departmentid = 0;
    protected $companydepartment = 0;
    protected $subhierarchieslist = null;
    protected $parentlevel = null;
    protected $groups = null;
    protected $company = null;

    public function __construct($actionurl, $context, $companyid, $departmentid, $courseid) {
        global $USER, $DB;
        $this->selectedcompany = $companyid;
        $this->selectedcourse = $courseid;
        $this->context = $context;
        $company = new company($this->selectedcompany);
        $this->company = $company;
        $this->parentlevel = company::get_company_parentnode($company->id);
        $this->companydepartment = $this->parentlevel->id;
        $context = context_system::instance();

        if (iomad::has_capability('block/vedific_company_admin:edit_all_departments', $context)) {
            $userhierarchylevel = $this->parentlevel->id;
        } else {
            $userlevel = $company->get_userlevel($USER);
            $userhierarchylevel = $userlevel->id;
        }

        $this->subhierarchieslist = company::get_all_subdepartments($userhierarchylevel);
        if ($departmentid == 0) {
            $this->departmentid = $userhierarchylevel;
        } else {
            $this->departmentid = $departmentid;
        }

        $this->course = $DB->get_record('course', array('id' => $courseid));

        parent::__construct($actionurl);
    }

    public function definition() {
        global $DB, $OUTPUT;
        $this->_form->addElement('hidden', 'companyid', $this->selectedcompany);
        $this->_form->addElement('hidden', 'deptid', $this->departmentid);
        $this->_form->addElement('hidden', 'selectedcourse', $this->selectedcourse);
        $this->_form->setType('companyid', PARAM_INT);
        $this->_form->setType('deptid', PARAM_INT);
        $this->_form->setType('selectedcourse', PARAM_INT);
        $sql = 'SELECT c.id,c.name FROM {cohort} c INNER JOIN {local_team} lt ON lt.cohortid = c.id WHERE lt.companyid=:companyid AND lt.departmentid=:departmentid';
        $teams = $DB->get_records_sql($sql, array('companyid' => $this->selectedcompany, 'departmentid' => $this->departmentid));
        $teamlist = ['' => get_string('select')];
        foreach ($teams as $team) {
            $teamlist[$team->id] = $team->name;
        }
        if ($teams) {
            $this->_form->addElement('autocomplete', 'selectedteam', get_string('selectedteam', 'local_team'), $teamlist);
            $this->add_action_buttons(false, get_string('enrol','local_team'));
        } else {
            $this->_form->addElement('html', '<div class="alert alert-warning">' . get_string('noteamindepartment', 'local_team') . '</div>');
        }
    }

    public function set_course($courses) {
        global $DB;

        if (!$this->groups = $DB->get_records_sql_menu("SELECT g.id, g.description
                                                   FROM {groups} g
                                                   JOIN {company_course_groups} ccg
                                                   ON (g.id = ccg.groupid)
                                                   WHERE ccg.companyid = :companyid
                                                   AND ccg.courseid = :courseid", array('companyid' => $this->selectedcompany,
            'courseid' => $this->course->id))) {
            $this->groups = array($this->company->get_name());
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['selectedteam'] == '') {
            $errors['selectedteam'] = get_string('required');
        }
        return $errors;
    }

}*/

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
//$companyid = optional_param('companyid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$departmentid = optional_param('deptid', 0, PARAM_INT);
$selectedcourse = optional_param('selectedcourse', 0, PARAM_INT);
$unenrollall = optional_param('unenrollall', 0, PARAM_INT);
$usersid = optional_param('usersid', 0, PARAM_RAW);
$groupid = optional_param('groupid', 0, PARAM_INT);
$instanceid = optional_param('instance', 0, PARAM_INT);
$confirm    = optional_param('confirm', 0, PARAM_BOOL);
$confirm2    = optional_param('confirm2', 0, PARAM_INT);
$action     = optional_param('action', '', PARAM_ALPHANUMEXT);

$context = context_system::instance();

$canconfig = has_capability('moodle/course:enrolconfig', $context);

require_login();

$params = array('companyid' => $companyid,
    'courseid' => $courseid,
    'deptid' => $departmentid,
    'selectedcourse' => $selectedcourse,
    'groupid' => $groupid);

$urlparams = array('companyid' => $companyid);
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}
if ($courseid) {
    $urlparams['courseid'] = $courseid;
}

// Correct the navbar.
// Set the name for the page.
$linktext = get_string('company_course_users_title', 'block_iomad_company_admin');
// Set the url.
$linkurl = new moodle_url('/local/team/showteamcourses.php');

// Print the page header.
$PAGE->set_context($context);
$PAGE->set_url($linkurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title($linktext);
// Set the page heading.
$PAGE->set_heading(get_string('myhome') . " - $linktext");

// get output renderer
$output = $PAGE->get_renderer('block_iomad_company_admin');

// Javascript for fancy select.
// Parameter is name of proper select form element followed by 1=submit its form
$PAGE->requires->js_call_amd('block_iomad_company_admin/department_select', 'init', array('deptid', 1, optional_param('deptid', 0, PARAM_INT)));
$PAGE->navbar->add(get_string('dashboard', 'block_iomad_company_admin'));
$PAGE->navbar->add($linktext, $linkurl);

require_login(null, false); // Adds to $PAGE, creates $output.
iomad::require_capability('block/vedific_company_admin:company_course_users', $context);
// Set the companyid
$companyid = iomad::get_my_companyid($context);
$parentlevel = company::get_company_parentnode($companyid);
$companydepartment = $parentlevel->id;
$syscontext = context_system::instance();
$company = new company($companyid);

if (iomad::has_capability('block/vedific_company_admin:edit_all_departments', $syscontext)) {
    $userhierarchylevel = $parentlevel->id;
} else {
    $userlevel = $company->get_userlevel($USER);
    $userhierarchylevel = $userlevel->id;
}

$subhierarchieslist = company::get_all_subdepartments($userhierarchylevel);
if (empty($departmentid)) {
    $departmentid = $userhierarchylevel;
}

$userdepartment = $company->get_userlevel($USER);
$departmenttree = company::get_all_subdepartments_raw($userdepartment->id);
$treehtml = $output->department_tree($departmenttree, optional_param('deptid', 0, PARAM_INT));

$departmentselect = new single_select(new moodle_url($linkurl, $params), 'deptid', $subhierarchieslist, $departmentid);
$departmentselect->label = get_string('department', 'block_iomad_company_admin') .
        $output->help_icon('department', 'block_iomad_company_admin') . '&nbsp';

$coursesform = new company_ccu_courses_form($PAGE->url, $context, $companyid, $departmentid, $selectedcourse, $parentlevel);

$cohorts = team_get_teams($context->id, $page = 0, $perpage = 25, $search = '', $companyid, $departmentid);
//$usersform = new company_course_team_form($PAGE->url, $context, $companyid, $departmentid, $selectedcourse);
echo $output->header();

// Check the department is valid.
if (!empty($departmentid) && !company::check_valid_department($companyid, $departmentid)) {
    print_error('invaliddepartment', 'block_iomad_company_admin');
}

if ($coursesform->is_cancelled() || optional_param('cancel', false, PARAM_BOOL)) {
    if ($returnurl) {
        redirect($returnurl);
    } else {
        redirect(new moodle_url('/my'));
    }
} else {
    echo html_writer::tag('h3', get_string('company_courses_for', 'block_iomad_company_admin', $company->get_name()));
    echo html_writer::start_tag('div', array('class' => 'fitem'));
    echo $treehtml;
    echo html_writer::start_tag('div', array('style' => 'display:none'));
    echo $output->render($departmentselect);
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
    echo html_writer::start_tag('div', array('class' => 'vedificclear'));
    //Tab controls layout
    if (!$id && ($editcontrols = team_edit_controls($context, $linkurl))) {
        echo $OUTPUT->render($editcontrols);
    }

    if ($companyid > 0) {
        $coursesform->set_data($params);
        echo $coursesform->display();
        if(!empty($selectedcourse)) {
            $selectedcourseid = $selectedcourse;
        }else{
            $selectedcourseid = $coursesform->get_data()->selectedcourse;
        }
      //  $instances = enrol_get_instances($selectedcourseid, false);
        /*$instances = $DB->get_records('enrol',array('enrol'='cohort','status'=>ENROL_INSTANCE_ENABLED,
            'courseid'=>$selectedcourseid));*/
        $sql = "SELECT e.* FROM {enrol} e INNER JOIN {local_team} t ON e.customint1 = t.cohortid 
                 WHERE t.companyid=:companyid AND t.departmentid=:departmentid 
                  AND e.enrol=:enroltype  AND e.courseid=:courseid";

 
        //die;
        $params1 = array('companyid'=>$company->id,'departmentid'=>$departmentid,
            'enroltype'=>'cohort','courseid'=>$selectedcourseid);
        if($selectedcourseid){
        $instances = $DB->get_records_sql($sql,$params1);
        }else{
            $instances = array();
        }
       // $plugins   = enrol_get_plugins(false);

        $table = new html_table();
        $table->head  = array(get_string('name'), get_string('users'), get_string('action'));
        $table->align = array('left', 'center', 'center', 'center');
        $table->width = '100%';
        $table->data  = array();
        // iterate through enrol plugins and add to the display table
        $updowncount = 1;
        $icount = count($instances);
        $updown = array();
        $edit = array();
        $count = 0;
        $iconcount =1;
        $plugins = enrol_get_plugins(false);
        foreach ($instances as $instance) {
            if($instance->enrol == 'cohort') {
                if (!isset($plugins[$instance->enrol])) {
                    continue;
                }
                $plugin = $plugins[$instance->enrol];
                $url = new moodle_url('/local/team/showteamcourses.php', array('sesskey'=>sesskey(), 'selectedcourse'=>$selectedcourseid));
                $displayname = $plugin->get_instance_name($instance);
                if (!enrol_is_enabled($instance->enrol) or $instance->status != ENROL_INSTANCE_ENABLED) {
                    $displayname = html_writer::tag('span', $displayname, array('class'=>'dimmed_text'));
                }

                $users = $DB->count_records('user_enrolments', array('enrolid'=>$instance->id));
                $usersid = implode(",",array_keys($DB->get_records_sql("SELECT userid FROM {user_enrolments} WHERE enrolid = $instance->id")));

                if (has_capability('local/team:manage', $context)) {
                    $aurl = new moodle_url($url, array('action'=>'delete', 'instance'=>$instance->id,'deptid'=>$departmentid));
                    $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/delete', $strdelete, 'core', array('class' => 'iconsmall')));
                }

                if (enrol_is_enabled($instance->enrol) && has_capability('local/team:manage', $context)) {
                    if ($instance->status == ENROL_INSTANCE_ENABLED) {
                        $aurl = new moodle_url($url, array('action'=>'disable', 'instance'=>$instance->id,'deptid'=>$departmentid));
                        $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/hide', $strdisable, 'core', array('class' => 'iconsmall')));
                    } else if ($instance->status == ENROL_INSTANCE_DISABLED) {
                        $aurl = new moodle_url($url, array('action'=>'enable', 'instance'=>$instance->id,'deptid'=>$departmentid));
                        $edit[] = $OUTPUT->action_icon($aurl, new pix_icon('t/show', $strenable, 'core', array('class' => 'iconsmall')));
                    } else {
                        // plugin specific state - do not mess with it!
                        $edit[] = $OUTPUT->pix_icon('t/show', get_string('show'));
                    }

                }
                // link to instance management
                // Add a row to the tabl    
                if($iconcount == 1) {
                    $iconcount = count($edit);
                    $table->data[] = array($displayname, $users, implode('', $edit));
                }else{
                    $edit = array_splice($edit,$iconcount);
                    $table->data[] = array($displayname, $users, implode('', $edit));
                }
                
                $count++;
            }
        }  
    }
    echo html_writer::end_tag('div');

    if (isset($instances[$instanceid]) and isset($plugins[$instances[$instanceid]->enrol])) {
        if ($action === 'disable') {
                $instance = $instances[$instanceid];
                $plugin = $plugins[$instance->enrol];
                if (has_capability('local/team:manage', $context)) {
                    if ($instance->status != ENROL_INSTANCE_DISABLED) {
                        if (has_capability('local/team:manage', $context)) {
                            if (!$confirm2) {
                                $yesurl = new moodle_url('/local/team/showteamcourses.php',
                                                         array('id' => $course->id,
                                                               'action' => 'disable',
                                                               'instance' => $instance->id,
                                                               'confirm2' => 1,
                                                               'sesskey' => sesskey(),
                                                               'deptid'=>$departmentid,
                                                               'selectedcourse'=>$selectedcourseid
                                                           ));
                                /*$displayname = $plugin->get_instance_name($instance);
                                $message = markdown_to_html(get_string('disableinstanceconfirmself',
                                                            'enrol',
                                                            array('name' => $displayname)));*/
                                //$plugin->update_status($instance, ENROL_INSTANCE_DISABLED);
                                //redirect($yesurl);
                                //echo $OUTPUT->header();

                                //echo $OUTPUT->confirm($message, $yesurl, $PAGE->url);
                                //echo $OUTPUT->footer();
                                //die();
                            }
                        }

                        $plugin->update_status($instance, ENROL_INSTANCE_DISABLED);
                        redirect($yesurl);
                        /*$redirecturl = new moodle_url('/local/team/showteamcourses.php', array('sesskey'=>sesskey(), 'selectedcourse'=>$selectedcourseid));
                        redirect($redirecturl);*/
                    }
                }
        } else if ($action === 'enable') {
            $instance = $instances[$instanceid];
            $plugin = $plugins[$instance->enrol];
            if (has_capability('local/team:manage', $context)) {
                if ($instance->status != ENROL_INSTANCE_ENABLED) {
                    $plugin->update_status($instance, ENROL_INSTANCE_ENABLED);
                    // enrol teacher as well
                    $teamdata = $DB->get_record('local_team', array('cohortid' => $instance->customint1), '*', MUST_EXIST);
                    $groupid = $instance->customint2;
                    if ($teamdata) {
                        require_once("$CFG->dirroot/group/lib.php");
                        $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
                        $enrol_cohort = new enrol_cohort();
                        if ($teacherrole) {
                            if (strlen($teamdata) > 1) {
                                $enrol_cohort->enrol_user($instance, $teamdata->mentor, $teacherrole->id);
                                groups_add_member($groupid, $teamdata->mentor);
                            } else {
                                $teammentors = explode(",", $teamdata->mentor);
                                foreach ($teammentors as $teammentor) {
                                    $enrol_cohort->enrol_user($instance, $teammentor, $teacherrole->id);
                                    groups_add_member($groupid, $teammentor);
                                }
                            }
                        }
                    }
                    $redirecturl = new moodle_url('/local/team/showteamcourses.php', array('sesskey'=>sesskey(), 'selectedcourse'=>$selectedcourseid,'deptid'=>$departmentid));
                    redirect($redirecturl);
                }
            }
        } else if ($action === 'delete') {

            $instance = $instances[$instanceid];
            $plugin = $plugins[$instance->enrol];

            if (has_capability('local/team:manage', $context)) {
                if (!$confirm) {
                    $yesurl = new moodle_url('/local/team/showteamcourses.php',
                                             array('id' => $course->id,
                                                   'action' => 'delete',
                                                   'instance' => $instance->id,
                                                   'confirm' => 1,
                                                   'confirm2' => 1,
                                                   'sesskey' => sesskey(),
                                                   'deptid'=>$departmentid,
                                                   'selectedcourse'=>$selectedcourseid));

                    $nourl = new moodle_url('/local/team/showteamcourses.php',
                                             array('id' => $course->id,
                                                   'instance' => $instance->id,
                                                   'sesskey' => sesskey(),
                                                   'deptid'=>$departmentid,
                                                   'selectedcourse'=>$selectedcourseid));

                    $displayname = $plugin->get_instance_name($instance);
                    $message = markdown_to_html(get_string('deleteinstanceconfirmself',
                                                           'enrol',
                                                           array('name' => $displayname)));

                    echo $OUTPUT->confirm($message, $yesurl, $nourl);
                    echo $OUTPUT->footer();
                    die();
                }

                if($confirm2){
                    $plugin->delete_instance($instance);
                    $redirecturl = new moodle_url('/local/team/showteamcourses.php', array('selectedcourse'=>$selectedcourseid,'deptid'=>$departmentid));
                    redirect($redirecturl);
                }
            }

        }
    }

    if(!empty($table->data)){
        echo html_writer::table($table);
    }else{
        echo "<h2>Nothing to display</h2>";
    }
    echo $output->footer();
}
