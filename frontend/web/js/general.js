$(document).ready(function () {
    $("body").tooltip({selector: '[data-toggle=tooltip]'});
});

$(function () {

    $("#content_view_table").DataTable({
        "order": [[0, "asc"]],
        "columnDefs": [
            {"orderable": false, "targets": 1}
        ],
        pageLength: 25,
        "processing": true,
        "serverSide": true,
        "oLanguage": {
            sProcessing: function () {
                $(".contentLoader").first().addClass('be-loading-active');
            }
        },
        initComplete: function () {
            $(".contentLoader").first().removeClass('be-loading-active');
        },
        "ajax": "/content/getpages",
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"

    });



    /*For Sfexpress table*/
    $('#tablesf1').DataTable({
        //"order": [[ 4, "desc" ]],
        pageLength: 10,
        "columnDefs": [
            {"orderable": true, "targets": 1}
        ],
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });

    $('#tablesf2').DataTable({
        //"order": [[ 4, "desc" ]],
        pageLength: 10,
        "columnDefs": [
            {"orderable": true, "targets": 1}
        ],
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });

    $('#tablesf3').DataTable({
        //"order": [[ 4, "desc" ]],
        pageLength: 10,
        "columnDefs": [
            {"orderable": true, "targets": 1}
        ],
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    $('#tablesf4').DataTable({
        //"order": [[ 4, "desc" ]],
        pageLength: 10,
        "columnDefs": [
            {"orderable": true, "targets": 1}
        ],
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    $('#tablesf5').DataTable({
        //"order": [[ 4, "desc" ]],
        pageLength: 10,
        "columnDefs": [
            {"orderable": true, "targets": 1}
        ],
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    $('#tablesf6').DataTable({
        //"order": [[ 4, "desc" ]],
        pageLength: 10,
        "columnDefs": [
            {"orderable": true, "targets": 1}
        ],
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    $('#tablesf7').DataTable({
        //"order": [[ 4, "desc" ]],
        pageLength: 10,
        "columnDefs": [
            {"orderable": true, "targets": 1}
        ],
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    $('#tablesf8').DataTable({
        //"order": [[ 4, "desc" ]],
        pageLength: 10,
        "columnDefs": [
            {"orderable": true, "targets": 1}
        ],
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    /*End For Sfexpress table*/


    /*Starts Connected Channel Or Store Order page */
    /*get current action in hidden field*/
    var action = $('#current_action').val();

    /*End Connected Channel Or Store Order page */

    var recent_orders_dashboard1212 = $('#recent_orders_dashboard_tbl1').DataTable({
        //"order": [[ 4, "desc" ]],
        pageLength: 5,
        "columnDefs": [
            {"orderable": false, "targets": 1}
        ],
        buttons: [
        ],
        dom: ""
    });

    $('#lazada_connect_modal').DataTable({
        // "order": [[ 2, "desc" ]],
//        pageLength: 2,
        "columnDefs": [
            //{"orderable": false, "targets": 1}
            // {"orderable": false, "targets": 2},
            //  {"orderable": false, "targets": 3},
            // {"orderable": false, "targets": 4}
        ],
        "order": [],
        buttons: [
        ],
        dom: ""
    });


    $('#products_table').DataTable({
        buttons: [
        ],
        dom: ""
    });

    $('#user_added').DataTable({
        buttons: [
        ],
        dom: ""
    });


//    recent_orders_dashboard1212.fixedHeader.disable();
//    recent_orders_dashboard1212.buttons().disable();

    /*Adding Active Open Class to Main Menu if any Submenu is open*/
    if (window.location.href.indexOf("products") > -1) {
        $('.be-left-sidebar .left-sidebar-content .sidebar-elements li a').each(function () {
            var a = $(this).find('span').text();
            if (a == 'Products') {
                $(this).parent().addClass('active open');
            }
        });
    }
    /*END*/



    /* Categories DataTable */
    $("#categories_table").dataTable({
        pageLength: 50,
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    /* END */
    /* Variations DataTable */
    $("#variations_table").dataTable({
        pageLength: 50,
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    /* END */


    //Image Divs Draggable 
    $('.draggable-element').arrangeable();
    //Save the Order of Images
    $(".pmedia_create_next").click(function (e) {
        e.preventDefault();
        var id = $(this).data("wizard");
        var img_order_array;
        var i = 0;
        $('.pmedia .draggable-element').each(function (index, value) {
            //  alert(index + ": " + $(value).find('.upld_img').val());
            var div_id = value.id;
            var id_arr = div_id.split('_');
            var id_num = id_arr[1];
            var img_order = parseInt(index) + 1;
            var img_Id = $(value).find('.upld_img').val();
            var img_default = $('#pimgRad_' + id_num + ':checked').length;
            if (img_Id != '') {
                i = parseInt(i) + 1;
                $.ajax({
                    type: 'post',
                    url: 'update-product-image-order',
                    data: {
                        ImgId: img_Id,
                        ImgOrder: i,
                        ImgDefault: img_default,
                    },
                    success: function (value) {
                    },
                });
            }
        });
        $(id).wizard('next');
    });
    /*AutoComplete Google Categories Fields Attribution Step - Product Create*/
    $("#pattr_cat1_create input").autocomplete({
        source: 'google-product-category'
    });
    $("#pattr_cat2_create input").autocomplete({
        source: 'google-product-category'
    });
    $("#pattr_cat3_create input").autocomplete({
        source: 'google-product-category'
    });
    /* END*/


    /* Product Create Wizard Completion Step */
    $(".wizard-complete").click(function (e) {
        var pID = $('#pID_created').val();
        var id = $(this).data("wizard");
        //General Step Fields
        var pName = $('#pName_create input').val();
        var pSKU = $('#pSKU_create input').val();
        var pUPC = $('#pUPC_create input').val();
        var pEAN = $('#pEAN_create input').val();
        var pJAN = $('#pJAN_create input').val();
        var pISBN = $('#pISBN_create input').val();
        var pMPN = $('#pMPN_create input').val();
        var pDes = $('#product-create-description').summernote('code');
        var pAdult = $('#pAdult_create select option:selected').text();
        var pAgeGroup = $('#pAgeGroup_create select option:selected').text();
        var pAvail = $('#pAvail_create select option:selected').text();
        var pBrand = $('#pBrand_create input').val();
        var pCond = $('#pCond_create select option:selected').text();
        var pGender = $('#pGend_create select option:selected').text();
        var pWeight = $('#pWght_create input').val();
        var pConnectMarketPlace = $('#pConnectMarketPlace').val();
        //Attribution Step Fields
//        var pCat1 = $('#pattr_cat1_create input').val();
//        var pCat2 = $('#pattr_cat2_create input').val();
//        var pCat3 = $('#pattr_cat3_create input').val();
        var Google_Cat1 = $('#pattr_cat1_create').text();
        var Google_Cat2 = $('#pattr_cat2_create').text();
        var pOccas = $('#pattr_occas_create .be-checkbox input:checked').map(function () {
            return $(this).next("label").text();
        }).get().join(",");
        var pWthr = $('#pattr_weather_create .be-checkbox input:checked').map(function () {
            return $(this).next("label").text();
        }).get().join(",");
        //Categories Step Fields

        //Channel Manager Step Fields

        //Inventory Management Step Fields
        var pStk_qty = $('#pstk_qty_create input').val();
        var pStk_lvl = $('#pstk_lvl_create select option:selected').text();
        var pStk_status = $('#pstk_sts_create select option:selected').text();
        var plow_stk_ntf = $('#plw_stk_ntf_create input').val();
        //Media Step Fields

        //Performance Step Fields

        //Pricing Step Fields
        var pPrice = $('#pprice_create input').val();
        var pSale_price = $('#psale_price_create input').val();
        var pSchedule_date = $('#psched_date2_create input').val();
        var Google_Cat_id = $('#gcatname').val();

        //connect store
        var connect_checkbox = document.getElementsByName("connect_store_chk[]");


        var vals = "";
        var n;
        for (var i = 0, n = connect_checkbox.length; i < n; i++)
        {
            if (connect_checkbox[i].checked)
            {
                vals += "," + connect_checkbox[i].value;
            }
        }

        vals = vals.substring(1);

        $.ajax({
            type: 'post',
            url: 'create-product?pID=' + pID,
            data: {
                pName: pName,
                pSKU: pSKU,
                pUPC: pUPC,
                pEAN: pEAN,
                pJAN: pJAN,
                pISBN: pISBN,
                pMPN: pMPN,
                pDes: pDes,
                pAdult: pAdult,
                pAgeGroup: pAgeGroup,
                pAvail: pAvail,
                pBrand: pBrand,
                pCond: pCond,
                pGender: pGender,
                pWeight: pWeight,
                pConnectMarketPlace: pConnectMarketPlace,
                Google_Cat1: Google_Cat1,
                Google_Cat2: Google_Cat2,
                Google_Cat_id: Google_Cat_id,
                pOccasion: pOccas,
                pWeather: pWthr,
                pStk_qty: pStk_qty,
                pStk_lvl: pStk_lvl,
                pStk_status: pStk_status,
                plow_stk_ntf: plow_stk_ntf,
                pPrice: pPrice,
                pSale_price: pSale_price,
                pSchedule_date: pSchedule_date,
                vals: vals,
            },
            success: function (value) {

            },
        });
        $(id).wizard('next');
        e.preventDefault();
    });
    /* Product Create Wizard END*/


    /*Store Connection Setup Wizard*/
    $('.wizard-next-connect-store1').click(function (e) {
        var id = $(this).data("wizard");
        var selected_store = $("input[name='store-radio']:checked").attr('id');
        if (selected_store == 'BigCommerce') {
//            $('#bigcommerce-authorize-form').show();
            $(id).wizard('next');
        } else {
            alert("please select Bigcommerce for now!");
        }
        e.preventDefault();
    });








    /**
     * Square connection and validation begin
     */

    $('.square-auth-channel').click(function (e) {
        $(this).css('pointer-events', 'none');
        e.preventDefault();
        var square_application_id = $('#square_application_id input').val();
        var square_personal_access_token = $('#square_personal_access_token input').val();
        var user_id = $('#user_id').val();
        //Required Fields Validation
        var isValid = true;

        if (square_application_id == '')
        {
            $('#square_application_id').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else
        {
            if ($('#square_application_id').hasClass('has-error')) {
                $('#square_application_id').removeClass('has-error');
            }
        }
        if (square_personal_access_token == '') {
            $('#square_personal_access_token').addClass('has-error');
            $(this).css('pointer-events', 'auto');
            isValid = false;
        } else {
            if ($('#square_personal_access_token').hasClass('has-error')) {
                $('#square_personal_access_token').removeClass('has-error');
            }
        }

        if (isValid) {
            $('.square-auth-channel').css('pointer-events', 'auto');
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/square/square-auth',
                data: {
                    square_application_id: square_application_id,
                    square_personal_access_token: square_personal_access_token,
                    user_id: user_id,
                },
            }).done(function (data) {
                var obj = JSON.parse(data);
                console.log(obj);
                $('.be-wrapper').removeClass('be-loading-active');
                if (obj["success"] != undefined)
                {
                    var html_data = obj['success'];
                    $("#square_ajax_msg").html(html_data);
                    $("#square_ajax_header_msg").html('Success!');
                    $('#square-authorize-form').hide();
                    $('#square_ajax_request').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                    $('#square-authorize-form')[0].reset();
                    $.ajax({
                        type: 'get',
                        url: BASE_URL + 'square/square-importing',
                        data: {
                            user_id: user_id,
                        },
                    }).done(function (data) {
                        //alert(data);
                        window.location.reload();
                    });
                }
                if (obj["api_error"] != undefined)
                {
                    var html_data_error = obj['api_error'];
                    $("#square_ajax_msg_eror").html(html_data_error);
                    $("#square_ajax_header_error_msg").html('Error!');
                    $('.square_ajax_request_error').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                }
            });
            // e.preventDefault();
        }
    });

    $('.square_error_modal_close').click(function () {
        $('.square_ajax_request_error').css({
            'display': 'none',
            'background': 'rgba(0,0,0,0.6)',
        });
    });

    $('.square_modal_close').click(function () {
        $('#square_ajax_request').css({
            'display': 'none',
            'background': 'rgba(0,0,0,0.6)',
        });
        window.location.reload()
    });


    /**
     * Square connection and validation End
     */

    /**
     * End For product multiple chekcbox
     */

    $(".product_multiple_check").change(function () {  //"select all" change 
        $(".product_row_check").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
//        if($(".product_multiple_check").prop('checked')==true){
//            $('#dropdown_delete').addClass('open');
//        }
//        else{
//            $('#dropdown_delete').removeClass('open');
//        }
    });

    $("body").on('change', ".product_row_check", function () {
        var atLeastOneIsChecked = $('.product_row_check:checkbox:checked').length;
//        if(atLeastOneIsChecked==0){
//            $('#dropdown_delete').removeClass('open');
//        }
//        else{
//            $('#dropdown_delete').addClass('open');
//        }
        //uncheck "select all", if one of the listed checkbox item is unchecked
        if (false == $(this).prop("checked")) { //if this item is unchecked
            $(".product_multiple_check").prop('checked', false); //change "select all" checked status to false
        }
        //check "select all" if all checkbox items are checked
        if ($('.product_row_check:checked').length == $('.product_row_check').length) {
            $(".product_multiple_check").prop('checked', true);
        }
    });
    $("body").on('click', "#product_delete_button", function () {
        $("#product-delete-modal-warning .close").click();
        $('.productTABLE').addClass('be-loading-active');
        var checkedProductId = $('input[name=product_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            type: 'POST',
            url: '/product/ajax-product-delete',
            data: {
                productIds: checkedProductId,
            },
        }).done(function (data) {
            setTimeout(function () {
                $('.productTABLE').removeClass('be-loading-active');
                window.location.reload();
            }, 4000);

        });
    });
    $("body").on('click', '#connect_product_delete_button', function () {
        $("#connect-product-delete-modal-warning .close").click();
        $(".ConnectedProDUCTS").addClass('be-loading-active');
        var checkedProductID = $('input[name=inactive_product_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            type: 'POST',
            url: '/product/ajax-product-delete',
            data: {
                productIds: checkedProductID
            }
        }).done(function (data) {
            setTimeout(function () {
                $('.ConnectedProDUCTS').removeClass('be-loading-active');
                window.location.reload();
            }, 4000);

        });

    })

    $("body").on('click', "#inactive_product_delete_button", function () {
        $("#inactive-product-delete-modal-warning .close").click();
        $('.InactiVeProDUCTS').addClass('be-loading-active');
        var checkedProductID = $('input[name=inactive_product_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            type: 'POST',
            url: '/product/ajax-inactive-product-delete',
            data: {
                checkedProductID: checkedProductID
            }
        }).done(function (data) {
            setTimeout(function () {
                $('.InactiVeProDUCTS').removeClass('be-loading-active');
                window.location.reload();
            }, 4000);


        });
    });
    /**
     * End For product multiple chekcbox
     */



    /**
     * End For order multiple chekcbox
     */


    /* Mercadolibre connection and validation begin */

    $('.wizard-next-auth-store-mercadolibre').click(function (e) {
        e.preventDefault();
        //var id = $(this).data("wizard");
        var mercadolibre_channel_email = $('#mercadolibre_channel_email').val();
        var mercadolibre_channel_password = $('#mercadolibre_channel_password').val();
        var mercadolibre_channel_client_id = $('#mercadolibre_channel_client_id').val();
        var mercadolibre_channel_secret_id = $('#mercadolibre_channel_secret_id').val();
        var user_id = $('#mercadolibre_user_id').val();

        //Required Fields Validation
        var chk = true;
        $(".mercado_validate").each(function () {
            var val = $.trim($(this).val());
            if (!val) {
                $(this).addClass("inpt_required");
                chk = false;
            } else {
                $(this).removeClass("inpt_required");
            }
        })


        if (chk == true) {
            $('.be-wrapper').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: 'auth-mercadolibre',
                data: {
                    mercadolibre_channel_email: mercadolibre_channel_email,
                    mercadolibre_channel_password: mercadolibre_channel_password,
                    mercadolibre_channel_client_id: mercadolibre_channel_client_id,
                    mercadolibre_channel_secret_id: mercadolibre_channel_secret_id,
                    user_id: user_id,
                },
            }).done(function (data) {
                //alert(data);
                var obj = JSON.parse(data);
                console.log(obj);
                $('.be-wrapper').removeClass('be-loading-active');

                if (obj["success"] != undefined)
                {
                    var html_data = obj['success'];
                    $("#mercadolibre_ajax_msg").html(html_data);
                    $("#ajax_header_msg").html('Success!');
                    $('#mercadolibre-authorize-form').hide();
                    $('#mercadolibre_ajax_request').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                    $('#mercadolibre-authorize-form')[0].reset();


                }
                if (obj["api_error"] != undefined)
                {
                    var html_data_error = obj['api_error'];
                    $("#mercadolibre_ajax_msg_eror").html(html_data_error);
                    $("#ajax_header_error_msg").html('Error!');
                    $('.mercadolibre_ajax_request_error').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                }
                if (obj["error"] != undefined)
                {
                    var html_data = obj['error'];
                    $("#mercadolibre_ajax_msg").html(html_data);
                    $("#mercadolibre_ajax_msg_eror").html(html_data);
                    $("#ajax_header_error_msg").html('Error!');
                    $('.mercadolibre_ajax_request_error').css({
                        'display': 'block',
                        'background': 'rgba(0,0,0,0.6)',
                    });
                }
            });
            e.preventDefault();
        }
    });


    /*Notifications*/
    $(document).on("click", ".elliot_notif .icon.mdi.mdi-notifications", function (event) {
        $('.elliot_notif .indicator').hide();
    });
    $(document).on("mouseover", ".elliot_notif .notification-unread", function (event) {
        $(this).find('.notif_check').show();
    });
    $(document).on("mouseout", ".elliot_notif .notification-unread", function (event) {
        $(this).find('.notif_check').hide();
    });

    //Prevent Notifications Dropdown closes on click
//    $(".be-notifications").on("click", function (e) {
//        e.stopPropagation();
//    });
    /*End*/

    $(document).on("click", ".notification-info", function (event) {
        var total_notf = $('.badge').text();
        var notif_id = this.id;
        $.ajax({
            type: 'post',
            url: '/dashboard/clear-notification',
            datatype: 'json',
            data: {
                notif_id: notif_id,
            },
            success: function (data) {
                $('#li_' + notif_id).hide("slide", {direction: "right"}, 500);
                total_notf = parseInt(total_notf) - 1;
                $('.badge').text(total_notf);
            }
        });
        event.stopPropagation();
    });

    //Editable Fields
    $('#op_set').editable({
        emptytext: 'Please Select'
    });



//    $('#op_set').on('save', function (e, params) {
//        var opset_id = params.newValue;
//        var var_html = '';
//        var_html += '<table id="var_tbl" style="clear: both" class="table table-striped table-borderless">';
//
//        $.ajax({
//            type: 'post',
//            url: 'get-opset-variations',
//            data: {
//                opset_id: opset_id,
//            },
//            success: function (value) {
//                console.log(value);
//                var op_arr = JSON.parse(value);
//                var op_html = '';
//                $.each(op_arr, function (index, val) {
//                    op_html += '<a class="pvaritems" href="javascript:" data-title="Please Select" data-type="select" data-value="" data-pk="1" class="editable editable-click" data-source="get-varitems?var_name=' + val + '"></a>';
//                });
//            },
//        });
//        var_html += '</table>';
//        $('#variations .table-responsive').append(var_html);
//
//
//    });


    $('.up_context_data').editable({
        emptytext: 'Please Select'
    });

});

/*For Lazada Categories*/
$('body').on('click', '.lazada_cat_class', function () {
    var check = $(this).attr("data-open");
    var channel_accquired = $(this).attr("data-lazada_cat");
    var data_id = $(this).attr("data-id");
  
    if (data_id == '') {
        if (check == 'yes') {
            var check = $(this).attr("data-open", 'no');
        }
        if (check == 'no') {
             $('#categories').addClass('be-loading-active');
            var check = $(this).attr("data-open", 'yes');
//        alert('opne hai');
            $.ajax({
                type: 'post',
                url: '/products/lazada-categories',
                data: {
                    data_id: data_id,
                    channel_accquired: channel_accquired,
                },
                success: function (data) {
                    $('#categories').removeClass('be-loading-active');
                    $('#cat_' + channel_accquired).html(data);
                },
            });
        }
    } else {
        
        var cat_check = $(this).attr("data-cat_check");
        if (cat_check == 'parent') {
            $('#categories').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: '/products/lazada-categories',
                data: {
                    data_id: data_id,
                    channel_accquired: channel_accquired,
                },
                
                success: function (data) {
                    $('#categories').removeClass('be-loading-active');
                    //$('#'+data_id).next('list1').remove();
                    $('#' + data_id).next().remove();
                    $('#' + data_id).after(data);
                },
            });
        }

    }

});

$('body').on('click', '.lazada_least', function () {
    var data_id = $(this).attr("data-id");

    $('#check' + data_id).prop('checked', true);
    $('.lazada_least').removeClass('radio_box_active');
    $(this).addClass('radio_box_active');
//    $('#'+ data_id).css({
//        'color': '#ffffff !important',
//        'background': '#4285f4 !important',
//        'border-color': '#4285f4 !important'
////    });
//	$('#'+data_id).addClass( "radio_box_active" );
});

/* JS Functions */
function updateattr_type(id) {
    var attr_type_name = $('#update-attr-type-name').text();
    var attr_type_label = $('#update-attr-type-label').text();
    var attr_type_desc = $('#update-attr-type-desc').summernote('code');
    var id = id;
    jQuery.ajax({
        type: 'post',
        url: '/attribute-type/update-attr_type',
        data: {
            id: id,
            attr_type_name: attr_type_name,
            attr_type_label: attr_type_label,
            attr_type_desc: attr_type_desc,
        },
        success: function (status) {

        },
    });
}

function updateattr(id) {
    var attr_name = $('#update-attr-name').text();
    var attr_label = $('#update-attr-label').text();
    var attr_type = $('#update-attr-type').text();
    var attr_desc = $('#update-attr-desc').summernote('code');
    var id = id;
    jQuery.ajax({
        type: 'post',
        url: '/attributes/update-attr',
        data: {
            id: id,
            attr_name: attr_name,
            attr_label: attr_label,
            attr_type: attr_type,
            attr_desc: attr_desc,
        },
        success: function (status) {

        },
    });
}
/*Print functionality Function*/
function printFunction() {
    window.print();
}
/*End Print functionality Function*/

if ($('#customer_grid_multiselect').length > 0)
{
    // add multiple select / deselect functionality
    $("#customer_grid_multiselect").click(function () {
        $('.multi_delete_user').attr('checked', this.checked);
    });

    // if all checkbox are selected, check the selectall checkbox
    // and viceversa
    $(".multi_delete_user").click(function () {

        if ($(".case").length == $(".multi_delete_user:checked").length) {
            $("#customer_grid_multiselect").attr("checked", "checked");
        } else {
            $("#customer_grid_multiselect").removeAttr("checked");
        }

    });
}

