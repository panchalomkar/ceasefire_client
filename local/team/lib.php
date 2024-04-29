<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Cohort related management functions, this file needs to be included manually.
 *
 * @package    core_team
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/* define('COHORT_ALL', 0);
  define('COHORT_COUNT_MEMBERS', 1);
  define('COHORT_COUNT_ENROLLED_MEMBERS', 3);
  define('COHORT_WITH_MEMBERS_ONLY', 5);
  define('COHORT_WITH_ENROLLED_MEMBERS_ONLY', 17);
  define('COHORT_WITH_NOTENROLLED_MEMBERS_ONLY', 23); */

/**
 * Add new team.
 *
 * @param  stdClass $cohort
 * @return int new cohort id
 */
require_once($CFG->dirroot . '/cohort/lib.php');
require_once("{$CFG->libdir}/csvlib.class.php");

function team_add_team($cohort, $companyid, $departmentid) {
    global $DB, $USER;

    if (!isset($cohort->name)) {
        throw new coding_exception('Missing team name in team_add_team().');
    }
    if (!isset($cohort->idnumber)) {
        $cohort->idnumber = NULL;
    }
    if (!isset($cohort->description)) {
        $cohort->description = '';
    }
    if (!isset($cohort->descriptionformat)) {
        $cohort->descriptionformat = FORMAT_HTML;
    }
    if (!isset($cohort->visible)) {
        $cohort->visible = 1;
    }
    if (empty($cohort->component)) {
        $cohort->component = 'local_team';
    }
    if (!isset($cohort->timecreated)) {
        $cohort->timecreated = time();
    }
    if (!isset($cohort->timemodified)) {
        $cohort->timemodified = $cohort->timecreated;
    }
    $transaction = $DB->start_delegated_transaction();
    $team = new stdClass();
    $team->mentor = 0;
    $team->timecreated = time();
    $team->timemodified = $cohort->timemodified;
    $cohortid = cohort_add_cohort($cohort);
    $data = new stdClass();
    $data->id = $cohortid;
    $data->idnumber = strtolower(str_replace(' ', '', $cohort->name)) . '_' . $cohortid;
    $team->cohortid = $cohortid;
    $team->companyid = $companyid;
    $team->departmentid = $departmentid;
    $team->cohortcreator = $USER->id;
    //Enrol team mentor to cohort

    if ($teammentors = explode(",", $team->mentor)) {
        foreach ($teammentors as $teammentor) {
            $mentorrecord = new stdClass();
            $mentorrecord->cohortid = $cohortid;
            $mentorrecord->userid = $teammentor;
            $mentorrecord->timeadded = time();
            $DB->insert_record('cohort_members', $mentorrecord);
        }
    }

    $DB->update_record('cohort', $data);
    $teamid = $DB->insert_record('local_team', $team);

    $transaction->allow_commit();
    /*
      $event = \core\event\cohort_created::create(array(
      'context' => context::instance_by_id($cohort->contextid),
      'objectid' => $cohort->id,
      ))

      $event->add_record_snapshot('local_team', $cohort);
      $event->trigger(); */

    return $teamid;
    //}
    //return false;
}

/**
 * Update existing team.
 * @param  stdClass $cohort
 * @return void
 */
function team_update_team($cohort) {
    global $DB;

    if (property_exists($cohort, 'component') and empty($cohort->component)) {
        // prevent NULLs
        $cohort->component = '';
    }
    $cohort->timemodified = time();
    if (!empty($cohort->name)) {
        $cohort->idnumber = strtolower(str_replace(' ', '', $cohort->name)) . '_' . $cohort->id;
    }
    $DB->update_record('cohort', $cohort);
    $team = $DB->get_record('local_team', array('cohortid' => $cohort->id));
    $teamcohort = new enrol_cohort();
    $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
    $studentrole = $DB->get_record('role', array('shortname' => 'student'));
    $instances = $DB->get_records('enrol', array('enrol' => 'cohort', 'customint1' => $cohort->id));
    foreach ($instances as $instance) {
        $context = context_course::instance($instance->courseid, MUST_EXIST);
        foreach (explode(',', $team->mentor) as $mentor) {
            // Un enrollment of mentor
            if (!in_array($mentor, $cohort->mentor)) {
                cohort_remove_member($cohort->id, $mentor);
            }
            //$teamcohort->unenrol_user($instance, $mentor);
            //role_unassign($teacherrole->id, $mentor, $context->id);
            //echo "<br>UN Enrolling user: $mentor in course : $instance->courseid";
            //groups_remove_member($instance->customint2, $mentor);
        }
        foreach ($cohort->mentor as $mentor) {
            //if (!in_array($mentor, explode(',', $team->mentor))) {
            // Enrollment of mentor
            if (!in_array($mentor, explode(',', $team->mentor))) {
                cohort_add_member($cohort->id, $mentor);
                role_assign($teacherrole->id, $mentor, $context->id);
            }
            //$teamcohort->enrol_user($instance, $mentor, $teacherrole->id);
            //echo "<br>Enrolling user: $mentor in course : $instance->courseid";
            //$teamcohort->enrol_user($instance, $mentor, $studentrole->id);
            // }
        }
    }

    $cohort->mentor = implode(',', $cohort->mentor);

    if ($team) {
        $team->cohortid = $cohort->id;
        if (!isset($cohort->setvisiblity)) {
            $team->mentor = $cohort->mentor;
        }
        if (isset($cohort->visible)) {
            $team->status = $cohort->visible;
        }
        $DB->update_record('local_team', $team);
    }
    $event = \core\event\cohort_updated::create(array(
                'context' => context::instance_by_id($cohort->contextid),
                'objectid' => $cohort->id,
    ));
    $event->trigger();
    //die;
}

/**
 * Delete team.
 * @param  stdClass $cohort
 * @return void
 */
