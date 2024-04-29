<?php
use theme_remui\widget;
//use context_helper;

function get_filter_block($filter, $multifilter) {
  require_once("../../config.php");
  global $CFG;
  $output = '';
    
  if(isset($_POST['clearall'])){
    $filter = null;
    $multifilter = null;
  }

  $search = optional_param('search', '', PARAM_TEXT);

  $output .= html_writer::start_tag('div', array('class' => 'search-filter-buttons col-sm-12 col-md-12 col-lg-12 col-xl-3'));
    //search filter
    $output .= html_writer::start_div('row mar-btm header-search');
      $output .= html_writer::start_div(' col-sm-12 searchbox px-0');
        $output .= html_writer::start_div('input-group custom-search-form');
          $output .= html_writer::tag('i', '', array('class' => 'men men-search-phx'));

          $classbutton = ($search) ? 'd-block' : 'hidden';
          $iconsearch = ($search) ? 'fa-close' : '';
        
          $output .= html_writer::tag('input','',['value'=>$search, 'name' => 'txt', 'id' => 'txt', 'placeholder' => get_string('Search...', 'local_people'), 'class' => 'form-control input-lg', 'type' => 'text']);
          $output .= html_writer::start_tag('span',['class' => 'input-group-btn']);
            $output .= html_writer::start_tag('button',['class' => 'btn btn-white btn-fab btn-fab-mini '.$classbutton,'id' => 'searchbutton']);
              $output .= html_writer::tag('i', '', ['class' => 'fa ' . $iconsearch ]);
            $output .= html_writer::end_tag('button');
          $output .= html_writer::end_tag('span');

        $output .= html_writer::end_div();
      $output .= html_writer::end_div();

      /////////////////////// COMPANIES ////////////////////////////////

      //if( isset($CFG->rlms_allow_mt) && $CFG->rlms_allow_mt ) {
        global $DB;

        /**
        * Defined temp variable as an empty array
        * @author Deyby G.
        * @since June 07 of 2017
        * @ticket 986
        * @rlms 
        */

        $output .= html_writer::start_div('col-sm-12 content-companies px-0');
          $output .= html_writer::start_div('input-group');
            $checkboxes .= html_writer::start_tag('span', array('class' => 'helptooltip'));
              $checkboxes .= html_writer::start_tag('a', array('target' => '_blank','aria-haspopup' => 'true','aria-haspopup' => 'true','href' => $CFG->wwwroot .'/help.php?component=local_people&identifier=select_tenant_people_input&lang='.current_language()));
                //$checkboxes .= html_writer::tag('img', '', array('src'=> $OUTPUT->pix_url('help'), 'class'=>'iconhelp', 'alt'=>'help'));
              $checkboxes .= html_writer::end_tag('a');
            $checkboxes .= html_writer::end_tag('span');

            $output .= $checkboxes;

          $output .= html_writer::end_div();
        $output .= html_writer::end_div();
      //}
    
      /**
      * Descripcion : Add create user button
      * @author Praveen.
      * @since 26 oct 2016
      * @rlms
      */
      /////////////////////// CREATE USER ///////////////////////////////

      //$output .= html_writer::start_tag('div', array('class' => 'create-user-top-wrapper col-sm-4'));   
      //$output .= html_writer::end_div();
      ////////////////////////////end///////////////////////////////////

      /**
      * Descripcion : Add clear all filter button
      * @author Hernan A.
      * @since 17/08/2016
      * @rlms
      */
      /////////////////////// CLEAR ALL FILTER BUTTON  ///////////////////////////////
    
      $output .= html_writer::start_tag('form', array('id'=>'form-clearall', 'method' => 'post', 'action' => htmlspecialchars($_SERVER['PHP_SELF']) ));
        $output .= html_writer::start_tag('input', array("type" => "hidden", "name"=>"clearall", "id"=>"clearall","value"=>"clearall"));
        $output .= html_writer::end_tag('input');
      $output .= html_writer::end_tag('form');

    $output .= html_writer::end_div(); //end serach filter 

    $output .= html_writer::start_div('col-sm-12 col-md-12 report-left-block-acordeon px-0');
      $output .= html_writer::start_div('pad-all card filterpeople');
          
        $output .= html_writer::start_div('hidden-xs hidden-sm', array('id'=>'SearchParameters'));

          $output .= html_writer::start_div('row');
            
            $output .= html_writer::start_div('aply-filtter col-sm-12 px-0');

              $output .= html_writer::start_div('content-apply col-sm-6'); 
                $output .= html_writer::start_tag('button', array("class" => "btn btn-round btn-primary", "id" => "send", "type" => "submit"));
                  $output .= get_string('search', 'local_people');
                $output .= html_writer::end_tag('button');
              $output .= html_writer::end_div();
                      
              $output .= html_writer::start_div('icon-filter col-sm-6'); 
                $output .= html_writer::start_tag('a', array('class' => 'clear-button d-inline-block'));
                  $cleartxt = get_string('Clear_all_filter', 'local_people');
                  $output .= html_writer::tag('i', '', array('class' => 'wid wid-icon-clearfiltter clear_filtter', "id" => "reset-form"));
                    $output .= $cleartxt;
                $output .= html_writer::end_tag('a');
              $output .= html_writer::end_div();

            $output .= html_writer::end_div();
                  
            $output .= html_writer::start_div('col-xs-12 col-sm-12 col-md-12 col-lg-12 content-filtter');
              $output .= html_writer::start_div('panel-group ', array('id' => 'accordion'));

                // New row added here
                $ffilter = (isset($multifilter['firstname'])) ? $multifilter['firstname'] : null;

                 if( isset($ffilter->field) && $ffilter->field == 'firstname') {
                  $firstnameop    = $ffilter->op;
                  $firstnamevalue = $ffilter->value;
                } else {
                  $firstnameop    = '';
                  $firstnamevalue = '';
                }

                // $output .= get_first_name_filter($firstnameop, $firstnamevalue);

                //////////////////////////////////////////////////////////

                $ffilter = (isset($multifilter['lastname'])) ? $multifilter['lastname'] : null;

                if(isset($ffilter->field) && $ffilter->field=='lastname') {
                  $lastnameop    =  $ffilter->op;
                  $lastnamevalue =  $ffilter->value;
                } else {
                  $lastnameop    = '';
                  $lastnamevalue = '';
                }

                // $output .= get_last_name_filter($lastnameop, $lastnamevalue);

                //////////////////////////////////////////////////////////
                $output .= html_writer::start_tag('div', array('class' => 'textfilter'));
                  $ffilter = (isset($multifilter['userfullname'])) ? $multifilter['userfullname'] : null;

                  if(isset($ffilter->field) && $ffilter->field=='userfullname') {
                    $userfullnameop    = $ffilter->op;
                    $userfullnamevalue = $ffilter->value;
                  } else {
                    $userfullnameop    = '';
                    $userfullnamevalue = '';
                  }

                  $output .= get_userfullname_filter($userfullnameop, $userfullnamevalue);

                  //////////////////////////////////////////////////////////

              
                  $ffilter = (isset($multifilter['email'])) ? $multifilter['email'] : null;

                  if(isset($ffilter->field) && $ffilter->field=='email') {
                    $emailop    = $ffilter->op;
                    $emailvalue = $ffilter->value;
                  } else {
                    $emailop    = '';
                    $emailvalue = '';
                  }

                  $output .= get_email_filter($emailop, $emailvalue);

                  //////////////////////////////////////////////////////////

                  $ffilter = (isset($multifilter['username'])) ? $multifilter['username'] : null;

                   if(isset($ffilter->field) && $ffilter->field=='username') {
                    $usernameop    = $ffilter->op;
                    $usernamevalue = $ffilter->value;
                  } else {
                    $usernameop    = '';
                    $usernamevalue = '';
                  }

                  $output .= get_username_filter($usernameop, $usernamevalue);
                $output .= html_writer::end_tag('div');

                //////////////////////////////////////////////////////////

                $output .= html_writer::start_tag('div', array('class' => 'option-filters'));
                  $ffilter = (isset($multifilter['city'])) ? $multifilter['city'] : null;

                   if(isset($ffilter->field) && $ffilter->field=='city') {
                    $cityop  = $ffilter->ar;
                  } else {
                    $cityop  = '';
                  }
                $output .=  html_writer::start_tag('div', array('class' => 'select-city'));
                  $output .= get_city_filter($cityop);
                $output .= html_writer::end_tag('div');
                  //////////////////////////////////////////////////////////

                  $ffilter = (isset($multifilter['country'])) ? $multifilter['country'] : null;

                    if(isset($ffilter->field) && $ffilter->field=='country') {
                    $countryop    = $ffilter->ar;
                  } else {
                    $countryop    = '';
                  }
                $output .=  html_writer::start_tag('div', array('class' => 'select-country'));
                    $output .= get_country_filter($countryop);
                $output .= html_writer::end_tag('div');

                  //////////////////////////////////////////////////////////
                      
                  $ffilter = (isset($multifilter['courserole'])) ? $multifilter['courserole'] : null;

                  if(isset($ffilter->field) && $ffilter->field=='courserole') {
                    $courselist = $ffilter->ar;
                    $courserole = $ffilter->role;
                  } else {
                    $courselist = '';
                    $courserole = '';
                  }
                  $output .=  html_writer::start_tag('div', array('class' => 'select-course'));
                    $output .= get_courserole_filter($courselist, $courserole);
                  $output .= html_writer::end_tag('div');

                   //////////////////////////////////////////////////////////
                   /*System role*/
                  $ffilter = (isset($multifilter['systemrole'])) ? $multifilter['systemrole'] : null;

                   if(isset($ffilter->field) && $ffilter->field=='systemrole') {
                   
                   } else {
                    $systemrole = '';
                   }

                  $output .= get_systemrole_filter($systemrole);

                  //////////////////////////////////////////////////////////
                          
                  $ffilter = (isset($multifilter['auth'])) ? $multifilter['auth'] : null;
                  /*Autentification*/
                  if(isset($ffilter->field) && $ffilter->field=='auth') {
                    $authenticationop = $ffilter->op;
                  } else {
                    $authenticationop = null;
                  }
                  $output .= get_auth_filter($authenticationop);
                $output .= html_writer::end_tag('div');

                //////////////////////////////////////////////////////////

                $output .= html_writer::start_tag('div', array('class' => 'radio-filters'));
                  $ffilter = (isset($multifilter['confirmed'])) ? $multifilter['confirmed'] : null;

                   if(isset($ffilter->field) && $ffilter->field=='confirmed') {
                    $authenticationop = $ffilter->value;
                  } else {
                    $authenticationop = null;
                  }
                $output .= html_writer::start_tag('div', array('class' => 'confirm-form'));
                    $output .= get_confirmed_filter($authenticationop);
                $output .= html_writer::end_tag('div'); 
                  //////////////////////////////////////////////////////////

                  $ffilter = (isset($multifilter['suspended'])) ? $multifilter['suspended'] : null;

                  if(isset($ffilter->field) && $ffilter->field=='suspended') {
                    $authenticationop = $ffilter->value;
                  } else {
                    $authenticationop = null;
                  }
                  $output .= html_writer::start_tag('div', array('class' => 'suspend-form'));
                    $output .= get_suspended_filter($authenticationop);
                   $output .= html_writer::end_tag('div'); 
                  $output .= html_writer::end_tag('div');

                  //////////////////////////////////////////////////////////

                  
                  $output .= html_writer::start_tag('div', array('class' => 'date-filters'));
                  $ffilter = (isset($multifilter['firstaccessd'])) ? $multifilter['firstaccessd'] : null;

                  if(isset($ffilter->field) && $ffilter->field=='firstaccessd') {
                    $firstaccessedgt = $ffilter->gt;
                    $firstaccessedlt = $ffilter->lt;
                    $neveraccessf    = $ffilter->access;
                  } else {
                    $firstaccessedgt = '';
                    $firstaccessedlt = '';
                    $neveraccessf    = '';
                  }

                  $output .= get_firstaccessd_filter($firstaccessedgt, $firstaccessedlt,$neveraccessf);

                  //////////////////////////////////////////////////////////

                  $ffilter = (isset($multifilter['lastaccessed'])) ? $multifilter['lastaccessed'] : null;

                  if(isset($ffilter->field) && $ffilter->field=='lastaccessed') {
                    $lastaccessedgt = $ffilter->gt;
                    $lastaccessedlt = $ffilter->lt;
                    $neveraccessl   = $ffilter->access;
                  } else {
                    $lastaccessedgt = '';
                    $lastaccessedlt = '';
                    $neveraccessl   = '';
                  }

                  $output .= get_lastaccessed_filter($lastaccessedgt, $lastaccessedlt, $neveraccessl);

                  //////////////////////////////////////////////////////////

                  $ffilter = (isset($multifilter['lastmodified'])) ? $multifilter['lastmodified'] : null;

                   if(isset($ffilter->field) && $ffilter->field=='lastmodified') {
                    $lastmodifiedgt = $ffilter->gt;
                    $lastmodifiedlt = $ffilter->lt;
                    $nevermodified  = $ffilter->edited;
                  } else {
                    $lastmodifiedgt = '';
                    $lastmodifiedlt = '';
                    $nevermodified  = '';
                  }
                 
                  $output .= get_lastmodified_filter($lastmodifiedgt, $lastmodifiedlt, $nevermodified);
                $output .= html_writer::end_tag('div');

                //////////////////////////////////////////////////////////

                $output .= get_clear_filter();

              $output .= html_writer::end_div(); //panel-group close here
            $output .= html_writer::end_div(); //
          $output .= html_writer::end_div(); // Row  div close here

        $output .= html_writer::end_div(); // panel div close here

      $output .= html_writer::end_div();
    $output .= html_writer::end_div();
  $output .= html_writer::end_tag('div');
  
  return $output;
}

