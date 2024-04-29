<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(
    'local_trigger_username_replace' => array(
        'classname' => 'local_trigger_external',
        'methodname' => 'username_replace',
        'classpath' => 'local/trigger/externallib.php',
        'description' => 'It replace temp username with confirm username',
	'type' => 'write',
	'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
        'local_trigger_redeem_points' => array(
        'classname' => 'local_trigger_external',
        'methodname' => 'redeem_points',
        'classpath' => 'local/trigger/externallib.php',
        'description' => 'Redeem point externally',
        'type' => 'write',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
);
