$(function () {
	var $square_form = $('#square_connection_form');
	$square_form.find('button').on('click', function(event) {
		event.preventDefault();
		data = $square_form.data("yiiActiveForm");
		$.each(data.attributes, function() {
		    this.status = 3;
		});
		$square_form.yiiActiveForm("validate");

		if($square_form.find('.has-error').length) {
			return false;
		}
		$(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: $square_form.attr('action'),
			type: $square_form.attr('method'),
			data: $square_form.serialize(),
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        		$('.be-wrapper').removeClass('be-loading-active');
        		console.log(response);
				response = JSON.parse(response);
				if(response.success) {
					$("#square_ajax_msg").html(response.message);
                    $("#square_ajax_header_msg").html('Success!');
                    $square_form.hide();
                    $('#square_ajax_request').modal('show');
				} else {
					$("#square_ajax_msg_eror").html(response.message);
                    $("#square_ajax_header_error_msg").html('Error!');
                    $('#square_ajax_error_modal').modal('show');
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

	var $square_publish_form = $('#square_publish_form');
	$square_publish_form.find('button').on('click', function(event) {
		event.preventDefault();
		data = $square_publish_form.data("yiiActiveForm");
		$.each(data.attributes, function() {
		    this.status = 3;
		});
		$square_publish_form.yiiActiveForm("validate");

		if($square_publish_form.find('.has-error').length) {
			return false;
		}
		$(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: $square_publish_form.attr('action'),
			type: $square_publish_form.attr('method'),
			data: $square_publish_form.serialize(),
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        $('.be-wrapper').removeClass('be-loading-active');
				response = JSON.parse(response);
				if(response.success) {
					$("#square_ajax_msg").html(response.message);
					$("#square_ajax_header_msg").html('Success!');
					$('#square_ajax_request').modal('show');
				} else {
					$("#square_ajax_msg_eror").html(response.message);
					$("#square_ajax_header_error_msg").html('Error!');
					$('#square_ajax_error_modal').modal('show');
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

	$('.square_error_modal_close').on('click', function () {
        $('#square_ajax_error_modal').modal('hide');
    });

    $('.square_modal_close').on('click', function () {
        $('#square_ajax_request').modal('hide');
    });

    $('#square_ajax_request').on('hidden.bs.modal', function() {
		window.location = '/';
	});
});