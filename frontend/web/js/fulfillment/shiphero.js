

$('.shiphero_auth_connection').click(function (e) {
    $(this).css('pointer-events', 'none');
    e.preventDefault();
    var shiphero_key = $('#shiphero_key input').val();
    var shiphero_secret = $('#shiphero_secret input').val();
    var user_id = $('#user_id').val();
    var isValid = true;


    if (shiphero_key == '') {
        $('#shiphero_key').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#shiphero_key').hasClass('has-error')) {
            $('#shiphero_key').removeClass('has-error');
        }
    }

    if (shiphero_secret == '') {
        $('#shiphero_secret').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#shiphero_secret').hasClass('has-error')) {
            $('#shiphero_secret').removeClass('has-error');
        }
    }

    if (isValid) {
        $('.shiphero_auth_connection').css('pointer-events', 'auto');
        $('.be-wrapper').addClass('be-loading-active');
        $.ajax({
            type: 'post',
            url: '/fulfillment/auth-shiphero',
            data: {
                shiphero_key: shiphero_key,
                shiphero_secret: shiphero_secret,
                user_id: user_id,
            },
        }).done(function (data) {
            var response = JSON.parse(data);
            var success = response['success'];
            var msg = response['error'];
            if(success){
                console.log(response);

                if ($('#shiphero_key').hasClass('has-error')) {
                    $('#shiphero_key').removeClass('has-error');
                }
                if ($('#shiphero_secret').hasClass('has-error')) {
                    $('#shiphero_secret').removeClass('has-error');
                }

                $('#shiphero_ajax_request').modal('show');
            }
            else{
                $('#shiphero_key').addClass('has-error');
                $(this).css('pointer-events', 'auto');
                $('#shiphero_secret').addClass('has-error');
                $(this).css('pointer-events', 'auto');
                $("#shiphero_ajax_header_error_msg").html(msg);
                $('.shiphero_ajax_request_error').modal('show');
            }
            $('.be-wrapper').removeClass('be-loading-active');
            console.log(response);
            return;
        });
    }
});
$('.shiphero_error_modal_close').click(function () {
    $('.shiphero_ajax_request_error').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
});

$('.shiphero_modal_close').click(function () {
    $('#shiphero_ajax_request').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
    location.reload();
});