function team_delete_team($cohort) {
    global $DB;
    if ($cohort->component) {
        // TODO: add component delete callback
    }

    $transaction = $DB->start_delegated_transaction();

    $DB->delete_records('cohort', array('id' => $cohort->id));
    $DB->delete_records('local_team', array('cohortid' => $cohort->id));
    $DB->delete_records('cohort_members', array('cohortid' => $cohort->id));
    $transaction->allow_commit();
    // Notify the competency subsystem.
    // \core_competency\api::hook_cohort_deleted($cohort);

    $event = \core\event\cohort_deleted::create(array(
                'context' => context::instance_by_id($cohort->contextid),
                'objectid' => $cohort->id,
    ));
    $event->add_record_snapshot('cohort', $cohort);
    $event->trigger();
}

/**
 * Somehow deal with teams when deleting course category,
 * we can not just delete them because they might be used in enrol
 * plugins or referenced in external systems.
 * @param  stdClass|coursecat $category
 * @return void
 */
function team_delete_category($category) {
    global $DB;
    // TODO: make sure that teams are really, really not used anywhere and delete, for now just move to parent or system context

    $oldcontext = context_coursecat::instance($category->id);

    if ($category->parent and $parent = $DB->get_record('course_categories', array('id' => $category->parent))) {
        $parentcontext = context_coursecat::instance($parent->id);
        $sql = "UPDATE {cohort} SET contextid = :newcontext WHERE contextid = :oldcontext";
        $params = array('oldcontext' => $oldcontext->id, 'newcontext' => $parentcontext->id);
    } else {
        $syscontext = context_system::instance();
        $sql = "UPDATE {cohort} SET contextid = :newcontext WHERE contextid = :oldcontext";
        $params = array('oldcontext' => $oldcontext->id, 'newcontext' => $syscontext->id);
    }

    $DB->execute($sql, $params);
}

/**
 * Add team member
 * @param  int $cohortid
 * @param  int $userid
 * @return void
 */
function team_add_member($teamid, $userid) {
    global $DB, $USER, $CFG;
    if ($DB->record_exists('cohort_members', array('cohortid' => $teamid, 'userid' => $userid))) {
        // No duplicates!
        return;
    }
    $record = new stdClass();
    $record->teamid = $teamid;
    $record->userid = $userid;
    $record->timecreated = time();
    $status = $DB->insert_record('cohort_members', $record);
    if ($status) {
        $adduser = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        $team = $DB->get_record_sql('SELECT c.name FROM {cohort} c INNER JOIN {local_team} lt '
                . 'ON lt.cohortid = c.id WHERE lt.id =:teamid', array('teamid' => $teamid));
        if (!$team) {
            return '';
        }
    }

    return $status;
    //return $DB->get_record('local_team', array('id' => $teamid), '*', MUST_EXIST);

    /* $event = \core\event\cohort_member_added::create(array(
      'context' => context::instance_by_id($cohort->contextid),
      'objectid' => $cohortid,
      'relateduserid' => $userid,
      ));
      $event->add_record_snapshot('cohort', $cohort);
      $event->trigger(); */
}

/**
 * Remove team member
 * @param  int $cohortid
 * @param  int $userid
 * @return void
 */
function team_remove_member($teamid, $userid) {
    global $DB, $USER;

    //$DB->delete_records('local_team_members', array('teamid' => $teamid, 'userid' => $userid));
    $deleteuser = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
    $team = $DB->get_record_sql('SELECT c.id as cohort,c.name FROM {cohort} c INNER JOIN {local_team} lt '
            . 'ON lt.cohortid = c.id WHERE lt.id =:teamid', array('teamid' => $teamid));
    if ($team->cohort) {
        $DB->delete_records('cohort_members', array('cohortid' => $team->cohort, 'userid' => $userid));
    }
    if (!$team) {
        return '';
    }
    $a = new stdClass();
    $a->name = $deleteuser->firstname;
    $a->team = $team->name;
    //$a->clink = $CFG->wwwroot . '/local/team/addtoteam.php?id=' . $teamid;
    //echo $a->clink;
    //send_team_notification('team_notification', get_string('notifyremovemembermessage', 'local_team', $a), get_string('notifyremovemembersubject', 'local_team', $team->name), $USER->id, $userid);
    //$cohort = $DB->get_record('cohort', array('id' => $cohortid), '*', MUST_EXIST);

    /* $event = \core\event\cohort_member_removed::create(array(
      'context' => context::instance_by_id($cohort->contextid),
      'objectid' => $cohortid,
      'relateduserid' => $userid,
      ));
      $event->add_record_snapshot('cohort', $cohort);
      $event->trigger(); */
}

/**
 * Returns the list of teams visible to the current user in the given course.
 *
 * The following fields are returned in each record: id, name, contextid, idnumber, visible
 * Fields memberscnt and enrolledcnt will be also returned if requested
 *
 * @param context $currentcontext
 * @param int $withmembers one of the COHORT_XXX constants that allows to return non empty teams only
 *      or teams with enroled/not enroled users, or just return members count
 * @param int $offset
 * @param int $limit
 * @param string $search
 * @return array
 */
