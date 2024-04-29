<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//Points type
define('POINT_TYPE_LOGIN', 'login');
define('POINT_TYPE_QUIZ', 'quiz');
define('POINT_TYPE_SPINWHEEL', 'spinwheel');

function get_available_points($userid) {
    global $DB;

    $record = $DB->get_record('user_points', array('userid' => $userid));

    return ($record->available_points > 0) ? $record->available_points : 0;
}

function set_my_rank($userid) {
    global $DB, $CFG;

    $record = $DB->get_record('user_points', array('userid' => $userid));

    $total_points = ($record->total_points > 0) ? $record->total_points : 0;

    $usergrade = get_user_grade($userid);
    $currentrank = $record->rank;
    $newrank = get_new_rank($userid, $total_points, $usergrade);
    if ($currentrank != $newrank) {
        //get config
        $config = get_config('local_mydashboard');
        $update = "UPDATE {user_points} SET rank = '$newrank' WHERE userid = $userid";
        if ($DB->execute($update)) {
            $count = $config->rank_promote;
//            if ($newrank != 'Team Ceasefire Cadet') {
            if (strpos($newrank, 'Cadet') == false) {
                for ($i = 0; $i < $count; $i++) {
                    $items = array(5, 0, 5, 0, 10, 10, 0, 15, 15, 0, 20, 10, 0, 20, 10, 0, 25, 5, 15, 0, 25, 15, 0, 30, 10, 0, 35, 0, 0, 10, 40, 5, 45, 5, 50);
                    $scratch = new stdClass();

                    $scratch->userid = $userid;
                    $scratch->itemid = 0;
                    $scratch->card_type = 'rank';
                    $scratch->point = $items[rand(0, count($items) - 1)];
                    $scratch->redeemed = 0;
                    $scratch->timecreated = time();

                    $DB->insert_record('user_scratchcard', $scratch);
                }
            }
        }
    }
}

function get_my_rank($userid) {
    global $DB, $CFG;

    $record = $DB->get_record('user_points', array('userid' => $userid));

    return $record->rank;
}

function get_new_rank($userid, $total_points, $usergrade) {
    global $DB;

    $SQL = "SELECT * FROM {custom_level} WHERE point <= $total_points AND grade <= $usergrade 
           ORDER BY point DESC LIMIT 1";
    if ($record = $DB->get_record_sql($SQL)) {
        return $record->level;
    }
    return '-';
}

function get_new_rank1($userid, $total_points, $usergrade) {
    global $DB;

    if ($total_points >= 500000 && $usergrade >= 95) {
        return 'Fire Chief';
    } else if ($total_points >= 300000 && $usergrade >= 95) {
        return 'Assistant Chief';
    } else if ($total_points >= 100000 && $usergrade >= 92) {
        return 'Battalion Chief';
    } else if ($total_points >= 90000 && $usergrade >= 92) {
        return 'Assistant Battalion Chief';
    } else if ($total_points >= 70000 && $usergrade >= 90) {
        return 'Senior Captain';
    } else if ($total_points >= 60000 && $usergrade >= 90) {
        return 'Captain';
    } else if ($total_points >= 50000 && $usergrade >= 90) {
        return 'Junior Captain';
    } else if ($total_points >= 30000 && $usergrade >= 87) {
        return 'Senior Lieutenant';
    } else if ($total_points >= 25000 && $usergrade >= 87) {
        return 'Lieutenant';
    } else if ($total_points >= 20000 && $usergrade >= 87) {
        return 'Junior Lieutenant';
    } else if ($total_points >= 15000 && $usergrade >= 85) {
        return 'Senior Firefighter';
    } else if ($total_points >= 10000 && $usergrade >= 85) {
        return 'Firefighter';
    } else if ($total_points >= 2100 && $usergrade >= 75) {
        return 'Probationary firefighter';
    } else if ($total_points >= 2000) {
        return 'Cadet';
    } else if ($total_points < 2000) {
        return '-';
    }
}

