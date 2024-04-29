<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A scheduled task for CAS user sync.
 *
 * @package    task
 * @copyright  2015 Vadim Dvorovenko <Vadimon@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mydashboard\task;

use \stdClass;

class usersync_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('syncuser', 'local_mydashboard');
    }

    /**
     * Run users sync.
     */
    public function execute() {
        global $DB, $CFG;

        include_once $CFG->dirroot . '/user/lib.php';
        $url = "https://ccrm.api.ceasefire.biz/ccrm/api/rest/Account/GetEmployeeHierarchy?tocken=Pq7ZIURbzmhYsOSK97qUBQ&EmpCode=All";
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $server_output = curl_exec($ch);
        curl_close($ch);
        $responses = (object) json_decode($server_output);
        $count = 1;

        foreach ($responses->Data as $resp) {
//            if ($count > 50) {
//                break;
//            }
            $count++;
            $resp = (object) $resp;

            $fullname = trim($resp->EmpName);
            $array = explode(' ', $fullname, 2);
            $firstname = $array[0];
            $lastname = '.';
            if ($array[1] != '') {
                $lastname = $array[1];
            }

            $record = new stdClass();
            $record->confirmed = '1';
            $record->mnethostid = '1';
            $record->timecreated = time();

            $record->firstname = $firstname;
            $record->lastname = $lastname;
            $record->username = strtolower(trim($resp->UserName));
            $record->phone1 = trim($resp->PhoneNo);
            $record->email = trim($resp->EmailId);
            $record->department = trim($resp->GlobalDimension2Code);
            $record->password = hash_internal_user_password('Wel@#93Cme2');

            //make custom fields
            $fields = array(
                'jobcode' => trim($resp->JobTitleCode),
                'level' => trim($resp->Level),
                'repotingto' => strtolower(trim($resp->ReportingManagerID)),
                'locationid' => trim($resp->LocationId),
                'department' => $resp->GlobalDimension2Code,
                'division' => trim($resp->DivisionCode),
                'subdivision' => trim($resp->SubDivisionCode),
                'branchname' => trim($resp->BranchName),
                'branchcode' => trim($resp->BranchCode),
                'doj' => strtotime($resp->DOJ),
                'doa' => strtotime($resp->DOA),
                'dob' => strtotime($resp->DOB),
                'spouse' => trim($resp->SpouseName),
                'designation' => trim($resp->Designation),
                'gender' => trim($resp->Gender),
                'inactivedate' => $resp->InactiveDate,
                'status' => ($resp->IsActive)?'Active':'In-active'
            );

            //check if user exists
            if (!$DB->record_exists('user', array('username' => $resp->UserName))) {
               /* if ($userid = user_create_user($record, false, true)) {

                    //insert the custom data record in database
                    foreach ($fields as $key => $data) {
                        if ($c = $DB->get_record('user_info_field', array('shortname' => $key))) {
                            $custom_object = new stdClass();
                            $custom_object->userid = $userid;
                            $custom_object->fieldid = $c->id;
                            $custom_object->data = $data;
                            $DB->insert_record('user_info_data', $custom_object);
                        }
                    }
                }*/
            } else {
                $user = $DB->get_record('user', array('username' => $resp->UserName));

                $record->id = $user->id;
                if ($resp->IsActive == false) {
                    $record->suspended = 1;
                }
                user_update_user($record, false, true);

                //update the fields

                foreach ($fields as $key => $data) {
                    if ($c = $DB->get_record('user_info_field', array('shortname' => $key))) {
                        $custom_object = new stdClass();
                        $custom_object->userid = $user->id;
                        $custom_object->fieldid = $c->id;
                        $custom_object->data = $data;
                        if ($DB->record_exists('user_info_data', array('userid' => $user->id, 'fieldid' => $c->id))) {
                            $record = $DB->get_record('user_info_data', array('userid' => $user->id, 'fieldid' => $c->id));
                            //Update record
                            $custom_object->id = $record->id;
                            $DB->update_record('user_info_data', $custom_object);
                        } else {
                            //insert record
                            $DB->insert_record('user_info_data', $custom_object);
                        }
                    }
                }
            }
        }
    }

}
