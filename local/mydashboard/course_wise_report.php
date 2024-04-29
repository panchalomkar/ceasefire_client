<?php
require_once('../../config.php');

require_once 'lib.php';
global $DB, $CFG, $USER;

include_once $CFG->dirroot . '/grade/querylib.php';

require_login();
$heading = get_string('course_report', 'local_mydashboard');
$PAGE->set_url('/local/mydashboard/course_wise_report.php');
$PAGE->set_title($heading);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($heading);
echo $OUTPUT->header();

$displayreport = false;
if (isset($_POST['submitbutton']) && $_POST['submitbutton'] == 'Submit') {
    $form = (object) $_POST;
    $courseid = $_POST['courseid'];
    $displayreport = true;
    $context = context_course::instance($courseid);
}
?>
<!DOCTYPE html>

<!-- DataTables -->
<link rel="stylesheet" href="external/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="external/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="external/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="external/dist/css/adminlte.min.css">

<form name=mform1" autocomplete="off" action="" method="post" accept-charset="utf-8" id="mform1" class="mform" enctype="multipart/form-data">

    <div class="form-group row  fitem">
        <div class="col-md-3">
            <label class="col-form-label d-inline " for="company">
                Course
            </label>
        </div>
        <div class="col-md-9 form-inline align-items-start felement" data-fieldtype="select">

            <select class="mdb-select md-form custom-select" searchable="Search here.." name="courseid" id="courseid" style="width: 30%;" required>
                <option value="">Select Course</option>
                <?php
                $courses = $DB->get_records('course');
                foreach ($courses as $course) {
                    if (@$form->courseid == $course->id) {
                        echo '<option value="' . $course->id . '" selected>' . $course->fullname . '</option>';
                    } else {
                        echo '<option value="' . $course->id . '">' . $course->fullname . '</option>';
                    }
                }
                ?>
            </select>  
        </div>
    </div>

    <div class="form-group row  fitem   ">
        <div class="col-md-3">
            <label class="col-form-label d-inline " for="title">
                Start Date
            </label>
        </div>
        <div class="col-md-9 form-inline felement" data-fieldtype="text">
            <input type="date" class="form-control" class="datepicker" name="startdate" id="id_title" value="<?php echo @$form->startdate; ?>">

        </div>
    </div>

    <div class="form-group row  fitem   ">
        <div class="col-md-3">
            <label class="col-form-label d-inline " for="title">
                End Date
            </label>
        </div>
        <div class="col-md-9 form-inline felement" data-fieldtype="text">
            <input type="date" class="form-control" class="datepicker" name="enddate" id="id_title" value="<?php echo @$form->enddate; ?>">

        </div>
    </div>

    <!--PROFILE FIELDS-->