function get_next_level($userid) {
    global $DB;

    $record = $DB->get_record('user_points', array('userid' => $userid));

    $total_points = ($record->total_points > 0) ? $record->total_points : 0;
    $usergrade = get_user_grade($userid);

     $SQL = "SELECT * FROM {custom_level} WHERE point >= $total_points AND grade <= $usergrade 
           ORDER BY point LIMIT 1";
    if ($record = $DB->get_record_sql($SQL)) {

        return array(($record->point - $total_points), $record->grade . '%', $record->level);
    }
    return array('-', '-', '-');

//
//    if ($total_points >= 500000) {
//        return array('', 'Top Level');
//    } else if ($total_points >= 300000 && $total_points < 500000) {
//        return array((500000 - $total_points), 'Fire Chief');
//    } else if ($total_points >= 100000 && $total_points < 300000) {
//        return array((300000 - $total_points), 'Assistant Chief');
//    } else if ($total_points >= 90000 && $total_points < 100000) {
//        return array((100000 - $total_points), 'Battalion Chief');
//    } else if ($total_points >= 70000 && $total_points < 90000) {
//        return array((90000 - $total_points), 'Assistant Battalion Chief');
//    } else if ($total_points >= 60000 && $total_points < 70000) {
//        return array((70000 - $total_points), 'Senior Captain');
//    } else if ($total_points >= 50000 && $total_points < 60000) {
//        return array((60000 - $total_points), 'Captain');
//    } else if ($total_points >= 30000 && $total_points < 50000) {
//        return array((50000 - $total_points), 'Junior Captain');
//    } else if ($total_points >= 25000 && $total_points < 30000) {
//        return array((30000 - $total_points), 'Senior Lieutenant');
//    } else if ($total_points >= 20000 && $total_points < 25000) {
//        return array((25000 - $total_points), 'Lieutenant');
//    } else if ($total_points >= 15000 && $total_points < 20000) {
//        return array((20000 - $total_points), 'Junior Lieutenant');
//    } else if ($total_points >= 10000 && $total_points < 15000) {
//        return array((15000 - $total_points), 'Senior Firefighter');
//    } else if ($total_points >= 2100 && $total_points < 10000) {
//        return array((10000 - $total_points), 'Firefighter');
//    } else if ($total_points >= 2000 && $total_points < 2100) {
//        return array((2100 - $total_points), 'Probationary firefighter');
//    } else if ($total_points < 2000) {
//        return array((2000 - $total_points), 'Cadet');
//    }
}

function get_user_grade($userid) {
    global $DB, $CFG;
    include_once $CFG->dirroot . '/grade/querylib.php';


    require_once($CFG->libdir.'/gradelib.php');
     // $courses = $DB->get_records('course');
    $courses = enrol_get_users_courses($userid);
    $carray = array();
    foreach ($courses as $course) {
        $carray[] = $course->id;
    }
    $grades = grade_get_course_grade($userid, $carray);

    $count = 0;
    $sum = 0;
    $maxsum = 0;
    foreach ($grades as $grade) {
        $sum = $sum + $grade->grade;
        $maxsum = $maxsum + $grade->item->grademax;
        $count++;
    }

    return ($sum / $maxsum) * 100;
}

function get_next_levelold($userid) {
    global $DB;
    $op = array();
    $record = $DB->get_record('user_points', array('userid' => $userid));

    $total_points = ($record->total_points > 0) ? $record->total_points : 0;

    //get level up config
    $level = $DB->get_record('block_xp_config', array('enabled' => 1));
    $p_config = (array) (json_decode($level->levelsdata));
    $p_config['name'] = (array) $p_config['name'];
    $p_config['xp'] = (array) $p_config['xp'];
    if ($total_points == 0) {
        $op[0] = $p_config['xp'][1];
        $op[1] = $p_config['name'][1];
    } else {
        foreach ((array) $p_config['xp'] as $key => $point) {
            if ($total_points < $point) {
                $op[0] = $point - $total_points;
                $op[1] = $p_config['name'][$key];
                break;
            }
        }
    }
    return $op;
}

function get_total_points($userid) {
    global $DB;

    $record = $DB->get_record('user_points', array('userid' => $userid));

    return ($record->total_points > 0) ? $record->total_points : 0;
}

function get_points($userid, $type) {
    global $DB;

    $SQL = "SELECT id, SUM(points) AS sumpoints FROM {user_points_log} WHERE userid = $userid AND point_type = '$type'";
    $record = $DB->get_record_sql($SQL);

    return ($record->sumpoints > 0) ? $record->sumpoints : 0;
}

function get_rewards_points_received($userid) {
    global $DB;

    $SQL = "SELECT id, SUM(points) AS sumpoints FROM {user_points_share} WHERE touserid = $userid";
    $record = $DB->get_record_sql($SQL);

    return ($record->sumpoints > 0) ? $record->sumpoints : 0;
}

function get_user_available_points() {
    global $DB;

    $SQL = "SELECT u.*, up.available_points FROM {user_points} up INNER JOIN {user} u ON u.id = up.userid";
    return $DB->get_records_sql($SQL);
}

