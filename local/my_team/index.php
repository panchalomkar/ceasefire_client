<?php
/**
 * Displays information about all the assignment modules in the requested course
 *
 * @package   local_my_team
 * @author    Jayesh
 */
require_once ("../../config.php");
require_once ($CFG->dirroot . '/local/my_team/lib.php');

require_login();

$context = context_system::instance();
$access = false;
if ($roles = get_user_roles($context, $USER->id)) {
    foreach ($roles as $role) {
        if ($role->shortname == 'manager') {
            $access = true;
            break;
        }
    }
}
// print_object($access); die;
//if (!($access || is_siteadmin())) {
//    print_error('Access denied');
//}

global $CFG, $OUTPUT, $PAGE, $USER;
$PAGE->requires->css(new moodle_url("/local/my_team/styles.css"));

$PAGE->set_context($context);

$PAGE->set_url(new moodle_url('/local/my_team/', $params));

$pluginname = get_string("pluginname", "local_my_team");
$PAGE->set_title($pluginname);
$PAGE->set_heading($pluginname);

echo $OUTPUT->header();

$userid = optional_param('uid', 0, PARAM_INT);

if ($userid == 0) {
    $userid = $USER->id;
}

$data = get_my_team_data($userid);
?>
<!DOCTYPE html>

<!-- DataTables -->
<link rel="stylesheet" href="../mydashboard/external/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="../mydashboard/external/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="../mydashboard/external/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="../mydashboard/external/dist/css/adminlte.min.css">

<a href="index.php?uid=<?php echo $USER->id ?>" class="btn btn-primary">Back to My Team</a>
<hr>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Team Report</h3>
    </div>
    <!-- /.card-header -->
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>User Pic</th>
                    <th>Fullname</th>
                    <th>View Team</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Sub Division</th>
                    <th>Course Enrolled</th>
                    <th>Course in-progress</th>
                    <th>Course Completed</th>
                    <th>Course Avg Grade</th>
                    <!--<th>Achieved Certificate</th>-->
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($data as $d) {

                    $enrolled = get_user_enrolled_courses($d->id);
                    $ip = get_user_inprogress_courses($d->id);
                    $comp = get_user_completed_courses($d->id);
                    $usercontext = context_user::instance($d->id);
                    $image = '<img src="' . $CFG->wwwroot . '/pluginfile.php/' . $usercontext->id . '/user/icon/f3" width="50">';
                    echo '<tr>';
                    echo '<td>' . $image . '</td>';
                    echo '<td>' . $d->firstname . ' ' . $d->lastname . '</td>';
                    echo '<td><a href="index.php?uid=' . $d->id . '">View</a></td>';
                    echo '<td>' . $d->email . '</td>';
                    echo '<td>' . $d->department . '</td>';
                    echo '<td>' . get_user_sub_division($d->id) . '</td>';
                    echo '<td>' . get_progress_bar(count($enrolled), count($enrolled), 1) . '</td>';
                    echo '<td>' . get_progress_bar($ip, count($enrolled), 2) . '</td>';
                    echo '<td>' . get_progress_bar($comp, count($enrolled), 3) . '</td>';
                    //                    echo '<td><a href="" class="btn btn-primary">My Certificate</a></td>';
                    try {
                        echo '<td>' . get_user_course_grade($d->id) . '</td>';

                    } catch (DivisionByZeroError $e) {

                        echo '<td>  </td>';
                    }
                    echo '</tr>';
                }


                ?>
            </tbody>

        </table>
    </div>
    <!-- /.card-body -->
</div>


<!-- /.content -->
<!-- jQuery -->
<script src="../mydashboard/external/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->

<script src="../mydashboard/external/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../mydashboard/external/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="../mydashboard/external/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="../mydashboard/external/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="../mydashboard/external/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="../mydashboard/external/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="../mydashboard/external/plugins/jszip/jszip.min.js"></script>
<script src="../mydashboard/external/plugins/pdfmake/pdfmake.min.js"></script>
<script src="../mydashboard/external/plugins/pdfmake/vfs_fonts.js"></script>
<script src="../mydashboard/external/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="../mydashboard/external/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="../mydashboard/external/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<!-- Page specific script -->
<script>
    $(function () {
        $("#example1").DataTable({
            "scrollX": true,
            //            "responsive": false, 
            "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');


    });
</script>

<?php
echo $OUTPUT->footer();
