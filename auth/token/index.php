<?php
/**
 * Use this file to do SSO, enrol user to courses, cohorts, etc
 * @GreenLms
 *
 * @author GreenLms Solutions LLC
 */
define('TOKEN_INTERNAL', 1);

require_once ('../../config.php');
require_once ('error.php');
require_once ('locallib.php');

global $CFG, $USER, $SESSION, $err, $DB, $PAGE;
//https://phoenix-demobeta.GreenLmslms.net/auth/token/index.php?user=sfbigdemo%40gmail.com&token=P4radi50%21&ts=1560931787&email=sales%40GreenLmssolutions.com&newuser=1&fn=John&ln=Smith&city=&country=&company=&cohorts=&courses=&address=&role=
$username   = required_param('user', PARAM_TEXT);
$token      = required_param('token', PARAM_TEXT);
$timestamp  = required_param('ts', PARAM_INT);
$email      = required_param('email', PARAM_TEXT);
$firstName  = required_param('fn', PARAM_TEXT);
$lastName   = required_param('ln', PARAM_TEXT);
$city       = required_param('city', PARAM_TEXT);
$country    = required_param('country', PARAM_TEXT);

// This will brin a shortname role parameter from third party platform in order to match with one of the plms role
$role = optional_param('role', '', PARAM_TEXT);

// if company is set a multitenant company will be created
// and the user will be added as company manager
$company = optional_param('company', '', PARAM_TEXT);

// New param for sets a user for a specific tenant
$company_sync = optional_param('company_sync', '', PARAM_BOOL);

// if courses is set then the user will be enrolled to the courses.
// Courses can be comma separated and each value have to match the course.idnumber column
$courses = optional_param('courses', '', PARAM_TEXT);

// if cohorts is set the user will be added to the cohorts.
// Cohorts can be comma separated and each value have to match the cohort.idnumber column
$cohorts = optional_param('cohorts', '', PARAM_TEXT);

// This value is used to try to create a new user
// If not set or the value = 0 then the script will only do SSO
#$newUser = optional_param('newuser', false, PARAM_BOOL);

$PAGE->set_url('/auth/token/index.php');
$context = context_system::instance();
$PAGE->set_context($context);

if(isset($_GET['user']) && isset($_GET['token']) && isset($_GET['ts']) && isset($_GET['email']))
{
    $pluginConfig = get_config('auth/token');
    $secretSalt = $pluginConfig->salt;
    $localToken = crypt($timestamp . $username . $email, $secretSalt);
    
    if($localToken == $token){
        $current_time =time();
        if($current_time <= $timestamp + (30 * 60)){
            $localUser = $DB->get_record('user', array(
                'username' => $username 
            ));
            
            if( !$localUser ){
                $localUser = $DB->get_record('user', array(
                    'email' => $email 
                ));
            }
            
            // the user exists so lets process to login
            if( !empty($localUser) ){
                // get user company
                $companyid = auth_token_get_user_company($localUser->id);

                if( !empty($cohorts) )
                        auth_token_process_cohorts($localUser->id, $cohorts);

                if( !empty($courses) )
                    auth_token_process_courses($localUser->id, $courses, $companyid);

                // This will add role if it matches with any lms role (sharepoint integration)
                //auth_token_assign_sharepoint_role($role, $localUser->id);
                do_login($localUser);
            } else {
                $tokenAuth = get_auth_plugin('token');
                $USER->username = $username;
                $USER->email = $email;
                $USER->auth = 'token';
                $USER->password = $username;
                $USER->deleted = 0;
                $USER->confirmed = 1;
                $USER->firstname = $firstName;
                $USER->lastname = $lastName;
                $USER->city = $city;
                $USER->country = $country;
                $USER->timecreated = $timestamp;
                
                $created = $tokenAuth->user_signup($USER, false);
            }
        }else{
            echo 'Cannot do SSO. Timeout';
            $err['login'] = get_string("auth_token_error_general_user_login", "auth_token" , $CFG->supportemail); // timeout
            token_error($err['login'] . '--TIMEOUT--', '?logout', $pluginConfig->tokenlogfile);
        }
    }else{
        echo 'Cannot do SSO. Token does not macth';
    }
}else{
    echo 'Cannot do SSO. Missing parametters';
    
    $err['login'] = get_string("auth_token_error_general_user_login", "auth_token" , $CFG->supportemail); // wrong token
    token_error($err['login'] . '--WRONG TOKEN--', '?logout');
}

session_write_close();
redirect($CFG->wwwroot);
