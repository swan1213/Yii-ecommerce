$(function () {
    function isValidUrl(text) {
        return /\b(http|https)/.test(text);
    }
    /**
     * Magento-2 connection and validation begin
     */

    $('.wizard-next-auth-store-magento_2').click(function (e) {
        $(this).css('pointer-events', 'none');
        e.preventDefault();
        //var id = $(this).data("wizard");
        var magento_2_shop = $('#magento_2_shop input').val();
        var magento_2_access_token = $('#magento_2_access_token input').val();
        var user_id = $('#user_id').val();
        var magento_2_country = $('#magento_2_country').val();
        //Required Fields Validation
        var isValid = true;

        if (!isValidUrl(magento_2_shop) || magento_2_shop == '')
        {
            $('#magento_2_shop').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else
        {
            if ($('#magento_2_shop').hasClass('has-error')) {
                $('#magento_2_shop').removeClass('has-error');
            }
        }
        if (magento_2_access_token == '') {
            $('#magento_2_access_token').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else {
            if ($('#magento_2_access_token').hasClass('has-error')) {
                $('#magento_2_access_token').removeClass('has-error');
            }
        }
        if (magento_2_country == '') {
            $('#wizard_magento_2_country').addClass('has-error');
            isValid = false;
        } else {
            if ($('#wizard_magento_2_country').hasClass('has-error')) {
                $('#wizard_magento_2_country').removeClass('has-error');
            }
        }
        if (isValid) {
            $('.wizard-next-auth-store-magento_2').css('pointer-events', 'auto');
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/stores/auth-magento2',
                data: {
                    magento_2_shop: magento_2_shop,
                    magento_2_access_token: magento_2_access_token,
                    magento_2_country: magento_2_country,
                    user_id: user_id,
                },
            }).done(function (data) {
                var obj = JSON.parse(data);
                console.log(obj);
                if (obj["success"] != undefined)
                {
                    setTimeout(function () {
                        $('.be-wrapper').removeClass('be-loading-active');
                        $("#magento_2_ajax_msg").html(html_data);
                        $("#ajax_header_msg").html('Success!');
                        $('#magento-2-authorize-form').hide();
                        $('#magento_2_ajax_request').css({
                            'display': 'block',
                            'background': 'rgba(0,0,0,0.6)',
                        });
                        $('#magento-2-authorize-form')[0].reset();

                        $('#magento_2_ajax_request').modal('show').fadeOut(1000).queue(function () {
                            window.location = '/';
                        });

                    }, 3000);
                }
                else {
                    $('.be-wrapper').removeClass('be-loading-active');
                }
                if (obj["api_error"] != undefined)
                {
                    var html_data_error = obj['api_error'];
                    $("#magento_2_ajax_msg_eror").html(html_data_error);
                    $("#ajax_header_error_msg").html('Error!');
                    $('.magento_2_ajax_request_error').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                }
                if (obj["error"] != undefined)
                {
                    var html_data = obj['error'];
                    $("#magento_2_ajax_msg_eror").html(html_data);
                    $("#ajax_header_error_msg").html(html_data);
                    $("#ajax_header_error_msg").html('Error!');
                    $('.magento_2_ajax_request_error').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                }
            });
            // e.preventDefault();
        }
    });

    $('.magento_2_error_modal_close').click(function () {
        $('.magento_2_ajax_request_error').css({
            'display': 'none',
            'background': 'rgba(0,0,0,0.6)',
        });
    });

    $('.magento_2_modal_close').click(function () {
        $('#magento_2_ajax_request').css({
            'display': 'none',
            'background': 'rgba(0,0,0,0.6)',
        });
        window.location = '/';
    });


    /**
     * Magento-2 connection and validation End
     */

});