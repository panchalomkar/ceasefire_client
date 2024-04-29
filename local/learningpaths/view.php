<?php
require_once ("../../config.php");

// Load moodle configurations, core, learning paths functions library and learning path clasess.
require_once ("lib.php");
require_once ("classes/objects/LearningPath.php");
// Security Validations.
require_login();
$stringman = get_string_manager();
$strings = $stringman->load_component_strings('local_learningpaths', 'en');


$PAGE->requires->strings_for_js(array_keys($strings), 'local_learningpaths');
try {
    // Validate capabilities.
    $learningpathsmanager = has_capability('local/learningpaths:managealllearningpaths', context_system::instance());
    $learningpathscompanymanager = has_capability('local/learningpaths:managecompanylearningpaths', context_system::instance());
    if (!$learningpathsmanager && !$learningpathscompanymanager) {
        throw new moodle_exception(get_string('access_denied'));
    }

    // Required global variables from moodle.
    global $PAGE, $CFG, $OUTPUT;

    // If form parameter exist, then check the submit.
    $formname = optional_param('form', "", PARAM_TEXT);

    // Learning path object definition. If learningpathid was sent them use it as id.
    $learningpathid = optional_param('learningpathid', 0, PARAM_INT);
    $learningpathid = ($learningpathid > 0) ? $learningpathid : optional_param('id', 0, PARAM_INT);
    $learningpath = new LearningPath($learningpathid);

    // Check post data. This function will do save of forms data.
    if (empty(!$formname)) {
        $learningpath->check_forms_submit($formname);
        ;
    }

    // Adding page title and heading.
    $PAGE->set_title(get_string('pluginname', 'local_learningpaths'));
    $PAGE->set_heading(get_string('pluginname', 'local_learningpaths'));
    $PAGE->navbar->add(get_string('pluginname', 'local_learningpaths'), '/local/learningpaths/index.php');
    $PAGE->navbar->add($learningpath->data->name);
    // Including additional css and js files.
    $PAGE->requires->css('/local/learningpaths/css/styles.css');
    $PAGE->requires->css('/local/learningpaths/css/switchery.css');
    $PAGE->requires->css(new moodle_url("{$CFG->wwwroot}/blocks/rlms_lpd/styles.css"));
    $PAGE->requires->js(new moodle_url("{$CFG->wwwroot}/local/learningpaths/js/functions.js"));
    $PAGE->requires->js_call_amd('local_learningpaths/learningpaths', 'lpactions');
    $PAGE->requires->js_call_amd('local_learningpaths/save_botton', 'init');
    // Print common page header and title.
    echo $OUTPUT->header();

    echo html_writer::start_tag('div', array('class' => 'content-title back row'));
    $params = array(
        'class' => 'wid wid-icon-phback-to',
        'data-placement' => 'right',
        'data-toggle' => 'tooltip',
        'aria-hidden' => 'true',
        'data-original-title' => get_string('back', 'local_learningpaths')
    );

    echo html_writer::start_tag('div', array('class' => ''));
    $icon = html_writer::tag('i', '', $params);
    echo html_writer::tag('a', $icon, array('class' => 'back_page', 'href' => $CFG->wwwroot . '/local/learningpaths/#'));
    echo html_writer::end_tag('div');

    echo html_writer::start_tag('div', array('class' => 'col-md-11'));
    echo html_writer::tag('h2', $learningpath->data->name, array('class' => 'title-learning'));
    $descriptionlp = get_string('descriptionname_int', 'local_learningpaths');
    echo html_writer::tag('span', $descriptionlp, ['class' => 'txt-learning']);
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
    ?>
    <div class="content tabs-panel">
        <div class="row tbs-content">
            <?php echo $learningpath->render_navigation_tabs(); ?>
        </div>
    </div>

    <div class="tab-content">
        <?php echo $learningpath->render_tabs($learningpath->data); ?>
    </div>
    <?php
    // Print common page footer.
    echo $OUTPUT->footer();

} catch (Exception $e) {
    throw $e;
}
