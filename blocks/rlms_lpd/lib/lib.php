<?php

define('MAX_PAGE_SIZE', 10); 
//require_login();

function displayLearningPathsView($option,$userid) {
    $url = new moodle_url('/blocks/rlms_lpd/block_rlms_lpd.php');
    $urlCourse = new moodle_url('/local/elisprogram/widgets/enrolment/ajax.php');
    $urlCourseDetail = new moodle_url('/course/view.php');
    
    $content = html_writer::start_tag('div', array('id' => 'block_rlms_lpd_content', 'class' => 'block_rlms_lpd_content container-fluid', 'data-children' => '.itemlpd'));
    $content .= getLearningPathsView($option,$userid);
    $content .= html_writer::end_tag('div');
    $content .= html_writer::tag('input', '', array('id' => 'txtUrlDetail', 'type' => 'hidden', 'value' => "{$url}"));
    $content .= html_writer::tag('input', '', array('id' => 'txtUrlCourse', 'type' => 'hidden', 'value' => "{$urlCourse}"));
    $content .= html_writer::tag('input', '', array('id' => 'txtUrlCourseDetail', 'type' => 'hidden', 'value' => "{$urlCourseDetail}"));

    return $content;
} 


function getLearningPathsView($option,$userid) {
    global $DB, $SESSION, $USER, $OUTPUT;
    $page = optional_param('page_course', 0, PARAM_INT);
        $dashboard_per_page = optional_param('courseperpage', 10, PARAM_INT);
    $content    = "";
    $result     = getLearningPathInfo($userid);
    //return '' ;
     
    $lpname = get_string('learning_name', 'block_rlms_lpd');
    $date = get_string('startdate', 'block_rlms_lpd');
    $end_date = get_string('enddate', 'block_rlms_lpd');
    $status = get_string('status', 'block_rlms_lpd');
    $credit = get_string('credits', 'block_rlms_lpd');

    $content .= html_writer::start_tag('div', array('class' => 'headinglpd'));
        $content .= html_writer::start_tag('div', array('class' => 'lpd-heading row'));
            
            $content .= html_writer::start_tag('div', array('class' => 'col-8 col-sm-6 col-md-6 col-lg-3 col-xl-3'));
                $content .= html_writer::tag('span', $lpname, array('class' => 'lp-name hd-lp'));
            $content .= html_writer::end_tag('div');

            $content .= html_writer::start_tag('div', array('class' => 'start-date hidden-md col-lg-2 col-xl-2'));
                $content .= html_writer::tag('span',$date , array('class' => 'lp-startdate hd-lp'));
            $content .= html_writer::end_tag('div');

            $content .= html_writer::start_tag('div', array('class' => 'lpd-lp-head-column end-date hidden-md col-lg-2 col-xl-2'));
                $content .= html_writer::tag('span',$end_date , array('class' => 'lp-enddate hd-lp'));
            $content .= html_writer::end_tag('div');

            $content .= html_writer::start_tag('div', array('class' => 'lpd-lp-head-column status col-4 col-sm-4 col-md-4 col-lg-3 col-xl-3'));
                $content .= html_writer::tag('span',$status , array('class' => 'lp-status hd-lp'));
            $content .= html_writer::end_tag('div');

            $content .= html_writer::start_tag('div', array('class' => 'lpd-lp-head-column credits hidden-xs col-sm-2 col-md-2 col-lg-2'));
                $content .= html_writer::tag('span',$credit , array('class' => 'lp-credits hd-lp'));
            $content .= html_writer::end_tag('div');

        $content .= html_writer::end_tag('div');
    $content .= html_writer::end_tag('div');

    $content .= html_writer::start_tag('div', array('class' => 'learningpaths-content scroll-lp'));
        $li_totals = count($result);
        $la_index  = array_keys($result);

                $la_pag_learninpath = array();

                if( $dashboard_per_page >10) $page = 0;

                for( $record=($page * $dashboard_per_page); $record < (( $page * $dashboard_per_page ) + $dashboard_per_page) ; $record++ ) {
                   if($result[ $la_index[$record] ]) $la_pag_learninpath[ $la_index[$record] ] = $result[ $la_index[$record] ];
                }
                $pages = count($result) / $dashboard_per_page;
                $active_page = 1;
        if(count($result) > 0) {
            $of     = get_string('of', 'block_rlms_lpd');
            foreach ($la_pag_learninpath as $index => $value) {
                
                $startDate      = '';
                $idConcat = 'block_rlms_lpd_'.$value->id;
                if($value->startdate) {
                    $startDate      = ucfirst(userdate($value->startdate,'%b %d, %Y'));
                }
                $completeDate   = '';
                if($value->enddate) {
                    $completeDate      = ucfirst(userdate($value->enddate,'%b %d, %Y'));
                }

                $companylabel = '';
                 // Added company name label , if company's LP
                if($value->companyid > 0 &&  is_siteadmin() && empty($SESSION->currenteditingcompany)){
                    $companyname = $DB->get_field('company', 'name', array('id' => $value->companyid));
                    $companylabel = '<span class="badge badge-primary companylabel">'.$companyname.'</span>';
                }

                $content .= html_writer::start_tag('div', array('class' => 'lpd-lp-content itemlpd row'));

                    $content .= html_writer::start_tag('div', array('data-lpid'=>$value->id,'class' => 'lpd-lp-content-header collapsed collapsedlpd col-12 col-sm-12 col-md-12 col-lg-12', 'data-toggle' => 'collapse', 'data-parent' => '#block_rlms_lpd_content', 'href' => "#{$idConcat}", 'aria-expanded' => 'true', 'aria-controls' => "{$idConcat}"));
                        $content .= html_writer::start_tag('div', array('class' => 'lpd-lp-body-column course-name col-7 col-sm-6 col-md-6 col-lg-3 col-xl-3'));
                            $content .= html_writer::tag('span',$value->lpname);
                            $content .= $companylabel;
                        $content .= html_writer::end_tag('div');

                        $content .= html_writer::tag('div', $startDate, array('class' => 'lpd-lp-body-column start-date hidden-md col-md-2 col-lg-2 col-xl-2'));
                        $content .= html_writer::tag('div', $completeDate, array('class' => 'lpd-lp-body-column end-date hidden-md col-md-2 col-lg-2 col-xl-2'));
                        $content .= html_writer::start_tag('div', array('class' => 'lpd-lp-body-column status col-4 col-sm-4 col-md-4 col-lg-3 col-xl-3'));
                                
                            $lpprogress = getLpProgress($value->id,$option,$userid);

                            $content .= html_writer::tag('span',$lpprogress."%");
                            
                            $content .= html_writer::start_tag('div', array('class' => 'progress'));
                                $content .= html_writer::start_tag('div', array('style' => "width: ".$lpprogress."%", 'class' => 'progress-bar', 'role' => 'progressbar', 'aria-valuenow' => $lpprogress, 'aria-valuemin' => '0', 'aria-valuemax' => '100'));
                                $content .= html_writer::end_tag('div');                                
                            $content .= html_writer::end_tag('div');

                        $content .= html_writer::end_tag('div');    
                        
                        $content .= html_writer::tag('div', $value->credits, array('class' => 'lpd-lp-body-column credits hidden-xs col-sm-2 col-md-2 col-lg-2'));
                                                                                                               
                    $content .= html_writer::end_tag('div');
                    
                    $content .= html_writer::tag('div', '', array('class' => 'lpd-lp-detail container-fluid pl-0'));
                $content .= html_writer::end_tag('div');
            }
            if ($li_totals > 10){
                    $content .= html_writer::start_tag('div', array('class'=>' mar-btm col-sm-12 mar-no pad-no mar-btm pagination_lpdd'));
                        $content .= html_writer::start_tag('ul');
                           
                            $content .= html_writer::start_tag('li');
                                if ($pages > 1)
            $lpid = $value->id;
             $content .= $OUTPUT->paging_bar(count($result), $page, $dashboard_per_page,'?id='.$lpid.'&tab=courses','page_course');

                            $content .= html_writer::end_tag('li');
                        $content .= html_writer::end_tag('ul');
                    $content .= html_writer::end_tag('div');//Close pagination div
                
                }

            //$content .= html_writer::end_tag('ul');
        } else {
            $content .= html_writer::tag('div', get_string('noresultslp', 'block_rlms_lpd'), array('class' => ' text-bold lpd-lp-detail col-sm-12 col-md-12 col-lg-12 alert alert-mtlms text-center'));
        }
    $content .= html_writer::end_tag('div');
    return $content;
}

