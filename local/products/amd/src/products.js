define(['jquery', 'core/str'], function ($, str) {
    var strings = str.get_strings([
        { key: 'addtocart', component: 'local_products' },
        { key: 'incart', component: 'local_products' },
        { key: 'adding...', component: 'local_products' },
        { key: 'removing...', component: 'local_products' },
    ]);
    return {
        init: function () {
            $(document).ready(function(){
                $(".addtocartbtn").click(function(){
                    var btn = $(this);
                    if($(this).hasClass("in-cart")){
                        btn.addClass("anim").text(M.util.get_string('removing...', 'local_products'));
                        
                        var object = {};
                        object['action'] = 'removefromcart';
                        object['course'] = $(this).attr('id');

                        var ajaxUrl = M.cfg.wwwroot + '/local/products/ajax/products_ajax.php';
                        $.ajax({
                            method: 'POST',
                            url: ajaxUrl,
                            data: object,
                            dataType: 'json',
                            async: false,
                        }).done(function (res) {
                            if(res.status){
                                btn.removeClass("anim").removeClass("in-cart").addClass("add-to-cart-btn").html('<i class="fa fa-cart-plus" aria-hidden="true"></i> ' + M.util.get_string('addtocart', 'local_products'));
                                var count = $('.popover-region .nav-link .cart-count-badge').text();
                                if(count != ''){
                                    $('.popover-region .nav-link .cart-count-badge').text(parseInt(count) - 1);
                                }
                            } else {
                                btn.removeClass("in-cart").html('<i class="fa fa-check" aria-hidden="true"></i> ' + M.util.get_string('incart', 'local_products'));
                            }
                        });
                    }
                    else {
                        btn.addClass("anim").text(M.util.get_string('adding...', 'local_products'));
                        
                        var object = {};
                        object['action'] = 'checkout';
                        object['course'] = $(this).attr('id');

                        var ajaxUrl = M.cfg.wwwroot + '/local/products/ajax/products_ajax.php';
                        $.ajax({
                            method: 'POST',
                            url: ajaxUrl,
                            data: object,
                            dataType: 'json',
                            async: false,
                        }).done(function (res) {
                            if(res.status){
                                btn.removeClass("anim").addClass("in-cart").html('<i class="fa fa-check" aria-hidden="true"></i> ' + M.util.get_string('incart', 'local_products'));
                                var count = $('.popover-region .nav-link .cart-count-badge').text();
                                if(count != ''){
                                    $('.popover-region .nav-link .cart-count-badge').text(parseInt(count) + 1);
                                }
                            } else {
                                btn.removeClass("in-cart").html('<i class="fa fa-cart-plus" aria-hidden="true"></i> ' + M.util.get_string('addtocart', 'local_products'));
                            }
                        });
                    }
                });

                $(document).on('click', '.card-link-secondary', function(){
                    var id = $(this).attr('data-id');
                    var courseid = $(this).attr('data-courseid');
                    
                    var object = {};
                    object['action'] = 'removefromcart';
                    object['course'] = courseid;

                    var ajaxUrl = M.cfg.wwwroot + '/local/products/ajax/products_ajax.php';
                    $.ajax({
                        method: 'POST',
                        url: ajaxUrl,
                        data: object,
                        dataType: 'json',
                        async: false,
                    }).done(function (res) {
                        if(res.status){
                            $('#single_cart_course_product_'+id).remove();
                            $('#separator_hr_'+id).remove();
                            var count = $('#cart_item_count').text();
                            if(count != ''){
                                $('#cart_item_count').text(parseInt(count) - 1);
                                $('.popover-region .nav-link .cart-count-badge').text(parseInt(count) - 1);
                            }
                            window.location.reload();
                        } else {
                        }
                    });
                });

                $("a").on('click', function(event) {
                    // Make sure this.hash has a value before overriding default behavior
                    if (this.hash !== "") {
                        // Prevent default anchor click behavior
                        event.preventDefault();
                    
                        // Store hash
                        var hash = this.hash;
                
                        // Using jQuery's animate() method to add smooth page scroll
                        // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
                        $('html, body').animate({
                            scrollTop: $(hash).offset().top - 100
                        }, 800);
                    } // End if
                });
            });
        }
    }
});