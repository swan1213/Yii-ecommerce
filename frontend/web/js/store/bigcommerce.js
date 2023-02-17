$(function () {


    function isValidUrl(text) {
        return /\b(http|https)/.test(text);
    }

    //BigCommerce Authorization 
    $('.wizard-next-auth-store-bgcmrc').click(function (e) {
        e.preventDefault();
        var id = $(this).data("wizard");
        var api_path = $('#big_api_path input').val();
        var access_token = $('#big_access_token input').val();
        var client_id = $('#big_client_id input').val();
        var client_secret = $('#big_client_secret input').val();
        var user_id = $('#user_id').val();
        var connection_id = $('#connection_id').val();
        //Required Fields Validation
        var isValid = true;
        if (!isValidUrl(api_path) || api_path == '') {
            $('#big_api_path').addClass('has-error');
            isValid = false;
        } else {
            if ($('#big_api_path').hasClass('has-error')) {
                $('#big_api_path').removeClass('has-error');
            }
        }
        if (access_token == '') {
            $('#big_access_token').addClass('has-error');
            isValid = false;
        } else {
            if ($('#big_access_token').hasClass('has-error')) {
                $('#big_access_token').removeClass('has-error');
            }
        }
        if (client_id == '') {
            $('#big_client_id').addClass('has-error');
            isValid = false;
        } else {
            if ($('#big_client_id').hasClass('has-error')) {
                $('#big_client_id').removeClass('has-error');
            }
        }

        if (isValid) {
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: 'auth-bigcommerce',
                data: {
                    api_path: api_path,
                    access_token: access_token,
                    client_id: client_id,
                    client_secret: client_secret,
                    user_id: user_id,
                    connection_id: connection_id,
                },
            }).done(function (response_data) {
                var obj = JSON.parse(response_data);
                var html_data = "";
                if (obj["api_error"] != undefined) {
                    html_data = obj['api_error'];
                    $('.be-wrapper').removeClass('be-loading-active');
                    $("#bigcommerce_ajax_msg_eror").html(html_data);
                    $("#bigcommerce_ajax_header_error_msg").html('Error!');
                    $('#bigcommerce_ajax_error_modal').modal('show');
                }
                if (obj["success"] != undefined) {

                    setTimeout(function () {
                        $('.be-wrapper').removeClass('be-loading-active');
                        $('#connect-modal').modal('show').fadeOut(7000).queue(function () {
                            window.location = '/';
                        });

                    }, 3000);


                }
                if (obj["error"] != undefined) {
                    $('.be-wrapper').removeClass('be-loading-active');
                    html_data = obj['api_error'];
                    $("#bigcommerce_ajax_msg_eror").html(html_data);
                    $("#bigcommerce_ajax_header_error_msg").html('Error!');
                    $('#bigcommerce_ajax_error_modal').modal('show');
                }
            });
        }
    });


    $('.empty-the-bigcommerce-form').click(function (e) {
        e.preventDefault();
        $('#bigcommerce-authorize-form').find("#big_api_path input").val("");
        $('#bigcommerce-authorize-form').find("#big_access_token input").val("");
        $('#bigcommerce-authorize-form').find("#big_client_id input").val("");
        $('#bigcommerce-authorize-form').find("#big_client_secret input").val("");


    });

    $('.bigcommerce_error_modal_close').click(function () {
        $('#bigcommerce_ajax_error_modal').modal('hide');
    });


    $('#connect-modal').on('hidden.bs.modal', function () {
        $('.be-wrapper').removeClass('be-loading-active');
        $('#bigcommerce-authorize-form').hide();
        $('#bigcommerce-connect-form').hide();
        $('#bgc_text1').show();
        $('#bgc_text2').show();
    });


});