/**
 * Return the learningpaths information of a user
 */
function getLearningPathInfo($userid) {
    global $DB, $OUTPUT, $SESSION;
    
    // if site admin, show all learningpaths
    if(is_siteadmin()){
        $sql = "SELECT lp.id, lp.name as lpname, lp.description, lp.credits, lp.startdate, lp.enddate, lp.companyid  
                FROM {learningpaths} as lp WHERE lp.deleted =0 ";

        $learningpaths = $DB->get_records_sql($sql);
        return $learningpaths;
    }
  
    $sql = " 
        SELECT lp.id, lp.name as lpname, lp.description, lp.credits, lp.startdate, lp.enddate 
        FROM {learningpaths} as lp 
        JOIN {learningpath_courses} as lpc on lp.id = lpc.learningpathid
        JOIN {learningpath_users} as lpu on lpu.learningpathid = lp.id
        WHERE lp.deleted = ?
    ";
    
    // It's for just an user?
    if ($userid) {
        $sql .= " AND lpu.userid = {$userid}";
    }

    // Get records and return
    $learningpaths = $DB->get_records_sql($sql, [0]);

    // It's for just an user?
    if ($userid) {
        //Get learning path where user was added by cohort
        $sql = "
            SELECT lp.id, lp.name AS lpname, lp.description, lp.credits, lp.startdate, lp.enddate
            FROM {learningpaths} AS lp
            JOIN {learningpath_courses} AS lpc ON lp.id = lpc.learningpathid
            JOIN {learningpath_cohorts} AS lpu ON lpu.learningpathid = lp.id
            JOIN {cohort_members} AS members ON lpu.cohortid = members.cohortid
            WHERE lp.deleted = ?
        ";

        // It's for just an user?
        if ($userid) {
            $sql .= " AND members.userid = {$userid}";
        }

        $learningpathcohorts = $DB->get_records_sql($sql, [0]);
        $learningpaths = array_merge($learningpaths, $learningpathcohorts);
    }

    return $learningpaths;
}

