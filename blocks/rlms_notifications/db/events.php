<?php
$observers = array(
    array(
        'eventname'   => '\core\event\course_completed',
        'callback'    => 'blocks_rlms_notifications_observer::notify_course_completed',
    ),

    array (
        'eventname'   => '\core\event\user_enrolment_updated',
        'includefile' => '/blocks/rlms_notifications/locallib/user_enrol_notify.php',
        'callback'    => 'user_enroll_notification_event'
    ),

    array (
        'eventname'   => '\core\event\user_enrolment_created',
        'includefile' => '/blocks/rlms_notifications/locallib/user_enrol_notify.php',
        'callback'    => 'user_enroll_notification_event'
    ),
);
