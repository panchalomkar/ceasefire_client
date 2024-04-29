<?php


defined('MOODLE_INTERNAL') || die();
    function display_rating_box($courseid){
        global $DB, $USER, $PAGE, $COURSE;
        $context = context_course::instance($COURSE->id);
        $isAllowRating = has_course_rating_allow($courseid);
        
       if($isAllowRating == "" && $isAllowRating == 0){
            return false;
        }


        $PAGE->requires->js("/local/rlmscourse_rating/javascript/course_rating.js");
        // Commented to getting fetal error because we "adding css after head load."
        //$PAGE->requires->css("/local/rlmscourse_rating/style.css");


        $canRate = has_capability('local/rlmscourse_rating:add', $context);

        $html = '';
        $cinfo = new completion_info($COURSE);
        $iscomplete = $cinfo->is_course_complete($USER->id); 
        
        if(isset($courseid)){
            $ratingArr = $DB->get_records('local_rlmscourse_rating', array('courseid' => $courseid,'userid' => $USER->id));
        } else {
           // $ratingArr = $DB->get_records('local_rlmscourse_rating', null);
        }

        $html ="";
        if(!empty($ratingArr) ){
            foreach ($ratingArr as $result) {

                $html .= html_writer::start_tag('div', array('id' => "course-$result->id"));
                $html .= html_writer::empty_tag('input',['type' => 'hidden','name' => 'rating', 'id' => 'rating' , 'value' => $result->rating]);
                $html .= html_writer::start_tag('ul', array("id" => $courseid, "class" => 'course_rating_list'));
                for($iCount = 1; $iCount <= 5; $iCount++) {
                    $selected = "none";
                    if(!empty($result->rating) && $iCount <= $result->rating) {
                        $selected = "selected";
                    }
                     $html .= html_writer::start_tag('li', array("class" => $selected));
                      $html .= html_writer::tag('i','',['class' => 'fa fa-star ']);
                     $html .=html_writer::end_tag('li');
                }
                $html .= html_writer::end_tag('ul');
                $html .=  get_total_count_by_rating($courseid);
                $html .= html_writer::end_tag('div');
            }
        } else {
            if($canRate && !is_siteadmin() ){
                $html .= html_writer::start_tag('div', array('id' => "course-0"));
                    $html .= html_writer::empty_tag('input',['type' => 'hidden','name' => 'rating', 'id' => 'rating' , 'value' => 0]);
                    $html .= html_writer::start_tag('ul', array('onMouseOut' => "resetRating(0)", "id" => $courseid, "class" => 'course_rating_list', "tooltip" => "Please wait")); 
                    for($i=1; $i<=5; $i++) {
                        if($iscomplete == 1){
                            $html .= html_writer::start_tag('li', array('onmouseover' => "highlightStar(this,0)",'onMouseLeave' => "resetRating(0)", "onClick" => "addRating(this,0,$USER->id,$courseid)"));
                              $html .= html_writer::tag('i','',['class' => 'icon fa fa-star ']);
                            $html .=html_writer::end_tag('li');
                        } else {
                            $tooltipText = get_string('nocoursecomplete', 'local_rlmscourse_rating');
                            $html .= html_writer::start_tag('li', array("data-toggle" => "tooltip", "data-placement" => "bottom", "title" => "$tooltipText"));
                              $html .= html_writer::tag('i','',['class' => 'icon fa fa-star ']);
                            $html .=html_writer::end_tag('li');
                        }
                    }
                    $html .= html_writer::end_tag('ul');
                    $html .=  get_total_count_by_rating($courseid);
                $html .= html_writer::end_tag('div');
            } else {
                $html .= get_total_avg_rating($courseid);
            }
        }
        
        
        return $html;
    }


    function has_course_rating_allow($course) {
        global $DB;
        $course_info_field =  $DB->get_record_sql("Select id from {customfield_field} Where shortname = ? ", array('courserating'));     
        $fieldid = $course_info_field->id;
        $course_info_data =  $DB->get_record_sql("Select data from {customfield_data} Where fieldid = ?  and courseid = ? ", array($fieldid,$course));
        $courserating = $course_info_data->data;
        return $courserating;
    }

    function get_total_avg_rating($course){
        global $DB;
        $max = 0;
        $html ="";
        $avgRating = 0;
        $courseRatings =  $DB->get_records_sql("SELECT rating from {local_rlmscourse_rating} WHERE courseid = ?", array($course));
        $totalRecord =  $DB->count_records("local_rlmscourse_rating", array("courseid" => $course));
        foreach($courseRatings as $ratings){
            $totalRate = $ratings->rating;
            $max = ($max + $totalRate);
            $avgRating = ceil($max / $totalRecord);

        }
        if($avgRating > 0 ){
            $html .= html_writer::start_tag('div', array('id' => "course-0"));
                $html .= html_writer::empty_tag('input',['type' => 'hidden','name' => 'rating', 'id' => 'rating' , 'value' => 0]);
                $html .= html_writer::start_tag('ul', array("id" => $course, "class" => 'course_rating_list', "tooltip" => "Please wait"));
                for($iCount = 1; $iCount <= 5; $iCount++) {
                    $selected = "none";
                    if( $iCount <= $avgRating) {
                        $selected = "selected";
                    }
                     $html .= html_writer::start_tag('li', array("class" => $selected));
                      $html .= html_writer::tag('i','',['class' => 'fa fa-star ']);
                     $html .=html_writer::end_tag('li');
                }
                $html .= html_writer::end_tag('ul');
                $html .=  get_total_count_by_rating($course);
                //$avgText = "<i>Total Avg Rating $avgRating</i>";
               // $html .= $avgText;
            $html .= html_writer::end_tag('div');
        }
        
        return $html;
    }

    /**
     * 
     * Return the avg rating of course . used in course filter
     * @author Dnyaneshwar K.
     * @ticket #619
     * @Dated 19-07-2019
     * @return int avgRating 
     * 
     */
    function get_course_rating($courseid){
        global $DB;
        $courseRatings =  $DB->get_records_sql("SELECT AVG(rating) as avg from {local_rlmscourse_rating} WHERE courseid = ? GROUP BY courseid", array($courseid));
        //$totalRecord =  $DB->count_records("local_rlmscourse_rating", array("courseid" => $courseid));
        $avgRating = 0;
        foreach($courseRatings as $ratings){
            $totalRate = $ratings->avg;
            $avgRating = ceil($totalRate);
        }
        return $avgRating;
    }
    
    function get_total_count_by_rating($courseid){
        global $DB;
        $sqlRating = $DB->get_records_sql("SELECT rating, count(*) as total from {local_rlmscourse_rating} WHERE courseid = ? group by rating order by rating desc", array($courseid));
        $html = "<div class='popup_rating hidden'>";
        $html .= "<table class='table'>";
           
        foreach($sqlRating as $rate ){
             $html .= "<tr>";
                $html .= "<td>".$rate->rating."&nbsp;<i class='fa fa-star selected'></i><div class='progress'>
  <div class='progress-bar' style='width:$rate->total%'></div>
</div></td>";
                $html .= "<td>".$rate->total."</td>";
             $html .= "</tr>"; 
        }
         $html .= "</table>"; 
         $html .= "</div>"; 
        return $html;
    }
