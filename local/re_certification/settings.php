<?php

defined('MOODLE_INTERNAL') || die;

global $CFG;
$PAGE->requires->jquery();

if($ADMIN) {
    $settings = new \admin_settingpage('local_re_certification', get_string('pluginname', 'local_re_certification'));
    $ADMIN->add('localplugins', $settings);

    $options = array(
         1 => get_string('recertificatebycoursecompletion', 'local_re_certification'),
         0 => get_string('recertificatebyenrolldate', 'local_re_certification')
    );
    $settings->add(new admin_setting_configselect('recertificate', new lang_string('recertificate', 'local_re_certification'),
    new lang_string('recertificatedefault', 'local_re_certification'), 1, $options));

        $settings->add(new admin_setting_configcheckbox('recertification_autoenrol', get_string('setting:autoenrol', 'local_re_certification'),get_string('setting:autoenrol_title', 'local_re_certification'), 0));

}
