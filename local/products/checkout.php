<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("../../config.php");
require_once './classes/billdesk.php';

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/products/checkout.php'));
$PAGE->requires->js(new moodle_url('https://services.billdesk.com/checkout-widget/src/app.bundle.js'));
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js(new moodle_url('/local/products/checkout.js'));

$pagename = get_string("pluginname", "local_products");
$PAGE->set_title($pagename);
$PAGE->set_heading($pagename);
$billdesk = new \local_product\BilldeskUtil();


echo $OUTPUT->header();

echo '<input type="button" value="Pay with BillDesk" id="pay" disabled " />';

echo $OUTPUT->footer();
