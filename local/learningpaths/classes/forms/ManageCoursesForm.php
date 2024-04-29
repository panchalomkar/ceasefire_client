<?php
defined('MOODLE_INTERNAL') || die;

// Global vars definition.
global $CFG;
require_once("{$CFG->libdir}/formslib.php");

class ManageCoursesForm extends moodleform
{
    public function definition() {
        $mform = $this->_form;

        // Important Hidden fields.
        $mform->addElement('hidden', 'learningpathid', $this->_customdata['learningpath']);
        $mform->addElement('hidden', 'courseid', $this->_customdata['courseid']);
        $mform->addElement('hidden', 'form', "ManageCoursesForm");

        // Section pre-requisites title.
        $mform->addElement('html', html_writer::start_tag('div', array('class' => 'lp-course-prerequisites')));

        if (isset($this->_customdata['learningpath_courses'])) {
            $leftcolumn = $rightcolumn = '';
            // Build columns based on required.
            foreach ($this->_customdata['learningpath_courses'] as $course) {
                $coursename = (strlen($course->data->coursename) >= 40)?substr($course->data->coursename, 0, 40).'...':$course->data->coursename;
                if (!in_array($course->data->id, $this->_customdata['does_not_prerequisites'])) {
                    if (in_array($course->data->courseid, $this->_customdata['prerequisites'])) {
                        $params = array('data-courseid' => $course->data->courseid, 'class' => 'name');
                        $rightcolumn .= html_writer::start_tag('li', $params);
                            $params = array('class' => 'name-course');
                            $rightcolumn .= html_writer::tag('span', $coursename, $params);
                        $rightcolumn .= html_writer::end_tag('li');
                    } else {
                        if (!empty($course->data->courseid)) {
                            $params = array('data-courseid' => $course->data->courseid, 'class' => 'name');
                            $leftcolumn .= html_writer::start_tag('li', $params);
                             
                                $leftcolumn .= html_writer::start_tag('span',array('class' => 'name-course'));
                                     $leftcolumn .= html_writer::tag('i', '',array('class' => 'fa men men-icon-phbullets icons_bullets'));
                                     $leftcolumn .= html_writer::tag('p', $coursename);
                             $leftcolumn .= html_writer::end_tag('span');

                            $leftcolumn .= html_writer::end_tag('li');
                        }
                    }
                }
            }

            $params = array('class' => 'prerequisites-drag-and-drop', 'data-courseid' => $this->_customdata['courseid']);
            $columns = $this->get_courses_columns($leftcolumn, $rightcolumn, $course->data->id, $this->_customdata['courseid']);
            $mform->addElement('html', html_writer::tag('div', $columns, $params));
        }

        // Close pre-requisites section.
        $mform->addElement('html', html_writer::end_tag('div'));

        // Action buttons.
        $this->add_action_buttons();
    }

