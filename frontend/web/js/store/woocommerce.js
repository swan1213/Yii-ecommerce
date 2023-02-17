$(function () {
	

    function isValidUrl(text) {
        return /\b(http|https)/.test(text);
    }

	/**
     * WooCommerce connection and validation begin
     */

    $('.wizard-next-auth-store-woocommerce').click(function (e) {
        e.preventDefault();
        //var id = $(this).data("wizard");
        var woocommerce_store_url = $('#woocommerce_store_url').val();
        var woocommerce_consumer = $('#woocommerce_consumer input').val();
        var woocommerce_secret = $('#woocommerce_secret input').val();
        var user_id = $('#woocommerce_user_id').val();
        var connection_id = $('#connection_id').val();

        //Required Fields Validation
        var chk = true;
        if (!isValidUrl(woocommerce_store_url)) {
            $('#woocommerce_url').addClass('has-error');
            chk = false;
        } else {
            if ($('#woocommerce_url').hasClass('has-error')) {
                $('#woocommerce_url').removeClass('has-error');
            }
        }
        $(".customer_validate").each(function () {
            var val = $.trim($(this).val());
            if (!val) {
                $(this).addClass("inpt_required");
                chk = false;
            } else {
                $(this).removeClass("inpt_required");
            }
        })


        if (chk == true) {
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: 'auth-woocommerce',
                data: {
                    woocommerce_store_url: woocommerce_store_url,
                    woocommerce_consumer: woocommerce_consumer,
                    woocommerce_secret: woocommerce_secret,
                    user_id: user_id,
                    connection_id: connection_id,
                },
            }).done(function (data) {
                //alert(data);
                var obj = JSON.parse(data);
                console.log(obj);
                $('.be-wrapper').removeClass('be-loading-active');

                if (obj["success"] != undefined) {
                    var html_data = obj['success'];
                    var store_connection_id = obj['store_connection_id'];
                    $("#woocommerce_ajax_msg").html(html_data);
                    $("#ajax_header_msg").html('Success!');
                    $('#woocommerce-authorize-form').hide();
                    $('#woocommerce-authorize-form')[0].reset();


                    setTimeout(function () {
                        $('#woocommerce_ajax_request').css({
                            'display': 'block',
                            'background': 'rgba(0,0,0,0.6)',
                        });

                        setTimeout(function () {
                            window.location = '/';
                        }, 5000);

                    }, 3000);

                    e.preventDefault();
                }
                if (obj["api_error"] != undefined)
                {
                    var html_data_error = obj['api_error'];
                    $("#woocommerce_ajax_msg_eror").html(html_data_error);
                    $("#ajax_header_error_msg").html('Error!');
                    $('.woocommerce_ajax_request_error').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                }
                if (obj["error"] != undefined)
                {
                    var html_data = obj['error'];
                    $("#woocommerce_ajax_msg").html(html_data);
                    $("#woocommerce_ajax_msg_eror").html(html_data);
                    $("#ajax_header_error_msg").html('Error!');
                    $('.woocommerce_ajax_request_error').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                }
            });
            e.preventDefault();
        }
    });

    $('.woocommerce_error_modal_close').click(function () {
        $('.woocommerce_ajax_request_error').css({
            'display': 'none',
            'background': 'rgba(0,0,0,0.6)',
        });
    });

    $('.woocommerce_modal_close').click(function () {
        $('#woocommerce_ajax_request').css({
            'display': 'none',
            'background': 'rgba(0,0,0,0.6)',
        });

        window.location = '/';
    });


    /**
     * WooCommerce connection and validation End
     */

});