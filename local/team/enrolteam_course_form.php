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
require_once($CFG->libdir . '/enrollib.php');
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
            $mform->addElement('autocomplete', 'selectedcourse', get_string('selectenrolmentcourse', 'block_iomad_company_admin'), $this->courses, $autooptions);
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

class company_course_team_form extends moodleform {

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
        global $DB, $OUTPUT, $CFG;
        $this->_form->addElement('hidden', 'companyid', $this->selectedcompany);
        $this->_form->addElement('hidden', 'deptid', $this->departmentid);
        $this->_form->addElement('hidden', 'selectedcourse', $this->selectedcourse);
        $this->_form->setType('companyid', PARAM_INT);
        $this->_form->setType('deptid', PARAM_INT);
        $this->_form->setType('selectedcourse', PARAM_INT);
        $sql = 'SELECT c.id,c.name FROM {cohort} c INNER JOIN {local_team} lt ON lt.cohortid = c.id WHERE lt.companyid=:companyid AND lt.departmentid=:departmentid';

        $teams = $DB->get_records_sql($sql, array('companyid' => $this->selectedcompany, 'departmentid' => $this->departmentid));

        //$teamlist = ['' => get_string('select')];
        $teamlist = array();
        foreach ($teams as $team) {
            $teammembers = count(get_all_members($team->id));
            $teamlist[$team->id] = $team->name .' ('.$teammembers.')';
        }
        
        $options = array(
            'multiple' => true,
            'selectedteam' => true,
            'id'=>'selected_team',
            'onchange' => 'allowe_limit_users_enrol()'
        );

