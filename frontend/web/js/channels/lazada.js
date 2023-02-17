$(function () {
	var $lazada_form = $('#lazada_connection_form');
	$lazada_form.find('button').on('click', function(event) {
		event.preventDefault();
		data = $lazada_form.data("yiiActiveForm");
		$.each(data.attributes, function() {
		    this.status = 3;
		});
		$lazada_form.yiiActiveForm("validate");

		if($lazada_form.find('.has-error').length) {
			return false;
		}
		$(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: $lazada_form.attr('action'),
			type: $lazada_form.attr('method'),
			data: $lazada_form.serialize(),
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        		$('.be-wrapper').removeClass('be-loading-active');
				response = JSON.parse(response);
				if(response.success) {
					$("#lazada_ajax_msg").html(response.message);
                    $("#lazada_ajax_header_msg").html('Success!');
                    $lazada_form.hide();
                    $('#lazada_ajax_request').modal('show');
				} else {
					$("#lazada_ajax_msg_eror").html(response.message);
                    $("#lazada_ajax_header_error_msg").html('Error!');
                    $('#lazada_ajax_error_modal').modal('show');
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

	$('.lazada_error_modal_close').on('click', function () {
        $('#lazada_ajax_error_modal').modal('hide');
    });

    $('.lazada_modal_close').on('click', function () {
        $('#lazada_ajax_request').modal('hide');
    });

    $('#lazada_ajax_request').on('hidden.bs.modal', function() {
		window.location = '/';
	});
});