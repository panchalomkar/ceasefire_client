<?php
require_once($CFG->libdir . '/gradelib.php');
require_once $CFG->dirroot . '/grade/report/overview/lib.php';
require_once $CFG->dirroot . '/grade/lib.php';
require_once(__DIR__ . '/../../config.php');
function get_c_images($courseid) {
    global $USER, $CFG, $OUTPUT, $DB, $PAGE;
    $course = $DB->get_record('course', array('id' => $courseid));
    require_once($CFG->dirroot.'/course/renderer.php');
       $chelper = new \coursecat_helper();
       if (is_array($course)) {
           $course = (object)$course;
       }
       $course->fullname = strip_tags($chelper->get_course_formatted_name($course));
   $course  = new core_course_list_element($course);
 //  print_object($course);
   foreach ($course->get_course_overviewfiles() as $file) {
       $isimage = $file->is_valid_image();
       $imageurl = file_encode_url(
           "$CFG->wwwroot/pluginfile.php",
           '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
           $file->get_filearea(). $file->get_filepath(). $file->get_filename(),
           !$isimage
       );
   }

   if (empty($imageurl)) {
       $imageurl = $OUTPUT->get_generated_image_for_id($courseid);
   }
   return $imageurl;
}


function get_user_profile($userid) {
    global $DB,$OUTPUT;
    $sql = "SELECT u.*, p.available_points, p.userrank FROM {user_points} p 
    INNER JOIN {user} u ON u.id = p.userid
    WHERE u.deleted = 0 AND u.suspended = 0 AND p.userid = $userid ORDER BY  p.available_points";
    $records = $DB->get_records_sql($sql);
    $userdetail = [];
    $achive = [];
    foreach ($records as $row) {
        $badges = $DB->get_records('badge_issued', array('userid' => $row->id));
        $user_object = core_user::get_user($row->id);
        $person_profile_pic = $OUTPUT->user_picture($user_object,array('link'=>false));

        $userdetail[] = array(
            'studentid' => $row->id,
            'studentname' => $row->firstname.' '.$row->lastname,
            'studentimage' => $person_profile_pic,
            'studentemail' => $row->email,
            'department' => $row->department,
            'mobileno' => $row->phone1,
        );

        $achive[] = array(
            'userpoints' => $row->available_points,
            'badgesearn' => COUNT($badges),
            'userlevel' => $row->userrank,
        );
    }
   return ['userinfo' => $userdetail, 'achievement' => $achive];
}

function get_course_progress($userid) {
    global $DB,$OUTPUT;

    $getsqlss = "SELECT ue.*, e.courseid FROM {enrol} e 
    INNER JOIN {user_enrolments} ue ON e.id = ue.enrolid WHERE ue.userid = $userid ORDER BY ue.id DESC"; 
    $courses = $DB->get_records_sql($getsqlss);


    $completioned = 0;
    $inprogress = 0;
    $totalnotstarted = 0;
    foreach ($courses as $value) {
    $course = $DB->get_record('course', array('id' => $value->courseid));

    // Load completion data.
    $info = new completion_info($course);
          // Is course complete?
      $coursecomplete = $info->is_course_complete($value->userid);

      // Has this user completed any criteria?
      $criteriacomplete = $info->count_course_user_data($value->userid);

      // Load course completion.
      $params = array(
          'userid' => $value->userid,
          'course' => $value->courseid,
      );

      $ccompletion = new completion_completion($params);

      // Save row data.
      $rows = array();

      // Flag to set if current completion data is inconsistent with what is stored in the database.
      $pendingupdate = false;

      // Load criteria to display.
      $completions = $info->get_completions($value->userid);

      foreach ($completions as $completion) {
        $criteria = $completion->get_criteria();
        if (!$pendingupdate && $criteria->is_pending($completion)) {
            $pendingupdate = true;
        }
    
        $row = array();
        $row['type'] = $criteria->criteriatype;
        $row['title'] = $criteria->get_title();
        $row['status'] = $completion->get_status();
        $row['complete'] = $completion->is_complete();
        $row['timecompleted'] = $completion->timecompleted;
        $row['details'] = $criteria->get_details($completion);
        $rows[] = $row;
    }

    if ($pendingupdate) {
      $pending++;
      } else if ($coursecomplete) {
        $completioned++;
      } else if (!$criteriacomplete && !$ccompletion->timestarted) {
         $totalnotstarted++;
      } else {
        $inprogress++;
      }
   
   }

   $completedper = $completioned /count($courses) *100;
   $inprogressper = $inprogress /count($courses) *100;
   $totalnotstartedper = $totalnotstarted /count($courses) *100;
   return ['completioned' => $completedper, 'inprogress' => $inprogressper, 'totalnotstarted' => $totalnotstartedper];
}


