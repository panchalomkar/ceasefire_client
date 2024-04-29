<?php

global $CFG, $SESSION, $PAGE, $USER;

require_once('../../config.php');

require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/authlib.php');
require_once($CFG->dirroot . '/user/filters/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->dirroot . '/local/people/lib.php');
require_once($CFG->dirroot . '/theme/remui/classes/widget.php');

$PAGE->requires->css(new moodle_url('/theme/remui/style/select2.min.css'));
$PAGE->requires->css(new moodle_url('/local/people/css/styles.css'));
 
$sitecontext  = context_system::instance();
$viewallusers = has_capability('local/people:viewallusers', $sitecontext);
// Editing existing user.
if( false == $viewallusers ){
    print_error('cannotviewallusers', 'local_people');
}

//(array $options, string $label = '' , string $id = '', string $selected = '', string $firstoption = '' ,string $name = "", bool $required = false, array $params = array(), bool $multiple = false)
$title_role = get_string('role','local_people');
$options = enroll_display_roles_bulk();

$select = \theme_remui\widget::select($options,$title_role,'selectroles',5,'','role', true);


$list = get_courses_display();
$title_course = get_string('courses','local_people');
$select_2 = \theme_remui\widget::select($list,$title_course,'selectcourses','','','course[]', true,array('size' => '10',true),true);

$inputs = sesskey();

$modal_body = '';
$modal_body .= html_writer::tag('div', '', array('id' => 'message'));

//The form is placed inside the body of modal
$modal_body .= html_writer::start_tag('form', array('id' => 'EnrolForm', 'metod' => 'post', 'class' => 'form-horizontal','onsubmit' => 'return courseValid()'));
//$modal_body .= html_writer::start_tag('form', array('id' => 'EnrolForm', 'metod' => 'post', 'class' => 'form-horizontal'));
    
    $modal_body .= html_writer::start_tag('div', array('style' => 'display: none;'));
        $modal_body .= html_writer::start_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => $inputs));
        $modal_body .= html_writer::start_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 'enroll'));
    $modal_body .= html_writer::end_tag('div');

    $modal_body .= html_writer::start_tag('div', array('class' => 'form-group row fitem'));
        $modal_body .= $select;
    $modal_body .= html_writer::end_tag('div');

    $courseselected = get_string('course_select_help','local_people');
    $modal_body .= html_writer::tag('div',$courseselected, array('id' => 'course_select_help'));

    $modal_body .= html_writer::start_tag('div', array('class' => 'form-group row fitem'));
        $modal_body .= $select_2;
    $modal_body .= html_writer::end_tag('div');

    $modal_body .= html_writer::start_tag('div', array('class' => 'form-group row fitem'));
        
        $modal_body .= html_writer::start_tag('div', array('class' => 'col-md-4'));
            $modal_body .= html_writer::tag('label',get_string('startdate','local_people'), array('class' => 'label-control bmd-label-static'));
        $modal_body .= html_writer::end_tag('div');

        $modal_body .= html_writer::start_tag('div', array('class' => 'col-sm-8 form-group is-filled startdate'));
            

            $modal_body .= html_writer::tag('input', '', array('id' => 'startdate', 'name' => 'startdate', 'type' => 'text', 'class' => 'form-control', 'value' => ''));
            
            $modal_body .= html_writer::start_tag('div', array('class' => 'input-group-prepend'));
                $modal_body .= html_writer::start_tag('a', array('class' => 'visibleifjs', 'onclick' =>'document.getElementById("startdate").focus();'));
                    $modal_body .= html_writer::tag('i', '', array('class' => 'fa fa-calendar'));
                $modal_body .= html_writer::end_tag('a');
            $modal_body .= html_writer::end_tag('div');

        $modal_body .= html_writer::end_tag('div');

    $modal_body .= html_writer::end_tag('div');

    $modal_body .= html_writer::start_tag('div', array('class' => 'form-group row fitem'));
        
        $modal_body .= html_writer::start_tag('div', array('class' => 'col-md-4'));
            $modal_body .= html_writer::tag('label',get_string('enddate','local_people'), array('class' => 'label-control bmd-label-static'));
        $modal_body .= html_writer::end_tag('div');

        $modal_body .= html_writer::start_tag('div', array('class' => 'col-sm-8 form-group is-filled enddate'));
            $modal_body .= html_writer::tag('input', '', array('id' => 'enddate', 'name' => 'enddate', 'type' => 'text', 'class' => 'form-control', 'value' => ''));
            
            $modal_body .= html_writer::start_tag('div', array('class' => 'input-group-prepend'));
                $modal_body .= html_writer::start_tag('a', array('class' => 'visibleifjs', 'onclick' =>'document.getElementById("enddate").focus();'));
                    $modal_body .= html_writer::tag('i', '', array('class' => 'fa fa-calendar'));
                $modal_body .= html_writer::end_tag('a');
            $modal_body .= html_writer::end_tag('div');

        $modal_body .= html_writer::end_tag('div');
        $modal_body .= html_writer::tag('input', '', array('type' => 'hidden', 'name' => 'action','value' => 'enroll'));

    $modal_body .= html_writer::end_tag('div');
    
