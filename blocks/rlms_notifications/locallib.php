<?php


function rlms_notifications_log($settingId, $status)
{
    GLOBAL $DB;

    $data = new stdClass();
    $data->settings_id = $settingId;
    $data->status = $status;
    $data->created_on = date('Y-m-d H:i:s');

    try
    {
        $DB->insert_record('block_rlms_ntf_log', $data);
    }
    catch(\Exception $e)
    {
        // error
        rlms_notifications_log($settingsId, 2);
    }
}