<div class="form-group row  fitem   ">
        <div class="col-md-3">
            <label class="col-form-label d-inline " for="title">
                User ID
            </label>
        </div>
        <div class="col-md-9 form-inline felement" data-fieldtype="text">
            <input type="text" class="form-control" name="username" value="<?php echo @$form->username; ?>">
        </div>
    </div>
	
	
    <div class="form-group row  fitem   ">
        <div class="col-md-3">
            <label class="col-form-label d-inline " for="title">
                Reporting Manager
            </label>
        </div>
        <div class="col-md-9 form-inline felement" data-fieldtype="text">
            <input type="text" class="form-control" name="reportingto" value="<?php echo @$form->reportingto; ?>">
        </div>
    </div>

    <div class="form-group row  fitem   ">
        <div class="col-md-3">
            <label class="col-form-label d-inline " for="title">
                Division
            </label>
        </div>
        <div class="col-md-9 form-inline felement" data-fieldtype="text">
            <input type="text" class="form-control" name="division" value="<?php echo @$form->division; ?>">
        </div>
    </div>

    <div class="form-group row  fitem   ">
        <div class="col-md-3">
            <label class="col-form-label d-inline " for="title">
                Sub Division
            </label>
        </div>
        <div class="col-md-9 form-inline felement" data-fieldtype="text">
            <input type="text" class="form-control" name="subdivision" value="<?php echo @$form->subdivision; ?>">
        </div>
    </div>

    <div class="form-group row  fitem   ">
        <div class="col-md-3">
            <label class="col-form-label d-inline " for="title">
                Branch
            </label>
        </div>
        <div class="col-md-9 form-inline felement" data-fieldtype="text">
            <input type="text" class="form-control" name="branchname" value="<?php echo @$form->branchname; ?>">
        </div>
    </div>

    <div class="form-group row  fitem   ">
        <div class="col-md-3">
            <label class="col-form-label d-inline " for="title">
                Position
            </label>
        </div>
        <div class="col-md-9 form-inline felement" data-fieldtype="text">
            <input type="text" class="form-control" name="position" value="<?php echo @$form->position; ?>">
        </div>
    </div>



    <!--PROFILE FIELDS-->

    <div class="form-group row  fitem femptylabel  " data-groupname="buttonar">
        <div class="col-md-3">

        </div>
        <div class="col-md-9 form-inline felement" data-fieldtype="group">

            <div class="form-group  fitem  ">

                <span data-fieldtype="submit">
                    <input type="submit" class="btn btn-primary" name="submitbutton" id="id_submitbutton" value="<?php echo get_string('submit'); ?>">
                </span>

            </div>

            <div class="form-group  fitem   btn-cancel">

                <span data-fieldtype="submit">
                    <a href="course_wise_report.php" class="btn btn-secondary" name="cancel" id="id_cancel" ><?php echo get_string('cancel'); ?></a>
                </span>

            </div>

        </div>
    </div>


