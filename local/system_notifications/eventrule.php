<?php
$courseid = optional_param('courseid', 0, PARAM_INT);
//Global vars
global $CFG, $PAGE, $DB, $USER;

require_login();

if(!isset($CFG->rlms_allow_pm) || $CFG->rlms_allow_pm == false)
    print_error('only_freemium');

//Required Js
$PAGE->requires->js(new moodle_url($CFG->wwwroot .'/theme/rlmslmsfull/nifty/plugins/bootstrap-datepicker/bootstrap-datepicker.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot .'/local/rlmslms/performance/js/trainingsessions.js'));
$PAGE->requires->js(new moodle_url($CFG->wwwroot . '/blocks/configurable_reports/js/jquery-ui.min.js'));
//Required strings
$PAGE->requires->strings_for_js([
    'rule_name_required',
    'rule_areatomonitor_required',
    'rule_event_required',
    'rule_frequency_required',
    'rule_minutes_required',
    'rule_message_required',
    'rules_deleted',
    'addnewrule',
    'editrule',
    'rule_delete_ts'
], 'local_system_notifications');


require $CFG->dirroot . '/local/rlmslms/class/plms_form.php';

require_once($CFG->libdir.'/adminlib.php');

$eventlist = tool_monitor\eventlist::get_all_eventlist(true);
$pluginlist = tool_monitor\eventlist::get_plugin_list();
$eventlist = array_merge(array('' => get_string('choosedots')), $eventlist);
$pluginlist = array_merge(array('' => get_string('choosedots')), $pluginlist);


$PAGE->requires->yui_module('moodle-tool_monitor-dropdown', 'Y.M.tool_monitor.DropDown.init',
        array(array('eventlist' => $eventlist)));

$mform = new tool_monitor\rule_form(null, array('eventlist' => $eventlist, 'pluginlist' => $pluginlist, 'rule' => array(),
        'courseid' => 0, 'subscriptioncount' => 0));

try{

if ($mformdata = $mform->get_data()) {
    
    $rule = \tool_monitor\rule_manager::clean_ruledata_form($mformdata);

    if (empty($rule->id)) {
        \tool_monitor\rule_manager::add_rule($rule);
    } else {
        \tool_monitor\rule_manager::update_rule($rule);
    }

    redirect("$CFG->wwwroot/local/system_notifications/system_notifications.php");
} 
}catch(Exception $e){
    ddd($e);
}

$output = "";

$output .= html_writer::start_tag('div', ['id' => 'new-event-popup', 'class' => 'modal fade', 'tabindex' => '-1', 'aria-hidden' => "true"]);
    $output .= html_writer::start_tag('div', ['class' => 'modal-dialog modal-lg']);
        $output .= html_writer::start_tag('div', ['class' => 'modal-content']);
            $output .= html_writer::start_tag('div', ['class' => 'modal-header']);
                $output .= html_writer::tag('button', html_writer::tag('span', 'x'), ['class' => 'close', 'data-dismiss' => 'modal']);
                $output .= html_writer::tag('h4', get_string('addnewrule', 'local_system_notifications'));
            $output .= html_writer::end_tag('div');
            $output .= html_writer::start_tag('div', ['class' => 'modal-body']);
            
                $output .= $mform->render();

            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
    $output .= html_writer::end_tag('div');
$output .= html_writer::end_tag('div');

echo  $output;
    echo html_writer::start_tag('div', ['id' => 'lp-loading']);
        echo html_writer::start_tag('div', ['class' => 'showbox']);
            echo html_writer::start_tag('div', ['class' => 'loader']);
                 echo html_writer::start_tag('svg', ['class' => 'circular', 'viewBox' => "25 25 50 50"]);
                    echo html_writer::empty_tag('circle', ['class' => 'path', "cx" =>"50", "cy" =>"50", "r" => "20", "fill" => "none", "stroke-width" => "2", "stroke-miterlimit" => "10"]);
                 echo html_writer::end_tag('svg');
            echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
?>