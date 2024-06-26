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

namespace format_tiles\output;

use core_courseformat\output\section_renderer;

/**
 * Basic renderer for tiles format.
 * @package format_tiles
 * @copyright 2022 David Watson
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends section_renderer {

    /**
     * Override this so that we can use our own local templates.
     * Used at present to ensure tiles specific editor controls are shown.
     * @return void
     */
    public function render_content() {
        $format = course_get_format($this->page->course->id);
        $contentclass = $format->get_output_classname('content');
        $section = 1; // TODO.
        $displayoptions = [];
        $contentoutput = new $contentclass(
            $format,
            $section,
            null,
            $displayoptions
        );

        $data = $contentoutput->export_for_template($this);

        echo $this->render_from_template('format_tiles/local/content', $data);
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page.
     *
     * @param \section_info|\stdClass $section The course_section entry from DB
     * @param \stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link.
     *
     * @param \section_info|\stdClass $section The course_section entry from DB
     * @param int|\stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Get the updated rendered version of a section.
     *
     * This method will only be used when the course editor requires to get an updated cm item HTML
     * to perform partial page refresh. It will be used for supporting the course editor webservices.
     *
     * By default, the template used for update a section is the same as when it renders initially,
     * but format plugins are free to override this method to provide extra effects or so.
     *
     * In tiles, we override so that when teacher hides/shows tile with AJAX, re-rendered sec includes photo/icon.
     * @see \format_tiles\output\section_renderer::render_section()
     * @see \format_tiles\output\courseformat\content\section\controlmenu::export_for_template()
     * @param \core_courseformat\base $format the course format
     * @param \section_info $section the section info
     * @return string the rendered element
     */
    public function course_section_updated(\core_courseformat\base $format, \section_info $section): string {
        $sectionclass = $format->get_output_classname('content\\section');
        $output = new $sectionclass($format, $section);
        $renderer = new \format_tiles\output\section_renderer($this->page, $this->target);

        // This ends up calling \format_tiles\output\section_renderer::render_section().
        return $renderer->render($output);
    }
}