/*
 * @auther alok_kumar@rlms
 * @param $filter
 * @param
 * @param
 * @return
 */
/**
* @issue #6: Issue with translations in LMS, change search...
* @author Jonatan U.
* @since 2018-02-12
* @rlms
*/
function get_user_block($filter, $sortfield='', $order='ASC', $multifilter) {
  global $CFG, $DB, $OUTPUT, $USER;

  /**
  * Descripcion : Add clear all case, Set filter an multifilter to null and set filter page to 0
  * @author Hernan A.
  * @since 17/08/2016
  * @rlms
  */
    
  if(isset($_POST['clearall'])){
      $filter = new stdClass();
      $filter->page = 0;
      $filter->userperpage = $_SESSION['userperpage'];
      $multifilter = array('page'=>'0');
  }

  $sitecontext = context_system::instance();
  $page = '';
  $params = array();

  /*if(isset($filter->field) && $filter->field) {
    $params['filter'] = $filter->field;
    $params[$filter->field.'op'] = $filter->op;
    $params[$filter->field] = $filter->value;
  }*/

  if ($sortfield == 'firstname') {
    $neworder = $order == 'ASC' ? 'DESC' : 'ASC';
  } else {
    $neworder = 'ASC';
  }

  $param['sort']  = 'firstname';
  $param['order'] =  $neworder;

  $firstnameurl = new moodle_url($CFG->wwwroot . '/local/people/index.php', $param);

  if ($sortfield == 'lastname') {
    $neworder = $order == 'ASC' ? 'DESC' : 'ASC';
  } else {
    $neworder = 'ASC';
  }

  $param['sort']  = 'lastname';
  $param['order'] =  $neworder;

  $lastnameurl = new moodle_url($CFG->wwwroot . '/local/people/index.php',$param);

  if ($sortfield == 'lastaccess') {
    $neworder = $order == 'ASC' ? 'DESC' : 'ASC';
  } else {
    $neworder = 'ASC';
  }

  $param['sort']  =  'lastaccess';
  $param['order'] =   $neworder;

  $lastaccessurl = new moodle_url($CFG->wwwroot . '/local/people/index.php', $param);

  $output = '';

  if($sortfield){
    $params['sort']  = $sortfield;
    $params['order'] = $order;
  }

  if($filter->page) {
    $params['page'] = $filter->page;
  }

  $search = optional_param('search', '', PARAM_TEXT);
  
  if($filter->userperpage) {
   $_SESSION['userperpage'] = $filter->userperpage;
  }

  $output .= html_writer::start_div('col-sm-12 col-md-12 col-lg-12 col-xl-9 mar-no pad-no pad table-filters');
    $output .= html_writer::start_div('bg-grey-dark');
      $output .= html_writer::start_div('pad-btm pad-hor card');
        
        $output .= html_writer::start_div('row mar-btm');

          $result    = get_user_list($filter, $multifilter);
          $users     = $result->data;
          $usercount = $result->count;

          /////////////////////// FILTER BTN ///////////////////////////////

          $output .= html_writer::start_tag('div', array('class'=> 'filters-list'));

            $arrayTypes = array(
              "textfilter"=>"value",
              "multipleselectfilter"=>"op",
              "courserole"=>"op",
              "systemrole"=>"op",
              "selectfilter"=>"op",
              "datefilter"=>"gt",
              "datefilter"=>"lt",
              "datefilterr"=>"sm"
            );

            foreach($multifilter as $ffilter){
              if($ffilter->field && $ffilter->field != "filter-true" && str_replace('\'','',$ffilter->{$arrayTypes[$ffilter->type]}) != ""){
                
                $output .= html_writer::start_tag('div', array('class' => 'btn btn-primary btn-sm reset', 'data-name' => $ffilter->field));
                  $output .= html_writer::start_tag('span', array( ));
                    $output .= get_string($ffilter->field, 'local_people');
                    $output .= html_writer::start_tag('span', array('class' => 'remove-filter'));    
                      $output .= '<i class="fa fa-close"></i> ';
                    $output .= html_writer::end_tag('span');
                  $output .= html_writer::end_tag('span');
                $output .= html_writer::end_tag('div');
              }
            }
          
          $output .= html_writer::end_tag('div');
        $output .= html_writer::end_div();

        /**
        * Add param userperpage so the pagination bar do proper filtering by bage
        * @author Esteban E.
        * @since October 10 of 2016
        * @rlms
        *
        */
       // $params['userperpage'] = $filter->userperpage;
        $params['search'] = $search;
        $baseurl   = new moodle_url('',$params);
        $usertable = new html_table();
        $header    = array();

        //$checkbox .= html_writer::start_tag('div', array('class'=>'form-group'));
          //$checkbox .= html_writer::start_tag('label', array("class" => "form-checkbox form-normal form-primary form-text"));
            //$checkbox .= html_writer::empty_tag('input',array("type" => "checkbox","id" => "bulk-select-all","class"=>"form-control"));
          //$checkbox .= html_writer::end_tag('label');
        //$checkbox .= html_writer::end_tag('div');
    
        $checkbox = widget::checkbox('', false, 'bulk-select-all');

        /**
        * Add email field
        * @author Carlos Alcaraz
        * @since Apr 6/2018
        */
      
        if(isset($_GET["order"]) && $_GET['order'] == 'ASC'){
          $icon = html_writer::tag('i','', array('class' => 'men men-icon-phsortingup'));
        }elseif((isset($_GET["order"]) && $_GET['order'] == 'DESC')){
          $icon = html_writer::tag('i','', array('class' => 'men men-icon-phsortingdown'));
        }else{
          $icon = html_writer::tag('i','', array('class' => 'men men-icon-phsorting'));
        }
      
        $usertable->head = array(
          $checkbox,
          html_writer::tag('a',get_string('firstname') .$icon, array('href' => $firstnameurl, 'class' => 'sorting')),
          html_writer::tag('a',get_string('lastname') .$icon, array('href' => $lastnameurl, 'class' => 'sorting')),
          get_string('email', 'local_people'),
          get_string('courseenrolled', 'local_people'),
          get_string('coursecompleted', 'local_people'),
          html_writer::tag('a',get_string('lastaccess', 'local_people') .$icon, array('href' => $lastaccessurl, 'class' => 'sorting')),
          get_string('status'),
          get_string('edit', 'local_people')
        );

        $usertable->attributes['class'] = 'table card-box table-people';
        $authconfir = get_config('auth/email');

        foreach ($users as $user) {

          unset($buttons);

          $stredit         = get_string('edit');
          $strdelete       = get_string('delete');
          $strdeletecheck  = get_string('deletecheck');
          $strshowallusers = get_string('showallusers');
          $strsuspend      = get_string('suspenduser', 'admin');
          $strunsuspend    = get_string('unsuspenduser', 'admin');
          $strunlock       = get_string('unlockaccount', 'admin');
          $strconfirm      = get_string('confirm');
          $strconfirminfo  = get_string('info_confirm_user', 'local_people');
          $strsendemail    = get_string('send_email_confirmation_again', 'local_people');          
          //////////////////////////// BUTTON DELETE //////////////////////////////

          /*if (has_capability('moodle/user:delete', $sitecontext)) {
              if (is_mnet_remote_user($user) or $user->id == $USER->id or is_siteadmin($user)) {
              } else {
                $strdelete = get_string('delete');
                $buttons[] = html_writer::link(new moodle_url($returnurl, array('delete' => $user->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => $strdelete, 'class' => 'iconsmall')), array('title' => $strdelete));
              }
            }*/
          
          //////////////////////////// BUTTON DELETE:END //////////////////////////////

          /**
          * Hide icons login and suspend for the role Site Administrator.
          * @author Alejandro G.
          * @since Oct 13 of 2017
          * @rlms
          */

          /**
          * Hide icons login and suspend for the role Site Administrator.
          * @author Alejandro G.
          * @since Oct 13 of 2017
          * @rlms
          */
          if(!$user->suspended){
            //////////////////////////// BUTTON LOGINAS //////////////////////////////
            $url = '/course/loginas.php';
            $strlogina = '';
            $strloginas = get_string('loginas', 'local_people');

            if(!is_siteadmin($user) && $USER->id <> $user->id)
            {
              $urlloginas =  $CFG->wwwroot.$url.'?sesskey='.sesskey().'&id=1&user='.$user->id ;

              $loginasbtn = html_writer::start_tag('a', array('href'=>$urlloginas,'title'=>$strlogina, 'class' => 'dropdown-item'));
                $loginasbtn .= $strloginas;
              $loginasbtn .= html_writer::end_tag('a');

              $buttons[] = $loginasbtn ;
            }else{

              if(has_capability('moodle/user:loginas',$sitecontext) && $USER->id <> $user->id && !is_siteadmin($user))
              {
                $urlloginas =  $CFG->wwwroot.$url.'?sesskey='.sesskey().'&id=1&user='.$user->id ;
                $loginasbtn = html_writer::start_tag('a', array('href'=>$urlloginas,'title'=>$strloginas, 'class' => 'dropdown-item'));
                  $loginasbtn .= $strloginas;
                $loginasbtn .= html_writer::end_tag('a');
                $buttons[] = $loginasbtn ;
              }
            }    
            
            //////////////////////////// BUTTON LOGINAS:END //////////////////////////////
            //////////////////////////// BUTTON UPDATE //////////////////////////////
            if (has_capability('moodle/user:update', $sitecontext)) {
              // prevent editing of admins by non-admins
              if (is_siteadmin($USER) or !is_siteadmin($user)) {

                $urledit = $CFG->wwwroot.'/user/editadvanced.php?id='.$user->id;
                $text = get_string('edit','local_people');
                $editbtn = html_writer::start_tag('a', array('href'=>$urledit,'title'=>$stredit, 'class' => 'dropdown-item'));
                  $editbtn .= $text;
                $editbtn .= html_writer::end_tag('a');
                $buttons[] = $editbtn ;
              }
            }
            //////////////////////////// BUTTON UPDATE:END //////////////////////////////
          }       

          //////////////////////////// BUTTON SUSPEND - UNSUSPEND //////////////////////////////

          /**
          * Add icons to suspend and
          * unsuspend user accounts
          * @author Esteban E.
          * @since September 23 of 2016
          * @rlms
          */

          if (has_capability('moodle/user:update', $sitecontext)) {
            // Esteban E. Add suspend icon or unsuspend
            if (!is_siteadmin($user) ) {
              $stringSUS = '';
              if($user->suspended){
                $stringSUS = $strunsuspend;
                $classSUS = 'fa-eye-slash';
                $actionSUS = 'unsuspend';
              }else{
                $stringSUS = $strsuspend;
                $classSUS = 'fa-eye';
                $actionSUS = 'suspend';
              }

              if($USER->id <> $user->id) {
                $urlSUS = $CFG->wwwroot.'/local/people/index.php?sort=name&dir=ASC&page'.$page.'&'.$actionSUS."=".$user->id.'&sesskey='.sesskey() ;
                $suspendbtn = html_writer::start_tag('a', array('href'=>$urlSUS,'title'=>$stringSUS, 'class' => 'dropdown-item'));
                  $suspendbtn .= $stringSUS;
                $suspendbtn .= html_writer::end_tag('a');

                $buttons[] = $suspendbtn ;
              } 
            }
          } 
          //////////////////////////// BUTTON SUSPEND - UNSUSPEND //////////////////////////////

          //////////////////////////// BUTTON CONFIRM  //////////////////////////////

          /**
          * Create confirm button
          * @author Yesid V.
          * @since Junio 24, 2017
          * @edit Daniel Carmona
          * @rlms
          *
          */
          /**
          * If user is not confirmed and user has confirmed the email we show the button
          * @author Daniel Carmona
          * @since 20-03-2018
          * @rlms
          */
        
          if ($user->confirmed == 0) {
            if (has_capability('moodle/user:update', $sitecontext)) {
              if(isset($authconfir->admin_confirmation) && $authconfir->admin_confirmation){
                $data_confirm = $DB->get_record('auth_email_confirm',['userid' => $user->id]);
                if($data_confirm && $data_confirm->email_confirmed){
                  $confirmurl = $CFG->wwwroot.'/local/people/index.php?confirmuser='.$user->id.'&sesskey='.sesskey() ;
                  $confirmbtn = html_writer::start_tag('a', array('href'=>$confirmurl,'title'=>$strconfirm));
                    //$confirmbtn .= html_writer::tag('i','', array( 'alt' => $strconfirm,'class' => 'iconsmall fa fa-check-circle'));
                    $confirmbtn .= $strconfirm;

                  $confirmbtn .= html_writer::end_tag('a');                    
                  $buttons[]  = $confirmbtn;
                }else{
                  $confirmbtn = html_writer::start_tag('a', array('href'=>"#",'title'=>$strconfirminfo));
                    $confirmbtn .= html_writer::tag('i','', array( 'alt' => $strconfirminfo,'class' => 'iconsmall men men-icon-helpdesk'));
                  $confirmbtn .= html_writer::end_tag('a');                    
                  $buttons[]  = $confirmbtn;

                  $confirmurl = $CFG->wwwroot.'/local/people/index.php?sendconfirmuser='.$user->id.'&sesskey='.sesskey() ;
                  $confirmbtn = html_writer::start_tag('a', array('href'=>$confirmurl,'title'=>$strsendemail));
                    $confirmbtn .= html_writer::tag('i','', array( 'alt' => $strsendemail,'class' => 'iconsmall wid wid-icon-email'));
                  $confirmbtn .= html_writer::end_tag('a');                    
                  $buttons[]  = $confirmbtn;
                }
              }else{
                $confirmurl = $CFG->wwwroot.'/local/people/index.php?confirmuser='.$user->id.'&sesskey='.sesskey() ;
                $confirmbtn = html_writer::start_tag('a', array('href'=>$confirmurl,'title'=>$strconfirm));
                  //$confirmbtn .= html_writer::tag('i','', array( 'alt' => $strconfirm,'class' => 'iconsmall fa fa-check-circle'));
                  $confirmbtn .= $strconfirm;
                $confirmbtn .= html_writer::end_tag('a');                    
                $buttons[]  = $confirmbtn; 
              }
            } else {
              $confirmbtn = html_writer::start_tag('a',array('class'=>'dimmed_text'));
                $confirmbtn .= html_writer::tag('i','', array('class' => 'iconsmall fa fa-check-circle'));
              $confirmbtn .= html_writer::end_tag('a');                     
              $buttons[]  = $confirmbtn;
            }
          }

         

          //////////////////////////// BUTTON CONFIRM  //////////////////////////////

          $courses = count_user_courses($user->id);

          if ($user->lastaccess) {
            $strlastaccess = format_time(time() - $user->lastaccess);
          } else {
            $strlastaccess = get_string('never');
          }

          $checkbox  = widget::checkbox('', false, '','id[]',false,array('value' => $user->id));

          $icon = html_writer::start_tag('div', array('class' => 'dropdown options-table'));
            
            $icon .= html_writer::start_tag('a', array('class' => 'btn dropdown-toggle', 'href' => '#', 'data-toggle' => 'dropdown'));
              $icon .=html_writer::tag('i','', array('class' => 'wid wid-dots fa fa-chevron-circle-down'));
            $icon .= html_writer::end_tag('a');

            $icon .= html_writer::start_tag('div', array('class' => 'dropdown-menu'));
            
              foreach ($buttons as $button) {
                $icon .= $button;
              }

            $icon .= html_writer::end_tag('div');

          $icon .= html_writer::end_tag('div');
          $suspended = $user->suspended ? get_string('suspended') : get_string('active');
        
          /**
          * Add email field
          * @author Carlos Alcaraz
          * @since Apr 6/2018
          */
          $row = array(
            $checkbox,
            $user->firstname,
            $user->lastname,
            $user->email,
            $courses['enroll'],
            $courses['complete'],
            $strlastaccess,
            $suspended,
            $icon,
          );

          //////////////////////////// SUSPENDED USER SPAN //////////////////////////////

          if ($user->suspended) {
            foreach ($row as $k => $v) {
              $row[$k] = html_writer::tag('span', $v, array('class' => 'usersuspended'));
            }
          }

          //////////////////////////// SUSPENDED USER SPAN:END //////////////////////////////

          if (has_capability('moodle/user:update', $sitecontext)) {

            $editurl = new moodle_url($CFG->wwwroot . '/user/profile.php', array('id' => $user->id));
            foreach ($row as $k => $v) {
              if($k > 0 && $k < 3 ){
                $row[$k] = html_writer::tag('a', $v, array('class' => 'td-click','href' => $editurl));
              }
            }

          }

          $usertable->data[] = $row;
        }

        $action = optional_param('action', NULL, PARAM_ALPHA);

        if($action == 'enroll'){

          $success = get_string('success_message','local_people',$message);

          $output .= html_writer::div($success , 'alert alert-success');
        }
    
        if(empty($usertable->data)){
          /**
          * Create invite button when string searched is an email
          * @author Carlos Alcaraz
          * @since Mar 28/2018
          */
          $lc_invite_button = ( preg_match("/^[0-9a-z._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i",$search) ) ? "<button class='btn btn-round btn-primary' id='btn_invite_user' >".get_string('invite', 'local_people')."</button>" : "";
          //$tableContent = html_writer::tag('p', get_string('users_not_found', 'local_people').$lc_invite_button , array('class' => 'text-center alert-mtlms'));
          $tableContent = html_writer::start_tag('div', array('class' => 'alert alert-warning text-center'));
            $tableContent .= html_writer::tag('span',get_string('users_not_found', 'local_people'));
            $tableContent .= $lc_invite_button;
          $tableContent .= html_writer::end_tag('div');
        } else {
          $tableContent = html_writer::table($usertable);
        }
        
        $output .= html_writer::start_div('content-form');
          $output .= html_writer::start_tag('form', array( 'id' => 'form-bulk-actions'));
                
            $output .= html_writer::start_div('row');
              /*Bulk Actions*/
              $output .= html_writer::start_div('col-sm-12 bulk-actions');
                $output .= html_writer::start_div('content-actions');
                  
                  $buttontxt = get_string('new_user','local_people');
                  $output .= html_writer::tag('a', $buttontxt , array('class' => 'btn btn-primary btn-round btn-labeled fa-2x loadiframe create-user-top', 'href' => $CFG->wwwroot.'/user/editadvanced.php?id=-1'));

                  $buttontxt = get_string('enrol_users','local_people');
                  $output .= html_writer::tag('a', $buttontxt , array('id' => 'enrol_user' ,'class' => 'bulk-action btn btn-primary btn-round btn-labeled fa-2x loadiframe', 'href' => '#'));

                  $buttontxt = get_string('bulk_add_cohort', 'local_people');
                  $output .= html_writer::tag('a', $buttontxt , array('data-action' => 'cohortadd', 'data-showmodal' => '1', 'class' => 'btn-bulk-action bulk-action btn btn-primary btn-round btn-labeled fa-2x loadiframe', 'href' => '#'));
                            
                  $buttontxt = get_string('bulk_send_message', 'local_people');
                  $output .= html_writer::tag('a', $buttontxt , array('data-action' => 'message', 'data-showmodal' => '1','class' => 'btn-bulk-action bulk-action btn btn-primary btn-round btn-labeled loadiframe', 'href' => '#'));

                  $uploadURL = $CFG->wwwroot.'/'.$CFG->admin.'/tool/uploaduser/index.php';
                  $buttontxt = get_string('bulk_upload', 'local_people');
                  $output .= html_writer::tag('a', $buttontxt , array('class' => 'btn btn-primary btn-round bulk-action btn-labeled loadiframe', 'href' => $uploadURL));

                  $buttontxt = get_string('bulk_download', 'local_people');
                  $output .= html_writer::tag('a', $buttontxt , array('data-action' => 'download', 'data-showmodal' => '0', 'class' => 'btn-bulk-action bulk-action btn btn-primary btn-round btn-labeled fa-2x loadiframe', 'href' => '#'));

                  
                  //if(has_capability('local/people:suspenduser',$sitecontext)){
                    $buttontxt = get_string('suspend', 'local_people');
                    $output .= html_writer::tag('a', $buttontxt , array('data-action' => 'suspend', 'data-showmodal' => '0','class' => 'btn-bulk-suspend_unsuspend bulk-action btn btn-primary btn-round btn-labeled loadiframe', 'href' => '#'));
                 // }
   
                  $buttontxt = get_string('enable', 'local_people');
                  $output .= html_writer::tag('a', $buttontxt , array('class' => 'btn-bulk-suspend_unsuspend bulk-action btn btn-primary btn-round btn-labeled loadiframe', 'href' => '#', 'data-action' => 'unlock','data-showmodal' => '0'));

                  if(has_capability('moodle/user:delete',$sitecontext)){
                    $buttontxt = get_string('bulk_delete', 'local_people');
                    $output .= html_writer::tag('a', $buttontxt , array('data-action' => 'delete','data-showmodal' => '0', 'class' => 'btn-bulk-action bulk-action btn btn-primary btn-round btn-labeled loadiframe', 'href' => '#'));
                  }
                     
                  /*if (is_siteadmin()) {
                    $report_url = new moodle_url($CFG->wwwroot . '/theme/rlmslmsfull/policies_report.php');
                    $output .= html_writer::link($report_url, html_writer::tag('i', '', ['class' => 'fa fa-line-chart']) . get_string('policies_report', 'theme_remuilmsfull'), array('class' => 'btn btn-primary btn-sm btn-labeled policies-report'));
                  }*/
                  
                  /**
                   * Changing the unsuspend string by enabled in the people module.
                   * @author Alejandro G.
                   * @since Jul 11 of 2017
                   * @rlms
                  */
                  //$output .= html_writer::link('#', html_writer::tag('i', '', ['class' => 'icon-btn fa fa-eye fa-2x ']).get_string('enable','local_people'), array('data-action' => 'unlock','class' => 'btn-bulk-suspend_unsuspend bulk-action btn btn-default btn-labeled loadiframe'));
                $output .= html_writer::end_div();
              $output .= html_writer::end_div(); 
            $output .= html_writer::end_div();
                
            $output .= html_writer::start_tag('div', array('class' => 'content-table table-responsive-sm'));    
              $output .= $tableContent;
            $output .= html_writer::end_tag('div');
          $output .= html_writer::end_tag('form');

          $output .= html_writer::end_tag('form');
        $output .= html_writer::end_div();
        
        /**
        * Add dropdown list to select records per page by default 20
        * @author Esteban E.
        * @since October 10 of 2016
        * @rlms
        */
        
        $output .= html_writer::start_tag('div', array('class'=>'col-sm-12 pagination-content'));
        //isset($_SESSION['userperpage']) 
          if( $_SESSION['userperpage'] < $result->count ){
            $output .= html_writer::start_tag('form',array('method'=>'POST', 'class' => 'recordsperpage'));

              $output .= html_writer::start_tag('div', array('class' => 'd-inline-block record-page'));
                $output .= html_writer::tag('span',get_string('recordsperpage','local_people'), array());
              $output .= html_writer::end_tag('div');

              $output .= html_writer::start_tag('div', array('class' => 'd-inline-block'));
                $output .= html_writer::start_tag('select',array('type'=>'text','id'=>'id_userperpage','name'=>'userperpage','class'=>'form-control','style'=>'width:70px;'));
                  $vals = array(10,20,30,40,50,60,70,80,90,100);
                  foreach ($vals  as $key) {
                    $selectedperpage = '';
                    if($_SESSION['userperpage'] == $key ) $selectedperpage = 'selected' ;
                    $output .= html_writer::tag('option',$key, array($selectedperpage=>$selectedperpage));
                  }
                $output .= html_writer::end_tag('select');
              $output .= html_writer::end_tag('div');
            $output .= html_writer::end_tag('form');
          }
          $output .= $OUTPUT->paging_bar($usercount, $filter->page, $filter->userperpage, $baseurl);
        $output .= html_writer::end_tag('div');
        
        if(!empty($_SESSION['userperpage'])) {
          $filter->userperpage = $_SESSION['userperpage'] ;
        }else{
          $filter->userperpage = 20;
        }

        if($result->count){
          $output .= html_writer::div(get_string('totalrecords', 'local_people', $result->count),'total-records footer-total-records col-sm-2 pl-0');
        }

      $output .= html_writer::end_div();
    $output .= html_writer::end_div();
  $output .= html_writer::end_div();
  return $output;
}