function displayLearningPathsViewDetail($learningPath, $userid, $page = 0, $lpid_selected = 0, $pageTypeLocalAjax = false) {
        global $CFG ,$PAGE, $OUTPUT, $DB;
        $inoverview = ($PAGE->pagetype == 'local-learningpaths-view' || $pageTypeLocalAjax) ? true : false ;
        define("MAX_LP_PAGE", 10);
        
        // Require completion library
        require_once "{$CFG->dirroot}/lib/completionlib.php";
        
        $content    = "";
        $params = [];
        if ($lpid_selected != $learningPath && !$inoverview) {
            $page = 0;
        }

        $offset = MAX_LP_PAGE * $page;
        $params["lp_id"] = $learningPath;
        $params["id"] = $learningPath;
        $baseurl = new moodle_url('', $params);
        // Get learningpath courses and count
        $result = getCoursesInfo($learningPath, false, $userid, MAX_LP_PAGE, $offset);
        $countCourses = count(getCoursesInfo($learningPath,true,$userid));
        
        // If learning path has courses
        if(count($result) > 0) {
            $content .= html_writer::start_tag('div', array('class' => 'lpd-lp-content'));
                
                // Open details container
                $content .= html_writer::start_tag('div', array('class' => 'lpd-lp-detail-head col-12 col-sm-12 pl-0 pr-0'));                
                    // Details container
                    $content .= html_writer::tag('div','', array('class' => 'lpd-lp-detail-head-column col-2 col-sm-2 col-md-1 col-lg-1'));
                    
                    $content .= html_writer::start_tag('div', array('class' => 'col-10 col-sm-10 col-md-11 col-lg-11 pl-0 d-flex'));
                        // is in learning path overview?
                        if (!$inoverview) {
                            $attrs = array('class' => 'lpd-lp-detail-head-column course-name col-8 col-sm-6 col-md-6 col-lg-6 col-xl-3 line-lp');
                            $content .= html_writer::tag('div', get_string('coursename', 'block_rlms_lpd'), $attrs);
                        } else {
                            $attrs = array('class' => 'lpd-lp-detail-head-column course-name line-lp col-8 col-sm-6 col-md-6 col-lg-6 col-xl-2 col-xl-3 col-md-3');
                            $content .= html_writer::tag('div', get_string('coursename', 'block_rlms_lpd'), $attrs);
                        }
                    
                        // Start date
                        $attrs = array('class' => 'lpd-lp-detail-head-column start-date col-xl-2 hidden-lg line-lp');
                        $content .= html_writer::tag('div', get_string('startdate', 'block_rlms_lpd'), $attrs);

                        // End date
                        $attrs = array('class' => 'lpd-lp-detail-head-column end-date col-xl-2 hidden-lg line-lp');
                        $content .= html_writer::tag('div', get_string('enddate', 'block_rlms_lpd'), $attrs);
                    
                        // Progress
                        if (!$inoverview) {
                            $attrs = array('class' => 'lpd-lp-detail-head-column progress-column col-4 col-sm-4 col-md-4 col-lg-4 col-xl-2 line-lp line-lp');
                            $content .= html_writer::tag('div', get_string('progress', 'block_rlms_lpd'), $attrs);
                        }
                    
                        // Credits
                        // $attrs = array('class' => 'lpd-lp-detail-head-column credits hidden-lg  col-xl-1 line-lp');
                        // $content .= html_writer::tag('div', get_string('credits', 'block_rlms_lpd'), $attrs);
                    
                        // Certificate
                        $attrs = array('class' => 'lpd-lp-detail-head-column certificate hidden-xs col-xl-2 line-lp');
                        $content .= html_writer::tag('div', get_string('certificate', 'block_rlms_lpd'), $attrs);
                    $content .= html_writer::end_tag('div');
                $content .= html_writer::end_tag('div');

                // Learning path details
                $attrs = array('class' => "lpd-lp-detail-body col-12 col-sm-12 col-md-12 col-lg-12 pl-0 pr-0", 'data-type'=> '', 'data-line' => '');
                $content .= html_writer::start_tag('div', $attrs);

                // Flag to count number of courses
                $count = 1;                
                foreach ($result as $key => $course) {
                    $element = '' ;
                    
                    // Which course is it showing?
                    if ($count == 1 && count($result) > 1) {
                        $element = 'first' ;
                    }

                    if ($count == count( $result )) {
                        $element = 'last' ;
                    }

                    if ($count > 1 && ($count < count($result))) {
                        $element = 'middle' ;
                    }

                    // Course row
                    $content .= html_writer::start_tag('div', array('class' => "lp-course-row row pl-0 pr-0 "));
                    $canvas = html_writer::tag('canvas','',array( 'data-element'=> $element ,'id'=>$course->id,"class"=>"canvas" ,"width"=>"30" ,"height"=>"30"));
                    
                    // Get rerequisites
                    $prerequisites = getCoursePrerequisite($course->learningpath_course);
                    $prereq = getPrereqHTMl($prerequisites);

                        // When course has prerequisites, calculate completion of those
                        if ($prereq && (int) $course->progress <> 100) {
                            
                            // Flag to count courses completed
                            $req_completed = 0;
                            foreach ($prerequisites as $pre) {
                                $course_prereq = $DB->get_record('course', ['id' => $pre->id]);
                                $completion = new completion_info($course_prereq);
                                $req_completed = ($completion->is_course_complete($userid)) ? $req_completed + 1 : $req_completed;
                            }
                            
                            // Show icon depending if all prerequisites were completed or not
                            if ($req_completed == count($prerequisites)) {
                                $attrs = array (
                                    'data-toggle'=>'tooltip',
                                    'title' => $prereq,
                                    'data-original-title' => $prereq,
                                    'data-color' => '#94b7c3',
                                    'class' => 'wid wid-icon-course-inprogress tooltipelement_html'
                                );
                                $icon = html_writer::tag('i', '', $attrs);
                            } else {
                                $attrs = array (
                                    'data-color' => '#94b7c3',
                                    'data-original-title' => $prereq,
                                    'data-toggle'=>'tooltip',
                                    'title' => $prereq,
                                    'class' => 'red wid wid-bloki fa fa-lock tooltipelement_html icon_block'
                                );
                                $icon = html_writer::tag('i', '', $attrs);
                            }
                        } elseif ((int) $course->progress == 100) {
                            if ($prereq) {
                                $attrs = array (
                                    'data-toggle'=>'tooltip',
                                    'title' => $prereq,
                                    'data-original-title' => $prereq,
                                    'data-color' => '#94b7c3',
                                    'class' => 'wid wid-icon-checked icon_ok fa fa-check tooltipelement_html'
                                );
                                $icon = html_writer::tag('i', '', $attrs);
                            } else {
                                $icon = html_writer::tag('i','', array('data-color'=>'#94b7c3', 'class' => 'wid wid-icon-checked icon_ok fa fa-check'));
                            }
                        } else {
                            $icon = html_writer::tag('i','',array('data-color'=>'#94b7c3','class'=>'fa fa-circle-thin'));
                        }

                        // Get the course name and validate if is longer than 40 chars.
                        $coursename = (strlen($course->name) >= 40)?substr($course->name, 0, 40).'...':$course->name;

                        $content .= html_writer::tag('div', $canvas.$icon, array('class' => 'lp-icons col-2 col-sm-2 col-md-1 col-lg-1 col-xl-1'));

                        $content .= html_writer::start_tag('div' , array('class' => 'lp_dashboard col-10 col-sm-10 col-md-11 col-lg-11 col-xl-11 pl-0 d-flex'));

                            $courselink = html_writer::tag('a',  $coursename , array('href' => $CFG->wwwroot.'/course/view.php?id='.$course->id , 'target'=>'_blank' )); ;
                            if(!$inoverview){
                                $content .= html_writer::tag('div', $courselink , array('class' => 'lp-file course-name col-8 col-sm-6 col-md-6 col-lg-6 col-xl-3'));
                            }else{
                                $content .= html_writer::tag('div', $courselink , array('class' => 'lp-file course-name col-sm-10 col-md-4 col-lg-4 col-xl-3 '));
                            }

                            $content .= html_writer::tag('div', ucfirst(userdate($course->startdate,'%b %d, %Y')), array('class' => 'lp-file start-date col-xl-2  hidden-lg'));
                            if ($course->enddate){
                                $content .= html_writer::tag('div', ucfirst(userdate($course->enddate,'%b %d, %Y')), array('class' => ' lp-file end-date col-xl-2 hidden-lg'));
                            }else{
                                $content .= html_writer::tag('div', get_string('not_set', 'block_rlms_lpd'), array('class' => ' lp-file end-date col-xl-2 hidden-lg')); 
                            }
                            
                            if(!$inoverview){
                                $progresshtml = html_writer::tag('span',round($course->progress)."%");
                                $progresshtml .= html_writer::start_tag('div', array('class' => 'progress hidden-md'));
                                    
                                    $progresshtml .= html_writer::tag('div','', array('style' => "width: ".$course->progress."%", 'class' => 'progress-bar progress-bar-info', 'role' => 'progressbar', 'aria-valuenow' => $course->progress, 'aria-valuemin' => '0', 'aria-valuemax' => '100'));
                                $progresshtml .= html_writer::end_tag('div');

                                $content .= html_writer::tag('div', $progresshtml , array('class' => 'lp-file progress-column col-4 col-sm-4 col-md-4 col-lg-4 col-xl-2'));
                            }

                          //  $content .= html_writer::tag('div', $course->credits , array('class' => 'lp-file credits  hidden-lg col-xl-1'));

                            $certificatelink = getCourseCertificateSelected($course->id,$userid);
                
                            if ($certificatelink) {
                                $icon = $certificatelink;
                            } else {
                                $icon = html_writer::tag('i','',array('class'=>'not-issued wid wid wid-icon-phcertificate fa fa-certificate'));
                            }
                            $content .= html_writer::tag('div', $icon, array('class' => 'lp-file certificate hidden-xs col-xl-2'));
                        $content .= html_writer::end_tag('div');
                    $content .= html_writer::end_tag('div');
                    $count ++ ;
                }
                
            $content .= html_writer::end_tag('div') ;
            $content .= $OUTPUT->paging_bar($countCourses, $page, MAX_PAGE_SIZE, $baseurl);
        } else {
            return html_writer::tag('div', get_string('nocourseslp', 'block_rlms_lpd'), array('class' => 'text-bold lpd-lp-detail col-sm-12 col-md-12 col-lg-12 alert alert-mtlms text-center'));
        }
        $content .= html_writer::end_tag('div');
    return $content;
}


