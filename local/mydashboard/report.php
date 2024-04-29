<?php
require_once ('../../config.php');

require_once 'lib.php';
global $DB, $CFG, $USER;

require_login();
$PAGE->set_url('/local/mydashboard/report.php');
$PAGE->set_title('Reports');
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add('Reports');
echo $OUTPUT->header();

//get user available points
$user_points = get_user_available_points();
$points_log = get_user_points_log();
$points_share = get_user_points_share();
$points_redeem = get_user_points_redeem();
?>
<!DOCTYPE html>

<!-- DataTables -->
<link rel="stylesheet" href="external/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="external/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<link rel="stylesheet" href="external/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
<!-- Theme style -->
<link rel="stylesheet" href="external/dist/css/adminlte.min.css">



<!-- Content Wrapper. Contains page content -->


<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <a href="level/index.php" class="btn btn-primary"> Point Matrix </a>
                <hr />

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">User Available points</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Emp ID</th>
                                    <th>Fullname</th>
                                    <th>Email</th>
                                    <th>Avaialble Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php


                                $itemsPerPage = 10;
                                $current_page = isset($_GET['available_points']) ? $_GET['available_points'] : 1;
                                $totalItems = count($user_points);
                                $totalPages = ceil($totalItems / $itemsPerPage);


                                $startIndex = ($current_page - 1) * $itemsPerPage;
                                $endIndex = min($startIndex + $itemsPerPage, $totalItems);


                                foreach (array_slice($user_points, $startIndex, $itemsPerPage) as $points) {
                                    echo '<tr>';
                                    echo '<td>' . $points->username . '</td>';
                                    echo '<td>' . $points->firstname . ' ' . $points->lastname . '</td>';
                                    echo '<td>' . $points->email . '</td>';
                                    echo '<td>' . $points->available_points . '</td>';
                                    echo '</tr>';
                                }



                                // echo "<div class='mb-3'>";
                                
                                // // Previous button
                                // if ($current_page > 1) {
                                //     echo "<a href='?page=" . ($current_page - 1) . "'class=' all_pages'>Previous</a> ";
                                // }
                                
                                // // Page numbers
                                // for ($i = 1; $i <= $totalPages; $i++) {
                                //     // Check if $i is the current page, if so, add a CSS class for highlighting
                                //     $class = ($i == $current_page) ? 'current' : '';
                                //     echo "<a href='?page=$i' class='$class all_pages'>$i</a> ";
                                // }
                                
                                // // Next button
                                // if ($current_page < $totalPages) {
                                //     echo "<a href='?page=" . ($current_page + 1) . " ' class=' all_pages'>Next</a> ";
                                // }
                                
                                // echo "</div>";
                                


                                // foreach ($user_points as $points) {
                                //     echo '<tr>';
                                //     echo '<td>' . $points->username . '</td>';
                                //     echo '<td>' . $points->firstname . ' ' . $points->lastname . '</td>';
                                //     echo '<td>' . $points->email . '</td>';
                                //     echo '<td>' . $points->available_points . '</td>';
                                //     echo '</tr>';
                                // }
                                ?>
                            </tbody>

                        </table>
                        <?php
                        echo "<div class='mb-2 text-right panel-position'>";

                        // Previous button
                        if ($current_page > 1) {
                            echo "<a href='?available_points=" . ($current_page - 1) . "' class='all_pages mr-1'>Previous</a> ";
                        }

                        // Page numbers
                        $visiblePages = 5; // Number of page links to show
                        $startPage = max(1, $current_page - floor($visiblePages / 2));
                        $endPage = min($totalPages, $startPage + $visiblePages - 1);

                        if ($startPage > 1) {
                            echo "<a href='?available_points=1' class='all_pages mr-1'>1</a>";
                            if ($startPage > 2) {
                                echo "<span class='all_pages mr-1'>...</span>";
                            }
                        }


                        for ($i = $startPage; $i <= $endPage; $i++) {

                            $class = ($i == $current_page) ? 'current' : '';
                            echo "<a href='?available_points=$i' class='$class all_pages mr-1'>$i</a>";

                        }

                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo "<span class='all_pages mr-1'>...</span>";
                            }
                            echo "<a href='?available_points=$totalPages' class='all_pages mr-1'>$totalPages</a>";
                        }


                        if ($current_page < $totalPages) {
                            echo "<a href='?available_points=" . ($current_page + 1) . "' class='all_pages mr-1'>Next</a> ";
                        }

                        echo "</div>";


                        ?>
                    </div>
                    <!-- /.card-body -->
                </div>


                <!--Point LOG-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">User points logs</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example2" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Emp ID</th>
                                    <th>Fullname</th>
                                    <th>Email</th>
                                    <th>Point Type</th>
                                    <th>Action</th>
                                    <th>Points</th>
                                    <th>Date Time</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $itemsPerPagelog = 10;
                                $currentPagelog = isset($_GET['log']) ? $_GET['log'] : 1;
                                $totalItemslog = count($points_log);
                                $totalPageslog = ceil($totalItemslog / $itemsPerPagelog);


                                $startIndex1 = ($currentPagelog - 1) * $itemsPerPagelog;
                                $endIndex1 = min($startIndex1 + $itemsPerPagelog, $totalItemslog);

                                foreach (array_slice($points_log, $startIndex1, $itemsPerPagelog) as $log) {
                                    echo '<tr>';
                                    echo '<td>' . $log->username . '</td>';
                                    echo '<td>' . $log->firstname . ' ' . $log->lastname . '</td>';
                                    echo '<td>' . $log->email . '</td>';
                                    echo '<td>' . ucwords($log->point_type) . '</td>';
                                    echo '<td>' . $log->action . '</td>';
                                    echo '<td>' . $log->points . '</td>';
                                    echo '<td>' . date('d-m-Y H:i', $log->timecreated) . '</td>';
                                    echo '<td>' . $log->ip_addr . '</td>';
                                    echo '</tr>';
                                }



                                ?>



                            </tbody>

                        </table>
                        <?php
                        echo "<div class='mb-3 text-right panel-position'>";

                        // Previous button
                        if ($currentPagelog > 1) {
                            echo "<a href='?log=" . ($currentPagelog - 1) . "' class='all_pages'>Previous</a> ";
                        }

                        // Page numbers
                        $startPage = max(1, $currentPagelog - 2);
                        $endPage = min($totalPageslog, $startPage + 4);

                        if ($startPage > 1) {
                            echo "<a href='?log=1' class='all_pages'>1</a> ";

                            if ($startPage > 2) {
                                echo "<span class='all_pages'>...</span> ";
                            }
                        }

                        for ($j = $startPage; $j <= $endPage; $j++) {
                            $class1 = ($j == $currentPagelog) ? 'current' : '';
                            echo "<a href='?log=$j' class='$class1 all_pages'>$j</a> ";
                        }

                        if ($endPage < $totalPageslog) {
                            if ($endPage < $totalPageslog - 1) {
                                echo "<span class='all_pages'>...</span> ";
                            }
                            echo "<a href='?log=$totalPageslog' class='all_pages'>$totalPageslog</a> ";
                        }

                        // Next button
                        if ($currentPagelog < $totalPageslog) {
                            echo "<a href='?log=" . ($currentPagelog + 1) . "' class='all_pages'>Next</a> ";
                            header("Location: ?log=$totalPages");
                        }

                        echo "</div>";

                        ?>
                    </div>
                    <!-- /.card-body -->


                </div>
                <!--Point LOG END-->

                <!--POINT SHARE-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">User Share points</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example3" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>From Emp ID</th>
                                    <th>To Emp ID</th>
                                    <th>Points</th>
                                    <th>Date Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // foreach ($points_share as $share) {
                                //     echo '<tr>';
                                //     echo '<td>' . $share->username . '</td>';
                                //     echo '<td>' . $share->tousername . '</td>';
                                //     echo '<td>' . $share->points . '</td>';
                                //     echo '<td>' . date('d-m-Y H:i', $share->timecreated) . '</td>';
                                //     echo '</tr>';
                                // }
                                
                                $itemsPerPagelog1 = 10;
                                $currentPagelog1 = isset($_GET['page']) ? $_GET['page'] : 1;
                                $totalItemslog1 = count($points_share);
                                $totalPageslog1 = ceil($totalItemslog1 / $itemsPerPagelog1);


                                $startIndex2 = ($currentPagelog1 - 1) * $itemsPerPagelog1;
                                $endIndex12 = min($startIndex1 + $itemsPerPagelog1, $totalItemslog1);



                                foreach (array_slice($points_share, $startIndex2, $itemsPerPage) as $share) {
                                    echo '<tr>';
                                    echo '<td>' . $share->username . '</td>';
                                    echo '<td>' . $share->tousername . '</td>';
                                    echo '<td>' . $share->points . '</td>';
                                    echo '<td>' . date('d-m-Y H:i', $share->timecreated) . '</td>';
                                    echo '</tr>';
                                }



                                ?>
                            </tbody>

                        </table>
                        <?php
                        echo "<div class='mb-3 text-right'panel-position>";

                        // Previous button
                        if ($currentPagelog1 > 1) {
                            echo "<a href='?page=" . ($currentPagelog1 - 1) . "' class='all_pages'>Previous</a> ";
                        }

                        // Page numbers
                        $startPage1 = max(1, $currentPagelog1 - 2);
                        $endPage1 = min($totalPageslog1, $startPage1 + 4);

                        if ($startPage1 > 1) {
                            echo "<a href='?page=1' class='all_pages'>1</a> ";
                            if ($startPage1 > 2) {
                                echo "<span class='all_pages'>...</span> ";
                            }
                        }

                        for ($j = $startPage1; $j <= $endPage1; $j++) {
                            $class1 = ($j == $currentPagelog1) ? 'current' : '';
                            echo "<a href='?page=$j' class='$class1 all_pages'>$j</a> ";
                        }

                        if ($endPage < $totalPageslog1) {
                            if ($endPage1 < $totalPageslog1 - 1) {
                                echo "<span class='all_pages'>...</span> ";
                            }
                            echo "<a href='?page=$totalPageslog1' class='all_pages'>$totalPageslog1</a> ";
                        }

                        // Next button
                        if ($currentPagelog1 < $totalPageslog1) {
                            echo "<a href='?page=" . ($currentPagelog1 + 1) . "' class='all_pages'>Next</a> ";
                        }

                        echo "</div>";

                        ?>
                    </div>
                    <!-- /.card-body -->
                </div>

                <!--REDEEM-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Redeem Points</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <table id="example3" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Emp ID</th>
                                    <th>Fullname</th>
                                    <th>Email</th>
                                    <th>Points</th>
                                    <th>Redeem Date Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // foreach ($points_redeem as $redeem) {
                                    // echo '<tr>';
                                    // echo '<td>' . $redeem->username . '</td>';
                                    // echo '<td>' . $redeem->firstname . ' ' . $redeem->lastname . '</td>';
                                    // echo '<td>' . $redeem->email . '</td>';
                                    // echo '<td>' . $redeem->points . '</td>';
                                    // echo '<td>' . date('d-m-Y H:i', $redeem->timecreated) . '</td>';
                                    // echo '</tr>';
                                // }
                                
                                $itemsPerPage_r = 10;
                                $currentPage_r = isset($_GET['points_redeem']) ? $_GET['points_redeem'] : 1;
                                $totalItems_r = count($points_redeem);
                                $totalPages_r = ceil($totalItemslog1 / $itemsPerPage_r);


                                $startIndex2 = ($currentPage_r - 1) * $itemsPerPage_r;
                                $endIndex12 = min($startIndex1 + $itemsPerPagelog1, $totalItems_r);

                                foreach (array_slice($points_redeem, $startIndex2, $itemsPerPage) as $redeem) {
                                        echo '<tr>';
                                    echo '<td>' . $redeem->username . '</td>';
                                    echo '<td>' . $redeem->firstname . ' ' . $redeem->lastname . '</td>';
                                    echo '<td>' . $redeem->email . '</td>';
                                    echo '<td>' . $redeem->points . '</td>';
                                    echo '<td>' . date('d-m-Y H:i', $redeem->timecreated) . '</td>';
                                    echo '</tr>';
                                }


                                ?>
                            </tbody>

                        </table>

                        <?php
                        echo "<div class='mb-3 text-right'panel-position>";

                        // Previous button
                        if ($totalItems_r === 0) {
                            $class1 = 'd-none';
                        } else {
                            if ($currentPage_r > 1) {
                                echo "<a href='?points_redeem=" . ($currentPage_r - 1) . "' class='all_pages'>Previous</a> ";
                            }

                            // Page numbers
                            $startPage2 = max(1, $currentPage_r - 2);
                            $endPage2 = min($totalPages_r, $startPage2 + 4);

                            if ($startPage1 > 1) {
                                echo "<a href='?points_redeem=1' class='all_pages'>1</a> ";
                                if ($startPage2 > 2) {
                                    echo "<span class='all_pages'>...</span> ";
                                }
                            }


                            for ($j = $startPage2; $j <= $endPage2; $j++) {
                                $class1 = ($j == $currentPage_r) ? 'current' : '';
                                echo "<a href='?points_redeem=$j' class='$class1 all_pages'>$j</a> ";
                            }



                            if ($endPage2 < $totalPages_r) {
                                if ($endPage2 < $totalPages_r - 1) {
                                    echo "<span class='all_pages'>...</span> ";
                                }
                                echo "<a href='?points_redeem=$totalPages_r' class='all_pages'>$totalPages_r</a> ";
                            }

                            // Next butto
                            if ($currentPage_r < $totalPages_r) {
                                echo "<a href='?points_redeem=" . ($currentPage_r + 1) . "' class='all_pages'>Next</a> ";
                            }
                        }
                        echo "</div>";

                        ?>
                    </div>
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

<!-- Page specific script -->
<script>
    $(function () {
        $("#example1").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');

        $("#example2").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example2_wrapper .col-md-6:eq(0)');

        $("#example3").DataTable({
            "responsive": true, "lengthChange": false, "autoWidth": false,
            "buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
        }).buttons().container().appendTo('#example3_wrapper .col-md-6:eq(0)');


    });
</script>
<style>
    #page-local-mydashboard-report .has-blocks {
        display: none;
    }

    #page-local-mydashboard-report {

        .current {
            z-index: 3;
            color: #fff !important;
            background-color: #007bff !important;
            border-color: #007bff;
            border: 1px solid;
            padding: 10px !important;
        }

        .all_pages {

            color: #000;
            background-color: #ffffff;

            border: 1px solid #007bff;
            padding: 9px;
        }

        .paginate_button {
            display: none;
        }

        .panel-position {
            position: relative;
            bottom: 25px;
        }

    }
</style>
<?php
echo $OUTPUT->footer();
