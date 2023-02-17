$(function () {

    /**
     * Jet connection and validation
     */
    /**
     * Square connection and validation begin
     */

    $('.jet-auth-channel').click(function (e) {
        e.preventDefault();
        $(this).css('pointer-events', 'none');
        var jet_api_user = $('#jet_api_user input').val();
        var jet_secret_key = $('#jet_secret_key input').val();
        var jet_merchant_id = $('#jet_merchant_id input').val();
        var user_id = $('#user_id').val();
        var connection_id = $('#connection_id').val();
        //Required Fields Validation
        var isValid = true;

        if (jet_api_user == '')
        {
            $('#jet_api_user').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else
        {
            if ($('#jet_api_user').hasClass('has-error')) {
                $('#jet_api_user').removeClass('has-error');
            }
        }
        if (jet_secret_key == '') {
            $('#jet_secret_key').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else {
            if ($('#jet_secret_key').hasClass('has-error')) {
                $('#jet_secret_key').removeClass('has-error');
            }
        }
        if (jet_merchant_id == '') {
            $('#jet_merchant_id').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else {
            if ($('#jet_merchant_id').hasClass('has-error')) {
                $('#jet_merchant_id').removeClass('has-error');
            }
        }



        if (isValid) {
            $('.jet-auth-channel').css('pointer-events', 'auto');
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/channels/auth-jet',
                data: {
                    jet_api_user: jet_api_user,
                    jet_secret_key: jet_secret_key,
                    jet_merchant_id: jet_merchant_id,
                    user_id: user_id,
                    connection_id: connection_id,
                },
                complete: function (data) {
                    var response_data = data.responseText;
                    var obj = JSON.parse(response_data);
                    console.log(obj);
                    if (obj["success"] != undefined) {
                        var html_data = obj['success'];
                        $("#jet_ajax_msg").html(html_data);
                        $("#jet_ajax_header_msg").html('Success!');
                        $('#jet-authorize-form').hide();
                        $('#jet_ajax_request').modal('show');

                    }
                    if (obj["api_error"] != undefined) {
                        var html_data_error = obj['api_error'];
                        $("#jet_ajax_msg_eror").html(html_data_error);
                        $("#jet_ajax_header_error_msg").html('Error!');
                        $('#jet_ajax_error_modal').modal('show');
                    }
                    $('.be-wrapper').removeClass('be-loading-active');
                },
            });
            // e.preventDefault();
        }
    });

    $('.jet_error_modal_close').click(function () {
        $('#jet_ajax_error_modal').modal('hide');
    });

    $('.jet_modal_close').click(function () {
        $('#jet_ajax_request').modal('hide');
        window.location = '/';
    });

    /**
     * Jet connection and validation end
     */

});


