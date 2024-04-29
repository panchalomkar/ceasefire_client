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

require_once($CFG->dirroot . '/lib/formslib.php');

class team_edit_form extends moodleform {

    /**
     * Define the team edit form
     */
    public function definition() {
        global $DB, $CFG;
        $mform = $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];
        $cohort = $this->_customdata['data'];
        $team = $this->_customdata['teamdata'];
        if ($team->id > 0) {
            $cohort->captain = $team->captain;
            $cohort->mentor = $team->mentor;
        }
        
        $mform->addElement('text', 'name', get_string('name', 'local_team'), 'maxlength="254" size="50"');
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        //$options = $this->get_category_options($cohort->contextid);
        //$mform->addElement('select', 'contextid', get_string('context', 'role'), $options);
        $mform->addElement('hidden', 'contextid', 1);
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('hidden', 'companyid', $this->_customdata['companyid']);
        $mform->setType('companyid', PARAM_INT);
        $mform->addElement('hidden', 'departmentid', $this->_customdata['departmentid']);
        $mform->setType('departmentid', PARAM_INT);
        /* $mform->addElement('text', 'idnumber', get_string('idnumber', 'local_team'), 'maxlength="254" size="50"');
          $mform->setType('idnumber', PARAM_RAW); // Idnumbers are plain text, must not be changed. */

        /* $mform->addElement('advcheckbox', 'visible', get_string('visible', 'team'));
          $mform->setDefault('visible', 1);
          $mform->addHelpButton('visible', 'visible', 'team'); */
        if ($cohort->id) {
            $memebers = get_members_list($cohort->id);
            $memeberslist = array('' => get_string('choose', 'local_team'));
            if ($memebers) {
                foreach ($memebers as $member) {
                    if ($member->id < 2) {  // excluding guest user from list
                        continue;
                    }
                    $memeberslist[$member->id] = fullname($member);
                }
            }
        }
        /*$options = array(
            'multiple' => true,
                //'noselectionstring' => get_string('selectuser', 'local_team'),
        );*/

        $options = array(
            'ajax' => ''.$CFG->wwwroot.'/local/team/form-user-selector.js',
            //'ajax'=>'local_team/select_mentor',
            'multiple' => true
        );

        // Get department users.
//        $users = company::get_recursive_department_users($this->_customdata['departmentid']);
//        if (!isset($cohort->mentor) || !$cohort->mentor) {
//            $userslist = array('' => get_string('choose', 'local_team'));
//        } else {
//            $mentor = $DB->get_record('user', array('id' => $cohort->mentor), '*', MUST_EXIST);
//            $userslist[$cohort->mentor] = fullname($mentor);
//        }
//        foreach ($users as $user) {
//            $user = $DB->get_record('user', array('id' => $user->userid), '*', MUST_EXIST);
//            $userslist[$user->id] = fullname($user);
//        }
        /* $options = array(
          'multiple' => false,
          'noselectionstring' => get_string('selectuser', 'search'),
          ); */

        //$mform->addElement('autocomplete', 'mentor', get_string('selectmentor', 'local_team'), $userslist, $options);
        //$mform->addRule('mentor', get_string('required'), 'required', null, 'client');
        $mform->addElement('hidden','mentor',0);

        $options = array(get_string('no'), get_string('yes'));
        // $mform->addElement('select', 'hidepicture', get_string('hidepicture'), $options);
        // $mform->addElement('filepicker', 'imagefile', get_string('newpicture', 'group'));
        // $mform->addHelpButton('imagefile', 'newpicture', 'group');
        $mform->addElement('editor', 'description_editor', get_string('description', 'local_team'), null, $editoroptions);
        $mform->setType('description_editor', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if (isset($this->_customdata['returnurl'])) {
            $mform->addElement('hidden', 'returnurl', $this->_customdata['returnurl']->out_as_local_url());
            $mform->setType('returnurl', PARAM_LOCALURL);
        }

        $this->add_action_buttons();

        $this->set_data($cohort);
    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        $idnumber = '';
        if ($idnumber === '') {
            // Fine, empty is ok.
        } else if ($data['id']) {
            $current = $DB->get_record('cohort', array('id' => $data['id']), '*', MUST_EXIST);
            if ($current->idnumber !== $idnumber) {
                if ($DB->record_exists('cohort', array('idnumber' => $idnumber))) {
                    $errors['idnumber'] = get_string('duplicateidnumber', 'local_team');
                }
            }
        } else {
            if ($DB->record_exists('cohort', array('idnumber' => $idnumber))) {
                $errors['idnumber'] = get_string('duplicateidnumber', 'local_team');
            }
        }

        if ($data['mentor'] === '') {
            $errors['mentor'] = get_string('required');
        }
        if (isset($data['captain'])) {
            if ($data['mentor'] === $data['captain']) {
                $errors['mentor'] = get_string('mentorcaptainerror', 'local_team');
            }
        }

        return $errors;
    }

    protected function get_category_options($currentcontextid) {
        global $CFG;
        require_once($CFG->libdir . '/coursecatlib.php');
        $displaylist = coursecat::make_categories_list('local/team:manage');
        $options = array();
        $syscontext = context_system::instance();
        if (has_capability('local/team:manage', $syscontext)) {
            $options[$syscontext->id] = $syscontext->get_context_name();
        }
        foreach ($displaylist as $cid => $name) {
            $context = context_coursecat::instance($cid);
            $options[$context->id] = $name;
        }
        // Always add current - this is not likely, but if the logic gets changed it might be a problem.
        if (!isset($options[$currentcontextid])) {
            $context = context::instance_by_id($currentcontextid, MUST_EXIST);
            $options[$context->id] = $syscontext->get_context_name();
        }
        return $options;
    }

    /**
     * Extend the form definition after the data has been parsed.
     */
    public function definition_after_data() {
        global $COURSE, $DB;

        $mform = $this->_form;
        $cohort = $this->_customdata['data'];
        $groupid = $mform->getElementValue('id');
        /*
          if ($group = $DB->get_record('local_team', array('cohortid' => $cohort->id))) {

          // Print picture.
          $group->name = $cohort->name;
          if (!($pic = print_team_picture($group,  true, true, false))) {
          $pic = get_string('none');
          if ($mform->elementExists('deletepicture')) {
          $mform->removeElement('deletepicture');
          }
          }
          $imageelement = $mform->getElement('currentpicture');
          $imageelement->setValue($pic);
          } else {
          if ($mform->elementExists('currentpicture')) {
          $mform->removeElement('currentpicture');
          }
          if ($mform->elementExists('deletepicture')) {
          $mform->removeElement('deletepicture');
          }
          }
         */
    }

}
