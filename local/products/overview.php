<?php
/**
 * Displays courses overview
 *
 * @package   local_products
 * @author    Jayesh
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/local/products/lib.php');

$context = context_system::instance();

global $CFG, $OUTPUT, $PAGE;
$PAGE->requires->css(new moodle_url("/local/products/styles.css", array('v' => random_int(2,4))));
// $PAGE->requires->js_call_amd('local_products/products', 'init');
$PAGE->set_context($context);

$id = required_param('id', PARAM_INT);

$PAGE->set_url(new moodle_url('/local/products/', array('id' => $id)));

$course = get_product_overview($id);
$sql = 'SELECT courseid,
        MAX(cost) as cost,
        MAX(currency) as currency
        FROM mdl_enrol
        WHERE courseid = :courseid';
$courseprice = $DB->get_record_sql($sql, array('courseid' => $id));
$coursecontent = $DB->get_record('course_info_field',array('shortname'=>'coursecontent'));
$price = $DB->get_record('course_info_field',array('shortname'=>'price'));
if($coursecontent)
{
   $ccontentvalue = $DB->get_record('course_info_data',array('courseid'=>$id ,'fieldid'=>$coursecontent->id));
}
if($price)
{
   $pricevalue = $DB->get_record('course_info_data',array('courseid'=>$id ,'fieldid'=>$price->id));
}


if($pricevalue->data > 0){
    $cost = '<div class="enrolmenticons d-inline-block price-wrap">
                <strong>'.$pricevalue->data.' INR</strong>
            </div>';
}
$pagename = $course->fullname;
$PAGE->set_title($pagename);

$productsincart = get_products_in_cart();
if(in_array($course->id, $productsincart)){
    $button = '<div id="'.$course->id.'" class="pull-right addtocartbtn add-to-cart-btn in-cart" title="'.get_string('incart', 'local_products').'"><i class="fa fa-check" aria-hidden="true"></i> '.get_string('incart', 'local_products').'</div>';
} else {
    $button = '<div id="'.$course->id.'" class="pull-right addtocartbtn add-to-cart-btn" title="'.get_string('addtocart', 'local_products').'"><i class="fa fa-cart-plus" aria-hidden="true"></i> '.get_string('addtocart', 'local_products').'</div>';
}

echo $OUTPUT->header();
$courseimage = get_course_image($course->id);
echo '<div class="course-overview-main-wrap">
    <h1 class="course-name" style="font-weight: 600;">'. $course->fullname .'</h1>
    <div class="content">
        <div class="img-container py-5">
            <img class="h-300 rounded" src="'.$courseimage.'" />
        </div>
        <section class="pb-5">
            <div class="row">
                <div class="col">
                    <h3>Course Overview</h3>
                    <hr>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-8">
                    <h4 class="heading-gradient">Course Summary</h4>
                    <div class="row course-summary" style="padding-top:4em; padding-bottom:4em;">
                    <div class="col-md-12">
                            <p>'.$course->summary.'</p>
                        </div>
                    </div>
                    <h4 class="heading-gradient">Course Contents</h4>
                    <div class="row" style="padding-top:4em">
                            <p>'.$ccontentvalue->data.'</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-4 text-justify course-duration">
                        <h4 class="heading-gradient" style="left:0">Duration</h4> <br> &nbsp; 
                        <p class="mt-2">
                            33 hours
                        </p>
                        '. $cost 
                        . $button .'
                    </div>
                </div>
            </div>
        </section>
    ';
        
echo '</div>
    </div>';

echo $OUTPUT->footer();