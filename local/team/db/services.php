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
 * This plugin provides access to Moodle data in form of analytics and reports in real time.
 *
 *
 * @package    local_team
 * @copyright  2019 Cohort
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// We defined the web service functions to install.
$functions = array(
        'local_team_search_users' => array(
                'classname'    => 'local_team_external',
                'methodname'   => 'search_users',
                'classpath'    => 'local/team/externallib.php',
                'description'  => 'Search for users.',
                'type'         => 'read',
                'capabilities' => '',
                'ajax'         => true,
        )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
        'vedificboard service' => array(
                'functions' => array (
                        'local_team_search_users',
                ),
                'restrictedusers' => 1,
                'enabled'=>1,
        )
);