function getCoursesInfo($learningPath,$getcoursesonly = false,$userid, $limit = null, $offset = null) {
    
        global $DB;
        $arrayCD  = $courseObj   = array();
        $sqlLimit = "";
        if(!is_null($limit) && !is_null($offset) && $limit > 0){
            $limit = (int)$limit;
            $offset = (int)$offset;
            $sqlLimit = " LIMIT {$limit} OFFSET {$offset}";
        }
        /* get course properties credits and enddate */
        $fieldcredits = $DB->get_record('course_info_field',array('shortname'=>'credits'));
        //$fieldendate = $DB->get_record('course_info_field',array('shortname'=>'enddate'));

        $sql = "
            SELECT  c.id,
                    c.fullname,
                    c.startdate ,
                    c.enddate ,
                    c.timecreated,
                    lpc.id as learningpath_course
            FROM 
                {learningpath_courses} as lpc , {course} as c 
            WHERE 
            lpc.courseid = c.id AND
            lpc.learningpathid = ".$learningPath." ORDER BY position ASC {$sqlLimit}";
    
        /* get LP courses */
        $lpcourses = $DB->get_records_sql($sql);
        if($getcoursesonly && $lpcourses )
        {
            return $lpcourses; 
        }
        if( count($lpcourses) > 0 ){
            foreach ($lpcourses as $key => $course) {
            
            $coursestd = new stdClass();

            $creditsvalue = '';
            $enddatevalue = '';

            /*if course properties credits and enddate exist and they have data then search specific course value */
            if($fieldcredits)
            {
               $creditsvalue = $DB->get_record('course_info_data',array('courseid'=>$course->id ,'fieldid'=>$fieldcredits->id));
            }
            if($fieldendate)
            {
                //$enddatevalue = $DB->get_record('course_info_data',array('courseid'=>$course->id ,'fieldid'=>$fieldendate->id));
            }

            $coursestd->id = $course->id; 
            $coursestd->name = $course->fullname; 
            if(empty($course->startdate)){
                $coursestd->startdate = $course->timecreated;
            }else{
                $coursestd->startdate = $course->startdate;
            }
            $coursestd->learningpath_course = $course->learningpath_course;
            
            if($creditsvalue == null)
            {
                $coursestd->credits = 0; 
            }else
            {
                $coursestd->credits = $creditsvalue->data;
            }

            if($enddatevalue)
            {
                $coursestd->enddate = $enddatevalue->data; 
            }else
            {
                 $coursestd->enddate = $course->enddate; 
            } 

            $progress = getCourseProgress($course->id,$userid);

            $coursestd->progress = $progress ;
            $courseObj[$course->id] = $coursestd ;

        }
    }   

    return $courseObj;
}

