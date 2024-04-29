<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace local_products\task;

require '../billdesk.php';
defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task to sync users with Azure AD.
 */
class transaction_status_enquiry extends \core\task\scheduled_task {

    public function execute() {
        global $DB;
        $billdeskUtil = new \local_products\BilldeskUtil();
        $transactions = $DB->get_records('billdesk_transaction', array('status' => $billdeskUtil::TRANSACTION_INITIATED_STATUS));
        foreach ($transactions as $transaction) {
            $billdeskUtil->updatePaymentStatus($transaction);
        }
    }

    public function get_name(): string {
        
    }

}