$modal_body .= html_writer::end_tag('form');


$modal_body .= '<script type="text/javascript">
function courseValid() 
{  
    if(jQuery("#selectcourses").val() == ""){
        jQuery("span.select2-selection.select2-selection--multiple").css("border", "1px solid red");
        return false;
    }
}
</script>';

$footer_body ='
    <button onclick="courseValid()" type="submit" name="submit" id="enroll" class="btn btn-round btn-primary">'.get_string('enroll','local_people').'</button>
    <button type="button" class="btn btn-round btn-default" data-action="hide" data-dismiss="modal">'.get_string('cancel').'</button>';

$stringman = get_string_manager();
$strings = $stringman->load_component_strings('local_people', 'en');
$PAGE->requires->strings_for_js(array_keys($strings), 'local_people');

$PAGE->requires->js_call_amd('local_people/main', 'init',[
    'modal' => [
        'title' => get_string('enrol_users','local_people'),
        'body' => $modal_body,
        'footer' => $footer_body
    ]
]);
$filter = new stdClass();
$page           = optional_param('page', 0, PARAM_INT);
$sortfield      = optional_param('sort', '', PARAM_TEXT);
$order          = optional_param('order', 'ASC', PARAM_TEXT);

/**
* Add param confirmuser to update mdl_user table
* @author Yesid V.
* @since Junio 24, 2017
* @remui
*
*/

$confirmuser  = optional_param('confirmuser', 0, PARAM_INT);
$sendconfirmuser  = optional_param('sendconfirmuser', 0, PARAM_INT);
$delete    = optional_param('delete', 0, PARAM_INT);
$suspend   = optional_param('suspend', 0, PARAM_INT);
$unsuspend = optional_param('unsuspend', 0, PARAM_INT);
$confirm      = optional_param('confirm', '', PARAM_ALPHANUM); //md5 confirmation hash
/**
* Add userperpage so the pagination
* bar do proper filtering
* @author Esteban E.
* @since October 10 of 2016
* @remui
*/
if(isset($_REQUEST['userperpage'])){
    $SESSION->userperpage = $_REQUEST['userperpage'];
}else{
    $SESSION->userperpage = 20 ;
}

require_login();

if(isset($SESSION->currentrole) && $SESSION->currentrole->current == 5){
    redirect($CFG->wwwroot);
    die();
}

$return = array('order' => $order,'sort' => $sortfield );

$return = array_filter($return);

$returnurl = new moodle_url('/local/people/index.php', array_filter($return) );
$sesskey   = optional_param('sesskey', null, PARAM_RAW);

if($sesskey) {
    $returnurl = $SESSION->return;
}

//////////////////////////// ACTION DELETE //////////////////////////////

