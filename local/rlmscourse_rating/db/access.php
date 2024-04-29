<?php

/*
 * @author Dnyaneshwar K.
 * @tickt #619
 * @dated 17-07-2019
 *
 */

$capabilities = array(
    'local/rlmscourse_rating:view' => array(
        'captype' => 'write',
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),
    'local/rlmscourse_rating:add' => array(
        'captype' => 'write',
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'student' => CAP_ALLOW
        )
    )
    
);