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
 * Cohort UI related functions and classes.
 *
 * @package    core_team
 * @copyright  2012 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
define('CONFIRMED', 1);
define('PENDING', 0);
define('REJECTED', 2);
require_once($CFG->dirroot . '/local/team/lib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');

/**
 * Cohort assignment candidates
 */
class team_candidate_selector extends user_selector_base {

    protected $cohortid;
    public $maxusersperpage = 100;
    protected $companyid;
    protected $departmentid;

    //public $extrafields = array('phone2');
    public function __construct($name, $options) {
        $this->cohortid = $options['cohortid'];
        $this->companyid = $options['companyid'];
        $this->departmentid = $options['departmentid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $CFG, $DB;
        $companyrec = $DB->get_record('company', array('id' => $this->companyid));
        $company = new company($this->companyid);

        // Get the full company tree as we may need it.
        $topcompanyid = $company->get_topcompanyid();
        $topcompany = new company($topcompanyid);
        $companytree = $topcompany->get_child_companies_recursive();
        $parentcompanies = $company->get_parent_companies_recursive();

        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['companyid'] = $this->companyid;
        $params['cohortid'] = $this->cohortid;


        // Deal with departments.
        $departmentlist = company::get_all_subdepartments($this->departmentid);
        $departmentsql = "";
        if (!empty($departmentlist)) {
            $departmentsql = " AND cu.departmentid IN (" . implode(',', array_keys($departmentlist)) . ")";
        } else {
            $departmentsql = "";
        }

        $fields = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';
        $extrasql = '';
        if ($params['cohortid']) {

            //$teammentor = $DB->get_record('local_team',array('cohortid'=>$params['cohortid']),'mentor');
            //$extrasql = " AND u.id NOT IN ($teammentor->mentor)";

        }    
        $sql = " FROM
	                {user} u INNER JOIN {company_users} cu ON cu.userid = u.id
                WHERE $wherecondition  AND u.suspended = 0 $departmentsql
                    AND
                    cu.companyid = :companyid
                    $userfilter
                    AND u.id NOT IN
                     (SELECT DISTINCT(ltm.userid)
                     FROM {cohort_members} ltm
                     INNER JOIN {local_team} lt
                     ON lt.cohortid = ltm.cohortid WHERE lt.cohortid=:cohortid) $extrasql";

        $order = ' ORDER BY u.lastname ASC, u.firstname ASC';
        // die;
        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, $params);

        if ($params['cohortid']) {
           // $mentorids = $DB->get_record_sql("SELECT mentor FROM {local_team} WHERE cohortid = ".$params['cohortid']);
           // $sql = " FROM {user} u WHERE u.id IN ($mentorids->mentor)";
            //$mentors = $DB->get_records_sql($fields . $sql,null);
            $groupname = get_string('currentusers', 'local_team');
        }
        
        if (empty($availableusers)) {
            return array();
        }

        if ($search) {
            $groupname = get_string('potentialcourseusersmatching', 'block_iomad_company_admin', $search);
        } else {
            $groupname = get_string('potentialcourseusers', 'block_iomad_company_admin');
        }
           
        return array($groupname => $availableusers);
        
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['cohortid'] = $this->cohortid;
        $options['departmentid'] = $this->departmentid;
        $options['companyid']= $this->companyid;
        $options['file'] = 'local/team/locallib.php';
        return $options;
    }

    protected function output_optgroup($groupname, $users, $select) {
        if (!empty($users)) {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . ' (' . count($users) . ')">' . "\n";
            //print_object($users);
            foreach ($users as $user) {
                //print_object($user);
                $attributes = '';
                // $class = $user->confirm? 'fa-check':'fa-cross';
                //$userstatus = $user->confirm?get_string('confirm','local_team'):get_string('pending','local_team');
                //$showstatus = '<span class="fa '.$class.'">'.$userstatus.'</span>';
                if (!empty($user->disabled)) {
                    $attributes .= ' disabled="disabled"';
                } else if ($select || isset($this->selected[$user->id])) {
                    $attributes .= ' selected="selected"';
                }
                unset($this->selected[$user->id]);

                /* $output .= '    <option' . $attributes . ' value="' . $user->id . '">' .
                  $this->output_user($user) .$showstatus. "</option>\n";
                 */
                $output .= '    <option' . $attributes . ' value="' . $user->id . '">' .
                        fullname($user) . "</option>\n";
                if (!empty($user->infobelow)) {
                    // Poor man's indent  here is because CSS styles do not work in select options, except in Firefox.
                    $output .= '    <option disabled="disabled" class="userselector-infobelow">' .
                            '&nbsp;&nbsp;&nbsp;&nbsp;' . s($user->infobelow) . '</option>';
                }
            }
        } else {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . '">' . "\n";
            $output .= '    <option disabled="disabled">&nbsp;</option>' . "\n";
        }
        $output .= "  </optgroup>\n";
        return $output;
    }

