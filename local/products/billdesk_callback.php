<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once '../../config.php';
require_once './classes/billdesk.php';
$message = required_param('msg', PARAM_RAW);
$billdesk = new local_products\BilldeskUtil();
$result = $billdesk->processTransactionCallback($message);
redirect(new moodle_url('/local/products/'));

