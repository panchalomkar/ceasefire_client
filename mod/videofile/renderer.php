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
 * Videofile module renderering methods are defined here.
 *
 * @package    mod_videofile
 * @copyright  2013 Jonas Nockert <jonasnockert@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/videofile/locallib.php');

/**
 * Videofile module renderer class
 */
class mod_videofile_renderer extends plugin_renderer_base {

    /**
     * Renders the videofile page header.
     *
     * @param videofile videofile
     * @return string
     */
    public function video_header($videofile) {
        global $CFG, $PAGE;

        $output = '';

        $name = format_string($videofile->get_instance()->name,
                              true,
                              $videofile->get_course());
        $title = $this->page->course->shortname . ': ' . $name;

        $coursemoduleid = $videofile->get_course_module()->id;
        $context = context_module::instance($coursemoduleid);

        // Add videojs css and js files.
        $this->page->requires->css('/mod/videofile/video-js-5.1/video-js.min.css');
        $this->page->requires->css('/mod/videofile/video-js-5.1/videojs-resume.min.css');

        $this->page->requires->js('/mod/videofile/video-js-5.1/video.js', true);
         $this->page->requires->js('/mod/videofile/video-js-5.1/store.min.js', true);
         $this->page->requires->js('/mod/videofile/video-js-5.1/videojs-resume.min.js', true);
        // Set the videojs flash fallback url.
        $swfurl = new moodle_url('/mod/videofile/video-js-5.1/video-js.swf');
        $this->page->requires->js_init_code(
            'videojs.options.flash.swf = "' . $swfurl . '";');

        // Yui module handles responsive mode video resizing.
        if ($videofile->get_instance()->responsive) {
            $config = get_config('videofile');

            $this->page->requires->yui_module('moodle-mod_videofile-videojs','M.mod_videofile.videojs.init',array($videofile->get_instance()->id,$swfurl,$videofile->get_instance()->width,$videofile->get_instance()->height,(boolean) $config->limitdimensions));
        }

        // Header setup.
        $this->page->set_title($title);
        $this->page->set_heading($this->page->course->fullname);

        $output .= $this->output->header();
        $output .= $this->output->heading($name, 2, array('class' => 'hidden-xs hidden-sm hidden-md hidden-lg'));

        if (!empty($videofile->get_instance()->intro)) {
            $output .= $this->output->box_start('generalbox boxaligncenter', 'intro');
            $output .= format_module_intro('videofile',$videofile->get_instance(),$coursemoduleid);
            $output .= $this->output->box_end();
        }

        $width = ($videofile->get_instance()->responsive ? '100%' : $videofile->get_instance()->width);
        $height = ($videofile->get_instance()->responsive ? '100%' : $videofile->get_instance()->height);
        $PAGE->requires->js_amd_inline("
    
            require(['jquery'], function($) {
                $( document ).ready(function() {
                    setTimeout(function(){
                        $('video').parents('.video-js').first().css('width', '$width');
                        $('video').parents('.video-js').first().css('height', '$height');
                    }, 300);
                });
            });"
        );
        
        return $output;
    }

    /**
     * Render the footer
     *
     * @return string
     */
    public function video_footer() {
        return $this->output->footer();
    }

    /**
     * Render the videofile page
     *
     * @param videofile videofile
     * @return string The page output.
     */
    public function video_page($videofile) {
        $output = '';
        $output .= $this->video_header($videofile);
        $output .= $this->video($videofile);
        $output .= $this->video_footer();

        if ($this->isMobile()) {
            return $this->video($videofile);
        }

        return $output;
    }


