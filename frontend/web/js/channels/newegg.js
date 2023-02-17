$(function () {
	var $upload_file_wrap = $('#newegg_file_update');
	$upload_file_wrap.find('button').on('click', function() {

	    var file_data = $upload_file_wrap.find('#update_batch_file').prop('files')[0];   
	    var form_data = new FormData();                  
	    form_data.append('file', file_data);
	    form_data.append('user_connection_id', $upload_file_wrap.find('#user_connection_id').val());
	    $(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: '/newegg/update-batch', // point to server-side PHP script 
			dataType: 'text', // what to expect back from the PHP script
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,
			type: 'post',
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        		$('.be-wrapper').removeClass('be-loading-active');
				response = JSON.parse(response);
				if(response.success) {
					$("#newegg_ajax_msg").html(response.message);
                    $("#newegg_ajax_header_msg").html('Success!');
                    $('#newegg_ajax_request').modal('show');
				} else {
					$("#newegg_ajax_msg_eror").html(response.message);
                    $("#newegg_ajax_header_error_msg").html('Error!');
                    $('#newegg_ajax_error_modal').modal('show');
				}
			},
			error: function (response) {
				console.log(response); // display error response from the PHP script
			}
		});
	});

	var $newegg_form = $('#newegg_connection_form');
	$newegg_form.find('button').on('click', function(event) {
		event.preventDefault();
		data = $newegg_form.data("yiiActiveForm");
		$.each(data.attributes, function() {
		    this.status = 3;
		});
		$newegg_form.yiiActiveForm("validate");

		if($newegg_form.find('.has-error').length) {
			return false;
		}
		var file_data = $newegg_form.find('input[type="file"]').prop('files')[0];
		var form_data = new FormData();
        form_data.append('NeweggConnectionForm[item_file]', file_data);

		for (var item of $newegg_form.serializeArray()) {
			if(item.name != 'NeweggConnectionForm[item_file]') {
				form_data.append(item.name, item.value);
			}
		}
		$(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: $newegg_form.attr('action'),
			type: $newegg_form.attr('method'),
			data: form_data,
            cache: false,
            contentType: false,
            processData: false,
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        		$('.be-wrapper').removeClass('be-loading-active');
				response = JSON.parse(response);
				if(response.success) {
					$("#newegg_ajax_msg").html(response.message);
                    $("#newegg_ajax_header_msg").html('Success!');
                    $newegg_form.hide();
                    $('#newegg_ajax_request').modal('show');
				} else {
					$("#newegg_ajax_msg_eror").html(response.message);
                    $("#newegg_ajax_header_error_msg").html('Error!');
                    $('#newegg_ajax_error_modal').modal('show');
				}
			},
			error: function(error) {
				$(".be-loading").first().removeClass("be-loading-active");
				$('.be-wrapper').removeClass('be-loading-active');
				console.log(error.responseText);
				$("#newegg_ajax_msg_eror").html('internal server error');
                $("#newegg_ajax_header_error_msg").html('Error!');
                $('#newegg_ajax_error_modal').modal('show');
			}
		});
	});

	$('.newegg_error_modal_close').on('click', function () {
        $('#newegg_ajax_error_modal').modal('hide');
    });

    $('.newegg_modal_close').on('click', function () {
        $('#newegg_ajax_request').modal('hide');
        window.location = '/';
    });
});