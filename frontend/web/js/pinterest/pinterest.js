
$(function () {

    $(".wizard-pinterest-auth").click(function (e) {
        var id = $(this).data("wizard");
        var accessToken = $('#pinterst_access_token input').val();

        var isValid = true;
        if (accessToken == '') {
            $('#pinterst_access_token').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pinterst_access_token').hasClass('has-error')) {
                $('#pinterst_access_token').removeClass('has-error');
            }
        }

        if (isValid) {
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/pinterest/valid-token',
                data: {
                    token: accessToken
                },
            }).done(function (data) {
                $('.be-wrapper').removeClass('be-loading-active');
                var response = JSON.parse(data);
                var success = response['success'];
                var msg = response['error'];
                if(success){
                    $('#p_token').val(accessToken);
                    $(id).wizard('next');
                }
                else{
                    $("#pinterest_ajax_header_error_msg").html(msg);
                    $('.pinterest_ajax_request_error').modal('show');
                }
            });

        }
        e.preventDefault();

    });

    $(".wizard-pinterest-feed-next").click(function (e) {

        var board_name = $('#p_board_name input').val();

        var cate_chk = false;
        var isValid = true;
        var categories = [];
        if (board_name == '') {
            $('#p_board_name').addClass('has-error');
            $("#pinterest_ajax_header_error_msg").html("Please input Board name!");
            $('.pinterest_ajax_request_error').modal('show');
            isValid = false;
            e.preventDefault();
            return;
        } else {
            if ($('#p_board_name').hasClass('has-error')) {
                $('#p_board_name').removeClass('has-error');
            }
        }
        var fb_feed_categories = $(".fb_feed_categories");
        for (let i = 0; i < fb_feed_categories.length; i++) {
            var category = fb_feed_categories[i];
            if(category.checked){
                cate_chk = true;
                categories.push(category.value);
            }
        }

        if (cate_chk ) {
            //isValid = true;
        }
        else {
            isValid = false;
            $("#pinterest_ajax_header_error_msg").html("Please select the categories!");
            $('.pinterest_ajax_request_error').modal('show');
        }
        if (isValid) {
            $('.be-wrapper').addClass('be-loading-active');
            var p_token = $('#p_token').val();
            $.ajax({
                type: 'post',
                url: '/pinterest/create-board',
                data: {
                    token: p_token,
                    board_name: board_name,
                    category: categories
                },
            }).done(function (data) {
                $('.be-wrapper').removeClass('be-loading-active');
                var response = JSON.parse(data);
                var success = response['success'];
                var msg = response['error'];
                if(success){

                    $('#pinterest_ajax_request').modal('show');
                }
                else{
                    $("#pinterest_ajax_header_error_msg").html(msg);
                    $('.pinterest_ajax_request_error').modal('show');
                }
            });

        }
        e.preventDefault();
    });

    $("#pinterest_board_create").click(function (e) {

        var cate_chk = false;
        var ctry_chk = false;
        var isValid = true;
        var categories = [];
        var countries = [];
        var token = $('#p_token input').val();
        if (token == '') {
            $('#p_token').addClass('has-error');
            isValid = false;
            e.preventDefault();
            return;
        } else {
            if ($('#p_token').hasClass('has-error')) {
                $('#p_token').removeClass('has-error');
            }
        }
        var board_name = $('#p_board_name input').val();
        if (board_name == '') {
            $('#p_board_name').addClass('has-error');
            isValid = false;
            e.preventDefault();
            return;
        } else {
            if ($('#p_board_name').hasClass('has-error')) {
                $('#p_board_name').removeClass('has-error');
            }
        }
        var p_categories = $(".p_categories");
        for (let i = 0; i < p_categories.length; i++) {
            var category = p_categories[i];
            if(category.checked){
                cate_chk = true;
                categories.push(category.value);
            }
        }
        if (!cate_chk ) {
            isValid = false;
            $("#pinterest_ajax_header_error_msg").html("Please select the categories!");
            $('.pinterest_ajax_request_error').modal('show');
            e.preventDefault();
            return;
        }
        var p_countries = $(".p_countries");
        for (let i=0; i<p_countries.length; i++) {
            var country = p_countries[i];
            if(country.checked){
                ctry_chk = true;
                countries.push(country.value);;
            }
        }
        if (!ctry_chk ) {
            isValid = false;
            $("#pinterest_ajax_header_error_msg").html("Please select the country!");
            $('.pinterest_ajax_request_error').modal('show');
            e.preventDefault();
            return;
        }
        if (isValid) {
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/pinterest/create-board',
                data: {
                    token: token,
                    board_name: board_name,
                    category: categories,
                    country: countries,
                },
            }).done(function (data) {
                $('.be-wrapper').removeClass('be-loading-active');
                var response = JSON.parse(data);
                var success = response['success'];
                var msg = response['error'];
                if(success){
                    window.location = "/pinterest";
                }
                else{
                    $("#pinterest_ajax_header_error_msg").html(msg);
                    $('.pinterest_ajax_request_error').modal('show');
                }
            });

        }
        e.preventDefault();
    });

    $("#pinterest_board_update").click(function (e) {

        var cate_chk = false;
        var ctry_chk = false;
        var isValid = true;
        var categories = [];
        var countries = [];
        var board_id = $('#board_id').val();
        var token = $('#p_token input').val();
        if (token == '') {
            $('#p_token').addClass('has-error');
            isValid = false;
            e.preventDefault();
            return;
        } else {
            if ($('#p_token').hasClass('has-error')) {
                $('#p_token').removeClass('has-error');
            }
        }
        var board_name = $('#p_board_name input').val();
        if (board_name == '') {
            $('#p_board_name').addClass('has-error');
            isValid = false;
            e.preventDefault();
            return;
        } else {
            if ($('#p_board_name').hasClass('has-error')) {
                $('#p_board_name').removeClass('has-error');
            }
        }
        var p_categories = $(".p_categories");
        for (let i = 0; i < p_categories.length; i++) {
            var category = p_categories[i];
            if(category.checked){
                cate_chk = true;
                categories.push(category.value);
            }
        }
        if (!cate_chk ) {
            isValid = false;
            $("#pinterest_ajax_header_error_msg").html("Please select the categories!");
            $('.pinterest_ajax_request_error').modal('show');
            e.preventDefault();
            return;
        }
        var p_countries = $(".p_countries");
        for (let i=0; i<p_countries.length; i++) {
            var country = p_countries[i];
            if(country.checked){
                ctry_chk = true;
                countries.push(country.value);;
            }
        }
        if (!ctry_chk ) {
            isValid = false;
            $("#pinterest_ajax_header_error_msg").html("Please select the country!");
            $('.pinterest_ajax_request_error').modal('show');
            e.preventDefault();
            return;
        }
        if (isValid) {
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/pinterest/update-board',
                data: {
                    id: board_id,
                    token: token,
                    board_name: board_name,
                    category: categories,
                    country: countries,
                },
            }).done(function (data) {
                $('.be-wrapper').removeClass('be-loading-active');
                var response = JSON.parse(data);
                var success = response['success'];
                var msg = response['error'];
                if(success){
                    window.location = "/pinterest";
                }
                else{
                    $("#pinterest_ajax_header_error_msg").html(msg);
                    $('.pinterest_ajax_request_error').modal('show');
                }
            });

        }
        e.preventDefault();
    });

    $(".pinterest_modal_close").click(function (e) {
        window.location = '/channels';
    });
});