        if ($teams) {
            $enrolment_capacity = ($CFG->enrolment_capacity)?$CFG->enrolment_capacity:3000;
            $capacity = '<span style="color:blue;font-weight:bold">'.$enrolment_capacity.'</span>';
            $this->_form->addElement('static', 'capacity', get_string('enrolcapacity', 'local_team'), $capacity);
            $this->_form->addElement('autocomplete', 'selectedteam', get_string('selectedteam', 'local_team'), $teamlist,$options);

            $this->_form->addRule('selectedteam', get_string('required'), 'required', null, 'client');
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
        global $CFG;
        $errors = parent::validation($data, $files);
        $enrolment_capacity = ($CFG->enrolment_capacity)?$CFG->enrolment_capacity:3000;
        if ($data['selectedteam'] == '') {
            $errors['selectedteam'] = get_string('required');
        }else{
            foreach($data['selectedteam'] as $selectedteam) {
                $selectedteammembers[] = count(get_all_members($selectedteam));
            }
            if(array_sum($selectedteammembers) > (int) $enrolment_capacity){
                $errors['selectedteam'] = get_string('enrol_exceed_error','local_team');
            }
        }

        return $errors;
    }

}

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$companyid = optional_param('companyid', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$departmentid = optional_param('deptid', 0, PARAM_INT);
$selectedcourse = optional_param('selectedcourse', 0, PARAM_INT);
$groupid = optional_param('groupid', 0, PARAM_INT);

$context = context_system::instance();
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
$linkurl = new moodle_url('/local/team/enrolteam_course_form.php');

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
$PAGE->requires->js(new moodle_url('/local/team/custom.js'),true);
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
$usersform = new company_course_team_form($PAGE->url, $context, $companyid, $departmentid, $selectedcourse);
echo $output->header();

// Check the department is valid.
if (!empty($departmentid) && !company::check_valid_department($companyid, $departmentid)) {
    print_error('invaliddepartment', 'block_iomad_company_admin');
}

if ($coursesform->is_cancelled() || $usersform->is_cancelled() ||
        optional_param('cancel', false, PARAM_BOOL)) {
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
        if ($data = $usersform->get_data()) {
            foreach($data->selectedteam as $selectedteam){

                $team = $DB->get_record('cohort', array('id' => $selectedteam), '*');
    
                $course = $DB->get_record('course', array('id' => $data->selectedcourse), '*');
                //context for selected course
                $context = CONTEXT_COURSE::instance($course->id);
                //get all enrolled users from the course
                /*$enrolledusers = get_enrolled_users($context);
                //Get cohort members
                $teammembers = get_all_members($team->id);

                if($teammembers) {
                    $teammemberids = array_keys($teammembers);
                }
                
                if($enrolledusers){
                    $enrolleduserids = array_keys($enrolledusers);
                }
                
                $unrolusers = array_intersect($teammemberids, $enrolleduserids);

                $courseobj = $DB->get_record('course', array('id' => $course->id));
                $courseenrolmentmanager = new course_enrolment_manager($PAGE, $courseobj);
                
                //Unenrol users which is already enrol into course from the teammembers
                if($unrolusers){
                    foreach($unrolusers as $unroluser) {
                        $ues = $courseenrolmentmanager->get_user_enrolments($unroluser);
                        foreach ($ues as $ue) {
                            if ( $ue->enrolmentinstance->courseid == $course->id ) {
                                $courseenrolmentmanager->unenrol_user($ue);
                            }
                        }
                    }
                }*/
                //Enrol team(cohort) into course
                if (!is_team_already_exit_in_course($course->id, $team->id)) {
                    if ($team && $course) {
                        $studentrole = $DB->get_record('role', array('shortname' => 'student'));

                        $studentroleid = 0;
                        if ($studentrole) {
                            $studentroleid = $studentrole->id;
                        }
                        $teamcohort = new enrol_cohort();
                        if ($teamcohort->add_instance($course, array('customint1' => $team->id, 'customint2' => COHORT_CREATE_GROUP, 'roleid' => $studentroleid))) {
                            echo $OUTPUT->notification(get_string('teamddeddtocourse', 'local_team', $team), 'success');
                            //redirect($CFG->wwwroot .'/local/team/enrolteam_course_form.php', get_string('teamddeddtocourse','local_team'), null, \core\output\notification::NOTIFY_SUCCESS);
                        }
                    }
                }else{
                    // Team is already added sync to course
                    if(count($data->selectedteam) > 1){
                        echo $OUTPUT->notification(get_string('teamalreadyaddedtocourse', 'local_team', $team), 'warning');
                    }else{
                        redirect($CFG->wwwroot .'/local/team/enrolteam_course_form.php', get_string('teamalreadyaddedtocourse','local_team', $team), null, \core\output\notification::NOTIFY_WARNING);
                    }
                     //echo $OUTPUT->notification(get_string('teamalreadyaddedtocourse', 'local_team'), 'warning');
                     //redirect($CFG->wwwroot .'/local/team/enrolteam_course_form.php');
                      
                    
                }
            }
            //redirect($CFG->wwwroot .'/local/team/enrolteam_course_form.php', get_string('teamddeddtocourse','local_team',$CFG->wwwroot), null, \core\output\notification::NOTIFY_SUCCESS);
            redirect($CFG->wwwroot .'/local/team/enrolteam_course_form.php',get_string('urlredirect','local_team',$CFG->wwwroot), 5);
        }
        $coursesform->set_data($params);
        echo $coursesform->display();
        if ($data = $coursesform->get_data() || !empty($selectedcourse)) {
            if ($courseid > 0) {
                $course = $DB->get_record('course', array('id' => $courseid));
                $usersform->set_course(array($course));
                $usersform->process();
                $usersform = new company_course_team_form($PAGE->url, $context, $companyid, $departmentid, $selectedcourse);
                $usersform->set_course(array($course));
                $usersform->set_data(array('groupid' => $groupid));
            } else if (!empty($selectedcourse)) {
                //$usersform->set_course($selectedcourse);
            }
            $usersform->display();
        } else if ($courseid > 0) {
            $course = $DB->get_record('course', array('id' => $courseid));
            $usersform->set_course(array($course));
            $usersform->process();
            $usersform = new company_course_team_form($PAGE->url, $context, $companyid, $departmentid, $selectedcourse);
            $usersform->set_course(array($course));
            $usersform->set_data(array('groupid' => $groupid));
            $usersform->display();
        }
    }
    echo html_writer::end_tag('div');

    echo $output->footer();
}
