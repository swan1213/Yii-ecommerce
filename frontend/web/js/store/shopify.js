$(function () {
    function isValidUrl(text) {
        return /\b(http|https)/.test(text);
    }


    $('.wizard-next-auth-store-shopify').click(function (e) {
        e.preventDefault();
        var id = $(this).data("wizard");
        var shopify_shop = $('#shopify_shop input').val();
        var shopify_api = $('#shopify_api input').val();
        var shopify_pass = $('#shopify_pass input').val();
        var shopify_shared_secret = $('#shopify_shared_secret input').val();
        var shopify_country = $('#shopify_country').val();
        var shopify_plus = $('#enable_shopify_plus').prop('checked');
        var user_id = $('#user_id').val();
        var connection_id = $('#connection_id').val();
        //Required Fields Validation
        var isValid = true;

        if (isValidUrl(shopify_shop) || shopify_shop == '')
        {
            $('#shopify_shop').addClass('has-error');
            isValid = false;
        } else
        {
            if ($('#shopify_shop').hasClass('has-error')) {
                $('#shopify_shop').removeClass('has-error');
            }
        }

        if (shopify_api == '') {
            $('#shopify_api').addClass('has-error');
            isValid = false;
        } else {
            if ($('#shopify_api').hasClass('has-error')) {
                $('#shopify_api').removeClass('has-error');
            }
        }
        if (shopify_pass == '') {
            $('#shopify_pass').addClass('has-error');
            isValid = false;
        } else {
            if ($('#shopify_pass').hasClass('has-error')) {
                $('#shopify_pass').removeClass('has-error');
            }
        }
        if (shopify_shared_secret == '') {
            $('#shopify_shared_secret').addClass('has-error');
            isValid = false;
        } else {
            if ($('#shopify_shared_secret').hasClass('has-error')) {
                $('#shopify_shared_secret').removeClass('has-error');
            }
        }

        if (shopify_country == '') {
            $('#wizard_shopify_country').addClass('has-error');
            isValid = false;
        } else {
            if ($('#wizard_shopify_country').hasClass('has-error')) {
                $('#wizard_shopify_country').removeClass('has-error');
            }
        }


        if (isValid) {
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: 'auth-shopify',
                data: {
                    shopify_shop: shopify_shop,
                    shopify_api: shopify_api,
                    shopify_pass: shopify_pass,
                    shopify_shared_secret: shopify_shared_secret,
                    shopify_plus: shopify_plus,
                    shopify_country: shopify_country,
                    user_id: user_id,
                    connection_id: connection_id,
                },
            }).done(function (data) {
                //alert(data);
                var obj = JSON.parse(data);
                var html_data = "";
                //console.log(obj);

                if (obj["api_error"] != undefined)
                {
                    $('.be-wrapper').removeClass('be-loading-active');
                    html_data = obj['api_error'];
                    $("#shopify_ajax_msg_eror").html(html_data);
                    $("#ajax_header_error_msg").html('Error!');
                    $('.shipify_ajax_request_error').modal('show');
                }
                if (obj["success"] != undefined)
                {
                    setTimeout(function () {
                        $('.be-wrapper').removeClass('be-loading-active');
                        var html_data = obj['success'];
                        $("#shopify_ajax_msg").html(html_data);
                        $("#ajax_header_msg").html('Success!');
                        $('#shopify-authorize-form').hide();
                        $('#shopify-authorize-form')[0].reset();
                        $('#shipify_ajax_request').modal('show').fadeOut(7000).queue(function () {
                            window.location = '/';
                        });

                    }, 3000);

                }
                if (obj["error"] != undefined)
                {
                    $('.be-wrapper').removeClass('be-loading-active');
                    html_data = obj['error'];
                    $("#shopify_ajax_msg").html(html_data);
                    $("#shopify_ajax_msg_eror").html(html_data);
                    $("#ajax_header_error_msg").html('Error!');
                    $('#shipify_ajax_request_error').modal('show');
                }


            });
        }
    });

    $('#shipify_ajax_request .mdi-close').click(function () {
        $('#shipify_ajax_request').modal('hide');
        window.location = '/';
    });

    $('#shipify_ajax_request .shipify_modal_close').click(function () {
        $('#shipify_ajax_request').modal('hide');
        window.location = '/';
    });

    $('.shipify_error_modal_close').click(function () {
        $('.shipify_ajax_request_error').modal('hide');
    });



    $('.reset-shopify-form-on-cancel').click(function (e) {
        e.preventDefault();
        $('#shopify-authorize-form').find('#shopify_shop input').val("");
        $('#shopify-authorize-form').find('#shopify_api input').val("");
        $('#shopify-authorize-form').find('#shopify_pass input').val("");
        $('#shopify-authorize-form').find('#shopify_shared_secret input').val("");

    });

    /**
     * Shopify connection and validation End
     */


});