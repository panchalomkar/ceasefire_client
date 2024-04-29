<?php
/**
 * Notifications block caps
 *
 * @package    block_rlms_notifications
 * @copyright  Mark Nelson <goran.josic@usi.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    
     'block/rlms_notifications:myaddinstance' => array(
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'user' => CAP_ALLOW
        ),
        
        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ),
    
    'block/rlms_notifications:addinstance' => array(
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        ),
        
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    )
);