if ($delete and confirm_sesskey()) {
    require_capability('moodle/user:delete', $sitecontext);
    $user = $DB->get_record('user', array('id' => $delete, 'mnethostid' => $CFG->mnet_localhost_id), '*', MUST_EXIST);

    if (is_siteadmin($user->id)) {
        print_error('useradminodelete', 'error');
    }

    if ($confirm != md5($delete)) {
        echo $OUTPUT->header();
        $fullname = fullname($user, true);
        echo $OUTPUT->heading(get_string('deleteuser', 'admin'));
        $optionsyes = array('delete' => $delete, 'confirm' => md5($delete), 'sesskey' => sesskey());
        echo $OUTPUT->confirm(get_string('deletecheckfull', '', "'$fullname'"), new moodle_url($returnurl, $optionsyes), $returnurl);
        echo $OUTPUT->footer();
        die;
    } else if (data_submitted() and ! $user->deleted) {

        if (delete_user($user)) {
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($returnurl);

        } else {

            \core\session\manager::gc(); // Remove stale sessions.
            echo $OUTPUT->header();
            echo $OUTPUT->notification($returnurl, get_string('deletednot', '', fullname($user, true)));

        }
    }
}
//////////////////////////// ACTION DELETE:END //////////////////////////////


//////////////////////////// ACTION SUSPEND ////////////////////////////////

if ($suspend and confirm_sesskey()) {

    require_capability('moodle/user:update', $sitecontext);

    if ($user = $DB->get_record('user', array('id' => $suspend, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0))) {

        if (!is_siteadmin($user) and $USER->id != $user->id and $user->suspended != 1) {

            $user->suspended = 1;
            // Force logout.
            \core\session\manager::kill_user_sessions($user->id);
            user_update_user($user, false);
        }
    }

    redirect($returnurl);
}

//////////////////////////// ACTION SUSPEND:END  //////////////////////////


//////////////////////////// ACTION UNSUSPEND ////////////////////////////////

if ($unsuspend and confirm_sesskey()) {

    require_capability('moodle/user:update', $sitecontext);

    if ($user = $DB->get_record('user', array('id' => $unsuspend, 'mnethostid' => $CFG->mnet_localhost_id, 'deleted' => 0))) {
        if ($user->suspended != 0) {
            $user->suspended = 0;
            user_update_user($user, false);
        }
    }
    redirect($returnurl);
}
if(isset($_POST['bulk_action'])){

    $json = $_POST['bulk_action'];

    $params = json_decode($json);
    
    bulk_action($params->name, $params->data);

}
//////////////////////////// ACTION UNSUSPEND:END  //////////////////////////

//////////////////////////// ACTION ENROLL ////////////////////////////////

$action = optional_param('action', NULL, PARAM_ALPHA);

if ($action == 'enroll' and confirm_sesskey() ) {

    global $DB;

    $roleid           =  required_param('role', PARAM_INT);
    $selected_users   =  required_param_array('id', PARAM_INT);
    $selected_courses =  required_param_array('course', PARAM_INT);

    if ($selected_users && $selected_courses && $roleid) {

        $userlists   = '';
        $courselists = '';

        foreach ($selected_courses as $courseid) {

            $course = get_course($courseid);

            $courselists .=$course->fullname.', ';

            $startdate = optional_param('startdate', '', PARAM_RAW);
            $enddate   = optional_param('enddate', '', PARAM_RAW);
            $recovergrades = 0;

            if (empty($roleid)) {
                $roleid = null;
            }
            if ($startdate) {
                $timestart = strtotime($startdate);
            } else {
                $today = time();
                $today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);
                $timestart = $today;
            }

            if ($enddate) {
                $timeend = strtotime($enddate);
            } else {
                $timeend = 0;
            }

            $manager = new course_enrolment_manager($PAGE, $course);
            $plugins = $manager->get_enrolment_plugins(true); // Do not allow actions on disabled plugins.
            $instances = $manager->get_enrolment_instances();
            foreach ($instances as $instance) {

                if ($instance->enrol == 'manual') {
                    $enrolid = $instance->id;

                    break;
                }
            }

             if (!array_key_exists($enrolid, $instances)) {
                 //continue;
               }

            $instance = $instances[$enrolid];
            if (!isset($plugins[$instance->enrol])) {
                throw new enrol_ajax_exception('enrolnotpermitted');
            }
            $plugin = $plugins[$instance->enrol];
            $context = context_course::instance($course->id);
            foreach ($selected_users as $userid) {

                $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
                if ($plugin->allow_enrol($instance) && has_capability('enrol/' . $plugin->get_name() . ':enrol', $context)) {
                    $plugin->enrol_user($instance, $user->id, $roleid, $timestart, $timeend, null, $recovergrades);
                    $userlists .= $user->firstname.', ';

                }
            }
        }

    }
}

