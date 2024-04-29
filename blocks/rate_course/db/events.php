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
 * Defines Workflow event handlers
 *
 * @package    block
 * @subpackage rate_course
 * @copyright  2009 Jenny Gray
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Code was Rewritten for Moodle 2.X By Atar + Plus LTD for Comverse LTD.
 * @copyright &copy; 2011 Comverse LTD.
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */


 /**
 * Removed old Event 1 API and implemented Event 2 API
 * refer - https://docs.moodle.org/dev/Event_2#Event_dispatching_and_observers
 *
 * @author  Jayesh T
 * @since   6 June 2019
 * @rlms
 */

/* List of observers. */

$observers = array(

    array(
        'eventname'   => '\core\event\course_deleted',
        'callback'    => 'blocks_rate_course::course_deleted',
        'includefile' => '/blocks/rate_course/classes/observer.php'
    ),
);