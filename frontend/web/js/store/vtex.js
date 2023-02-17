$(function () {

    /**
     * Vtex connection and validation begin
     */

    $('.vtex_auth_connection').click(function (e) {
        $(this).css('pointer-events', 'none');
        e.preventDefault();
        var vtex_account = $('#vtex_account input').val();
        var vtex_app_key = $('#vtex_app_key input').val();
        var vtex_app_token = $('#vtex_app_token input').val();
        var vtex_country = $('#vtex_country').val();
        var user_id = $('#user_id').val();
        var connection_id = $('#connection_id').val();
        //Required Fields Validation
        var isValid = true;

        if (vtex_account == '')
        {
            $('#vtex_account').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else
        {
            if ($('#vtex_account').hasClass('has-error')) {
                $('#vtex_account').removeClass('has-error');
            }
        }
        if (vtex_app_key == '') {
            $('#vtex_app_key').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else {
            if ($('#vtex_app_key').hasClass('has-error')) {
                $('#vtex_app_key').removeClass('has-error');
            }
        }
        if (vtex_app_token == '') {
            $('#vtex_app_token').addClass('has-error');
            isValid = false;
        } else {
            if ($('#vtex_app_token').hasClass('has-error')) {
                $('#vtex_app_token').removeClass('has-error');
            }
        }

        if (vtex_country == '') {
            $('#wizard_vtex_country').addClass('has-error');
            isValid = false;
        } else {
            if ($('#wizard_vtex_country').hasClass('has-error')) {
                $('#wizard_vtex_country').removeClass('has-error');
            }
        }


        if (isValid) {
            $('.vtex_auth_connection').css('pointer-events', 'auto');
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/stores/auth-vtex',
                data: {
                    vtex_account: vtex_account,
                    vtex_app_key: vtex_app_key,
                    vtex_app_token: vtex_app_token,
                    user_id: user_id,
                    vtex_country: vtex_country,
                    connection_id: connection_id
                },
            }).done(function (data) {
                var obj = JSON.parse(data);
                console.log(obj);

                if (obj["success"] != undefined) {

                    setTimeout(function () {
                        $('.be-wrapper').removeClass('be-loading-active');

                        var html_data = obj['success'];
                        var store_connection_id = obj['store_connection_id'];
                        $("#vtex_ajax_msg").html(html_data);
                        $("#vtex_ajax_header_msg").html('Success!');
                        $('#vtex-authorize-form').hide();
                        // $('#vtex_ajax_request').modal('show');
                        $('#vtex-authorize-form')[0].reset();
                        $('#vtex_ajax_request').modal('show').fadeOut(7000).queue(function () {
                            window.location = '/';
                        });

                    }, 3000);

                }
                if (obj["api_error"] != undefined) {
                    var html_data_error = obj['api_error'];
                    $("#vtex_ajax_msg_eror").html(html_data_error);
                    $("#vtex_ajax_header_error_msg").html('Error!');
                    $('.vtex_ajax_request_error').modal('show');
                }
            });
            // e.preventDefault();
        }
    });

    $('.vtex_error_modal_close').click(function () {
        $('.vtex_ajax_request_error').modal('hide');
    });

    $('.vtex_modal_close').click(function () {
        $('#vtex_ajax_request').modal('hide');
        window.location.reload()
    });


    /**
     * Vtex connection and validation End
     */


});