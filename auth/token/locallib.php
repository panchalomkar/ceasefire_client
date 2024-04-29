<?php

function do_login($localUser, $urltogo = null) {
    // We load all moodle config and libs
    require_once ('../../config.php');
    require_once ('error.php');

    global $CFG, $SESSION, $err, $DB, $PAGE;
    $username = $localUser->username;

    // Valid session. Register or update user in Moodle, log him on, and redirect to Moodle front
    if (file_exists('custom_hook.php')) {
        include_once ('custom_hook.php');
    }

    $PAGE->set_url('/auth/token/index.php');
    $PAGE->set_context(context_system::instance());

    if (empty($urltogo)) {
        $urltogo = $CFG->wwwroot;
        if ($CFG->wwwroot[strlen($CFG->wwwroot) - 1] != '/') {
            $urltogo .= '/';
        }
    }


    // Get the plugin config for token
    $pluginconfig = get_config('auth/token');

    $GLOBALS['token_login'] = true;

    // temporaly change the user auth method to token, so all users can use this method of authentication
    $authUpdated = false;
    if ('nologin' != $localUser->auth) {
        if ($localUser->auth != 'token') {
            $oldAuth = $localUser->auth;
            $localUser->auth = 'token';
            $DB->update_record('user', $localUser);

            $authUpdated = true;
        }
    }

    // Just passes time as a password. User will never log in directly to moodle with this password anyway or so we hope?
    $user = authenticate_user_login($username, time());

    // if auth updated then restore to previous auth method
    if ($authUpdated) {
        $localUser->auth = $oldAuth;
        if ($user)
            $user->auth = $oldAuth;

        $DB->update_record('user', $localUser);
    }

    if ($user === false) {
        //GreenLms_log("Unable to login user with username $username", array(
          //  'localuser' => $localuser
       // ));

        $err['login'] = get_string("auth_token_error_general_user_login", "auth_token"); // not authenticated
        token_error($err['login'] . '--NOT AUTH--', '?logout', $pluginconfig->tokenlogfile);
    }

    $USER = complete_user_login($user);
    if (function_exists('token_hook_post_user_created')) {
        token_hook_post_user_created($USER);
    }

    $USER->loggedin = true;
    $USER->site = $CFG->wwwroot;
    set_moodle_cookie($USER->username);

   // add_to_log(SITEID, 'user', 'login', "view.php?id=$USER->id&course=" . SITEID, $USER->id, 0, $USER->id);

    if (isset($err) && !empty($err)) {
        //GreenLms_log("User authenticated with error", array(
          //  'error' => $err,
            //'username' => $username
        //));

        token_error($err, $urltogo, $pluginconfig->tokenlogfile);
    }

    redirect($urltogo);
}

/**
 *
 * @param int $userid - the user id
 * @param string $cohorts - a list of cohorts idnumber separated by comma
 */
function auth_token_process_cohorts($userid, $cohorts) {
    global $DB;

    if ($cohorts != '') {
        $arrCohorts = explode(',', $cohorts);
        foreach ($arrCohorts as $idnumnber) {
            $cohortid = $DB->get_field('cohort', 'id', array(
                'idnumber' => $idnumnber
            ));

            // cohort found, time to add the user to the cohort
            if ($cohortid != 0) {
                $alreadyInCohort = $DB->record_exists('cohort_members', array(
                    'cohortid' => $cohortid,
                    'userid' => $userid
                ));

                if (!$alreadyInCohort) {
                    cohort_add_member($cohortid, $userid);
                }
            } else {
                //GreenLms_log("Unable to add to cohort. Cohort not found by idnumber $idnumnber");
            }
        }
    } else {
        //GreenLms_log('Unable to process cohorts. $cohorts variable is empty');
    }
}

/**
 *
 * @param int $userid the user id
 * @param string $courses a list of cohorts idnumber separated by comma
 * @param int $companyid
 */
