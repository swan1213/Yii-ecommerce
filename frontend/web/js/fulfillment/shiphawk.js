$(function () {
	var $shiphawk_form = $('#shiphawk_connection_form');
	$shiphawk_form.find('button').on('click', function(event) {
		event.preventDefault();
		data = $shiphawk_form.data("yiiActiveForm");
		$.each(data.attributes, function() {
		    this.status = 3;
		});
		$shiphawk_form.yiiActiveForm("validate");

		if($shiphawk_form.find('.has-error').length) {
			return false;
		}
		$(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: $shiphawk_form.attr('action'),
			type: $shiphawk_form.attr('method'),
			data: $shiphawk_form.serialize(),
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        		$('.be-wrapper').removeClass('be-loading-active');
				response = JSON.parse(response);
				if(response.success) {
					$("#shiphawk_ajax_msg").html(response.message);
                    $("#shiphawk_ajax_header_msg").html('Success!');
                    $shiphawk_form.hide();
                    $('#shiphawk_ajax_request').modal('show');
				} else {
					$("#shiphawk_ajax_msg_eror").html(response.message);
                    $("#shiphawk_ajax_header_error_msg").html('Error!');
                    $('#shiphawk_ajax_error_modal').modal('show');
				}
			},
			error: function(error) {
				$(".be-loading").first().removeClass("be-loading-active");
				$('.be-wrapper').removeClass('be-loading-active');
				console.log('internal server error');
				console.log(error)
			}
		});
	});

	$('.shiphawk_error_modal_close').on('click', function () {
        $('#shiphawk_ajax_error_modal').modal('hide');
    });

    $('.shiphawk_modal_close').on('click', function () {
        $('#shiphawk_ajax_request').modal('hide');
        window.location = '/';
    });
});