</form>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo $heading; ?></h3>
                    </div>
                    <!-- /.card-header -->
                    <?php if ($displayreport) { ?>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>

                                        <th>Fullname</th>
                                        <th>Email</th>
                                        <th>City</th>
                                        <th>Country</th>
                                        <th>Reporting Manager</th>
                                        <th>Sex</th>
                                        <th>Division</th>
                                        <th>Sub Division</th>
                                        <th>Emp Code</th>
                                        <th>Batch</th>
                                        <th>Branch</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Trainer</th>
                                        <?php
//get course sections
                                        $SQL = "SELECT * FROM {course_sections} WHERE course = $courseid AND sequence != '' ORDER BY section";
                                        $sections = $DB->get_records_sql($SQL);

                                        $modinfo = get_fast_modinfo($courseid);

                                        foreach ($sections as $section) {
                                            $modules = explode(',', $section->sequence);
                                            if (count($modules) == 1) {
                                                $cm = $modinfo->get_cm($section->sequence);
						echo '<th>' . $cm->name . '</th>';
						if($cm->modname == 'quiz'){
							echo '<th>'.$cm->name.' Score</th>';
						}
                                                $usermoddata[] = $cm;
                                            } else {
                                                foreach ($modules as $module) {
                                                    $cm = $modinfo->get_cm($module);
                                                    $usermoddata[] = $cm;
						    echo '<th>' . $cm->name . '</th>';
						    if($cm->modname == 'quiz'){
                                                        echo '<th>'.$cm->name.' Score</th>';
                                                }
                                                }
                                            }
                                        }
                                        ?>
                                        <th>Course Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $condition = " ";
                                    $join = " ";
                                    if ($form->startdate != '') {
                                        $starttime = strtotime($form->startdate);
                                        $condition .= " AND a.timestart >= $starttime ";
                                    }
                                    if ($form->enddate != '') {
                                        $endtime = strtotime($form->enddate);
                                        $condition .= " AND a.timeend <= ($endtime+86399) ";
                                    }
									
									if ($form->username != '') {
                                        $condition .= " AND u.username = '$form->username' ";
                                    }


                                    $enSQL = "SELECT u.* FROM {enrol} e 
                                        INNER JOIN {user_enrolments} a ON e.id = a.enrolid
                                        INNER JOIN {user} u ON u.id = a.userid WHERE e.courseid = $courseid $condition
                                        ";
                                    $users = $DB->get_records_sql($enSQL);

                                    foreach ($users as $user) {
                                        profile_load_data($user);

                                        if ($form->division != '' && $form->division != $user->profile_field_division) {
                                            continue;
                                        }

                                        if ($form->subdivision != '' && $form->subdivision != $user->profile_field_subdivision) {
                                            continue;
                                        }

                                        if ($form->branchname != '' && $form->branchname != $user->profile_field_branchname) {
                                            continue;
                                        }

                                        if ($form->position != '' && $form->position != $user->profile_field_designation) {
                                            continue;
                                        }
                                        if ($form->reportingto != '' && $form->reportingto != $user->profile_field_repotingto) {
                                            continue;
                                        }

                                        echo '<tr>';
                                        echo '<td>' . $user->firstname . ' ' . $user->lastname . '</td>';
                                        echo '<td>' . $user->email . '</td>';
                                        echo '<td>' . $user->city . '</td>';
                                        echo '<td>' . $user->country . '</td>';
                                        echo '<td>' . $user->profile_field_repotingto . '</td>';
                                        echo '<td>' . $user->profile_field_gender . '</td>';
                                        echo '<td>' . $user->profile_field_division . '</td>';
                                        echo '<td>' . $user->profile_field_subdivision . '</td>';
                                        echo '<td><a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '">' . $user->username . '</td>';
                                        echo '<td>' . $user->profile_field_department . '</td>';
                                        echo '<td>' . $user->profile_field_branchname . '</td>';
                                        echo '<td>' . $user->profile_field_designation . '</td>';
                                        echo '<td>' . $user->profile_field_status . '</td>';
                                        echo '<td>' . $user->profile_field_trainer . '</td>';
                                        $sumgrade = 0;
					$gradeno = 0;
					
                                        foreach ($usermoddata as $umod) {
                                            
                                           $modstatus = $DB->get_record('course_modules_completion', array('userid' => $user->id, 'coursemoduleid' => $umod->id));
                                            $statustext = ($modstatus->completionstate == 0) ? 'Not Completed' : 'Completed';
                                            $comdate = ($modstatus->timemodified > 0 && $statustext == 'Completed') ? '(' . date('D, d-m-Y h:i A', $modstatus->timemodified) . ')' : '';
                                            echo '<td>' . $statustext . '' . $comdate . ' </td>';
                                            //get the quiz grade
					    $grade = ''; 
					   $gradeval = '';
					    if($umod->modname == 'quiz'){
						   $quizobj = $DB->get_record('quiz',array('id' => $umod->instance));
                                                $grade = quiz_get_best_grade($quizobj, $user->id);
						if($grade != NULL) {    
						   $gradeval = round($grade,2);
						$sumgrade = $sumgrade + $grade;
						   $gradeno++;
						}
						echo '<td>'. $gradeval.'</td>';
                                            }
					  //  echo '<td>' . $statustext . '' . $comdate . ' </td>';
					    
                                        }
                                       // $cgrade = grade_get_course_grade($user->id, $courseid);
					// echo '<td>' . $cgrade->str_grade . '</td>';
				$gpercentage = ($sumgrade > 0)?($sumgrade / $gradeno):'';
                                        echo '<td>'.round($gpercentage,2) . '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>

                            </table>
                        </div>

                    <?php } ?>
                    <!-- /.card-body -->
                </div>

                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
</section>
<!-- /.content -->



<!-- ./wrapper -->

<!-- jQuery -->
<script src="external/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->

<script src="external/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="external/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="external/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="external/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="external/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="external/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="external/plugins/jszip/jszip.min.js"></script>
<script src="external/plugins/pdfmake/pdfmake.min.js"></script>
<script src="external/plugins/pdfmake/vfs_fonts.js"></script>
<script src="external/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="external/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="external/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>


        <script src='select2/select2/dist/js/select2.min.js' type='text/javascript'></script>

        <link href='select2/select2/dist/css/select2.min.css' rel='stylesheet' type='text/css'>

<!-- Page specific script -->
<script>
    $(function () {
        $("#example1").DataTable({
            "responsive": false, "lengthChange": true, "autoWidth": true,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');


    });
	
	
$('.mdb-select').select2();


</script>
<style>
    #page-local-mydashboard-course_wise_report .has-blocks {
        display: none;
    }

</style>
<?php
echo $OUTPUT->footer();
