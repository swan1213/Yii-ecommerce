$(function () {
	var $fetch_date_element = $('.googleshopping-feed-container .fetch-date-wrap');
	var $fetch_weekday_element = $('.googleshopping-feed-container .fetch-weekday-wrap');
	$fetch_date_element.hide();
	$fetch_weekday_element.hide();

	$("#google_shopping_confirm_dialog").dialog({
		autoOpen: false,
		modal: true
	});

	var $google_shopping_form = $('#google_shopping_datafeed_form');
	$google_shopping_form.find('button').on('click', function(event) {
		event.preventDefault();
		data = $google_shopping_form.data("yiiActiveForm");
		$.each(data.attributes, function() {
		    this.status = 3;
		});
		$google_shopping_form.yiiActiveForm("validate");

		if($google_shopping_form.find('.has-error').length) {
			return false;
		}
		$(".be-loading").first().addClass("be-loading-active");

		$.ajax({
			url: $google_shopping_form.attr('action'),
			type: $google_shopping_form.attr('method'),
			data: $google_shopping_form.serialize(),
			success: function (response) {
				$(".be-loading").first().removeClass("be-loading-active");
        		$('.be-wrapper').removeClass('be-loading-active');
				response = JSON.parse(response);
				if(response.success) {
					$google_shopping_form.find('.error-wrap').hide();
					var id = $google_shopping_form.find('#google_shopping_id').val();
					window.location = '/googleshopping?id=' + id;
				} else {
					$google_shopping_form.find('.error-wrap').show();
					if (response.message instanceof Array) {
						$google_shopping_form.find('.error-wrap').html(Object.values(response.message)[0]);	
					} else {
						$google_shopping_form.find('.error-wrap').html(response.message);
					}
					
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

	var connection_id = $('#google_shopping_connection_id').val();

	$('#google_shopping_setting').on('click', function() {
		var is_check = $('#google_shopping_setting').is(':checked') ? 'yes' : 'no';

		$.ajax({
            type: 'post',
            url: '/googleshopping/savechannel/',
            data: {
                is_check: is_check,
                connection_id: connection_id
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (response) {
                $(".be-wrapper").removeClass("be-loading-active");
                console.log(response);
                response = JSON.parse(response);
				if(response.success) {
					$("#google_shopping_ajax_msg").html(response.message);
                    $("#google_shopping_ajax_header_msg").html('Success!');
                    $('#google_shopping_ajax_request').modal('show');
				} else {
					$("#google_shopping_ajax_msg_eror").html(response.message);
                    $("#google_shopping_ajax_header_error_msg").html('Error!');
                    $('#google_shopping_ajax_error_modal').modal('show');
				}
            }
        });
	});

	$('.google_shopping_error_modal_close').on('click', function () {
        $('#google_shopping_ajax_error_modal').modal('hide');
    });

    $('.google_shopping_modal_close').on('click', function () {
        $('#google_shopping_ajax_request').modal('hide');
        location.reload();
    });

    $("#google_shopping_feed_list").dataTable({
    	responsive: true,
    	pageLength: 10,
    	"oLanguage": {
            sProcessing: function () {
                $(".be-loading").first().addClass("be-loading-active");

            }
        },
        "initComplete": function () {
            $(".be-loading").first().removeClass("be-loading-active");

        },
        processing: true,
    	ajax: "/googleshopping/feeds-ajax?id=" + connection_id,
    });
});

// automatically set the available languages depending on the country
function onChangeTargetCountry(event) {
	var available_languages = {
		'AR' : {es: 'Spanish'},
        'AU' : {en: 'English',zh: 'Chinese'},
        'AT' : {de: 'German', en: 'English'},
        'BE' : {en: 'English', fr: 'French', nl: 'Dutch'},
        'BR' : {pt: 'Portuguese'},
        'CA' : {en: 'English', fr: 'French', zh: 'Chinese'},
        'CL' : {es: 'Spanish'},
        'CN' : {zh: 'Chinese'},
        'CO' : {es: 'Spanish'},
        'CZ' : {cs: 'Czech', en: 'English'},
        'DK' : {da: 'Danish', en: 'English'},
        'FR' : {fr: 'French'},
        'DE' : {de: 'German', en: 'English'},
        'HK' : {en: 'English', zh: 'Chinese'},
        'IN' : {en: 'English'},
        'ID' : {en: 'English', id: 'Indonesian'},
        'IE' : {en: 'English'},
        'IT' : {it: 'Italian'},
        'JP' : {ja: 'Japanese'},
        'MY' : {en: 'English', ms: 'Malay', zh: 'Chinese'},
        'MX' : {en: 'English', es: 'Spanish'},
        'NL' : {en: 'English', nl: 'Dutch'},
        'NZ' : {en: 'English'},
        'NO' : {en: 'English', no: '	Norwegian'},
        'PH' : {en: 'English', tl: 'Filipino'},
        'PL' : {pl: 'Polish'},
        'PT' : {pt: 'Portuguese'},
        'RW' : {ru: 'Russian'},
        'SG' : {en: 'English', zh: 'Chinese'},
        'ZA' : {en: 'English'},
        'ES' : {es: 'Spanish'},
        'SE' : {en: 'English', sv: 'Swedish'},
        'CH' : {en: 'German', en: 'English', fr: 'French', it: 'Italian'},
        'TW' : {en: 'English', zh: 'Chinese'},
        'TR' : {en: 'English', tr: 'Turkish'},
        'AE' : {ar:'Arabic', en: 'English'},
        'GB' : {en: 'English'},
        'US' : {en: 'English', zh: 'Chinese'}
	};

	$('.googleshopping-feed-container select.feed-language')
		.find('option')
    	.remove();

	if(available_languages.hasOwnProperty(event)) {
		$.each(available_languages[event], function(key, value) {
			$('.googleshopping-feed-container select.feed-language')
				.append($("<option></option>")
					.attr("value",key)
					.text(value));
		});
	}
}

function onChangeFrequency(event) {
	var $fetch_date_element = $('.googleshopping-feed-container .fetch-date-wrap');
	var $fetch_weekday_element = $('.googleshopping-feed-container .fetch-weekday-wrap');
	$fetch_date_element.hide();
	$fetch_weekday_element.hide();

	switch(event) {
		case 'daily':
			$fetch_date_element.hide();
			$fetch_weekday_element.hide();
		break;

		case 'weekly':
			$fetch_date_element.hide();
			$fetch_weekday_element.show();
		break;

		case 'monthly':
			$fetch_date_element.show();
			$fetch_weekday_element.hide();
		break;
	}
}

function onDeleteGoogleFeed(connection_id, feed_id, fetch_url) {
	$("#google_shopping_confirm_dialog").dialog("open");
	$("#google_shopping_confirm_dialog").dialog({
		buttons : {
			"Confirm": function() {
				$(this).dialog("close");
				$(".be-loading").first().addClass("be-loading-active");
				$.ajax({
					url: '/googleshopping/delete-feed',
					type: 'post',
					data: {
						connection_id: connection_id,
						feed_id: feed_id,
						fetch_url: fetch_url
					},
					success: function (response) {
						$(".be-loading").first().removeClass("be-loading-active");
                		$('.be-wrapper').removeClass('be-loading-active');
						console.log(response)
						response = JSON.parse(response);
						if(response.success) {
							location.reload();
						} else {
						}
					},
					error: function() {
						$(".be-loading").first().removeClass("be-loading-active");
        				$('.be-wrapper').removeClass('be-loading-active');
						console.log('internal server error');
					}
				});
			},
			"Cancel": function() {
				$(this).dialog("close");
			}
		}
	});
}