$(function () {

    function isValidUrl(text) {
        return /\b(http|https)/.test(text);
    }
    /**
     * Vtex connection and validation begin
     */

    $('.reaction_auth_connection').click(function (e) {
        $(this).css('pointer-events', 'none');
        e.preventDefault();
        var reaction_url = $('#reaction_url input').val();
        var reaction_user_email = $('#reaction_user_email input').val();
        var reaction_user_pwd = $('#reaction_user_pwd input').val();
        var reaction_country = $('#reaction_country').val();
        var user_id = $('#user_id').val();
        var connection_id = $('#connection_id').val();
        //Required Fields Validation
        var isValid = true;

        if (!isValidUrl(reaction_url) || reaction_url == '')
        {
            $('#reaction_url').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else
        {
            if ($('#reaction_url').hasClass('has-error')) {
                $('#reaction_url').removeClass('has-error');
            }
        }
        if (reaction_user_email == '') {
            $('#reaction_user_email').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else {
            if ($('#reaction_user_email').hasClass('has-error')) {
                $('#reaction_user_email').removeClass('has-error');
            }
        }
        if (reaction_user_pwd == '') {
            $('#reaction_user_pwd').addClass('has-error');
            isValid = false;
        } else {
            if ($('#reaction_user_pwd').hasClass('has-error')) {
                $('#reaction_user_pwd').removeClass('has-error');
            }
        }

        if (reaction_country == '') {
            $('#wizard_reaction_country').addClass('has-error');
            isValid = false;
        } else {
            if ($('#wizard_reaction_country').hasClass('has-error')) {
                $('#wizard_reaction_country').removeClass('has-error');
            }
        }

        if (isValid) {
            $('.reaction_auth_connection').css('pointer-events', 'auto');
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/stores/auth-reaction',
                data: {
                    reaction_url: reaction_url,
                    reaction_user_email: reaction_user_email,
                    reaction_user_pwd: reaction_user_pwd,
                    user_id: user_id,
                    reaction_country: reaction_country,
                    connection_id: connection_id
                },
            }).done(function (data) {
                var obj = JSON.parse(data);
                var html_data = "";
                console.log(obj);

                if (obj["success"] != undefined) {

                    $('.be-wrapper').removeClass('be-loading-active');

                    html_data = obj['success'];
                    var store_connection_id = obj['store_connection_id'];
                    $("#reaction_ajax_msg").html(html_data);
                    $("#reaction_ajax_header_msg").html('Success!');
                    $('#reaction-authorize-form').hide();
                    $('#reaction-authorize-form')[0].reset();
                    $('#reaction_ajax_request').modal('show');

                }
                if (obj["api_error"] != undefined) {
                    html_data = obj['api_error'];
                    $("#reaction_ajax_msg_eror").html(html_data);
                    $("#reaction_ajax_header_error_msg").html('Error!');
                    $('.reaction_ajax_request_error').modal('show');
                }
            });
            // e.preventDefault();
        }
    });

    $('.reaction_error_modal_close').click(function () {

        $('.be-wrapper').removeClass('be-loading-active');
        $('#reaction-authorize-form')[0].reset();
        $('.reaction_ajax_request_error').modal('hide');

    });

    $('.reaction_modal_close').click(function () {
        $('#reaction_ajax_request').modal('hide');
        window.location = '/';
    });


    /**
     * Vtex connection and validation End
     */


});