// Regarding channels

$(document).ready(function () {
/// start full fill code -----------------------------------------
    $('#channelfulfillment').click(function () {
        if ($('#channelfulfillment').is(':checked')) {
            $('#newfullfill').show();
            $('#fulfillmentlist').show();
        } else {
            $('#newfullfill').hide();
            $('#fulfillmentlist').hide();
            $('#disable-warning-fulfilled').addClass('in');
            $('#disable-warning-fulfilled').show();
        }
    });

    $('.proceed_todlt_fulfillment').click(function () {
        var fulfillmentID = 0;
        $('#disable-warning-fulfilled').removeClass('in');
        $('#disable-warning-fulfilled').hide();
        $('#fulfillmentlist').hide();
        $.ajax({
            type: 'post',
            url: '/channelsetting/fulfill_save/',
            data: {
                fulfillListID: fulfillmentID,
                user_connection_id: user_connection_id,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
            },
        });

    });

    $(".todlt_fulfillment_close").click(function () {
        $("#disable-warning-fulfilled").css('display', 'none');
    });

    $('#fulfillmentlist').change(function () {
        var fulfillmentID = $('#fulfillmentlist').find(":selected").val();
        if(fulfillmentID==0)
            return;
        if (!fulfillmentID) {
            $("#fulfillmentlist").addClass("inpt_required");
            return false;
        } else {
            $("#fulfillmentlist").removeClass("inpt_required");
        }
        if ($('#channelfulfillment').is(':checked')) {

        } else {
            $('.channelfulfillment-error').show();
            $('#fulfillmentlist').removeAttr('selected').find('option:first').attr('selected', 'selected');
        }

        $.ajax({
            type: 'post',
            url: '/channelsetting/fulfill_save/',
            data: {
                fulfillListID: fulfillmentID,
                user_connection_id: user_connection_id,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {

                if (value == 'error') {
                    $('#walkthechat_request_error').modal('show');
                    $(".be-wrapper").removeClass("be-loading-active");
                } else if (value) {
                    var fulfillmentID = $('#fulfillmentlist').find(":selected").val();
                    if (fulfillmentID == fulfillment_sfexpress_id) {
                        $.ajax({
                            type: 'post',
                            url: '/sfexpress/sforders/',
                            data: {user_connection_id: user_connection_id, fulfillmentID: fulfillmentID},
                            success: function (value) {//alert(value);
                                $(".be-wrapper").removeClass("be-loading-active");
                                if (value == 'success') {
                                    $('.channelfulfillment-success').show();
                                } else {
                                    $('.channelfulfillment-success-error-found').show();
                                    $('.channelfulfillment-success-error-found #magento_ajax_msg').html(value);
                                }
                            }
                        });
                    } else {
                        $('.channelfulfillment-success').show();
                        $(".be-wrapper").removeClass("be-loading-active");
                    }
                }
            },
        });
    });

    $('#channelmappingyes').change(function () {
        if ($('#channelmappingyes').is(':checked')) {
            var channelmappingyes = 'yes';
            //$('#mapping_body').show();
        } else {
            var channelmappingyes = 'no';
            $('#mapping_body').hide();
        }

        $.ajax({
            type: 'post',
            url: '/channelsetting/mapping-enable/',
            data: {
                enable: channelmappingyes,
                user_connection_id: user_connection_id,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
                window.location = '/channelsetting?id=' + u + '&type=' + type + '&u=' + user_connection_id + '&refresh=1';
            },
        });

    });

    $(".channelfulfillment_error_modal_close").click(function () {
        $('.channelfulfillment-error').hide();
    });
    $(".channelfulfillment_modal_close").click(function () {
        $('.channelfulfillment-success').hide();
        $('.channelfulfillment-success-not-found').hide();
        $('.channelfulfillment-success-error-found').hide();
    });
/// end full fill code -----------------------------------------

    // channel disable code start
    $(".chnl_dsbl").click(function () {
        dt = $(this).attr("data-connid");
        c_name = $(this).attr("data-cname");
        c_type = $(this).attr("data-type");
        cls = $(this).attr("data-cls");

        $('#disable-warning').attr("data-cls", cls);
        $(".modalmsg").text(c_name);
        $(".proceed_todlt").attr("data-type", c_type);
        $(".proceed_todlt").attr("data-connid", dt);
        if (!$(this).hasClass("test")) {
            $('#disable-warning').modal('show');
        }
        $(this).addClass('test');

    })
    $('#disable-warning').on('hidden.bs.modal', function () {
        cls = $(this).attr("data-cls");
        $("." + cls + " .chnl_dsbl").click();
        $("." + cls + " .chnl_dsbl").removeClass('test');
    })

    $(".proceed_todlt").click(function () {
        $("#channel_disable .be-spinner span").html('Your data is deleting...');
        $("#disable-warning").modal('hide');
        $("#channel_disable").addClass("be-loading-active");
        $.ajax({
            type: 'post',
            url: '/channelsetting/channeldisable',
            data: {
                user_connection_id: user_connection_id,
            },
            success: function (value) {
                //window.location.reload('/channels');
                $("#channel_disable").removeClass("be-loading-active");
                window.location = '/';
            },
        });
    })
    //-----channel disable code end//

    //translation page code start
    /* Translation tab text editor*/
    App.textEditors = function () {
        //Summernote Description
        $('#translation-description').summernote({
            height: 300
        });
        //Summernote shortdescription
        $('#trans-shortdescription').summernote({
            height: 300
        });
        //Summernote applicationtips
        $('#trans-applicationtips').summernote({
            height: 300
        });
        //Summernote ingredients
        $('#trans-ingredients').summernote({
            height: 300
        });
    };
    /* Translation tab text editor END*/
    $('.credit_card_close').click(function () {
        $('.translatoinfulfillment-success').css('display', 'none');
    });

    $('#selectTranslation').on('change', function () {
        if (this.value == 'Google MT with Edit') {
            $('#humantran').show();
            $('#machinetran').hide();
            $('#Hybrid').hide();
        } else if (this.value == 'Translation with Edit') {
            $('#humantran').hide();
            $('#machinetran').hide();
            $('#Hybrid').show();
        } else {
            $('#humantran').hide();
            $('#machinetran').show();
            $('#Hybrid').hide();
        }
    });

    $('.trans_rate_modal_close').click(function () {
        $('.translationmod-rate').hide();
    });
    $('.trans_error_modal_close').click(function () {
        $('.translationmod-error').hide();

    });
    $('.trans_modal_close').click(function () {
        $('.translationmod-success').hide();

    });
    $('#getRateTable').click(function () {
        $('.translationmod-rate').show();

    });

    $('.translatoinfulfillment_modal_close').click(function () {
        $('.translatoinfulfillment-success').hide();
    });
    $('.translatoinfulfillment12_modal_close').click(function () {
        $('.translatoinfulfillment12-success').hide();
    });
    $('.translatoinfulfillment1_modal_close').click(function () {
        $('.translatoinfulfillment1-success').hide();
        window.location.reload();
    });

    // $(".swt99").each(function () {
    //     if ($(this).is(":checked")) {
    //         var conn_id = $(this).attr("data-connectionid");
    //         $('#smartling_editor' + conn_id).summernote({
    //             width: 1000, //don't use px
    //         });
    //         $(".div_translate_title" + conn_id).show();
    //     }
    // });
    $('.trans_title').editable();
    $('.trans_brand').editable();
    $('.smartling_editor').summernote('code');
    // $('.swt99').change(function () {
    //
    //     if ($(this).is(":checked"))
    //     {
    //         var connection_id = $(this).attr("data-connectionid");
    //         var channel_acc = $(this).attr("data-channel");
    //         var product_id = $(this).attr("data-productid");
    //         $.ajax({
    //             type: 'post',
    //             url: '/products/smarting-translation-status-enable',
    //             data: {
    //                 connection_id: connection_id,
    //                 channel_acc: channel_acc,
    //                 product_id: product_id,
    //             },
    //
    //             success: function (value) {
    //                 if (value == 'success') {
    //                     $('#smartling_editor'+connection_id).summernote({
    //                         width: 1000, //don't use px
    //                     });
    //                     $(".div_translate_title"+connection_id).show();
    //                     $('.smartling_editor'+connection_id).next().show();
    //                 }
    //             },
    //         });
    //     } else {
    //         var connection_id = $(this).attr("data-connectionid");
    //         var channel_acc = $(this).attr("data-channel");
    //         var product_id = $(this).attr("data-productid");
    //         $.ajax({
    //             type: 'post',
    //             url: '/products/smarting-translation-status-disable',
    //             data: {
    //                 connection_id: connection_id,
    //                 channel_acc: channel_acc,
    //                 product_id: product_id,
    //             },
    //
    //             success: function (value) {
    //                 if (value == 'success') {
    //                     $(".div_translate_title"+connection_id).hide();
    //                     $('.smartling_editor'+connection_id).next().hide();
    //                 }
    //             },
    //         });
    //     }
    // });


    if ($("#channelsmartlingyes").length > 0) {
        var atLeastOneIsChecked = $('#channelsmartlingyes:checkbox:checked').length > 0;
        if (atLeastOneIsChecked == true) {
            $("#channel_id_translation_data").css('display', 'block');
        } else {
            $("#channel_id_translation_data").css('display', 'none');
            $(".be-wrapper").removeClass("be-loading-active");
        }
        //Channel
    }

    // Custom mapping
    var selected_index = -1;
    var selected_elliot_id = '';
    var selected_store_id = '';
    var selected_elliot_val = '';
    var selected_store_val = '';

    $(document).on('click','.mapped_elliot_label',function(e) {
        e.preventDefault();
        selected_elliot_id = $(this).attr('id');
        selected_elliot_val = $(this).text();
        var parent = $(this).parent();
        for (i = 0 ; i < parent.children().length ; i ++) {
            if ($(parent.children().get(i)).attr('id') == selected_elliot_id) {
                selected_index = i;
                break;
            }
        }
        var store = $('#store_label').children().get(selected_index);
        selected_store_val = $(store).text();
        selected_store_id = $(store).attr('id');

        if ($(this).css('border') == '1px solid rgb(234, 67, 85)') {
            $(this).css('border', '');
            $(store).css('border', '');
            selected_index = - 1
        }
        else {
            for (i = 0 ; i < $('#elliot_label').children().length ; i ++) {
                $($('#elliot_label').children().get(i)).css('border', '');
            }
            for (i = 0 ; i < $('#store_label').children().length ; i ++) {
                $($('#store_label').children().get(i)).css('border', '');
            }
            $(this).css('border', '1px solid rgb(234, 67, 85)');
            $(store).css('border', '1px solid rgb(234, 67, 85)');
        }

        if (selected_index == -1) {
            $('.mapping_delete').hide()
        }
        else {
            $('.mapping_delete').show()
        }
    });

    $(document).on('click','.mapped_store_label',function(e) {
        e.preventDefault();
        selected_store_id = $(this).attr('id');
        selected_store_val = $(this).text();
        var parent = $(this).parent();
        for (i = 0 ; i < parent.children().length ; i ++) {
            if ($(parent.children().get(i)).attr('id') == selected_store_id) {
                selected_index = i;
                break;
            }
        }
        var elliot = $('#elliot_label').children().get(selected_index);
        selected_elliot_val = $(elliot).text();
        selected_elliot_id = $(elliot).attr('id');

        if ($(this).css('border') == '1px solid rgb(234, 67, 85)') {
            $(this).css('border', '');
            $(elliot).css('border', '');
            selected_index = - 1
        }
        else {
            for (i = 0 ; i < $('#elliot_label').children().length ; i ++) {
                $($('#elliot_label').children().get(i)).css('border', '');
            }
            for (i = 0 ; i < $('#store_label').children().length ; i ++) {
                $($('#store_label').children().get(i)).css('border', '');
            }
            $(this).css('border', '1px solid rgb(234, 67, 85)');
            $(elliot).css('border', '1px solid rgb(234, 67, 85)');
        }

        if (selected_index == -1) {
            $('.mapping_delete').hide()
        }
        else {
            $('.mapping_delete').show()
        }
    });

    $( ".mapping_delete" ).on( "click", function() {

        $($('#elliot_label').children().get(selected_index)).remove();
        $($('#store_label').children().get(selected_index)).remove();
        $("#elliot_attribute_list optgroup").append("<option value='" + selected_elliot_id + "'>" + selected_elliot_val + "</option>");
        $("#store_attribute_list optgroup").append("<option value='" + selected_store_id + "'>" + selected_store_val + "</option>");
        $('.mapping_delete').hide();
        if ($('#elliot_label').children().length == 0) {
            $("#product_feed_down").hide();
            $("#product_feed_save").hide();
        }

        $.ajax({
            type: 'post',
            url: '/channelsetting/mapping-delete',
            data: {
                elliot_id: selected_elliot_id,
                store_id: selected_store_id,
                user_connection_id: user_connection_id,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
                if (value == 'success') {

                }
                else {

                }
            },
        });
    });

    $( ".mapping_mapped" ).on( "click", function() {
    //$(".mapping_mapped").click(function(e){
        var m_selected_elliot_id = $('#elliot_attribute_list').find(":selected");
        var m_selected_store_id = $('#store_attribute_list').find(":selected");

        var isValid = true;
        if (m_selected_elliot_id.val() == 0 || m_selected_elliot_id.val() == undefined) {
            isValid = false;
            $("#mapping_ajax_header_error_msg").html("Please select Elliot Attribute!");
            $('.mapping_ajax_request_error').modal('show');
            return false;
        }
        if (m_selected_store_id.val() == 0  || m_selected_store_id.val() == undefined) {
            isValid = false;
            $("#mapping_ajax_header_error_msg").html("Please select Channel Attribute!");
            $('.mapping_ajax_request_error').modal('show');
            return false;
        }

        $("#product_feed_down").show();
        $("#product_feed_save").show();
        $("#elliot_label").append("<li class='mapped_elliot_label' id='" + m_selected_elliot_id.val() + "' value='" + m_selected_elliot_id.text().trim() + "'><a>" + m_selected_elliot_id.text().trim() + "</a></li>");
        $("#store_label").append("<li class='mapped_store_label' id='" + m_selected_store_id.val() + "' value='" + m_selected_store_id.text().trim() + "'><a>" + m_selected_store_id.text().trim() + "</a></li>");

        m_selected_elliot_id.detach();
        m_selected_store_id.detach();
        $("#elliot_attribute_list").trigger("change");
        $("#store_attribute_list").trigger("change");

        $.ajax({
            type: 'post',
            url: '/channelsetting/mapping',
            data: {
                elliot_id: m_selected_elliot_id.val(),
                store_id: m_selected_store_id.val(),
                user_connection_id: user_connection_id,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
                if (value == 'success') {

                }
                else {

                }
            },
        });

    });

    $(".mapping_save").click(function(e){
        $.ajax({
            type: 'post',
            url: '/channelsetting/mapping-finish',
            data: {
                user_connection_id: user_connection_id,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
                if (value == 'success') {

                }
                else {

                }
            },
        });
    });
});