function get_user_points_log() {
    global $DB;

    $SQL = "SELECT up.*, u.username, u.firstname, u.lastname, u.email  FROM {user_points_log} up 
            INNER JOIN {user} u ON u.id = up.userid ORDER BY up.id DESC";
    return $DB->get_records_sql($SQL);
}

function get_my_points_log($userid) {
    global $DB;

    $SQL = "SELECT up.*, u.username, u.firstname, u.lastname, u.email  FROM {user_points_log} up 
            INNER JOIN {user} u ON u.id = up.userid WHERE u.id = $userid ORDER BY up.id DESC";
    return $DB->get_records_sql($SQL);
}

function get_user_points_share() {
    global $DB;

    $SQL = "SELECT us.*, u.username, u1.username  AS tousername FROM {user_points_share} us 
            INNER JOIN {user} u ON u.id = us.fromuserid
            INNER JOIN {user} u1 ON u1.id = us.touserid ORDER BY us.id DESC";
    return $DB->get_records_sql($SQL);
}

function get_user_points_redeem() {
    global $DB;

    $SQL = "SELECT us.*, u.username, u.firstname, u.lastname, u.email FROM {user_points_log} us 
            INNER JOIN {user} u ON u.id = us.userid
            WHERE us.point_type = 'redeem'";
    return $DB->get_records_sql($SQL);
}

function add_point_log($userid, $pointtype, $action, $points) {
    global $DB;
    $insert = new stdClass();
//    if ($points > 0) {
    $insert->userid = $userid;
    $insert->point_type = $pointtype;
    $insert->action = $action;
    $insert->points = $points;
    $insert->timecreated = time();
    $insert->ip_addr = get_client_ip();
    if ($DB->insert_record('user_points_log', $insert)) {
        $operator = ($action == 'added') ? '+' : '-';
        $UPDATE = "UPDATE {user_points} SET available_points = (available_points $operator $insert->points) WHERE userid =$userid ";
        $DB->execute($UPDATE);

        //add total points
        if ($action == 'added') {
            $UPDATE = "UPDATE {user_points} SET total_points = (total_points + $insert->points) WHERE userid =$userid ";
            $DB->execute($UPDATE);
        }
    }

//        return TRUE;
//    }
    return TRUE;
}

function get_spinwheel_button($userid) {
    global $DB;

    //check if spin wheel points alloted to user for today
    $SQL = "SELECT * FROM {user_points_log} WHERE userid = $userid AND point_type = 'spinwheel'
                AND DATE_FORMAT(FROM_UNIXTIME(`timecreated`), '%Y-%m-%d') = CURDATE()";
    if (!$DB->record_exists_sql($SQL)) {
        return '<img id="spin_button" src="spin_off.png" alt="Spin" onClick="startSpin();"/>';
    }
    return '<i>You have won today\'s luck on wheel, try next day.</i>';
}

function get_leaderboard() {
    global $DB, $CFG;
    include $CFG->dirroot . '/lib/badgeslib.php';
    $sql = "SELECT u.*, p.available_points, p.rank FROM {user_points} p INNER JOIN {user} u ON u.id = p.userid
            ORDER BY available_points DESC LIMIT 0,10";

    $records = $DB->get_records_sql($sql);
    $i = 1;
    $table = '';
    foreach ($records as $row) {

        $usercontext = context_user::instance($row->id);
        $src = $CFG->wwwroot . "/pluginfile.php/$usercontext->id/user/icon/f1";
        $badges = $DB->get_records('badge_issued', array('userid' => $row->id));


        $table .= '<tr>
                      <th scope="row"> <span>' . $i . '</span></th>
                      <td>
                        <div class="d-flex align-items-center">
                          <img class="rounded-circle" src="' . $src . '" width="30">
                          <div class="ms-2">' . $row->firstname . ' ' . $row->lastname . '</div>
                        </div>
                      </td>
                      <td>' . get_rank_icon($row->rank) . '</td>
                      <td>' . $row->department . '</td>
                      <td>' . $row->available_points . '</td>
                      <td class="badgesData">
                        <div class="d-flex align-items-center">';
        foreach ($badges as $badge) {

            $badgeObj = new badge($badge->badgeid);

            $badge_context = $badgeObj->get_context();

            $table .= print_badge_image($badgeObj, $badge_context, 'small');  //  size parameter could be 'small' or 'large'
        }



        $table .= '</div>
                      </td>
                    </tr>
                    ';
        $i++;
    }
    return $table;
}

function get_rank_icon($rank) {
    global $DB, $CFG;

    $level = $DB->get_record('custom_level', array('level' => $rank));

    if ($level) {
        if ($level->icon != NULL) {
            return '<img src="' . $CFG->wwwroot . '/local/mydashboard/images/' . $level->icon . '" width="80">';
        }
    }
    return '';
}

