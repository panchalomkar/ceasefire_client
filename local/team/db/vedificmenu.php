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

// Define the iomad menu items that are defined by this plugin

function local_team_menu() {

        return array(
            'editteam' => array(
                'category' => 'Reports',
                'tab' => 3,
                'name' => get_string('assignteam', 'local_team'),
                'url' => '/local/team/enrolteam_course_form.php?contextid=1',
                'cap' => 'local/team:view',
                'icondefault' => 'assigncourses',
                'style' => 'department'
            ),
            'addteam' => array(
                'category' => 'Reports',
                'tab' => 2,
                'name' => get_string('pluginname', 'local_team'),
                'url' => '/local/team/index.php?contextid=1',
                'cap' => 'local/team:view',
                'icondefault' => 'assigndepartmentusers',
                'style' => 'department'
            ),
            'unenrolteam' => array(
                'category' => 'Reports',
                'tab' => 3,
                'name' => get_string('unenrolteam', 'local_team'),
                'url' => '/local/team/showteamcourses.php?contextid=1',
                'cap' => 'local/team:view',
                'icondefault' => 'managecoursesettings',
                'style' => 'department'
            )
        );
}
