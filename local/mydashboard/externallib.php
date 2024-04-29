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
 * This plugin provides access to Moodle data in form of analytics and reports in real time.
 *
 *
 * @package    local_vedificboard
 * @copyright  2019 VedificBoard
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . '/local/mydashboard/lib.php');
include_once $CFG->dirroot . '/grade/querylib.php';
//require_once($CFG->dirroot . '/grade/querylib.php');
require_once($CFG->libdir . '/gradelib.php');
//use grade_item;
class local_mydashboard_external extends external_api {
            /**
     * Returns the description of external function parameters.
     *
     * @return external_function_parameters.
     */
	public static function user_reward_points_parameters() {
			        return new external_function_parameters(
                array(
			'username' => new external_value(PARAM_USERNAME, 'Student username')
                    )
        );
    }

    /**
     * Search users.
     *
     * @param string $query
     * @param string $capability
     * @param int $limitfrom
     * @param int $limitnum
     * @return array
     */
    public static function user_reward_points($username) {
        require_once 'lib.php';
        global $CFG, $DB, $USER;
        $params = self::validate_parameters(self::user_reward_points_parameters(), array(
                    'username' => $username
        ));
        $result = [];
        $user = $DB->get_record('user', array('username' => $params['username'], 'deleted' => 0));
        if (!$user) {
            $result['error'] = true;
            $result['errormessage'] = 'Invalid username, not found user with this username';
            return $result;
	}
	//print_object($username);
	$output = array();
        $output['fullname'] = $user->firstname . ' ' . $user->lastname;
        $output['email'] = $user->email;
        $output['department'] = $user->department;
        $output['designation'] = $user->institution;
        $output['empcode'] = $user->username;
        $output['phone'] = $user->phone1;
        $usercontext = context_user::instance($user->id);
        $output['profileimage'] = $src = $CFG->wwwroot . "/pluginfile.php/$usercontext->id/user/icon/f1";
        $output['my_rank'] = get_my_rank($user->id);
        $output['available_points'] = get_available_points($user->id);
        $output['total_points'] = get_total_points($user->id);
        $output['redeem_points']  = get_redeemed_points($user->id);
        $output['average_grade'] = get_user_grade($user->id);
        $result['reward_data'][] = $output;

header('Access-Control-Allow-Origin: *');
header("Content-type: application/json; charset=utf-8");
echo json_encode($result);
die;

}
 /**
     * Returns description of external function result value.
     *
     * @return external_description
     */
    public static function user_reward_points_returns() {

        return new external_single_structure(
            array(
                'count' => new external_value(PARAM_RAW, 'count'),
                'users'=> new external_multiple_structure(
                    new external_single_structure(
                        array(
                           'userid' => new external_value(PARAM_RAW, 'userid'),
                           'fullname' => new external_value(PARAM_RAW, 'fullname'),
                        )
                    )
                )
            )
        );
    }

}
      
