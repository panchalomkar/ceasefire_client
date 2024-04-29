<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$observers = array(
      array(
        'eventname'   => '\core\event\cohort_deleted',
        'callback'    => 'team_removed',
        'includefile' => '/local/team/locallib.php',
    ),
);