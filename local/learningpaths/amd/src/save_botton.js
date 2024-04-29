
var lang_locale='',
lang = document.getElementsByTagName('html')[0].getAttribute('lang');
if( lang != '' && lang != 'en' && lang != 'en-us'){
     lang_locale=  "local_people/" + lang;
}
define([
    "jquery",
    lang_locale,
    "local_learningpaths/bootbox",
    "local_people/bootstrap-datetimepicker"
], function($, lang_locale,bootbox, datetimepicker) {
    function delay(callback, ms) {
        var timer = 0;
        return function () {
            var context = this,
                args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                callback.apply(context, args);
            }, ms || 0);
        };
    }

    function disabledButton() {
        // check if other required fields have value, then make button active
        var a_tag_html = $('.filepicker-filename').find('a').html();
        
        if(a_tag_html !== undefined  && a_tag_html !== "" && ($("#id_description").val() != "" && $("#id_description").val() != "<p><br></p>") && $("#id_name").val() !== ""){
            $("#id_submitbutton").attr("disabled", false);
        }else{
            $("#id_submitbutton").attr("disabled", true);
        }
    }

    return {
        init: function () {
            $(document).ready(function () {
                $("#id_userperpage").change(function () {
                    $(this)
                        .parents("form:first")
                        .submit();
                });
                
                //learning path course form validation
                $('#courses-popup-content form').on('submit', function(e){
                    e.preventDefault();
                   if (!$('.course-learninpath').is(':checked')) {
                        bootbox.alert({
                            message: M.util.get_string("learningpath_required_course", "local_learningpaths")
                        });
                        return false;
                   } else {
                       this.submit();
                   }
                });
                
                //user form validation
                $('#users-popup-content form').on('submit', function(e){
                    e.preventDefault();
                   if (!$('.users-lpall').is(':checked')) {
                        bootbox.alert({
                            message: M.util.get_string("learningpath_required_user", "local_learningpaths")
                        });
                        return false;
                   } else {
                       this.submit();
                   }
                });
                
                //cohort validation
                 $('#cohorts-popup-content form').on('submit', function(e){
                    e.preventDefault();
                   if (!$('.cohort-learninpath').is(':checked')) {
                        bootbox.alert({
                            message: M.util.get_string("learningpath_required_cohorts", "local_learningpaths")
                        });
                        return false;
                   } else {
                       this.submit();
                   }
                });


                var icons = {
                    time: "fa fa-clock-o",
                    date: "fa fa-calendar",
                    up: "fa fa-chevron-up",
                    down: "fa fa-chevron-down",
                    previous: "fa fa-chevron-left",
                    next: "fa fa-chevron-right",
                    today: "fa fa-screenshot",
                    clear: "fa fa-trash",
                    close: "fa fa-remove"
                };
                $("#id_enddate").datetimepicker({
                    icons:icons,
                    format: "MM/DD/YYYY",
                    defaultDate : $("#lp_enddate").val()
                });
                $("#id_startdate").datetimepicker({
                    icons:icons,
                    format: "MM/DD/YYYY",
                    defaultDate : $("#lp_startdate").val()
                });

                $("#id_startdate").on("dp.change", function (e) {
                    $("#lp_startdate").val($("#id_startdate").val());
                });

                $("#id_enddate").on("dp.change", function (e) {
                    $("#lp_enddate").val($("#id_enddate").val());
                    if ($("#id_startdate").val() !== "") {
                        $("#id_enddate")
                            .data("DateTimePicker")
                            .minDate($("#id_startdate").val());
                    } else {
                        return false;
                    }
                });
            });
            if (document.querySelector("#page-local-learningpaths-index") !== null) {
                $("#id_submitbutton").attr("disabled", true);
                $("input[name*='learningpath_imagechoose']").on(
                    "click",
                    disabledButton
                );
                $('input[name="learningpath_image"], #id_description').on('change', disabledButton);
                $("#id_name").on('keyup', disabledButton);

                $("#id_submitbutton").on("click", validateFilePicker);
                function validateFilePicker() {
                    while (document.querySelector(".filepicker-container") !== null) {
                        $("#id_submitbutton").attr("disabled", true);
                        bootbox.alert(
                            M.util.get_string("mandatory_msg", "local_learningpaths")
                        );
                        break;
                    }
                    while (document.querySelector(".filepicker-container") == null) {
                        $("#id_submitbutton").attr("disabled", false);
                        return true;
                    }
                }
            }

            $(document).on(
                "keyup",
                ".add-users-search",
                delay(function (e) {
                    e.preventDefault();
                    var id = $("#users-popup input[name=id]").val();
                    var url = $("#users-popup ul.pagination li > a").attr("href");
                    var search = $(".add-users-search").val();

                    var urlajax = M.cfg.wwwroot + "/local/learningpaths/ajax.php";

                    $.ajax({
                        type: "GET",
                        url: urlajax,
                        data: { action: "pagination", id: id, search: search },
                        dataType: "json",
                        success: function (data) {
                            if (data.msg) {
                                $("#users-popup #users-popup-content").empty();
                                $("#users-popup-content").html(data.html);
                                $(".add-users-search").focus();
                                var tmpStr = $(".add-users-search").val();
                                $(".add-users-search").val("");
                                $(".add-users-search").val(tmpStr);
                            }
                        }
                    });
                    return false;
                }, 1000)
            );

            $(document).on("click", "#users-popup ul.pagination li > a", function (e) {
                e.preventDefault();
                var id = $("#users-popup input[name=id]").val();
                var url = $(this).attr("href");
                var urllast = url.replace("?", "#");
                urllast = urllast.replace("&", "#");
                var search = $(".add-users-search")
                    .val()
                    .toLowerCase();

                var URLSplited = urllast.split("#");
                var page = URLSplited[1].split("=");
                var urlajax = M.cfg.wwwroot + "/local/learningpaths/ajax.php";

                $.ajax({
                    type: "GET",
                    url: urlajax,
                    data: { action: "pagination", page: page[1], id: id, search: search },
                    dataType: "json",
                    success: function (data) {
                        if (data.msg) {
                            $("#users-popup #users-popup-content").empty();
                            $("#users-popup-content").html(data.html);
                        }
                    }
                });
                return false;
            });
            $(document).on("change", "#users-popup #id_userpopupperpage", function (
                e
            ) {
                var perpage = this.value;
                var id = $("#users-popup input[name=id]").val();
                var search = $(".add-users-search")
                    .val()
                    .toLowerCase();
                var urlajax = M.cfg.wwwroot + "/local/learningpaths/ajax.php";
                $.ajax({
                    type: "GET",
                    url: urlajax,
                    data: {
                        action: "pagination",
                        id: id,
                        perpage: perpage,
                        search: search
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data.msg) {
                            $("#users-popup #users-popup-content").empty();
                            $("#users-popup-content").html(data.html);
                        }
                    }
                });
                return false;
            });

            $(".course-lp .form-check-input").click(function () {
                if ($(this).is(":checked")) {
                    $(this)
                        .parent()
                        .parent()
                        .parent()
                        .addClass("checkbbg");
                } else {
                    $(this)
                        .parent()
                        .parent()
                        .parent()
                        .removeClass("checkbbg");
                }
            });
            
            $("#course-all").click(function () {
                if ($("#course-all").is(":checked")) {
                    $(".course-lp").addClass(" checkbbg");
                } else {
                    //$('.course-lp .form-check-input').removAttr('checked');
                    $(".course-lp").removeClass(" checkbbg");
                }
            });
            
            $(".course-learninpath .form-check-input").click(function () {
                if ($(this).is(":checked")) {
                    $(this)
                        .parent()
                        .parent()
                        .parent()
                        .addClass("checkbbg");
                } else {
                    $(this)
                        .parent()
                        .parent()
                        .parent()
                        .removeClass("checkbbg");
                }
            });
            
        } //end-if
    };
});


