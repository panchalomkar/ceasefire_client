<?php
require_once ('../../config.php');
require_once ('error.php');
require_once ('../../cohort/lib.php');

$secretSalt = required_param('token', PARAM_TEXT); // "GreenLms"
$email = required_param('email', PARAM_TEXT);
$firstName = required_param('fn', PARAM_TEXT);
$lastName = required_param('ln', PARAM_TEXT);

$city = optional_param('city', '', PARAM_TEXT);
$country = optional_param('country', '', PARAM_TEXT);
$address = optional_param('address', '', PARAM_TEXT);
$company = optional_param('company', '', PARAM_TEXT);
$courses = optional_param('courses', '', PARAM_TEXT);
$cohorts = optional_param('cohorts', '', PARAM_TEXT);
$timestamp = optional_param('ts', '', PARAM_INT);
$user = optional_param('user', '', PARAM_TEXT);
$role = optional_param('role', '', PARAM_TEXT);

$newuser = 1;

if(empty($user))
    $user = $email;

if(empty($timestamp))
    $timestamp = time();

$token = crypt($timestamp . $user . $email, $secretSalt);

$url = $CFG->wwwroot . '/auth/token/index.php';

$params = array(
    'user' => $user,
    'token' => $token,
    'ts' => $timestamp,
    'email' => $email,
    'newuser' => $newuser,
    'fn' => $firstName,
    'ln' => $lastName,
    'city' => $city,
    'country' => $country,
    'company' => $company,
    'cohorts' => $cohorts,
    'courses' => $courses,
    'address' => $address,
    'role'    => $role
);

$ssoUrl = "$url?" . http_build_query($params, null, '&');

header("Location: $ssoUrl");

/*
echo "<pre>";
print_r($sso_url);
echo "</pre>";
*/