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
 * Trail Format - A topics based format that uses a trail of user selectable images to popup a light box of the section.
 *
 * @package    format_trail
 * @copyright  &copy; 2019 Jose Wilson  in respect to modifications of grid format.
 * @author     &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 * @author     G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 * @author     Based on code originally written by Paul Krix and Julian Ridden.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/lib.php'); // For format_base.

/**
 * format_trail class
 *
 * @package    format_trail
 * @copyright  &copy; 2019 Jose Wilson  in respect to modifications of grid format.
 * @author     &copy; 2012 G J Barnard in respect to modifications of standard topics format.
 */
class format_trail extends format_base {

    // CONTRIB-4099.
    /**
     * @var array $imagecontainerwidths.
     */
    private static $imagecontainerwidths = array(192 => '192', 210 => '210');

    /**
     * @var array $imagecontainerratios.
     */
    private static $imagecontainerratios = array(
        1 => '3-2', 2 => '3-1');

    /**
     * @var array $borderwidths.
     */
    private static $borderwidths = array(0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7',
        8 => '8', 9 => '9', 10 => '10');
    /* Image holder height and new activity position for all on the basis that once calculated the majority of courses
      will be the same. */

    /**
     * @var int $currentwidth.
     */
    private static $currentwidth = 210;

    /**
     * @var int $currentratio.
     */
    private static $currentratio = 1; // 3-2.
    /**
     * @var int $currentborderwidth.
     */
    private static $currentborderwidth = 3;

    /**
     * @var int $currentheight.
     */
    private static $currentheight = 140;

    /**
     * @var int $activitymargintop.
     */
    private static $activitymargintop = 101;

    /**
     * @var int $activitymarginleft.
     */
    private static $activitymarginleft = 1118;

    /**
     * @var array $opacities.
     */
    private static $opacities = array('0' => '0.0', '.1' => '0.1', '.2' => '0.2', '.3' => '0.3', '.4' => '0.4',
        '.5' => '0.5', '.6' => '0.6', '.7' => '0.7', '.8' => '0.8', '.9' => '0.9', '1' => '1.0');

    /**
     * @var array $sectiontitlefontsizes.
     */
    private static $sectiontitlefontsizes = array(0 => '0', 12 => '12', 13 => '13', 14 => '14', 15 => '15', 16 => '16',
        17 => '17', 18 => '18', 19 => '19', 20 => '20', 21 => '21', 22 => '22', 23 => '23', 24 => '24');

    /**
     * @var string $settings.
     */
    private $settings;

    /**
     * @var boolean $section0attop.
     */
    private $section0attop = null;

    /**
     * Creates a new instance of class
     *
     * @param string $format
     * @param int $courseid
     * @return format_trail
     */
    protected function __construct($format, $courseid) {
        if ($courseid === 0) {
            global $COURSE;
            $courseid = $COURSE->id;  // Save lots of global $COURSE as we will never be the site course.
        }
        parent::__construct($format, $courseid);
    }

    /**
     * States if section 0 is at the top.
     *
     * @return string The default value for the section name.
     */
    public function is_section0_attop() {
        if (is_null($this->section0attop)) {
            $this->section0attop = $this->get_summary_visibility($this->courseid)->showsummary == 1;
        }
        return $this->section0attop;
    }

    /**
     * Returns the default section name for the format.
     *
     * @param stdClass $section Section object from database or just field course_sections section
     * @return string The default value for the section name.
     */
    public function get_default_section_name($section) {
        /* Follow the same logic so that this method is supported.  The MDL-51610 enchancement refactored things,
          but that is not appropriate for us. */
        return $this->get_section_name($section);
    }

    /**
     * Prevents ability to change a static variable outside of the class.
     * @return array Array of horizontal alignments.
     */
    public static function get_horizontal_alignments() {
        $imagecontaineralignments = array(
            'left' => get_string('left', 'format_trail'),
            'center' => get_string('centre', 'format_trail'),
            'right' => get_string('right', 'format_trail')
        );
        return $imagecontaineralignments;
    }

    /**
     * Gets the default image container width.
     * @return int Default image container alignment.
     */
    public static function get_default_image_container_alignment() {
        return 'center';
    }

    /**
     * Prevents ability to change a static variable outside of the class.
     * @return array Array of imagecontainer widths.
     */
    public static function get_image_container_widths() {
        return self::$imagecontainerwidths;
    }

    /**
     * Gets the default image container width.
     * @return int Default image container width.
     */
    public static function get_default_image_container_width() {
        return 210;
    }

    /**
     * Prevents ability to change a static variable outside of the class.
     * @return array Array of image container ratios.
     */
    public static function get_image_container_ratios() {
        return self::$imagecontainerratios;
    }

    /**
     * Gets the default image container ratio.
     * @return int Default image container ratio.
     */
    public static function get_default_image_container_ratio() {
        return 1; // Ratio of '3-2'.
    }

    /**
     * Gets the default image resize method.
     * @return int Default image resize method.
     */
    public static function get_default_image_resize_method() {
        return 1; // Scale.
    }

    /**
     * Gets the default border colour.
     * @return string Default border colour.
     */
    public static function get_default_border_colour() {
        return '#dddddd';
    }

    /**
     * Prevents ability to change a static variable outside of the class.
     * @return array Array of border widths.
     */
    public static function get_border_widths() {
        return self::$borderwidths;
    }

    /**
     * Gets the default border width.
     * @return int Default border width.
     */
    public static function get_default_border_width() {
        return 3; // Pixels.
    }

    /**
     * Gets the default border width.
     * @return int Default border width.
     */
    public static function get_default_border_radius() {
        return 2; // On.
    }

    /**
     * Gets the default image container background colour.
     * @return string Default image container background colour.
     */
    public static function get_default_image_container_background_colour() {
        return '#f1f2f2';
    }

    /**
     * Gets the default current selected section colour.
     * @return string Default current selected section colour.
     */
    public static function get_default_current_selected_section_colour() {
        return '#8E66FF';
    }

    /**
     * Gets the default current selected image container text colour.
     * @return string Default current selected image container text colour.
     */
    public static function get_default_current_selected_image_container_text_colour() {
        return '#3b53ad';
    }

    /**
     * Gets the default current selected image container colour.
     * @return string Default current selected image container colour.
     */
    public static function get_default_current_selected_image_container_colour() {
        return '#ffc540';
    }

    /**
     * Gets the default hide section title.
     * @return int Default default hide section title.
     */
    public static function get_default_hide_section_title() {
        return 1; // No.
    }

    /**
     * Gets the default section title trail length max option.
     * @return int Default default section title trail length max option.
     */
    public static function get_default_section_title_trail_length_max_option() {
        return 0; // No truncation.
    }

    /**
     * Gets the default section title box position.
     * @return int Default default section title box position.
     */
    public static function get_default_section_title_box_position() {
        return 2; // Outside.
    }

    /**
     * Gets the default section title box inside position.
     * @return int Default default section title box inside position.
     */
    public static function get_default_section_title_box_inside_position() {
        return 1; // Top.
    }

    /**
     * Gets the default section title box height.
     * @return int Default default section title box height.
     */
    public static function get_default_section_title_box_height() {
        return 0; // Calculated.
    }

    /**
     * Gets the default opacity for the section title box.
     * @return string Opacity of section title box.
     */
    public static function get_default_section_title_box_opacity() {
        return '.8';
    }

    /**
     * Gets the default opacities.
     * @return Array Opacities.
     */
    public static function get_default_opacities() {
        return self::$opacities;
    }

    /**
     * Gets the default font size for the section title.
     * @return int Font size of the section title.
     */
    public static function get_default_section_title_font_size() {
        return 0;
    }

    /**
     * Gets the default alignment for the section title.
     * @return string Alignment of the section title.
     */
    public static function get_default_section_title_alignment() {
        return 'center';
    }

    /**
     * Gets the default section title font sizes.
     * @return Array Font sizes.
     */
    public static function get_default_section_font_sizes() {
        return self::$sectiontitlefontsizes;
    }

    /**
     * Gets the default section title inside text colour.
     * @return string Default default section title inside text colour.
     */
    public static function get_default_section_title_inside_title_text_colour() {
        return '#000000';
    }

    /**
     * Gets the default section title inside background colour.
     * @return string Default default section title inside background colour.
     */
    public static function get_default_section_title_inside_title_background_colour() {
        return '#ffffff';
    }

    /**
     * Gets the default show section title summary.
     * @return int Default default show section title summary.
     */
    public static function get_default_show_section_title_summary() {
        return 2; // Yes.
    }

    /**
     * Gets the default set show section title summary position.
     * @return int Default default set show section title summary position.
     */
    public static function get_default_set_show_section_title_summary_position() {
        return 1; // Top.
    }

    /**
     * Gets the default set show section title summary position.
     * @return int Default default set show section title summary position.
     */
    public static function get_default_set_show_background() {
        return 1; // Top.
    }

    /**
     * Gets the default section title summary max length.
     * @return int Default default section title summary max length.
     */
    public static function get_default_section_title_summary_max_length() {
        return 0; // No truncation.
    }

    /**
     * Gets the default section title summary on hover text colour.
     * @return string Default section title summary on hover text colour.
     */
    public static function get_default_section_title_summary_text_colour() {
        return '#3b53ad';
    }

    /**
     * Gets the default section title summary on hover background colour.
     * @return string Default section title summary on hover background colour.
     */
    public static function get_default_section_title_summary_background_colour() {
        return '#ffc540';
    }

    /**
     * Gets the default opacity for the section title summary on hover.
     * @return string Opacity of section title summary on hover.
     */
    public static function get_default_section_title_summary_opacity() {
        return '1';
    }

    /**
     * Gets the displayed image path for storage of the displayed image.
     * @return string The path.
     */
    public static function get_image_path() {
        return '/trailimage/';
    }

    /**
     * Gets the max width image.
     * @return int The path.
     */
    public static function get_maximum_image_width() {
        return 768;
    }

    /**
     * Returns the format's settings and gets them if they do not exist.
     * @return array The settings as an array.
     */
    public function get_settings() {
        if (empty($this->settings) == true) {
            $this->settings = $this->get_format_options();
        }
        return $this->settings;
    }

    /**
     * Returns the mapped value of the 'setshowsectiontitlesummaryposition' setting.
     * @return string One of 'top', 'bottom', 'left' or 'right'.
     */
    public function get_set_show_section_title_summary_position() {
        $settings = $this->get_settings();
        $returnvalue = 'top';

        switch ($settings['setshowsectiontitlesummaryposition']) {
            case 1:
                $returnvalue = 'top';
                break;
            case 2:
                $returnvalue = 'bottom';
                break;
            case 3:
                $returnvalue = 'left';
                break;
            case 4:
                $returnvalue = 'right';
                break;
        }

        return $returnvalue;
    }

    /**
     * Gets the name for the provided section.
     *
     * @param stdClass $section The section.
     * @return string The section name.
     */
    public function get_section_name($section) {
        $section = $this->get_section($section);
        if (!empty($section->name)) {
            return format_string($section->name, true, array('context' => $this->get_context()));
        } if ($section->section == 0) {
            return get_string('topic0', 'format_trail');
        } else {
            return get_string('topic', 'format_trail') . ' ' . $section->section;
        }
    }

    /**
     * Indicates this format uses sections.
     *
     * @return bool Returns true
     */
    public function uses_sections() {
        return true;
    }

    /**
     * The URL to use for the specified course (with section)
     *
     * @param int|stdClass $section Section object from database or just field course_sections.section
     *     if omitted the course view page is returned
     * @param array $options options for view URL. At the moment core uses:
     *     'navigation' (bool) if true and section has no separate page, the function returns null
     *     'sr' (int) used by multipage formats to specify to which section to return
     * @return null|moodle_url
     */
    public function get_view_url($section, $options = array()) {
        $course = $this->get_course();
        $url = new moodle_url('/course/view.php', array('id' => $course->id));

        $sr = null;
        if (array_key_exists('sr', $options)) {
            $sr = $options['sr'];
        }
        if (is_object($section)) {
            $sectionno = $section->section;
        } else {
            $sectionno = $section;
        }
        if ($sectionno !== null) {
            if ($sr !== null) {
                if ($sr) {
                    $usercoursedisplay = COURSE_DISPLAY_MULTIPAGE;
                    $sectionno = $sr;
                } else {
                    $usercoursedisplay = COURSE_DISPLAY_SINGLEPAGE;
                }
            } else {
                $usercoursedisplay = $course->coursedisplay;
            }
            if ($sectionno != 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                $url->param('section', $sectionno);
            } else if ($sectionno == 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE && (!$this->is_section0_attop())) {
                $url->param('section', $sectionno);
            } else if ($sectionno == 0 && $usercoursedisplay == COURSE_DISPLAY_MULTIPAGE && $this->is_section0_attop() &&
                    ($this->get_settings()['setsection0ownpagenotrailonesection'] == 2)) {
                $url->param('section', $sectionno);
            } else {
                $url->set_anchor('section-' . $sectionno);
            }
        }
        return $url;
    }

    /**
     * Returns the information about the ajax support in the given source format
     *
     * The returned object's property (boolean)capable indicates that
     * the course format supports Moodle course ajax features.
     *
     * @return stdClass
     */
    public function supports_ajax() {
        $ajaxsupport = new stdClass();
        $ajaxsupport->capable = true;
        return $ajaxsupport;
    }

