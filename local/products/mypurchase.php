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
$PAGE->set_title('My Purchase');
$PAGE->set_heading('My Purchase');
$PAGE->set_url(new moodle_url('/local/products/mypurchase.php'));
echo $OUTPUT->header();

echo html_writer::table(get_my_purchase());

echo $OUTPUT->footer();

