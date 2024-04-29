<?php
class notify_not_logged_users{
    public function __construct() {
        
    }
    function render_mail( $data, $template ) {
        if ( preg_match_all("/\{([a-z]{1,})\}/i",$template,$la_result) ) {
            if ( count($la_result) > 0 ) {
                foreach ( array_values($la_result[1]) as $lc_Attribute ) {
                    if ( property_exists($data,$lc_Attribute) ) {  $template = preg_replace("/\{".$lc_Attribute."\}/",$data->{$lc_Attribute},$template); }
                }
            }
        }
        return $template;
    }
    /**
     * @description string with tags in format {"course"_tag} will be search in the array given and it will replace the tags for the value
     * @author Daniel Carmona <daniel.carmona@rlmssolutions.com>
     * @since 03-26-2018
     * @param sttring $string String with tags to be replaced
     * @param array $settings Array with the next setup ['course' => (array)$data,...,n]
     * @return string String without tags
     */
    function replace_tags($string,$settings = []){
        if(!empty($settings)){
            /*we get all the tags string*/
            $data_tags = $this->get_string_between($string, '{', '}');
            foreach ($data_tags as $tag) {
                $pos = strpos($tag, '_');
                $model = '';
                $property = '';
                if($pos !== false){
                    $property = substr($tag, $pos+1, strlen($tag));
                    $model = substr($tag, 0, $pos);
                    if($model == 'user' && $property == 'fullname'){
                        $settings[$model][$property] = $settings[$model]['firstname'].' '.$settings[$model]['lastname'];
                    }
                }
                if(!empty($model) && !empty($property) && array_key_exists($model, $settings)){
                    $string = str_replace('{'.$tag.'}', $settings[$model][$property] , $string);
                }
            }
        }
        
        return $string;
    }
    
    function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        
        $array_tags = [];
        if ($ini == 0) return $array_tags;
        
        $exist_data = $ini;
        while($exist_data != ''){
            $ini += strlen($start);
            $len = strpos($string, $end, $ini) - $ini;
            $str_tmp = substr($string, $ini, $len);
            $array_tags[] = $str_tmp;
            $string = str_replace($start.$str_tmp.$end, '', $string);
            $exist_data = strpos($string, $start);
        }
        
        return $array_tags;
    }
    
    function send_mail_user($row, $user) {
        global $CFG;
        
        if ($user->email) {
            // Render template
            $body = $this->replace_tags($row['notify_not_logged_users_message'], ['user' => (array)$user,'cfg' => ['wwwroot' => $CFG->wwwroot]]);
            try {
                $site = get_site();
                email_to_user($user, $site->shortname, get_string('subjet_notify_not_logged_users', 'local_system_notifications'),$body,$body);
            } catch (Exception $e) {
                $this->store_log( "error mail student ".$e->getMessage() );            
            }
        }
    }
    function run($record) {
        $users = get_users();
        
        if (count($users) > 0) {
            /*iterate users*/
            foreach ($users as $user) {
                $diff = date_diff(date_create(date('Y-m-d h:i:s',$user->lastlogin)), date_create(date('Y-m-d h:i:s')))->format("%R%a");
                if($diff >= (int)$record['notify_not_logged_users_days']){
                    $this->send_mail_user($record,$user);
                }
            }
        }
    }
}