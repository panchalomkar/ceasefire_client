/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function () {



    $('#checkout').on('click', function () {
        var ajaxUrl = M.cfg.wwwroot + '/local/products/ajax/products_ajax.php';
         var data = {};
                        data['action'] = 'checkout';
                       

        $.ajax({
            method: 'GET',
            url: ajaxUrl,
            data: data,
            dataType: 'json',
            async: false,
        }).done(function (res) {
            console.log(res.message);
              var message =
                bdPayment.initialize({
                    msg: res.message,
                    callbackUrl: M.cfg.wwwroot + '/local/products/billdesk_callback.php',

                    options: {
                        enableChildWindowPosting: true,
                        enablePaymentRetry: true,
                        retry_attempt_count: 2,
                        // txtPayCategory: "NETBANKING"
                    }
                });
        });
      
      
    });


});