/*
 * @about   This is 1'st filter block
 * @auther  Alok.kumar_rlms
 * @params  string $firstnameop
 * @params  string $firstnamevalue
 * @return  This method return the first name filter block
 */

function get_first_name_filter($firstnameop='contain', $firstnamevalue='') {

  $output = '';
  $output .= html_writer::start_div('panel panel-grey panel-bordered');
    $output .= html_writer::start_div('panel-heading panel-grey');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse1', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('firstname', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if($firstnamevalue) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'firstname', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');

    $output .= html_writer::end_div();
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse1'));
      $output .= html_writer::start_div('panel-body');
        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('rcol-md-12 searchbox');
            $output .= html_writer::start_tag('form', array('id'=>'form-firstname', 'method' => 'post', 'action' => '', 'class'=>'bv-form'));

              $output .= html_writer::start_div('form-group');
                $output .= html_writer::start_div('input-group custom-search-form');
                  $output .= html_writer::start_tag('input', array('id' => 'firstname', 'name' => 'firstname', 'type' => 'text', 'class' => 'form-control input-lg ', 'placeholder' => get_string('Search...', 'local_people'),'value'=>$firstnamevalue)); // input-lg
                  $output .= html_writer::end_tag('input');
                $output .= html_writer::end_div();
              $output .= html_writer::end_div();

              $output .= html_writer::start_tag('button', array("class" => "btn btn-success btn-labeled fa fa-search loadiframe", "value" => "firstname", "id" => "textfilter", "name" => "textfilter", "type" => "submit"));
                $output .= get_string('search', 'local_people');
              $output .= html_writer::end_tag('button');
            $output .= html_writer::end_tag('form');

          $output .= html_writer::end_div();
        $output .= html_writer::end_div();
      $output .= html_writer::end_div();  //Panel body closes here
    $output .= html_writer::end_div();  //
  $output .= html_writer::end_div();   //
  return $output;
}

