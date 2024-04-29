function get( name ){
        var regexS = "[\\?&]"+name+"=([^&#]*)";
        var regex = new RegExp ( regexS );
        var tmpURL = window.location.href;
        var results = regex.exec( tmpURL );
        if( results == null )
            return"";
        else
            return results[1];   
}

function URLToArray(url) {
    var request = {};
    var pairs = url.substring(url.indexOf('?') + 1).split('&');
    for (var i = 0; i < pairs.length; i++) {
        if(!pairs[i])
            continue;
        var pair = pairs[i].split('=');
        request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
     }
     return request;
}

function paginationAjax(){
        jQuery("#block_rlms_lpd_content .pagination-rlms li.page-number").each(function(){
           item = this; 
           /*If finds element*/
           aElement = jQuery(item).find("a");
           if(aElement.length > 0){
               var hrefTmp = jQuery(aElement).attr("href");
               request = URLToArray(hrefTmp);
               jQuery(aElement).attr("href","#block_rlms_lpd_content")
               jQuery(aElement).attr("data-page",request.page || 0);
               jQuery(aElement).attr("data-lp",request.lp_id || 0);
           }
        });
        jQuery("#block_rlms_lpd_content .pagination.pagination-rlms li.page-number").click(function(){
            e = $(this);
        /*Get the attrs related to the data*/
        page = jQuery(this).find("a").data("page") || 0;
        lp_id = jQuery(this).find("a").data("lp") || 0;
        if(lp_id > 0){
            console.log(page)
            /*Search the lp*/
            $.ajax({
                type    : "POST",
                dataType: 'json',
                data    : { action: 'getLpDetail', learningPath: lp_id, 'page': page, 'lpid_selected' : lp_id },
                url     : M.cfg.wwwroot + '/blocks/rlms_lpd/lib/ajax.php', 
                async   : false
            }).done(function(response) {
                
                if(response.view && response.view !== '') {
                    if($('body').attr('data-pagetype') == 'site-index' || $('body').attr('data-pagetype') == 'my-index'){
                        e.closest('.lpd-lp-detail').html(response.view);
                        e.closest('.lpd-lp-detail').css('height', 'auto');
                    }else{
                        jQuery('.lpd-lp-content').html(response.view);
                        jQuery('.lpd-lp-content').css('height', 'auto');
                    }

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
                paginationAjax();
            }); 
        }
    });  
}