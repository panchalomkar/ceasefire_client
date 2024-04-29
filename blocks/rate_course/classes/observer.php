<?php
 /**
 * Removed old Event 1 API and implemented Event 2 API
 * refer - https://docs.moodle.org/dev/Event_2#Event_dispatching_and_observers
 *
 * @author  Jayesh T
 * @since   6 June 2019
 * @rlms
 */

defined('MOODLE_INTERNAL') || die();

class blocks_rate_course {
    public static function course_delete($eventdata) {
        global $DB;
        $res = $DB->delete_records('block_rate_course',
                array('course'=>$eventdata->id));
        if ($res === false) {
            return $res;
        }
        return true;
    }
}
