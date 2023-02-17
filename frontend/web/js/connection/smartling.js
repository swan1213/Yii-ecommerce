$(document).ready(function () {
    if ($("#channelsmartlingyes").length > 0) {
        var atLeastOneIsChecked = $('#channelsmartlingyes:checkbox:checked').length > 0;
        if (atLeastOneIsChecked == true) {
            $("#channel_id_translation_data").css('display', 'block');
        } else {
            $("#channel_id_translation_data").css('display', 'none');
            $(".be-wrapper").removeClass("be-loading-active");
        }
    }

});
function sticky_ntf_for_translation(msg) {
    $.extend($.gritter.options, {position: 'top-right'});
    $.gritter.add({
        title: 'Success',
        text: msg,
        image: '../img/elliot-logo-small.svg',
        class_name: 'clean color success',
        time: '10000'
    });
    return false;
}

$('#channelsmartlingyes').click(function () {
    if ($('#channel_id_translation_data').css('display') == 'block') {
        $('#channel_id_translation_data').css('display', 'none');
        $(".be-wrapper").removeClass("be-loading-active");
    } else {
        $('#channel_id_translation_data').css('display', 'block');
    }
});

$('#channelsmartlingyes').change(function () {
    if ($('#channelsmartlingyes').is(':checked')) {
        var channelsmartlingyes = 'yes';
    } else {
        var channelsmartlingyes = 'no';
    }
    if ($('#smartlingyes').is(":checked"))
    {
        var smartlingyes = 'yes';
    } else {
        var smartlingyes = 'no';
    }
    var language = $("#selectTranslationsetting option:selected").text();
    var selectTranslation = $("#selectTranslation option:selected").text();
    if (channelsmartlingyes == 'no') {
        $.ajax({
            type: 'post',
            url: '/smartling/translationchannelsettingdisable/',
            data: {
                enable: channelsmartlingyes,
                smartlingyes: smartlingyes,
                user_connection_id: user_connection_id,
                selectTranslation: selectTranslation,
                language: language,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
                document.getElementById("smartlingyes").checked = false;
                $('.translatoinfulfillment12-success').show();
            },
        });
    }
});

// $(".smartling-button").click(function (e) {
//     //General Step Fields
//     var smrt_project_id = $('#smrt_project_id input').val();
//     var smrt_user_id = $('#smrt_user_id input').val();
//     var smrt_secret_key = $('#smrt_secret_key input').val();
//     //General Step Required Fields Validation
//     var isValid = true;
//     if (smrt_project_id == '') {
//         $('#smrt_project_id').addClass('has-error');
//         isValid = false;
//     } else {
//         if ($('#smrt_project_id').hasClass('has-error')) {
//             $('#smrt_project_id').removeClass('has-error');
//         }
//     }
//
//     if (smrt_user_id == '') {
//         $('#smrt_user_id').addClass('has-error');
//         isValid = false;
//     } else {
//         if ($('#smrt_user_id').hasClass('has-error')) {
//             $('#smrt_user_id').removeClass('has-error');
//         }
//     }
//
//     if (smrt_secret_key == '') {
//         $('#smrt_secret_key').addClass('has-error');
//         isValid = false;
//     } else {
//         if ($('#smrt_secret_key').hasClass('has-error')) {
//             $('#smrt_secret_key').removeClass('has-error');
//         }
//     }
//
//     if (isValid) {
//         $.ajax({
//             type: 'post',
//             url: 'save-smartling-api',
//             data: {
//                 smrt_project_id: smrt_project_id,
//                 smrt_user_id: smrt_user_id,
//                 smrt_secret_key: smrt_secret_key,
//             },
//             success: function () {
//                 window.location = "integrate-transaltion";
//             },
//         });
//     }
//     e.preventDefault();
// });

$('#translation_corporate_id').click(function () {
    if ($('#channelsmartlingyes').is(':checked')) {
        var channelsmartlingyes = 'yes';
    } else {
        var channelsmartlingyes = 'no';
    }
    if ($('#smartlingyes').is(":checked"))
    {
        var smartlingyes = 'yes';
    } else {
        var smartlingyes = 'no';
    }

    if (smartlingyes == 'no') {
        alert('Please Accept terms and conditions');
        return false;
    }

    var language = $("#selectTranslationsetting option:selected").text();
    var selectTranslation = $("#selectTranslation option:selected").val();

    if(selectTranslation=="Google MT"){
        $('.google-machine-transaltion-modal').modal('show');
    }
    else{
        if (channelsmartlingyes == 'yes') {
            $.ajax({
                type: 'post',
                url: '/smartling/translationchannelcost/',
                data: {
                    enable: channelsmartlingyes,
                    smartlingyes: smartlingyes,
                    user_connection_id: user_connection_id,
                    selectTranslation: selectTranslation,
                    language: language,
                },
                beforeSend: function (xhr) {
                    $(".be-wrapper").addClass("be-loading-active");
                },
                success: function (value) {
                    $(".be-wrapper").removeClass("be-loading-active");
                    $('.translatoinfulfillment-success').show();
                    $('#priceShow').html('Translation Cost - $' + value + '/-');
                    $('#getValuePrice').html(value);
                },
            });
        }
    }
});

