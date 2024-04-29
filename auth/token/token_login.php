<?php
define('TOKEN_INTERNAL', 1);

require_once ('../../config.php');
require_once ('error.php');
require_once ('../../cohort/lib.php');

global $CFG, $USER, $SESSION, $err, $DB, $PAGE;

$username = required_param('username', PARAM_TEXT);
$token = required_param('token', PARAM_TEXT);
$timestamp = required_param('timestamp', PARAM_INT);
$email = required_param('email', PARAM_TEXT);

$PAGE->set_url('/auth/token/index.php');
$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));

if(isset($_GET['username']) && isset($_GET['token']) && isset($_GET['timestamp']) && isset($_GET['email']))
{
    $pluginconfig = get_config('auth/token');
    $secret_salt = $pluginconfig->salt;
    $localtoken = crypt($timestamp . $email, $secret_salt);
    
    if($localtoken == $token)
    {
        $current_time = time();
        
        if($current_time <= $timestamp + (30 * 60))
        {
            $localuser = $DB->get_record('user', array(
                'username' => $username,
                'email' => $email 
            ));
            
            if(! empty($localuser))
            {
                $do_update = false;
                do_login();
            }
            else
            {
                $err['login'] = get_string("auth_token_error_general_user_login", "auth_token" ,  $CFG->supportemail);
                token_error($err['login'] . '--USER NOT LOGIN--', '?logout', $pluginconfig->tokenlogfile);
            }
        }
        else
        {
            $err['login'] = get_string("auth_token_error_general_user_login", "auth_token" ,  $CFG->supportemail); // timeout
            token_error($err['login'] . '--TIMEOUT--', '?logout', $pluginconfig->tokenlogfile);
        }
    }
    else
    {
        $err['login'] = get_string("auth_token_error_general_user_login", "auth_token" ,  $CFG->supportemail); // wrong token
        token_error($err['login'] . '--WRONG TOKEN--', '?logout', $pluginconfig->tokenlogfile);
    }
}
else
{
    
    $err['login'] = get_string("auth_token_error_general_user_login", "auth_token" ,  $CFG->supportemail); // wrong token
    token_error($err['login'] . '--WRONG TOKEN--', '?logout', $pluginconfig->tokenlogfile);
}


function do_login()
{
    // We load all moodle config and libs
    require_once ('../../config.php');
    require_once ('error.php');
    
    global $CFG, $USER, $SESSION, $err, $DB, $PAGE;
    global $username, $localuser;
    
    $goto = (isset($_REQUEST['goto']) && trim($_REQUEST['goto']) != '') ? $_REQUEST['goto'] : '';
    
    // Valid session. Register or update user in Moodle, log him on, and redirect to Moodle front
    if(file_exists('custom_hook.php'))
    {
        include_once ('custom_hook.php');
    }
    
    $PAGE->set_url('/auth/token/token_login.php');
    $PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));
    
    if(! empty($goto))
    {
        $urltogo = urldecode($goto);
    }
    else
    {
        $urltogo = $CFG->wwwroot;
        if($CFG->wwwroot[strlen($CFG->wwwroot) - 1] != '/')
        {
        	$urltogo .= '/';
        }
    }
    
    // Get the plugin config for token
    $pluginconfig = get_config('auth/token');
    
    $GLOBALS['token_login'] = true;
    
    // Just passes time as a password. User will never log in directly to moodle with this password anyway or so we hope?
    $user = token_user_login($username, time());
    if($user === false)
    {
        $err['login'] = get_string("auth_token_error_general_user_login", "auth_token" ,  $CFG->supportemail); // not authenticated
        token_error($err['login'] . '--NOT AUTH--', '?logout', $pluginconfig->tokenlogfile);
    }
    
    // Complete the user login sequence
    /*
     * $user = get_complete_user_data('id', $USER->id); if ($user === false) { $err['login'] = get_string("auth_token_error_general_user_login", "auth_token"); // data not completed token_error($err['login'].'--DATA NOT COMPLETE--', '?logout', $pluginconfig->tokenlogfile); }
     */
    
    $USER = complete_user_login($user);
    if(function_exists('token_hook_post_user_created'))
    {
        token_hook_post_user_created($USER);
    }
    
    $USER->loggedin = true;
    $USER->site = $CFG->wwwroot;
    set_moodle_cookie($USER->username);
    
    add_to_log(SITEID, 'user', 'login', "view.php?id=$USER->id&course=" . SITEID, $USER->id, 0, $USER->id);
    
    if(isset($err) && ! empty($err))
    {
        token_error($err, $urltogo, $pluginconfig->tokenlogfile);
    }
    
    redirect($urltogo);
}


/**
 * Authenticates a user against the chosen authentication mechanism
 *
 * Given a username and password, this function looks them
 * up using the currently selected authentication mechanism,
 * and if the authentication is successful, it returns a
 * valid $user object from the 'user' table.
 *
 * Uses auth_ functions from the currently active auth module
 *
 * After authenticate_user_login() returns success, you will need to
 * log that the user has logged in, and call complete_user_login() to set
 * the session up.
 *
 * Note: this function works only with non-mnet accounts!
 *
 * @param string $username User's username
 * @param string $password User's password
 * @param bool $ignorelockout useful when guessing is prevented by other mechanism such as captcha or SSO
 * @param int $failurereason login failure reason, can be used in renderers (it may disclose if account exists)
 * @return stdClass false {@link $USER} object or false if error
 */
