<?php
/**
 * Displays courses
 *
 * @package   local_products
 * @author    Jayesh
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/local/products/lib.php');

// require_login();

$context = context_system::instance();

global $CFG, $OUTPUT, $PAGE;
$PAGE->requires->css(new moodle_url("/local/products/styles.css", array('v' => random_int(2,4))));
// $PAGE->requires->js_call_amd('local_products/products', 'init');
$PAGE->set_context($context);

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', PER_PAGE, PARAM_INT);
$grid = 4;
$params = array();
if(!empty($page)){
    $params['page'] = $page;
}

$PAGE->set_url(new moodle_url('/local/products/', $params));

if(!empty($perpage)){
    $params['perpage'] = $perpage;
}

$pagename = get_string("pluginname", "local_products");
$PAGE->set_title($pagename);
$PAGE->set_heading($pagename);

echo $OUTPUT->header();

if ($page < 0) {
    $page = 0;
}

$products = get_products($params);
$productsincart = get_products_in_cart();

echo '<div class="product-main-wrap">
<div class="row content">';
foreach ($products['courses'] as $course) {
    if(in_array($course->id, $productsincart)){
        $button = '<div id="'.$course->id.'" class="pull-right addtocartbtn add-to-cart-btn in-cart" title="'.get_string('incart', 'local_products').'"><i class="fa fa-check" aria-hidden="true"></i> '.get_string('incart', 'local_products').'</div>';
    } else {
        $button = '<div id="'.$course->id.'" class="pull-right addtocartbtn add-to-cart-btn" title="'.get_string('addtocart', 'local_products').'"><i class="fa fa-cart-plus" aria-hidden="true"></i> '.get_string('addtocart', 'local_products').'</div>';
    }
    $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
    $courseimage = get_course_image($course->id);
    echo '<div class="col-lg-3 col-sm-12 col-md-6 d-flex animation-none">
            <div class="card border w-full rounded-bottom bg-white " style="">
                <div class="img-container">
                    <a href="'.$courseurl.'">
                    <div class="h-200 rounded-top" style="background-image: url(\''.$courseimage.'\');background-size: cover;background-position: center; position: relative;"></div></a>
                </div>
                <div class="card-block" title="'.$course->fullname.'">
                    <h4 class="card-title my-5 ellipsis ellipsis-2" style="-webkit-box-orient: vertical;"><a href="'.$courseurl.'" class="font-weight-400 blue-grey-600 font-size-18">'.$course->fullname.'</a></h4>
                    <p class="card-text m-0"><span class="text-muted ellipsis ellipsis-1" style="-webkit-box-orient: vertical;"></span></p><div class="text_to_html">'.$course->categoryname.'</div><p></p>
                    <p class="card-text module line-clamp ellipsis ellipsis-3 m-0" style="-webkit-box-orient: vertical;">'.$course->summary.'</p>
                </div>
                <div class="card-footer card-footer-transparent text-muted activity-btn ">
                    <div class="enrolmenticons d-inline-block price-wrap">
                        <strong>'. CURRENCY_SYMBOL[$course->currency] . $course->cost .'</strong>
                    </div>

                    '.$button.'
                </div>
            </div>
        </div>';
}
echo '</div>
</div>';
echo $OUTPUT->paging_bar($products['count'], $page, $perpage, $PAGE->url);

echo $OUTPUT->footer();