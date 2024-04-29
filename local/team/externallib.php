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

use core_user\external\user_summary_exporter;
use external_api;

use core_competency\api;
class local_team_external extends external_api {
	    /**
     * Returns the description of external function parameters.
     *
     * @return external_function_parameters.
     */
    public static function search_users_parameters() {
        $query = new external_value(
            PARAM_RAW,
            'Query string'
        );
        $capability = new external_value(
            PARAM_RAW,
            'Required capability'
        );
        $limitfrom = new external_value(
            PARAM_INT,
            'Number of records to skip',
            VALUE_DEFAULT,
            0
        );
        $limitnum = new external_value(
            PARAM_RAW,
            'Number of records to fetch',
            VALUE_DEFAULT,
            100
        );

        $departmentid = new external_value(
            PARAM_RAW,
            'departmentid to fetch',
            VALUE_DEFAULT,
            0
        );

        return new external_function_parameters(array(
            'query' => $query,
            'capability' => $capability,
            'limitfrom' => $limitfrom,
            'limitnum' => $limitnum,
            'departmentid' => $departmentid,
        ));
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
    public static function search_users($query, $capability = '', $limitfrom = 0, $limitnum = 100) {
        global $DB, $CFG, $PAGE, $USER;
        $params = self::validate_parameters(self::search_users_parameters(), array(
            'query' => $query,
            'capability' => $capability,
            'limitfrom' => $limitfrom,
            'limitnum' => $limitnum,
            'departmentid' => $departmentid,
        ));

        $request_body = json_decode(file_get_contents('php://input'));
        $departmentid = $request_body[0]->args->departmentid;
        $query = $params['query'];
        $cap = $params['capability'];
        $context = context_system::instance();
        self::validate_context($context);
        $output = $PAGE->get_renderer('tool_lp');

        list($filtercapsql, $filtercapparams) = api::filter_users_with_capability_on_user_context_sql($cap,
            $USER->id, SQL_PARAMS_NAMED);

        $extrasearchfields = array();
        if (!empty($CFG->showuseridentity) && has_capability('moodle/site:viewuseridentity', $context)) {
            $extrasearchfields = explode(',', $CFG->showuseridentity);
        }
        //$fields = \user_picture::fields('u', $extrasearchfields);

        list($wheresql, $whereparams) = users_search_sql($query, 'u', true, $extrasearchfields);
        list($sortsql, $sortparams) = users_order_by_sql('u', $query, $context);

        //$countsql = "SELECT COUNT('x') FROM {user} u WHERE $wheresql AND u.id $filtercapsql";
        //$countparams = $whereparams + $filtercapparams;
		$companyid = iomad::get_my_companyid($context);
		$company = new company($companyid);
		$userlevel = $company->get_userlevel($USER);
		$userhierarchylevel = $userlevel->id;
		if ($departmentid == 0 ) {
		    $departmentid = $userhierarchylevel;
		}
        //$sql = "SELECT $fields FROM {user} u WHERE $wheresql AND u.id $filtercapsql ORDER BY $sortsql";

        $params = $whereparams + $filtercapparams + $sortparams;
    	$params['companyid'] = $companyid;
    	
		$departmentlist = company::get_all_subdepartments($departmentid);
		
        $userlist = array();
  		foreach ($departmentlist as $id => $value) {
  			$params['departmentid'] = $id;
            $departmentusers = self::get_department_users($wheresql,$params);
            $userlist = $userlist + $departmentusers;
        }
       
        $users = array();
        $count = count($userlist);

        foreach ($userlist as $key => $userobj) {
        	$fullname = fullname($userobj);
        	$users[] = array(
        		'userid' => $userobj->userid,
        		'fullname'=> $fullname
        	);
        }

        return array(
        	'count' => $count,
            'users' => $users
            
        );
    }

    /**
     * Returns description of external function result value.
     *
     * @return external_description
     */
    public static function search_users_returns() {
  
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

    public function get_department_users($wheresql,$params) {
        global $DB;	
        $sql = "SELECT cu.userid,u.firstname,u.lastname,cu.id,cu.companyid,cu.managertype,cu.departmentid,cu.suspended FROM {company_users} cu INNER JOIN {user} u ON u.id = cu.userid WHERE cu.departmentid  =:departmentid AND cu.companyid = :companyid AND $wheresql LIMIT 0, 100";
        
        if($departmentusers = $DB->get_records_sql($sql,$params)){
		return $departmentusers;        	
        } else {
            return array();
        }
    }

}