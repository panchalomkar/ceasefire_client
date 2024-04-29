define(['jquery', 'jqueryui'], function($) {

    function canvascount() {
        $('.lpd-lp-detail-body canvas').each(function(){
            var canvas = this;

            switch ($(this).data('element')) {
                case 'first':

                var context = canvas.getContext('2d'); 
                context.beginPath();
                context.moveTo(15, 15);
                context.lineTo(15,30);
                context.lineWidth = 2;
                context.strokeStyle = $(this).parent().children('i').data('color');
                context.stroke();  
           
                break; 
                case 'middle':

                    var context = canvas.getContext('2d');
                    context.beginPath();
                    context.moveTo(15, 0);
                    context.lineTo(15,30);
                    context.lineWidth = 2;
                    context.strokeStyle = $(this).parent().children('i').data('color');
                    context.stroke(); 
           
                break;
                case 'last':
           
                    var context = canvas.getContext('2d');
                    context.beginPath();
                    context.moveTo(15, 0);
                    context.lineTo(15,15);
                    context.lineWidth = 2;
                    context.strokeStyle = $(this).parent().children('i').data('color');
                    context.stroke(); 
               
                break;
            }

            $('.block_rlms_lpd_content .tooltipelement_html').tooltip({html:true , placement: "right" });
        });
    }
    return {
        init: function() {
            $(document).ready(function(){

                paginationAjax();
                $.ajaxPrefilter(function( options, original_Options, jqXHR ) {
                    options.async = true;
                });
                $('#block_rlms_lpd_content .lpd-lp-content-header').click(function() {
                    
                    var lpid = $(this).attr('data-lpid');
                    var page = get("page");
                    var lpid_selected = get("lp_id");
                    var lphtmlobject = $(this) ;
                    
                    if( $(this).hasClass('collapsedlpd')) {
                        $('#block_rlms_lpd_content .lpd-lp-content-header').addClass('collapsedlpd');
                          
                        $.ajax({
                            type    : "POST",
                            dataType: 'json',
                            data    : { action: 'getLpDetail', learningPath: lpid, 'page': page, 'lpid_selected' : lpid_selected },
                            url     : M.cfg.wwwroot + '/blocks/rlms_lpd/lib/ajax.php', 
                            async   : false
                        }).done(function(response) {
                    
                            if(response.view && response.view !== '') {
                                lphtmlobject.parent().find('.lpd-lp-detail').html(response.view);
                                lphtmlobject.parent().css('height', 'auto');

                                canvascount();     
                            }
                            paginationAjax();
                        });
                
                    }else {
                        $(this).parent().find('.lpd-lp-detail').empty();
                    }

                    if( $(this).hasClass('collapsedlpd')) {
                        $(this).removeClass('collapsedlpd');
                    }else{
                        $(this).addClass('collapsedlpd');
                    }     

                });
                var lpid_selected = parseInt(get("lp_id"));
                jQuery("#block_rlms_lpd_content .lpd-lp-content").each(function(key, value){
                    e = jQuery(value).find(".lpd-lp-content-header");
                    lpid = e.attr('data-lpid');
                    if(lpid > 0 && lpid_selected == lpid){
                        jQuery(e).click();
                    }
                });
                var ccw,cch;

                $('#block_rlms_lpd_content div.lpd-lp-content-header').click( function () {
                    $('#block_rlms_lpd_content div.lpd-lp-content-header').each(function() {
                        if ($(this).hasClass('collapsedlpd')) {
                            $(this).parent().removeClass('card-box');
                            $(this).parent().find('.lpd-lp-detail').empty();
                        }
                    });

                    if($(this).hasClass('collapsedlpd')) {
                        $(this).parent().removeClass('card-box');
                    }

                    if (!$(this).hasClass('collapsedlpd')) {
                        $(this).parent().addClass('card-box');
                    }
                });

                $('div.tbs-content ul li#view-button').click(function(){
                    canvascount();  
                });



                 /*
                      @author :-Akshay Pingale
                      @ticket :-862
                 */
                ///PAGINATION
                $(document).on('click', '.pagination_lpdd ul.pagination li > a',function(e) {

                e.preventDefault();
                var url =  $(this).attr('href');
               
                var urllast =  url.split("&");
                    urllast =  urllast[2].split("=");
                   
                var userid = $('#useridloged').val();
            
                var url = M.cfg.wwwroot +"/blocks/rlms_lpd/ajax.php";
               
                $.ajax({
                        type: 'GET',
                        url: url,
                        data: {page:urllast[1]},
                        dataType: 'text',
                        success: function(data) {
                        
                         //$("#id_resultsearchusers").hide();
                         $("div.block_rlms_lpd_content").html(data);
                        }  
                    });
                    
                return false;

            
                });



            });
        }
    };
});


