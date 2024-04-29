<?php

defined('MOODLE_INTERNAL') || die();

use core_user\output\status_field;

/**
 * Get Top 5 courses per viewed
 */
function get_top_viewed($numdata, $companyid) {
    global $DB, $CFG;

    if (!$numdata or $numdata == get_string('select', 'block_courses_statistics')) {
        $numdata = 5;
    }

    if (!$companyid) {

        $csql = "SELECT 
                    COUNT(l.id) AS `Views`,
                    c.fullname 'Course', 
                    c.id AS 'Courseid'
                FROM {logstore_standard_log} l,
                    {course} c,
                    {role_assignments} ra 
                WHERE

                    c.visible=1 AND c.id<>1
                AND  l.eventname = '\\\core\\\\event\\\course_viewed' 
                AND l.action = 'viewed' 
                AND l.target='course'
                AND l.courseid=c.id 
                AND l.userid = ra.userid 
                AND l.contextid=ra.contextid 
                AND ra.roleid =5 
                GROUP BY l.`courseid`
                ORDER BY count(l.id) DESC ";

                  
       
    } else {

        $csql = "SELECT 
                COUNT(l.id) AS `Views`,
                c.fullname 'Course', 
                c.id AS 'Courseid'
                FROM {logstore_standard_log} l,
                {course} c,
                {role_assignments} ra ,
                {company_course} cc
                WHERE 
                    l.eventname = '\\\core\\\\event\\\course_viewed'
                AND l.action = 'viewed' AND l.target='course' 
                AND l.courseid=c.id AND l.userid = ra.userid 
                AND l.contextid=ra.contextid AND ra.roleid = 5
                AND l.courseid = cc.courseid AND cc.companyid= $companyid "
                . " GROUP BY l "
                . ". `courseid` "
                . " ORDER BY  count(l.id)  DESC ";
        
            
    }

    if (!$usercount = $DB->get_records_sql($csql, null, $limitfrom = 0, $limitnum = $numdata)) {
        $usercount = get_string("none");
    } else {
        $array = array();
        foreach ($usercount as $u) {
            $array[] = $u;
        }

        $usercount = $array;
    }
    return $usercount;
}

/**
 * Get Top 5 courses per enrolled
 */
/**
     * Get Top 5 courses per enrolled
     */
// function get_top_enrolled($numdata, $companyid) {
//         global $DB, $CFG;

        
//         $join   = $whr = '';

//         // if company, get company's top enrolled courses
//         if(!empty($company)){
//             $join   =  " JOIN {company_course} cc ON (cc.courseid=c.id) JOIN {company_users} cu ON (cu.userid = u.id) ";
//             $whr    =  " AND cc.companyid= $company AND cu.companyid= $company ";
//         }

//         $csql = "SELECT "
//                 . " c.fullname AS 'Course', "
//                 . " COUNT(ue.id) AS 'Enrolled' "
//                 . " FROM {course} c  "
//                 . " JOIN {enrol} en ON (en.courseid = c.id) "
//                 . " JOIN {user_enrolments} ue ON (ue.enrolid = en.id) "
//                 . " JOIN {user} u ON (u.id=ue.userid) " 
//                 .   $join
//                 . " WHERE "
//                 . " u.deleted=0 " 
//                 .   $whr
//                 . " GROUP BY c.id "
//                 . " ORDER BY   "
//                 . "  `Enrolled` DESC";

//         if (!$usercount = $DB->get_records_sql($csql, null, $limitfrom = 0, $limitnum = $numdata)) {
//             $usercount = get_string("none");
//         } else {
//             $array = array();
//             foreach ($usercount as $u) {
//                 $array[] = $u;
//             }
//             $usercount = $array;
//         }
//         return $usercount;
//     }
function get_top_enrolled($numdata = 5, $companyid) {
    global $DB, $CFG;    
    $join  = $whr = $csql = '';
 
        if(!empty($companyid)){
            $join   =  " JOIN {company_course} cc ON (cc.courseid=c.id) JOIN {company_users} cu ON (cu.userid = u.id) ";
            $whr    =  " AND cc.companyid= $companyid AND cu.companyid= $companyid ";
        }
       
        $csql = "SELECT "
                . " c.id AS 'Courseid', "
                . " c.fullname AS 'Course', "
                . " COUNT(ue.id) AS 'Enrolled' "
                . " FROM {course} c  "
                . " JOIN {enrol} en ON (en.courseid = c.id) "
                . " JOIN {user_enrolments} ue ON (ue.enrolid = en.id) "
                . " JOIN {user} u ON (u.id=ue.userid) "
                .   $join
                . " WHERE "
                . " u.deleted=0 "
                .   $whr
                . " GROUP BY c.id "
                . " ORDER BY   "
                . "  `Enrolled` DESC";
     
    if (!$usercount = $DB->get_records_sql($csql, null, $limitfrom = 0, $limitnum = $numdata)) {
        $usercount = get_string("none");
    } else {
        $array = array();
        foreach ($usercount as $u) {
            $array[] = $u;
        }
        $usercount = $array;
    }

    return $usercount;
}


