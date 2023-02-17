$(function () {
    function isValidUrl(text) {
        return /\b(http|https)/.test(text);
    }
    /**
     * Magento connection and validation begin
     */

    $('.wizard-next-auth-store-magento').click(function (e) {
        e.preventDefault();
        //var id = $(this).data("wizard");
        var magento_shop = $('#magento_shop input').val();
        var magento_soap_user = $('#magento_soap_user input').val();
        var magento_soap_api = $('#magento_soap_api input').val();
        var magento_country = $('#magento_country').val();

        var user_id = $('#user_id').val();
        //Required Fields Validation
        var isValid = true;

        if (!isValidUrl(magento_shop) || magento_shop == '')
        {
            $('#magento_shop').addClass('has-error');
            isValid = false;
        } else
        {
            if ($('#magento_shop').hasClass('has-error')) {
                $('#magento_shop').removeClass('has-error');
            }
        }

        if (magento_soap_user == '') {
            $('#magento_soap_user').addClass('has-error');
            isValid = false;
        } else {
            if ($('#magento_soap_user').hasClass('has-error')) {
                $('#magento_soap_user').removeClass('has-error');
            }
        }
        if (magento_soap_api == '') {
            $('#magento_soap_api').addClass('has-error');
            isValid = false;
        } else {
            if ($('#magento_soap_api').hasClass('has-error')) {
                $('#magento_soap_api').removeClass('has-error');
            }
        }
        if (magento_country == '') {
            $('#wizard_magento_country').addClass('has-error');
            isValid = false;
        } else {
            if ($('#wizard_magento_country').hasClass('has-error')) {
                $('#wizard_magento_country').removeClass('has-error');
            }
        }

        if (isValid) {
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/stores/auth-magento',
                data: {
                    magento_shop: magento_shop,
                    magento_soap_user: magento_soap_user,
                    magento_soap_api: magento_soap_api,
                    user_id: user_id,
                    magento_country: magento_country,
                },
            }).done(function (data) {
                var obj = JSON.parse(data);
                console.log(obj);

                if (obj["success"] != undefined)
                {
                    setTimeout(function () {
                        $('.be-wrapper').removeClass('be-loading-active');
                        $("#magento_ajax_msg").html(html_data);
                        $("#ajax_header_msg").html('Success!');
                        $('#magento-authorize-form').hide();
                        $('#magento_ajax_request').css({
                            'display': 'block',
                            'background': 'rgba(0,0,0,0.6)',
                        });
                        $('#magento-authorize-form')[0].reset();

                        $('#magento_ajax_request').modal('show').fadeOut(1000).queue(function () {
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
                    $("#magento_ajax_msg_eror").html(html_data_error);
                    $("#ajax_header_error_msg").html('Error!');
                    $('.magento_ajax_request_error').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                }
                if (obj["error"] != undefined)
                {
                    var html_data = obj['error'];
                    $("#magento_ajax_msg").html(html_data);
                    $("#magento_ajax_msg_eror").html(html_data);
                    $("#ajax_header_error_msg").html('Error!');
                    $('.magento_ajax_request_error').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                }
            });
            // e.preventDefault();
        }
    });

    $('.magento_error_modal_close').click(function () {
        $('.magento_ajax_request_error').css({
            'display': 'none',
            'background': 'rgba(0,0,0,0.6)',
        });
    });

    $('.magento_modal_close').click(function () {
        $('#magento_ajax_request').css({
            'display': 'none',
            'background': 'rgba(0,0,0,0.6)',
        });
        window.location = '/';
    });


    /**
     * Magento connection and validation End
     */

});