//////////////////////////// ACTION ENROLL:END  //////////////////////////

//////////////////////////// ACTION CONFIRM USER  //////////////////////////

/**
* Validate if confirmuser param, validate user capabilities and execute user_confirm function to set confirmed = 1 on mdl_user table 
* @author Yesid V.
* @since Junio 24, 2017
* @remui
*
*/

if ($confirmuser and confirm_sesskey()) {

        require_capability('moodle/user:update', $sitecontext);
        //if (!$user = $DB->get_record('user', array('id'=>$confirmuser, 'mnethostid'=>$CFG->mnet_localhost_id))) {
	if (!$user = $DB->get_record('user', array('id'=>$confirmuser))) {
            print_error('nousers');
        }

        $auth = get_auth_plugin($user->auth);

        $result = $auth->user_confirm($user->username, $user->secret);

        if ($result == AUTH_CONFIRM_OK or $result == AUTH_CONFIRM_ALREADY) {
            redirect($returnurl);
        } else {
            echo $OUTPUT->header();
            redirect($returnurl, get_string('usernotconfirmed', '', fullname($user, true)));
        }

}

if ($sendconfirmuser and confirm_sesskey()) {
    require_capability('moodle/user:update', $sitecontext);
    if (!$user = $DB->get_record('user', array('id'=>$sendconfirmuser))) {
        print_error('nousers');
    }
    \core\event\user_created::create_from_userid($user->id)->trigger();

    if (! send_confirmation_email($user)) { 
        $this->ajax_notice(get_string('auth_emailnoemail','auth_email'));
        //print_error('auth_emailnoemail','auth_email');
        die();
    }
}

//////////////////////////// ACTION CONFIRM USER:END //////////////////////////

$json = isset($_POST['filters']) ? $_POST['filters'] : '';

$params = json_decode($json);
$multifilter = array();


if( !empty($params) )

    foreach($params as $param) {

        $filter = new stdClass();

        if(isset($param->textfilter)) {
            if ( $param->{$param->textfilter} != "" ) {
                $filter->field = $param->textfilter;
                $filter->value = $param->{$param->textfilter};
                $filter->type  = 'textfilter';
                $filter->page  = 0;
            }

        } else if(isset($param->selectfilter)) {
            if ( $param->{$param->selectfilter . 'op'} != "" ) {
                $filter->field = $param->selectfilter;
                $filter->op    = $param->{$param->selectfilter . 'op'};
                $filter->page  = 0;
                $filter->type  = 'selectfilter';
            }

        } else if(isset($param->datefilter)) {

            if ( $param->{$param->datefilter . 'gt'} != "" ) {
                $filter->field = $param->datefilter;

                $filter->gt     = $param->{$param->datefilter . 'gt'};
                $filter->lt     = $param->{$param->datefilter . 'lt'};
                $filter->access = $param->neveraccess;
                $filter->edited = $param->nevermodified;
                $filter->type   = 'datefilter';
                $filter->page   = $page;
            }

            /**
            * First Access - Last Access - never access
            * @author Carlos Alcaraz
            * @since Apr 11/2018
            */
            if ( isset($param->neveraccess) ) {
                    $filter->field  = $param->datefilter;
                    $filter->edited = $param->neveraccess;
                    $filter->type   = 'datefilterr';
                    $filter->sm     = 'date';
                    $filter->page   = $page;
            }
            
            /**
            * Last modified - never modified
            * @author Carlos Alcaraz
            * @since Apr 11/2018
            */
            if ( isset($param->nevermodified) ) {
                    $filter->field  = $param->datefilter;
                    $filter->edited = $param->nevermodified;
                    $filter->type   = 'datefilterr';
                    $filter->sm     = 'date';
                    $filter->page   = $page;
            }

        } else if(isset($param->multipleselectfilter)) {

            if(!empty($param->{$param->multipleselectfilter . 'op[]'})){

                $filter->field = $param->multipleselectfilter;

                $filter->ar    = $param->{$param->multipleselectfilter . 'op[]'};
                $filter->op    = "'".implode("','",$filter->ar)."'";

                $filter->type  = 'multipleselectfilter';
                $filter->page  = $page;
            }

        } else if(isset($param->systemrole)) {
            if ( $param->{$param->systemrole . 'op'} != "" ) {
                $filter->field = $param->systemrole;

                $filter->op    = $param->{$param->systemrole . 'op'};

                $filter->type  = 'systemrole';
                $filter->page  = $page;
            }

        } else if(isset($param->courserole)) {
            if ( $param->{"courserolename[]"} != "" ) {
                $filter->field = $param->courserole;

                $filter->ar    = $param->{"courserolename[]"};
                $filter->op    = "'".implode("','",$filter->ar)."'";

                $filter->role  = $param->courseroleop;

                $filter->type  = 'courserole';
                $filter->page  = $page;
            }
        } else { 
            $la_attribute = get_object_vars($param); 
            $lc_attribute = array_keys($la_attribute)[0];
            if ( $lc_attribute != "" && $lc_attribute != "clearall" ) {
                $filter->field = $lc_attribute;
                $filter->value = $param->{$lc_attribute};
                $filter->type  = 'textfilter';
                $filter->page  = 0;
            }
        }

        if( isset($filter->field) && $filter->field != "clearall" ){
            
            $multifilter[$filter->field] = $filter;
        }

    }

