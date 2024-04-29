<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../../config.php");
require_once("lib.php");

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_title('All Purchase');
$PAGE->set_heading('All Purchase');
$PAGE->set_url(new moodle_url('/local/products/purchases.php'));
if(!is_siteadmin()){
    print_error("Accesss denied, Sorry you cann't see this page ");
}
echo $OUTPUT->header();

echo html_writer::table(get_all_purchase());

echo $OUTPUT->footer();

