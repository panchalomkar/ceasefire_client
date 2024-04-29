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

// In case we ever want to switch back to ordinary certificates

function xmldb_local_rlmscourse_rating_install() {
    global $CFG, $DB;
        $ratings = new stdClass();
        $ratings->id = '';
        $ratings->shortname = "courserating";
        $ratings->name = "Allow Course Rating";
        $ratings->type = "checkbox";
        $ratings->categoryid = "2";
        $ratings->description = "";
        $ratings->descriptionformat = "1";
        $ratings->configdata = "0";
        $ratings->sortorder = "0";
        $ratings->timecreated = time();
        $ratings->timemodified = time();

        $DB->insert_record("customfield_field", $ratings); 
    return true;
}