$('.smartling_enble_controller_google_machine').click(function(){
    $('.google-machine-transaltion-modal .credit_card_modal').addClass('be-loading-active');
    if ($('#channelsmartlingyes').is(':checked')) {
        var channelsmartlingyes = 'yes';
    }
    else {
        var channelsmartlingyes = 'no';
        alert('Please Enable Smartling.');
        return false;
    }
    if ($('#smartlingyes').is(":checked")){
        var smartlingyes = 'yes';
    }
    else {
        var smartlingyes = 'no';
        alert('Please Accept terms and conditions');
        return false;
    }

    var credit = 0;
    var language = $("#selectTranslationsetting option:selected").text();
    var selectTranslation = $("#selectTranslation option:selected").val();

    $.ajax({
        type: 'post',
        url: '/smartling/translationchannelsetting/',
        data: {
            enable: channelsmartlingyes,
            smartlingyes: smartlingyes,
            user_connection_id: user_connection_id,
            selectTranslation: selectTranslation,
            language: language,
            credit: credit,
        },
        beforeSend: function (xhr) {
            $('.translatoinfulfillment-success .credit_card_modal').addClass('be-loading-active');
        },
        success: function (value) {
            $('.google-machine-transaltion-modal .credit_card_modal').removeClass('be-loading-active');
            $('.google-machine-transaltion-modal').hide();
            $('.alldataadd').html(value);
            $('.translatoinfulfillment1-success').show();
        },
    });


});

$('body').on('click', '.smartling_enble_controller', function () {
    $('.translatoinfulfillment-success .credit_card_modal').addClass('be-loading-active');
    checkCredit = $('#checkCredit').val();
    if (checkCredit == '1') {
        var creditemail = $('#creditemail').val();
        var creditcart = $('#creditcart').val();
        var creditdate = $('#creditdate').val();
        var creditcvc = $('#creditcvc').val();

        if (creditemail == ''){
            $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
            $('#smartling_creditemail').addClass('has-error');
            return false;
        }
        else{
            if ($('#smartling_creditemail').hasClass('has-error')) {
                $('#smartling_creditemail').removeClass('has-error');
            }
        }
        if (creditcart == ''){
            $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
            $('#smartling_creditcart').addClass('has-error');
            return false;
        }
        else{
            if ($('#smartling_creditcart').hasClass('has-error')) {
                $('#smartling_creditcart').removeClass('has-error');
            }
        }
        if (creditdate == ''){
            $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
            $('#smartling_creditdate').addClass('has-error');
            return false;
        }
        else{
            if ($('#smartling_creditdate').hasClass('has-error')) {
                $('#smartling_creditdate').removeClass('has-error');
            }
        }
        if (creditcvc == ''){
            $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
            $('#smartling_creditcvc').addClass('has-error');
            return false;
        }
        else{
            if ($('#smartling_creditcvc').hasClass('has-error')) {
                $('#smartling_creditcvc').removeClass('has-error');
            }
        }


        if ($('#channelsmartlingyes').is(':checked')) {
            var channelsmartlingyes = 'yes';
        } else {
            var channelsmartlingyes = 'no';
        }
        if ($('#smartlingyes').is(":checked"))
        {
            var smartlingyes = 'yes';
        } else {
            var smartlingyes = 'no';
        }

        var credit = 0;
        var total_value_cost = $(this).attr('total_value_cost');
        var total_word_count = $(this).attr('total_word_count');
        var price_per_word = $(this).attr('price_per_word');
        var language = $("#selectTranslationsetting option:selected").text();
        var selectTranslation = $("#selectTranslation option:selected").val();
        if (channelsmartlingyes == 'yes') {
            $('.smartling_enble_controller').html('Hold For A While');
            $.ajax({
                type: 'post',
                url: '/dashboard/subscriptionpaymentcustom',
                data: {
                    enable: channelsmartlingyes,
                    smartlingyes: smartlingyes,
                    user_connection_id: user_connection_id,
                    selectTranslation: selectTranslation,
                    language: language,
                    credit: credit,
                    creditemail: creditemail,
                    creditcart: creditcart,
                    creditdate: creditdate,
                    creditcvc: creditcvc,
                    total_value_cost: total_value_cost,
                    price_per_word: price_per_word,
                    total_word_count: total_word_count,
                },
                beforeSend: function (xhr) {
                    $('.translatoinfulfillment-success .credit_card_modal').addClass('be-loading-active');
                },
                success: function (value) {
                    if (value = 'succeeded') {
                        $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
                        $.ajax({
                            type: 'post',
                            url: '/smartling/translationchannelsetting/',
                            data: {
                                enable: channelsmartlingyes,
                                smartlingyes: smartlingyes,
                                user_connection_id: user_connection_id,
                                selectTranslation: selectTranslation,
                                language: language,
                                credit: credit,
                            },
                            beforeSend: function (xhr) {
                                $('.translatoinfulfillment-success .credit_card_modal').addClass('be-loading-active');
                            },
                            success: function (value) {
                                $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
                                $('.translatoinfulfillment-success').hide();
                                $('.alldataadd').html(value);
                                $('.translatoinfulfillment1-success').show();
                            },
                        });

                    }
                },
                error: function (error) {
                    $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
                    alert('Enter Valid Card Details');
                }
            });
        }
    } else {
        var credit = 1;
        if ($('#channelsmartlingyes').is(':checked')) {
            var channelsmartlingyes = 'yes';
        } else {
            var channelsmartlingyes = 'no';
        }
        if ($('#smartlingyes').is(":checked"))
        {
            var smartlingyes = 'yes';
        } else {
            var smartlingyes = 'no';
        }

        var total_value_cost = $(this).attr('total_value_cost');
        var total_word_count = $(this).attr('total_word_count');
        var price_per_word = $(this).attr('price_per_word');
        var channel_id_translation = $('#channel_id_translation').val();
        var language = $("#selectTranslationsetting option:selected").text();
        var selectTranslation = $("#selectTranslation option:selected").val();
        if (channelsmartlingyes == 'yes') {
            $('.smartling_enble_controller').html('Hold For A While');
            $.ajax({
                type: 'post',
                url: '/dashboard/subscriptionpaymentcustom',
                data: {
                    enable: channelsmartlingyes,
                    smartlingyes: smartlingyes,
                    user_connection_id: user_connection_id,
                    selectTranslation: selectTranslation,
                    language: language,
                    credit: credit,
                    customer_token: 1,
                    total_value_cost: total_value_cost,
                    price_per_word: price_per_word,
                    total_word_count: total_word_count,
                },
                beforeSend: function (xhr) {
                    $('.translatoinfulfillment-success .credit_card_modal').addClass('be-loading-active');
                },
                success: function (value) {
                    if (value = 'succeeded') {
                        $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
                        $.ajax({
                            type: 'post',
                            url: '/smartling/translationchannelsetting/',
                            data: {
                                enable: channelsmartlingyes,
                                smartlingyes: smartlingyes,
                                user_connection_id: user_connection_id,
                                selectTranslation: selectTranslation,
                                language: language,
                                credit: credit,
                            },
                            beforeSend: function (xhr) {
                                $('.translatoinfulfillment-success .credit_card_modal').addClass('be-loading-active');
                            },
                            success: function (value) {
                                $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
                                $('.translatoinfulfillment-success').hide();
                                $('.alldataadd').html(value);
                                $('.translatoinfulfillment1-success').show();
                            },
                        });

                    }
                },
                error: function (error) {
                    $('.translatoinfulfillment-success .credit_card_modal').removeClass('be-loading-active');
                    alert('Enter Valid Card Details');
                }
            });
        }
    }
});