function get_users_by_company_cs($count = false) {
    global $DB;
    $company = get_current_editing_company_cs();
    $guest = guest_user();
    if (!empty($company) && $company != '') {
        $sql = " FROM
       {user} u INNER JOIN {company_users} cu ON (cu.companyid = :companyid AND cu.userid = u.id )
       WHERE u.id <> :guestid AND u.deleted = 0 AND u.confirmed = 1 AND u.suspended = 0 ";
        $params = [
            'companyid' => $company->id,
            'guestid' => $guest->id,
        ];
    } else {
        $sql = " FROM
        {user} u
        WHERE u.id <> :guestid AND u.deleted = 0 AND u.confirmed = 1 AND u.suspended = 0 ";
        $params = [
            'guestid' => $guest->id,
        ];
    }
    if ($count) {
        $fields = 'SELECT COUNT(1)';
        return $DB->count_records_sql($fields . $sql, $params);
    } else {
        $fields = 'SELECT u.*';
        return $DB->get_records_sql($fields . $sql, $params);
    }
}

function get_enrolled_users_company_cs() {
    global $DB;
    $users = get_users_by_company_cs();
    $count = 0;
    if (count($users) > 0) {
        foreach ($users as $id => $value) {
            $control = false;
            $sql = 'SELECT E.* FROM {user_enrolments} UE'
                    . ' INNER JOIN {enrol} E ON UE.enrolid = E.id'
                    . ' INNER JOIN {course} C ON C.id = E.courseid'
                    . ' WHERE userid = :uid AND C.visible = 1 AND UE.status = ' . status_field::STATUS_ACTIVE;

            $UE = $DB->get_records_sql($sql, ['uid' => $id]);
            if (!empty($UE)) {
                foreach ($UE as $key => $enrol_instance) {
                    $context = context_course::instance($enrol_instance->courseid);
                    if (is_enrolled($context, $value->id, '', true)) {
                        $count ++;
                        break;
                    }
                }
            }
        }
    }
    return $count;
}

/**
 * In case the current user is_siteadmin() then, will return the current editing company
 * or null of is not editing eny company
 * In case the current user !is_siteadmin() then, will return the company the user belogns to
 *
 * @return stdClass
 */
function get_current_editing_company_cs() {
    global $DB, $USER;

    $context = context_system::instance();
    $roles = get_user_roles($context, $USER->id);
    $companymanager = false;
    $companyid = iomad::is_company_user();

    foreach ($roles as $role) {
        if ($role->shortname == 'companymanager') {
            $companymanager = true;
            break;
        }
    }

    $company = null;
    if ((is_siteadmin() || $companymanager == true ) || (isset($companyid) && !empty($companyid) )) {
        $company = $DB->get_record('company', array(
            'id' => (int) $companyid
        ));
    } else {
        if (isset($USER->company)) {
            $company = $DB->get_record('company', array('id' => (int) $USER->company->id));
        }
    }
    return $company;
}

function get_courses_company_cs() {
    global $DB;
    $company = get_current_editing_company_cs();

    $courses = 0;
    if (empty($company) || $company == '') {
        // Get every course.
        $courses = $DB->count_records_sql('SELECT COUNT(1) FROM mdl_course WHERE id != 1');
    } else {
        // Get the courses belonging to that company only.
        $select = "";
        $sql = "SELECT c.id from {course} c, {company_course} cc WHERE
                cc.companyid=$company->id AND cc.courseid = c.id $select";
        $courses = $DB->get_records_sql($sql);
    }
    return $courses;
}
