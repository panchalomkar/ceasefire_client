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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @since 2.0
 * @package block_rlms_courserecords
 * @copyright 2014
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * @package block_rlms_courserecords
 * @category rlms_courserecords
 * @copyright 2014
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rlms_courserecords extends block_base
{

	/**
	 *
	 * @var string The name of the block
	 */
	public $blockname = null;
	
	/**
	 *
	 * @var bool A switch to indicate whether content has been generated or not.
	 */
	protected $contentgenerated = false;
	
	/**
	 * Set the initial properties for the block
	 */
	function init()
	{
            $this->title = get_string('pluginname', 'block_rlms_courserecords');
	}
	
	/**
	 *
	 * @return bool Returns false
	 */
	function instance_allow_multiple()
	{
            return true;
	}
	
	function has_config()
	{
            return true;
	}

	/**
	 * Set the applicable formats for this block to all
	 *
	 * @return array
	 */
	function applicable_formats()
	{
            return array (
                'all' => true 
            );
	}
	
	/**
	 * Allow the user to configure a block instance
	 *
	 * @return bool Returns true
	 */
	function instance_allow_config()
	{
            return true;
	}
	
	/**
	 * The navigation block cannot be hidden by default as it is integral to
	 * the navigation of Moodle.
	 *
	 * @return false
	 */
	function instance_can_be_hidden()
	{
            return true;
	}
	
	/**
	 * Find out if an instance can be docked.
	 *
	 * @return bool true or false depending on whether the instance can be docked or not.
	 */
	function instance_can_be_docked()
	{
            return false;
	}
	
	/**
	 * Gets the content for this block by grabbing it from $this->page
	 *
	 * @return object $this->content
	 */
	function get_content()
	{
            global $CFG, $USER, $PAGE,$DB;

            if (isguestuser($USER->id))
            	return $this->content->text = '';
    
	        $PAGE->requires->css(new moodle_url('/blocks/rlms_courserecords/styles.css'));        
            if ($this->contentgenerated === true)
            {
                return $this->content;
            }
           
            $pluginName = 'rlms_courserecords';
            
            $this->content = new stdClass();
            $this->content->text = '';
            
            
            if(('side-pre' == $this->instance->region) || ('side-post' == $this->instance->region))
            {
                $learningRecords = get_string('learning_records', "block_$pluginName");
                
                $url = "{$CFG->wwwroot}/blocks/rlms_courserecords/my_records.php";
                $icon = "{$CFG->wwwroot}/blocks/rlms_courserecords/pix/icon-48.png";
                
                $this->content->text .= '<div class="rlms-course-records">';
                $this->content->text .= '<a href="' . $url . '"> <img src=' . $icon . '>' . $learningRecords . '</a>';
                $this->content->text .= '</div>';
            }
            else
            {
                require_once(dirname(__FILE__) . '/locallib.php');
                
                $this->content->text .= '<div class="rlms-course-records">';
                //$this->content->text .= '<h2>' . get_string('learning_records', "block_$pluginName") . '</h2>';
                $this->content->text .= rlms_courserecords_myrecords($USER->id);
                $this->content->text .= '</div>';
            } 
            
            return $this->content;
	}
	

	/**
	 *
	 *
	 *
	 * {@link block_tree::html_attributes()} is used to get the default arguments
	 * and then we check whether the user has enabled hover expansion and add the
	 * appropriate hover class if it has.
	 *
	 * @return array An array of HTML attributes
	 */
	public function html_attributes()
	{
            $attributes = parent::html_attributes ();
            
            if (! empty ( $this->config->enablehoverexpansion ) && $this->config->enablehoverexpansion == 'yes')
            {
                $attributes ['class'] .= ' block_js_expansion';
            }
            
            return $attributes;
	}
	
	
	/**
	 * Returns the role that best describes the navigation block...
	 * 'navigation'
	 *
	 * @return string 'navigation'
	 */
	public function get_aria_role()
	{
            return 'navigation';
	}

}