/*
 * @about   This is 2'nd filter block for last name filter
 * @auther  Alok.kumar_rlms
 * @params  string $firstnameop
 * @params  string $firstnamevalue
 * @return  This method return the first name filter block
 */

function get_last_name_filter($lastnameop = 'contain', $lastnamevalue = '') {

  $output = '';
  $output .= html_writer::start_div('panel panel-grey panel-bordered');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse2', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('lastname', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if($lastnamevalue) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'lastname', 'class' => 'reset'));
        }
      $output .= html_writer::end_tag('h5');

    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse2'));
      $output .= html_writer::start_div('panel-body');
        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('rcol-md-12 searchbox');
            $output .= html_writer::start_tag('form', array('id'=>'form-lastname','method' => 'post', 'action' => ''));

              $output .= html_writer::start_div('form-group');
                $output .= html_writer::start_div('input-group custom-search-form');
                  $output .= html_writer::start_tag('input', array('required'=>'required', 'id' => 'lastname', 'name' => 'lastname', 'type' => 'text', 'class' => 'form-control input-lg ', 'placeholder' => get_string('Search...', 'local_people'),'value'=>$lastnamevalue)); // input-lg
                  $output .= html_writer::end_tag('input');
                $output .= html_writer::end_div();
              $output .= html_writer::end_div();

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'textfilter', 'value' => 'lastname'));
            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();  //Panel body closes here
    $output .= html_writer::end_div();  //
  $output .= html_writer::end_div();  //
  return $output;
}

