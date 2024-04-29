<?php
require_once("../../config.php");

// Security Validations.
require_login();

// Validate capabilities.
$learningpathsmanager = has_capability('local/learningpaths:managealllearningpaths', context_system::instance());
$learningpathscompanymanager = has_capability('local/learningpaths:managecompanylearningpaths', context_system::instance());
if (!$learningpathsmanager && !$learningpathscompanymanager) {
    throw new moodle_exception(get_string('access_denied'));
}
global $PAGE,$OUTPUT, $CFG;

// Require learningpath class.
require_once("{$CFG->dirroot}/local/learningpaths/classes/objects/LearningPath.php");
require_once("{$CFG->dirroot}/local/learningpaths/lib.php");
// Learning path object definition and check forms submit to create new learningpaths.
$learningpath = new LearningPath();
$learningpath->check_forms_submit(optional_param('form', "", PARAM_TEXT));

// Page configurations.
$PAGE->set_title(get_string('pluginname', 'local_learningpaths'));
$PAGE->set_heading(get_string('pluginname', 'local_learningpaths'));
//$PAGE->navbar->add(get_string('pluginname', 'local_learningpaths'), '/local/learningpaths/index.php');
// Load additional resources.
$PAGE->requires->css(new moodle_url("/local/learningpaths/css/styles.css"));
$PAGE->requires->js_call_amd('local_learningpaths/save_botton', 'init');
//$PAGE->requires->css(new moodle_url("/local/rlmslms/performance/css/learning-paths.css"));

// Show header.
// Set Page layout.
// Set external page admin.
$context = context_system::instance();
$PAGE->set_context($context);
$pageurl = new moodle_url($CFG->wwwroot."/local/learningpaths/index.php");
// Set page URL.
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
echo $OUTPUT->header();

echo html_writer::start_tag('div', array('class' => 'content-title'));
    $title = get_string('pluginname', 'local_learningpaths');
    $descriptionlp = get_string('descriptionname', 'local_learningpaths');
    echo html_writer::tag('h2', $title, ['class' => 'title-learning']);
    echo html_writer::tag('span', $descriptionlp, ['class' => 'txt-learning']);

   echo learningpaths_view();
echo html_writer::end_tag('div');

// Show footer.
echo $OUTPUT->footer();