function getCourseProgress($courseid,$userid)
{
    global $DB, $USER;
    $total_progress = 0 ;

    $course = $DB->get_record('course',array('id'=>$courseid));

    //Get mod info
    $modinfo = get_fast_modinfo($course);
    //Get the completion info of the course
    $info = new completion_info($course);
    $complete = $info->is_course_complete($userid);
    if($complete){
        $total_progress = 100;
    } else{
        //check if the current user is enrolled in the current course
        $my_courses = enrol_get_my_courses();
        $is_enrollled = false;
        if (isset($my_courses[$course->id]->id))
            $is_enrollled = true;

        //If eht completion info is enabled for the site and for the course
        if (completion_info::is_enabled_for_site() && $info->is_enabled()) {
            //Get the completions for current user
            $completions = $info->get_completions($userid);


             // For aggregating activity completion.
            $activities = array();
            $activities_complete = 0;

             // Loop through course criteria.
            foreach ($completions as $completion) {
                //If is a videofile get the progress of the user in the video
                $criteria = $completion->get_criteria();
                $complete = $completion->is_complete();

                // Activities are a special case, so cache them and leave them till last.
                if ($criteria->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {
                    $activities[$criteria->moduleinstance] = $complete;

                    if ($complete) {

                        $activities_complete++;

                    } else if($completion->get_criteria()->module == 'videofile' ){
                        /**
                        * commented because it is generating mistakes to calculate the progress with video activities. 
                        * @author Sergio A.
                        * @since Feb 14 of 2018
                        * @rlms
                        */
                        /*$cm = $modinfo->cms[$completion->get_criteria()->moduleinstance];
                        $videoprogress = $DB->get_record_sql('SELECT percentage FROM {video_attempts} WHERE cmid = '.$cm->id.' AND userid = '.$USER->id.' ORDER BY percentage DESC LIMIT 0, 1');
                        if($videoprogress){
                            $activities_complete = $activities_complete + (intval($videoprogress->percentage) / 100);
                        }*/
                    }

                }
                else if($complete)
                {
                    $activities_complete++;
                }
            }

            if ( count($activities) > 0 ) {
                $total_progress = ($activities_complete / count($activities));
                $total_progress = $total_progress * 100;
            }else
            {
               $total_progress = 0 ; 
            }
        }
    }

    return $total_progress ;
}

function getLPProgressCourseCompleted($learningpathid,$userid)
{   
    global $DB;
  
    $courses = getCoursesInfo($learningpathid,true,$userid);
    $coursesCompleted = array();
    $countCourses = 0;
    // Check if there is courses
    if( count($courses) > 0 ){
        foreach ($courses as $key => $coure) {
            $courseprogress = getCourseProgress($coure->id,$userid) ;
            $required = $DB->get_record('learningpath_courses',array('learningpathid'=>$learningpathid,'courseid' => $coure->id))->required;
            if ($required) {
                $countCourses ++;
            }
            if( (int)$courseprogress == 100 && $required)
            {
                $coursesCompleted[$coure->id] = 1;    
            } 
        }
    }

    if( count($coursesCompleted) > 0 ) {
        $lpprogress =  ( ( count($coursesCompleted) * 100 ) / $countCourses ) ;
    }else
    {
        $lpprogress = 0 ;
    } 
    
    return round($lpprogress) ;

}

function getLPProgressCreditsEarned($learningpathid,$userid)
{
    global $USER ,$DB ;
    
    $courses = getCoursesInfo($learningpathid,false,$userid);
    $lpinfo = $DB->get_record('learningpaths',array('id'=>$learningpathid));
    $coursesCompleted = 0 ;

    foreach ($courses as $key => $coure) {
        $courseprogress = getCourseProgress($coure->id,$userid) ;
        if( (int)$courseprogress == 100 )
        {
            $coursesCompleted += $coure->credits;    
        } 
    }

    if( $coursesCompleted > 0 ) {
        $lpprogress =  ( ( (int)$coursesCompleted * 100 ) / (int)$lpinfo->credits ) ;
    }else
    {
        $lpprogress = 0 ;
    }
    return $lpprogress ;

}

function getLpProgress($learningpathid,$option,$userid) 
{
    switch ($option) {
        case 1:
           return getLPProgressCourseCompleted($learningpathid,$userid) ;
            break; 
        case 2:
           return getLPProgressCreditsEarned($learningpathid,$userid) ;
            break;
    }
}

/**
* Get course prerequisites using learning path course id
*/
function getCoursePrerequisite($learningpath_courseid)
{
    global $DB;
    $sql = "SELECT {course}.id as id, {course}.fullname as fullname
            FROM {learningpath_course_prereq}
            INNER JOIN {course} ON {course}.id = {learningpath_course_prereq}.prerequisite
            WHERE {learningpath_course_prereq}.learningpath_courseid = ? {$company_courses_sql} ORDER BY {learningpath_course_prereq}.prerequisite";
    $prerequisites = $DB->get_records_sql($sql, array('courseid'=> $learningpath_courseid));
    return $prerequisites ;
}

function getPrereqHTMl($courses)
{
    $content = '';
    if(count($courses) > 0)
    {    
        $content = get_string('prereq', 'block_rlms_lpd'); 
        $html=  strip_tags($content, array('class'=>'tooltip_title'));
        foreach ($courses as $key => $course) {
            $content.= strip_tags(" • ".($course->fullname));
        }
    }

    return $content ;
}

function getCourseCertificateSelected($courseid ,$userid) 
{
    global $DB,$CFG ;   
    
    /* get course properties credits and enddate */
    $fieldcertificate = $DB->get_record('course_info_field',array('shortname'=>'certificate'));
    if ($fieldcertificate == null) {
        $fieldcertificate = 0;
    }
    
    $data = $DB->get_record('course_info_data',array('courseid'=>$courseid , 'fieldid' => $fieldcertificate->id ));
    
    $output  ='' ;
    if($data)
    {
        // Load completion library
        require_once "{$CFG->dirroot}/lib/completionlib.php";
        $course = $DB->get_record('course', ['id' => $courseid]);
        $completion = new completion_info($course);
        if ($completion->is_course_complete($userid)) {
            $getissued = getcertissuedlpd($userid, $courseid ,$data->data);
            if($getissued->id != '')
            {
                $output = '
                <a href="' . $CFG->wwwroot . '/mod/certificate/view.php?id=' . $getissued->id . '&action=get" target="_blank" aria-label="">
                    <i class="issued wid wid-icon-phcertificate fa fa-certificate"></i>
                </a>';  
            }
        }
    }

    return $output ; 
}

/**
 * Issued certificate.
 */
function getcertissuedlpd($userid, $course, $activity) {
    if (!$activity) {
        return null;
    }
    global $CFG, $DB;
    $certificatemod = $DB->get_field('modules', 'id', array('name' => 'certificate'));
    $getissued = $DB->get_record_sql('
        SELECT cm.id 
        FROM {course_modules} AS cm,
        {certificate_issues} AS ci,
        {certificate} AS c 
        WHERE cm.course='.$course.'
        AND cm.course=c.course
        AND cm.instance ='.(int)$activity.'
        AND cm.module = '.$certificatemod
    );

    if($getissued) {
        return $getissued;
    } else {
        return null;
    }
}