function get_courses_filter($lastnameop = 'contain', $lastnamevalue = '') {

  $options = array('contain' => 'Contains',
    'doesnotcontain' => 'doesn\'t contain',
    'isequalto' => 'is equal to',
    'startwith' => 'start with',
    'endwith' => 'End with',
    'isempty' => 'is empty'
  );

  $output = '';
  $output .= html_writer::start_div('panel panel-grey panel-bordered');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));
        $output .= html_writer::link('#collapse2', 'Last Name', array('data-toggle' => 'collapse', 'data-parent' => '#accordion'));

        if($lastnamevalue) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'course', 'class' => 'reset'));
        }
      $output .= html_writer::end_tag('h5');

    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse2'));
      $output .= html_writer::start_div('panel-body');
        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('rcol-md-12 searchbox');
            $output .= html_writer::start_tag('form', array('method' => 'post', 'action' => ''));

              $output .= html_writer::select($options, 'lastnameop', $lastnameop);
                
              $output .= html_writer::start_div('input-group custom-search-form');
                $output .= html_writer::start_tag('input', array('id' => 'lastname', 'name' => 'lastname', 'type' => 'text', 'class' => 'form-control input-lg ', 'placeholder' => get_string('Search...', 'local_people'),'value'=>$lastnamevalue)); // input-lg
                $output .= html_writer::end_tag('input');
              $output .= html_writer::end_div();

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();  //Panel body closes here
    $output .= html_writer::end_div();  //
  $output .= html_writer::end_div();  //
  return $output;
}