function team_get_available_teams($currentcontext, $withmembers = 0, $offset = 0, $limit = 25, $search = '') {
    global $DB;

    $params = array();

    // Build context subquery. Find the list of parent context where user is able to see any or visible-only teams.
    // Since this method is normally called for the current course all parent contexts are already preloaded.
    $contextsany = array_filter($currentcontext->get_parent_context_ids(), function($a) {
        return has_capability("local/team:view", context::instance_by_id($a));
    });
    $contextsvisible = array_diff($currentcontext->get_parent_context_ids(), $contextsany);
    if (empty($contextsany) && empty($contextsvisible)) {
        // User does not have any permissions to view teams.
        return array();
    }
    $subqueries = array();
    if (!empty($contextsany)) {
        list($parentsql, $params1) = $DB->get_in_or_equal($contextsany, SQL_PARAMS_NAMED, 'ctxa');
        $subqueries[] = 'c.contextid ' . $parentsql;
        $params = array_merge($params, $params1);
    }
    if (!empty($contextsvisible)) {
        list($parentsql, $params1) = $DB->get_in_or_equal($contextsvisible, SQL_PARAMS_NAMED, 'ctxv');
        $subqueries[] = '(c.visible = 1 AND c.contextid ' . $parentsql . ')';
        $params = array_merge($params, $params1);
    }
    $wheresql = '(' . implode(' OR ', $subqueries) . ')';

    // Build the rest of the query.
    $fromsql = "";
    $fieldssql = 'c.id, c.name, c.contextid, c.idnumber, c.visible';
    $groupbysql = '';
    $havingsql = '';
    if ($withmembers) {
        $fieldssql .= ', s.memberscnt';
        $subfields = "c.id, COUNT(DISTINCT cm.userid) AS memberscnt";
        $groupbysql = " GROUP BY c.id";
        $fromsql = " LEFT JOIN {cohort_members} cm ON cm.cohortid = c.id ";
        if (in_array($withmembers, array(COHORT_COUNT_ENROLLED_MEMBERS, COHORT_WITH_ENROLLED_MEMBERS_ONLY, COHORT_WITH_NOTENROLLED_MEMBERS_ONLY))) {
            list($esql, $params2) = get_enrolled_sql($currentcontext);
            $fromsql .= " LEFT JOIN ($esql) u ON u.id = cm.userid ";
            $params = array_merge($params2, $params);
            $fieldssql .= ', s.enrolledcnt';
            $subfields .= ', COUNT(DISTINCT u.id) AS enrolledcnt';
        }
        if ($withmembers == COHORT_WITH_MEMBERS_ONLY) {
            $havingsql = " HAVING COUNT(DISTINCT cm.userid) > 0";
        } else if ($withmembers == COHORT_WITH_ENROLLED_MEMBERS_ONLY) {
            $havingsql = " HAVING COUNT(DISTINCT u.id) > 0";
        } else if ($withmembers == COHORT_WITH_NOTENROLLED_MEMBERS_ONLY) {
            $havingsql = " HAVING COUNT(DISTINCT cm.userid) > COUNT(DISTINCT u.id)";
        }
    }
    if ($search) {
        list($searchsql, $searchparams) = team_get_search_query($search);
        $wheresql .= ' AND ' . $searchsql;
        $params = array_merge($params, $searchparams);
    }

    if ($withmembers) {
        $sql = "SELECT " . str_replace('c.', 'cohort.', $fieldssql) . "
                  FROM {cohort} cohort
                  JOIN (SELECT $subfields
                          FROM {cohort} c $fromsql
                         WHERE $wheresql $groupbysql $havingsql
                        ) s ON cohort.id = s.id
              ORDER BY cohort.name, cohort.idnumber";
    } else {
        $sql = "SELECT $fieldssql
                  FROM {cohort} c $fromsql
                 WHERE $wheresql
              ORDER BY c.name, c.idnumber";
    }

    return $DB->get_records_sql($sql, $params, $offset, $limit);
}

/**
 * Check if team exists and user is allowed to access it from the given context.
 *
 * @param stdClass|int $cohortorid cohort object or id
 * @param context $currentcontext current context (course) where visibility is checked
 * @return boolean
 */
function team_can_view_team($cohortorid, $currentcontext) {
    global $DB;
    if (is_numeric($cohortorid)) {
        $cohort = $DB->get_record('cohort', array('id' => $cohortorid), 'id, contextid, visible');
    } else {
        $cohort = $cohortorid;
    }

    if ($cohort && in_array($cohort->contextid, $currentcontext->get_parent_context_ids())) {
        if ($cohort->visible) {
            return true;
        }
        $cohortcontext = context::instance_by_id($cohort->contextid);
        if (has_capability('moodle/cohort:view', $cohortcontext)) {
            return true;
        }
    }
    return false;
}

/**
 * Get a team by id. Also does a visibility check and returns false if the user cannot see this cohort.
 *
 * @param stdClass|int $cohortorid cohort object or id
 * @param context $currentcontext current context (course) where visibility is checked
 * @return stdClass|boolean
 */
function team_get_team($cohortorid, $currentcontext) {
    global $DB;
    if (is_numeric($cohortorid)) {
        $cohort = $DB->get_record('cohort', array('id' => $cohortorid), 'id, contextid, visible');
    } else {
        $cohort = $cohortorid;
    }

    if ($cohort && in_array($cohort->contextid, $currentcontext->get_parent_context_ids())) {
        if ($cohort->visible) {
            return $cohort;
        }
        $cohortcontext = context::instance_by_id($cohort->contextid);
        if (has_capability('local/team:view', $cohortcontext)) {
            return $cohort;
        }
    }
    return false;
}

/**
 * Produces a part of SQL query to filter teams by the search string
 *
 * Called from {@link team_get_teams()}, {@link team_get_all_teams()} and {@link team_get_available_teams()}
 *
 * @access private
 *
 * @param string $search search string
 * @param string $tablealias alias of team table in the SQL query (highly recommended if other tables are used in query)
 * @return array of two elements - SQL condition and array of named parameters
 */
