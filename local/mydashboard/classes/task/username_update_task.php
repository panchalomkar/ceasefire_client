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

class username_update_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('username_update', 'local_mydashboard');
    }

    /**
     * Run users sync.
     */
    public function execute() {
        global $DB, $CFG;

        include_once $CFG->dirroot . '/user/lib.php';

        //get users
        $users = $DB->get_records('user');
        foreach ($users as $user) {

            $url = "https://ccrm.api.ceasefire.biz/ccrm/api/rest/Account/GetEmployeeCodebyTempCode?tocken=Pq7ZIURbzmhYsOSK97qUBQ&TempEmpCode=$user->username";
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            $server_output = curl_exec($ch);
            curl_close($ch);
            $responses = (object) json_decode($server_output);

            if (count($responses->Data) > 0) {
                $resp = (object) $responses->Data;
                if ($rec = $DB->get_record('user', array('username' => $resp->Tmp_Code))) {
                    $update = new stdClass();
                    $update->id = $rec->id;
                    $update->username = strtolower(trim($resp->Emp_Code));

                    $DB->update_record('user', $update);
                }
            }
        }
    }

}
