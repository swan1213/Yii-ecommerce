$(function () {
	var $amazon_form = $('#amazon_connection_form');
	$amazon_form.find('button').on('click', function(event) {
		event.preventDefault();
		data = $amazon_form.data("yiiActiveForm");
		$.each(data.attributes, function() {
		    this.status = 3;
		});
		$amazon_form.yiiActiveForm("validate");

		if($amazon_form.find('.has-error').length) {
			return false;
		}
		$(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: $amazon_form.attr('action'),
			type: $amazon_form.attr('method'),
			data: $amazon_form.serialize(),
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        		$('.be-wrapper').removeClass('be-loading-active');
				response = JSON.parse(response);
				if(response.success) {
					$("#amazon_ajax_msg").html(response.message);
                    $("#amazon_ajax_header_msg").html('Success!');
                    $amazon_form.hide();
                    $('#amazon_ajax_request').modal('show');
				} else {
					$("#amazon_ajax_msg_eror").html(response.message);
                    $("#amazon_ajax_header_error_msg").html('Error!');
                    $('#amazon_ajax_error_modal').modal('show');
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

	$('.amazon_error_modal_close').on('click', function () {
        $('#amazon_ajax_error_modal').modal('hide');
    });

    $('.amazon_modal_close').on('click', function () {
        $('#amazon_ajax_request').modal('hide');
    });

    $('#amazon_ajax_request').on('hidden.bs.modal', function() {
		window.location = '/';
	});
});