function get_learninpath_data($userid) {
    global $DB,$OUTPUT;

    $course_modules = $DB->get_records_sql("SELECT l.* FROM {learningpaths} l 
    INNER JOIN {learningpath_users} lu ON l.id = lu.learningpathid WHERE lu.userid = $userid AND l.publish = 1");
    $completioned = 0;
    $inprogress = 0;
    $totalnotstarted = 0;
     $dataget = array();
     $datagetcourse = array();
     foreach ($course_modules as $keyalue) {
     $noofcourse = $DB->get_records('learningpath_courses', array('learningpathid' => $keyalue->id));
    
     
     $noofcoursess = $DB->get_record('learningpath_courses', array('learningpathid' => $keyalue->id));
  
     $jsonString = "$keyalue->description";

     // Decode the JSON string into a PHP associative array
    $data = json_decode($jsonString, true);
     // Access the "text" value
    $textValue = $data['text'];
    $startDateString = DATE('Y-m-d', $keyalue->startdate);
    $endDateString = DATE('Y-m-d', $keyalue->enddate);
    $timeFirst  = strtotime($startDateString);
    $timeSecond = strtotime($endDateString);
    $differenceInSeconds = $timeSecond - $timeFirst;
    $duration = secondsToTime($differenceInSeconds);

  
    $course = $DB->get_record('course', ['id' => $noofcoursess->courseid]);
    // $completion = new completion_info($course);
    // $completed = ($completion->is_course_complete($USER->id));
    // $percentage = COUNT($completed) / COUNT($noofcourse) * 100;
    define("MAX_LP_PAGE", 10);
    $offset = MAX_LP_PAGE * $page;

    //kkkkkkkkkk
   $learningpaths = $DB->get_record('learningpaths', ['id' => $keyalue->id]);
   $result = getCoursesInfo($learningpaths->id, false, $USER->id, MAX_LP_PAGE, $offset,null);
//    $percentage = 0;
//    foreach ($result as $key => $course) {
//     $percentage .= $course->progress;
//    }  
   $lpprogress = newLpprogress($learningpaths->id,$learningpaths->credits,$USER->id);  
   //print_r($result);
  // exit();

   $progress = '<div class="progress" style="width:100px">
    <div class="progress-bar bg-success" role="progressbar" style="width: '.$lpprogress.'%" aria-valuenow="'.$lpprogress.'" aria-valuemin="0" aria-valuemax="100">'.round($lpprogress).'%</div>
    </div>';

   $imagepathurl = $CFG->wwwroot."/local/learningpaths/pluginfile.php?learningpathid={$keyalue->id}&t=";

        $dataget[] = array(
            'learningpathname' => $keyalue->name,
            'creadit' => $keyalue->credits,
            'startdate' => $keyalue->startdate,
            'enddate' => $keyalue->enddate,
            'publish' => $keyalue->publish,
            'self_enrollment' => $keyalue->self_enrollment,
            'learningpathimage' => $imagepathurl,
            'discriotion' => $textValue,
            'nocourses' => COUNT($noofcourse),
            'duration' => $duration,
            'progress' => $progress,
            'urllink' => $CFG->wwwroot."/blocks/learningpathview/lp_view_course.php?id=$keyalue->id",
        );

        $get_learningpath = $DB->get_records_sql("SELECT * FROM {learningpath_courses} WHERE learningpathid = $keyalue->id");

        foreach ($get_learningpath as $keyval) {
            $courserepre = $DB->get_record_sql("SELECT * FROM {learningpath_course_prereq} WHERE learningpath_courseid = $keyval->id");
            $getcourse = $DB->get_record('course', array('id' => $courserepre->prerequisite));
           
            
            $getcour = $DB->get_record('course', array('id' => $keyval->courseid));
            $progressdata = \core_completion\progress::get_course_progress_percentage($getcour,$USER->id);
            $percentage = floor($progressdata);
         
            $getimg = get_c_images($getcour->id);

            $progress = '<div class="progress">
            <div class="progress-bar bg-success" role="progressbar" style="width: '.$percentage.'%" aria-valuenow="'.$percentage.'" aria-valuemin="0" aria-valuemax="100">'.$percentage.'%</div>
            </div>';
            $datagetcourse[] = array(
                'coursename' => $getcour->fullname,
                'coursedec' => $getcour->summary,
                'courseimg' => $getimg,
                'courseprogressbar' => $progress,
                'required' => $keyval->required,
                'courseprerequisite' => $courserepre->prerequisite,
                'courselink' => $courselink
            );
        }



    }
   return ['learningpathdata' => $dataget, 'learningpathprogress' => $datagetcourse];
}

function get_user_grade_avg($userid) {
    global $DB,$OUTPUT;

    $getsqlss = "SELECT DISTINCT(e.courseid) FROM {enrol} e 
    INNER JOIN {user_enrolments} ue ON e.id = ue.enrolid  
    WHERE ue.userid = $userid ORDER BY ue.id DESC"; 
    $getcourses = $DB->get_records_sql($getsqlss);
    
    $finalgrade = 0;
    $countgrade = 0;
    $countactivity = [];
    $comsession = [];
    foreach ($getcourses as $keyvaluess) {
    $countactivity += $DB->get_records('course_modules', array('course' => $keyvaluess->courseid));

    $comsession += $DB->get_records_sql("SELECT * FROM {course_modules} cm 
    INNER JOIN {course_modules_completion} cmc ON cm.id = cmc.coursemoduleid 
    WHERE cmc.completionstate = 1 AND cmc.userid = $userid AND cm.course = $keyvaluess->courseid");
    // Get course grade_item
    $course_item = grade_item::fetch_course_item($keyvaluess->courseid);

    // Get the stored grade
    $course_grade = new grade_grade(array('itemid'=>$course_item->id, 'userid'=>$userid));
    $course_grade->grade_item =& $course_item;
    $finalgrade += $course_grade->finalgrade;
    $countgrade++;
    }
 

    $average = $finalgrade/$countgrade;
   
   return ['totalnoofactivity' => count($countactivity), 'completedactivity' => count($comsession), 'avaragegrade' => $average];
}

 function secondsToTime($inputSeconds) {
    $secondsInAMinute = 60;
    $secondsInAnHour = 60 * $secondsInAMinute;
    $secondsInADay = 24 * $secondsInAnHour;

    // Extract days
    $days = floor($inputSeconds / $secondsInADay);

    // Extract hours
    $hourSeconds = $inputSeconds % $secondsInADay;
    $hours = floor($hourSeconds / $secondsInAnHour);

    // Extract minutes
    $minuteSeconds = $hourSeconds % $secondsInAnHour;
    $minutes = floor($minuteSeconds / $secondsInAMinute);

    // Extract the remaining seconds
    $remainingSeconds = $minuteSeconds % $secondsInAMinute;
    $seconds = ceil($remainingSeconds);

    // Format and return
    $timeParts = [];
    $sections = [
        'day' => (int)$days,
        'hour' => (int)$hours,
        'minute' => (int)$minutes,
        'second' => (int)$seconds,
    ];

    foreach ($sections as $name => $value){
        if ($value > 0){
            $timeParts[] = $value. ' '.$name.($value == 1 ? '' : 's');
        }
    }

    return implode(', ', $timeParts);
}