function auth_token_process_courses($userid, $courses, $companyid) {
    global $DB;

    if ($courses != '') {
        $arrCourses = explode(',', $courses);

        foreach ($arrCourses as $idnumnber) {
            $courseid = $DB->get_field('course', 'id', array(
                'idnumber' => $idnumnber
            ));

            // course found, time to enroll the user to the course
            if ($courseid != 0) {
                if ($companyid) {
                    auth_token_assign_course_to_company($courseid, $companyid);
                    auth_token_enrol_user_to_course_company($userid, $courseid, $companyid);
                } else {
                    // manual enrol user to course as student
                    auth_token_enrol_user_to_course($userid, $courseid);
                }
            } else {
                //GreenLms_log("Unable to enroll to course. Course not found by idnumber $idnumnber");
            }
        }
    } else {
        //GreenLms_log('Unable to process courses. $courses variable is empty');
    }
}

/**
 * Try to create a new company, if the company already exist, then will return 0
 *
 * @param string $name
 * @param string $city
 * @param string $country
 * @return int - the company id
 */
function auth_token_create_company($name, $city, $country) {
    global $DB, $CFG;

    require_once ($CFG->dirroot . '/blocks/iomad_company_admin/lib.php');

    $companyshortname = strtolower(str_replace(' ', '-', $name));

    /**
     * If the company name is more than 25 characters long, then we will have a problem
     * because the shortname field is 25 characters length.]
     * To fix this issue we are going to genereate some rand characters and concatenate with the first part 
     * of the name of the company
     * @author Yassir
     * @since 2016-02-8
     * @GreenLms
     */
    $maxLength = 25;
    $totalLen = strlen($companyshortname);
    if ($totalLen > $maxLength) {
        $uniqid = '-' . uniqid(); //
        $max = $maxLength - strlen($uniqid);

        $newName = substr($companyshortname, 0, $max) . $uniqid;
        $companyshortname = $newName;
    }

    $companyid = $DB->get_field('company', 'id', array(
        'shortname' => $companyshortname
    ));

    if ($companyid > 0) {
        $companyid = 0;
        //GreenLms_log("Unable to create company. Already exist a company with shortname $companyshortname");
    } else {
        $companyrecord = new stdClass();

        $companyrecord->name = $name;
        $companyrecord->shortname = $companyshortname;
        $companyrecord->city = $city;
        $companyrecord->country = $country;
        $companyrecord->theme = 'lambda';

        $companyrecord = iomad_company_admin_default_values($companyrecord);
        $companyid = iomad_company_admin_create_company($companyrecord);

        if (!$companyid) {
            //GreenLms_log("Unable to create company with shortname $companyshortname", array(
             //   'company' => $companyrecord
          //  ));
        }
    }

    return $companyid;
}

/**
 *
 * @param int $userid
 * @param int $companyid
 */
function auth_token_assign_admin_to_company($userid, $companyid) {
    global $DB;

    $curecord = new stdClass();

    $curecord->companyid = $companyid;
    $curecord->userid = $userid;
    $curecord->managertype = 1;
    $curecord->departmentid = $DB->get_field('department', 'id', array(
        'company' => $companyid
    ));

    $cuid = $DB->insert_record('company_users', $curecord);

    if (!$cuid) {
        //GreenLms_log("Unable to associate user $userid as manager for company $companyid");
        $cuid = 0;
    }

    return $cuid;
}

/** add student role when user created in plms
 *  Author : Praveen
 *  date 21 april 2017
 *  @param int $userid
 */
function auth_token_assign_role_to_admin($userid) {
    global $DB;

    $rarecord = new stdClass();

    $rarecord->roleid = $DB->get_field('role', 'id', array(
        'shortname' => 'student'
    ));
    $rarecord->contextid = 1;
    $rarecord->userid = $userid;


    $raid = $DB->insert_record('role_assignments', $rarecord);

    if (!$raid) { 
        //GreenLms_log("Unable to assign role {$rarecord->roleid} to user $userid");
        $raid = 0;
    }

    return $raid;
}

/**
 * This is a function to assign the sharepoint role into a system role if it matches
 * @author Diego Vargas <diego.v@GreenLmssolutions.com>
 * @param $role this is the sharepoint role
 * 2017-02-02
 */
