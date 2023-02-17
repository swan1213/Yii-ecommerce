

$('.tpl_auth_connection').click(function (e) {
    $(this).css('pointer-events', 'none');
    e.preventDefault();
    var tpl_client_id = $('#tpl_client_id input').val();
    var tpl_client_secret = $('#tpl_client_secret input').val();
    var tpl_key = $('#tpl_key input').val();
    var tpl_encoded = $('#tpl_encoded input').val();
    var user_id = $('#user_id').val();    
    var isValid = true;
    
    
    tpl_client_id = "unnecessary";
    tpl_client_secret = "unnecessary";                                                                               

    if (tpl_client_id == '') {
        $('#tpl_client_id').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#tpl_client_id').hasClass('has-error')) {
            $('#tpl_client_id').removeClass('has-error');
        }
    }

    if (tpl_client_secret == '') {
        $('#tpl_client_secret').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#tpl_client_secret').hasClass('has-error')) {
            $('#tpl_client_secret').removeClass('has-error');
        }
    }
    
    if (tpl_key == '') {
        $('#tpl_key').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#tpl_key').hasClass('has-error')) {
            $('#tpl_key').removeClass('has-error');
        }
    }
    
    if (tpl_client_id == '') {
        $('#tpl_encoded').addClass('has-error');
        $(this).css('pointer-events', 'auto');
        isValid = false;
    } else {
        if ($('#tpl_encoded').hasClass('has-error')) {
            $('#tpl_encoded').removeClass('has-error');
        }
    }
    
    if (isValid) {
        $('.tpl_auth_connection').css('pointer-events', 'auto');
        $('.be-wrapper').addClass('be-loading-active');
        var currentURL = $("#hidURL").val();
        var subdomain = currentURL.split('.');
        $.ajax({
            type: 'post',
            url: 'https://' + subdomain[0] + '.' + subdomain[1] + '.' + subdomain[2] + '/tpl-central/auth-tpl',
            data: {
                tpl_client_id: tpl_client_id,
                tpl_client_secret: tpl_client_secret,
                tpl_key: tpl_key,
                tpl_encoded: tpl_encoded,
            },
        }).done(function (data) {
            var response = JSON.parse(data);
            var success = response['success'];
            var msg = response['msg'];
            var data = response['data'];
            if(success){
                console.log(response);
                if ($('#tpl_client_id').hasClass('has-error')) {
                    $('#tpl_client_id').removeClass('has-error');
                }
                if ($('#tpl_client_secret').hasClass('has-error')) {
                    $('#tpl_client_secret').removeClass('has-error');
                }
                if ($('#tpl_key').hasClass('has-error')) {
                    $('#tpl_key').removeClass('has-error');
                }
                if ($('#tpl_encoded').hasClass('has-error')) {
                    $('#tpl_encoded').removeClass('has-error');
                }
                
                $('#tpl_ajax_request').modal('show');            
            }
            else{
                $('#tpl_client_id').addClass('has-error');
                $(this).css('pointer-events', 'auto');
                $('#tpl_client_secret').addClass('has-error');
                $(this).css('pointer-events', 'auto');
                $('#tpl_key').addClass('has-error');
                $(this).css('pointer-events', 'auto');
                $('#tpl_encoded').addClass('has-error');
                $(this).css('pointer-events', 'auto');    
                $("#tpl_ajax_header_error_msg").html(msg);
                $('.tpl_ajax_request_error').modal('show');     
            }
            $('.be-wrapper').removeClass('be-loading-active');
            console.log(response);
            return;
        });
    }
});
$('.tpl_error_modal_close').click(function () {
    $('.tpl_ajax_request_error').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
});

$('.tpl_modal_close').click(function () {
    $('#tpl_ajax_request').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
    location.reload();
});