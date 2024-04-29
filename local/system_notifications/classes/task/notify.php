<?php
namespace local_system_notifications\task;
class notify extends \core\task\scheduled_task
{
    
    public function get_name()
    {
        // Shown in admin screens
        return get_string('pluginname', 'local_system_notifications');
    }
                                                                  
    public function execute()
    {
        GLOBAL $DB, $CFG;
        
//        include_once("{$CFG->dirroot}/blocks/rlms_notifications/locallib.php");
        
        // get notifications where type=cron
        $plugin_data = (array)get_config('local_system_notifications');
        
//        $plugin_keys = array_keys((array)$plugin_data);
        $records = [];
        foreach ($plugin_data as $key => $value) {
            $str_enable = substr($key, 0, 7);
            if($str_enable == 'enable_' && $value){
                $notify_name = substr($key, 7, strlen($key));
                unset($plugin_data[$key]);
                $tmp_config = [];
                foreach ($plugin_data as $key2 => $value2) {
                    if (strpos($key2, $notify_name) !== false) {
                        $tmp_config[$key2] = $value2;
                        unset($plugin_data[$key2]);
                    }
                }
                $records[$notify_name] = $tmp_config;
            }
        }
        
        if (!$this->get_last_run_time() || date_diff(date_create(date('Y-m-d h:i:s',$this->get_last_run_time())), date_create(date('Y-m-d h:i:s')))->format("%R%a") >= 1) {
            foreach ($records as $key => $record) {
                // lets load the class for this notification
                $urlbase = str_replace('\\', '/', $CFG->dirroot);
                $fileName = $urlbase . "/local/system_notifications/classes/{$key}.php";
                mtrace($fileName);
                if (file_exists($fileName)) {
                    include_once($fileName);
                    mtrace($key);
                    $obj = new $key();
                    $obj->run($record);
                }
            }
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