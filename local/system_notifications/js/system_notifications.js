/**
 * js for tabs
 * @author Praveen
 * @since 2017-02-14
 * @rlms
 */

$(document).ready(function() {
    	$('.accordion').find('div.panel-heading').click(function() {
		$(this).next().slideToggle('600');
		$(".accordion-content").not($(this).next()).slideUp('600');
	});
	$('div.panel-heading').on('click', function() {
                
		$(this).toggleClass('current').siblings().removeClass('current');
	});
    
    //Show animations for ajax requests
    $(document).ajaxStart(function() {
        $( "#lp-loading" ).show();
    });

    $(document).ajaxStop(function() {
        $( "#lp-loading" ).hide();
    });
    //Call function to execute click event in edit training session button
    ruleEventEdit();
    ruleEventDelete();


    //Add rule
    $('#add-event-rule').click(function (e) {
        $('#name, #plugin, #eventname, #description,#frequency,#minutes,#messagetemplate,#eventrule_ruleid').attr('value', '');
        $('#new-event-popup h4').text(M.util.get_string('addnewrule', 'local_system_notifications'));
    })
    
    //Save a new or edited eule
    $('#save-event-rule').click(function(e){
        //Validate name of training session
        var name = $('#name').val();
        var plugin = $('#plugin').val();
        var eventname = $('#eventname').val();
        var frequency = $('#frequency').val();
        var minutes = $('#minutes').val();
        var messagetemplate = $('#messagetemplate').val();
        if (name.trim() == "") {
            $.niftyNoty({
                type: 'plms-danger',
                message: M.util.get_string('rule_name_required', 'local_system_notifications'),
                container: 'floating',
                timer: 6000
            });
            e.preventDefault();
            return false;
        }
        if (plugin.trim() == "") {
            $.niftyNoty({
                type: 'plms-danger',
                message: M.util.get_string('rule_areatomonitor_required', 'local_system_notifications'),
                container: 'floating',
                timer: 6000
            });
            e.preventDefault();
            return false;
        }
        if (eventname.trim() == "") {
            $.niftyNoty({
                type: 'plms-danger',
                message: M.util.get_string('rule_event_required', 'local_system_notifications'),
                container: 'floating',
                timer: 6000
            });
            e.preventDefault();
            return false;
        }
        if (frequency.trim() == "") {
            $.niftyNoty({
                type: 'plms-danger',
                message: M.util.get_string('rule_frequency_required', 'local_system_notifications'),
                container: 'floating',
                timer: 6000
            });
            e.preventDefault();
            return false;
        }
        if (minutes.trim() == "") {
            $.niftyNoty({
                type: 'plms-danger',
                message: M.util.get_string('rule_minutes_required', 'local_system_notifications'),
                container: 'floating',
                timer: 6000
            });
            e.preventDefault();
            return false;
        }
        if (messagetemplate.trim() == "") {
            $.niftyNoty({
                type: 'plms-danger',
                message: M.util.get_string('rule_message_required', 'local_system_notifications'),
                container: 'floating',
                timer: 6000
            });
            e.preventDefault();
            return false;
        }

        var ajaxUrl = "";

        var data;

        //Build data to send
        if ($('#eventrule_ruleid').val() !== "") {
            //Update is done using a custom process with only a SQL Update
            ajaxUrl = M.cfg.wwwroot + '/local/system_notifications/ajax/addRule.php';

            //Prepare data for edit
            data = {
                id: $('#eventrule_ruleid').val(),
                userid: $('#eventrule_userid').val(),
                action: 'update-event-rule',
                curid: $('#eventrule_courseid').val(),
                name: $('#name').val(),
                plugin : $('#plugin').val(),
                eventname : $('#eventname').val(),
                description : $('#description').val(),
                frequency : $('#frequency').val(),
                timewindow : $('#minutes').val(),
                template : $('#messagetemplate').val()
            }
        } else {

            ajaxUrl = M.cfg.wwwroot + '/local/system_notifications/saverule.php';

            //Prepare data for ajax request
            data = {
                
            'courseid': 0,
            'name' : $('#name').val(),
            'plugin': $('#plugin').val(),
            'eventname': $('#eventname').val(),
            'description': $('#description').val(),
            'frequency':  $('#frequency').val(),
            'timewindow': $('#minutes').val(),
            'template':$('#messagetemplate').val(),
            'userid':$('#eventrule_userid').val(),
            'submitbutton' : ""
               
            }

        }

        //Make a request to create or edit a event rule
        $.ajax({
            method: 'POST',
            url: ajaxUrl,
            data: data,
        }).done(function (data) {
            //When the request get done, then do a new request to learning path training sessions.
            $.ajax({
                url: M.cfg.wwwroot + '/local/system_notifications/ajax/addRule.php',
                data: {
                    action: 'get-event-rule',
                    curid: $('#eventrule_courseid').val(),
                },
                dataType: "json"
            }).done(function(d){
                $('#event-rule-list').html(d.html);
                $('#new-event-popup .close').click();
                ruleEventEdit();
                ruleEventDelete();

                //Add training session event
                $('#add-event-rule').off().click(function (e) {
                    $('#name, #plugin, #eventname, #description,#frequency,#minutes,#messagetemplate,#eventrule_ruleid').attr('value', '');
                    $('#new-event-popup h4').text(M.util.get_string('add_training_session', 'local_rlmslms'));
                })
            })
        })

        e.preventDefault();
        return false;
    })


/**
 * execute click event when user try to edit a rule
 * @return stop click event
 */
function ruleEventEdit () {
    //Edit rule
    $('.edit-ruleevent').click(function (e){
        var trainingId = $(this).data('id');
        $('#add-event-rule').click();

        $.ajax({
            method: 'POST',
            url: M.cfg.wwwroot + '/local/system_notifications/ajax/addRule.php',
            data: {
                action: 'edit-ruleevent',
                id: trainingId
            }
        }).done(function (data) {
            $('#new-event-popup h4').text(M.util.get_string('editrule', 'local_system_notifications'));
            var data = JSON.parse(data);
            $('#name').val(data.name);
            $('#plugin').val(data.plugin);
            $('#eventrule_ruleid').val(data.id);
            $('#eventname').val(data.eventname);
            $('#description').val(data.description);
            $('#frequency').val(data.frequency);
            $('#minutes').val(data.timewindow);
            $('#messagetemplate').val(data.template);
        })
        e.preventDefault();
        return false;
    })
}

/**
 * execute click event when user try to delete a training session
 * @return stop click event
 */
function ruleEventDelete () {
    //Edit training session
    $('.delete-eventrule').click(function (e){
        var trainingId = $(this).data('id');

        //Use bootbox to confirm delete
        bootbox.confirm(M.util.get_string('rule_delete_ts', 'local_system_notifications'), function(result) {
            if (result) {
                $.ajax({
                    method: 'POST',
                    url: M.cfg.wwwroot + '/local/system_notifications/ajax/addRule.php',
                    data: {
                        action: 'delete-eventrule',
                        id: trainingId,
                        curid: $('#eventrule_courseid').val(),
                    }
                }).done(function (data) {
                    $('#event-rule-list').html(data);
                    ruleEventEdit();
                    ruleEventDelete();
                    $.niftyNoty({
                        type: 'success',
                        message : M.util.get_string('rules_deleted', 'local_system_notifications'),
                        container : 'floating',
                        timer : 9000
                    });
                })
            }
        });
        
        e.preventDefault();
        return false;
    })
}


});


