/**
    * #809 Custom UI for system notifications
    * @author Yesid V
    * @since  07 April 2017
    * @rlms
    */

$(document).ready(function(){
     
//    $(".panel-heading").click(function(){
//    
//        if($(this).hasClass("current")){
//            
//            $(".pane").not($(this).next()).slideUp();
//            $(".panel-heading").not($(this)).css("color","black");
//            $(this).css("color","white");
//            
//        }else{
//            
//            $(this).css("color","black");
//            
//        }
//        
//    });    
        
//    var id_form = $("#edit_template_system_notifications form").attr("id");
//    var action = $("#edit_template_system_notifications form").attr("action");  
//    var icons = ["jat jat-classint","icon icon-addquiz","jat jat-notstarted","jat jat-notcompleted","icon icon-addquiz","jat jat-pathnot","jat jat-trackenrollment","jat jat-coursedescription","jat jat-reminder","mult mult-bulkcourses"];
//    
//    $("#edit_template_system_notifications").append('<form action="'+action+'" autocomplete="off" method="post" accept-charset="utf-8" id="custom_form"></form>');    
//    
//    $("#edit_template_system_notifications .fgrouplabel label").each(function(idx,val){
//       
//        $("#custom_form").append('<fieldset style="width:95%; margin-bottom: -10px;" class="clearfix collapsible collapsed" id="edit_template_system_notifications_header_'+idx+'">\n\
//                                        <legend style="position: relative" class="ftoggler well css_a lerning_path" id="legend_lerninh_path_'+idx+'">\n\
//                                            <a href="#" class="fheader" role="button" aria-expanded="false" id="label_'+idx+'">\n\
//                                            <div class="div_icon">\n\
//                                                <i class="jat_custom '+icons[idx]+'"></i>\n\
//                                            </div></a>\n\
//                                        </legend>\n\
//                                        <div style="display:none" class="fcontainer clearfix pane_custom" id="div_legend_lerninh_path_'+idx+'"></div>\n\
//                                  </fieldset>');
//              
//        var sub_check = $(this).parent().parent().parent().attr("id");        
//        var id_textarea = $(this).parent().parent().parent().next(".form_element").attr("id");
//        var id_text = $(this).parent().parent().parent().next().next(".form_element").attr("id");
//        
//        $("#"+sub_check).after().appendTo("#div_legend_lerninh_path_"+idx);
//        $("#"+id_textarea).after().appendTo("#div_legend_lerninh_path_"+idx); 
//        $("#"+id_text).after().appendTo("#div_legend_lerninh_path_"+idx);        
//        $(this).after().appendTo("#label_"+idx);   
//        
//        
//    });
    
//    $("#custom_form").append('<fieldset style="width:95%" id="fieldsed_btn"></fieldset>');
//    
//    $("#edit_template_system_notifications #fgroup_id_buttonar").after().appendTo("#fieldsed_btn");   
//    $("#id_notifications").css("display","none");
//        
//    var divInputs = $("#"+id_form).find("div").html();
//    $("#custom_form").append("<div style='display:none'>"+divInputs+"</div>");
//    
//    $("#"+id_form).remove();
//    
    $(".lerning_path").on("click",function(){
        
       var color = $(".current").css("background-color"); 
       var id = $(this).attr("id");
       
       if($("#div_"+id).is(":visible")){          
           
           $(this).css("background-color",""); 
           $(this).removeClass("arrowDown");           
           $("#div_"+id).css("display","none");                      
           
       }else{
          
          $(this).css("background-color",color);  
          $(this).addClass("arrowDown");
          $("#div_"+id).css("display","block");  
          
          $(".pane_custom").not($(this).next()).css("display","none");
          $(".lerning_path").not($(this)).css("background-color",""); 
          $(".lerning_path").not($(this)).removeClass("arrowDown"); 
          
       }
        
    });      
    
    $("#add-event-rule").append("<span class='custom_font'> Add New Rule</span>");
    
    $("#add-event-rule").after().appendTo(".text-left");
    
    $(".text-left").css("width","84%");
    $(".event-rule").css("width","80%");   
    
    
    
//        var courseid = get('id');
//        if(courseid === '0' || courseid === '' ){
//           $("#menucourse-selection").change(function(){
//                menucourse($("#menucourse-selection"));
//            });
//        }
//        else
//        {   
//            menucourse($("#menucourse-selection"));
//            $('#ft').click();
//        }
//        
//        $("#menucourse-selection").change(function(){
//                menucourse($(this));
//        });
    
//    $( ".mform fieldset.collapsible").each(function(idx,val) {
//        var parent = $(this).parent().parent().attr('id');
//        if(parent != 'edit_template_course_notifications'){           
//               //FieldsetInForm.push('1');        
//        }else{
//
//           $(".ftoggler").addClass("well");  
//           $(this).removeClass("clearfix"); 
//
//        }
//
//    });
    
//    $(".fheader").on("click",function(){
//        var parent = $(this).parent().parent().parent().parent().attr('id');
//        var $class = $(this).closest(".collapsible").hasClass("collapsed");
//        if(parent == "edit_template_course_notifications"){
//            if($class == false){                
//                //Open
//                var color = $(".current").css("background-color");
//                $(this).parent().css("background-color",color);
//                $(this).parent().addClass("arrowDown");
//            } else  {
//                $(this).parent().removeClass("arrowDown");
//                $(this).parent().css("background-color", "");
//            }    
//        }      
//
//    });
    
//    var iconsC = ["jat jat-completion","jat jat-completion","jat jat-teacher","jat jat-enrrollstu","wid wid-icon-myprogress","wid wid-icon-upcomingevents","wid wid-icon-notification","jat jat-coursex","jat jat-reminder"];
//    $("#edit_template_course_notifications .well").each(function(idx, val){
//        $(this).addClass("full_click");
//        $(this).append('<div class="div_icon_course">\n\
//            <i class="jat_custom '+iconsC[idx]+'"></i>\n\
//        </div>');
//    });

    //Submit a new event
    $('#new-event-popup #id_submitbutton').click(function(event){
        var required_fields = ['#id_plugin', '#new-event-popup input[name="name"]', '#id_eventname', '#id_frequency', '#id_minutes', 'id_template'];

        $(required_fields).each(function(index, value) {
            if ($(value).val() === '') {
                $.niftyNoty({
                    type: 'plms-danger',
                    message: M.util.get_string('empty_fields_required', 'local_system_notifications'),
                    container: 'floating',
                    timer: 116000
                });
                event.preventDefault();
                return false;
            }
        })
    })    
    
});