    /**
     * Custom action after section has been moved in AJAX mode
     *
     * Used in course/rest.php
     *
     * @return array This will be passed in ajax respose
     */
    public function ajax_section_move() {
        global $PAGE;
        $titles = array();
        $course = $this->get_course();
        $modinfo = get_fast_modinfo($course);
        $renderer = $this->get_renderer($PAGE);
        if ($renderer && ($sections = $modinfo->get_section_info_all())) {
            foreach ($sections as $number => $section) {
                $titles[$number] = $renderer->section_title($section, $course);
            }
        }
        return array('sectiontitles' => $titles, 'action' => 'move');
    }

    /**
     * Returns the list of blocks to be automatically added for the newly created course
     *
     * @return array of default blocks, must contain two keys BLOCK_POS_LEFT and BLOCK_POS_RIGHT
     *     each of values is an array of block names (for left and right side columns)
     */
    public function get_default_blocks() {
        return array(
            BLOCK_POS_LEFT => array(),
            BLOCK_POS_RIGHT => array('search_forums', 'news_items', 'calendar_upcoming', 'recent_activity')
        );
    }

    /**
     * Definitions of the additional options that this course format uses for the course.
     *
     * @param bool $foreditform
     * @return array of options
     */
    public function course_format_options($foreditform = false) {
        static $courseformatoptions = false;
        $courseconfig = null;

        if ($courseformatoptions === false) {
            /* Note: Because 'admin_setting_configcolourpicker' in 'settings.php' needs to use a prefixing '#'
              this needs to be stripped off here if it's there for the format's specific colour picker. */
            $defaults = $this->get_course_format_colour_defaults();

            $courseconfig = get_config('moodlecourse');
            $courseid = $this->get_courseid();
            if ($courseid == 1) { // New course.
                $defaultnumsections = $courseconfig->numsections;
            } else { // Existing course that may not have 'numsections' - see get_last_section().
                global $DB;
                $defaultnumsections = $DB->get_field_sql('SELECT max(section) from {course_sections}
                    WHERE course = ?', array($courseid));
            }
            $courseformatoptions = array(
                'numsections' => array(
                    'default' => $defaultnumsections,
                    'type' => PARAM_INT,
                ),
                'hiddensections' => array(
                    'default' => $courseconfig->hiddensections,
                    'type' => PARAM_INT
                ),
                'coursedisplay' => array(
                    'default' => $courseconfig->coursedisplay,
                    'type' => PARAM_INT
                ),
                'hidenavside' => array(
                    'default' => get_config('format_trail', 'defaulthidenavside'),
                    'type' => PARAM_INT
                ),
                'hidesectionlock' => array(
                    'default' => get_config('format_trail', 'defaulthidesectionlock'),
                    'type' => PARAM_INT
                ),
                'showbackground' => array(
                    'default' => get_config('format_trail', 'defaultsetshowbackground'),
                    'type' => PARAM_ALPHANUM
                ),
                'showcheckstar' => array(
                    'default' => get_config('format_trail', 'defaultsetshowcheckstar'),
                    'type' => PARAM_ALPHANUM
                ),
                'imagecontaineralignment' => array(
                    'default' => get_config('format_trail', 'defaultimagecontaineralignment'),
                    'type' => PARAM_ALPHANUM
                ),
                'imagecontainerwidth' => array(
                    'default' => get_config('format_trail', 'defaultimagecontainerwidth'),
                    'type' => PARAM_INT
                ),
                'imagecontainerratio' => array(
                    'default' => get_config('format_trail', 'defaultimagecontainerratio'),
                    'type' => PARAM_ALPHANUM
                ),
                'imageresizemethod' => array(
                    'default' => get_config('format_trail', 'defaultimageresizemethod'),
                    'type' => PARAM_INT
                ),
                'bordercolour' => array(
                    'default' => $defaults['defaultbordercolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'borderwidth' => array(
                    'default' => get_config('format_trail', 'defaultborderwidth'),
                    'type' => PARAM_INT
                ),
                'borderradius' => array(
                    'default' => get_config('format_trail', 'defaultborderradius'),
                    'type' => PARAM_INT
                ),
                'imagecontainerbackgroundcolour' => array(
                    'default' => $defaults['defaultimagecontainerbackgroundcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'currentselectedsectioncolour' => array(
                    'default' => $defaults['defaultcurrentselectedsectioncolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'currentselectedimagecontainertextcolour' => array(
                    'default' => $defaults['defaultcurrentselectedimagecontainertextcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'currentselectedimagecontainercolour' => array(
                    'default' => $defaults['defaultcurrentselectedimagecontainercolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'hidesectiontitle' => array(
                    'default' => get_config('format_trail', 'defaulthidesectiontitle'),
                    'type' => PARAM_INT
                ),
                'sectiontitletraillengthmaxoption' => array(
                    'default' => get_config('format_trail', 'defaultsectiontitletraillengthmaxoption'),
                    'type' => PARAM_INT
                ),
                'sectiontitleboxposition' => array(
                    'default' => get_config('format_trail', 'defaultsectiontitleboxposition'),
                    'type' => PARAM_INT
                ),
                'sectiontitleboxinsideposition' => array(
                    'default' => get_config('format_trail', 'defaultsectiontitleboxinsideposition'),
                    'type' => PARAM_INT
                ),
                'sectiontitleboxheight' => array(
                    'default' => get_config('format_trail', 'defaultsectiontitleboxheight'),
                    'type' => PARAM_INT
                ),
                'sectiontitleboxopacity' => array(
                    'default' => get_config('format_trail', 'defaultsectiontitleboxopacity'),
                    'type' => PARAM_RAW
                ),
                'sectiontitlefontsize' => array(
                    'default' => get_config('format_trail', 'defaultsectiontitlefontsize'),
                    'type' => PARAM_INT
                ),
                'sectiontitlealignment' => array(
                    'default' => get_config('format_trail', 'defaultsectiontitlealignment'),
                    'type' => PARAM_ALPHANUM
                ),
                'sectiontitleinsidetitletextcolour' => array(
                    'default' => $defaults['defaultsectiontitleinsidetitletextcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'sectiontitleinsidetitlebackgroundcolour' => array(
                    'default' => $defaults['defaultsectiontitleinsidetitlebackgroundcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'showsectiontitlesummary' => array(
                    'default' => get_config('format_trail', 'defaultshowsectiontitlesummary'),
                    'type' => PARAM_INT
                ),
                'setshowsectiontitlesummaryposition' => array(
                    'default' => get_config('format_trail', 'defaultsetshowsectiontitlesummaryposition'),
                    'type' => PARAM_INT
                ),
                'sectiontitlesummarymaxlength' => array(
                    'default' => get_config('format_trail', 'defaultsectiontitlesummarymaxlength'),
                    'type' => PARAM_INT
                ),
                'sectiontitlesummarytextcolour' => array(
                    'default' => $defaults['defaultsectiontitlesummarytextcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'sectiontitlesummarybackgroundcolour' => array(
                    'default' => $defaults['defaultsectiontitlesummarybackgroundcolour'],
                    'type' => PARAM_ALPHANUM
                ),
                'sectiontitlesummarybackgroundopacity' => array(
                    'default' => get_config('format_trail', 'defaultsectiontitlesummarybackgroundopacity'),
                    'type' => PARAM_RAW
                ),
                'newactivity' => array(
                    'default' => get_config('format_trail', 'defaultnewactivity'),
                    'type' => PARAM_INT
                ),
                'fitsectioncontainertowindow' => array(
                    'default' => get_config('format_trail', 'defaultfitsectioncontainertowindow'),
                    'type' => PARAM_INT
                ),
                'greyouthidden' => array(
                    'default' => get_config('format_trail', 'defaultgreyouthidden'),
                    'type' => PARAM_INT
                ),
                'setsection0ownpagenotrailonesection' => array(
                    'default' => get_config('format_trail', 'defaultsection0ownpagenotrailonesection'),
                    'type' => PARAM_INT
                )
            );
        }
        if ($foreditform && !isset($courseformatoptions['coursedisplay']['label'])) {
            /* Note: Because 'admin_setting_configcolourpicker' in 'settings.php' needs to use a prefixing '#'
              this needs to be stripped off here if it's there for the format's specific colour picker. */
            $defaults = $this->get_course_format_colour_defaults();

            $context = $this->get_context();

            if (is_null($courseconfig)) {
                $courseconfig = get_config('moodlecourse');
            }
            $sectionmenu = array();
            for ($i = 0; $i <= $courseconfig->maxsections; $i++) {
                $sectionmenu[$i] = "$i";
            }
            $courseformatoptionsedit = array(
                'numsections' => array(
                    'label' => new lang_string('numbersections', 'format_trail'),
                    'element_type' => 'select',
                    'element_attributes' => array($sectionmenu),
                ),
                'hiddensections' => array(
                    'label' => new lang_string('hiddensections'),
                    'help' => 'hiddensections',
                    'help_component' => 'moodle',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            0 => new lang_string('hiddensectionscollapsed'),
                            1 => new lang_string('hiddensectionsinvisible')
                        )
                    ),
                ),
                'coursedisplay' => array(
                    'label' => new lang_string('coursedisplay'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            COURSE_DISPLAY_SINGLEPAGE => new lang_string('coursedisplay_single'),
                            COURSE_DISPLAY_MULTIPAGE => new lang_string('coursedisplay_multi')
                        )
                    ),
                    'help' => 'coursedisplay',
                    'help_component' => 'moodle',
                )
            );

            if (has_capability('format/trail:changeimagecontaineralignment', $context)) {
                $courseformatoptionsedit['imagecontaineralignment'] = array(
                    'label' => new lang_string('setimagecontaineralignment', 'format_trail'),
                    'help' => 'setimagecontaineralignment',
                    'help_component' => 'format_trail',
                    'element_type' => 'select',
                    'element_attributes' => array(self::get_horizontal_alignments())
                );
            } else {
                $courseformatoptionsedit['imagecontaineralignment'] = array('label' => get_config(
                            'format_trail', 'defaultimagecontaineralignment'), 'element_type' => 'hidden');
            }

            if (has_capability('format/trail:changeimagecontainersize', $context)) {
                $courseformatoptionsedit['imagecontainerwidth'] = array(
                    'label' => new lang_string('setimagecontainerwidth', 'format_trail'),
                    'help' => 'setimagecontainerwidth',
                    'help_component' => 'format_trail',
                    'element_type' => 'select',
                    'element_attributes' => array(self::$imagecontainerwidths)
                );
                $courseformatoptionsedit['imagecontainerratio'] = array(
                    'label' => new lang_string('setimagecontainerratio', 'format_trail'),
                    'help' => 'setimagecontainerratio',
                    'help_component' => 'format_trail',
                    'element_type' => 'select',
                    'element_attributes' => array(self::$imagecontainerratios)
                );
            } else {
                $courseformatoptionsedit['imagecontainerwidth'] = array('label' => get_config(
                            'format_trail', 'defaultimagecontainerwidth'), 'element_type' => 'hidden');
                $courseformatoptionsedit['imagecontainerratio'] = array('label' => get_config(
                            'format_trail', 'defaultimagecontainerratio'), 'element_type' => 'hidden');
            }

            if (has_capability('format/trail:changeimageresizemethod', $context)) {
                $courseformatoptionsedit['imageresizemethod'] = array(
                    'label' => new lang_string('setimageresizemethod', 'format_trail'),
                    'help' => 'setimageresizemethod',
                    'help_component' => 'format_trail',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('scale', 'format_trail'), // Scale.
                            2 => new lang_string('crop', 'format_trail')   // Crop.
                        )
                    )
                );
            } else {
                $courseformatoptionsedit['imageresizemethod'] = array('label' => get_config(
                            'format_trail', 'defaultimageresizemethod'), 'element_type' => 'hidden');
            }

            if (has_capability('format/trail:changeimagecontainerstyle', $context)) {
                $courseformatoptionsedit['bordercolour'] = array(
                    'label' => new lang_string('setbordercolour', 'format_trail'),
                    'help' => 'setbordercolour',
                    'help_component' => 'format_trail',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('value' => $defaults['defaultbordercolour'])
                    )
                );

                $courseformatoptionsedit['borderwidth'] = array(
                    'label' => new lang_string('setborderwidth', 'format_trail'),
                    'help' => 'setborderwidth',
                    'help_component' => 'format_trail',
                    'element_type' => 'select',
                    'element_attributes' => array(self::$borderwidths)
                );

                $courseformatoptionsedit['borderradius'] = array(
                    'label' => new lang_string('setborderradius', 'format_trail'),
                    'help' => 'setborderradius',
                    'help_component' => 'format_trail',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('off', 'format_trail'),
                            2 => new lang_string('on', 'format_trail'))
                    )
                );

                $courseformatoptionsedit['imagecontainerbackgroundcolour'] = array(
                    'label' => new lang_string('setimagecontainerbackgroundcolour', 'format_trail'),
                    'help' => 'setimagecontainerbackgroundcolour',
                    'help_component' => 'format_trail',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('value' => $defaults['defaultimagecontainerbackgroundcolour'])
                    )
                );

                $courseformatoptionsedit['currentselectedsectioncolour'] = array(
                    'label' => new lang_string('setcurrentselectedsectioncolour', 'format_trail'),
                    'help' => 'setcurrentselectedsectioncolour',
                    'help_component' => 'format_trail',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('value' => $defaults['defaultcurrentselectedsectioncolour'])
                    )
                );

                $courseformatoptionsedit['currentselectedimagecontainertextcolour'] = array(
                    'label' => new lang_string('setcurrentselectedimagecontainertextcolour', 'format_trail'),
                    'help' => 'setcurrentselectedimagecontainertextcolour',
                    'help_component' => 'format_trail',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('value' => $defaults['defaultcurrentselectedimagecontainertextcolour'])
                    )
                );

                $courseformatoptionsedit['currentselectedimagecontainercolour'] = array(
                    'label' => new lang_string('setcurrentselectedimagecontainercolour', 'format_trail'),
                    'help' => 'setcurrentselectedimagecontainercolour',
                    'help_component' => 'format_trail',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('value' => $defaults['defaultcurrentselectedimagecontainercolour'])
                    )
                );
            } else {
                $courseformatoptionsedit['bordercolour'] = array('label' => $defaults['defaultbordercolour'],
                    'element_type' => 'hidden');
                $courseformatoptionsedit['borderwidth'] = array('label' => get_config('format_trail', 'defaultborderwidth'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['borderradius'] = array('label' => get_config('format_trail', 'defaultborderradius'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['imagecontainerbackgroundcolour'] = array(
                    'label' => $defaults['defaultimagecontainerbackgroundcolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['currentselectedsectioncolour'] = array(
                    'label' => $defaults['defaultcurrentselectedsectioncolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['currentselectedimagecontainertextcolour'] = array(
                    'label' => $defaults['defaultcurrentselectedimagecontainertextcolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['currentselectedimagecontainercolour'] = array(
                    'label' => $defaults['defaultcurrentselectedimagecontainercolour'], 'element_type' => 'hidden');
            }

            if (has_capability('format/trail:changesectiontitleoptions', $context)) {
                $courseformatoptionsedit['hidesectiontitle'] = array(
                    'label' => new lang_string('hidesectiontitle', 'format_trail'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('no'), // No.
                            2 => new lang_string('yes') // Yes.
                        )
                    ),
                    'help' => 'hidesectiontitle',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['sectiontitletraillengthmaxoption'] = array(
                    'label' => new lang_string('sectiontitletraillengthmaxoption', 'format_trail'),
                    'element_type' => 'text',
                    'element_attributes' => array(array('size' => 3)),
                    'help' => 'sectiontitletraillengthmaxoption',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['sectiontitleboxposition'] = array(
                    'label' => new lang_string('sectiontitleboxposition', 'format_trail'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('sectiontitleboxpositioninside', 'format_trail'),
                            2 => new lang_string('sectiontitleboxpositionoutside', 'format_trail')
                        )
                    ),
                    'help' => 'sectiontitleboxposition',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['sectiontitleboxinsideposition'] = array(
                    'label' => new lang_string('sectiontitleboxinsideposition', 'format_trail'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('sectiontitleboxinsidepositiontop', 'format_trail'),
                            2 => new lang_string('sectiontitleboxinsidepositionmiddle', 'format_trail'),
                            3 => new lang_string('sectiontitleboxinsidepositionbottom', 'format_trail')
                        )
                    ),
                    'help' => 'sectiontitleboxinsideposition',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['sectiontitleboxheight'] = array(
                    'label' => new lang_string('sectiontitleboxheight', 'format_trail'),
                    'element_type' => 'text',
                    'element_attributes' => array(array('size' => 3)),
                    'help' => 'sectiontitleboxheight',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['sectiontitleboxopacity'] = array(
                    'label' => new lang_string('sectiontitleboxopacity', 'format_trail'),
                    'element_type' => 'select',
                    'element_attributes' => array(self::get_default_opacities()),
                    'help' => 'sectiontitleboxopacity',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['sectiontitlefontsize'] = array(
                    'label' => new lang_string('sectiontitlefontsize', 'format_trail'),
                    'element_type' => 'select',
                    'element_attributes' => array(self::get_default_section_font_sizes()),
                    'help' => 'sectiontitlefontsize',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['sectiontitlealignment'] = array(
                    'label' => new lang_string('sectiontitlealignment', 'format_trail'),
                    'help' => 'sectiontitlealignment',
                    'help_component' => 'format_trail',
                    'element_type' => 'select',
                    'element_attributes' => array(self::get_horizontal_alignments())
                );
                $courseformatoptionsedit['sectiontitleinsidetitletextcolour'] = array(
                    'label' => new lang_string('sectiontitleinsidetitletextcolour', 'format_trail'),
                    'help' => 'sectiontitleinsidetitletextcolour',
                    'help_component' => 'format_trail',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('value' => $defaults['defaultsectiontitleinsidetitletextcolour'])
                    )
                );
                $courseformatoptionsedit['sectiontitleinsidetitlebackgroundcolour'] = array(
                    'label' => new lang_string('sectiontitleinsidetitlebackgroundcolour', 'format_trail'),
                    'help' => 'sectiontitleinsidetitlebackgroundcolour',
                    'help_component' => 'format_trail',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('value' => $defaults['defaultsectiontitleinsidetitlebackgroundcolour'])
                    )
                );
                $courseformatoptionsedit['showsectiontitlesummary'] = array(
                    'label' => new lang_string('showsectiontitlesummary', 'format_trail'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('no'), // No.
                            2 => new lang_string('yes') // Yes.
                        )
                    ),
                    'help' => 'showsectiontitlesummary',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['setshowsectiontitlesummaryposition'] = array(
                    'label' => new lang_string('setshowsectiontitlesummaryposition', 'format_trail'),
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('top', 'format_trail'),
                            2 => new lang_string('bottom', 'format_trail'),
                            3 => new lang_string('left', 'format_trail'),
                            4 => new lang_string('right', 'format_trail')
                        )
                    ),
                    'help' => 'setshowsectiontitlesummaryposition',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['sectiontitlesummarymaxlength'] = array(
                    'label' => new lang_string('sectiontitlesummarymaxlength', 'format_trail'),
                    'element_type' => 'text',
                    'element_attributes' => array(array('size' => 3)),
                    'help' => 'sectiontitlesummarymaxlength',
                    'help_component' => 'format_trail'
                );
                $courseformatoptionsedit['sectiontitlesummarytextcolour'] = array(
                    'label' => new lang_string('sectiontitlesummarytextcolour', 'format_trail'),
                    'help' => 'sectiontitlesummarytextcolour',
                    'help_component' => 'format_trail',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('value' => $defaults['defaultsectiontitlesummarytextcolour'])
                    )
                );
                $courseformatoptionsedit['sectiontitlesummarybackgroundcolour'] = array(
                    'label' => new lang_string('sectiontitlesummarybackgroundcolour', 'format_trail'),
                    'help' => 'sectiontitlesummarybackgroundcolour',
                    'help_component' => 'format_trail',
                    'element_type' => 'gfcolourpopup',
                    'element_attributes' => array(
                        array('value' => $defaults['defaultsectiontitlesummarybackgroundcolour'])
                    )
                );
                $courseformatoptionsedit['sectiontitlesummarybackgroundopacity'] = array(
                    'label' => new lang_string('sectiontitlesummarybackgroundopacity', 'format_trail'),
                    'element_type' => 'select',
                    'element_attributes' => array(self::get_default_opacities()),
                    'help' => 'sectiontitlesummarybackgroundopacity',
                    'help_component' => 'format_trail'
                );
            } else {
                $courseformatoptionsedit['hidesectiontitle']
                        = array('label' => get_config('format_trail', 'defaulthidesectiontitle'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitletraillengthmaxoption']
                        = array('label' => get_config('format_trail', 'defaultsectiontitletraillengthmaxoption'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleboxposition'] = array('label' => get_config('format_trail',
                        'defaultsectiontitleboxposition'),
                    'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleboxinsideposition'] = array(
                    'label' => get_config('format_trail', 'defaultsectiontitleboxinsideposition'), 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleboxheight'] = array(
                    'label' => get_config('format_trail', 'defaultsectiontitleboxheight'), 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleboxopacity'] = array(
                    'label' => get_config('format_trail', 'defaultsectiontitleboxopacity'), 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitlefontsize'] = array(
                    'label' => get_config('format_trail', 'defaultsectiontitlefontsize'), 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitlealignment'] = array(
                    'label' => get_config('format_trail', 'defaultsectiontitlealignment'), 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleinsidetitletextcolour'] = array(
                    'label' => $defaults['defaultsectiontitleinsidetitletextcolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitleinsidetitlebackgroundcolour'] = array(
                    'label' => $defaults['defaultsectiontitleinsidetitlebackgroundcolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['showsectiontitlesummary'] = array(
                    'label' => get_config('format_trail', 'defaultshowsectiontitlesummary'), 'element_type' => 'hidden');
                $courseformatoptionsedit['setshowsectiontitlesummaryposition'] = array(
                    'label' => get_config('format_trail', 'defaultsetshowsectiontitlesummaryposition'), 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitlesummarymaxlength'] = array(
                    'label' => get_config('format_trail', 'defaultsectiontitlesummarymaxlength'), 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitlesummarytextcolour'] = array(
                    'label' => $defaults['defaultsectiontitlesummarytextcolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitlesummarybackgroundcolour'] = array(
                    'label' => $defaults['defaultsectiontitlesummarybackgroundcolour'], 'element_type' => 'hidden');
                $courseformatoptionsedit['sectiontitlesummarybackgroundopacity'] = array(
                    'label' => get_config('format_trail', 'defaultsectiontitlesummarybackgroundopacity'),
                    'element_type' => 'hidden');
            }

            $courseformatoptionsedit['newactivity'] = array(
                'label' => new lang_string('setnewactivity', 'format_trail'),
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('no'), // No.
                        2 => new lang_string('yes') // Yes.
                    )
                ),
                'help' => 'setnewactivity',
                'help_component' => 'format_trail'
            );

            $courseformatoptionsedit['fitsectioncontainertowindow'] = array(
                'label' => new lang_string('setfitsectioncontainertowindow', 'format_trail'),
                'help' => 'setfitsectioncontainertowindow',
                'help_component' => 'format_trail',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('no'), // No.
                        2 => new lang_string('yes') // Yes.
                    )
                )
            );

            $courseformatoptionsedit['greyouthidden'] = array(
                'label' => new lang_string('greyouthidden', 'format_trail'),
                'help' => 'greyouthidden',
                'help_component' => 'format_trail',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('no'), // No.
                        2 => new lang_string('yes') // Yes.
                    )
                )
            );

            // Alterado por JOTA.
            $courseformatoptionsedit['hidenavside'] = array(
                'label' => new lang_string('hidenavside', 'format_trail'),
                'help' => 'hidenavside',
                'help_component' => 'format_trail',
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('no'), // No.
                        2 => new lang_string('yes') // Yes.
                    )
                )
            );
            $courseformatoptionsedit['hidesectionlock'] = array(
                'label' => new lang_string('sethidesectionlock', 'format_trail'),
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('none', 'format_trail'),
                        2 => new lang_string('lock', 'format_trail'),
                        3 => new lang_string('mini_lock', 'format_trail'),
                        4 => new lang_string('mini_lock_tesouro', 'format_trail')
                    )
                ),
                'help' => 'sethidesectionlock',
                'help_component' => 'format_trail'
            );
            $courseformatoptionsedit['showbackground'] = array(
                'label' => new lang_string('setshowbackground', 'format_trail'),
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('tipo_pista', 'format_trail'),
                        5 => new lang_string('tipo_pista2', 'format_trail'),
                        2 => new lang_string('tipo_rio', 'format_trail'),
                        3 => new lang_string('tipo_quebra1', 'format_trail'),
                        4 => new lang_string('tipo_quebra2', 'format_trail'),
                        5 => new lang_string('tipo_tesouro', 'format_trail')
                    )
                ),
                'help' => 'setshowbackground',
                'help_component' => 'format_trail'
            );
            $courseformatoptionsedit['showcheckstar'] = array(
                'label' => new lang_string('setshowcheckstar', 'format_trail'),
                'element_type' => 'select',
                'element_attributes' => array(
                    array(
                        1 => new lang_string('none', 'format_trail'),
                        2 => new lang_string('check', 'format_trail'),
                        3 => new lang_string('star', 'format_trail'),
                        4 => new lang_string('like', 'format_trail')
                    )
                ),
                'help' => 'setshowcheckstar',
                'help_component' => 'format_trail'
            );

            if (has_capability('format/trail:changeimagecontainernavigation', $context)) {
                $courseformatoptionsedit['setsection0ownpagenotrailonesection'] = array(
                    'label' => new lang_string('setsection0ownpagenotrailonesection', 'format_trail'),
                    'help' => 'setsection0ownpagenotrailonesection',
                    'help_component' => 'format_trail',
                    'element_type' => 'select',
                    'element_attributes' => array(
                        array(
                            1 => new lang_string('no'), // No.
                            2 => new lang_string('yes') // Yes.
                        )
                    )
                );
            } else {
                $courseformatoptionsedit['setsection0ownpagenotrailonesection'] = array('label' => get_config(
                            'format_trail', 'defaultsection0ownpagenotrailonesection'), 'element_type' => 'hidden');
            }

            $courseformatoptions = array_merge_recursive($courseformatoptions, $courseformatoptionsedit);
        }
        return $courseformatoptions;
    }

    /**
     * Gets colours defaults.
     * @return string.
     */
    protected function get_course_format_colour_defaults() {
        $defaults = array();
        $defaults['defaultbordercolour'] = get_config('format_trail', 'defaultbordercolour');
        if ($defaults['defaultbordercolour'][0] == '#') {
            $defaults['defaultbordercolour'] = substr($defaults['defaultbordercolour'], 1);
        }
        $defaults['defaultimagecontainerbackgroundcolour']
                = get_config('format_trail', 'defaultimagecontainerbackgroundcolour');
        if ($defaults['defaultimagecontainerbackgroundcolour'][0] == '#') {
            $defaults['defaultimagecontainerbackgroundcolour']
                    = substr($defaults['defaultimagecontainerbackgroundcolour'], 1);
        }
        $defaults['defaultcurrentselectedsectioncolour']
                = get_config('format_trail', 'defaultcurrentselectedsectioncolour');
        if ($defaults['defaultcurrentselectedsectioncolour'][0] == '#') {
            $defaults['defaultcurrentselectedsectioncolour']
                    = substr($defaults['defaultcurrentselectedsectioncolour'], 1);
        }
        $defaults['defaultcurrentselectedimagecontainertextcolour']
                = get_config('format_trail', 'defaultcurrentselectedimagecontainertextcolour');
        if ($defaults['defaultcurrentselectedimagecontainertextcolour'][0] == '#') {
            $defaults['defaultcurrentselectedimagecontainertextcolour']
                    = substr($defaults['defaultcurrentselectedimagecontainertextcolour'], 1);
        }
        $defaults['defaultcurrentselectedimagecontainercolour']
                = get_config('format_trail', 'defaultcurrentselectedimagecontainercolour');
        if ($defaults['defaultcurrentselectedimagecontainercolour'][0] == '#') {
            $defaults['defaultcurrentselectedimagecontainercolour']
                    = substr($defaults['defaultcurrentselectedimagecontainercolour'], 1);
        }
        $defaults['defaultsectiontitleinsidetitletextcolour']
                = get_config('format_trail', 'defaultsectiontitleinsidetitletextcolour');
        if ($defaults['defaultsectiontitleinsidetitletextcolour'][0] == '#') {
            $defaults['defaultsectiontitleinsidetitletextcolour']
                    = substr($defaults['defaultsectiontitleinsidetitletextcolour'], 1);
        }
        $defaults['defaultsectiontitleinsidetitlebackgroundcolour']
                = get_config('format_trail', 'defaultsectiontitleinsidetitlebackgroundcolour');
        if ($defaults['defaultsectiontitleinsidetitlebackgroundcolour'][0] == '#') {
            $defaults['defaultsectiontitleinsidetitlebackgroundcolour']
                    = substr($defaults['defaultsectiontitleinsidetitlebackgroundcolour'], 1);
        }
        $defaults['defaultsectiontitlesummarytextcolour']
                = get_config('format_trail', 'defaultsectiontitlesummarytextcolour');
        if ($defaults['defaultsectiontitlesummarytextcolour'][0] == '#') {
            $defaults['defaultsectiontitlesummarytextcolour']
                    = substr($defaults['defaultsectiontitlesummarytextcolour'], 1);
        }
        $defaults['defaultsectiontitlesummarybackgroundcolour']
                = get_config('format_trail', 'defaultsectiontitlesummarybackgroundcolour');
        if ($defaults['defaultsectiontitlesummarybackgroundcolour'][0] == '#') {
            $defaults['defaultsectiontitlesummarybackgroundcolour']
                    = substr($defaults['defaultsectiontitlesummarybackgroundcolour'], 1);
        }
        return $defaults;
    }

    /**
     * Adds format options elements to the course/section edit form.
     *
     * @param MoodleQuickForm $mform form the elements are added to.
     * @param bool $forsection 'true' if this is a section edit form, 'false' if this is course edit form.
     * @return array array of references to the added form elements.
     */
    public function create_edit_form_elements(&$mform, $forsection = false) {
        global $CFG, $OUTPUT, $PAGE, $USER;
        MoodleQuickForm::registerElementType('gfcolourpopup', "$CFG->dirroot/course/format/trail/js/gf_colourpopup.php",
                'moodlequickform_gfcolourpopup');

        $elements = parent::create_edit_form_elements($mform, $forsection);

        /* Increase the number of sections combo box values if the user has increased the number of sections
          using the icon on the course page beyond course 'maxsections' or course 'maxsections' has been
          reduced below the number of sections already set for the course on the site administration course
          defaults page.  This is so that the number of sections is not reduced leaving unintended orphaned
          activities / resources. */
        if (!$forsection) {
            $maxsections = get_config('moodlecourse', 'maxsections');
            $numsections = $mform->getElementValue('numsections');
            $numsections = $numsections[0];
            if ($numsections > $maxsections) {
                $element = $mform->getElement('numsections');
                for ($i = $maxsections + 1; $i <= $numsections; $i++) {
                    $element->addOption("$i", $i);
                }
            }
        }
        $context = $this->get_context();

        $changeimagecontaineralignment = has_capability('format/trail:changeimagecontaineralignment', $context);
        $changeimagecontainernavigation = has_capability('format/trail:changeimagecontainernavigation', $context);
        $changeimagecontainersize = has_capability('format/trail:changeimagecontainersize', $context);
        $changeimageresizemethod = has_capability('format/trail:changeimageresizemethod', $context);
        $changeimagecontainerstyle = has_capability('format/trail:changeimagecontainerstyle', $context);
        $changesectiontitleoptions = has_capability('format/trail:changesectiontitleoptions', $context);
        $resetall = is_siteadmin($USER); // Site admins only.

        $elements[] = $mform->addElement('header', 'gfreset', get_string('gfreset', 'format_trail'));
        $mform->addHelpButton('gfreset', 'gfreset', 'format_trail', '', true);

        $bsfour = false;
        if (strcmp($PAGE->theme->name, 'boost') === 0) {
            $bsfour = true;
        } else if (!empty($PAGE->theme->parents)) {
            if (in_array('boost', $PAGE->theme->parents) === true) {
                $bsfour = true;
            }
        } else if (strcmp($PAGE->theme->name, 'foundation') === 0) {
            $bsfour = true;
        }

        $resetelements = array();

        if (($changeimagecontaineralignment) ||
                ($changeimagecontainernavigation) ||
                ($changeimagecontainersize) ||
                ($changeimageresizemethod) ||
                ($changeimagecontainerstyle) ||
                ($changesectiontitleoptions)) {

            if ($changeimagecontaineralignment) {
                if ($bsfour) {
                    $checkboxname = get_string('resetimagecontaineralignment', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontaineralignment', '', $checkboxname);
                    $resetelements[] = & $mform->createElement('html',
                            $OUTPUT->help_icon('resetimagecontaineralignment', 'format_trail'));
                } else {
                    $checkboxname = get_string('resetimagecontaineralignment', 'format_trail') .
                            $OUTPUT->help_icon('resetimagecontaineralignment', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontaineralignment', '', $checkboxname);
                }
            }

            if ($changeimagecontainernavigation) {
                if ($bsfour) {
                    $checkboxname = get_string('resetimagecontainernavigation', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontainernavigation', '', $checkboxname);
                    $resetelements[] = & $mform->createElement('html',
                            $OUTPUT->help_icon('resetimagecontainernavigation', 'format_trail'));
                } else {
                    $checkboxname = get_string('resetimagecontainernavigation', 'format_trail') .
                            $OUTPUT->help_icon('resetimagecontainernavigation', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontainernavigation', '', $checkboxname);
                }
            }

            if ($changeimagecontainersize) {
                if ($bsfour) {
                    $checkboxname = get_string('resetimagecontainersize', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontainersize', '', $checkboxname);
                    $resetelements[] = & $mform->createElement('html',
                            $OUTPUT->help_icon('resetimagecontainersize', 'format_trail'));
                } else {
                    $checkboxname = get_string('resetimagecontainersize', 'format_trail') .
                            $OUTPUT->help_icon('resetimagecontainersize', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontainersize', '', $checkboxname);
                }
            }

            if ($changeimageresizemethod) {
                if ($bsfour) {
                    $checkboxname = get_string('resetimageresizemethod', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimageresizemethod', '', $checkboxname);
                    $resetelements[] = & $mform->createElement('html',
                            $OUTPUT->help_icon('resetimageresizemethod', 'format_trail'));
                } else {
                    $checkboxname = get_string('resetimageresizemethod', 'format_trail') .
                            $OUTPUT->help_icon('resetimageresizemethod', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimageresizemethod', '', $checkboxname);
                }
            }

            if ($changeimagecontainerstyle) {
                if ($bsfour) {
                    $checkboxname = get_string('resetimagecontainerstyle', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontainerstyle', '', $checkboxname);
                    $resetelements[] = & $mform->createElement('html',
                            $OUTPUT->help_icon('resetimagecontainerstyle', 'format_trail'));
                } else {
                    $checkboxname = get_string('resetimagecontainerstyle', 'format_trail') .
                            $OUTPUT->help_icon('resetimagecontainerstyle', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetimagecontainerstyle', '', $checkboxname);
                }
            }

            if ($changesectiontitleoptions) {
                if ($bsfour) {
                    $checkboxname = get_string('resetsectiontitleoptions', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetsectiontitleoptions', '', $checkboxname);
                    $resetelements[] = & $mform->createElement('html',
                            $OUTPUT->help_icon('resetsectiontitleoptions', 'format_trail'));
                } else {
                    $checkboxname = get_string('resetsectiontitleoptions', 'format_trail') .
                            $OUTPUT->help_icon('resetsectiontitleoptions', 'format_trail');
                    $resetelements[] = & $mform->createElement('checkbox', 'resetsectiontitleoptions', '', $checkboxname);
                }
            }
        }

        if ($bsfour) {
            $checkboxname = get_string('resetnewactivity', 'format_trail');
            $resetelements[] = & $mform->createElement('checkbox', 'resetnewactivity', '', $checkboxname);
            $resetelements[] = & $mform->createElement('html', $OUTPUT->help_icon('resetnewactivity', 'format_trail'));

            $checkboxname = get_string('resetfitpopup', 'format_trail');
            $resetelements[] = & $mform->createElement('checkbox', 'resetfitpopup', '', $checkboxname);
            $resetelements[] = & $mform->createElement('html', $OUTPUT->help_icon('resetfitpopup', 'format_trail'));
        } else {
            $checkboxname = get_string('resetnewactivity', 'format_trail') .
                    $OUTPUT->help_icon('resetnewactivity', 'format_trail');
            $resetelements[] = & $mform->createElement('checkbox', 'resetnewactivity', '', $checkboxname);

            $checkboxname = get_string('resetfitpopup', 'format_trail') .
                    $OUTPUT->help_icon('resetfitpopup', 'format_trail');
            $resetelements[] = & $mform->createElement('checkbox', 'resepopup', '', $checkboxname);
        }
        $elements[] = $mform->addGroup($resetelements, 'resetgroup', get_string('resetgrp', 'format_trail'), null, false);

        if ($resetall) {
            $resetallelements = array();

            if ($bsfour) {
                $checkboxname = get_string('resetallimagecontaineralignment', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontaineralignment', '', $checkboxname);
                $resetallelements[] = & $mform->createElement('html',
                        $OUTPUT->help_icon('resetallimagecontaineralignment', 'format_trail'));

                $checkboxname = get_string('resetallimagecontainernavigation', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontainernavigation', '', $checkboxname);
                $resetallelements[] = & $mform->createElement('html',
                        $OUTPUT->help_icon('resetallimagecontainernavigation', 'format_trail'));

                $checkboxname = get_string('resetallimagecontainersize', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontainersize', '', $checkboxname);
                $resetallelements[] = & $mform->createElement('html',
                        $OUTPUT->help_icon('resetallimagecontainersize', 'format_trail'));

                $checkboxname = get_string('resetallimageresizemethod', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimageresizemethod', '', $checkboxname);
                $resetallelements[] = & $mform->createElement('html',
                        $OUTPUT->help_icon('resetallimageresizemethod', 'format_trail'));

                $checkboxname = get_string('resetallimagecontainerstyle', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontainerstyle', '', $checkboxname);
                $resetallelements[] = & $mform->createElement('html',
                        $OUTPUT->help_icon('resetallimagecontainerstyle', 'format_trail'));

                $checkboxname = get_string('resetallsectiontitleoptions', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallsectiontitleoptions', '', $checkboxname);
                $resetallelements[] = & $mform->createElement('html',
                        $OUTPUT->help_icon('resetallsectiontitleoptions', 'format_trail'));

                $checkboxname = get_string('resetallnewactivity', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallnewactivity', '', $checkboxname);
                $resetallelements[] = & $mform->createElement('html',
                        $OUTPUT->help_icon('resetallnewactivity', 'format_trail'));

                $checkboxname = get_string('resetallfitpopup', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallfitpopup', '', $checkboxname);
                $resetallelements[] = & $mform->createElement('html',
                        $OUTPUT->help_icon('resetallfitpopup', 'format_trail'));
            } else {
                $checkboxname = get_string('resetallimagecontaineralignment', 'format_trail') .
                        $OUTPUT->help_icon('resetallimagecontaineralignment', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontaineralignment', '', $checkboxname);

                $checkboxname = get_string('resetallimagecontainernavigation', 'format_trail') .
                        $OUTPUT->help_icon('resetallimagecontainernavigation', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontainernavigation', '', $checkboxname);

                $checkboxname = get_string('resetallimagecontainersize', 'format_trail') .
                        $OUTPUT->help_icon('resetallimagecontainersize', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontainersize', '', $checkboxname);

                $checkboxname = get_string('resetallimageresizemethod', 'format_trail') .
                        $OUTPUT->help_icon('resetallimageresizemethod', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimageresizemethod', '', $checkboxname);

                $checkboxname = get_string('resetallimagecontainerstyle', 'format_trail') .
                        $OUTPUT->help_icon('resetallimagecontainerstyle', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallimagecontainerstyle', '', $checkboxname);

                $checkboxname = get_string('resetallsectiontitleoptions', 'format_trail') .
                        $OUTPUT->help_icon('resetallsectiontitleoptions', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallsectiontitleoptions', '', $checkboxname);

                $checkboxname = get_string('resetallnewactivity', 'format_trail') .
                        $OUTPUT->help_icon('resetallnewactivity', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallnewactivity', '', $checkboxname);

                $checkboxname = get_string('resetallfitpopup', 'format_trail') .
                        $OUTPUT->help_icon('resetallfitpopup', 'format_trail');
                $resetallelements[] = & $mform->createElement('checkbox', 'resetallfitpopup', '', $checkboxname);
            }

            $elements[] = $mform->addGroup($resetallelements, 'resetallgroup',
                    get_string('resetallgrp', 'format_trail'), null, false);
        }

        return $elements;
    }

    /**
     * Override if you need to perform some extra validation of the format options
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @param array $errors errors already discovered in edit form validation
     * @return array of "element_name"=>"error_description" if there are errors,
     *         or an empty array if everything is OK.
     *         Do not repeat errors from $errors param here
     */
    public function edit_form_validation($data, $files, $errors) {
        $retr = array();

        if ($this->validate_colour($data['bordercolour']) === false) {
            $retr['bordercolour'] = get_string('colourrule', 'format_trail');
        }
        if ($this->validate_colour($data['imagecontainerbackgroundcolour']) === false) {
            $retr['imagecontainerbackgroundcolour'] = get_string('colourrule', 'format_trail');
        }
        if ($this->validate_colour($data['currentselectedsectioncolour']) === false) {
            $retr['currentselectedsectioncolour'] = get_string('colourrule', 'format_trail');
        }
        if ($this->validate_colour($data['currentselectedimagecontainercolour']) === false) {
            $retr['currentselectedimagecontainercolour'] = get_string('colourrule', 'format_trail');
        }
        if ($data['sectiontitletraillengthmaxoption'] < 0) {
            $retr['sectiontitletraillengthmaxoption'] = get_string('sectiontitletraillengthmaxoptionrule', 'format_trail');
        }
        if ($this->validate_colour($data['sectiontitleinsidetitletextcolour']) === false) {
            $retr['sectiontitleinsidetitletextcolour'] = get_string('colourrule', 'format_trail');
        }
        if ($this->validate_colour($data['sectiontitleinsidetitlebackgroundcolour']) === false) {
            $retr['sectiontitleinsidetitlebackgroundcolour'] = get_string('colourrule', 'format_trail');
        }
        if ($this->validate_opacity($data['sectiontitleboxopacity']) === false) {
            $retr['sectiontitleboxopacity'] = get_string('opacityrule', 'format_trail');
        }
        if ($this->validate_section_title_font_size($data['sectiontitlefontsize']) === false) {
            $retr['sectiontitlefontsize'] = get_string('sectiontitlefontsizerule', 'format_trail');
        }
        if ($this->validate_colour($data['sectiontitlesummarytextcolour']) === false) {
            $retr['sectiontitlesummarytextcolour'] = get_string('colourrule', 'format_trail');
        }
        if ($this->validate_colour($data['sectiontitlesummarybackgroundcolour']) === false) {
            $retr['sectiontitlesummarybackgroundcolour'] = get_string('colourrule', 'format_trail');
        }
        if ($this->validate_opacity($data['sectiontitlesummarybackgroundopacity']) === false) {
            $retr['sectiontitlesummarybackgroundopacity'] = get_string('opacityrule', 'format_trail');
        }
        return $retr;
    }

    /**
     * Validates the colour that was entered by the user.
     * Borrowed from 'admin_setting_configcolourpicker' in '/lib/adminlib.php'.
     *
     * I'm not completely happy with this solution as would rather embed in the colour
     * picker code in the form, however I find this area rather fraut and I hear that
     * Dan Poltawski (via MDL-42270) will be re-writing the forms lib so hopefully more
     * developer friendly.
     *
     * Note: Colour names removed, but might consider putting them back in if asked, but
     *       at the moment that would require quite a few changes and coping with existing
     *       settings.  Either convert the names to hex or allow them as valid values and
     *       fix the colour picker code and the CSS code in 'format.php' for the setting.
     *
     * Colour name to hex on: http://www.w3schools.com/cssref/css_colornames.asp.
     *
     * @param string $data the colour string to validate.
     * @return true|false
     */
    private function validate_colour($data) {
        if (preg_match('/^#?([[:xdigit:]]{3}){1,2}$/', $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validates the opacity that was entered by the user.
     *
     * @param string $data the opacity string to validate.
     * @return true|false
     */
    private function validate_opacity($data) {
        if (array_key_exists($data, self::$opacities)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validates the font size that was entered by the user.
     *
     * @param string $data the font size integer to validate.
     * @return true|false
     */
    private function validate_section_title_font_size($data) {
        if (array_key_exists($data, self::$sectiontitlefontsizes)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates format options for a course
     *
     * In case if course format was changed to 'Trail', we try to copy options
     * 'coursedisplay', 'numsections' and 'hiddensections' from the previous format.
     * The layout and colour defaults will come from 'course_format_options'.
     *
     * @param array $data
     * @param stdClass $oldcourse
     * @return boolean whether there were any changes to the options values.
     */
    public function update_course_format_options($data, $oldcourse = null) {
        global $DB; // MDL-37976.
        /*
         * Notes: Using 'unset' to really ensure that the reset form elements never get into the database.
         *        This has to be done here so that the reset occurs after we have done updates such that the
         *        reset itself is not seen as an update.
         */
        $resetimagecontaineralignment = false;
        $resetimagecontainernavigation = false;
        $resetimagecontainersize = false;
        $resetimageresizemethod = false;
        $resetimagecontainerstyle = false;
        $resetsectiontitleoptions = false;
        $resetnewactivity = false;
        $resetfitpopup = false;
        $resetallimagecontaineralignment = false;
        $resetallimagecontainernavigation = false;
        $resetallimagecontainersize = false;
        $resetallimageresizemethod = false;
        $resetallimagecontainerstyle = false;
        $resetallsectiontitleoptions = false;
        $resetallnewactivity = false;
        $resetallfitpopup = false;
        $resetgreyouthidden = false;
        $resethidenavside = false;
        if (isset($data->resetimagecontaineralignment) == true) {
            $resetimagecontaineralignment = true;
            unset($data->resetimagecontaineralignment);
        }
        if (isset($data->resetimagecontainernavigation) == true) {
            $resetimagecontainernavigation = true;
            unset($data->resetimagecontainernavigation);
        }
        if (isset($data->resetimagecontainersize) == true) {
            $resetimagecontainersize = true;
            unset($data->resetimagecontainersize);
        }
        if (isset($data->resetimageresizemethod) == true) {
            $resetimageresizemethod = true;
            unset($data->resetimageresizemethod);
        }
        if (isset($data->resetimagecontainerstyle) == true) {
            $resetimagecontainerstyle = true;
            unset($data->resetimagecontainerstyle);
        }
        if (isset($data->resetsectiontitleoptions) == true) {
            $resetsectiontitleoptions = true;
            unset($data->resetsectiontitleoptions);
        }
        if (isset($data->resetnewactivity) == true) {
            $resetnewactivity = true;
            unset($data->resetnewactivity);
        }
        if (isset($data->resetfitpopup) == true) {
            $resetfitpopup = true;
            unset($data->resetfitpopup);
        }
        if (isset($data->resetallimagecontaineralignment) == true) {
            $resetallimagecontaineralignment = true;
            unset($data->resetallimagecontaineralignment);
        }
        if (isset($data->resetallimagecontainernavigation) == true) {
            $resetallimagecontainernavigation = true;
            unset($data->resetallimagecontainernavigation);
        }
        if (isset($data->resetallimagecontainersize) == true) {
            $resetallimagecontainersize = true;
            unset($data->resetallimagecontainersize);
        }
        if (isset($data->resetallimageresizemethod) == true) {
            $resetallimageresizemethod = true;
            unset($data->resetallimageresizemethod);
        }
        if (isset($data->resetallimagecontainerstyle) == true) {
            $resetallimagecontainerstyle = true;
            unset($data->resetallimagecontainerstyle);
        }
        if (isset($data->resetallsectiontitleoptions) == true) {
            $resetallsectiontitleoptions = true;
            unset($data->resetallsectiontitleoptions);
        }
        if (isset($data->resetallnewactivity) == true) {
            $resetallnewactivity = true;
            unset($data->resetallnewactivity);
        }
        if (isset($data->resetallfitpopup) == true) {
            $resetfitpopup = true;
            unset($data->resetallfitpopup);
        }
        if (isset($data->resetgreyouthidden) == true) {
            $resetgreyouthidden = true;
            unset($data->resetgreyouthidden);
        }
        if (isset($data->resethidenavside) == true) {
            $resethidenavside = true;
            unset($data->resethidenavside);
        }

        $settings = $this->get_settings();
        $changedisplayedimages = false;
        if (isset($data->imagecontainerwidth)) {
            // We are have the CONTRIB-4099 options and this is not from a pre-CONTRIB-4099 backup file.
            if (((!(($resetimagecontainersize) || ($resetallimagecontainersize))) &&
                    (($settings['imagecontainerwidth'] != $data->imagecontainerwidth) ||
                    ($settings['imagecontainerratio'] != $data->imagecontainerratio))) ||
                    ((!(($resetimageresizemethod) || ($resetallimageresizemethod))) &&
                    ($settings['imageresizemethod'] != $data->imageresizemethod))) {
                /* Detect now and action later as 'setup_displayed_image' when called from 'update_displayed_images()' will need to
                  use the new values. */
                $changedisplayedimages = true;
            }
        }

        $data = (array) $data;
        if ($oldcourse !== null) {
            $oldcourse = (array) $oldcourse;
            $options = $this->course_format_options();

            foreach ($options as $key => $unused) {
                if (!array_key_exists($key, $data)) {
                    if (array_key_exists($key, $oldcourse)) {
                        $data[$key] = $oldcourse[$key];
                    } else if ($key === 'numsections') {
                        // If previous format does not have the field 'numsections'
                        // and $data['numsections'] is not set,
                        // we fill it with the maximum section number from the DB.
                        $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                            WHERE course = ?', array($this->courseid));
                        if ($maxsection) {
                            // If there are no sections, or just default 0-section, 'numsections' will be set to default.
                            $data['numsections'] = $maxsection;
                        }
                    }
                }
            }
        }
        $changes = $this->update_format_options($data);

        if ($changes && array_key_exists('numsections', $data)) {
            // If the numsections was decreased, try to completely delete the orphaned sections (unless they are not empty).
            $numsections = (int) $data['numsections'];
            $maxsection = $DB->get_field_sql('SELECT max(section) from {course_sections}
                        WHERE course = ?', array($this->courseid));
            for ($sectionnum = $maxsection; $sectionnum > $numsections; $sectionnum--) {
                if (!$this->delete_section($sectionnum, false)) {
                    break;
                }
            }
        }

        // Now we can change the displayed images if needed.
        if ($changedisplayedimages) {
            $this->settings = null; // Invalidate as changed.
            $settings = $this->get_settings();

            $this->update_displayed_images($this->courseid, $this, $settings, true);
        }

        // Now we can do the reset.
        if (($resetallimagecontaineralignment) ||
                ($resetallimagecontainernavigation) ||
                ($resetallimagecontainersize) ||
                ($resetallimageresizemethod) ||
                ($resetallimagecontainerstyle) ||
                ($resetallsectiontitleoptions) ||
                ($resetallnewactivity) ||
                ($resetallfitpopup)) {
            $this->reset_trail_setting(0, $resetallimagecontaineralignment, $resetallimagecontainernavigation,
                    $resetallimagecontainersize, $resetallimageresizemethod, $resetallimagecontainerstyle,
                    $resetallsectiontitleoptions, $resetallnewactivity, $resetallfitpopup);
            $changes = true;
        } else if (
                ($resetimagecontaineralignment) ||
                ($resetimagecontainersize) ||
                ($resetimageresizemethod) ||
                ($resetimagecontainerstyle) ||
                ($resetsectiontitleoptions) ||
                ($resetnewactivity) ||
                ($resetfitpopup)) {
            $this->reset_trail_setting($this->courseid, $resetimagecontaineralignment,
                    $resetimagecontainernavigation, $resetimagecontainersize, $resetimageresizemethod,
                    $resetimagecontainerstyle, $resetsectiontitleoptions, $resetnewactivity, $resetfitpopup);
            $changes = true;
        }

        return $changes;
    }

    /**
     * Deletes a section
     *
     * Do not call this function directly, instead call
     *
     * @param int|stdClass|section_info $section
     * @param bool $forcedeleteifnotempty if set to false section will not be deleted if it has modules in it.
     * @return bool whether section was deleted
     */
    public function delete_section($section, $forcedeleteifnotempty = false) {
        if (!$this->uses_sections()) {
            // Not possible to delete section if sections are not used.
            return false;
        }
        if (!is_object($section)) {
            global $DB;
            $section = $DB->get_record('course_sections', array('course' => $this->get_courseid(),
                'section' => $section), 'id,section,sequence,summary');
        }
        if (!$section || !$section->section) {
            // Not possible to delete 0-section.
            return false;
        }

        if (!$forcedeleteifnotempty && (!empty($section->sequence) || !empty($section->summary))) {
            return false;
        }
        if (parent::delete_section($section, $forcedeleteifnotempty)) {
            $this->delete_image($section->id, $this->get_context()->id);
            return true;
        }
        return false;
    }

    /**
     * Whether this format allows to delete sections
     *
     * Do not call this function directly
     *
     * @param int|stdClass|section_info $section
     * @return boolean
     */
    public function can_delete_section($section) {
        return true;
    }

    /**
     * Updates format options for a section
     *
     * Section id is expected in $data->id (or $data['id'])
     * If $data does not contain property with the option name, the option will not be updated
     *
     * @param array $data
     * @return boolean whether there were any changes to the options values
     */
    public function update_section_format_options($data) {
        $data = (array) $data;

        // Resets the displayed image because changing the section name / details deletes the file.
        // See CONTRIB-4784.
        global $DB;
        $DB->set_field('format_trail_icon', 'displayedimageindex', 0, array('sectionid' => $data['id']));

        return parent::update_section_format_options($data);
    }

    /**
     * Resets the format setting to the default.
     * @param int $courseid If not 0, then a specific course to reset.
     * @param int $imagecontaineralignmentreset If true, reset the alignment to the default in the settings for the format.
     * @param int $imagecontainernavigationreset If true, reset the alignment to the default in the settings for the format.
     * @param int $imagecontainersizereset If true, reset the layout to the default in the settings for the format.
     * @param int $imageresizemethodreset If true, reset the image resize method to the default in the settings for the format.
     * @param int $imagecontainerstylereset If true, reset the colour to the default in the settings for the format.
     * @param int $sectiontitleoptionsreset If true, reset the section title options to the default in the settings for the format.
     * @param int $newactivityreset If true, reset the new activity to the default in the settings for the format.
     * @param int $fitpopupreset If true, reset the fit popup to the default in the settings for the format.
     */
    public function reset_trail_setting($courseid, $imagecontaineralignmentreset,
            $imagecontainernavigationreset, $imagecontainersizereset, $imageresizemethodreset,
            $imagecontainerstylereset, $sectiontitleoptionsreset, $newactivityreset, $fitpopupreset) {
        global $DB, $USER;

        $context = $this->get_context();

        if ($courseid == 0) {
            $records = $DB->get_records('course', array('format' => $this->format), '', 'id');
        } else {
            $records = $DB->get_records('course', array('id' => $courseid, 'format' => $this->format), '', 'id');
        }

        $resetallifall = ((is_siteadmin($USER)) || ($courseid != 0)); // Will be true if reset all capability or a single course.

        $updatedata = array();
        $updateimagecontaineralignment = false;
        $updateimagecontainernavigation = false;
        $updateimagecontainersize = false;
        $updateimageresizemethod = false;
        $updateimagecontainerstyle = false;
        $updatesectiontitleoptions = false;
        $updatenewactivity = false;
        $updatefitpopup = false;
        if ($imagecontaineralignmentreset && has_capability('format/trail:changeimagecontaineralignment', $context)
                && $resetallifall) {
            $updatedata['imagecontaineralignment'] = get_config('format_trail', 'defaultimagecontaineralignment');
            $updateimagecontaineralignment = true;
        }
        if ($imagecontainernavigationreset && has_capability('format/trail:changeimagecontaineralignment', $context)
                && $resetallifall) {
            $updatedata['setsection0ownpagenotrailonesection'] = get_config('format_trail',
                    'defaultsection0ownpagenotrailonesection');
            $updateimagecontainernavigation = true;
        }
        if ($imagecontainersizereset && has_capability('format/trail:changeimagecontainersize', $context)
                && $resetallifall) {
            $updatedata['imagecontainerwidth'] = get_config('format_trail', 'defaultimagecontainerwidth');
            $updatedata['imagecontainerratio'] = get_config('format_trail', 'defaultimagecontainerratio');
            $updateimagecontainersize = true;
        }
        if ($imageresizemethodreset && has_capability('format/trail:changeimageresizemethod', $context)
                && $resetallifall) {
            $updatedata['imageresizemethod'] = get_config('format_trail', 'defaultimageresizemethod');
            $updateimageresizemethod = true;
        }
        if ($imagecontainerstylereset && has_capability('format/trail:changeimagecontainerstyle', $context)
                && $resetallifall) {
            $updatedata['bordercolour'] = get_config('format_trail', 'defaultbordercolour');
            $updatedata['borderwidth'] = get_config('format_trail', 'defaultborderwidth');
            $updatedata['borderradius'] = get_config('format_trail', 'defaultborderradius');
            $updatedata['imagecontainerbackgroundcolour'] = get_config('format_trail',
                    'defaultimagecontainerbackgroundcolour');
            $updatedata['currentselectedsectioncolour'] = get_config('format_trail',
                    'defaultcurrentselectedsectioncolour');
            $updatedata['currentselectedimagecontainertextcolour'] = get_config('format_trail',
                    'defaultcurrentselectedimagecontainertextcolour');
            $updatedata['currentselectedimagecontainercolour'] = get_config('format_trail',
                    'defaultcurrentselectedimagecontainercolour');
            $updateimagecontainerstyle = true;
        }
        if ($sectiontitleoptionsreset && has_capability('format/trail:changesectiontitleoptions', $context)
                && $resetallifall) {
            $updatedata['hidesectiontitle'] = get_config('format_trail', 'defaulthidesectiontitle');
            $updatedata['sectiontitletraillengthmaxoption'] = get_config('format_trail',
                    'defaultsectiontitletraillengthmaxoption');
            $updatedata['sectiontitleboxposition'] = get_config('format_trail', 'defaultsectiontitleboxposition');
            $updatedata['sectiontitleboxinsideposition'] = get_config('format_trail',
                    'defaultsectiontitleboxinsideposition');
            $updatedata['sectiontitleboxheight'] = get_config('format_trail', 'defaultsectiontitleboxheight');
            $updatedata['sectiontitleboxopacity'] = get_config('format_trail', 'defaultsectiontitleboxopacity');
            $updatedata['sectiontitlefontsize'] = get_config('format_trail', 'defaultsectiontitlefontsize');
            $updatedata['sectiontitlealignment'] = get_config('format_trail', 'defaultsectiontitlealignment');
            $updatedata['sectiontitleinsidetitletextcolour'] = get_config('format_trail',
                    'defaultsectiontitleinsidetitletextcolour');
            $updatedata['sectiontitleinsidetitlebackgroundcolour'] = get_config('format_trail',
                    'defaultsectiontitleinsidetitlebackgroundcolour');
            $updatedata['showsectiontitlesummary'] = get_config('format_trail', 'defaultshowsectiontitlesummary');
            $updatedata['setshowsectiontitlesummaryposition'] = get_config('format_trail',
                    'defaultsetshowsectiontitlesummaryposition');
            $updatedata['sectiontitlesummarymaxlength'] = get_config('format_trail',
                    'defaultsectiontitlesummarymaxlength');
            $updatedata['sectiontitlesummarytextcolour'] = get_config('format_trail',
                    'defaultsectiontitlesummarytextcolour');
            $updatedata['sectiontitlesummarybackgroundcolour'] = get_config('format_trail',
                    'defaultsectiontitlesummarybackgroundcolour');
            $updatedata['sectiontitlesummarybackgroundopacity'] = get_config('format_trail',
                    'defaultsectiontitlesummarybackgroundopacity');
            $updatesectiontitleoptions = true;
        }
        if ($newactivityreset && $resetallifall) {
            $updatedata['newactivity'] = get_config('format_trail', 'defaultnewactivity');
            $updatenewactivity = true;
        }
        if ($fitpopupreset && $resetallifall) {
            $updatedata['fitsectioncontainertowindow'] = get_config('format_trail', 'defaultfitsectioncontainertowindow');
            $updatefitpopup = true;
        }

        foreach ($records as $record) {
            if (($updateimagecontaineralignment) ||
                    ($updateimagecontainernavigation) ||
                    ($updateimagecontainersize) ||
                    ($updateimageresizemethod) ||
                    ($updateimagecontainerstyle) ||
                    ($updatesectiontitleoptions) ||
                    ($updatenewactivity) ||
                    ($updatefitpopup)) {
                $ourcourseid = $this->courseid;
                $this->courseid = $record->id;
                if (($updateimagecontainersize) || ($updateimageresizemethod)) {
                    $courseformat = null;
                    if ($ourcourseid !== $this->courseid) {
                        $courseformat = course_get_format($this->courseid);
                        $currentsettings = $courseformat->get_settings();
                    } else {
                        $currentsettings = $this->get_settings();
                        $courseformat = $this;
                    }

                    if (($updateimagecontainersize) &&
                            (($currentsettings['imagecontainerwidth'] != $updatedata['imagecontainerwidth']) ||
                            ($currentsettings['imagecontainerratio'] != $updatedata['imagecontainerratio']))) {
                        $performimagecontainersize = true; // Variable $updatedata will be correct.
                    } else {
                        // If image resize method needs to operate so use current settings.
                        $updatedata['imagecontainerwidth'] = $currentsettings['imagecontainerwidth'];
                        $updatedata['imagecontainerratio'] = $currentsettings['imagecontainerratio'];
                        $performimagecontainersize = false;
                    }

                    if (($updateimageresizemethod) &&
                            ($currentsettings['imageresizemethod'] != $updatedata['imageresizemethod'])) {
                        $performimageresizemethod = true; // Variable $updatedata will be correct.
                    } else {
                        // If image container size needs to operate so use current setting.
                        $updatedata['imageresizemethod'] = $currentsettings['imageresizemethod'];
                        $performimageresizemethod = false;
                    }

                    if (($performimagecontainersize) || ($performimageresizemethod)) {
                        // No need to get the settings as parsing the updated ones, but do need to invalidate them.
                        $courseformat->settings = null;
                        $courseformat->update_displayed_images($record->id, $courseformat, $updatedata, false);
                    }
                }
                $this->update_format_options($updatedata);
                $this->courseid = $ourcourseid;
            }
        }
    }

    // Trail specific methods...
    /**
     * Returns a moodle_url to a trail format file.
     * @param string $url The URL to append.
     * @param array $params URL parameters.
     * @return moodle_url The created URL.
     */
    public function trail_moodle_url($url, array $params = null) {
        return new moodle_url('/course/format/trail/' . $url, $params);
    }

    /**
     * Gets the trail image entries for the given course.
     *
     * @param int $courseid
     * @return booelan
     */
    public function get_images($courseid) {
        global $DB;

        if (!$courseid) {
            return false;
        }

        if (!$sectionimagecontainers = $DB->get_records('format_trail_icon',
                array('courseid' => $courseid), '', 'sectionid, image, displayedimageindex')) {
            $sectionimagecontainers = false;
        }
        return $sectionimagecontainers;
    }

    /**
     * Gets the trail image entry for the given course and section.  If an entry cannot be found then one is created
     * and returned.  If the course id is set to the default then it is updated to the one supplied as the value
     * will be accurate.
     * @param int $courseid The course id to use.
     * @param int $sectionid The section id to use.
     * @return booelan
     */
    public function get_image($courseid, $sectionid) {
        global $DB;

        if ((!$courseid) || (!$sectionid)) {
            return false;
        }

        if (!$sectionimage = $DB->get_record('format_trail_icon', array('sectionid' => $sectionid))) {

            $newimagecontainer = new stdClass();
            $newimagecontainer->sectionid = $sectionid;
            $newimagecontainer->courseid = $courseid;
            $newimagecontainer->displayedimageindex = 0;

            if (!$newimagecontainer->id = $DB->insert_record('format_trail_icon', $newimagecontainer, true)) {
                throw new moodle_exception('invalidrecordid', 'format_trail', '', 'Could not create image container.'
                . '  Trail format database is not ready.' .
                '  An admin must visit the notifications section.');
            }
            $sectionimage = $newimagecontainer;
        } else if ($sectionimage->courseid == 1) { // 1 is the default and is the 'site' course so cannot be the Trail format.
            // Note: Using a double equals in the test and not a triple as the latter does not work for some reason.
            /* Course id is the default and needs to be set correctly.  This can happen with data created by versions prior to
              13/7/2012. */
            $DB->set_field('format_trail_icon', 'courseid', $courseid, array('sectionid' => $sectionid));
            $sectionimage->courseid = $courseid;
        }
        return $sectionimage;
    }

    /**
     * Get summary visibility, if it doesn't exist create it.
     * The summary visibility is if section 0 is displayed (1) or in the trail(0).
     * @param int $courseid The course id.
     * @return stdClass The summary visibility for the course or false if not found.
     * @throws moodle_exception If cannot add a new record to the 'format_trail_summary' table.
     */
    public function get_summary_visibility($courseid) {
        global $DB;
        if (!$summarystatus = $DB->get_record('format_trail_summary', array('courseid' => $courseid))) {
            $newstatus = new stdClass();
            $newstatus->courseid = $courseid;
            $newstatus->showsummary = 1;

            if (!$newstatus->id = $DB->insert_record('format_trail_summary', $newstatus)) {
                throw new moodle_exception('invalidrecordid', 'format_trail', '',
                        'Could not set summary status. Trail format database is not ready.'
                . ' An admin must visit the notifications section.');
            }
            $summarystatus = $newstatus;

            /* Technically this only happens once when the course is created, so we can use it to set the
             * course format options for the first time.  This so that the defaults are set upon creation
             * and therefore do not have to detect when they change in the global site settings.  Which
             * cannot be detected and therefore the icons would look odd.  So here they are set and set once
             * until course settings are reset or changed.
             */
            $this->update_course_format_options($this->get_settings());
        }
        return $summarystatus;
    }

    // CONTRIB-4099 methods:....
    /**
     * Calculates the image container properties so that it can be rendered correctly.
     * @param int $imagecontainerwidth The width of the container.
     * @param int $imagecontainerratio The ratio array index of the container, see: $imagecontainerratios.
     * @param int $borderwidth The width of the border.
     * @return array with the key => value of 'height' for the container and 'margin-top' and 'margin-left' for the new activity
     *               image.
     */
    public function calculate_image_container_properties($imagecontainerwidth, $imagecontainerratio, $borderwidth) {

        if (($imagecontainerwidth !== self::$currentwidth) || ($imagecontainerratio !== self::$currentratio) ||
                ($borderwidth !== self::$currentborderwidth)) {
            $height = $this->calculate_height($imagecontainerwidth, $imagecontainerratio);
            // This is: margin-top = image holder height - ( image height - border width)).
            // This is: margin-left = (image holder width - image width) + border width.

            $result = array(
                'height' => $height,
                'margin-top' => $height - (42 - $borderwidth),
                'margin-left' => ($imagecontainerwidth - 95) + $borderwidth
            );

            // Store:...
            self::$currentwidth = $imagecontainerwidth;
            self::$currentratio = $imagecontainerratio;
            self::$currentborderwidth = $borderwidth;
            self::$currentheight = $result['height'];
            self::$activitymargintop = $result['margin-top'];
            self::$activitymarginleft = $result['margin-left'];
        } else {
            $result = array(
                'height' => self::$currentheight,
                'margin-top' => self::$activitymargintop,
                'margin-left' => self::$activitymarginleft
            );
        }

        return $result;
    }

    /**
     * Gets the image container properties given the settings.
     * @param array $settings Must have integer keys for 'imagecontainerwidth' and 'imagecontainerratio'.
     * @return array with the key => value of 'height' and 'width' for the container.
     */
    private function get_displayed_image_container_properties($settings) {
        return array('height' => $this->calculate_height($settings['imagecontainerwidth'],
                $settings['imagecontainerratio']),
            'width' => $settings['imagecontainerwidth']);
    }

    /**
     * Calculates height given the width and ratio.
     *
     * @param int $width
     * @param int $ratio
     * @return int
     */
    private function calculate_height($width, $ratio) {
        $basewidth = $width;

        switch ($ratio) {
            case 1: // 3-2.
            case 2: // 3-1.
            case 3: // 3-3.
            case 7: // 3-4.
                $basewidth = $width / 3;
                break;
            case 4: // 2-3.
                $basewidth = $width / 1;
                break;
            case 5: // 1-3.
                $basewidth = $width;
                break;
            case 6: // 4-3.
                $basewidth = $width / 4;
                break;
        }

        $height = $basewidth;
        switch ($ratio) {
            case 2: // 3-1.
                $height = $basewidth;
                break;
            case 1: // 3-2.
                $height = $basewidth * 2;
                break;
            case 3: // 3-3.
            case 4: // 2-3.
            case 5: // 1-3.
            case 6: // 4-3.
                $height = $basewidth * 3;
                break;
            case 7: // 3-4.
                $height = $basewidth * 4;
                break;
        }

        return round($height);
    }

    /**
     * Create original image.
     *
     * @param int $contextid
     * @param int $sectionid
     * @param string $filename
     * @return array
     */
    public function create_original_image_record($contextid, $sectionid, $filename) {
        $created = time();
        $storedfilerecord = array(
            'contextid' => $contextid,
            'component' => 'course',
            'filearea' => 'section',
            'itemid' => $sectionid,
            'filepath' => '/',
            // CONTRIB-5001 - Avoid clashes with the same image in the section summary by using a different name.
            'filename' => 'goi_' . $filename, // Goi = tla = trail original image.
            'timecreated' => $created,
            'timemodified' => $created);

        return $storedfilerecord;
    }

    /**
     * Create section image.
     *
     * @param string $tempfile
     * @param string $storedfilerecord
     * @param string $sectionimage
     * @return none.
     */
    public function create_section_image($tempfile, $storedfilerecord, $sectionimage) {
        global $DB, $CFG;
        require_once($CFG->libdir . '/gdlib.php');

        $fs = get_file_storage();

        try {
            $convertsuccess = true;

            // Ensure the right quality setting...
            $mime = $tempfile->get_mimetype();

            $imageinfo = $tempfile->get_imageinfo();
            $imagemaxwidth = self::get_maximum_image_width();
            if ($imageinfo['width'] > $imagemaxwidth) { // Maximum width as defined in lib.php.
                // Recalculate height:...
                $ratio = $imagemaxwidth / $imageinfo['width'];
                $imageinfo['height'] = $imageinfo['height'] * $ratio; // Maintain image's aspect ratio.
                // Set width:...
                $imageinfo['width'] = $imagemaxwidth;
            }

            $storedfilerecord['mimetype'] = $mime;

            // Note: It appears that this works with transparent Gif's to, so simplifying.
            $tmproot = make_temp_directory('trailformaticon');
            $tmpfilepath = $tmproot . '/' . $tempfile->get_contenthash();
            $tempfile->copy_content_to($tmpfilepath);

            $data = generate_image_thumbnail($tmpfilepath, $imageinfo['width'], $imageinfo['height']);
            if (!empty($data)) {
                $fs->create_file_from_string($storedfilerecord, $data);
            } else {
                $convertsuccess = false;
            }
            unlink($tmpfilepath);

            $tempfile->delete();
            unset($tempfile);

            if ($convertsuccess == true) {
                $DB->set_field('format_trail_icon', 'image', $storedfilerecord['filename'],
                        array('sectionid' => $storedfilerecord['itemid']));

                // Set up the displayed image:...
                $sectionimage->newimage = $storedfilerecord['filename'];
                $icbc = self::hex2rgb($this->get_settings()['imagecontainerbackgroundcolour']);
                $this->setup_displayed_image($sectionimage, $storedfilerecord['contextid'],
                        $this->get_settings(), $icbc, $mime);
            } else {
                throw new moodle_exception('imagecannotbeused', 'format_trail', $CFG->wwwroot . "/course/view.php?id="
                        . $this->courseid);
            }
        } catch (Exception $e) {
            if (isset($tempfile)) {
                $tempfile->delete();
                unset($tempfile);
            }
            print('Trail Format Image Exception:...');
            debugging($e->getMessage());
        }
    }

    /**
     * Set up the displayed image.
     * @param array $sectionimage Section information from its row in the 'format_trail_icon' table.
     * @param array $contextid The context id to which the image relates.
     * @param array $settings The course settings to apply.
     * @param array $icbc The 'imagecontainerbackgroundcolour' as an RGB array.
     * @param string $mime The mime type if already known.
     * @return array The updated $sectionimage data.
     */
    public function setup_displayed_image($sectionimage, $contextid, $settings, $icbc, $mime = null) {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/repository/lib.php');
        require_once($CFG->libdir . '/gdlib.php');

        // Set up the displayed image:...
        $fs = get_file_storage();
        if ($imagecontainerpathfile = $fs->get_file($contextid, 'course', 'section',
                $sectionimage->sectionid, '/', $sectionimage->newimage)) {
            $trailimagepath = $this->get_image_path();
            $convertsuccess = true;
            if (!$mime) {
                $mime = $imagecontainerpathfile->get_mimetype();
            }

            $displayedimageinfo = $this->get_displayed_image_container_properties($settings);

            $tmproot = make_temp_directory('trailformatdisplayedimagecontainer');
            $tmpfilepath = $tmproot . '/' . $imagecontainerpathfile->get_contenthash();
            $imagecontainerpathfile->copy_content_to($tmpfilepath);

            if ($settings['imageresizemethod'] == 1) {
                $crop = false;
            } else {
                $crop = true;
            }
            $data = self::generate_image($tmpfilepath, $displayedimageinfo['width'],
                    $displayedimageinfo['height'], $crop, $icbc, $mime);
            if (!empty($data)) {
                // Updated image.
                $sectionimage->displayedimageindex++;
                $created = time();
                $displayedimagefilerecord = array(
                    'contextid' => $contextid,
                    'component' => 'course',
                    'filearea' => 'section',
                    'itemid' => $sectionimage->sectionid,
                    'filepath' => $trailimagepath,
                    'filename' => $sectionimage->displayedimageindex . '_' . $sectionimage->newimage,
                    'timecreated' => $created,
                    'timemodified' => $created,
                    'mimetype' => $mime);

                if ($fs->file_exists($displayedimagefilerecord['contextid'],
                        $displayedimagefilerecord['component'], $displayedimagefilerecord['filearea'],
                        $displayedimagefilerecord['itemid'], $displayedimagefilerecord['filepath'],
                        $displayedimagefilerecord['filename'])) {
                    /* This can happen with previous CONTRIB-4099 versions where it was possible for the backup file to
                      have the 'trailimage' files too.  Therefore without this, then 'create_file_from_string' below will
                      baulk as the file already exists.   Unfortunately has to be here as the restore mechanism restores
                      the trail format data for the database and then the files.  And the Trail code is called at the 'data'
                      stage. */
                    if ($oldfile = $fs->get_file($displayedimagefilerecord['contextid'],
                            $displayedimagefilerecord['component'], $displayedimagefilerecord['filearea'],
                            $displayedimagefilerecord['itemid'], $displayedimagefilerecord['filepath'],
                            $displayedimagefilerecord['filename'])) {
                        // Delete old file.
                        $oldfile->delete();
                    }
                }
                $fs->create_file_from_string($displayedimagefilerecord, $data);
            } else {
                $convertsuccess = false;
            }
            unlink($tmpfilepath);

            if ($convertsuccess == true) {
                // Now safe to delete old file if it exists.
                if ($oldfile = $fs->get_file($contextid, 'course', 'section', $sectionimage->sectionid,
                        $trailimagepath, ($sectionimage->displayedimageindex - 1) . '_' . $sectionimage->image)) {
                    $oldfile->delete();
                }
                $DB->set_field('format_trail_icon', 'displayedimageindex',
                        $sectionimage->displayedimageindex, array('sectionid' => $sectionimage->sectionid));
            } else {
                throw new moodle_exception('cannotconvertuploadedimagetodisplayedimage',
                        'format_trail', $CFG->wwwroot . "/course/view.php?id=" . $this->courseid);
            }
        } else {
            $DB->set_field('format_trail_icon', 'image', null, array('sectionid' => $sectionimage->sectionid));
        }

        return $sectionimage;  // So that the caller can know the new value of displayedimageindex.
    }

    // Alterado por Jota.
    /**
     * Output section image.
     * @param array $section
     * @param string $sectionname
     * @param string $sectionimage
     * @param int $contextid
     * @param \sdtClass $thissection
     * @param string $trailimagepath
     * @param string $output
     * @param int $bloqueado
     * @return array The updated $sectionimage data.
     */
    public function output_section_image($section, $sectionname, $sectionimage,
            $contextid, $thissection, $trailimagepath, $output, $bloqueado = 0) {
        global $CFG;
        $content = '';
        if (is_object($sectionimage) && ($sectionimage->displayedimageindex > 0)) {
            $imgurl = moodle_url::make_pluginfile_url(
                            $contextid, 'course', 'section', $thissection->id,
                    $trailimagepath, $sectionimage->displayedimageindex . '_' . $sectionimage->image);
            // Alterado por Jota.
            if ($bloqueado > 0) {
                $imgurl = $CFG->wwwroot . '/course/format/trail/pix/lock.png';
            }
            $content = html_writer::empty_tag('img', array(
                        'src' => $imgurl,
                        'alt' => $sectionname,
                        'role' => 'img',
                        'aria-label' => $sectionname));
        } else if ($section == 0) {
            $imgurl = $output->image_url('info', 'format_trail');
            // Alterado por Jota.
            if ($bloqueado > 0) {
                $imgurl = $CFG->wwwroot . '/course/format/trail/pix/lock.png';
            }
            $content = html_writer::empty_tag('img', array(
                        'src' => $imgurl,
                        'alt' => $sectionname,
                        'class' => 'info',
                        'role' => 'img',
                        'aria-label' => $sectionname));
        }
        return $content;
    }

    /**
     * Delete image.
     *
     * @param int $sectionid
     * @param int $contextid
     * @return none.
     */
    public function delete_image($sectionid, $contextid) {
        $sectionimage = $this->get_image($this->courseid, $sectionid);
        if ($sectionimage) {
            global $DB;
            if (!empty($sectionimage->image)) {
                $fs = get_file_storage();

                // Delete the image.
                if ($file = $fs->get_file($contextid, 'course', 'section', $sectionid, '/', $sectionimage->image)) {
                    $file->delete();
                    $DB->set_field('format_trail_icon', 'image', null, array('sectionid' => $sectionimage->sectionid));
                    // Delete the displayed image.
                    $trailimagepath = $this->get_image_path();
                    if ($file = $fs->get_file($contextid, 'course', 'section', $sectionid,
                            $trailimagepath, $sectionimage->displayedimageindex . '_' . $sectionimage->image)) {
                        $file->delete();
                    }
                }
            }
            $DB->delete_records("format_trail_icon", array('courseid' => $this->courseid,
                'sectionid' => $sectionimage->sectionid));
        }
    }

    /**
     * Delete images.
     *
     * @return none.
     */
    public function delete_images() {
        $sectionimages = $this->get_images($this->courseid);

        if (is_array($sectionimages)) {
            global $DB;
            $context = $this->get_context();
            $fs = get_file_storage();
            $trailimagepath = $this->get_image_path();

            foreach ($sectionimages as $sectionimage) {
                // Delete the image if there is one.
                if (!empty($sectionimage->image)) {
                    if ($file = $fs->get_file($context->id, 'course', 'section',
                            $sectionimage->sectionid, '/', $sectionimage->image)) {
                        $file->delete();
                        // Delete the displayed image.
                        if ($file = $fs->get_file($context->id, 'course', 'section',
                                $sectionimage->sectionid, $trailimagepath, $sectionimage->displayedimageindex
                                . '_' . $sectionimage->image)) {
                            $file->delete();
                        }
                    }
                }
            }
            $DB->delete_records("format_trail_icon", array('courseid' => $this->courseid));
        }
    }

    /**
     * Delete displayed images.
     *
     * @return none.
     */
    public function delete_displayed_images() {
        $sectionimages = $this->get_images($this->courseid);

        if (is_array($sectionimages)) {
            global $DB;

            $context = $this->get_context();
            $fs = get_file_storage();
            $trailimagepath = $this->get_image_path();
            $t = $DB->start_delegated_transaction();

            foreach ($sectionimages as $sectionimage) {
                // Delete the displayed image.
                if ($file = $fs->get_file($context->id, 'course', 'section',
                        $sectionimage->sectionid, $trailimagepath, $sectionimage->displayedimageindex
                        . '_' . $sectionimage->image)) {
                    $file->delete();
                    $DB->set_field('format_trail_icon', 'displayedimageindex', 0,
                            array('sectionid' => $sectionimage->sectionid));
                }
            }
            $t->allow_commit();
        }
    }

    /**
     * Updates the displayed images because the settings have changed.
     * @param int $courseid The course id.
     * @param int $us The instance of format_trail to use.
     * @param array $settings The settings to use.
     * @param int $ignorenorecords True we should not worry about no records existing, possibly down to a restore of a course.
     */
    private function update_displayed_images($courseid, $us, $settings, $ignorenorecords) {
        global $DB;

        $sectionimages = $us->get_images($courseid);
        if (is_array($sectionimages)) {
            $context = $this->get_context();

            $icbc = self::hex2rgb($this->get_settings()['imagecontainerbackgroundcolour']);
            $t = $DB->start_delegated_transaction();
            foreach ($sectionimages as $sectionimage) {
                if ($sectionimage->displayedimageindex > 0) {
                    $sectionimage->newimage = $sectionimage->image;
                    $sectionimage = $us->setup_displayed_image($sectionimage, $context->id, $settings, $icbc);
                }
            }
            $t->allow_commit();
        } else if (!$ignorenorecords) { // Only report error if it's ok not to have records.
            throw new moodle_exception('cannotgetimagesforcourse', 'format_trail', '', null,
                    "update_displayed_images - Course id: " . $courseid);
        }
    }

    /**
     * Generates a thumbnail for the given image
     *
     * If the GD library has at least version 2 and PNG support is available, the returned data
     * is the content of a transparent PNG file containing the thumbnail. Otherwise, the function
     * returns contents of a JPEG file with black background containing the thumbnail.
     *
     * @param string $filepath the full path to the original image file
     * @param int $requestedwidth the width of the requested image.
     * @param int $requestedheight the height of the requested image.
     * @param bool $crop false = scale, true = crop.
     * @param array $icbc The 'imagecontainerbackgroundcolour' as an RGB array.
     * @param string $mime The mime type.
     * @return string|bool false if a problem occurs or the image data.
     */
    private static function generate_image($filepath, $requestedwidth, $requestedheight, $crop, $icbc, $mime) {
        if (empty($filepath) or empty($requestedwidth) or empty($requestedheight)) {
            return false;
        }

        $imageinfo = getimagesize($filepath);

        if (empty($imageinfo)) {
            return false;
        }

        $originalwidth = $imageinfo[0];
        $originalheight = $imageinfo[1];

        if (empty($originalwidth) or empty($originalheight)) {
            return false;
        }

        $original = imagecreatefromstring(file_get_contents($filepath));

        switch ($mime) {
            case 'image/png':
                if (function_exists('imagepng')) {
                    $imagefnc = 'imagepng';
                    $filters = PNG_NO_FILTER;
                    $quality = 1;
                } else {
                    debugging('PNG\'s are not supported at this server, please fix the system configuration' .
                            ' to have the GD PHP extension installed.');
                    return false;
                }
                break;
            case 'image/jpeg':
                if (function_exists('imagejpeg')) {
                    $imagefnc = 'imagejpeg';
                    $filters = null;
                    $quality = 90;
                } else {
                    debugging('JPG\'s are not supported at this server, please fix the system configuration' .
                            ' to have the GD PHP extension installed.');
                    return false;
                }
                break;
            case 'image/gif':
                if (function_exists('imagegif')) {
                    $imagefnc = 'imagegif';
                    $filters = null;
                    $quality = null;
                } else {
                    debugging('GIF\'s are not supported at this server, please fix the system configuration' .
                            ' to have the GD PHP extension installed.');
                    return false;
                }
                break;
            default:
                debugging('Mime type \'' . $mime . '\' is not supported as an image format in the Trail format.');
                return false;
        }

        $width = $requestedwidth;
        $height = $requestedheight;

        // Note: Code transformed from original 'resizeAndCrop' in 'imagelib.php' in the Moodle 1.9 version.
        if ($crop) {
            $ratio = $width / $height;
            $originalratio = $originalwidth / $originalheight;
            if ($originalratio < $ratio) {
                // Change the supplied height - 'resizeToWidth'.
                $ratio = $width / $originalwidth;
                $height = $originalheight * $ratio;
                $cropheight = true;
            } else {
                // Change the supplied width - 'resizeToHeight'.
                $ratio = $height / $originalheight;
                $width = $originalwidth * $ratio;
                $cropheight = false;
            }
        }

        if (function_exists('imagecreatetruecolor')) {
            $tempimage = imagecreatetruecolor($width, $height);
            if ($imagefnc === 'imagepng') {
                imagealphablending($tempimage, false);
                imagefill($tempimage, 0, 0, imagecolorallocatealpha($tempimage, 0, 0, 0, 127));
                imagesavealpha($tempimage, true);
            } else if (($imagefnc === 'imagejpeg') || ($imagefnc === 'imagegif')) {
                imagealphablending($tempimage, false);
                imagefill($tempimage, 0, 0, imagecolorallocate($tempimage, $icbc['r'], $icbc['g'], $icbc['b']));
            }
        } else {
            $tempimage = imagecreate($width, $height);
        }

        if ($crop) {
            // First step, resize.
            imagecopybicubic($tempimage, $original, 0, 0, 0, 0, $width, $height, $originalwidth, $originalheight);
            imagedestroy($original);
            $original = $tempimage;

            // Second step, crop.
            if ($cropheight) {
                // Reset after change for resizeToWidth.
                $height = $requestedheight;
                // This is 'cropCenterHeight'.
                $width = imagesx($original);
                $srcoffset = (imagesy($original) / 2) - ($height / 2);
            } else {
                // Reset after change for resizeToHeight.
                $width = $requestedwidth;
                // This is 'cropCenterWidth'.
                $height = imagesy($original);
                $srcoffset = (imagesx($original) / 2) - ($width / 2);
            }

            if (function_exists('imagecreatetruecolor')) {
                $finalimage = imagecreatetruecolor($width, $height);
                if ($imagefnc === 'imagepng') {
                    imagealphablending($finalimage, false);
                    imagefill($finalimage, 0, 0, imagecolorallocatealpha($finalimage, 0, 0, 0, 127));
                    imagesavealpha($finalimage, true);
                } else if (($imagefnc === 'imagejpeg') || ($imagefnc === 'imagegif')) {
                    imagealphablending($tempimage, false);
                    imagefill($finalimage, 0, 0, imagecolorallocate($finalimage, $icbc['r'], $icbc['g'], $icbc['b']));
                }
            } else {
                $finalimage = imagecreate($width, $height);
            }

            if ($cropheight) {
                // This is 'cropCenterHeight'.
                imagecopybicubic($finalimage, $original, 0, 0, 0, $srcoffset, $width, $height, $width, $height);
            } else {
                // This is 'cropCenterWidth'.
                imagecopybicubic($finalimage, $original, 0, 0, $srcoffset, 0, $width, $height, $width, $height);
            }
        } else {
            $finalimage = $tempimage;
            $ratio = min($width / $originalwidth, $height / $originalheight);

            if ($ratio < 1) {
                $targetwidth = floor($originalwidth * $ratio);
                $targetheight = floor($originalheight * $ratio);
            } else {
                // Do not enlarge the original file if it is smaller than the requested thumbnail size.
                $targetwidth = $originalwidth;
                $targetheight = $originalheight;
            }

            $dstx = floor(($width - $targetwidth) / 2);
            $dsty = floor(($height - $targetheight) / 2);

            imagecopybicubic($finalimage, $original, $dstx, $dsty, 0, 0,
                    $targetwidth, $targetheight, $originalwidth, $originalheight);
        }

        ob_start();
        if (!$imagefnc($finalimage, null, $quality, $filters)) {
            ob_end_clean();
            return false;
        }
        $data = ob_get_clean();

        imagedestroy($original);
        imagedestroy($finalimage);

        return $data;
    }

    /**
     * Returns the RGB for the given hex.
     *
     * @param string $hex
     * @return array
     */
    public static function hex2rgb($hex) {
        if (strlen($hex) == 3) {
            $r = substr($hex, 0, 1);
            $r .= $r;
            $g = substr($hex, 1, 1);
            $g .= $g;
            $b = substr($hex, 2, 1);
            $b .= $b;
        } else {
            $r = substr($hex, 0, 2);
            $g = substr($hex, 2, 2);
            $b = substr($hex, 4, 2);
        }
        return array('r' => hexdec($r), 'g' => hexdec($g), 'b' => hexdec($b));
    }

    /**
     * Returns a new instance of us so that specialised methods can be called.
     * @param int $courseid The course id of the course.
     * @return format_trail object.
     */
    public static function get_instance($courseid) {
        return new format_trail('trail', $courseid);
    }

    /**
     * Prepares the templateable object to display section name.
     *
     * @param \section_info|\stdClass $section
     * @param bool $linkifneeded
     * @param bool $editable
     * @param null|lang_string|string $edithint
     * @param null|lang_string|string $editlabel
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_render_section_name($section, $linkifneeded = true,
            $editable = null, $edithint = null, $editlabel = null) {
        if (empty($edithint)) {
            $edithint = new lang_string('editsectionname', 'format_trail');
        }
        if (empty($editlabel)) {
            $title = $this->get_section_name($section);
            $editlabel = new lang_string('newsectionname', 'format_trail', $title);
        }
        return parent::inplace_editable_render_section_name($section, $linkifneeded,
                $editable, $edithint, $editlabel);
    }

    /**
     * Indicates whether the course format supports the creation of a news forum.
     *
     * @return bool
     */
    public function supports_news() {
        return true;
    }

    /**
     * Returns whether this course format allows the activity to
     * have "triple visibility state" - visible always, hidden on course page but available, hidden.
     *
     * @param stdClass|cm_info $cm course module (may be null if we are displaying a form for adding a module)
     * @param stdClass|section_info $section section where this module is located or will be added to
     * @return bool
     */
    public function allow_stealth_module_visibility($cm, $section) {
        // Allow the third visibility state inside visible sections or in section 0, not allow in orphaned sections.
        return !$section->section || ($section->visible && $section->section <= $this->get_course()->numsections);
    }

    /**
     * Action section.
     *
     * @param \stdClass $section
     * @param string $action
     * @param string $sr
     * @return string
     */
    public function section_action($section, $action, $sr) {
        global $PAGE;

        if ($section->section && ($action === 'setmarker' || $action === 'removemarker')) {
            // Format 'trail' allows to set and remove markers in addition to common section actions.
            require_capability('moodle/course:setcurrentsection', context_course::instance($this->courseid));
            course_set_marker($this->courseid, ($action === 'setmarker') ? $section->section : 0);
            return null;
        }

        // For show/hide actions call the parent method and return the new content for .section_availability element.
        $rv = parent::section_action($section, $action, $sr);
        $renderer = $PAGE->get_renderer('format_trail');
        $rv['section_availability'] = $renderer->section_availability($this->get_section($section));
        return $rv;
    }

    /**
     * Get context.
     *
     * @return \sdtClass
     */
    private function get_context() {
        global $SITE;

        if ($SITE->id == $this->courseid) {
            // Use the context of the page which should be the course category.
            global $PAGE;
            return $PAGE->context;
        } else {
            return context_course::instance($this->courseid);
        }
    }

}

/**
 * Implements callback inplace_editable() allowing to edit values in-place.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_trail_inplace_editable($itemtype, $itemid, $newvalue) {
    global $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        global $DB;
        $section = $DB->get_record_sql(
                'SELECT s.* FROM {course_sections} s'
                . ' JOIN {course} c ON s.course = c.id'
                . ' WHERE s.id = ? AND c.format = ?', array($itemid, 'trail'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section,
                $itemtype, $newvalue);
    }
}

/**
 * Indicates this format uses sections.
 *
 * @return bool Returns true
 */
function callback_trail_uses_sections() {
    return true;
}

/**
 * Used to display the course structure for a course where format=trail
 *
 *
 * @param array $navigation
 * @param \stdClass $course
 * @param \stdClass $coursenode
 * @return bool Returns true
 */
function callback_trail_load_content(&$navigation, $course, $coursenode) {
    return $navigation->load_generic_course_sections($course, $coursenode, 'trail');
}

/**
 * The string that is used to describe a section of the course
 * e.g. Topic, Week...
 *
 * @return string
 */
function callback_trail_definition() {
    return get_string('topic', 'format_trail');
}