function team_get_search_query($search, $tablealias = '') {
    global $DB;
    $params = array();
    if (empty($search)) {
        // This function should not be called if there is no search string, just in case return dummy query.
        return array('1=1', $params);
    }
    if ($tablealias && substr($tablealias, -1) !== '.') {
        $tablealias .= '.';
    }
    $searchparam = '%' . $DB->sql_like_escape($search) . '%';
    $conditions = array();
    $fields = array('name', 'idnumber', 'description');
    $cnt = 0;
    foreach ($fields as $field) {
        $conditions[] = $DB->sql_like($tablealias . $field, ':csearch' . $cnt, false);
        $params['csearch' . $cnt] = $searchparam;
        $cnt++;
    }
    $sql = '(' . implode(' OR ', $conditions) . ')';
    return array($sql, $params);
}

/**
 * Get all the teams defined in given context.
 *
 * The function does not check user capability to view/manage teams in the given context
 * assuming that it has been already verified.
 *
 * @param int $contextid
 * @param int $page number of the current page
 * @param int $perpage items per page
 * @param string $search search string
 * @return array    Array(totalteams => int, teams => array, allteams => int)
 */
function team_get_teams($contextid, $page = 0, $perpage = 25, $search = '', $company, $department) {
    global $DB, $USER;

    $fields = "SELECT DISTINCT c.*,lt.cohortcreator";
    $countfields = "SELECT COUNT(1)";
    $sql = " FROM {cohort} c
             JOIN {local_team} lt ON lt.cohortid = c.id
             WHERE contextid = :contextid AND lt.companyid =:company AND lt.departmentid =:department
             ";
    if (empty($owner)) {
        $owner = $USER->id;
    }

    $params = array('contextid' => $contextid, 'company' => $company, 'department' => $department);
    $order = " ORDER BY name ASC, idnumber ASC";
    $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql, $params);
    if (!empty($search)) {
        list($searchcondition, $searchparams) = team_get_search_query($search, 'c');
        $sql .= ' AND ' . $searchcondition;
        $params = array_merge($params, $searchparams);
        $totalcohorts = $DB->count_records_sql($countfields . $sql, $params);
    }

    //$totalcohorts = $allcohorts = $DB->count_records('cohort', array('contextid' => $contextid));
    // if (!empty($search)) {
    //}
    $cohorts = $DB->get_records_sql($fields . $sql . $order, $params, $page * $perpage, $perpage);
    return array('totalteams' => $totalcohorts, 'teams' => $cohorts, 'allteams' => $allcohorts);
}

/**
 * Get all the teams defined anywhere in system.
 *
 * The function assumes that user capability to view/manage teams on system level
 * has already been verified. This function only checks if such capabilities have been
 * revoked in child (categories) contexts.
 *
 * @param int $page number of the current page
 * @param int $perpage items per page
 * @param string $search search string
 * @return array    Array(totalcohorts => int, cohorts => array, allcohorts => int)
 */
function team_get_all_teams($page = 0, $perpage = 25, $search = '', $owner = '') {
    global $USER, $DB;

    if (empty($owner)) {
        $owner = $USER->id;
    }
    $fields = "SELECT concat('team_',c.id,'_user_',cm.userid) as teamuser,c.*, " . context_helper::get_preload_record_columns_sql('ctx');
    $countfields = "SELECT COUNT(DISTINCT c.id)";
    $sql = " FROM {cohort} c
             JOIN {local_team} lt ON lt.cohortid = c.id
             JOIN {cohort_members} cm ON cm.cohortid = c.id
             JOIN {context} ctx ON ctx.id = c.contextid ";
    $params = array();
    $wheresql = '';

    if ($excludedcontexts = team_get_invisible_contexts()) {
        list($excludedsql, $excludedparams) = $DB->get_in_or_equal($excludedcontexts, SQL_PARAMS_NAMED, 'excl', false);
        $wheresql = ' WHERE c.contextid ' . $excludedsql;
        $params = array_merge($params, $excludedparams);
    }


    // $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);


    if (!has_capability('local/team:manageallteam', context_system::instance())) {
        $wheresql .= ($wheresql ? ' AND ' : ' WHERE ') . ' cm.userid = ' . $owner . ' AND lt.status = 1';
    } else {
        $wheresql .= ($wheresql ? ' AND ' : ' WHERE ') . ' 1=1';
    }



    $order = " GROUP BY c.id ORDER BY c.name ASC, c.idnumber ASC";

    $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
    if (!empty($search)) {
        list($searchcondition, $searchparams) = team_get_search_query($search, 'c');
        $wheresql .= ($wheresql ? ' AND ' : ' WHERE ') . $searchcondition;
        $params = array_merge($params, $searchparams);
        $totalcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);
    }


    $cohorts = $DB->get_records_sql($fields . $sql . $wheresql . $order, $params, $page * $perpage, $perpage);
//print_object($cohorts);
    // Preload used contexts, they will be used to check view/manage/assign capabilities and display categories names.
    foreach (array_keys($cohorts) as $key) {
        context_helper::preload_from_record($cohorts[$key]);
    }

    return array('totalteams' => $totalcohorts, 'teams' => $cohorts, 'allteams' => $allcohorts);
}

/**
 * Returns list of contexts where teams are present but current user does not have capability to view/manage them.
 *
 * This function is called from {@link team_get_all_teams()} to ensure correct pagination in rare cases when user
 * is revoked capability in child contexts. It assumes that user's capability to view/manage teams on system
 * level has already been verified.
 *
 * @access private
 *
 * @return array array of context ids
 */
function team_get_invisible_contexts() {
    global $DB;
    if (is_siteadmin()) {
        // Shortcut, admin can do anything and can not be prohibited from any context.
        return array();
    }
    $records = $DB->get_recordset_sql("SELECT DISTINCT ctx.id, " . context_helper::get_preload_record_columns_sql('ctx') . " " .
            "FROM {context} ctx JOIN {cohort} c ON ctx.id = c.contextid ");
    $excludedcontexts = array();
    foreach ($records as $ctx) {
        context_helper::preload_from_record($ctx);
        if (!has_any_capability(array('local/team:manage', 'local/team:view'), context::instance_by_id($ctx->id))) {
            $excludedcontexts[] = $ctx->id;
        }
    }
    return $excludedcontexts;
}