    private function get_courses_columns($leftcourses, $rightcourses, $courseid, $lpcourse) {
        // Left column, this column has courses availables to add as pre-requisites.
        $output = html_writer::start_tag('div', array('class' => 'row search_course'));
            $output .= html_writer::start_tag('div', array('class' => 'col-sm-5 first'));
                $output .= html_writer::start_tag('div', array('id' => 'searchbox', 'class' => 'searchbox', 'role' => 'search'));
                    $output .= html_writer::start_tag('div', array('class' => 'mt-search input-group custom-search-form'));
                        $output .= html_writer::start_tag('span', array('class' => 'input-group-btn'));
                            $params = array('class' => 'text-muted btn btn-default', 'type' => 'button', 'onclick' => '', 'aria-label' => '');
                            $output .= html_writer::start_tag('button', $params);
                                $params = array('class' => 'men men-search-phx fa fa-search header-txtmen men-icon-search i-search');
                                $output .= html_writer::tag('i', '', $params);
                            $output .= html_writer::end_tag('button');
                        $output .= html_writer::end_tag('span');
                        $params = array(
                            'class' => 'form-control search_courses available-courses-search',
                            'type' => 'text',
                            'placeholder' => get_string('search_course', 'local_learningpaths'),
                            'data-target' => 'available-prerequisites-' . $lpcourse,
                            'data-ttype' => 'class',
                            'data-parent' => 'no'
                        );
                        $output .= html_writer::tag('input', '', $params);
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');

            $output .= html_writer::tag('div', '', array('class' => 'col-sm-2'));

            $output .= html_writer::start_tag('div', array('class' => 'col-sm-5 two-search'));
                $output .= html_writer::start_tag('div', array('id' => 'searchbox', 'class' => 'searchbox', 'role' => 'search'));
                    $output .= html_writer::start_tag('div', array('class' => 'mt-search input-group custom-search-form'));
                        $output .= html_writer::start_tag('span', array('class' => 'input-group-btn'));
                            $params = array('class' => 'text-muted btn btn-default', 'type' => 'button', 'onclick' => '', 'aria-label' => '');
                            $output .= html_writer::start_tag('button', $params);
                                $params = array('class' => 'men men-search-phx fa fa-search header-txtmen men-icon-search i-search');
                                $output .= html_writer::tag('i', '', $params);
                            $output .= html_writer::end_tag('button');
                        $output .= html_writer::end_tag('span');
                        $params = array(
                            'class' => 'form-control search_courses assigned-courses-search',
                            'type' => 'text',
                            'placeholder' => get_string('search_course', 'local_learningpaths'),
                            'data-target' => 'added-prerequisites-' . $lpcourse,
                            'data-ttype' => 'class',
                            'data-parent' => 'no'
                        );
                        $output .= html_writer::tag('input', '', $params);
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        $output .= html_writer::start_tag('div', array('class' => 'row title'));
      

            $output .= html_writer::tag('div', '', array('class' => 'col-sm-2'));

      
        $output .= html_writer::end_tag('div');
         $output .= html_writer::start_tag('div', array('class' => 'row'));
        $output .= html_writer::start_tag('div', array('class' => 'col-sm-5'));
            $output .= html_writer::start_tag('div', array('class' => 'pad-all card-box'));
                $output .= html_writer::start_tag('div');
                    $output .= html_writer::start_tag('div');
                        $params = array(
                            'id' => 'available-prerequisites',
                            'class' => 'drag-and-drop-connected available-prerequisites-' . $lpcourse
                        );
                        $output .= html_writer::start_tag('ul', $params);
                              $output .= html_writer::start_tag('div' , array('class' => 'head_prerequisites'));
                                $output .= html_writer::tag('span', get_string('aviable_courses', 'local_learningpaths'));
                              $output .= html_writer::end_tag('div');
                            $output .= $leftcourses;
                        $output .= html_writer::end_tag('ul');
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
                $output .= html_writer::tag('div', '', array('class' => 'clearfix'));
            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        // Add and remove Buttons.
        $output .= html_writer::start_tag('div', array('class' => 'pad-all col-sm-2 center'));
            $params = array('class' => 'add-prerequisites btn btn-primary btn-round', 'data-courseid' => $courseid);
            $output .= html_writer::tag('button', get_string('add', 'local_learningpaths'), $params);
            $params = array('class' => 'remove-prerequisites btn btn-primary btn-round', 'data-courseid' => $courseid);
            $output .= html_writer::tag('button', get_string('remove', 'local_learningpaths'), $params);
        $output .= html_writer::end_tag('div');

        // Right column, this column has courses availables to add as pre-requisites.
        $output .= html_writer::start_tag('div', array('class' => 'col-sm-5'));
            $output .= html_writer::start_tag('div', array('class' => 'pad-all card-box'));
                $output .= html_writer::start_tag('div');
                    $output .= html_writer::start_tag('div');
                        $params = array(
                            'id' => 'added-prerequisites',
                            'class' => 'drag-and-drop-connected added-prerequisites-' . $lpcourse
                        );
                        $output .= html_writer::start_tag('ul', $params);
                              $output .= html_writer::start_tag('div' , array('class' => 'head_prerequisites'));
                                $output .= html_writer::tag('span', get_string('assigned_courses', 'local_learningpaths'));
                              $output .= html_writer::end_tag('div');
                            $output .= $rightcourses;
                        $output .= html_writer::end_tag('ul');
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
                $output .= html_writer::tag('div', '', array('class' => 'clearfix'));
            $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
    $output .= html_writer::end_tag('div');
        return $output;
    }

    // Add action buttons.
    public function add_action_buttons ($cancel = false, $submitlabel = null) {
        $mform = $this->_form;
        $buttonarray = array();

        if ($cancel) {
            $params = array('class' => 'btn btn-cancel');
            $buttonarray[] = &$mform->createElement('html', html_writer::tag('button', get_string('cancel'), $params));
        }

        if ($submitlabel !== false) {

            $submitlabel = get_string('save', 'local_learningpaths');
            $params = ['data-courseid' => $this->_customdata['courseid'], 'data-class' => 'submit-lpcourse'];
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel, $params);
        }

        $mform->addGroup($buttonarray, 'buttonar', '', array(''), false);
        $mform->setType('buttonar', PARAM_RAW);
        $mform->closeHeaderBefore('buttonar');
    }
}