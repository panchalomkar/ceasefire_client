define(['jquery', 'theme_remui/select2'], function ($, Select2) {
    /**
     * Add function from the url that help to render the url to the course selected
     * @author Hugo S.
     * @url //https://gist.github.com/excalq/2961415
     * @since May 29 of 2018
     * @ticket 1626
     * @rlms
     */
    var updateQueryStringParam = function (key, value) {
        var baseUrl = [location.protocol, '//', location.host, location.pathname].join(''),
                urlQueryString = document.location.search,
                newParam = key + '=' + value,
                params = '?' + newParam;

        // If the "search" string exists, then build params from it
        if (urlQueryString) {
            keyRegex = new RegExp('([\?&])' + key + '[^&]*');

            // If param exists already, update it
            if (urlQueryString.match(keyRegex) !== null) {
                params = urlQueryString.replace(keyRegex, "$1" + newParam);
            } else { // Otherwise, add it to end of query string
                params = urlQueryString + '&' + newParam;
            }
        }
        window.history.replaceState({}, "", baseUrl + params);
    };

    //Function Get
    function get(name) {
        var regexS = "[\\?&]" + name + "=([^&#]*)";
        var regex = new RegExp(regexS);
        var tmpURL = window.location.href;
        var results = regex.exec(tmpURL);
        if (results == null)
            return"";
        else
            return results[1];
    }


//Function menucourse-selection
    function menucourse($items) {
        var course_id = $items.val();
        ajaxUrl = M.cfg.wwwroot + '/blocks/rlms_notifications/ajax/course_notification_form.php';
        data = {
            id: course_id
        };

        $.ajax({
            method: 'POST',
            url: ajaxUrl,
            data: data,
            dataType: 'json'
        }).done(function (data) {
            $("#edit_template_course_notifications form").css("display", "block");
            $(data).each(function (index, value) {
                if (value.enabled != 0) {
                    if (!$('#id_enabled_' + value.name).is(':checked')) {
                        $('#id_enabled_' + value.name).click();
                    }
                } else {
                    if ($('#id_enabled_' + value.name).is(':checked')) {
                        $('#id_enabled_' + value.name).click();
                    }
                }
                $('#id_template_' + value.name).val(value.template);
                $('#id_template_' + value.name + '_ifr').contents().find('body').html(value.template);
            })
            $('#edit_template_course_notifications').removeClass('hide');
        });

    }

    return {
        init: function () {
            $(document).ready(function () {
                var courseid = get('id');
                
                if ((courseid !== '0' || courseid != '') && courseid > 0) {
                    menucourse($("#menucourse-selection"));
                }

                $(".mform fieldset.collapsible").each(function (idx, val) {
                    var parent = $(this).parent().parent().attr('id');
                    $(this).addClass('');
                    if (parent != 'edit_template_course_notifications') {
                        //FieldsetInForm.push('1');        
                    } else {
                        $(".ftoggler").addClass("notification_bg");
                        $(this).removeClass("clearfix");

                    }

                });

                $("#edit_template_course_notifications legend.ftoggler, #edit_template_course_notifications legend.ftoggler a").click(function () {
                    var e = $(this).closest(".collapsible");
                    var $class = e.hasClass("collapsed");
                    $("#edit_template_course_notifications legend.ftoggler").parent().addClass('collapsed');
                    if ($class == true) {
                        e.removeClass("collapsed");
                    }

                });
                
                $('#menucourse-selection').change(function () {
                    var courseid = $('#menucourse-selection').val();
                    $("#cid").val(courseid);
                    /**
                     * Use the function updateQueryStringParam to render the url with the course selected and then redirect.
                     * @author Hugo S.
                     * @since May 29 of 2018
                     * @ticket 1626
                     * @rlms
                     */
                    updateQueryStringParam('id', courseid);
                    window.location.href = location.href;
                });


            });
        }
    };
});