// $('.translationwizard').click(function () {
//     var translation_type = $('#select2-selectTranslation-container').text();
//     if ($('#smartlingyes').is(':checked')) {
//         var smartlingyes = 'yes';
//         $('.translationmod-success').show();
//     } else {
//         var smartlingyes = 'no';
//         $('.translationmod-error').show();
//
//     }
//     var proidval = $('#proidunique').val();
//     $('.be-wrapper').addClass('be-loading-active');
//     $.ajax({
//         type: 'post',
//         url: "/channelsetting/translationsave",
//         data: {
//             translation_type: translation_type,
//             smartlingyes: smartlingyes,
//             proidval: proidval,
//         },
//         beforeSend: function (xhr) {
//             $(".be-wrapper").addClass("be-loading-active");
//         },
//         success: function (value) {
//             $(".be-wrapper").removeClass("be-loading-active");
//         },
//     });
// });

// $("#smartling_form_id").on('submit', (function (e) {
//     e.preventDefault();
//     $('.be-wrapper').addClass('be-loading-active');
//     $.ajax({
//         url: "/smartling/translation",
//         type: "POST",
//         data: new FormData(this),
//         contentType: false,
//         cache: false,
//         processData: false,
//         success: function (data) {
//             $('.be-wrapper').removeClass('be-loading-active');
//         },
//         error: function () {
//         }
//     });
// }));

// $('.smartling-button-jobs').click(function () {
//     var token = $(this).val();
//     $('#proidunique').remove();
//     $('.proidget').append('<input type="hidden" value="' + token + '" id="proidunique" />');
//     $.ajax({
//         type: 'post',
//         url: '/smartling/jobsetting/',
//         data: {
//             token: token,
//         },
//         beforeSend: function (xhr) {
//             $(".be-wrapper").addClass("be-loading-active");
//         },
//         success: function (value) {
//             $(".be-wrapper").removeClass("be-loading-active");
//             $("#smartjob1").removeClass("active");
//             $("#smartjobli1").removeClass("active");
//             $("#smartjobli1").addClass("complete");
//             $("#smartjob2").addClass("active");
//             $("#smartjobli2").addClass("active");
//         },
//     });
// });