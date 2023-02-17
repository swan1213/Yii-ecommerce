$(function () {

    function getExtension(filename) {
        var parts = filename.split('.');
        return parts[parts.length - 1];
    }


    $('#upload_products').on('click', function (e) {

        e.preventDefault();

        var user_connection_id = $('#user_connection_id').val();
        var fileObj = $('#flipkart_products_xls');
        if ( fileObj[0].files[0] ) {
            var file_data = fileObj[0].files[0];
            var fileExt = getExtension(file_data.name);

            if( (file_data.size > 0) && ((fileExt.toLowerCase() === 'xls') || (fileExt.toLowerCase() === 'xlsx')) ) {

                $('.be-wrapper').addClass('be-loading-active');

                var form_data = new FormData();
                form_data.append('file', file_data);
                form_data.append('user_connection_id', user_connection_id);

                $.ajax({
                    url: '/channels/upload-flipkart-xls', // point to server-side PHP script
                    dataType: 'json', // what to expect back from the PHP script
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,
                    type: 'post',
                    success: function (response) {
                        $('.be-wrapper').removeClass('be-loading-active');

                        //var obj = JSON.parse(response);
                        var result_success = response['success'];
                        var result_message = response['message'];
                        if (result_success == false) {

                            $("#flipkart_ajax_msg_eror").html(result_message);
                            $("#flipkart_ajax_header_error_msg").html('Upload Fail!');
                            $('#flipkart_ajax_error_modal').modal('show');


                        } else {
                            $('#flipkart_products_xls').val('');
                            $("#flipkart_ajax_msg").html(result_message);
                            $("#flipkart_ajax_header_msg").html('Upload Done!');
                            $('#flipkart_ajax_request').modal('show');
                        }

                    },
                    error: function (response) {
                        $('.be-wrapper').removeClass('be-loading-active');

                        $("#flipkart_ajax_msg_eror").html(response);
                        $("#flipkart_ajax_header_error_msg").html('Upload Fail!');
                        $('#flipkart_ajax_error_modal').modal('show');

                    }
                });

                return true;

            }

        }

        return false;
    });

    $('#upload_orders').on('click', function (e) {

        e.preventDefault();

        var user_connection_id = $('#user_connection_id').val();
        var fileObj = $('#flipkart_orders_csv');
        if ( fileObj[0].files[0] ) {
            var file_data = fileObj[0].files[0];
            var fileExt = getExtension(file_data.name);

            if( (file_data.size > 0) && fileExt.toLowerCase() == 'csv' ) {

                $('.be-wrapper').addClass('be-loading-active');

                var form_data = new FormData();
                form_data.append('file', file_data);
                form_data.append('user_connection_id', user_connection_id);

                $.ajax({
                    url: '/channels/upload-flipkart-csv', // point to server-side PHP script
                    dataType: 'json', // what to expect back from the PHP script
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,
                    type: 'post',
                    success: function (response) {
                        $('.be-wrapper').removeClass('be-loading-active');

                        //var obj = JSON.parse(response);
                        var result_success = response['success'];
                        var result_message = response['message'];
                        if (result_success == false) {

                            $("#flipkart_ajax_msg_eror").html(result_message);
                            $("#flipkart_ajax_header_error_msg").html('Upload Fail!');
                            $('#flipkart_ajax_error_modal').modal('show');


                        } else {
                            $('#flipkart_orders_csv').val('');
                            $("#flipkart_ajax_msg").html(result_message);
                            $("#flipkart_ajax_header_msg").html('Upload Done!');
                            $('#flipkart_ajax_request').modal('show');
                        }

                    },
                    error: function (response) {
                        $('.be-wrapper').removeClass('be-loading-active');

                        $("#flipkart_ajax_msg_eror").html(response);
                        $("#flipkart_ajax_header_error_msg").html('Upload Fail!');
                        $('#flipkart_ajax_error_modal').modal('show');

                    }
                });

                return true;

            }

        }

        return false;

    });


    $('.flipkart_error_modal_close').click(function () {
        $('#flipkart_ajax_error_modal').modal('hide');
    });

    $('.flipkart_modal_close').click(function () {
        $('#flipkart_ajax_request').modal('hide');
    });


});


