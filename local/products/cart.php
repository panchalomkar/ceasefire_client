<?php
/**
 * Displays courses
 *
 * @package   local_products
 * @author    Jayesh
 */

require_once("../../config.php");
require_once($CFG->dirroot.'/local/products/lib.php');

$login = optional_param('login', 0, PARAM_INT);
if($login){
    require_login();
}

$context = context_system::instance();

global $CFG, $OUTPUT, $PAGE, $SESSION, $USER;

$PAGE->requires->css(new moodle_url("/local/products/styles.css"));
$PAGE->requires->js(new moodle_url('https://services.billdesk.com/checkout-widget/src/app.bundle.js'));
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js_call_amd('local_products/products', 'init');
$PAGE->requires->js(new moodle_url('/local/products/checkout.js'));

$PAGE->set_context($context);

$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', PER_PAGE, PARAM_INT);
$grid = 4;
$params = array();
if(!empty($page)){
    $params['page'] = $page;
}

if($USER->id > 0){
    if(count($SESSION->productcart) > 0){
        foreach($SESSION->productcart as $productcart){
            addtocart($USER->id, $productcart);
        }
    }
} else {
    $params['login'] = 1;
}

$PAGE->set_url(new moodle_url('/local/products/cart.php', $params));

if(!empty($perpage)){
    $params['perpage'] = $perpage;
}

$pagename = get_string("cart", "local_products");
$PAGE->set_title($pagename);
$PAGE->set_heading($pagename);

echo $OUTPUT->header();

if ($page < 0) {
    $page = 0;
}

$productsincart = get_products_details_in_cart();

$productpage = new moodle_url('/local/products/');
echo '<a href="'.$productpage.'" class="" ><i class="fa fa-long-arrow-left" aria-hidden="true"></i> '.get_string('backtoproducts', 'local_products').'</a>';

echo '<!--Section: Block Content-->
    <section>

        <!--Grid row-->
        <div class="row">

            <!--Grid column-->
            <div class="col-lg-8">

                <!-- Card -->
                <div class="mb-3">
                    <div class="pt-4 wish-list">

                        <h5 class="mb-4">Cart (<span id="cart_item_count">'.count($productsincart).'</span> items)</h5>';

                    foreach($productsincart as $productincart){
                        if($productincart->currency){
                            $productincartcurrency = $productincart->currency;
                        } else {
                            $productincartcurrency = 'USD';
                        }
                        if($productincart->cost){
                            $productincartcost = $productincart->cost;
                        } else {
                            $productincartcost = 0;
                        }
                        $courseinstructors = get_course_instructor($productincart->courseid);
                        $instructors = '';
                        if(count($courseinstructors) > 0){
                            $instructors = get_string('intructor', 'local_products');
                        }
                        $instructors .= '<ul class="cart-course-intructor">';
                        foreach($courseinstructors as $courseinstructor){
                            $instructors .= '<li class="text-muted small">'. $courseinstructor->firstname .' '. $courseinstructor->lastname .'</li>';
                        }
                        $instructors .= '</ul>';

                        $courseurl = new moodle_url('/course/view.php', array('id' => $productincart->courseid));

                        echo '<div id="single_cart_course_product_'.$productincart->id.'" class="row mb-4 single-cart-course-product" >
                            <div class="col-md-5 col-lg-3 col-xl-3">
                                <div class="view zoom overlay z-depth-1 rounded mb-3 mb-md-0">
                                    <a href="'.$courseurl.'" class="">
                                    <div class="h-200 rounded-top" style="background-image: url(\''.get_course_image($productincart->courseid).'\');background-size: cover;background-position: center; position: relative;"></div></a>
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-7 col-lg-9 col-xl-9">
                                <div>
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <a href="'.$courseurl.'" class="">
                                                <h5>'.$productincart->fullname.'</h5>
                                            </a>
                                            <h6 class="mb-2 text-muted">'.$productincart->categoryname.'</h6>
                                            <p class="mb-3 text-muted small module cart-course-summary">'.$productincart->summary.'</p>
                                            <p class="mb-2 text-muted small">'.$instructors.'</p>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <button data-id="'.$productincart->id.'" data-courseid="'.$productincart->courseid.'" class="card-link-secondary small text-uppercase mr-3"><i
                                                class="fa fa-times mr-1"></i> Remove item </button>
                                        </div>
                                        <p class="mb-0"><span><strong id="summary">'. CURRENCY_SYMBOL[$productincartcurrency] . $productincartcost .'</strong></span></p class="mb-0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr id="separator_hr_'.$productincart->id.'" class="mb-4">';
                    }
                    echo '</div>
                </div>
                <!-- Card -->

            </div>
            <!--Grid column-->

            <!--Grid column-->
            <div class="col-lg-4">

                <!-- Card -->
                <div class="mb-3" style="padding: 1rem; box-shadow: 0 0 11px rgba(33,33,33,.2);">
                    <div class="pt-4">

                    <h4 class="mb-3">The total amount</h4>
                    <hr>
                    <form action="'.$PAGE->url.'" method="post" >
                        <ul class="list-group list-group-flush">';
                        $total = 0;
                        foreach($productsincart as $productincart){
                            if($productincart->currency){
                                $productincartcurrency = $productincart->currency;
                            } else {
                                $productincartcurrency = 'USD';
                            }
                            if($productincart->cost){
                                $productincartcost = $productincart->cost;
                            } else {
                                $productincartcost = 0;
                            }
                            echo '<li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 pb-0">
                            '.$productincart->fullname.'
                            <span>'. CURRENCY_SYMBOL[$productincartcurrency] . $productincartcost .'</span>
                            </li>';
                            $total += $productincartcost;
                        }
                        echo '
                            <li class="list-group-item d-flex justify-content-between align-items-center border-0 px-0 mb-3">
                                <div>
                                    <h5>The total amount</h5>
                                </div>
                                <span><h5>'. CURRENCY_SYMBOL[$productincartcurrency] . $total .'</h5></span>
                            </li>
                        </ul>';
                        if(isloggedin()){
                            echo '<input id="checkout" class="btn btn-primary btn-block" value="Pay With Billdesk" role="button"></input>';
                        }else{
                          echo '<input type="submit" class="btn btn-primary btn-block" name="gotocheckout" value="go to checkout">';  
                        }
                        
                        
                    echo '</form>
                    </div>
                </div>
                <!-- Card -->

            </div>
            <!--Grid column-->

        </div>
        <!-- Grid row -->

    </section>
    <!--Section: Block Content-->';
echo $OUTPUT->paging_bar($products['count'], $page, $perpage, $PAGE->url);

echo $OUTPUT->footer();