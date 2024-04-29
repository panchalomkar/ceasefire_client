<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'block/courses_statistics:addinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'manager' => CAP_ALLOW
        ),
 
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ),

  'block/courses_statistics:view' => array(
  'captype'      => 'read',
  'contextlevel' => CONTEXT_BLOCK,
      'archetypes' => array(
        'guest'          => CAP_PREVENT,
        'student'        => CAP_ALLOW,
        'teacher'        => CAP_ALLOW,
        'editingteacher' => CAP_ALLOW,
        'coursecreator'  => CAP_ALLOW,
        'manager'        => CAP_ALLOW
      )
  ),
);

