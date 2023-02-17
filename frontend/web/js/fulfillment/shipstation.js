

$('.shipstation_auth_connection').click(function (e) {
    $(this).css('pointer-events', 'none');
    e.preventDefault();
    var shipstation_key = $('#shipstation_key input').val();
    var shipstation_secret = $('#shipstation_secret input').val();
    var user_id = $('#user_id').val();    
    var isValid = true;
    
    
    if (shipstation_key == '') {
        $('#shipstation_key').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#shipstation_key').hasClass('has-error')) {
            $('#shipstation_key').removeClass('has-error');
        }
    }
    
    if (shipstation_secret == '') {
        $('#shipstation_secret').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#shipstation_secret').hasClass('has-error')) {
            $('#shipstation_secret').removeClass('has-error');
        }
    }
    
    if (isValid) {
        $('.shipstation_auth_connection').css('pointer-events', 'auto');
        $('.be-wrapper').addClass('be-loading-active');
        $.ajax({
            type: 'post',
            url: '/fulfillment/auth-shipstation',
            data: {
                shipstation_key: shipstation_key,
                shipstation_secret: shipstation_secret,
                user_id: user_id,
            },
        }).done(function (data) {
            var response = JSON.parse(data);
            var success = response['success'];
            var msg = response['error'];
            if(success){
                console.log(response);

                if ($('#shipstation_key').hasClass('has-error')) {
                    $('#shipstation_key').removeClass('has-error');
                }
                if ($('#shipstation_secret').hasClass('has-error')) {
                    $('#shipstation_secret').removeClass('has-error');
                }
                
                $('#shipstation_ajax_request').modal('show');            
            }
            else{
                $('#shipstation_key').addClass('has-error');
                $(this).css('pointer-events', 'auto');
                $('#shipstation_secret').addClass('has-error');
                $(this).css('pointer-events', 'auto');    
                $("#shipstation_ajax_header_error_msg").html(msg);
                $('.shipstation_ajax_request_error').modal('show');     
            }
            $('.be-wrapper').removeClass('be-loading-active');
            console.log(response);
            return;
        });
    }
});
$('.shipstation_error_modal_close').click(function () {
    $('.shipstation_ajax_request_error').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
});

$('.shipstation_modal_close').click(function () {
    $('#shipstation_ajax_request').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
    location.reload();
});