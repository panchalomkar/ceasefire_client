<?php

 /* get the conformation of the completion */
function getconform($user, $courseid)
{
    global $DB;
    $course = $DB->get_record('course', array('id' => $courseid));
    $info = new completion_info($course);
    $completions = $info->get_completions($user);
    $coursecomplete = true;
    $criteriacomplete = $info->count_course_user_data($user);
    return $coursecomplete;
}


/*issued certificate */
function getcertissued($userid, $course)
{
    global $CFG,$DB;
    $certificatemod = $DB->get_field('modules', 'id', array('name' => 'certificate'));
    $getissued = $DB->get_records_sql('SELECT cm.id FROM {course_modules} AS cm,{certificate_issues} AS ci,{certificate} AS c where cm.course='.$course.' AND cm.module='.$certificatemod.' AND cm.course=c.course AND c.id=ci.certificateid AND ci.userid='.$userid.'');

    foreach($getissued as $issue)
    {
        $output = array();
        $output = $issue->id;
        return $output;
    }
}

/* get my certificate */
function getmycertificate($userid, $courseid)
{
    global $CFG,$DB;
    $certificate = $DB->get_records_sql('SELECT ce.id,cmc.coursemoduleid,ce.printhours FROM 
    
    {course_modules_completion} AS cmc,
    {course_modules} AS cm,{certificate} AS ce,
    {course} AS c 
    WHERE c.id=' . $courseid . ' AND c.id=ce.course AND ce.id=cm.instance AND cm.id=cmc.coursemoduleid AND cmc.userid='.$userid.' ');
    
    foreach($certificate as $Mycertificate)
    {
        $output = array();
        $output[0] = $Mycertificate->id;
        $output[1] = $Mycertificate->coursemoduleid;
        $output[2] = $Mycertificate->printhours;
        return $output;
    }
}


function getmygrades($user,$course)
{
    global $CFG,$DB;
    $Mytranscripts = $DB->get_recordset_sql('SELECT c.fullname,c.id,gg.finalgrade,gg.timemodified FROM 
    {course} AS c,
    {grade_items} AS gi,
    {grade_grades} AS gg,
    {user} AS u 
    WHERE u.id =' . $user . ' AND c.id=' . $course . ' AND c.id = gi.courseid AND gg.itemid = gi.id AND u.id = gg.userid AND gi.itemtype="mod"');
    
    foreach($Mytranscripts as $transcript)
    {
        return $transcript->finalgrade;
    }
}

function rlms_courserecords_myrecords($userId)
{
    global $CFG, $DB;
    $pluginName = 'rlms_courserecords';
    
    require_once "{$CFG->libdir}/completionlib.php";
    
    $sql = "
    SELECT
        c.fullname as course_full_name,
        c.id as course_id,
        cc.timecompleted as time_completed,
        cc.userid as user_id,
        cc.course as course_id,
        cc.timestarted as time_started,
        cc.timeenrolled as time_enrolled,
        ROUND(gg.finalgrade, 2) final_grade
    FROM
        mdl_course c
        LEFT JOIN mdl_course_categories ct ON (ct.id = c.category)
        LEFT JOIN mdl_enrol e ON (e.courseid = c.id)
        LEFT JOIN mdl_user_enrolments ue ON (ue.enrolid = e.id)
        LEFT JOIN mdl_course_completions AS cc on cc.course = c.id
        LEFT JOIN mdl_grade_items AS gi ON gi.courseid = c.id
        LEFT JOIN mdl_grade_grades AS gg ON gg.itemid = gi.id
    WHERE 
        cc.timecompleted IS NOT NULL AND cc.timestarted IS NOT NULL
    AND cc.userid = $userId
    AND gi.itemtype = 'course'
    GROUP BY c.id ORDER BY ct.sortorder, c.sortorder ASC
    ";
    
  
    
    $mycompletions = $DB->get_recordset_sql($sql);

    $table = '<div class="generaltable rlms-course-records-table generaltable_rlms">
        <div class="header row">
            <div class="span-header col-md-4" scope="col">' . get_string('course_name', "block_$pluginName") . '</div>
            <div class="span-header col-md-4" scope="col">' . get_string('certificate', "block_$pluginName") . '</div>
            <div class="span-header col-md-2" scope="col">' . get_string('score_grade', "block_$pluginName") . '</div>
            <div class="span-header col-md-2" scope="col">' . get_string('completed_dates', "block_$pluginName") . '</div>
        </div>
    ';

    $coursesCompleted = 0; // counting how many courses completed are
    $table .= '<div class="items-content">';
    $coursescount = 0;
    foreach ($mycompletions as $completions) {
        //Loop for the Transcript 
        $courseid = $completions->course_id;
        $coursename = $completions->course_full_name;
        $completeddate = $completions->time_completed; 
        $startdate = $completions->time_started;
        $timeenrolled = $completions->time_enrolled;
        $coursegrade = $completions->final_grade;

        /* Check if certificate issued */
        $getissued = getcertissued($userId, $courseid);

        if(!$completeddate) {
            if(!$startdate) {
                $transcripttime = $completions->time_enrolled;
            } else {
                $transcripttime = $completions->time_started;
            }
        } else {
            $transcripttime = $completions->time_completed;
        }

        if($coursegrade){
            if($coursegrade <= 0){
                $finalgrade = get_string('no_applicable', "block_rlms_courserecords");
            } else {
                $finalgrade = $coursegrade . " %";
            }
        }
        else{
            $finalgrade = get_string('no_applicable', "block_rlms_courserecords");
        }

        $table .= '<div class="item row">';
        $table .= '<div class="responsive_my_records coursename col-md-4 col-sm-4"> <a href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseid . '" class="mycourse">' . $coursename . '</a></div>';
        if ($getissued) {
            $table .= '<div class="responsive_my_records col-md-4 col-sm-4 items-cell certificate_hide">'; 
            $table .= '<a class="certificate" href="' . $CFG->wwwroot . '/mod/certificate/view.php?id=' . $getissued . '&action=get" target="_blank" aria-label="'.get_string('getcertification', 'block_rlms_courserecords').'"><i class="wid wid-icon-phcertificate fa fa-certificate"></i>'.get_string('dw_certificate', 'block_rlms_courserecords').'</a>';
            $table .= '</div>';
        } else {
            $table .= '<div class="responsive_my_records col-md-4 col-sm-4 items-cell certificate_hide">'; 
            $table .= '<a class="certificate" href="' . $CFG->wwwroot . '/course/view.php?id=' . $courseid . '" aria-label="'.get_string('getcertification', 'block_rlms_courserecords').'" ><i class="wid wid-icon-phcertificate fa fa-certificate"></i>'.get_string("no_certificate", "block_rlms_courserecords").'</a>';
            $table .= '</div>';
        }
        $table .= "<div class='responsive_my_records col-md-2 col-sm-2'><span class='final_grade_block'>".$finalgrade."</div>";
        $table .= '<div class="responsive_my_records col-md-2 col-sm-2"><span class="completed_dates"> '. ucfirst(userdate($transcripttime,'%B %d, %Y')).'</span></div>';

        $table .= '</div>';
        $coursescount++;
    }
     
    // if courses completed variable is equal to 0 return a warning alert
    if($coursescount == 0){
        $table .= html_writer::start_tag('div',array("class"=> "tab-noresult-row item"));
        $table .= html_writer::start_tag('span');
        $table .= html_writer::empty_tag('image', array('src'=>$CFG->wwwroot . '/theme/rlms/pix/noresult.png', 'class'=>'no result_img'));
        $table .= get_string('tabtablenoresult','theme_rlms');
        $table .= html_writer::end_tag('span');
        $table .= html_writer::end_tag('div');
    }

    /* end of  the completion loop */
    $table .= "</div>";
    $table .= "</div>";

    // return table results
    return $table;
}


function rlms_courserecords_myrecords_get_final_grade($courseid, $userid)
{
    global $CFG, $DB;
    
    require_once($CFG->dirroot . '/grade/lib.php');
    require_once($CFG->dirroot . '/grade/report/user/lib.php');
    
    $course = $DB->get_record('course', array('id' => $courseid));
    $context = context_course::instance($course->id);
    
    $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'user', 'courseid'=>$courseid, 'userid'=>$userid));
    $report = new grade_report_user($courseid, $gpr, $context, $userid);
    $report->fill_table();
    $html = $report->print_table(true);
    
    $dom = new \DOMDocument();
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    @$dom->loadHtml($html);
    
    $domTd = $dom->getElementsByTagName('td');
    
    $r = '';
    foreach ($domTd as $td) {
        if($td->hasAttribute('headers')) {
            if (stripos($td->getAttribute('headers'), 'grade') !== false && stripos($td->getAttribute('headers'), 'lettergrade') === false) {

                $r = $td->textContent;
                if (strtolower($r)==='error' || trim($r) === '-' ) {
                    $r = get_string('no_applicable', "block_rlms_courserecords");
                }else{
                   // $r .=' '.get_string('points', "block_rlms_courserecords");
                }
            }
        }
    }
    return $r;
}