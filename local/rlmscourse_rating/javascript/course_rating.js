
    function highlightStar(obj,id) {
        removeHighlight(id);		
        $('.course-rating-container #course-'+id+' li').each(function(index) {
            $(this).addClass('highlight');
            if(index == $('.course-rating-container #course-'+id+' li').index(obj)) {
                return false;	
            }
        });
    }

    function removeHighlight(id) {
        $('.course-rating-container #course-'+id+' li').removeClass('selected');
        $('.course-rating-container #course-'+id+' li').removeClass('highlight');
    }

    function addRating(obj,id,user,course) {
        $('.course-rating-container #course-'+id+' li').each(function(index) {
            $(this).addClass('selected');
            $('#course-'+id+' #rating').val((index+1));
            if(index == $('.course-rating-container #course-'+id+' li').index(obj)) {
                return false;	
            }
        });
        $.ajax({
            url: M.cfg.wwwroot+"/local/rlmscourse_rating/ajax/request.php",
            data:'action="addrating"&courseid='+course+'&userid='+user+'&rating='+$('#course-'+id+' #rating').val(),
            type: "POST",
            success: function(response) {
                //if(response.status)
                var getarray = jQuery.parseJSON(response);
                if(getarray[0].status){
                    $('.course-rating-container').append("<i class='fa fa-check text-success'></i>");
                    $('.course-rating-container #course-'+id+' li').removeAttr('onclick').removeAttr('onmouseover');
                } else {
                    $('.course-rating-container').append("<i class='fa fa-times text-danger'></i>");
                }
            }
        });
    }

    function resetRating(id) {
        if($('#course-'+id+' #rating').val() == 0) {
            $('.course-rating-container #course-'+id+' li').each(function(index) {
                $(this).removeClass('highlight');
                if((index+1) == $('#course-'+id+' #rating').val()) {
                    return false;	
                }
            });
        }
    }  
    