    public function output_user($user) {
        $out = fullname($user);
        return $out;
    }

}

/**
 * Cohort assignment candidates
 */
class team_existing_selector extends user_selector_base {

    protected $cohortid;

    public function __construct($name, $options) {
        $this->cohortid = $options['cohortid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        $mentors = array();
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['teamid'] = $this->cohortid;

        $fields = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                 JOIN {cohort_members} ltm ON (ltm.userid = u.id AND ltm.cohortid = :teamid)
                 WHERE $wherecondition AND u.deleted=0";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));
        
        if ($params['teamid']) {
           // $mentorids = $DB->get_record_sql("SELECT mentor FROM {local_team} WHERE cohortid = ".$params['teamid']);
//            $team_mentors = $DB->get_records_sql("SELECT mentors.* FROM (SELECT * FROM {user} WHERE id IN ($mentorids->mentor)) as mentors WHERE mentors.deleted=0");
//            
//            foreach($team_mentors as $teammentor){
//                $mids[] = $teammentor->id;
//            }
            
            //$sql = " FROM {user} u WHERE u.id IN (".implode(",", $mids).")";
            //$mentors = $DB->get_records_sql($fields . $sql,null);
            $groupname = get_string('currentusers', 'local_team');
        }
        
//        foreach($mentors as $mentor){
//            if(isset($availableusers[$mentor->id])){
//                unset($availableusers[$mentor->id]);
//            }
//        }


        if ($search) {
            $groupname = get_string('currentusersmatching', 'local_team', $search);
        } else {
            $groupname = get_string('currentusers', 'local_team');
           // $studentlabel = get_string('student', 'local_team');
           // $mentorlabel = get_string('mentor', 'local_team');
        }
        
        //return array($studentlabel=>$availableusers);
        return array($groupname => array_merge($availableusers));
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['cohortid'] = $this->cohortid;
        $options['file'] = 'local/team/locallib.php';
        return $options;
    }

    protected function output_optgroup($groupname, $users, $select) {
        global $DB;
        if (!empty($users)) {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . ' (' . count($users) . ')">' . "\n";
            //print_object($cohort);
            foreach ($users as $user) {

                $attributes = '';
                if (!empty($user->disabled)) {
                    $attributes .= ' disabled="disabled"';
                } else if ($select || isset($this->selected[$user->id])) {
                    $attributes .= ' selected="selected"';
                }
//                if($groupname == 'Mentor'){
//                     $attributes .= ' disabled="disabled"';
//                }
                unset($this->selected[$user->id]);

                /* $output .= '    <option' . $attributes . ' value="' . $user->id . '">' .
                  $this->output_user($user) .$showstatus. "</option>\n";
                 */
                $output .= '    <option' . $attributes . ' value="' . $user->id . '">' .
                        fullname($user) . "</option>\n";
                if (!empty($user->infobelow)) {
                    // Poor man's indent  here is because CSS styles do not work in select options, except in Firefox.
                    $output .= '    <option disabled="disabled" class="userselector-infobelow">' .
                            '&nbsp;&nbsp;&nbsp;&nbsp;' . s($user->infobelow) . '</option>';
                }
            }
        } else {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . '">' . "\n";
            $output .= '    <option disabled="disabled">&nbsp;</option>' . "\n";
        }
        $output .= "  </optgroup>\n";
        return $output;
    }

    public function output_user($user) {
        $out = fullname($user);
        return $out;
    }

}

class team_existing_list extends user_selector_base {

    protected $cohortid;

