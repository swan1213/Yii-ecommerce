$(function () {
	var $wechat_form = $('#wechat_connection_form');
	$wechat_form.find('button').on('click', function(event) {
		event.preventDefault();
		data = $wechat_form.data("yiiActiveForm");
		$.each(data.attributes, function() {
		    this.status = 3;
		});
		$wechat_form.yiiActiveForm("validate");

		if($wechat_form.find('.has-error').length) {
			return false;
		}
		$(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: $wechat_form.attr('action'),
			type: $wechat_form.attr('method'),
			data: $wechat_form.serialize(),
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        		$('.be-wrapper').removeClass('be-loading-active');
				response = JSON.parse(response);
				if(response.success) {
					$("#wechat_ajax_msg").html(response.message);
                    $("#wechat_ajax_header_msg").html('Success!');
                    $wechat_form.hide();
                    $('#wechat_ajax_request').modal('show');
				} else {
					$("#wechat_ajax_msg_eror").html(response.message);
                    $("#wechat_ajax_header_error_msg").html('Error!');
                    $('#wechat_ajax_error_modal').modal('show');
				}
			},
			error: function() {
				$(".be-loading").first().removeClass("be-loading-active");
				$('.be-wrapper').removeClass('be-loading-active');
				console.log('internal server error');
			}
		});
	});

	var $walkthechat = $('#walkthechat_connection_form');
	$walkthechat.find('button').on('click', function(event) {
		event.preventDefault();
		data = $walkthechat.data("yiiActiveForm");
		$.each(data.attributes, function() {
		    this.status = 3;
		});
		$walkthechat.yiiActiveForm("validate");

		if($walkthechat.find('.has-error').length) {
			return false;
		}
		$(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: $walkthechat.attr('action'),
			type: $walkthechat.attr('method'),
			data: $walkthechat.serialize(),
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        		$('.be-wrapper').removeClass('be-loading-active');
				response = JSON.parse(response);
				if(response.success) {
					$("#wechat_ajax_msg").html(response.message);
                    $("#wechat_ajax_header_msg").html('Success!');
                    $walkthechat.hide();
                    $('#wechat_ajax_request').modal('show');
				} else {
					$("#wechat_ajax_msg_eror").html(response.message);
                    $("#wechat_ajax_header_error_msg").html('Error!');
                    $('#wechat_ajax_error_modal').modal('show');
				}
			},
			error: function() {
				$(".be-loading").first().removeClass("be-loading-active");
				$('.be-wrapper').removeClass('be-loading-active');
				console.log('internal server error');
			}
		});
	});

	$('.wechat_error_modal_close').on('click', function () {
        $('#wechat_ajax_error_modal').modal('hide');
    });

    $('.wechat_modal_close').on('click', function () {
        $('#wechat_ajax_request').modal('hide');
        window.location = '/';
    });
});