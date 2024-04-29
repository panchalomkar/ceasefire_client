<?php
/**
 * Handles upgrading instances of this block.
 *
 * @param int $oldversion
 * @param object $block
 */
function xmldb_block_rlms_notifications_upgrade($oldversion, $block)
{
    global $DB, $CFG, $COURSE;

    $dbman = $DB->get_manager();    
    $result=true;

    // Fix bad filtering on posted_to values.
    if ($oldversion < 2018032302) {
        
        upgrade_plugin_savepoint(true, 2018032302, 'block','rlms_notifications');
    }

    /**
     * Update to the notification config key from json to resolve the 
     * course 
     * 
     * @author Sandeep B
     * @since 07-06-2019
     * @rlms
     */
    if ( $oldversion < 2019060700 ) {
        $record = $DB->get_record( 'block_rlms_ntf', ['name' => 'notify_on_quiz_reminder'] );
        if( !empty( (array) $record ) ){
            $record_config = json_decode ($record->config);
            if( !empty($record_config) && isset($record_config->days_after_reminder) ){
                $record_config->days_before_reminder = $record_config->days_after_reminder;
                unset($record_config->days_after_reminder);
                $record->config = json_encode($record_config);
                $updated = $DB->update_record('block_rlms_ntf', $record );
            }
        }

        upgrade_plugin_savepoint(true, 2019060700, 'block','rlms_notifications');
    }

    /**
     * New user enrollment expiration notification
     * 
     * @author Jayesh T
     * @since 03 April 2020
     * @rlms
     */
    if ( $oldversion < 2020040300 ) {
        $record = $DB->get_record( 'block_rlms_ntf', ['name' => 'notify_on_enrollment_expire'] );
        if( !$record ){
            $new_notification = new stdClass();
            $new_notification->name = 'notify_on_enrollment_expire';
            $new_notification->template = 'NO TEMPLATE YET';
            $new_notification->config = '{\"notify_email_subject\":\"Enrollment will be expire for : {course_name}\",\"days_before_enrollment_expiration\":45}';
            $new_notification->type = 'cron';
            $DB->insert_record('block_rlms_ntf', $new_notification );
        }
        upgrade_plugin_savepoint(true, 2020040300, 'block','rlms_notifications');
    }

    return $result;
}