    public function __construct($name, $options) {
        $this->cohortid = $options['cohortid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['teamid'] = $this->cohortid;

        $fields = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                 JOIN {cohort_members} ltm ON (ltm.userid = u.id AND ltm.cohortid = :teamid)
                WHERE $wherecondition AND u.deleted=0";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('currentusersmatching', 'local_team', $search);
        } else {
            $groupname = get_string('currentusers', 'local_team');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['cohortid'] = $this->cohortid;
        $options['file'] = 'local/team/locallib.php';
        return $options;
    }

    protected function output_optgroup($groupname, $users, $select) {
        if (!empty($users)) {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . ' (' . count($users) . ')">' . "\n";
            foreach ($users as $user) {
                //print_object($user);
                $attributes = '';
                //$class = $user->confirm? 'fa-check':'fa-cross';
                //$userstatus = $user->confirm ? get_string('confirm', 'local_team') : get_string('pending', 'local_team');
                //$showstatus = get_status($user->confirm);
                $showstatus = '';
                if (!empty($user->disabled)) {
                    $attributes .= ' disabled="disabled"';
                } else if ($select || isset($this->selected[$user->id])) {
                    $attributes .= ' selected="selected"';
                }
                unset($this->selected[$user->id]);

                /* $output .= '    <option' . $attributes . ' value="' . $user->id . '">' .
                  $this->output_user($user) .$showstatus. "</option>\n";
                 */
                $output .= '    <option' . $attributes . ' value="' . $user->id . '">' .
                        fullname($user) . "</option>\n";
                if (!empty($user->infobelow)) {
                    // Poor man's indent  here is because CSS styles do not work in select options, except in Firefox.
                    $output .= '    <option disabled="disabled" class="userselector-infobelow">' .
                            '&nbsp;&nbsp;&nbsp;&nbsp;' . s($user->infobelow) . '</option>';
                }
            }
        } else {
            $output = '  <optgroup label="' . htmlspecialchars($groupname) . '">' . "\n";
            $output .= '    <option disabled="disabled">&nbsp;</option>' . "\n";
        }
        $output .= "  </optgroup>\n";
        return $output;
    }

    public function output_user($user) {
        $out = fullname($user);
        return $out;
    }

}

function team_member_added(\core\event\cohort_member_added $event) {
    global $DB, $USER;

    $eventdata = $event->get_record_snapshot('cohort', $event->objectid);
    $team = $DB->get_record('local_team', array('cohortid' => $eventdata->id));
    if ($team) {
        team_add_member($team->id, $event->relateduserid, CONFIRMED);
    }
}

function team_removed(\core\event\cohort_deleted $event) {
    global $DB, $USER;
    $eventdata = $event->get_record_snapshot('cohort', $event->objectid);
    $team = $DB->get_record('local_team', array('cohortid' => $eventdata->id));
    if ($team) {
        team_delete_team($eventdata->id);
    }
}

function team_member_removed(\core\event\cohort_member_removed $event) {
    global $DB, $USER;
    $eventdata = $event->get_record_snapshot('cohort', $event->objectid);
    $team = $DB->get_record('local_team', array('cohortid' => $eventdata->id));
    if ($team) {
        team_remove_member($team->id, $event->relateduserid);
    }
}

function send_team_notification($eventtype, $message, $subject, $userfrom, $userto) {

    $eventdata = new \core\message\message();
    $eventdata->courseid = SITEID;
    $eventdata->modulename = 'team';
    $eventdata->userfrom = $userfrom;
    $eventdata->userto = $userto;
    $eventdata->subject = $subject;
    $eventdata->fullmessage = $message;
    $eventdata->fullmessageformat = FORMAT_HTML;
    $eventdata->fullmessagehtml = $message;
    $eventdata->smallmessage = $subject;

    $eventdata->name = $eventtype;
    $eventdata->component = 'local_team';
    $eventdata->notification = 1;
    //$eventdata->contexturl      = $info->url;
    //$eventdata->contexturlname  = $info->assignment;*/

    message_send($eventdata);
}

function get_status($status) {

    switch ($status) {
        case CONFIRMED:
            $status = get_string('confirm', 'local_team');
            break;
        case PENDING:
            $status = get_string('pending', 'local_team');
            break;
        case REJECTED:
            $status = get_string('reject', 'local_team');
            break;
        default:
            $status = get_string('pending', 'local_team');
            break;
    }
    return $status;
}