/**
 * Returns navigation controls (tabtree) to be displayed on team management pages
 *
 * @param context $context system or category context where teams controls are about to be displayed
 * @param moodle_url $currenturl
 * @return null|renderable
 */
function team_edit_controls(context $context, moodle_url $currenturl) {
    $tabs = array();
    $currenttab = 'view';
    $viewurl = new moodle_url('/local/team/index.php', array('contextid' => $context->id));
    if (($searchquery = $currenturl->get_param('search'))) {
        $viewurl->param('search', $searchquery);
    }
    if ($context->contextlevel == CONTEXT_SYSTEM) {

        $tabs[] = new tabobject('view', $viewurl, get_string('teams', 'local_team'));
    }
    if (has_capability('local/team:manage', $context)) {
        $addurl = new moodle_url('/local/team/edit.php', array('contextid' => $context->id));
        $tabs[] = new tabobject('addcohort', $addurl, get_string('addteam', 'local_team'));
        if ($currenturl->get_path() === $addurl->get_path() && !$currenturl->param('id')) {
            $currenttab = 'addcohort';
        }

//        $uploadurl = new moodle_url('/local/team/enrolteam_course_form.php', array('contextid' => $context->id));
//        $tabs[] = new tabobject('assignteam', $uploadurl, get_string('assignteam', 'local_team'));
//        if ($currenturl->get_path() === $uploadurl->get_path()) {
//            $currenttab = 'assignteam';
//        }
//
//        $showteamcourses = new moodle_url('/local/team/showteamcourses.php', array('contextid' => $context->id));
//        $tabs[] = new tabobject('showteamcourses', $showteamcourses, get_string('showteamcourses', 'local_team'));
//        if ($currenturl->get_path() === $showteamcourses->get_path()) {
//            $currenttab = 'showteamcourses';
//        }
    }
    if (count($tabs) > 1) {
        return new tabtree($tabs, $currenttab);
    }
    return null;
}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function core_team_inplace_editable($itemtype, $itemid, $newvalue) {
    if ($itemtype === 'cohortname') {
        return \core_cohort\output\cohortname::update($itemid, $newvalue);
    } else if ($itemtype === 'cohortidnumber') {
        return \core_cohort\output\cohortidnumber::update($itemid, $newvalue);
    }
}

function is_allowed_to_assign($id) {
    global $DB, $USER;

    $cohort = $DB->get_record('cohort', array('id' => $id), '*', MUST_EXIST);
    $context = context::instance_by_id($cohort->contextid, MUST_EXIST);

    if (has_capability('local/team:manageallteam', $context)) {
        return true;
    }
    if (!has_capability('local/team:assign', $context)) {
        return false;
    }
    return $DB->get_record('local_team', array('cohortid' => $cohort->id));
}

function is_allowed_user($id) {
    global $DB, $USER;
    $cohort = $DB->get_record('cohort', array('id' => $id), '*', MUST_EXIST);
    $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
    if (has_capability('local/team:manageallteam', $context)) {
        return true;
    }
    if (!has_capability('local/team:manage', $context)) {
        return false;
    }

    return $DB->get_record('local_team', array('cohortid' => $cohort->id));
}

function get_members_list($teamid) {
    global $DB;
    $team = $DB->get_record('local_team', array('cohortid' => $teamid));
    $sql = "SELECT u.* FROM {user} u
                 JOIN {cohort_members} ltm ON ltm.userid = u.id
                WHERE ltm.cohortid = :cohortid AND u.id NOT IN ($team->mentor)";
    return $DB->get_records_sql($sql, array('cohortid' => $teamid));
}

function get_all_members($teamid) {
    global $DB;
    $team = $DB->get_record('local_team', array('cohortid' => $teamid));
    $sql = "SELECT u.* FROM {user} u
                 JOIN {cohort_members} ltm ON ltm.userid = u.id
                WHERE ltm.cohortid = :cohortid AND u.suspended = 0";
    return $DB->get_records_sql($sql, array('cohortid' => $teamid));
}

class enrol_cohort extends enrol_plugin {

    public function add_instance($course, array $fields = null) {
        global $CFG, $DB, $OUTPUT;

        if (!empty($fields['customint2']) && $fields['customint2'] == COHORT_CREATE_GROUP) {
            // Create a new group for the cohort if requested.
            $context = context_course::instance($course->id);
            $groupid = enrol_cohort_create_new_group($course->id, $fields['customint1']);
            $fields['customint2'] = $groupid;
        }

        $result = parent::add_instance($course, $fields);
        require_once("$CFG->dirroot/enrol/cohort/locallib.php");
        $trace = new null_progress_trace();
        enrol_team_sync($trace, $course->id, $fields);
        $trace->finished();
        // enrol teacher as well
        $teamdata = $DB->get_record('local_team', array('cohortid' => $fields['customint1']), '*', MUST_EXIST);

        if ($result) {
            require_once("$CFG->dirroot/group/lib.php");
            $instance = $DB->get_record('enrol', array('id' => $result));
            $teacherrole = $DB->get_record('role', array('shortname' => 'teacher'));
            if ($teacherrole) {
                if (strlen($team->mentor) > 1) {
                    try {
                        $this->enrol_user($instance, $teamdata->mentor, $teacherrole->id);
                        groups_add_member($groupid, $teamdata->mentor);
                    } catch (moodle_exception $e) {
                        $u = $DB->get_record('user', array('id' => $teamdata->mentor));
                        $OUTPUT->notification(fullname($u) . 'does not exists', 'warning');
                    }
                } else {
                    $teammentors = explode(",", $teamdata->mentor);
                    foreach ($teammentors as $teammentor) {
                        try {
                            $this->enrol_user($instance, $teammentor, $teacherrole->id);
                            groups_add_member($groupid, $teammentor);
                        } catch (moodle_exception $e) {
                            $u = $DB->get_record('user', array('id' => $teammentor));
                            $OUTPUT->notification(fullname($u) . 'does not exists', 'warning');
                        }
                    }
                }
            }
        }
        return $result;
    }

}

