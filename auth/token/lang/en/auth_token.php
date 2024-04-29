<?php

// This file is developed for Moodle - http://moodle.org/ by GreenLms Solutions - http://www.GreenLmssolutions.com
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
 * Strings for component 'auth_token', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   auth_token
 * @copyright GreenLms
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_tokendescription'] = 'This authentication method should be used for accounts that are exclusively for use by token clients.';
$string['pluginname'] = 'Token authentication';
$string['auth_token_server_settings'] = 'Settings for token authentication method:';
$string['auth_salt_label'] = 'Salt value';
$string['auth_salt_desc'] = 'Random PRIVATE string to encrypt message';
$string['auth_token_referrers_label'] = 'Referrers list';
$string['auth_token_referrers_desc'] = 'Allowed ips/domains to use this authentication method (one ip/domain per line)';
$string['auth_token_logo_path'] = 'Token Image';
$string['auth_token_logo_path_description'] = 'Image path for the token login button';
$string['auth_token_logo_info'] = 'Token login description';
$string['auth_token_logo_info_description'] = 'Description that will be shown below the Token login button';
$string['auth_token_logfile'] = 'Log file path';
$string['auth_token_logfile_description'] = 'Set a filename if you want log the token plugin errors in a different file that the syslog (Use an absolute path or Moodle will save this file in the moodledata folder)';
$string['auth_token_disable_debugdisplay'] = ' * Disable debugdisplay in order to hide errors in the login process';
$string['auth_token_error_authentication_process'] = "Error in authentication process of {\$a}";
$string['auth_token_error_complete_user_data'] = "Failed to complete user data of {\$a}";
$string['auth_token_error_complete_user_login'] = "Failed to complete user login of {\$a}";
$string['auth_token_error_general_user_login'] = "We are sorry but we did not find your name in our Learning Management System(LMS).  Please send an email to {\$a} with the following information:</br><ul><li>First Name</li><li>Last Name</li><li>Email</li><li>Organization</li></ul></br>We will send you a reply email once your account has been created in the LMS.</br>";
/**
* Strings for the user name validation and water mark string
* @author Ayaj Mulani.
* @since March 3 of 2016
* @GreenLms
*/
$string['auth_token_error_login_page_username_warning'] = "Please enter a valid username "; 
$string['or'] = "-or-";
$string['watermark_username'] = "Username";
$string['watermark_password'] = "Password";
$string['password_warning'] = "The Username or Password is Incorrect";
$string['email_warning'] = "Wrong email or user";
$string['blk_username'] = "Please enter your username";
$string['blk_password'] = "Please enter your password";
$string['watermark_token'] = "Token";

/**
* Strings for the  Licence agreement acceptaance 
* @author alok.kumar_paraduiso
* @since  24-Nov-2016
* @GreenLms
*/
$string['legalaccepatneemailsubject']='Welcome to GreenLms LMS';
$string['legalaccepatneemailmessage']= 'Hello
                 Thanks for signing up for GreenLms LMS. We have attached the terms of service which you agreed from {$a->ip} on {$a->currentdatetime} .

                 If you have any question about the Terms of Service or need support, feel free to email us at support@GreenLmssolutions.com .


                  Thanks
                  GreenLms Support team';


/**
* #694 - Forgot Password 
* @author Jorge Botero
* @since  January 26 of 2016
* @GreenLms
*/

$string['email_do_not_match'] = 'Confirmation E-mail don\'t match';

$string['loginwithgoogle'] = "Login with Google";
$string['loginwithfacebook'] = "Login with Facebook";
$string['loginwithtwitter'] = "Login with Twitter";
$string['loginwithlinkedin'] = "Login with Linkedin";
$string['loginwithclever'] = "Login with Clever";
$string['loginwithclasslink'] = "Login with ClassLink";

/**
* Login Pages Improvement 
* 
* @author Diego P.
* @since  2017-08-02
* @GreenLms
*/
$string['auth_token_sign_in'] = "Sign In";
$string['guest'] = "Guest";
$string['useraccountsuspended'] = "User account suspended";