function token_user_login($username, $password, $ignorelockout = false, &$failurereason = null)
{
    global $CFG, $DB;
    require_once ("$CFG->libdir/authlib.php");
    
    $authsenabled = get_enabled_auth_plugins();
    
    if($user = get_complete_user_data('username', $username, $CFG->mnet_localhost_id))
    {
        // always use token.
        $auth = 'token';
        if(! empty($user->suspended))
        {
            add_to_log(SITEID, 'login', 'error', 'index.php', $username);
            error_log('[client ' . getremoteaddr() . "]  $CFG->wwwroot  Suspended Login:  $username  " . $_SERVER['HTTP_USER_AGENT']);
            $failurereason = AUTH_LOGIN_SUSPENDED;
            return false;
        }
        if($auth == 'nologin' or ! is_enabled_auth($auth))
        {
            add_to_log(SITEID, 'login', 'error', 'index.php', $username);
            error_log('[client ' . getremoteaddr() . "]  $CFG->wwwroot  Disabled Login:  $username  " . $_SERVER['HTTP_USER_AGENT']);
            // Legacy way to suspend user.
            $failurereason = AUTH_LOGIN_SUSPENDED;
            return false;
        }
        $auths = array(
            $auth 
        );
    }
    else
    {
        // Check if there's a deleted record (cheaply), this should not happen because we mangle usernames in delete_user().
        if($DB->get_field('user', 'id', array(
            'username' => $username,
            'mnethostid' => $CFG->mnet_localhost_id,
            'deleted' => 1 
        )))
        {
            error_log('[client ' . getremoteaddr() . "]  $CFG->wwwroot  Deleted Login:  $username  " . $_SERVER['HTTP_USER_AGENT']);
            $failurereason = AUTH_LOGIN_NOUSER;
            return false;
        }
        
        // Do not try to authenticate non-existent accounts when user creation is not disabled.
        if(! empty($CFG->authpreventaccountcreation))
        {
            add_to_log(SITEID, 'login', 'error', 'index.php', $username);
            error_log('[client ' . getremoteaddr() . "]  $CFG->wwwroot  Unknown user, can not create new accounts:  $username  " . $_SERVER['HTTP_USER_AGENT']);
            $failurereason = AUTH_LOGIN_NOUSER;
            return false;
        }
        
        // User does not exist.
        $auths = $authsenabled;
        $user = new stdClass();
        $user->id = 0;
    }
    
    if($ignorelockout)
    {
        // Some other mechanism protects against brute force password guessing, for example login form might include reCAPTCHA
        // or this function is called from a SSO script.
    }
    else 
        if($user->id)
        {
            // Verify login lockout after other ways that may prevent user login.
            if(login_is_lockedout($user))
            {
                add_to_log(SITEID, 'login', 'error', 'index.php', $username);
                error_log('[client ' . getremoteaddr() . "]  $CFG->wwwroot  Login lockout:  $username  " . $_SERVER['HTTP_USER_AGENT']);
                $failurereason = AUTH_LOGIN_LOCKOUT;
                return false;
            }
        }
        else
        {
            // We can not lockout non-existing accounts.
        }
    
    foreach($auths as $auth)
    {
        $authplugin = get_auth_plugin($auth);
        
        // On auth fail fall through to the next plugin.
        if(! $authplugin->user_login($username, $password))
        {
            continue;
        }
        
        // Successful authentication.
        if($user->id)
        {
            // User already exists in database.
            if(empty($user->auth))
            {
                // For some reason auth isn't set yet.
                $DB->set_field('user', 'auth', $auth, array(
                    'username' => $username 
                ));
                $user->auth = $auth;
            }
            
            if($authplugin->is_synchronised_with_external())
            {
                // Update user record from external DB.
                $user = update_user_record($username);
            }
        }
        else
        {
            // Create account, we verified above that user creation is allowed.
            $user = create_user_record($username, $password, $auth);
        }
        
        $authplugin->sync_roles($user);
        
        foreach($authsenabled as $hau)
        {
            $hauth = get_auth_plugin($hau);
            $hauth->user_authenticated_hook($user, $username, $password);
        }
        
        if(empty($user->id))
        {
            $failurereason = AUTH_LOGIN_NOUSER;
            return false;
        }
        
        if(! empty($user->suspended))
        {
            // Just in case some auth plugin suspended account.
            add_to_log(SITEID, 'login', 'error', 'index.php', $username);
            error_log('[client ' . getremoteaddr() . "]  $CFG->wwwroot  Suspended Login:  $username  " . $_SERVER['HTTP_USER_AGENT']);
            $failurereason = AUTH_LOGIN_SUSPENDED;
            return false;
        }
        
        login_attempt_valid($user);
        $failurereason = AUTH_LOGIN_OK;
        return $user;
    }
    
    // Failed if all the plugins have failed.
    add_to_log(SITEID, 'login', 'error', 'index.php', $username);
    if(debugging('', DEBUG_ALL))
    {
        error_log('[client ' . getremoteaddr() . "]  $CFG->wwwroot  Failed Login:  $username  " . $_SERVER['HTTP_USER_AGENT']);
    }
    
    if($user->id)
    {
        login_attempt_failed($user);
        $failurereason = AUTH_LOGIN_FAILED;
    }
    else
    {
        $failurereason = AUTH_LOGIN_NOUSER;
    }
    
    return false;
}

session_write_close();
redirect($CFG->wwwroot);