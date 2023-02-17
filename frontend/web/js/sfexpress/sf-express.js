
$('.sf_express_auth_connection').click(function (e) {
    $(this).css('pointer-events', 'none');
    e.preventDefault();
    var sf_express_username = $('#SfExpress_username input').val();
    var sf_express_password = $('#SfExpress_password input').val();
    var user_id = $('#user_id').val();
    var isValid = true;

    if (sf_express_username == '') {
        $('#SfExpress_username').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#SfExpress_username').hasClass('has-error')) {
            $('#SfExpress_username').removeClass('has-error');
        }
    }

    if (sf_express_password == '') {
        $('#SfExpress_password').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#SfExpress_password').hasClass('has-error')) {
            $('#SfExpress_password').removeClass('has-error');
        }
    }

    if (isValid) {
        $('.sf_express_auth_connection').css('pointer-events', 'auto');
        $('.be-wrapper').addClass('be-loading-active');
        $.ajax({
            type: 'post',
            url: '/sfexpress/auth',
            data: {
                sf_express_username: sf_express_username,
                sf_express_password: sf_express_password,
            },
        }).done(function (data) {
            var response = JSON.parse(data);
            var success = response['success'];
            var msg = response['msg'];
            var data = response['data'];
            if(success){
                console.log(response);
                if ($('#SfExpress_username').hasClass('has-error')) {
                    $('#SfExpress_username').removeClass('has-error');
                }
                if ($('#SfExpress_password').hasClass('has-error')) {
                    $('#SfExpress_password').removeClass('has-error');
                }

                $('#SfExpress_ajax_request').modal('show');
            }
            else{
                $('#SfExpress_username').addClass('has-error');
                $(this).css('pointer-events', 'auto');
                $('#SfExpress_password').addClass('has-error');
                $(this).css('pointer-events', 'auto');
                $("#ajax_header_error_msg").html(msg);
                $('.SfExpress_ajax_request_error').modal('show');
            }
            $('.be-wrapper').removeClass('be-loading-active');
            console.log(response);
            return;
        });
    }
});
$('.SfExpress_error_modal_close').click(function () {
    $('.SfExpress_ajax_request_error').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
});

$('.sf_express_modal_close').click(function () {
    $('#SfExpress_ajax_request').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
    window.location.href = '/fulfillment/carriers';
});

var selectedObj;

$(document).on('click', '.label-print-awb', function (e) {
    $(this).css('pointer-events', 'none');
    e.preventDefault();
    var user_id = $('#user_id').val();
    var order_id = $('#order_id').val();

    //$(this).parent().parent().html('<span><a target="_blank" href="https://track.aftership.com/sf-express/070036785706">070036785706</a></span>');
    //return;
    selectedObj = $(this);
    $('.be-wrapper').addClass('be-loading-active');

    $.ajax({
        type: 'post',
        url: '/sfexpress/order',
        data: {
            user_id: user_id,
            order_id: order_id,
        },
    }).done(function (data) {
        var response = JSON.parse(data);
        var success = response['Success'];
        var data = response['Data'];
        var hawbs = data['Hawbs'];
        if(success){
            console.log(response);
            selectedObj.parent().parent().html('<span><a target="_blank" href="https://track.aftership.com/sf-express/' + hawbs + '">' + hawbs + '</a></span>');
            //$( '.sf_express_print' ).append( '<span><a target="_blank" href="https://track.aftership.com/sf-express/' + hawbs + '">' + hawbs + '</a></span>' );
        }
        else{

        }
        $('.be-wrapper').removeClass('be-loading-active');
        console.log(response);
        return;
    });
});