<?php
namespace block_rlms_notifications\task;

class notify extends \core\task\scheduled_task
{
    
    public function get_name()
    {
        // Shown in admin screens
        return get_string('pluginname', 'block_rlms_notifications');
    }
                                                                     
    public function execute()
    {
        GLOBAL $DB, $CFG;
        
        include_once("{$CFG->dirroot}/blocks/rlms_notifications/locallib.php");
        
        // get notifications where type=cron

        /**
        * Get all enabled course notifications and validate id file exists 
        * @author Yesid V.
        * @since Sep 01, 2017
        * @rlms
        *
        */

        $sql = "SELECT s.*, n.name, n.type
                FROM {block_rlms_ntf_settings}    AS s
                     INNER JOIN {block_rlms_ntf} AS n ON n.id = s.notification_id
                WHERE s.enabled = 1 AND n.type like '%cron%'";
        
        $records = $DB->get_records_sql($sql);
        
        foreach($records as $record) {
            //if(!$this->_find_in_log($record->id)) {
                // lets load the class for this notification
                $urlbase = str_replace('\\', '/', $CFG->dirroot);
                $fileName = $urlbase."/blocks/rlms_notifications/classes/{$record->name}.php";
                    mtrace($fileName);
                if(file_exists($fileName)) {


                    include_once($fileName);
                    
                    $cls = $record->name;
                    
                    mtrace($cls);

                    $obj = new $cls();
                    $obj->run($record);

                } else {
                    //rlms_notifications_log($record->id, 2);
                }
            //}
        }
    }
   
    
    protected function _find_in_log($settingId) {
        GLOBAL $DB;
        
        $start = date('Y-m-d') . ' 00:00:00';
        $end = date('Y-m-d') . ' 23:59:59';
        
        $sql = "
        SELECT 
        *
        FROM {block_rlms_ntf_log}
        WHERE settings_id = '$settingId'
        AND created_on BETWEEN '$start' AND '$end'";

        $records = $DB->get_records_sql($sql);

        return count($records);
    }
}