$(document).ready(function () {
    $('#CreateCustomerCountRY').on('change', function () {
        var country_name = $(this).val();
        if (country_name) {
            $.ajax({
                type: 'POST',
                url: 'createcountryajax',
                data: {
                    country_name: country_name
                },
                success: function (response) {
                    console.log(response);
                    // $('#CreateCustomerStatES').html('');
                    $('#CreateCustomerStatES').append(response);
                }
            });

        }
    });

    //code for create customer STATE on change 

    $("#CreateCustomerStatES").on('change', function () {
        var state_ID = $(this).val();
        if (state_ID) {
            $.ajax({
                type: 'POST',
                url: 'createcountryajax',
                data: {
                    state_ID: state_ID
                },
                success: function (response) {
                    console.log(response);
                    // $('#CreateCustomerCitiES').html('');
                    $('#CreateCustomerCitiES').append(response);
                }
            })
        }
    });

    //code for create customer ship country on change

    $("#createShipCountRY").on('change', function () {
        var ship_countryName = $(this).val();
        if (ship_countryName) {
            $.ajax({
                type: "POST",
                url: 'createcountryajax',
                data: {
                    ship_countryName: ship_countryName
                },
                success: function (response) {
                    console.log(response);
                    // $('#createShipStatES').html('');
                    $('#createShipStatES').append(response);
                }
            })
        }
    });

    //code for create customer ship State on change

    $('#createShipStatES').on('change', function () {
        var ship_StateID = $(this).val();
        if (ship_StateID) {
            $.ajax({
                type: "POST",
                url: 'createcountryajax',
                data: {
                    ship_StateID: ship_StateID
                },
                success: function (response) {
                    console.log(response);
                    // $('#createShipCitiES').html('');
                    $('#createShipCitiES').append(response);
                }
            });
        }
    });

    $('.customCC').click(function () {
        $('.custOMPerf').css('display', 'block'); //code for making the publish button show on perfromance tab only more code on line number 721
    });

    $(".wechat_sbmt1").click(function () {

        storename = $.trim($("#wechat_storename").val());
        password = $.trim($("#wechat_password").val());
        type = $.trim($("#wechat_type").val());
        if (!storename || !password) {
            $("#wechat_storename").addClass("inpt_required");
            $("#wechat_password").addClass("inpt_required");
            return false;
        } else {
            $("#wechat_storename").removeClass("inpt_required");
            $("#wechat_password").removeClass("inpt_required");
        }

        $.ajax({
            type: 'post',
            url: '/channels/wechat',
            data: {
                storename: storename,
                password: password,
                wechat_type: type,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
                if (value) {
                    $('#walkthechat_request').modal('show');
                    $(".afterwechatrequestmsg").show();
                    $(".wechatrequestform").hide();
                }
            },
        });
    })

    $(".wechat_import").click(function () {

        if (!$(this).hasClass("yyy")) {
            $(this).addClass("yyy");
            window.location = "/channels/wechatimport";
//    alert();
        }

        return false;
    })

    $(".google_error_modal_close").click(function () {
        $('.googlemod-error').hide();
    });
    $(".google_modal_close").click(function () {
        $('.googlemod-success').hide();
    });
    $('#googlefeedcheck').click(function () {

        if ($('#googlefeedcheck').is(':checked')) {
            var googlefeed = 'yes';
            $('.googlemod-success').show();
            $('.code').show();
            $('.code2').hide();
        } else {
            var googlefeed = 'no';
            $('.googlemod-error').show();
            $('.code').hide();
            $('.code2').hide();
        }


        $.ajax({
            type: 'post',
            url: '/google-shopping/feed/',
            data: {
                feed: googlefeed,
                // already: 'yes',
                // email: '',
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {

                $(".be-wrapper").removeClass("be-loading-active");
                if (value == 'error') {
                    $('#walkthechat_request_error').modal('show');
                } else if (value) {
                    // window.location.href = value;
                    // $('#walkthechat_request').modal('show');
                    //$(".afterwechatrequestmsg_already").show();
                    $(".div23").addClass('active');
                    $(".data2").addClass('active');
                    $(".data1").addClass('complete');
                    $(".div21").removeClass('active');
                }

//           location.reload();
            },
        });




    });

    $('.netsuite-success_modal_close').click(function () {
        location.reload();
    });
    $('.netsuitewizardfinish').click(function () {
        var elliotresturl = $('#elliotresturl').val();
        if (elliotresturl == '') {
            $("#elliotresturl").addClass("inpt_required");

            return false;
        } else {
            $("#elliotresturl").removeClass("inpt_required");

        }
        $.ajax({
            type: 'post',
            url: '/integrations/netsuiteupdateurl/',
            data: {
                elliotresturl: elliotresturl,
                // already: 'yes',
                // email: '',
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
                //location.reload();
            },
            success: function (value) {

                $(".be-wrapper").removeClass("be-loading-active");
                $('.netsuite-success').show();
                //location.reload();
            },
        });
    });

    $('.netsuitecredentialupdate').click(function () {
        var netsuitecompanyid = $('#netsuitecompanyid').val();
        var netsuiteempemail = $('#netsuiteempemail').val();
        var netsuitemppass = $('#netsuitemppass').val();
        if (netsuitecompanyid == '' || netsuiteempemail == '' || netsuitemppass == '') {
            $("#netsuitecompanyid").addClass("inpt_required");
            $("#netsuiteempemail").addClass("inpt_required");
            $("#netsuitemppass").addClass("inpt_required");
            return false;
        } else {
            $("#netsuitecompanyid").removeClass("inpt_required");
            $("#netsuiteempemail").removeClass("inpt_required");
            $("#netsuitemppass").removeClass("inpt_required");
        }
        $.ajax({
            type: 'post',
            url: '/integrations/netsuiteupdate/',
            data: {
                netsuitecompanyid: netsuitecompanyid,
                netsuiteempemail: netsuiteempemail,
                netsuitemppass: netsuitemppass,
                // already: 'yes',
                // email: '',
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
                //location.reload();
            },
            success: function (value) {

                $(".be-wrapper").removeClass("be-loading-active");
                $('#netsuiterole').append(value);
            },
        });
    });


    function isEmail(email) {
        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    $('#sfexpresscheck').click(function () {

        if ($('#sfexpresscheck').is(':checked')) {
            var sfexpresscheck = 'yes';
            $('.googlemod-success').show();
            $('.code').show();
            $('.code2').hide();
        } else {
            var sfexpresscheck = 'no';
            $('.googlemod-error').show();
            $('.code').hide();
            $('.code2').hide();
        }


        $.ajax({
            type: 'post',
            url: '/sfexpress/default/',
            data: {
                feed: sfexpresscheck,
                // already: 'yes',
                // email: '',
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
                location.reload();
            },
            success: function (value) {

                $(".be-wrapper").removeClass("be-loading-active");
                if (value == 'error') {
                    $('#walkthechat_request_error').modal('show');
                }

//           location.reload();
            },
        });




    });















    $(".fb_error_modal_close").click(function () {
        $('.fbmod-error').hide();
    });
    $(".fb_modal_close").click(function () {
        $('.fbmod-success').hide();
    });
    $('#facebookfeedcheck').click(function () {

        if ($('#facebookfeedcheck').is(':checked')) {
            var fb_feed = 'yes';
            $('.fbmod-success').show();
            $('.code').show();
            $('.code2').hide();
        } else {
            var fb_feed = 'no';
            $('.fbmod-error').show();
            $('.code').hide();
            $('.code2').hide();
        }

        $.ajax({
            type: 'post',
            url: '/facebook/savechannel/',
            data: {
                feed: fb_feed,
                // already: 'yes',
                // email: '',
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {

                $(".be-wrapper").removeClass("be-loading-active");
                if (value == 'error') {
                    $('#walkthechat_request_error').modal('show');
                } else if (value) {
                    // window.location.href = value;
                    // $('#walkthechat_request').modal('show');
                    //$(".afterwechatrequestmsg_already").show();
                    $(".div23").addClass('active');
                    $(".data2").addClass('active');
                    $(".data1").addClass('complete');
                    $(".div21").removeClass('active');
                }

//           location.reload();
            },
        });




    });

    $("#channel_WeChat-Store .btn-primary").click(function () {
        var radioValue = '';
        radioValue = $("#channel_WeChat-Store input[name='rad3']:checked").val();

        if (typeof radioValue != 'undefined') {
            if ($("#channel_WeChat-Store").hasClass("admin_pg")) {
                window.location = "/channels/wechatconnect?type=" + radioValue;
            } else {
                window.location = "/channels/wechat?type=" + radioValue;
            }

            $("#channel_WeChat-Store label").removeClass("checkrequired");
        } else {
            $("#channel_WeChat-Store label").addClass("checkrequired");
        }

    });

    $("#channel_Lazada-Vietnam .btn-primary").click(function () {
        var radioValue = '';
        radioValue = $("#channel_Lazada-Vietnam input[name='rad3']:checked").val();

        if (typeof radioValue != 'undefined') {
            lazada_type = radioValue.split(' ');
            window.location = "/lazada/lazada/?type=" + lazada_type[1];

            $("#channel_Lazada-Vietnam label").removeClass("checkrequired");
        } else {
            $("#channel_Lazada-Vietnam label").addClass("checkrequired");
        }

    });


//     $(".wizard-next-auth-store-lazada").click(function () {
//         lazada_user_email = $.trim($("#lazada_user_email").val());
//         lazada_api_key = $.trim($("#lazada_api_key").val());
//         lazada_api_url = $.trim($("#lazada_api_url").val());
//         channel_type = $.trim($("#channel_type").val());
//         channel_id = $.trim($("#channel_id").val());
//         var user_id = $('#loggedin_user_id').val();
//         if (!lazada_api_key || !lazada_user_email || !lazada_api_url) {
//             $("#lazada_api_key").addClass("inpt_required");
//             $("#lazada_user_email").addClass("inpt_required");
//             $("#lazada_api_url").addClass("inpt_required");
//             return false;
//         } else {
//             $("#lazada_api_key").removeClass("inpt_required");
//             $("#lazada_user_email").removeClass("inpt_required");
//         }
// //alert('he');return false;
//         $.ajax({
//             type: 'post',
// //            url: '/lazada/lazada/?type=' + channel_type,
//             url: '/lazada/auth-lazada-malaysia/?type=' + channel_type,
//             data: {
//                 lazada_user_email: lazada_user_email,
//                 lazada_api_key: lazada_api_key,
//                 lazada_api_url: lazada_api_url,
//                 channel_id: channel_id,
//             },
//             beforeSend: function (xhr) {
//                 $(".be-loading").first().addClass("be-loading-active");
//             },
//             success: function (value) {
//                 var obj = JSON.parse(value);
//                 $(".be-loading").first().removeClass("be-loading-active");
//                 $('.be-wrapper').removeClass('be-loading-active');
//                 if (obj["api_error"] != undefined)
//                 {
//                     var html_data_error = obj['api_error'];
//                     $("#lazada_ajax_msg_eror").html(html_data_error);
//                     $("#lazada_ajax_header_error_msg").html('Error!');
//                     $('.lazada_ajax_request_error').modal('show');
//                 }
//                 if (obj["success"] != undefined)
//                 {
//                     var html_data = obj['success'];
//                     $("#lazada_ajax_msg").html(html_data);
//                     $("#lazada_ajax_header_msg").html('Success!');
//                     $('#lazada-authorize-form').hide();
//                     $('#lazada_ajax_request').modal('show');
//                     $('#lazada-authorize-form')[0].reset();
//                     $.ajax({
//                         type: 'get',
//                         url: BASE_URL + 'lazada/malaysia-importing/?type=' + channel_type,
//                         data: {
//                             user_id: user_id,
//                         },
//                         complete: function (data) {
//                             window.location.reload();
//                         }
//                     });
//                 }
//                 if (obj["error"] != undefined)
//                 {
//                     var html_data = obj['error'];
//                     $("#lazada_ajax_msg_eror").html(html_data);
//                     $("#lazada_ajax_header_error_msg").html('Error!');
//                     $('.lazada_ajax_request_error').modal('show');
//                 }


//             },
//         });
//     });
    $('#lazada_ajax_request .mdi-close').click(function () {
        $('#lazada_ajax_request').modal('hide');
        window.location.reload()
    });

    $('#lazada_ajax_request .lazada_modal_close').click(function () {
        $('#lazada_ajax_request').modal('hide');
        window.location.reload()
    });

    $('.lazada_error_modal_close').click(function () {
        $('.lazada_ajax_request_error').modal('hide');
    });

    $('#cats_list').editable();
//    var currencies_array=[];
//    $.ajax({
//        type: 'get',
//        dataType: 'json',
//        data: {},
//        url: '/integrations/currencies',
//        success: function (data) {
//          currencies_array=data;
//            console.log(currencies_array);
//        }
//    });

    $('#annual_order_revenue').editable({

        success: function (response, newValue) {
            var request = $.ajax({
                url: "/site/saveorderrevenue",
                type: "POST",
                data: {revenue: newValue},
//                dataType: "html"
            });

            request.done(function (msg) {
//                alert(msg);
            });
        }
    });
})

$(document).on("click", "#customer_submit", function (event) {
    var customer_first_name = $('#customer-first-name input').val();
    var customer_last_name = $('#customer-last-name input').val();
    var customer_email = $('#customer-email input').val();
    var bill_steet1 = $('#customer-Street-1 input').val();
    var bill_steet2 = $('#customer-Street-2 input').val();
    var bill_country = $('#customer-Country select').val();
    var bill_city = $('#customer-City select').val();
    var bill_state = $('#customer-State select').val();
    var ship_country = $('#customer-Country-ship select').val();
    var ship_city = $('#customer-City-ship select').val();
    var ship_state = $('#customer-State-ship select').val();
    var bill_zip = $('#customer-Zip input').val();

    var chk = true;
    $(".customer_validate").each(function () {
        var val = $.trim($(this).val());
        if (!val || val == 'Please Select Value' || val == 'Please Select Country First' || val == 'Please Select State First') {
            $(this).addClass("inpt_required");
            chk = false;
        } else {
            //     if(val == 'Please Select Value') {
            //          $(this).addClass("inpt_required");
            //     chk = false;
            // } else {

            $(this).removeClass("inpt_required");
            // }
        }
    })

    if (!chk) {
        return false;
    }

});


/***************For content Create Form Validations********************/

$(document).on('click', "#Content_submit", function (e) {
    e.preventDefault();
    var page_title = $('#page_title input').val();
    var page_desc = $('#page_description').summernote('code');
    var chk = true;
    $('.content_validate').each(function () {
        var val = $.trim($(this).val());
        if (!val) {
            $(this).addClass("inpt_required");
            chk = false;
        } else {
            $(this).removeClass("inpt_required");
        }
    });
    if (!chk) {
        return false;
    } else {
        $.ajax({
            type: 'post',
            url: '/content/create',
            data: {
                'page_title': page_title,
                'page_desc': page_desc
            },
            success: function (response) {
                console.log(response);
            }
        });


    }

});


/************For update form validations and ajax*******************/

$(document).on('click', '#Content_update_submit', function (e) {
    e.preventDefault();
    var page_title_update = $("#page_title_update input").val();
    var page_desc_update = $("#page_description_update").summernote('code');
    var chk = true;
    $('.content_validate').each(function () {
        var val = $.trim($(this).val());
        if (!val) {
            $(this).addClass("inpt_required");
            chk = false;
        } else {
            $(this).removeClass("inpt_required");
        }
    });
    if (!chk) {
        return false;
    } else {
        var page_id = $("#update_page_ID").val();
        $.ajax({
            type: 'post',
            url: '/content/updatepage',
            data: {
                'page_title': page_title_update,
                'page_desc': page_desc_update,
                'page_id': page_id
            },
            success: function (response) {
                console.log(response);
            }
        });

    }


});


/**********For dashboard Main chart buttons click***************/
/*click on month button put value in hiiden field*/
$("#Month").click(function () {
    $("#week_row_div").css("display", "none");
    $("#today_row_div").css("display", "none");
    $("#Year_row_div").css("display", "none");
    $("#main-chart1").css("display", "none");
    $("#main-chartToday").css("display", "none");
    $("#main-chartYear").css("display", "none");
    $("#month_row_div").css("display", "block");
    $("#main-chartMonthly").css("display", "block");
    $('#hidden_graph').val('Month');
    $('.active-remove').removeClass('active');
    $(this).addClass('active');
    App.dashboard();
});

// click on Year button put value in hiiden field
$("#Year").click(function () {
    $('#hidden_graph').val('Year');
    $("#month_row_div").css("display", "none");
    $("#week_row_div").css("display", "none");
    $("#today_row_div").css("display", "none");
    $("#main-chartToday").css("display", "none");
    $("#main-chart1").css("display", "none");
    $("#main-chartMonthly").css("display", "none");
    $("#main-chartYear").css("display", "block");
    $("#Year_row_div").css("display", "block");
    $('.active-remove').removeClass('active');
    $(this).addClass('active');
    App.dashboard();
});
// click on WEEK button put value in hiiden field
$("#Week").click(function () {
    $("#month_row_div").css("display", "none");
    $("#today_row_div").css("display", "none");
    $("#Year_row_div").css("display", "none");
    $("#main-chartMonthly").css("display", "none");
    $("#main-chartToday").css("display", "none");
    $("#main-chartYear").css("display", "none");
    $("#main-chart1").css("display", "block");
    $("#week_row_div").css("display", "block");
    $('#hidden_graph').val('Week');
    $('.active-remove').removeClass('active');
    $(this).addClass('active');
    App.dashboard();
});

// click on Today button put value in hiiden field
$("#Today").click(function () {

    $("#month_row_div").css("display", "none");
    $("#week_row_div").css("display", "none");
    $("#Year_row_div").css("display", "none");
    $("#main-chart1").css("display", "none");
    $("#main-chartMonthly").css("display", "none");
    $("#main-chartYear").css("display", "none");
    $("#main-chartToday").css("display", "block");
    $("#today_row_div").css("display", "block");
    $('#hidden_graph').val('Today');
    $('.active-remove').removeClass('active');
    $(this).addClass('active');
    App.dashboard();
});
/**********End dashboard Main chart buttons click***************/



