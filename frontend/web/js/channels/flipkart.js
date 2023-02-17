$(function () {

    /**
     * Flipkart connection and validation
     */

    $('.flipkart-auth-channel').click(function (e) {
        e.preventDefault();
        $(this).css('pointer-events', 'none');
        var flipkart_app_id = $('#flipkart_app_id input').val();
        var flipkart_app_secret = $('#flipkart_app_secret input').val();
        var user_id = $('#user_id').val();
        var connection_id = $('#connection_id').val();

        //Required Fields Validation
        var isValid = true;

        if (flipkart_app_id == '')
        {
            $('#flipkart_app_id').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else
        {
            if ($('#flipkart_app_id').hasClass('has-error')) {
                $('#flipkart_app_id').removeClass('has-error');
            }
        }
        if (flipkart_app_secret == '') {
            $('#flipkart_app_secret').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else {
            if ($('#flipkart_app_secret').hasClass('has-error')) {
                $('#flipkart_app_secret').removeClass('has-error');
            }
        }

        if (isValid) {
            $('.flipkart-auth-channel').css('pointer-events', 'auto');
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/channels/auth-flipkart',
                data: {
                    flipkart_app_id: flipkart_app_id,
                    flipkart_app_secret: flipkart_app_secret,
                    user_id: user_id,
                    connection_id: connection_id,
                },
                complete: function (data) {
                    var response_data = data.responseText;
                    var obj = JSON.parse(response_data);
                    console.log(obj);
                    if (obj["success"] != undefined) {
                        var html_data = obj['success'];
                        var user_connection_id = obj['user_connection_id'];
                        $("#user_connection_id").val(user_connection_id);
                        $("#flipkart_ajax_msg").html(html_data);
                        $("#flipkart_ajax_header_msg").html('Success!');
                        $('#flipkart-authorize-form').hide();
                        $('#flipkart_ajax_request').modal('show');
                        
                    }
                    if (obj["api_error"] != undefined) {
                        var html_data_error = obj['api_error'];
                        $("#flipkart_ajax_msg_eror").html(html_data_error);
                        $("#flipkart_ajax_header_error_msg").html('Error!');
                        $('#flipkart_ajax_error_modal').modal('show');
                    }
                    $('.be-wrapper').removeClass('be-loading-active');
                },
            });
            // e.preventDefault();
        }
    });

    $('.flipkart_error_modal_close').click(function () {
        $('#flipkart_ajax_error_modal').modal('hide');
    });

    $('.flipkart_modal_close').click(function () {
        var user_connection_id = $("#user_connection_id").val();
        $('#flipkart_ajax_request').modal('hide');
        window.location = '/channels/upload-flipkart?connection=' + user_connection_id;
    });

    /**
     * Flipkart connection and validation end
     */

});