    /**
     * Utility function for getting a file URL
     *
     * @param stored_file $file
     * @return string file url
     */
    private function util_get_file_url($file) {
        return moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
            false
        );
    }

    /**
     * Utility function for getting area files
     *
     * @param int $contextid
     * @param string $areaname file area name (e.g. "videos")
     * @return array of stored_file objects
     */
    private function util_get_area_files($contextid, $areaname) {
        $fs = get_file_storage();
        return $fs->get_area_files($contextid,'mod_videofile',$areaname,false,'itemid, filepath, filename',false);
    }

    /**
     * Utility function for getting the video poster image
     *
     * @param int $contextid
     * @return url to the poster image (or the default image)
     */
    
    private function get_poster_image($contextid) {
        $posterurl = null;
        $posters = $this->util_get_area_files($contextid, 'posters');
        foreach ($posters as $file) {
            $posterurl = $this->util_get_file_url($file);
            break;  // Only one poster allowed.
        }
        if (!$posterurl) {
            $posterurl = $this->pix_url('banner_bg', 'videofile');
        }

        return $posterurl;
    }

    /**
     * Utility function for creating the video element HTML.
     *
     * @param object $videofile
     * @param url to the video poster image
     * @return string the video element HTML
     */
    private function get_video_element_html($videofile, $posterurl) {
        global $USER;
        /* The width and height are set to auto if responsive flag is set
           but is not ignored. They are still used to calculate proportions
           in the javascript that handles video resizing. */
        $width = ($videofile->get_instance()->responsive ?
            'auto' : $videofile->get_instance()->width);
        $height = ($videofile->get_instance()->responsive ?
            'auto' : $videofile->get_instance()->height);

        // Renders the video element.
        return html_writer::start_tag('video',array('id' => 'videofile-' . $videofile->get_instance()->id.'-user-'.$USER->id,'class' => 'video-js vjs-default-skin',
            'controls' => 'controls', 'muted'=>"muted" ,'preload' => 'auto','width' => $width,'height' => $height,'poster' => $posterurl,'data-setup' => '{}')
        );
    }

    /**
     * Utility function for creating the video source elements HTML.
     *
     * @param int $contextid
     * @return string HTML
     */
    private function get_video_source_elements_html($contextid) {
        $output = '';
        $videos = $this->util_get_area_files($contextid, 'videos');
        foreach ($videos as $file) {
            if ($mimetype = $file->get_mimetype()) {
                $videourl = $this->util_get_file_url($file);

                $output .= html_writer::empty_tag('source',array('src' => $videourl,'type' => $mimetype,'core-external-content' => ''));
            }
        }

        return $output;
    }

    /**
     * Utility function for creating the video caption track elements
     * HTML.
     *
     * @param int $contextid
     * @return string HTML
     */
    private function get_video_caption_track_elements_html($contextid) {
        $output = '';
        $first = true;
        $captions = $this->util_get_area_files($contextid, 'captions');
        foreach ($captions as $file) {
            if ($mimetype = $file->get_mimetype()) {
                $captionurl = $this->util_get_file_url($file);

                // Get or construct caption label for video.js player.
                $filename = $file->get_filename();
                $dot = strrpos($filename, '.');
                if ($dot) {
                    $label = substr($filename, 0, $dot);
                } else {
                    $label = $filename;
                }

                // Perhaps filename is a three letter ISO 6392 language code (e.g. eng, swe)?
                if (preg_match('/^[a-z]{3}$/', $label)) {
                    $maybelabel = get_string($label, 'core_iso6392');

                    /* Strings not in language files come back as [[string]], don't
                       use those for labels. */
                    if (substr($maybelabel, 0, 2) !== '[[' ||
                            substr($maybelabel, -2, 2) === ']]') {
                        $label = $maybelabel;
                    }
                }

                $options = array('kind' => 'captions','src' => $captionurl,'label' => $label);
                if ($first) {
                    $options['default'] = 'default';
                    $first = false;
                }

                // Track seems to need closing tag in IE9 (!).
                $output .= str_replace(">"," core-external-content />",html_writer::start_tag('track', $options));
            }
        }

        return $output;
    }

    /**
     * Utility function for getting the HTML for the alternative video
     * links in case video isn't showing/playing properly.
     *
     * @param int $contextid
     * @return string HTML
     */
    private function get_alternative_video_links_html($contextid) {
        $output = '';
        $videooutput = '';

        $first = true;
        $videos = $this->util_get_area_files($contextid, 'videos');
        foreach ($videos as $file) {
            if ($mimetype = $file->get_mimetype()) {
                $videourl = $this->util_get_file_url($file);

                if ($first) {
                    $first = false;
                } else {
                    $videooutput .= ', ';
                }
                $extension = pathinfo($file->get_filename(), PATHINFO_EXTENSION);
                $videooutput .= html_writer::tag('a',$extension,array('href' => $videourl));
            }
        }

        $output = html_writer::tag('p',get_string('video_not_playing','videofile',$videooutput),array());
        return html_writer::tag('div',$output,array('class' => 'videofile-not-playing-msg'));
    }

    /**
     * Renders videofile video.
     *
     * @param videofile $videofile
     * @return string HTML
     */
    public function video($videofile) {
        global $PAGE;
        $PAGE->requires->js(new moodle_url("/lib/xhprof/xhprof_html/jquery/jquery-1.2.6.js"));
        $output  = '';
        $contextid = $videofile->get_context()->id;

        // Open videofile div.
        $vclass = ($videofile->get_instance()->responsive ?
            'videofile videofile-responsive' : 'videofile');
        $output .= $this->output->container_start($vclass);

        // Open video tag.
        $posterurl = $this->get_poster_image($contextid);
        $output .= $this->get_video_element_html($videofile, $posterurl);

        // Elements for video sources.
        $output .= $this->get_video_source_elements_html($contextid);

        // Elements for caption tracks.
        $output .= $this->get_video_caption_track_elements_html($contextid);

        // Close video tag.
        $output .= html_writer::end_tag('video');

        // Alternative video links in case video isn't showing/playing properly.
        $output .= $this->get_alternative_video_links_html($contextid);

        global $PAGE, $CFG, $COURSE;


        if($videofile->get_instance()->forward == 0){
            $no_forward = "get_video = document.getElementsByTagName('video');
                            reseek = true;
                            //If the user is seeking not save the progress
                            get_video[0].onseeking = function(){
                                if(reseek == false){
                                    no_save = true;
                                }
                            }
                            get_video[0].onseeked = function(){
                                if(reseek == false){
                                    reseek = true;
                                } else {
                                    seek = true;
                                    $.ajax({
                                        method: \"POST\",
                                        url : '$CFG->wwwroot/mod/videofile/video_attempts.php',
                                        data : {
                                            cmid : " . $PAGE->cm->id . ",
                                            seek : get_video[0].currentTime,
                                        },
                                    }).done(function(data){
                                        result = JSON.parse(data);
                                        //get_video[0].currentTime = parseInt(result.result);
                                        no_save = false;
                                        reseek = false;
                                    })
                                }
                            }";
        } else {
            $no_forward = '';
        }
        
        $PAGE->requires->js_amd_inline("
            require(['jquery'], function($) {
                var attempt;
                no_save = false;

                $.ajax({
                    method: \"POST\",
                    url : '$CFG->wwwroot/mod/videofile/video_attempts.php',
                    data : { cmid : " . $PAGE->cm->id . " },
                    dataType: \"json\"
                })
                .done(function(data){
                    attempt = data;
                    var miid_seek = $('video[id^=videofile-]').attr('id');
                    if(data.last.second > 0){
                        /*I have comment below code beacuse when video forward setting is enable , video started from last point*/
                        /*document.getElementById(miid_seek).currentTime = data.last.second;
                        document.getElementById(miid_seek).play();*/
                    }
                })
                var count = 0;
                var flag = 1;
                setInterval(function(){
                    video = document.getElementsByTagName('video');
                    percentage = (100 / video[0].duration) * video[0].currentTime;
                    if(percentage > 98 ){
                        percentage = 100;
                    }
                    // get the attempt id
                    var attempt_id = ( typeof(attempt.last) != 'undefined' ) ? attempt.last.id : attempt.current_id;
                    if(percentage > 0 ){
                        if(".$videofile->get_instance()->videoprogress." <= percentage && flag ){
                            count = 1;
                            flag = 0;
                        } else {
                            count = 0;
                        }
                        $.ajax({
                            method: \"POST\",
                            url: \"" . $CFG->wwwroot . "/mod/videofile/video_attempts.php\",
                            data: {
                                id : attempt_id,
                                second : video[0].currentTime,
                                percent : parseInt(percentage),
                                cmid : " . $PAGE->cm->id . ",
                                course : " . $COURSE->id . ",
                                hasUpdate : count
                            }
                        }).done(function(data){
                            result = JSON.parse(data);
                            if(result.status == 'completed'){
                                $('#module-".$PAGE->cm->id. "').find('.autocompletion img').remove();
                                $('#module-".$PAGE->cm->id. "').find('.autocompletion').append('<img class=icon src=$CFG->wwwroot/theme/image.php/remui/core/1564492370/i/completion-manual-y>');
                            }
                        })
                    }
                }, 5000)
                ".$no_forward."
            });"
        );
        

        // Close videofile div.
        $output .= $this->output->container_end();


        return $output;
    }

    public function video_url($videofile) {
        $output = '';
        $output .= $this->video_header($videofile);
        $output .= $this->video_external($videofile, $record_attempt);
        $output .= $this->video_footer();
        if ($this->isMobile()) {
           return $this->video_external($videofile, $record_attempt);
        }
        return $output;
    }

    private function isMobile(){
        $useragent=$_SERVER['HTTP_USER_AGENT'];

        return(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)));
    }

    public function video_external_mobile($videofile){
        $this->isMobile == true;
        return $this->video_external($videofile);
    }

    public function video_external($videofile){
        global $CFG, $PAGE, $COURSE;
        $url = $videofile->get_instance()->video_url;
        $output = "";
        if(strpos($url, 'youtu') !== false){
            if(strpos($url, 'watch?v=')){
                $url = explode('watch?v=', $url);
            } else {
                $url = explode('youtu.be/', $url);
            }

            if($videofile->get_instance()->responsive){
                $output .= html_writer::start_tag('div', array('class' => 'col-md-8 col-md-offset-2'));
                    $output .= html_writer::start_tag('div', array('class' => 'video-container'));
                        $output .= html_writer::tag('div', '', array('id' => 'rlms-video', 'class' => 'embed-responsive-item'));
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
            } else {
                $output .= html_writer::tag('div', '', array('id' => 'rlms-video'));
            }
            if ($this->isMobile() || $this->isMobile) {

                $output .= html_writer::tag('iframe', '', array('id' => 'rlms-video', 'src' => "https://www.youtube.com/embed/" . $url[1] . '', 'width' => $videofile->get_instance()->width, 'height' => $videofile->get_instance()->height, 'allowfullscreen'=> 'allowfullscreen', 'mozallowfullscreen' => 'mozallowfullscreen', 'msallowfullscreen' => 'msallowfullscreen', 'oallowfullscreen'=> 'oallowfullscreen', 'webkitallowfullscreen' => 'webkitallowfullscreen'));
            }

            $controlsoff="?&amp;controls=1&showinfo=0&rel=0";

            $output .= "
                <script>
                    var attempt;
                    save = false;
                    // Call to ajax for create the video attempt
                    var attempt;
                    save = false;
                    seek_to = 0;
                    var tag = document.createElement('script');
                    tag.src = \"https://www.youtube.com/iframe_api\";
                    var firstScriptTag = document.getElementsByTagName('script')[0];
                    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                    var player;
                    function onYouTubeIframeAPIReady() {
                        player = new YT.Player('rlms-video', {
                        height: '" . $videofile->get_instance()->height . "',
                        width: '" . $videofile->get_instance()->width . "',
                        videoId: '" . $url[1] . $controlsoff . "',
                        events: {
                            'onReady': onPlayerReady,
                            'onStateChange': onPlayerStateChange
                            }
                        });
                    }
                    function onPlayerReady(event) {

                        var url = '{$CFG->wwwroot}/mod/videofile/video_attempts.php';
                        var data = { cmid : " . $PAGE->cm->id . " };

                        $.post(url, data, function(res){
                            res = JSON.parse(res);

                            res.last.second = parseInt(res.last.second);

                            attempt = res;
                            if(res.last.second > 0){
                                player.seekTo(res.last.second);
                            }
                        });

                        setInterval(function(){
                            percentage = (100 / player.getDuration()) * player.getCurrentTime();
                            seconds = player.getCurrentTime();
                            $.ajax({
                                method: \"POST\",
                                url: \"" . $CFG->wwwroot . "/mod/videofile/video_attempts.php\",
                                data: {
                                    id : attempt.current_id,
                                    second : seconds,
                                    percent : percentage,
                                    cmid : " . $PAGE->cm->id . ",
                                    course : " . $COURSE->id . "
                                }
                            }).done(function(data){
                                console.log(data);
                            })
                        }, 5000);
                    }
                    function onPlayerStateChange(event){
                        if (event.data == YT.PlayerState.ENDED){
                            percentage = (100 / player.getDuration()) * player.getCurrentTime();
                            seconds = player.getCurrentTime();
                            $.ajax({
                                method: \"POST\",
                                url: \"" . $CFG->wwwroot . "/mod/videofile/video_attempts.php\",
                                data: {
                                    id : attempt.current_id,
                                    second : seconds,
                                    percent : percentage
                                }
                            })
                        }
                    }
                </script>
            ";
        } elseif(strpos($url, 'vimeo') !== false){
            $url = explode('/', $url);
            $last = count($url) -1;

            if($videofile->get_instance()->forward == 0){
                $forward = "post('addEventListener', 'seek');";
                //This var include the javascript for the seek event
                $seek_event = "
                case 'seek':
                    if(no_reseek == true){
                        no_reseek = false;
                    } else {
                        seek = true;
                        $.ajax({
                            method: \"POST\",
                            url : '$CFG->wwwroot/mod/videofile/video_attempts.php',
                            data : {
                                cmid : " . $PAGE->cm->id . ",
                                seek : data.data.seconds,
                            },
                        }).done(function(data){
                            result = JSON.parse(data);
                            post(\"seekTo\", parseInt(result.result));
                            post('play');
                            seek = false;
                            no_reseek = true;
                        })
                    }
                    break;";
            } else {
                $forward = "";
                $seek_event = "";
            }

            if($videofile->get_instance()->responsive){
                $output .= html_writer::start_tag('div', array('class' => 'col-md-8 col-md-offset-2'));
                    $output .= html_writer::start_tag('div', array('class' => 'video-container'));
                        $output .= html_writer::tag('iframe', '', array('id' => 'rlms-video', 'src' => "https://player.vimeo.com/video/" . $url[$last] . '?api=1&player_id=rlms-video', 'width' => $videofile->get_instance()->width, 'height' => $videofile->get_instance()->height, 'allowfullscreen'=> 'allowfullscreen', 'mozallowfullscreen' => 'mozallowfullscreen', 'msallowfullscreen' => 'msallowfullscreen', 'oallowfullscreen'=> 'oallowfullscreen', 'webkitallowfullscreen' => 'webkitallowfullscreen')
                    );
                    $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('div');
            } else {
                $output .= html_writer::tag('iframe', '', array('id' => 'rlms-video', 'src' => "https://player.vimeo.com/video/" . $url[$last] . '?api=1&player_id=rlms-video', 'width' => $videofile->get_instance()->width, 'height' => $videofile->get_instance()->height, 'allowfullscreen'=> 'allowfullscreen', 'mozallowfullscreen' => 'mozallowfullscreen', 'msallowfullscreen' => 'msallowfullscreen', 'oallowfullscreen'=> 'oallowfullscreen', 'webkitallowfullscreen' => 'webkitallowfullscreen'));
            }

            $PAGE->requires->js_amd_inline("
                require(['jquery'], function($) {
                    // Call to ajax for create the video attempt
                    var attempt;
                    save = false;
                    seek = false;
                    no_reseek = false;
                    attempt;

                    //This script is for vimeo progress
                    $(function() {
                        //Get the iframe of the video
                        var lastsecond = 0;
                        var iframe = $('#rlms-video')[0];
                        var player = $('iframe');
                        var playerOrigin = '*';
                   
                        // Listen for messages from the player
                        if (window.addEventListener) {
                            window.addEventListener('message', onMessageReceived, false);
                        } else {
                            window.attachEvent('onmessage', onMessageReceived, false);
                        }

                        // Handle messages received from the player
                        function onMessageReceived(event) {

                            // Handle messages from the vimeo player only
                            if (!(/^https?:\/\/player.vimeo.com/).test(event.origin)) {
                                return false;
                            }

                            if (playerOrigin === '*') {
                                playerOrigin = event.origin;
                            }

                            var data = JSON.parse(event.data);

                            switch (data.event) {
                                case 'ready':
                                    onReady();
                                break;

                                case 'playProgress':
                                    onPlayProgress(data.data);
                                break;

                                case 'pause':
                                    //onPause();
                                break;

                                case 'finish':
                                    //onFinish();
                                break;

                                " . $seek_event . "
                            }
                        }

                        // Helper function for sending a message to the player
                        function post(action, value) {
                            var data = {
                                method: action
                            };

                            if (value) {
                                data.value = value;
                            }

                            var message = JSON.stringify(data);
                            player[0].contentWindow.postMessage(data, playerOrigin);
                        }

                        function onReady() {
                            post('addEventListener', 'playProgress');
                            ".$forward."
                            $.ajax({
                                method: \"POST\",
                                url : '$CFG->wwwroot/mod/videofile/video_attempts.php',
                                data : { cmid : " . $PAGE->cm->id . " },
                                dataType: \"json\"
                            })
                            .done(function(data){
                                attempt = data;
                                if(data.last.second > 0){
                                    post(\"seekTo\", data.last.second);
                                    post('play');
                                }
                            })
                        }

                        //Save the progress in the database
                        function onPlayProgress(data) {
                            if(save == true || seek == true){
                                return false;
                            }
                            $.ajax({
                                dataType: \"json\",
                                method: \"POST\",
                                url: \"" . $CFG->wwwroot . "/mod/videofile/video_attempts.php\",
                                data : {
                                    id : attempt.current_id,
                                    second : parseInt(data.seconds),
                                    percent : parseInt(data.percent * 100)
                                }
                            });
                            save = true;
                        }
                    });

                    //Intervals for save.
                    setInterval(function(){
                        save = false;
                    }, 30000)
                    ".$no_forward."
                });"
            );
        } else {
            // Open videofile div.
            $vclass = ($videofile->get_instance()->responsive ? 'videofile videofile-responsive' : 'videofile');
            $output .= $this->output->container_start($vclass);

            $width = ($videofile->get_instance()->responsive ? 'auto' : $videofile->get_instance()->width);
            $height = ($videofile->get_instance()->responsive ? 'auto' : $videofile->get_instance()->height);

            $output .= html_writer::start_tag('video',
                array(
                    'id' => 'videofile-' . $videofile->get_instance()->id,
                    'class' => 'video-js vjs-default-skin',
                    'controls' => 'controls',
                    'preload' => 'auto',
                    'width' => $width,
                    'height' => $height,
                    'poster' => $posterurl,
                    'data-setup' => '{}',
                    'src' => $url
                )
            );

            $PAGE->requires->js_amd_inline("
                require(['jquery'], function($) {
                    var attempt;
                    no_save = false;
                    $.ajax({
                        method: \"POST\",
                        url : '$CFG->wwwroot/mod/videofile/video_attempts.php',
                        data : { cmid : " . $PAGE->cm->id . " },
                        dataType: \"json\"
                    })
                    .done(function(data){
                        attempt = data;

                        var miid_seek = $('video[id^=videofile-]').attr('id');
                        if(data.last.second > 0){
                            document.getElementById(miid_seek).currentTime = data.last.second;
                            document.getElementById(miid_seek).play();
                        }
                    })

                    setInterval(function(){
                        video = document.getElementsByTagName('video');
                        percentage = (100 / video[0].duration) * video[0].currentTime;
                        $.ajax({
                            method: \"POST\",
                            url: \"" . $CFG->wwwroot . "/mod/videofile/video_attempts.php\",
                            data: {
                                id : attempt.current_id,
                                second : video[0].currentTime,
                                percent : parseInt(percentage),
                                cmid : " . $PAGE->cm->id . ",
                                course : " . $COURSE->id . "
                            }
                        });
                    }, 10000)
                    ".$no_forward."
                });"
            );

            // Close videofile div.
            $output .= $this->output->container_end();
        }
        return $output;
    }
}
