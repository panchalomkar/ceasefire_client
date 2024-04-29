
$('.save-department').click(function (e) {
    e.preventDefault()
    alert()
    

    var url = 'ajax.php?action=manual_marking';

//    $.ajax({
//        url: url,
//        dataType: 'html',
//        type: 'POST',
//        data: {mark: mark, maxmark: maxmark, attemptid: attemptid, userid: userid, courseid: courseid, uniqueid: uniqueid},
//        success: function (data) {
//            if (data == '1') {
//                $('.manual-mark').css('display', 'none')
//                $('.modal-body').html('Mark successfully saved');
//                $('#' + attemptid).html(mark);
//            } else {
//                $('.modal-body').html('Something went wrong, Please try again')
//            }
//        },
//        error: function () {
//            alert('Data saving error')
//        },
//    });
})
