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
 * Strings for component 'profilefield_checkbox', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   profilefield_checkbox
 * @copyright  2008 onwards Shane Elliot {@link http://pukunui.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class profile_field_checkbox
 *
 * @copyright  2008 onwards Shane Elliot {@link http://pukunui.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lp_field_checkbox extends lp_profile_field_base {

    /**
     * Constructor method.
     * Pulls out the options for the checkbox from the database and sets the
     * the corresponding key for the data if it exists
     *
     * @param int $fieldid
     * @param int $lpid
     */
    public function lp_field_checkbox($fieldid=0, $lpid=0) {
        global $DB;
        // First call parent constructor.
        $this->lp_profile_field_base($fieldid, $lpid);

        if (!empty($this->field)) {
            $datafield = $DB->get_field('lp_info_data', 'data', array('lpid' => $lpid, 'fieldid' => $fieldid));
            
            if ($datafield !== false) {
                $this->data = $datafield;
            } else {
                $this->data = $this->field->defaultdata;
            }
        }
    }

    /**
     * Add elements for editing the profile field value.
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {
        // Create the form field.
       
        $checkbox = $mform->addElement('advcheckbox', $this->inputname, format_string($this->field->name));
        if ($this->data == '1') {
            $checkbox->setChecked(true);
        }
        $mform->setType($this->inputname, PARAM_BOOL);
        if ($this->is_required() and !has_capability('moodle/user:update', context_system::instance())) {
            $mform->addRule($this->inputname, get_string('required'), 'nonzero', null, 'client');
        }
      
    }

    /**
     * Display the data for this field
     *
     * @return string HTML.
     */
    public function display_data() {
        $options = new stdClass();
        $options->para = false;
        $checked = intval($this->data) === 1 ? 'checked="checked"' : '';
        return '<input disabled="disabled" type="checkbox" name="'.$this->inputname.'" '.$checked.' />';
    }

}


