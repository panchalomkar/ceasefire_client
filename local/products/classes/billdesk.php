<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace local_products;

defined('MOODLE_INTERNAL') || die();

class BilldeskUtil {

    private const TRANSACTION_INITIATED_STATUS = "INITIATED";
    private const TRANSACTION_SUCCESS_STATUS = "SUCCESS";
    private const TRANSACTION_PENDING_STATUS = "PENDING";
    private const TRANSACTION_FAILED_STATUS = "FAILED";
    private const TRANSACTION_ERROR_STATUS = "ERROR";
    private const TRANSACTION_TECHNICAL_ERROR_STATUS = "TECHNICAL_ERROR";
    private const BILLDESK_SUCCESS_STATUS_CODE = '0300';
    private const BILLDESK_FAILURE_STATUS_CODE = '0399';
    private const BILLDESK_ERROR_STATUS_CODE = 'NA';
    private const BILLDESK_PENDING_STATUS_CODE = '0002';
    private const BILLDESK_TECNICAL_ERROR_STATUS_CODE = '0001';
    private const FIELD_PRICE_SHORTNAME = 'price';
    private const ROLE_ID = 5;

    public function getMessaage(string $merchantid, string $securityid, string $trans_id, float $amount, string $email, string $mobile): string {

        return "$merchantid|$trans_id|NA|$amount|NA|NA|NA|INR|NA|R|$securityid|NA|NA|F|$email|$mobile|NA|NA|NA|NA|NA|NA";
    }

    public function getCheckSum(string $message, string $checksum): string {
        return strtoupper(hash_hmac('sha256', $message, $checksum, false));
    }

    public function initCheckout(): array {

        global $USER;
        $response = array();

        $email = 'NA';
        $mobile = 'NA';
        if (isset($USER->email) && validate_email($USER->email)) {
            $email = $USER->email;
        }


        $courseids = "";
        $amount = 0;

        $courses = get_products_in_cart();

        //return $courses;
        foreach ($courses as $course) {

            $courseids .= $course . ',';

            $amount += $this->get_course_price($course);
        }
        $courseids = trim($courseids, ',');
        $amount = floatval($amount);
       
        $product_config = get_config('local_products');
        $transection_id = "RAPTECH_" . $this->initTransaction($USER->id, $amount, $courseids);

        $message = $this->getMessaage($product_config->merchantid, $product_config->securityid, $transection_id, $amount, $email, $mobile);
        $response['message'] = $message . '|' . $this->getCheckSum($message, $product_config->checksum);
        return $response;
    }

    public function initTransaction($userid, $amount, $courseids) {
        global $DB;
        $transaction = new \stdClass();
        $transaction->userid = $userid;
        $transaction->amount = $amount;
        $transaction->status = self::TRANSACTION_INITIATED_STATUS;
        $transaction->timecreated = time();
        $transaction->courseids = $courseids;
        return $DB->insert_record('billdesk_transaction', $transaction);
    }

    public function processTransactionCallback($message) {
        global $USER, $DB, $CFG;
        require_once("$CFG->dirroot/enrol/locallib.php");
        require_once("$CFG->dirroot/local/products/lib.php");

        $response = explode('|', $message);

        $transaction_id = explode('_', $response[1]);


        $transaction = $DB->get_record('billdesk_transaction', array('id' => $transaction_id[1]));
        if ($transaction) {
            if (self::TRANSACTION_INITIATED_STATUS !== $transaction->status) {
                print_error(get_string('Invalid_transaction_status', 'local_products'));
            }
            $authStatus = $response[14];
            $transaction->txn_reference_no = $response[2];
            $transaction->auth_status = $authStatus;
            $transaction->bank_reference_no = $response[3];
            $transaction->error_status = $response[23];
            $transaction->error_description = $response[24];


            if (self::BILLDESK_SUCCESS_STATUS_CODE === $authStatus) {

                $courseids = str_getcsv($transaction->courseids);
                foreach ($courseids as $courseid) {
                    role_assign(self::ROLE_ID, $transaction->userid, \context_course::instance($courseid));
                    removefromcart($transaction->userid, $courseid);
                }
                $transaction->status = self::TRANSACTION_SUCCESS_STATUS;
            } else if (self::BILLDESK_FAILURE_STATUS_CODE === $authStatus) {

                $transaction->status = self::TRANSACTION_FAILED_STATUS;
            } else if (self::BILLDESK_PENDING_STATUS_CODE === $authStatus) {

                $transaction->status = self::TRANSACTION_PENDING_STATUS;
            } else if (self::BILLDESK_ERROR_STATUS_CODE === $authStatus) {

                $transaction->status = self::TRANSACTION_ERROR_STATUS;
            } else if (self::BILLDESK_TECNICAL_ERROR_STATUS_CODE === $authStatus) {

                $transaction->status = self::TRANSACTION_TECHNICAL_ERROR_STATUS;
            }
            $DB->update_record('billdesk_transaction', $transaction);
        } else {
            print_error('Something went wrong');
        }

        return true;
    }

  

     function get_course_price($course) {
        global $DB;
        
        
        $fields = $DB->get_records_sql('SELECT * FROM {course_info_field} ORDER BY categoryid ASC');
        foreach ($fields as $field) {
            if( trim($field->shortname) == self::FIELD_PRICE_SHORTNAME){
               $price = $DB->get_record('course_info_data',array('courseid'=>$course,'fieldid'=>$field->id));
               if($price){
                   return $price->data;
               }
            }
        }
        return 0;
    }

  
}