if ($sortfield) {

    $filter->sort  = $sortfield;
    $filter->order = $order;
}


//////////////////////////////////////////////////////////

if(isset($params) && !is_null($params) ) {

    $SESSION->multifilter = $multifilter;

    $SESSION->filter = $filter;
}

if(isset($removefilter)) {
    unset($SESSION->multifilter[$removefilter] );
}

/**
* Descripcion : Add clear all case, delete the multifiter SESSION var
* @author Hernan A.
* @since 17/08/2016
* @remui
*/

if(isset($_POST['clearall'])) {

    unset($SESSION->multifilter);
}

if(isset($SESSION->filter) && !(isset($filter->type) || isset($filter->sort)) ) {

    $filter = $SESSION->filter;
}

$multifilter = isset($SESSION->multifilter) ? $SESSION->multifilter : array();

if(isset($_GET['filter'])){

    unset($filter->type);
    unset($filter->field);
    unset($multifilter);
    unset($SESSION->multifilter);
}
/**
* Add userperpage so the pagination
* bar do proper filtering
* @author Esteban E.
* @since October 10 of 2016
* @remui
*/
if(isset($SESSION->userperpage)) {
    $filter->userperpage = $SESSION->userperpage;
}else {
    $filter->userperpage = 20;
}

//////////////////////////////////////////////////////////

$filter->page = $page;

$PAGE->set_url(new moodle_url($CFG->wwwroot . '/local/people/index.php?demo'));
$PAGE->set_context(context_system::instance());
/**
* @issue #6: Issue with translations in LMS - people
* @author Jonatan Uribe
* @since 2018-02-14
* @remui
*/
$PAGE->set_title(get_string('pluginname','local_people'));
$PAGE->set_heading(get_string('pluginname','local_people'));
$PAGE->set_pagelayout('default_plugins');
$PAGE->navbar->add(get_string('pluginname', 'local_people'), '/local/people/index.php');
echo $OUTPUT->header();

//$PAGE->requires->js(new moodle_url('people.js'));

echo html_writer::start_tag('div');
    $var = $CFG->wwwroot . '/support/doc/quick-training-guides/adding-and-editing-users';

    echo html_writer::start_tag('div', array('class' => 'col-sm-12 titepeople'));
        echo html_writer::tag('h2',get_string('pluginname','local_people'), array('class' => 'title-coursecoursewizard'));
        echo html_writer::tag('p',get_string('peopledesc','local_people'), array('class' => 'description_newcourse'));
    echo html_writer::end_tag('div');
    
    echo html_writer::start_tag('div', array('class' => 'row content px-0'));
    echo get_filter_block($filter, $multifilter);
    echo get_user_block($filter, $sortfield, $order, $multifilter);
    echo html_writer::end_tag('div');

    echo html_writer::start_div('',['id' => 'message_bulk_container']);
        get_message_form_html()->display();
    echo html_writer::end_div();
echo html_writer::end_tag('div');
echo $OUTPUT->footer();

$SESSION->return = $returnurl;

