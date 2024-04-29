<?php
defined('MOODLE_INTERNAL') || die();

$tasks = array(
    array(
        'classname'     => 'local_re_certification\task\recertification',
        'blocking'      => 0,
        'minute'        => '*/5',
        'hour'          => '*',
        'day'           => '*',
        'dayofweek'     => '*',
        'month'         => '*'
    ),
);