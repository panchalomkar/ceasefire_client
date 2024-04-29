<?php

defined('MOODLE_INTERNAL') || die();
define('PER_PAGE', 12);
define('CURRENCY_SYMBOL', array('USD' => '$', 'INR' => '₹'));

//require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->dirroot . '/local/products/classes/billdesk.php');

function get_products($params = array()) {
    global $DB;
    $sql = "SELECT c.id as id,
                c.fullname as fullname,
                c.summary as summary,
                c.category as category,
                cc.name as categoryname,
                e.cost,
                e.currency
            FROM {enrol} e
                JOIN {course} c ON c.id = e.courseid
                LEFT JOIN {course_categories} cc ON cc.id = c.category
            WHERE e.status = :status
                AND c.id > 1";

    if (isset($params['page']) && $params['page'] > 0) {
        $page = $params['page'] * $params['perpage'];
        $perpage = $params['perpage'];
    } else {
        $page = 0;
        $perpage = PER_PAGE;
    }

    $count = $DB->get_records_sql($sql, array('status' => 0));
    $response['count'] = count($count);

    $courses = $DB->get_records_sql($sql, array('status' => 0), $page, $perpage);
    $response['courses'] = $courses;

    return $response;
}

function addtocart($userid, $course) {
    global $DB, $CFG, $SESSION, $USER;

    if ($userid > 1) {
        if ($inacart = $DB->get_record('products_in_cart', array('course' => $course, 'userid' => $userid))) {
            $response['status'] = true;
        } else {
            $res = $DB->insert_record('products_in_cart', array('course' => $course, 'userid' => $userid, 'timecreated' => time()));
            $response['status'] = true;
        }
    } else {
        $SESSION->productcart[$course] = $course;
        $response['status'] = true;
    }

    return $response;
}

function removefromcart($userid, $course) {
    global $DB, $CFG, $SESSION;

    if ($userid > 1) {
        if ($inacart = $DB->get_record('products_in_cart', array('course' => $course, 'userid' => $userid))) {
            $removearr = array('id' => $inacart->id);
            $DB->delete_records('products_in_cart', $removearr);
            $response['status'] = true;
        } else {
            $response['status'] = true;
        }
    } else {
        unset($SESSION->productcart[$course]);
        $response['status'] = true;
    }

    return $response;
}

function get_products_in_cart() {
    global $DB, $USER, $SESSION;

    if ($USER->id > 1) {
        $productsincart = $DB->get_records('products_in_cart', array('userid' => $USER->id));
        foreach ($productsincart as $productincart) {
            $response[] = $productincart->course;
        }
    } else {
        if (count($SESSION->productcart) > 0) {
            $response = $SESSION->productcart;
        } else {
            $response = array();
        }
    }
    return $response;
}

