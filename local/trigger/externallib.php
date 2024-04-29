<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

/**
 * User external functions
 *
 * @package    core_user
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class local_trigger_external extends external_api {

    public static function username_replace_parameters() {
        return new external_function_parameters(
                array(
            'temp_empid' => new external_value(PARAM_TEXT, 'Temp username'),
            'new_empid' => new external_value(PARAM_TEXT, 'Confirm username')
                )
        );
    }

    /**
     * Returns general information about files in the user private files area.
     *
     * @param int $userid Id of the user, default to current user.
     * @return array of warnings and file area information
     * @since Moodle 3.4
     * @throws moodle_exception
     */
    public static function username_replace($temp_empid, $new_empid) {
        global $DB;

        $params = self::validate_parameters(self::username_replace_parameters(), array('temp_empid' => $temp_empid, 'new_empid' => $new_empid));
        $warnings = array();

        $result = array();
        $result['success'] = 'false';

        //check if temp username exists 
        if($user = $DB->get_record('user', array('username' => $temp_empid))){
            //update the username with new username
    
									$update  = new stdClass();

            $update->id = $user->id;
            $update->username = $new_empid;
            $DB->update_record('user', $update);
            $result['success'] = 'true';

        }

        return $result;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.4
     */
    public static function username_replace_returns() {
        return new external_single_structure(
                array(
            'success' => new external_value(PARAM_TEXT, 'Response'),

                )
        );
    }

    public static function redeem_points_parameters() {
        return new external_function_parameters(
                array(
            'empid' => new external_value(PARAM_RAW, ' username'),
            'point' => new external_value(PARAM_INT, 'Point'),
            'point_type' => new external_value(PARAM_TEXT, 'type')
                )
        );
    }

    /**
     * Returns general information about files in the user private files area.
     *
     * @param int $userid Id of the user, default to current user.
     * @return array of warnings and file area information
     * @since Moodle 3.4
     * @throws moodle_exception
     */
    public static function redeem_points($empid, $point, $point_type) {
       global $DB, $CFG;

        require_once("$CFG->dirroot/local/mydashboard/lib.php");

        $params = self::validate_parameters(self::redeem_points_parameters(), array('empid' => $empid, 'point' => $point,
         'point_type' => $point_type));
        $warnings = array();

        $result = array();
        $result['success'] = 'false';
        if ($user = $DB->get_record('user', array('username' => $empid, 'suspended' => 0))) {
            //check ponits
            $userpoints = $DB->get_record('user_points', array('userid' => $user->id));
            if ($point > 0 && $point < $userpoints->available_points) {
                $insert = new stdClass();
                $insert->userid = $user->id;
                $insert->point_type = $point_type;
                $insert->action = 'api_deducted';
                $insert->points = $point;
                $insert->timecreated = time();
                $insert->ip_addr = get_client_ip();
                if ($DB->insert_record('user_points_log', $insert)) {

                    $UPDATE = "UPDATE {user_points} SET available_points = (available_points - $insert->points) WHERE userid = $user->id ";
                    if ($DB->execute($UPDATE)) {
                        $result['success'] = 'true';
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     * @since Moodle 3.4
     */
    public static function redeem_points_returns() {
        return new external_single_structure(
                array(
            'success' => new external_value(PARAM_TEXT, 'Response'),
                )
        );
    }
}