function get_lifetime_points($userid) {
    global $DB;

    $SQL = "SELECT id, SUM(points) AS points FROM {user_points_log} WHERE userid = $userid AND action = 'added' ";
    $record = $DB->get_record_sql($SQL);

    return ($record->points > 0) ? $record->points : 0;
}

function get_redeemed_points($userid) {
    global $DB;

    $SQL = "SELECT id, SUM(points) AS points FROM {user_points_log} WHERE userid = $userid AND point_type = 'redeem' ";
    $record = $DB->get_record_sql($SQL);

    return ($record->points > 0) ? $record->points : 0;
}

function get_lastfive_spin($userid) {
    global $DB;
//[0, 0], [1, 10], [2, 23], [3, 17], [4, 18], [5, 9],
    $SQL = "SELECT * FROM {user_points_log} WHERE userid = $userid AND point_type = 'spinwheel'
             ORDER BY timecreated DESC LIMIT 5";
    $array = "[0,0],";
    $records = $DB->get_records_sql($SQL);
    $records = array_reverse($records);
    $i = 1;
    foreach ($records as $row) {
//        $array .= "[$i, $row->points],";
         $array .= '{X:"' . $i . '", Y:' . $row->points . '},';
        $i++;
    }
    return $array;
}

function get_lastfive_login($userid) {
    global $DB;
//[0, 0], [1, 10], [2, 23], [3, 17], [4, 18], [5, 9],
    $SQL = "SELECT * FROM {user_points_log} WHERE userid = $userid AND point_type = 'login'
             ORDER BY timecreated DESC LIMIT 5";
    $array = "[0,0],";
    $records = $DB->get_records_sql($SQL);
    $records = array_reverse($records);
    $i = 1;
    foreach ($records as $row) {
//        $array .= "[$i, $row->points],";
         $array .= '{X:"' . $i . '", Y:' . $row->points . '},';
        $i++;
    }
    return $array;
}

function get_lastfive_quiz($userid) {
    global $DB;
//[0, 0], [1, 10], [2, 23], [3, 17], [4, 18], [5, 9],
    $SQL = "SELECT * FROM {user_points_log} WHERE userid = $userid AND point_type = 'quiz'
             ORDER BY timecreated DESC LIMIT 5";
    $array = "[0,0],";
    $records = $DB->get_records_sql($SQL);
    $records = array_reverse($records);
    $i = 1;
    foreach ($records as $row) {
//        $array .= "[$i, $row->points],";
        $array .= '{X:"' . $i . '", Y:' . $row->points . '},';
        $i++;
    }
    return $array;
}

function get_my_scratchcard($userid) {
    global $DB, $CFG;

    $SQL = "SELECT * FROM {user_scratchcard} WHERE userid = $userid AND redeemed = 0 AND card_type = 'rank' LIMIT 3";
    $records = $DB->get_records_sql($SQL);

    $card = '';
    $i = 1;
    $points = array();
    $number = array();
    foreach ($records as $row) {
        $card .= '&nbsp;&nbsp;&nbsp;<div id="demo' . $i . '" class="scratchpad" scid="' . $row->id . '" point="' . $row->point . '"></div>&nbsp;&nbsp;&nbsp;';
        $i++;
        $points[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/' . $row->point . '.jpg';

        if ($row->point <= 20) {
            $number[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/s1-20.jpg';
        } else if ($row->point > 20 && $row->point <= 35) {
            $number[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/s25-35.jpg';
        } else if ($row->point > 35 && $row->point <= 50) {
            $number[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/s40-50.jpg';
        } else {
            $number[] = $CFG->wwwroot . '/local/mydashboard/sunil/images/s1-20.jpg';
        }
    }
    return array($card, $points, $number);
}

function get_scratch_counter($userid) {
    global $DB;

    $SQL = "SELECT * FROM {user_scratchcard} WHERE userid = $userid AND redeemed = 0 AND card_type = 'rank'";
    $records = $DB->get_records_sql($SQL);

    $count = count($records);

    if ($count > 3) {
        return "3 / $count";
    }
    return "$count / $count";
}

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function get_quiz_passed($marks) {
    if ($marks > 95) {
        return 15;
    } else if ($marks > 90 && $marks <= 95) {
        return 10;
    } else if ($marks > 85 && $marks <= 90) {
        return 7;
    } else if ($marks > 80 && $marks <= 85) {
        return 5;
    } else if ($marks <= 80) {
        return 0;
    }
}
