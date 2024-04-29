define(['jquery', 'core/str', 'jqueryui','local_learningpaths/bootbox'], function($, str, ju, bootbox) {
	return {
        lpactions: function() {
            var translation = str.get_strings([
                            
                {key: 'confirm_delete', component: 'local_learningpaths'},
                {key: 'users_delete_success', component: 'local_learningpaths'},
                {key: 'cohorts_delete_success', component: 'local_learningpaths'},
                {key: 'selectusers', component: 'local_learningpaths'},
                {key: 'delete_success', component: 'local_learningpaths'},
                {key: 'selectcohort', component: 'local_learningpaths'}
            ]);

            var refresh_courses = 1;        
            prerequisites_drag_and_drop();
            save_courses_positions();
            //add_course_to_learningpath();
            add_required_switch();
            learningpaths_pagination();
            searchers();
            removeCourseAction();

            // Select all elements of a list.
            check_all('course-all', 'course-learninpath');
            check_all('id_all_cohorts', 'learningpath-cohort');
            check_all('selec_allcohorts', 'cohort-learninpath');
            check_all('id_all_users', 'learningpath-user');
            

            if (!get_url_param('coursename')) {
                courses_drag_and_drop();
            }

            $('.learningpath-user').click(function() {
                var usersselected = new Array();
                $('.learningpath-user').each(function() {
                    if($(this)[0].checked)
                    {
                        usersselected.push( $(this)[0].dataset.userid ); 
                    }
                });
                if(usersselected.length > 0) {
                    $('#learningpath-remove-users').attr('style','background-color:#77b300;cursor:pointer;visibility:visible;display:initial;');
                    } else {
                    $('#learningpath-remove-users').attr('style','background-color:#FFFFFF');
                    $('#page-local-learningpaths-view div.select-add div.button-delete a.delete-btn').attr('style','display:none;visibility:hidden;');


                }
            });

            $('#learningpath-remove-users').click(function(event) {
                event.preventDefault();
                $('#lpstatus > div.alert').removeAttr('style').html('');
                var users = [];
                $("#lpstatus").hide();
                $('.learningpath-user:checked').each(function() {
                    users.push($(this).data('userid'));
                });

                var usersselect = M.util.get_string('selectusers', 'local_learningpaths');
                // Check if users are selected
                if( users.length <= 0 ) {
                    $("#lpstatus").show().removeClass('hidden').children().addClass('alert-warning').html(usersselect).fadeOut(5000);
                    return false;
                }
                bootbox.confirm(M.util.get_string('confirm_delete', 'local_learningpaths'), function(result) {
                    if(result) {
                        var parameters = new Object();
                        parameters.users = users;
                        parameters.action = 'remove-users';

                        learningpath_ajax_request(parameters, function(data) {
                            $.each(users, function( index, value ) {
                                $("#table_users tr#user-"+value).remove();
                                var userdelete = M.util.get_string('users_delete_success', 'local_learningpaths');
                                $("#lpstatus").show().removeClass('hidden').children().addClass('alert-danger').html(userdelete).fadeOut(5000);
                            }); 
                            setTimeout(function() { window.location.reload(true); }, 3000);
                        }, function(error) {
                            console.log(error);
                        });
                    }
                });
            });

            $('#learningpath-remove-cohorts').click(function(event) {
                $('#cohortst').hide().children().removeAttr('style').html('');
                var cohorts = [];
                $('.learningpath-cohort:checked').each(function() {
                    cohorts.push($(this).data('cohortid'));
                })

                var cohortselect = M.util.get_string('selectcohort', 'local_learningpaths');
                // Check if users are selected
                if(cohorts.length <= 0) {
                    $("#cohortst").show().removeClass('hidden').children().addClass('alert-warning').html(cohortselect).fadeOut(5000);
                    return false;
                }
                bootbox.confirm(M.util.get_string('confirm_delete', 'local_learningpaths'), function(result) {
                    if(result) {
                        var parameters = new Object();
                        parameters.cohorts = cohorts;
                        parameters.action = 'remove-cohorts';

                        learningpath_ajax_request( parameters, function( data ) {
                            $.each(cohorts, function( index, value ) {
                                $("#table_cohorts tr#cohort-"+value).remove();
                                var cohortsdelete = M.util.get_string('cohorts_delete_success', 'local_learningpaths');
                                $("#cohortst").show().removeClass('hidden').children().addClass('alert-danger').html(cohortsdelete).fadeOut(5000);
                            }); 
                            setTimeout(function() { window.location.reload(true); }, 3000);
                        }, function(error) {
                            console.log(error);
                        });
                    }
                });
            });

            $('#page-local-learningpaths-view div.check form div.fitem_fcheckbox').removeClass('col-sm-6');
            $('#page-local-learningpaths-view div.check form div.fitem_fcheckbox').addClass('col-sm-2');
            $('#page-local-learningpaths-view div.check form div.fitem_feditor').removeClass('col-sm-6');
            $('#page-local-learningpaths-view div.check form div.fitem_feditor').addClass('col-sm-12');
            $('#page-local-learningpaths-view div.check form div.fitem_ftext').removeClass('col-sm-6');
            $('#page-local-learningpaths-view div.check form div.fitem_ftext').addClass('col-sm-12');
            $('#page-local-learningpaths-view table.mceLayout').addClass('card-box');

            $('a.notifications_enroll').click(function() {
                changes_icon( '#' + $(this)[0].id );
                var actualid = $(this)[0].id;

                if(!$(this).hasClass('collapsed')) {
                    $(this).closest('div[class*="collapse_"]').removeClass('active');
                }else{
                    $(this).closest('div[class*="collapse_"]').addClass('active');
                }
                
                $('a.notifications_enroll').each(function() {
                    if (actualid != $(this)[0].id) {
                        if (!$(this).hasClass('collapsed')) {
                            $(this).addClass('collapsed');
                            $(this).parent().parent().children('.collapse-color.collapse').removeClass('in').addClass('out');
                            $(this).children('i').removeClass('wid wid-icon-up').addClass('wid wid-icon-down');
                            $(this).closest('div[class*="collapse_"]').removeClass('active');
                        }
                    }
                });

            });


            // For learningpaths block embeded.
            $('.lpd-lp-detail-body canvas').each(function() {
                var canvas = this;
                switch ($(this).data('element')) {
                    case 'first':
                        var context = canvas.getContext('2d');
                        context.beginPath();
                        context.moveTo(15, 0);
                        context.lineTo(15,30);
                        //contex.lineTo(20, 100);
                        context.lineWidth = 2;
                        //context.lineHeight = 20;
                        context.strokeStyle = $(this).parent().children('i').data('color');
                        context.stroke();
                    break;

                    case 'middle':
                        var context = canvas.getContext('2d');
                        context.beginPath();
                        context.moveTo(15, 0);
                        context.lineTo(15,150);
                        context.lineWidth = 2;
                        context.strokeStyle = $(this).parent().children('i').data('color');
                        context.stroke();
                    break;

                    case 'last':
                        var context = canvas.getContext('2d');
                        context.beginPath();
                        context.moveTo(15, 0);
                        context.lineTo(15,150);
                        context.lineWidth = 2;
                        context.strokeStyle = $(this).parent().children('i').data('color');
                        context.stroke();
                    break;
                }
            });

            // Courses tab.
            $('#courses-tab-button').click(function(event) {
                $('.tbs-content ul li a').removeClass('active').attr('aria-expanded','false');
                $('#courses-button a').addClass('active').attr('aria-expanded','true');
                $('.tab-pane').removeClass('active in');
                var target = this.href.split('#');
                $("#"+target[1]).addClass("active in");
            });
            
            $('#courses-button').click(function(event) {
                if( refresh_courses ) {
                    var parameters = new Object();
                    parameters.action = "refresh_courses";
                    parameters.learningpathid = get_url_param('id');
                    // Call ajax function.
                    learningpath_ajax_request(parameters, function(data) {
                        reload_courses_list(data);
                    }, function(error) {
                        console.log(error);
                    });
                    refresh_courses = 0;
                }
            });
            
            $('a.close').click(function() {
                $('div.contentenrollusers input.users-lpall, input.users_lpall').prop('checked', false);
            });

            $('a#add-users-lp').click(function() {
                $('div.contentenrollusers input.users-lpall, input.users_lpall').prop('checked', false);
            });
		}
	};
});