function is_team_already_exit_in_course($courseid, $teamid) {

    global $DB;
    return $DB->get_record('enrol', array('enrol' => 'cohort', 'courseid' => $courseid, 'customint1' => $teamid));
}

function unenroll_all($courseid, $unrolusers, $unenrollall) {
    global $DB, $PAGE;
    if ($unenrollall) {
        $courseobj = $DB->get_record('course', array('id' => $courseid));
        $courseenrolmentmanager = new course_enrolment_manager($PAGE, $courseobj);
        //Unenrol users which is already enrol into course from the teammember
        $unenrolusers = explode(",", $unrolusers);

        if ($unenrolusers) {
            foreach ($unenrolusers as $unroluser) {
                $ues = $courseenrolmentmanager->get_user_enrolments($unroluser);
                foreach ($ues as $ue) {
                    if ($ue->enrolmentinstance->courseid == $courseid) {
                        $courseenrolmentmanager->unenrol_user($ue);
                    }
                }
            }
        }
    }
}

function enrol_team_sync(progress_trace $trace, $courseid = NULL, $fields = '') {
    global $CFG, $DB;
    require_once("$CFG->dirroot/group/lib.php");
    $context = context_course::instance($courseid);
    // Purge all roles if cohort sync disabled, those can be recreated later here by cron or CLI.
    if (!enrol_is_enabled('cohort')) {
        $trace->output('Cohort sync plugin is disabled, unassigning all plugin roles and stopping.');
        role_unassign_all(array('component' => 'enrol_cohort'));
        return 2;
    }

    // Unfortunately this may take a long time, this script can be interrupted without problems.
    core_php_time_limit::raise();
    raise_memory_limit(MEMORY_HUGE);

    $trace->output('Starting user enrolment synchronisation...');

    $allroles = get_all_roles();
    $instances = array(); //cache

    $plugin = enrol_get_plugin('cohort');
    $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_UNENROL);

    $cohortwheresql = '';
    if (!empty($fields['customint1'])) { // If cohort id exist
        $cohortwheresql = ' AND cm.cohortid =:cohortid';
    }
    // Iterate through all not enrolled yet users.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT cm.userid, e.id AS enrolid, ue.status,cm.cohortid
              FROM {cohort_members} cm
              JOIN {enrol} e ON (e.customint1 = cm.cohortid AND e.enrol = 'cohort' AND e.status = :enrolstatus $onecourse)
              JOIN {user} u ON (u.id = cm.userid AND u.deleted = 0 AND u.suspended = 0)
         LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = cm.userid)
             WHERE (ue.id IS NULL OR ue.status = :suspended) $cohortwheresql";
    $params = array();
    $params['courseid'] = $courseid;
    $params['suspended'] = ENROL_USER_SUSPENDED;
    $params['enrolstatus'] = ENROL_INSTANCE_ENABLED;
    if (!empty($fields['customint1'])) {
        $params['cohortid'] = $fields['customint1'];
    }
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $ue) {
        if (!isset($instances[$ue->enrolid])) {
            $instances[$ue->enrolid] = $DB->get_record('enrol', array('id' => $ue->enrolid));
        }
        $team = $DB->get_record('local_team', array('cohortid' => $ue->cohortid));
        $instance = $instances[$ue->enrolid];
        $userroles = get_user_roles($context, $ue->userid);
        foreach ($userroles as $role) {
            if (in_array($role->shortname, array('companycoursenoneditor', 'companycourseeditor', 'teacher', 'editingteacher', 'manager'))) {
                if ($team && !in_array($ue->userid, explode(',', $team->mentor))) {
                    role_unassign($role->roleid, $role->userid, $role->contextid);
                }
            }
        }
        if ($ue->status == ENROL_USER_SUSPENDED) {
            $plugin->update_user_enrol($instance, $ue->userid, ENROL_USER_ACTIVE);
            $trace->output("unsuspending: $ue->userid ==> $instance->courseid via cohort $instance->customint1", 1);
        } else {
            $plugin->enrol_user($instance, $ue->userid);
            $trace->output("enrolling: $ue->userid ==> $instance->courseid via cohort $instance->customint1", 1);
        }
    }
    $rs->close();


    // Unenrol as necessary.
    $sql = "SELECT ue.*, e.courseid
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'cohort' $onecourse)
         LEFT JOIN {cohort_members} cm ON (cm.cohortid = e.customint1 AND cm.userid = ue.userid)
             WHERE cm.id IS NULL $cohortwheresql";
    $params = array();
    $params['courseid'] = $courseid;
    if (!empty($fields['customint1'])) {
        $params['cohortid'] = $fields['customint1'];
    }
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $ue) {
        if (!isset($instances[$ue->enrolid])) {
            $instances[$ue->enrolid] = $DB->get_record('enrol', array('id' => $ue->enrolid));
        }
        $instance = $instances[$ue->enrolid];
        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            // Remove enrolment together with group membership, grades, preferences, etc.
            $plugin->unenrol_user($instance, $ue->userid);
            $trace->output("unenrolling: $ue->userid ==> $instance->courseid via cohort $instance->customint1", 1);
        } else { // ENROL_EXT_REMOVED_SUSPENDNOROLES
            // Just disable and ignore any changes.
            if ($ue->status != ENROL_USER_SUSPENDED) {
                $plugin->update_user_enrol($instance, $ue->userid, ENROL_USER_SUSPENDED);
                $context = context_course::instance($instance->courseid);
                role_unassign_all(array('userid' => $ue->userid, 'contextid' => $context->id, 'component' => 'enrol_cohort', 'itemid' => $instance->id));
                $trace->output("suspending and unsassigning all roles: $ue->userid ==> $instance->courseid", 1);
            }
        }
    }
    $rs->close();
    unset($instances);


    $onecohort = !empty($fields['customint1']) ? "AND e.customint1 = :cohortid" : "";

    // Now assign all necessary roles to enrolled users - skip suspended instances and users.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT e.roleid, ue.userid, c.id AS contextid, e.id AS itemid, e.courseid
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = 'cohort' AND e.status = :statusenabled $onecourse $onecohort)
              JOIN {role} r ON (r.id = e.roleid)
              JOIN {context} c ON (c.instanceid = e.courseid AND c.contextlevel = :coursecontext)
              JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0)
         LEFT JOIN {role_assignments} ra ON (ra.contextid = c.id AND ra.userid = ue.userid AND ra.itemid = e.id AND ra.component = 'enrol_cohort' AND e.roleid = ra.roleid)
             WHERE ue.status = :useractive AND ra.id IS NULL";
    $params = array();
    $params['statusenabled'] = ENROL_INSTANCE_ENABLED;
    $params['useractive'] = ENROL_USER_ACTIVE;
    $params['coursecontext'] = CONTEXT_COURSE;
    $params['courseid'] = $courseid;
    $params['cohortid'] = $fields['customint1'];

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $ra) {
        role_assign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_cohort', $ra->itemid);
        $trace->output("assigning role: $ra->userid ==> $ra->courseid as " . $allroles[$ra->roleid]->shortname, 1);
    }
    $rs->close();

    $onecohort = !empty($fields['customint1']) ? "AND e.customint1 = :cohortid" : "";
    // Remove unwanted roles - sync role can not be changed, we only remove role when unenrolled.
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $sql = "SELECT ra.roleid, ra.userid, ra.contextid, ra.itemid, e.courseid
              FROM {role_assignments} ra
              JOIN {context} c ON (c.id = ra.contextid AND c.contextlevel = :coursecontext)
              JOIN {enrol} e ON (e.id = ra.itemid AND e.enrol = 'cohort' $onecourse $onecohort)
         LEFT JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = ra.userid AND ue.status = :useractive)
             WHERE ra.component = 'enrol_cohort' AND (ue.id IS NULL OR e.status <> :statusenabled)";
    $params = array();
    $params['statusenabled'] = ENROL_INSTANCE_ENABLED;
    $params['useractive'] = ENROL_USER_ACTIVE;
    $params['coursecontext'] = CONTEXT_COURSE;
    $params['courseid'] = $courseid;
    $params['cohortid'] = $fields['customint1'];
    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $ra) {
        role_unassign($ra->roleid, $ra->userid, $ra->contextid, 'enrol_cohort', $ra->itemid);
        $trace->output("unassigning role: $ra->userid ==> $ra->courseid as " . $allroles[$ra->roleid]->shortname, 1);
    }
    $rs->close();

    
    // Finally sync groups.
    $groupid ='';
    if($fields['customint2']){
        $groupid = $fields['customint2'];
    }
    //$affectedusers = groups_sync_with_enrolment('cohort', $courseid);
    $affectedusers = team_groups_sync_with_enrolment('cohort', $courseid,'customint2',$groupid);
    foreach ($affectedusers['removed'] as $gm) {
        $trace->output("removing user from group: $gm->userid ==> $gm->courseid - $gm->groupname", 1);
    }
    foreach ($affectedusers['added'] as $ue) {
        $trace->output("adding user to group: $ue->userid ==> $ue->courseid - $ue->groupname", 1);
    }

    $trace->output('...user enrolment synchronisation finished.');

    return 0;
}