//city
function get_city_filter($cityop = 'contain') {

  global $DB;

  $options = $DB->get_records_sql("SELECT city FROM {user} GROUP BY city");

  foreach($options as $option){
    $cities[$option->city] = $option->city;
  }

  $cities = array_filter($cities);

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse9', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('city', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if($cityop) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'city', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');

    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse9'));
      $output .= html_writer::start_div('panel-body');
        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('col-md-12 searchbox select-option');
            $output .= html_writer::start_tag('form', array('id' => 'form-city','method' => 'post', 'action' => ''));

              if($cityop) {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= html_writer::start_div('form-group');
                $output .= html_writer::select($cities, 'cityop[]', $cityop, null, array('required'=>'required', 'class'=>'form-control chosen-people', 'multiple'=>'multiple' ));
              $output .= html_writer::end_div();

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'multipleselectfilter', 'value' => 'city'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();
      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//country
function get_country_filter($countryop) {

  $options = get_string_manager()->get_list_of_countries();

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));
        $output .= html_writer::start_tag('a', array('href' => '#collapse10', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('country', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if($countryop) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'country', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse10'));
      $output .= html_writer::start_div('panel-body');
        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('col-md-12 searchbox select-option');
            $output .= html_writer::start_tag('form', array('id'=>'form-country','method' => 'post', 'action' => ''));

              if($countryop) {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= html_writer::start_div('form-group');
                $output .= html_writer::select($options, 'countryop[]', $countryop, null, array('class'=>'form-control chosen-people', 'multiple'=>'multiple' ));
              $output .= html_writer::end_div();

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'multipleselectfilter', 'value' => 'country'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//fullname
function get_userfullname_filter($lastnameop = 'contain', $userfullnamevalue = '') {

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse7', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('userfullname', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if($userfullnamevalue) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'userfullname', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse7'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('searchbox select-option');
            $output .= html_writer::start_tag('form', array('id'=>'form-userfullname','method' => 'post', 'action' => ''));

              if($userfullnamevalue) {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= html_writer::start_div('form-group');
                $output .= html_writer::start_div('input-group custom-search-form');
                  $output .= html_writer::start_tag('input', array('required'=>'required', 'id' => 'userfullname', 'name' => 'userfullname', 'type' => 'text', 'class' => 'form-control input-lg ', 'placeholder' => get_string('Search...', 'local_people'),'value'=>$userfullnamevalue)); // input-lg
                  $output .= html_writer::end_tag('input');
                $output .= html_writer::end_div();
              $output .= html_writer::end_div();

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'textfilter', 'value' => 'userfullname'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}


//email
function get_email_filter($emailop = 'contain', $emailvalue = '') {

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse8', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('email', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if($emailvalue) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'email', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse8'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('searchbox select-option');
            $output .= html_writer::start_tag('form', array('id'=>'form-email','method' => 'post', 'action' => ''));

              if($emailvalue) {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= html_writer::start_div('form-group');
                $output .= html_writer::start_div('input-group custom-search-form');
                  $output .= html_writer::start_tag('input', array('id' => 'email', 'name' => 'email', 'type' => 'text', 'class' => 'form-control input-lg ', 'placeholder' => get_string('Search...', 'local_people'),'value'=>$emailvalue)); // input-lg
                  $output .= html_writer::end_tag('input');
                $output .= html_writer::end_div();
              $output .= html_writer::end_div();

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'textfilter', 'value' => 'email'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//usser name
function get_username_filter($usernameop = 'contain', $usernamevalue = '') {

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse22', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('username', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if($usernamevalue) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'username', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse22'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('searchbox select-option');
            $output .= html_writer::start_tag('form', array('id'=>'form-username','method' => 'post', 'action' => ''));

              if($usernamevalue) {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= html_writer::start_div('form-group');
                $output .= html_writer::start_div('input-group custom-search-form');
                  $output .= html_writer::start_tag('input', array('id' => 'username', 'name' => 'username', 'type' => 'text', 'class' => 'form-control input-lg ', 'placeholder' => get_string('Search...', 'local_people'),'value'=>$usernamevalue)); // input-lg
                  $output .= html_writer::end_tag('input');
                $output .= html_writer::end_div();
              $output .= html_writer::end_div();

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'textfilter', 'value' => 'username'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//suspend
function get_suspended_filter($suspendedop = 'manual') {
  global $SESSION;

  $options = array('1'=>get_string('selectyes', 'local_people'),'0'=>get_string('selectno', 'local_people'));
  $output  = '';

  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse12', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('suspended', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if(!is_null($suspendedop)) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'suspended', 'class' => 'reset'));
        }elseif(isset ($SESSION->multifilter['unsuspended'])){
            $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'unsuspended', 'class' => 'reset'));
        }else{
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse12'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row mt-2');
          $output .= html_writer::start_div('col-md-12 searchbox');
            $output .= html_writer::start_tag('form', array('id'=>'form-suspended', 'method' => 'post', 'action' => ''));
   
              if(!is_null($suspendedop)) {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

             if( isset($SESSION->multifilter['unsuspended']) ){
                $SESSION->multifilter['unsuspended']->field = 'suspended';
                $SESSION->multifilter['unsuspended']->value = 0;
              }

              $checked1 = false;
              $checked2 = false;
              if( isset($SESSION->multifilter['unsuspended']) && $SESSION->multifilter['unsuspended']->value == 0 ){
                $checked1 = true;
              }elseif( isset($SESSION->multifilter['suspended']) && $SESSION->multifilter['suspended']->value == 1 ){
                $checked2 = true;
              }
      
              $input = widget::radio(get_string('no'), $checked1,'', 'unsuspended', false, array('value' => 0));        
              $input2 = widget::radio(get_string('yes'), $checked2,'', 'suspended', false, array('value' => 1));    

              $output .= html_writer::start_tag('div', array('class' => 'pad-ver'));
                $output .= html_writer::start_tag('form', array('form-block'));
                  $output .= $input2;
                  $output .= $input;
                $output .= html_writer::end_tag('form');
              $output .= html_writer::end_tag('div');    

              $output .= html_writer::start_div(' text-center mar-rgt');
              $output .= html_writer::end_div();

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'selectfilter', 'value' => 'suspended'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

        $output .= html_writer::end_div();   //Panel body closes here
      $output .= html_writer::end_div();   //
    $output .= html_writer::end_div();   //
  return $output;
}


//confirmed
function get_confirmed_filter($confirmedop = 'yes') {
  global $SESSION;

  $options = array('1'=>get_string('selectyes', 'local_people'),'0'=>get_string('selectno', 'local_people'));

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse11', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('confirmed', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');
    
        if(!is_null($confirmedop)) {

            $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'confirmed', 'class' => 'reset'));
        }elseif(isset($SESSION->multifilter['unconfirmed'])){
            $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'unconfirmed', 'class' => 'reset'));
        }else{
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse11'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row mt-2');
          $output .= html_writer::start_div('col-sm-12 searchbox');
            $output .= html_writer::start_tag('form', array('id'=>'form-confirmed', 'method' => 'post', 'action' => ''));
    
              if(isset($SESSION->multifilter['unconfirmed'])){
                $SESSION->multifilter['unconfirmed']->field = 'confirmed';
                $SESSION->multifilter['unconfirmed']->value = 0;
              }

              $checked1 = false;
              $checked2 = false;

              if( isset($SESSION->multifilter['unconfirmed']) && $SESSION->multifilter['unconfirmed']->value == 0 ){
                $checked1 = true;
              }elseif(isset($SESSION->multifilter['confirmed']) && $SESSION->multifilter['confirmed']->value == 1){
                $checked2 = true;
              }else{
                $checked2 = false;
                $checked1 = false;
              }
              
              $input2 = widget::radio(get_string('no'), $checked1,'unconfirmed', 'unconfirmed', false, array('value' => 0));        
              $input = widget::radio(get_string('yes'), $checked2,'confirmed', 'confirmed', false, array('value' => 1));    
              
              $output .= html_writer::start_tag('div', array('class' => 'pad-ver'));
                $output .= html_writer::start_tag('form', array('form-block'));
                  $output .= $input;     
                  $output .= $input2;
                $output .= html_writer::end_tag('form');
              $output .= html_writer::end_tag('div');
    
              //$output .= html_writer::select($options, 'confirmedop', $confirmedop, array( '' => get_string('anyvalue', 'local_people')), array('required'=>'required'));
              $output .= html_writer::start_div(' text-center mar-rgt');
              $output .= html_writer::end_div();

              //$output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'selectfilter', 'value' => 'confirmed'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//sistem role
function get_systemrole_filter($system) {

  $options =  enroll_display_roles(CONTEXT_SYSTEM);

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse13', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('systemrole', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if($system) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'confirmed', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse13'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('searchbox select-option');
            $output .= html_writer::start_tag('form', array('id' => 'form-systemrole', 'method' => 'post', 'action' => ''));

              $output .= \theme_remui\widget::select($options, '', '', (string)$system, get_string('anyvalue', 'local_people'), 'systemroleop');
              //$output .= html_writer::select($options, 'systemroleop', $system, array( '' => get_string('anyvalue', 'local_people')), array('class' => 'anyvalue') );
              $output .= html_writer::start_div(' text-center mar-rgt');
              $output .= html_writer::end_div();

              if($system) {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'systemrole', 'value' => 'systemrole'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//course
function get_courserole_filter($courselist,  $role) {

  $options = enroll_display_roles(CONTEXT_COURSE);

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse14', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('courserole', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');


        if($courselist) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'confirmed', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse14'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('col-md-12 searchbox select-option');
            $output .= html_writer::start_tag('form', array('id' => 'form-courserole','method' => 'post', 'action' => ''));

              $courses  = get_courses();
              $selector = array();

              foreach($courses as $key => $course){
                $selector[$course->id] = $course->fullname;
              }           

              $output .= html_writer::start_div('form-group col-sm-12 select-some');
                $output .= html_writer::select($selector, 'courserolename[]', $courselist, null, array('class'=>'form-control chosen-people', 'multiple'=>'multiple' ));    
              $output .= html_writer::end_div();

              $output .= html_writer::start_div('form-group col-sm-12');
                $output .= \theme_remui\widget::select($options, '', '', (string)$role, get_string('anyvalue', 'local_people'), 'courseroleop');
              $output .= html_writer::end_div();

              $output .= html_writer::start_div('text-center mar-rgt');
              $output .= html_writer::end_div();

              if($courselist) {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= html_writer::start_div('form-group');
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'courserole', 'value' => 'courserole', 'class' => 'form-control'));
              $output .= html_writer::end_div();

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//first accessd
function get_firstaccessd_filter($firstaccessdgt = '', $firstaccessdlt = '', $checked = '') {
  global $SESSION;

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse15', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('firstaccessd', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        /**
        * Validate selection
        * @author Carlos Alcaraz
        * @since Apr 11/2018
        */
        if ( isset($SESSION->multifilter) && is_array($SESSION->multifilter) && isset( $SESSION->multifilter['firstaccessd'] ) && isset( $SESSION->multifilter['firstaccessd']->edited ) && $SESSION->multifilter['firstaccessd']->edited == "on" ) { $checked = 'on'; 
        }

        if($firstaccessdgt || $firstaccessdlt || $checked == 'on') {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'firstaccessd', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse15'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('searchbox select-option');
            $output .= html_writer::start_tag('form', array('method' => 'post', 'action' => '', 'id' => 'form-firstaccessd'));

              if($firstaccessdgt || $firstaccessdlt || $checked == 'on') {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }
              //Datepicker's start here!
                $output .= html_writer::start_tag('div', array('class' => 'row date-time'));
                $output .= widget::datepicker(get_string('dateisafter', 'local_people'),
                (int)$firstaccessdgt,
                'firstaccessdgt','firstaccessdgt',
                false,
                true,
                false,
                ['name_2' => 'firstaccessdlt', 'id_2' => 'firstaccessdlt','value_2' => $firstaccessdlt]);
              $output .= html_writer::end_tag('div');

              $checked = ($checked == 'on') ? true : false;

              $output .= html_writer::start_tag('div', array('class'=>'form-group col-sm-12'));
                $output .= widget::checkbox(get_string('neveraccess', 'local_people'), $checked, '', 'neveraccess' );
              $output .= html_writer::end_tag('div');
    
              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'datefilter', 'value' => 'firstaccessd'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//last accssed
function get_lastaccessed_filter($lastaccessedgt = '', $lastaccessedlt = '', $checked = '') {
    global $SESSION;

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse18', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('lastaccessed', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        /**
        * Validate selection
        * @author Carlos Alcaraz
        * @since Apr 11/2018
        */
        if ( isset($SESSION->multifilter) && is_array($SESSION->multifilter) && isset( $SESSION->multifilter['lastaccessed'] ) && isset( $SESSION->multifilter['lastaccessed']->edited ) && $SESSION->multifilter['lastaccessed']->edited == "on" ) { $checked = 'on'; }

        if($lastaccessedgt || $lastaccessedlt || $checked == 'on') {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'lastaccessed', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse18'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('searchbox select-option');
            $output .= html_writer::start_tag('form', array('id' => 'form-lastaccessed','method' => 'post', 'action' => ''));

              if($lastaccessedgt || $lastaccessedlt || $checked == 'on') {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= html_writer::start_tag('div', array('class' => 'row date-time'));
                $output .= widget::datepicker(get_string('dateisafter', 'local_people'),
                 (int)$lastaccessedgt,
                'lastaccessedgt',
                'lastaccessedgt',
                false,
                true,
                false,
                ['name_2' => 'lastaccessedlt', 'id_2' => 'lastaccessedlt','value_2' => $lastaccessedlt]);
              $output .= html_writer::end_tag('div');

              $checked = ($checked == 'on') ? true : false;

              $output .= html_writer::start_tag('div');
                $output .= widget::checkbox(get_string('neveraccess', 'local_people'), $checked, '', 'neveraccess' );
              $output .= html_writer::end_tag('div');

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'datefilter', 'value' => 'lastaccessed'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//last modified
function get_lastmodified_filter($lastmodifiedgt = '', $lastmodifiedlt = '', $checked = '') {
  global $SESSION;

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse20', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('lastmodified', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        /**
        * Validate selection
        * @author Carlos Alcaraz
        * @since Apr 11/2018
        */
        if ( isset($SESSION->multifilter) && is_array($SESSION->multifilter) && isset( $SESSION->multifilter['lastmodified'] ) && isset( $SESSION->multifilter['lastmodified']->edited ) && $SESSION->multifilter['lastmodified']->edited == "on" ) { $checked = 'on'; }
        
        if($lastmodifiedgt || $lastmodifiedlt || $checked == 'on') {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'lastmodified', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse20'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('searchbox select-option');
            $output .= html_writer::start_tag('form', array('id' => 'form-lastmodified','method' => 'post', 'action' => ''));

              if($lastmodifiedgt || $lastmodifiedlt || $checked == 'on') {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= html_writer::start_tag('div', array('class' => 'row date-time'));
                $output .= widget::datepicker(get_string('dateisafter', 'local_people'),
                (int)$lastmodifiedgt,
                'lastmodifiedgt',
                'lastmodifiedgt',
                false,
                true,
                false,
                ['name_2' => 'lastmodifiedlt', 'id_2' => 'lastmodifiedlt','value_2' => $lastmodifiedlt]);
              $output .= html_writer::end_tag('div');
    
              $checked = ($checked == 'on') ? true : false;

              $output .= html_writer::start_tag('div');
                $output .= widget::checkbox(get_string('nevermodified', 'local_people'), $checked, '','nevermodified');
              $output .= html_writer::end_tag('div');

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'datefilter', 'value' => 'lastmodified'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

//authentication
function get_auth_filter($authenticationop = 'manual') {

  $enableauths = get_enabled_auth_plugins();
  $options = array();

  foreach($enableauths  as $enableauth){
    $options[$enableauth] = $enableauth;
  }

  $output = '';
  $output .= html_writer::start_div('filterslist');
    $output .= html_writer::start_div('panel-heading');
      $output .= html_writer::start_tag('h5', array('class' => 'panel-title'));

        $output .= html_writer::start_tag('a', array('href' => '#collapse23', 'data-toggle' => 'collapse', 'data-parent' => '#accordion', 'class'=>'plms-reports-dropdown collapsed'));
          $output .= get_string('auth', 'local_people');
          $output .= html_writer::start_tag('i', array("class" => "reports-dropdown fa fa-angle-down collapsed"));
          $output .= html_writer::end_tag('i');
        $output .= html_writer::end_tag('a');

        if($authenticationop) {
          $output .= html_writer::link('#', get_string('clear', 'local_people'),array('data-name' => 'auth', 'class' => 'reset'));
        }

      $output .= html_writer::end_tag('h5');
    $output .= html_writer::end_div();
    
    $output .= html_writer::start_div('panel-collapse collapse ', array('id' => 'collapse23'));
      $output .= html_writer::start_div('panel-body');

        $output .= html_writer::start_div('row');
          $output .= html_writer::start_div('searchbox select-option');
            $output .= html_writer::start_tag('form', array('id'=>'form-auth', 'method' => 'post', 'action' => ''));

              if($authenticationop) {
                $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'class' => 'filter-true', 'name' => 'filter-true', 'value' => 'true'));
              }

              $output .= \theme_remui\widget::select($options, '', '', (string)$authenticationop, get_string('anyvalue', 'local_people'), 'authop');
              //$output .= html_writer::select($options, 'authop', $authenticationop, array('' => get_string('anyvalue', 'local_people')), array('required'=>'required', 'class' => 'anyvalue'));
              $output .= html_writer::start_div(' text-center mar-rgt');
              $output .= html_writer::end_div();

              $output .= html_writer::empty_tag('input',array('type' => 'hidden', 'name' => 'selectfilter', 'value' => 'auth'));

            $output .= html_writer::end_tag('form');
          $output .= html_writer::end_div();
        $output .= html_writer::end_div();

      $output .= html_writer::end_div();   //Panel body closes here
    $output .= html_writer::end_div();   //
  $output .= html_writer::end_div();   //
  return $output;
}

function validate_name($name){

  $array = array (
    'firstname',
    'lastname',
    'userfullname',
    'email',
    'city',
    'country',
    'confirmed',
    'suspended',
    'unsuspended',
    'systemrole',
    'courserole',
    'firstaccess',
    'lastaccess',
    'neveraccess',
    'lastmodified',
    'timemodified',
    'nevermodified',
    'username',
    'auth'
  );

  if(in_array($name,$array)){
    return $name;
  } else {
    return false;
  }
}

function get_user_list($filter, $multifilter) {

  global $DB;

  $countcondition = array();
  $result   = new stdClass();
  $countsql = 'SELECT count(*),id,firstname,email,lastname,lastaccess,suspended FROM {user} WHERE id > 1 AND deleted <> 1';
  $sql      = 'SELECT id,firstname,email,lastname,lastaccess,suspended,confirmed FROM {user} WHERE id > 1 AND deleted <> 1'; 
  
  $params = array();

  /**
  * Create array with filter types and associate key to compare with selected filters.
  * @author Yesid Valencia
  * @since Apr 10/2018
  */

  $arrayTypes = array(
    "textfilter"=>"value",
    "multipleselectfilter"=>"op",
    "courserole"=>"op",
    "systemrole"=>"op",
    "selectfilter"=>"op",
    "datefilter"=>"gt",
    "datefilter"=>"lt",
    "datefilterr"=>"sm"
  );
                       
  foreach ($multifilter as $ffilter) {
     
    /**
    * Change validation to compare current filter with type of filter array so we can cover all type of filters
    * @author Yesid Valencia
    * @since Apr 10/2018
    */
     
    if (  trim($ffilter->{$arrayTypes[$ffilter->type]}) != "" && str_replace('\'','',$ffilter->{$arrayTypes[$ffilter->type]}) != "" && $ffilter->field != "filter-true" ) {
          
      if(isset($ffilter->field)) {
        $sql      .= " AND ";
        $countsql .= " AND ";
      }

      /**
      * Redefine datefilter neveraccess
      * @author Carlos Alcaraz
      * @since Apr 11/2018
      */
      if($ffilter->type == 'datefilterr' ) { 
        $ffilter->type = 'datefilter'; 
      }

      if (isset($ffilter->field) && $ffilter->type=='textfilter') { 
        $value = trim($ffilter->value);

        if ( $ffilter->field == 'userfullname' ) {  
          $field = 'CONCAT(firstname," ", lastname)';
          $sql .= "$field LIKE CONCAT('%', :userfullname, '%')";
          $countsql .= "$field LIKE CONCAT('%', :userfullname, '%')";
          $params['userfullname'] = $value;
        } else {
          $field = validate_name($ffilter->field);
          $sql .= "$field LIKE CONCAT('%', :$field, '%')";
          $countsql .= "$field LIKE CONCAT('%', :$field, '%')";
          $params[$field]  = $value;
        }

      }else if(isset($ffilter->field) && isset($ffilter->op) && $ffilter->type=='selectfilter') {
        $field = validate_name($ffilter->field);

        if($ffilter->field !=='auth') {
          $op = ($ffilter->op) ? " = 1" : " = 0";
          $sql .= "$field{$op}";
          $countsql .= "$field{$op}";
        }else {
          $sql .= "$field = :selection";
          $countsql .= "$field = :selection";
          $params['selection'] = $ffilter->op;
        }
      }

      if(isset($ffilter->field) && $ffilter->type=='datefilter') {

        if($ffilter->field == 'firstaccessd') {
          $field = 'firstaccess';
        }

        if($ffilter->field == 'lastaccessed') {
          $field = 'lastaccess';
        }

        if($ffilter->field == 'lastmodified') {
          $field = 'timemodified';
        }

        $field = validate_name($field);

        if ( $ffilter->edited == 'on' || $ffilter->access == 'on' ) {
          $sql .= " $field = 0";
          $countsql .= " $field = 0";

        }else {

          if(!is_null($ffilter->gt)) {
            $params["greater{$field}"] = $ffilter->gt;
            $sql .= " {$field} > UNIX_TIMESTAMP(STR_TO_DATE(:greater$field,'%m/%d/%Y'))";
            $countsql .= " {$field} > UNIX_TIMESTAMP(STR_TO_DATE(:greater$field,'%m/%d/%Y'))";
          }

          $sql = (!is_null($ffilter->lt)) ? $sql      .= ' AND ' : $sql;
          $countsql = (!is_null($ffilter->lt)) ? $countsql .= ' AND ' : $sql;

          if(!is_null($ffilter->lt)) {

            $params["less{$field}"] = $ffilter->lt;
            $sql .= " {$field} < UNIX_TIMESTAMP(DATE_ADD(STR_TO_DATE(:less$field,'%m/%d/%Y'),INTERVAL 1 DAY) )";
            $countsql .= " {$field} < UNIX_TIMESTAMP(DATE_ADD(STR_TO_DATE(:less$field,'%m/%d/%Y'),INTERVAL 1 DAY) )";
          }
        }
      }

      $invalues = array();

      if(isset($ffilter->field) && isset($ffilter->op) && $ffilter->type=='multipleselectfilter') {
          
        $field = validate_name($ffilter->field);

        foreach ($ffilter->ar as $key => $value) {
          $invalues[] = ":in$key$field";
          $params['in'.$key.$field]  = $value;
        }

        $invalues = implode(', ', $invalues);
        $sql .= " $field IN({$invalues})";
        $countsql .= " $field IN({$invalues})";
      }

      if(isset($ffilter->field) && $ffilter->type=='systemrole') {
        $sql .= " id IN (SELECT userid FROM mdl_role_assignments a WHERE a.contextid=1 AND a.roleid= :roleid )";
        $countsql .= " id IN (SELECT userid FROM mdl_role_assignments a WHERE a.contextid=1 AND a.roleid= :roleid )";
        $params['roleid'] = $ffilter->op;
      }
          
      if(isset($ffilter->field) && $ffilter->type=='courserole' && !empty($ffilter->ar)) {
        $field = validate_name($ffilter->field);
          
        foreach ($ffilter->ar as $key => $value) {
          $invalues[] = ":in$key$field";
          $params['in'.$key.$field]  = $value;
        }

        $invalues = implode(', ', $invalues);

        $query = ($ffilter->role) ? "AND a.roleid = :courserole" : '';

        $query = " id IN (SELECT userid FROM mdl_role_assignments a INNER JOIN mdl_context b ON a.contextid=b.id INNER JOIN mdl_course c ON b.instanceid=c.id WHERE b.contextlevel=50 $query AND c.id IN ($invalues))";

        if($ffilter->role) {
          $params['courserole'] = $ffilter->role;
        }

        $sql .= $query;
        $countsql .= $query;
      }        
    }
  }
    
  $search = optional_param('search', null, PARAM_TEXT);

  if($search) {
    $params['search'] = $search;
    $fullname = 'CONCAT(firstname," ", lastname)';
    $sql .= " AND CONCAT(firstname,email,lastname,username,".$fullname.") LIKE CONCAT('%', :search, '%')";
    $countsql .= " AND CONCAT(firstname,email,lastname,username,".$fullname.") LIKE CONCAT('%', :search, '%')";
  }
  
  $count  = $DB->count_records_sql($countsql, $params);

  $result->count = $count;

  if (isset($filter->sort) && $filter->sort) {
    $sql .= " ORDER BY $filter->sort $filter->order";
  } else {
    $sql .= " ORDER BY firstname ASC";
  }


  /**
  * added validations and new structure to prevent the pagination to display wrong  
  * @author Jorge M.
  * @since July 24 of 2017
  * @rlms
  */

  $information = $DB->get_records_sql($sql, $params);
  if($information == null) {
    $sql = "";
    $sql  = 'SELECT id,firstname,lastname,email,lastaccess,suspended FROM {user} WHERE id > 1 AND deleted <> 1';
    $sql .= " ORDER BY firstname ASC";
    $filter->page = 0;
    $sql .= " LIMIT ".$filter->page*$filter->userperpage. ",". $filter->userperpage;
    $data = $DB->get_records_sql($sql, $params);
  }else{
    $data = $information;
  }


    $result->data = $data;
  
  return $result;
}

function get_clear_filter() {

  return false;

  global $CFG;
  $output = '';
  $output .= html_writer::start_div('text-left');
    $output .= html_writer::link($CFG->wwwroot.'/local/people/index.php', 'Clear Filter', array('class' => 'btn btn-success btn-labeled loadiframe'));
  $output .= html_writer::end_div();

  return $output;
}

function enroll_display_roles($param) {
      
  $roles   = array();
  $options = array();

  $rolesContext = get_roles_for_contextlevels($param);

  foreach ($rolesContext as $roleContext) {
    $role    = enroll_get_role_name($roleContext);
    $roles[] = $role;
  }

  foreach ($roles as $role) {
    $role->name = role_get_name($role);
    $options[$role->id] = $role->name;
  }

  return $options;
}

/**
 * Returns an array with the name and id of the roles
 * @author Sergio A.
 * @since  Sep 13 of 2018
 * @rlms
 * 
*/

function enroll_display_roles_bulk() {

  $roles = array();
  $rolesContext = get_roles_for_contextlevels(CONTEXT_COURSE);

  foreach ($rolesContext as $roleContext) {
    $role = enroll_get_role_name($roleContext);
    $role->name = role_get_name($role);
    $roles[$role->id] = $role->name;
  }
  return $roles;
}

/**
 * Returns an array with the name and id of the courses
 * @author Sergio A.
 * @since  Sep 13 of 2018
 * @rlms
 * 
*/

function get_courses_display() {
  
  $courses = get_courses();
  $list = array();
  foreach ($courses as $key => $course) {
    if($course->id ==1)continue;
    $list[$course->id] = $course->fullname;   
  }
  return $list;     
}

function enroll_get_role_name($roleid) {

  global $DB;
  $sql = 'SELECT  * FROM {role} WHERE id= ' . $roleid;

  return $DB->get_record_sql($sql);
}

function count_user_courses($id) {

  $courses  = 0;
  $complete = 0;

  if($student_course_arry = enrol_get_users_courses($id, true, null, 'visible DESC,sortorder ASC')) {

    foreach($student_course_arry as $value) {
      $course = new core_course_list_element($value);
      $info   = new completion_info($course);

      if($info->is_course_complete($id)){
        $complete++;
      }

      $courses++;
    }
  }

  return array( 'enroll' => $courses, 'complete' => $complete);
}

function bulk_action($actionName, $arrayValues) {

  global $CFG, $SESSION;
  $SESSION->bulk_users = array();

  foreach ($arrayValues as $key => $value) {
    $SESSION->bulk_users[$value] = $value;
  }
    
  $url = $CFG->wwwroot."/local/people/actions/user_bulk_$actionName.php";
  redirect($url);
}

/**
 * Get all the cohorts defined anywhere in system.
 *
 * The function assumes that user capability to view/manage cohorts on system level
 * has already been verified. This function only checks if such capabilities have been
 * revoked in child (categories) contexts.
 *
 * @param string $search search string
 * @return array    Array(totalcohorts => int, cohorts => array, allcohorts => int)
 */
function cohort_get_all_cohorts_people() {
  global $DB, $CFG;
  require($CFG->dirroot.'/cohort/lib.php');
    
  $fields = "SELECT c.*, ".context_helper::get_preload_record_columns_sql('ctx');
  $countfields = "SELECT COUNT(*)";
  $sql = " FROM {cohort} c
  JOIN {context} ctx ON ctx.id = c.contextid ";
  $params = array();
  $wheresql = '';

  if ($excludedcontexts = cohort_get_invisible_contexts()) {
    list($excludedsql, $excludedparams) = $DB->get_in_or_equal($excludedcontexts, SQL_PARAMS_NAMED, 'excl', false);
    $wheresql = ' WHERE c.contextid '.$excludedsql;
    $params = array_merge($params, $excludedparams);
  }

  $totalcohorts = $allcohorts = $DB->count_records_sql($countfields . $sql . $wheresql, $params);

  $order = " ORDER BY c.name ASC, c.idnumber ASC";
  $cohorts = $DB->get_records_sql($fields . $sql . $wheresql . $order, $params);

  // Preload used contexts, they will be used to check view/manage/assign capabilities and display categories names.
  foreach (array_keys($cohorts) as $key) {
    context_helper::preload_from_record($cohorts[$key]);
  }

  return array('totalcohorts' => $totalcohorts, 'cohorts' => $cohorts, 'allcohorts' => $allcohorts);
}

/**
 * Ajax function to get the form of the cohort list
 *
 * @author	Daniel C
 * @since	13-07-2018
 * @param1	array $users List of user ids
 * @return	array Full form and data to be printed
 * @rlms
 */
function get_cohortadd_form(array $users): array{
  global $DB;
    
  // Iterate users to know if they exist
  foreach ($users as $key => $userid) {
    $record = $DB->get_record('user',['id' => $userid]);
    if(!$record){
      unset($users[$key]);
    }
  }
    
  $q = true;
  $output = '';
  $message = '';
  $cohorts = cohort_get_all_cohorts_people();
  if (empty($users)) {
    $q = false;
    $message = get_string('invalid_users','local_people');
  }elseif($cohorts['totalcohorts'] <= 0){
    $q = false;
    $message = get_string('invalid_cohorts','local_people');
  }else{
    $output .= html_writer::start_tag('form',['method' => 'POST','action' => '#', 'onsubmit' => 'return false;','id' => 'frmcohortsadd']);
      // Input of the users to be added to a specific cohort
      $output .= html_writer::tag('input', '',['type' => 'hidden','name' => 'users','value' => implode(',', $users)]);
      // Cohort list
      $cohortlist = array();
      foreach ($cohorts['cohorts'] as $key => $value) {
        $cohortlist[$key] = $value->name;
      }
      $output .= \theme_remui\widget::select2(get_string('cohortlist','local_people'), $cohortlist, 'cohortlist', '-1', 'cohortlist', true);
    $output .= html_writer::end_tag('form');
  }
  return ['form' => $output, 'message' => $message ,'q' => $q, 'title' => get_string('bulk_add_cohort', 'local_people')];
}

/**
 * function to get the form to send a message
 *
 * @author	Daniel C
 * @since	13-07-2018
 * @param1	array $users List of user ids
 * @return	string Full form to be printed
 * @rlms
 */
function get_message_form( array $users ): array{
  global $DB;
    
  // Iterate users to know if they exist
  foreach ($users as $key => $userid) {
    $record = $DB->get_record('user',['id' => $userid]);
    if(!$record){
      unset($users[$key]);
    }
  }
    
  $q = true;
  $output = '';
  $message = '';
  if (empty($users)) {
    $q = false;
    $message = get_string('invalid_users','local_people');
  }
  return ['form' => '', 'message' => $message ,'q' => $q, 'title' => get_string('bulk_send_message', 'local_people')];
}

/**
 * function to get the form to send a message
 *
 * @author	Daniel C
 * @since	13-07-2018
 * @param1	array $users List of user ids
 * @return	string Full form to be printed
 * @rlms
 */
function get_message_form_html(): user_message_form{
  global $CFG, $DB;
  require_once($CFG->libdir.'/adminlib.php');
  require_once($CFG->dirroot.'/message/lib.php');
  require_once('classes/user_message_form.php');

  $msg = optional_param('msg', '', PARAM_CLEANHTML);
  $confirm = optional_param('confirm', 0, PARAM_BOOL);

  require_login();
  admin_externalpage_setup('userbulk');
  require_capability('moodle/site:readallmessages', context_system::instance());

  if (empty($CFG->messaging)) {
    print_error('messagingdisable', 'error');
  }
    
  //TODO: add support for large number of users

  if ($confirm and ! empty($msg) and confirm_sesskey()) {
    foreach ($users as $user) {
      //TODO we should probably support all text formats here or only FORMAT_MOODLE
      //For now bulk messaging is still using the html editor and its supplying html
      //so we have to use html format for it to be displayed correctly
      message_post_message($USER, $user, $msg, FORMAT_HTML);
    }
  }

  $msgform = new user_message_form('user_bulk_message.php',null,'post','',['class' => 'hidden']);
  $q = true;
  if ($msgform->is_cancelled()) {
    redirect($return);
  } else if ($formdata = $msgform->get_data()) {
    $options = new stdClass();
    $options->para = false;
    $options->newlines = true;
    $options->smiley = false;
        
    $msg = format_text($formdata->messagebody['text'], $formdata->messagebody['format'], $options);

    list($in, $params) = $DB->get_in_or_equal($SESSION->bulk_users);
    $userlist = $DB->get_records_select_menu('user', "id $in", $params, 'fullname', 'id,' . $DB->sql_fullname() . ' AS fullname');
    $usernames = implode(', ', $userlist);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('confirmation', 'admin'));
    echo $OUTPUT->box($msg, 'boxwidthnarrow boxaligncenter generalbox', 'preview'); //TODO: clean once we start using proper text formats here

    $formcontinue = new single_button(new moodle_url('user_bulk_message.php', array('confirm' => 1, 'msg' => $msg)), get_string('yes')); //TODO: clean once we start using proper text formats here
    $formcancel = new single_button(new moodle_url($SESSION->return), get_string('no'), 'get');
    echo $OUTPUT->confirm(get_string('confirmmessage', 'bulkusers', $usernames), $formcontinue, $formcancel);
    echo $OUTPUT->footer();
    die;
  }
  return $msgform;
}
/**
 * function to save the users in the cohort selected
 *
 * @author	Daniel C
 * @since	13-07-2018
 * @param1	array $users List of user ids
 * @return	string Full form to be printed
 * @rlms
 */
function save_cohortadd(array $data):array {
  global $DB, $CFG;
  require($CFG->dirroot.'/cohort/lib.php');
  $response = array('error' => true,'message' => get_string('error_cohortadd','local_people'));
  $cohortid = (int)$data['input_value'];
  if(!empty($data['users']) && $cohort = $DB->get_record('cohort', array('id'=>$cohortid), '*', MUST_EXIST)){
    $response['error'] = false;
    $response['message'] = get_string('success_cohortadd','local_people');
    foreach ($data['users'] as $uid) {
      if($user = $DB->get_record('user',['id' => $uid])){
        cohort_add_member($cohort->id, $user->id);
      }
    }
  }
  return $response;
}

/**
 * function to save|send the form to send a message
 *
 * @author	Daniel C
 * @since	13-07-2018
 * @param1	array $users List of user ids
 * @return	string Full form to be printed
 * @rlms
 */
function save_message(array $data):array {
  global $DB, $CFG, $USER;
  require_once($CFG->dirroot.'/message/lib.php');
  require_once('classes/user_message_form.php');
    
  $response = array('error' => true,'message' => get_string('error_message','local_people'));
  $message = $data['input_value'];
  if(!empty($message)){
    $response['error'] = false;
    $response['message'] = get_string('success_message_users','local_people');
    foreach ($data['users'] as $uid) {
      if($user = $DB->get_record('user',['id' => $uid])){
        //TODO we should probably support all text formats here or only FORMAT_MOODLE
        //For now bulk messaging is still using the html editor and its supplying html
        //so we have to use html format for it to be displayed correctly
        message_post_message($USER, $user, $message, FORMAT_HTML);
      }
    }
  }
  return array();
}