function get_course_image($courseid) {
     global $USER, $CFG, $OUTPUT, $DB, $PAGE;
     $course = $DB->get_record('course', array('id' => $courseid));
     require_once($CFG->dirroot.'/course/renderer.php');
        $chelper = new \coursecat_helper();
        if (is_array($course)) {
            $course = (object)$course;
        }
        $course->fullname = strip_tags($chelper->get_course_formatted_name($course));
    $course  = new core_course_list_element($course);
    print_object($course);
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

function get_products_details_in_cart() {
    global $DB, $USER, $SESSION;

    if ($USER->id > 1) {
        $sql = "SELECT pic.id,
                c.id as courseid,
                c.fullname as fullname,
                c.summary as summary,
                c.category as category,
                cc.name as categoryname,
                e.cost,
                e.currency
            FROM {products_in_cart} pic
                JOIN {enrol} e ON e.courseid = pic.course
                JOIN {course} c ON c.id = pic.course
                LEFT JOIN {course_categories} cc ON cc.id = c.category
            WHERE pic.userid = :userid
                AND e.status = :status
                AND c.id > 1";

        $courses = $DB->get_records_sql($sql, array('userid' => $USER->id, 'status' => 0));
    } else {
        if (count($SESSION->productcart) > 0) {
            $productcart = $SESSION->productcart;
            sort($productcart);
            $productcart = implode(',', $productcart);
            $sql = "SELECT @row:=@row+1 serial_number,
                            c.id as courseid,
                            c.fullname as fullname,
                            c.summary as summary,
                            c.category as category,
                            cc.name as categoryname,
                            (SELECT cost
                                FROM {enrol}
                                WHERE courseid = c.id
                                    AND status = 0
                                ORDER BY cost DESC
                                LIMIT 1
                            ) as cost,
                            (SELECT currency
                                FROM {enrol}
                                WHERE courseid = c.id
                                    AND status = 0
                                ORDER BY cost DESC
                                LIMIT 1
                            ) as currency
                        FROM {course} c
                            LEFT JOIN {course_categories} cc ON cc.id = c.category,
                            (SELECT @row:=0) cnt
                        WHERE c.id IN ($productcart)";
            $courses = $DB->get_records_sql($sql, array('status' => 0));
        } else {
            $courses = array();
        }
    }

    return $courses;
}

function get_course_instructor($courseid) {
    global $DB;

    $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
    $context = context_course::instance($courseid);
    $teachers = get_role_users($role->id, $context);
    return $teachers;
}

function get_available_courses_frontpage($params = array()) {
    global $DB;

    if ($params['cat'] > 0) {
        $where = ' AND cc.id = ' . $params['cat'];
    }

    $sort = $params['sort'];

    $sql = "SELECT c.id as id,
                c.fullname as fullname,
                c.summary as summary,
                c.category as category,
                cc.name as categoryname,
                e.cost,
                e.currency
            FROM {course} c
                LEFT JOIN {course_categories} cc ON cc.id = c.category
                LEFT JOIN (
                    SELECT courseid,
                        MAX(cost) as cost,
                        MAX(currency) as currency
                    FROM {enrol}
                    GROUP BY courseid
                ) e ON e.courseid = c.id
            WHERE c.id > 1
            AND c.visible = 1
            $where
            ORDER BY c.fullname $sort";

    if (isset($params['page']) && $params['page'] > 0) {
        $page = $params['page'] * $params['perpage'];
        $perpage = $params['perpage'];
    } else {
        $page = 0;
        $perpage = PER_PAGE;
    }

    $count = $DB->get_records_sql($sql);
    $response['count'] = count($count);

    $courses = $DB->get_records_sql($sql, array(), $page, $perpage);
    $response['courses'] = $courses;

    return $response;
}

function get_available_courses_frontpage_cards() {
    global $OUTPUT, $PAGE;

    $page = optional_param('page', 0, PARAM_INT);
    $perpage = optional_param('perpage', PER_PAGE, PARAM_INT);
    $cat = optional_param('cat', 0, PARAM_INT);
    $sort = optional_param('sort', 'asc', PARAM_TEXT);

    $params = array();
    if (!empty($page)) {
        $params['page'] = $page;
    }
    if (!empty($perpage)) {
        $params['perpage'] = $perpage;
    }
    if (!empty($cat)) {
        $params['cat'] = $cat;
    }
    if (!empty($sort)) {
        $params['sort'] = $sort;
    }
    if ($page < 0) {
        $page = 0;
    }

    $products = get_available_courses_frontpage($params);
    $productsincart = get_products_in_cart();

    $PAGE->set_url(new moodle_url('/', $params));
    unset($params['sort']);
    $sorturl = new moodle_url('/', $params);
    $categories = coursecat::get_all();

    foreach ($categories as $category) {
        if ($category->visible) {
            $selected = ($cat == $category->id) ? 'selected' : '';
            $categoryoption .= '<option value="' . $category->id . '" ' . $selected . '>' . $category->name . '</option>';
        }
    }

    if ($sort == 'asc') {
        $ascstyle = '';
        $descstyle = 'color: #ccc;';
    } else {
        $ascstyle = 'color: #ccc;';
        $descstyle = '';
    }

    // filters
    $coursehtml .= '<div class="row product-filter-wrap">
        <div class="col-md-3 d-flex my-4">
            <h5 class="mr-2">' . get_string('category', 'local_products') . ': </h5>
            <form style="width: 100%;">
                <input type="hidden" name="sort" value="' . $sort . '">
                <select name="cat" class="form-control" onchange="this.form.submit()">
                    <option value="0">' . get_string('select') . '</option>
                    ' . $categoryoption . '
                </select>
            </form>
        </div>
        <div class="col-md-9 d-flex my-4">
            <h5 class="mr-2">' . get_string('sort', 'local_products') . ': </h5>
                <a href="' . $sorturl . '&sort=asc" title="' . get_string('sortasc', 'local_products') . '" style="' . $ascstyle . '">
                    <i class="fa fa-sort-alpha-asc mr-2" style="font-size: 2em;" aria-hidden="true"></i>
                </a>
                <a href="' . $sorturl . '&sort=desc" title="' . get_string('sortdesc', 'local_products') . '" style="' . $descstyle . '">
                    <i class="fa fa-sort-alpha-desc mr-2" style="font-size: 2em;" aria-hidden="true"></i>
                </a>
        </div>
    </div>
    <hr>';

    $coursehtml .= '<div class="product-main-wrap">
    <div class="row content">';
    foreach ($products['courses'] as $course) {
        if (in_array($course->id, $productsincart)) {
            $button = '<div id="' . $course->id . '" class="pull-right addtocartbtn add-to-cart-btn in-cart" title="' . get_string('incart', 'local_products') . '"><i class="fa fa-check" aria-hidden="true"></i> ' . get_string('incart', 'local_products') . '</div>';
        } else {
            $button = '<div id="' . $course->id . '" class="pull-right addtocartbtn add-to-cart-btn" title="' . get_string('addtocart', 'local_products') . '"><i class="fa fa-cart-plus" aria-hidden="true"></i> ' . get_string('addtocart', 'local_products') . '</div>';
        }
        $courseurl = new moodle_url('/local/products/overview.php', array('id' => $course->id));
        $courseimage = get_course_image($course->id);
        $coursehtml .= '<div class="col-lg-3 col-sm-12 col-md-3 d-flex animation-none">
                <div class="card border w-full rounded-bottom bg-white " style="">
                    <div class="img-container">
                        <a href="' . $courseurl . '">
                        <div class="h-200 rounded-top" style="background-image: url(\'' . $courseimage . '\');background-size: cover;background-position: center; position: relative;"></div></a>
                    </div>
                    <div class="card-block" title="' . $course->fullname . '">
                        <h4 class="card-title my-5 ellipsis ellipsis-2" style="-webkit-box-orient: vertical;"><a href="' . $courseurl . '" class="font-weight-400 blue-grey-600 font-size-18">' . $course->fullname . '</a></h4>
                        <p class="card-text m-0"><span class="text-muted ellipsis ellipsis-1" style="-webkit-box-orient: vertical;"></span></p><div class="text_to_html">' . $course->categoryname . '</div><p></p>
                        <p class="card-text module line-clamp ellipsis ellipsis-3 m-0" style="-webkit-box-orient: vertical;">' . $course->summary . '</p>
                    </div>
                    <div class="card-footer card-footer-transparent text-muted activity-btn ">
                        <div class="enrolmenticons d-inline-block price-wrap">
                            <strong>' . CURRENCY_SYMBOL[$course->currency] . $course->cost . '</strong>
                        </div>
                        ' . $button . '
                    </div>
                </div>
            </div>';
    }
    if (count($products['courses']) < 1) {
        $coursehtml .= '<div class="col-md-12"><p>' . get_string('norecordsfound', 'local_products') . '</p></div>';
    }
    $coursehtml .= '</div>
    </div>';
    $coursehtml .= $OUTPUT->paging_bar($products['count'], $page, $perpage, $PAGE->url);

    return $coursehtml;
}

function get_product_overview($id) {
    $course = get_course($id);
    return $course;
}

function frontpage_instructor_carousel() {
    global $OUTPUT;

    // instructor carousel start
    $carouselheading = $OUTPUT->heading(get_string('topinstructors', 'local_products'));
    $carousel .= '<div id="frontpage_instructor_carousel_wrap" class="carousel slide" data-ride="carousel">
    ' . $carouselheading . '
        <div class="carousel-inner">
            <div class="carousel-item active">
                <div class="frontpage-instructor-carousel">
                    <img src="https://www.inifdpune.co.in/images/mentors/Twinkle-Khanna.png">
                    <div class="instructor-carousel-image-caption">Twinkle Khanna</div>
                    <p class="instructor-carousel-info">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.</p>
                </div>
            </div>
            <div class="carousel-item">
                <div class="frontpage-instructor-carousel">
                    <img src="https://www.inifdpune.co.in/images/mentors/Manish-Malhotra.png" class="img-fluid">
                    <div class="instructor-carousel-image-caption">Manish Malhotra</div>
                    <p class="instructor-carousel-info">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.</p> 
                </div>
            </div>
            <div class="carousel-item">
                <div class="frontpage-instructor-carousel">
                    <img src="https://www.inifdpune.co.in/images/mentors/Mangesh-Jadhav.png" class="img-fluid">
                    <div class="instructor-carousel-image-caption">Mangesh Jadhav</div>
                    <p class="instructor-carousel-info">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.</p>
                </div>
            </div>
        </div>
        <a class="carousel-control-prev" href="#frontpage_instructor_carousel_wrap" data-slide="prev">
            <i class="carousel-control-prev-icon fa fa-chevron-left" style="color: #000;"></i>
        </a>
        <a class="carousel-control-next" href="#frontpage_instructor_carousel_wrap" data-slide="next">
            <i class="carousel-control-next-icon fa fa-chevron-right" style="color: #000;"></i>
        </a>
    </div>';
    // instructor carousel end
    return $carousel;
}

function checkout() {

    global $CFG;
    
    require_once $CFG->dirroot . '/local/products/classes/billdesk.php';

    $billdesk = new \local_products\BilldeskUtil();
   
    return $billdesk->initCheckout();
}


function get_my_purchase(){
    global $DB,$USER;
    $transactions = $DB->get_records('billdesk_transaction',array('userid'=>$USER->id));
    if($transactions){
        $table = new html_table();
        $table->head = array('Transaction Reference No','Course ids', 'Amount','Status','Time');
        foreach ($transactions as $transaction){
          
            
         $table->data[] =array($transaction->txn_reference_no,$transaction->courseids,
             $transaction->amount,$transaction->status,
             userdate($transaction->timecreated));   
        }
        
    }
    return $table;
    
    
}

function get_all_purchase(){
    global $DB,$USER;
    $transactions = $DB->get_records('billdesk_transaction');
    if($transactions){
        $table = new html_table();
        $table->head = array('id', 'Transaction Reference No','Courses', 'UserId', 'Amount','Status','Time');
        foreach ($transactions as $transaction){
          
            //print_object(userdate($transaction->timecreated);
         $table->data[] =array($transaction->id,$transaction->txn_reference_no,$transaction->courseids,
            $transaction->userid, $transaction->amount,$transaction->status,
             userdate($transaction->timecreated));   
        }
        
    }
    return $table;
    
}
 
