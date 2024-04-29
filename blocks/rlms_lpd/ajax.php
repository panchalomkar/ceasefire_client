<?php

require('../../config.php');

global $CFG, $DB,$PAGE,$USER,$SESSION,$OUTPUT;

require_once($CFG->dirroot."/blocks/rlms_lpd/lib/lib.php");
$html ='';
$page = optional_param('page', 0, PARAM_INT);
$userid = optional_param('id',$USER->id, PARAM_INT);
$dashboard_per_page = optional_param('courseperpage', 10, PARAM_INT);
$option = optional_param('option', 1, PARAM_INT);
if($userid <> 0){
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
            $lpid = $value->learningpathid;
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
    echo $content;

}
exit();

echo "hi"; 
?>