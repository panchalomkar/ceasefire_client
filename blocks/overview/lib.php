<?php

defined('MOODLE_INTERNAL') || die();
use core_user\output\status_field;

function get_users_by_company_overview($count = false){
   global $DB;
   $company = get_current_editing_company_overview();
   $guest = guest_user();
   if(!empty($company) && $company != ''){
       $sql = " FROM
       {user} u INNER JOIN {company_users} cu ON (cu.companyid = :companyid AND cu.userid = u.id )
       WHERE u.id <> :guestid AND u.deleted = 0 AND u.confirmed = 1 AND u.suspended = 0 ";
       $params = [
           'companyid' => $company->id,
           'guestid' => $guest->id,
        ];
    }else{
        $sql = " FROM
        {user} u
        WHERE u.id <> :guestid AND u.deleted = 0 AND u.confirmed = 1 AND u.suspended = 0 ";
        $params = [
            'guestid' => $guest->id,
         ];

    }
    if($count){
        $fields = 'SELECT COUNT(1)';
        return $DB->count_records_sql($fields . $sql, $params);
    }else{
        $fields = 'SELECT u.*';
        return $DB->get_records_sql($fields . $sql, $params);
   }
}

function get_enrolled_users_company_overview(){ 
  global $DB; 
    $guest = guest_user(); 
  
    $company = get_current_editing_company_overview();  
    if(!empty($company)){
        $joinsql = " INNER JOIN {company_users} AS cu ON (cu.companyid = :companyid AND cu.userid = u.id )";
    }else{
        $joinsql = '';
    }
  
  $sql = ' FROM {role_assignments} AS r' 
           . ' INNER JOIN {user} AS u on r.userid = u.id' 
           . ' INNER JOIN {role} AS rn on r.roleid = rn.id' 
           . ' INNER JOIN {context} AS ctx on r.contextid = ctx.id' 
           . ' INNER JOIN {course} AS c on ctx.instanceid = c.id' .$joinsql
           . ' WHERE u.id <> :guestid AND rn.shortname = "student" AND u.deleted = 0 AND u.confirmed = 1 AND u.suspended = 0 AND c.visible = 1'; 
        $fields = 'SELECT COUNT(DISTINCT(u.id))'; 
        $params = [ 
          'guestid' => $guest->id, 
          'companyid' => isset($company->id) ? $company->id : 0, 
        ];
    $count =  $DB->count_records_sql($fields . $sql ,$params); 
    return $count; 
}

function get_current_editing_company_overview(){
    global $DB, $USER;

    $context = context_system::instance();
    $roles = get_user_roles($context, $USER->id);
    $companymanager = false;
    $companyid = iomad::is_company_user();
    
    foreach ($roles as $role) {
        if ($role -> shortname == 'companymanager') {
            $companymanager = true;
            break;
        }
    }

    $company = null;
    if((is_siteadmin() || $companymanager == true ) || (isset($companyid) && !empty($companyid) ) ){
        $company = $DB->get_record('company', array(
            'id' => (int)$companyid
        ));
    }
    else{
        if(isset($USER->company)){
            $company = $DB->get_record('company', array('id' => (int)$USER->company->id));
        }
    }
    return $company;
}

function get_courses_company_overview(){
    global $DB;
    $company = get_current_editing_company_overview();
    
    $courses = 0;
    if (empty($company) || $company == '') {
         // Get every course.
         $courses = $DB->count_records_sql('SELECT COUNT(1) FROM mdl_course WHERE id != 1');
    } else {
        // Get the courses belonging to that company only.
        $select = "";
        $sql = "SELECT COUNT(1) from {course} c, {company_course} cc WHERE
                cc.companyid=$company->id AND cc.courseid = c.id $select";
        $courses = $DB->count_records_sql($sql);
    }
    return $courses;
}

function get_count_company_overview(){
    global $DB;
    $organisations = $DB->count_records_sql('SELECT COUNT(1) FROM mdl_company');
    return $organisations;
}
