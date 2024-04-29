$( document ).ready(function() {
    
    $.ajaxPrefilter(function( options, original_Options, jqXHR ) {
        options.async = true;
    });

    $('#block_rlms_lpd_content .lpd-pagination-li').click(function() {
        $('.lpd-lp-detail-body').addClass('hide');
        $('.lpd-pagination-li').removeClass('active');
        $('.lpd-lp-detail-body-page-'+$(this).data('page')).removeClass('hide');
        $(this).addClass('active');
        return false;
    });
    
    $('#block_rlms_lpd_content .lpd-arrow-right').click(function() {
        var parent          = $(this).parents('.lpd-pagination').first();
        var liActive        = $(parent).find('.lpd-pagination-li.active');
        var dataPage        = $(liActive).data('page');
        dataPage++;
        setPagePaginationLpd(parent, dataPage);
        return false;
    });
    
    $('#block_rlms_lpd_content .lpd-arrow-left').click(function() {
        var parent          = $(this).parents('.lpd-pagination').first();
        var liActive        = $(parent).find('.lpd-pagination-li.active');
        var dataPage        = $(liActive).data('page');
        dataPage--;
        setPagePaginationLpd(parent, dataPage);
        return false;
    });
    
    $('#block_rlms_lpd_content .lpd-arrow-left').click(function() {
        var liActive = $('.lpd-pagination').find('.lpd-pagination-li.active');
        return false;
    });
    
    $('#block_rlms_lpd_content .lpd-lp-detail-body-column-name.status-prereq-ok').click(function() {
        dataLocal               = {};
        dataLocal['action']     = $(this).data('action');
        dataLocal['classid']    = $(this).data('classid');
        var urlCourse           = M.cfg.wwwroot + '/course/view.php' + '?id=' + $(this).data('course');
        $.ajax({
            type    : "POST",
            dataType: 'json',
            data     : {m: 'changeclassstatus', data: dataLocal},
            url     : M.cfg.wwwroot + '/local/elisprogram/widgets/enrolment/ajax.php',
            async   : true
        }).done(function(response) {
            if(response.status == 'success' || response.data.error == 1 || response.data.error == 2) {
                window.location = urlCourse;
            } else {
                if(typeof (response.data.msg)  != 'undefined') {
                    alert(response.data.msg);
                } else if(typeof (response.msg) != 'undefined') {
                    alert(response.msg);
                } 
            }
        });
        return false;
    });
    
    $('i[data-toggle=tooltip]').tooltip({html: true});

});

function setPagePaginationLpd(parent, dataPage) {
    $( document ).ready(function() {
        if($(parent).find("[data-page='" + dataPage + "']").length > 0) {
            var parentContent   = $(parent).parents('.lpd-lp-content').first();
            $(parentContent).find('.lpd-lp-detail-body').addClass('hide');
            $(parent).find('.lpd-pagination-li').removeClass('active');
            $(parentContent).find('.lpd-lp-detail-body-page-'+dataPage).removeClass('hide');
            $(parent).find("[data-page='" + dataPage + "']").addClass('active');
        }
    });
}