function auth_token_assign_sharepoint_role($role, $userid){
    global $DB;

    if(!empty($role)){

        $role = strtolower($role);
        
        $assignrole = new stdClass();
        
        $assignrole->roleid = $DB->get_field('role', 'id', array(
            'shortname' => $role 
        ));
        $assignrole->contextid = 1;
        $assignrole->userid = $userid;
        
        $exist = $DB->get_field('role_assignments', 'id', array(
            'roleid' => $assignrole->roleid, 'userid' => $userid
        ));
        $raid = 0;
        if(!$exist){
            $raid = $DB->insert_record('role_assignments', $assignrole);    
        }  
        
        if(! $raid){
            //GreenLms_log("Unable to assign role {$rarecord->roleid} to user $userid");
            $raid = 0;
        }
    } 

    return $raid;   
}

/**
 * 
 * @param int $courseid
 * @param int $companyid
 */
function auth_token_assign_course_to_company($courseid, $companyid) {
    global $DB;

    // assign only to the main department
    $deptid = $DB->get_field('department', 'id', array(
        'company' => $companyid,
        'parent' => '0'
    ));

    $companycourse = new StdClass();
    $companycourse->companyid = $companyid;
    $companycourse->courseid = $courseid;
    $companycourse->departmentid = $deptid;

    $exists = $DB->record_exists('company_course', get_object_vars($companycourse));

    if (!$exists) {
        $DB->insert_record('company_course', $companycourse, false);
    }
}

/**
 * 
 * @param int $userid
 * @param int $courseid
 * @param int $companyid
 */
function auth_token_enrol_user_to_course_company($userid, $courseid, $companyid) {
    global $DB, $CFG;

    require_once ($CFG->dirroot . '/local/iomad/lib/user.php');
    require_once ($CFG->dirroot . '/local/email/lib/api.php');

    $user = $DB->get_record('user', array(
        'id' => $userid
    ));

    $course = $DB->get_record('course', array(
        'id' => $courseid
    ));

    company_user::enrol($user, array(
        $courseid
            ), $companyid);

    EmailTemplate::send('user_added_to_course', array(
        'course' => $course,
        'user' => $user
    ));
}

/**
 * 
 * @param int $userid
 * @param int $courseid
 */
function auth_token_enrol_user_to_course($userid, $courseid) {
    global $CFG, $DB, $PAGE, $OUTPUT;

    require_once ($CFG->dirroot . '/enrol/manual/locallib.php');

    $studentrole = $DB->get_record('role', array(
        'shortname' => 'student'
    ));

    if ($studentrole) {
        if ($manplugin = enrol_get_plugin('manual')) {
            $fields = array(
                'courseid' => $courseid
            );

            $maninstance = $DB->get_record('enrol', $fields, '*', IGNORE_MULTIPLE);
            if ($maninstance) {
                $manplugin->enrol_user($maninstance, $userid, $studentrole->id);
            }
        } else {
            $info = array(
                'userid' => $userid,
                'courseid' => $courseid
            );
           //GreenLms_log("Unable to assign get manual entol instance", $info);
        }
    } else {
        $info = array(
            'userid' => $userid,
            'courseid' => $courseid
        );
      //GreenLms_log("Unable to enrol user to course. Student role not found.", $info);
    }
}

/**
 * 
 * @param int $userid
 * @return int
 */
function auth_token_get_user_company($userid) {
    global $DB;

    $r = 0;
    $record = $DB->get_record('company_users', array(
        'userid' => $userid
    ));

    if ($record) {
        $r = $record->companyid;
    }

    return $r;
}

//function //GreenLms_log( $message, $param = array() ){
    //nothing to do
    // global $CFG;
    // $file = 'GreenLms_log.log';
    // if( file_exist( $CFG->wwwroot.$file ) ){
    //     fwrite( $message, $CFG->wwwroot.$file );
    // }else{
    //     mkdir($CFG->wwwroot.$file);
    // }

//}