function cohort_members_export_csv($cohortusers, $cohortid, $csvtype = 'cohortmembers') {
    global $DB;
    $team = $DB->get_record('cohort', array('id' => $cohortid));
    $export = new csv_export_writer();

    $filename = $team->idnumber;
    $export->set_filename("Cohort_report_" . $filename);
    $row = array();
    //$row[] = 'Team Creator';
    $row[] = 'Team Name';
    $row[] = 'Members';
    $row[] = 'Username';
    $row[] = 'Firstname';
    $row[] = 'Surname';
    $row[] = 'Email';
    $row[] = 'Company';
    $row[] = 'Department';
    $row[] = 'State';
    $row[] = 'City';
    $row[] = 'Created On';
    $row[] = 'Status';
    $row[] = "Designation";
    $row[] = "Level";

    $export->add_data($row);
    if (count($cohortusers) > 0) {
        $count = 1;
        foreach ($cohortusers as $key => $data) {

            if ($count == 1) {
                if (!empty($data->cohortcreator)) {
                    $cohort_creator = get_complete_user_data('id', $data->cohortcreator);
                    $cohortcreatorrow = array();
                    $sql1 = "
                        SELECT c.name as company_name,
                        d.name as department_name
                        FROM {user} as u 
                            INNER JOIN {company_users} cu ON cu.userid = u.id
                            INNER JOIN {company} c ON c.id =cu.companyid
                            INNER JOIN {department} d ON d.id = cu.departmentid
                        WHERE cu.userid=$cohort_creator->id";
                    //Get company and department name for mentors
                    $cohort_creator_company = $DB->get_record_sql($sql1, null);

                    //$row[] = fullname($cohort_creator);
                    $cohortcreatorrow[] = $data->cohortname;
                    $cohortcreatorrow[] = 'Creator';
                    $cohortcreatorrow[] = $cohort_creator->username;
                    $cohortcreatorrow[] = $cohort_creator->firstname;
                    $cohortcreatorrow[] = $cohort_creator->lastname;
                    $cohortcreatorrow[] = $cohort_creator->email;
                    $cohortcreatorrow[] = $cohort_creator_company->company_name;
                    $cohortcreatorrow[] = $cohort_creator_company->department_name;
                    $cohortcreatorrow[] = $cohort_creator->profile['state'];
                    $cohortcreatorrow[] = $cohort_creator->city;
                    $cohortcreatorrow[] = date('Y-m-d H:i:s', $cohort_creator->timecreated);
                    $cohortcreatorrow[] = $data->accountstatus;
                    $cohortcreatorrow[] = $cohort_creator->profile['designation'];
                    $cohortcreatorrow[] = $cohort_creator->profile['level'];
                    $export->add_data($cohortcreatorrow);
                }


                foreach (($data->mentor == 1) ? (array) $data->mentor : explode(",", $data->mentor) as $mentor) {
                    $dusers = $DB->get_record('user', array('id' => $mentor, 'deleted' => 0));
                    if (!empty($dusers)) {
                        $row = array();
                        $sql1 = "SELECT c.name as company_name,
                                d.name as department_name
                                FROM {user} as u 
                                INNER JOIN {company_users} cu ON cu.userid = u.id
                                INNER JOIN {company} c ON c.id =cu.companyid
                                INNER JOIN {department} d ON d.id = cu.departmentid
                                WHERE cu.userid=$mentor";
                        //Get company and department name for mentors
                        $mentor_company = $DB->get_record_sql($sql1, null);
                        //get mentor data
                        $cu = get_complete_user_data('id', $mentor);
                        //get cohort creator data
                        //$row[] = fullname($cohort_creator);
                        $row[] = $data->cohortname;
                        $row[] = 'Mentor';
                        $row[] = $cu->username;
                        $row[] = $cu->firstname;
                        $row[] = $cu->lastname;
                        $row[] = $cu->email;
                        $row[] = $mentor_company->company_name;
                        $row[] = $mentor_company->department_name;
                        $row[] = $cu->profile['state'];
                        $row[] = $cu->city;
                        $row[] = date('Y-m-d H:i:s', $cu->timecreated);
                        $row[] = $data->accountstatus;
                        $row[] = $cu->profile['designation'];
                        $row[] = $cu->profile['level'];
                        $export->add_data($row);
                    }
                }
            }

            if (!in_array($data->userid, explode(",", $data->mentor))) {
                $sql = "SELECT ud.data 
                        FROM {user_info_data} ud 
                        JOIN {user_info_field} uf ON uf.id = ud.fieldid
                        WHERE ud.userid = :userid AND uf.shortname = :fieldname";
                $params = array('userid' => $data->userid, 'fieldname' => 'state');

                $statevalue = $DB->get_record_sql($sql, $params);
                //get mentor data
                $cs = get_complete_user_data('id', $data->userid);

                $row = array();
                //$row[] = fullname($cohort_creator);
                $row[] = $data->cohortname;
                $row[] = 'Student';
                $row[] = $data->username;
                $row[] = $data->firstname;
                $row[] = $data->lastname;
                $row[] = $data->email;
                $row[] = $data->company_name;
                $row[] = $data->department_name;
                $row[] = $statevalue->data;
                $row[] = $data->city;
                $row[] = date('Y-m-d H:i:s', $cs->timecreated);
                $row[] = $data->accountstatus;
                $row[] = $cs->profile['designation'];
                $row[] = $cs->profile['level'];
                $export->add_data($row);
            }
            $count++;
        }
    }
    $export->download_file();
    exit;
}
function team_groups_sync_with_enrolment($enrolname, $courseid = 0, $gidfield = 'customint2',$groupid='') {
    global $DB;
    $onecourse = $courseid ? "AND e.courseid = :courseid" : "";
    $params = array(
        'enrolname' => $enrolname,
        'component' => 'enrol_'.$enrolname,
        'courseid' => $courseid
    );

    $affectedusers = array(
        'removed' => array(),
        'added' => array()
    );
    $groupsql = '';
    if($groupid){
        $params['groupid'] = $groupid;
        $groupsql = ' AND g.id=:groupid';
    }
    // Remove invalid.
    $sql = "SELECT ue.userid, ue.enrolid, e.courseid, g.id AS groupid, g.name AS groupname
              FROM {groups_members} gm
              JOIN {groups} g ON (g.id = gm.groupid)
              JOIN {enrol} e ON (e.enrol = :enrolname AND e.courseid = g.courseid $onecourse)
              JOIN {user_enrolments} ue ON (ue.userid = gm.userid AND ue.enrolid = e.id)
             WHERE gm.component=:component AND gm.itemid = e.id AND g.id <> e.{$gidfield} $groupsql";

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $gm) {
        groups_remove_member($gm->groupid, $gm->userid);
        $affectedusers['removed'][] = $gm;
    }
    $rs->close();

    // Add missing.
    $sql = "SELECT ue.userid, ue.enrolid, e.courseid, g.id AS groupid, g.name AS groupname
              FROM {user_enrolments} ue
              JOIN {enrol} e ON (e.id = ue.enrolid AND e.enrol = :enrolname $onecourse)
              JOIN {groups} g ON (g.courseid = e.courseid AND g.id = e.{$gidfield})
              JOIN {user} u ON (u.id = ue.userid AND u.deleted = 0)
         LEFT JOIN {groups_members} gm ON (gm.groupid = g.id AND gm.userid = ue.userid)
             WHERE gm.id IS NULL $groupsql";

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach ($rs as $ue) {
        groups_add_member($ue->groupid, $ue->userid, 'enrol_'.$enrolname, $ue->enrolid);
        $affectedusers['added'][] = $ue;
    }
    $rs->close();

    return $affectedusers;
}
