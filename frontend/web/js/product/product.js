

/*************DONUT CHART*****************PODUCT CHART START HERE****************************************************/

(function ($) {
    $(function () {
        "use strict";
        if ($('#donut-chart').hasClass('donut-chart') == true) {
            var user_id = $('#user_id').val();
            $('.ProductsCharTS').addClass('be-loading-active');
            $.ajax({
                method: 'post',
                url: '/product/donutchartonproduct',
                dataType: "json",
                data: {data: 'donutchartmonth', user_id: user_id},
                success: function (res) {
                    if (res == 'invalid') {
                        $('.customPeople').removeClass('active');
                        //$('#' + value).addClass('active');
                        $('.ProductsCharTS').removeClass('be-loading-active');
                        $('#donut-chart').html('');
                        $('#donut-chart').html('<div style="font-size:15px;text-align: center; padding-top: 7%;">Data  Not Available!!</div>');
                    } else {
                        var resp = JSON.parse(res.data);
                        var colors = JSON.parse(res.colors);
                        var id = JSON.parse(res.id);
                        $('#donut-chart').html('');
                        $('.customPeople').removeClass('active');
                        // $('#donutchartmonth').addClass('active');
                        $('#' + id).addClass('active');
                        $('#donutchartmonthmob').addClass('active');
                        $('.ProductsCharTS').removeClass('be-loading-active');
                        var donut = new Morris.Donut({
                            element: 'donut-chart',
                            data: resp,
                            hideHover: 'auto',
                            colors: colors,
                            resize: true,
                        });
                    }
                }
            });
        };
    });
})(jQuery);
/***********************************DONUT CHART ON BUTTON CLICK START HERE*******************************************************************/

(function ($) {
    $(function () {
        "use strict";
        if ($('#donut-chart').hasClass('donut-chart') == true) {
            $('body').on('click', '.customPeople', function () {
                var user_id = $('#user_id').val();
                $('.ProductsCharTS').addClass('be-loading-active');
                var value = $(this).attr('id');
                $.ajax({
                    method: 'post',
                    url: '/product/donutchartonproduct',
                    dataType: "json",
                    data: {data: value, user_id: user_id},
                    success: function (res) {

                        if (res == 'invalid') {
                            $('.customPeople').removeClass('active');
                            $('#' + value).addClass('active');
                            $('.ProductsCharTS').removeClass('be-loading-active');
                            $('#donut-chart').html('');
                            $('#donut-chart').html('<div style="font-size:15px;text-align: center; padding-top: 7%;">Data  Not Available!!</div>');
                        } else {
                            var resp = JSON.parse(res.data);
                            var colors = JSON.parse(res.colors);
                            $('#donut-chart').html('');
                            $('.ProductsCharTS').removeClass('be-loading-active');
                            $('.customPeople').removeClass('active');
                            $('#' + value).addClass('active');
                            var donut = new Morris.Donut({
                                element: 'donut-chart',
                                data: resp,
                                hideHover: 'auto',
                                colors: colors,
                                resize: true,
                            });
                        }
                    }
                });
            });
        }
        ;
    });
})(jQuery);

var lazada_category_id;
var lazada_product_id;

function getLazadaCategories($product_id) {
    $(".channel-categories #lazada_category").first().addClass('be-loading-active');
    $.ajax({
        url: '/product/get-lazada-categories?product_id='+$product_id,
        type: 'GET',
        success: function (response) {
            $(".channel-categories #lazada_category").first().removeClass("be-loading-active");
            $(".channel-categories #lazada_category").first().removeClass("be-loading");
            $('.be-wrapper').removeClass('be-loading-active');
            $(".channel-categories #lazada_category .be-spinner").hide();
            $('.channel-categories .lazada-category .list-group-root').html(response);
        },
        error: function(error) {
            $(".channel-categories #lazada_category").first().removeClass("be-loading-active");
            $(".channel-categories #lazada_category").first().removeClass("be-loading");
            $('.be-wrapper').removeClass('be-loading-active');
            $(".channel-categories #lazada_category .be-spinner").hide();
            console.log(error)
        }
    });
}

$(function () {
    if($('#product_id').val()) {
        getLazadaCategories($('#product_id').val());
    }

    lazada_product_id = $('#pID_created').val();

    $(document).on('click', '.lazada-category .list-group-item', function () {
        $('.glyphicon', this)
            .toggleClass('glyphicon-chevron-right')
            .toggleClass('glyphicon-chevron-down');

        $(this).closest('.list-group').find('.list-group-item').removeClass('cur');
        $(this).addClass('cur');

        if($(this).attr('cate')) {
            $(this).addClass('checked');
            lazada_category_id = $(this).attr('cate');
        }
    });

    $('#channelsonproductview').editable();

    /* Products Listing DataTable */
    $("#products_table1").dataTable({
        responsive: true,
        "order": [[5, "desc"]],
        pageLength: 25,
        "columnDefs": [
            {"orderable": false, "targets": 0},
            {"targets": 2, "orderable": false}
        ],
        "columns": [
            null,
            null,
            {className: "Custom_SCroll"},
            null,
            null,
            null,
            null
        ],
        "oLanguage": {
            sProcessing: function () {
                $(".productTABLE").first().addClass('be-loading-active');

            }
        },
        "initComplete": function () {
            $(".productTABLE").first().removeClass('be-loading-active');

        },
        drawCallback: function (settings) {
            var api = this.api();
            $('[data-toggle="tooltip"]').tooltip();
            $("[data-toggle='popover']").popover();
        },
        "processing": true,
        "serverSide": true,
        "ajax": "/product/products-ajax",
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
});


/**********For Single Product Perforamnce Main chart buttons click***************/

if ($('#hidden_product_id').length > 0 && $('.singleProductGraph')[0]) {

    var hidden_product_value = $('#hidden_product_id').val();
    if ($('#single_product_chart').hasClass('single_product_chart') == true) {
        $(document).ready(function () {
//            getSingleProductGraph('day', hidden_product_value, 'yes');
        });

    }
    function getSingleProductGraph(id, hidden_product_value, onload) {

        $('.PerformanceTab').addClass('be-loading-active');

        $.ajax({
            type: 'Post',
            dataType: 'json',
            data: {id: id, product_id: hidden_product_value},
            url: '/dashboard/single-product-graph',
            success: function (value) {
                $('.PerformanceTab').removeClass('be-loading-active');
                $('#chartInfoProduct').html('');
                $('#chartInfoProduct').html(value.showcolorhtml);
                if (onload == 'no') {
                    $('#single_product_chart').html('');
                }
                var resp = JSON.parse(value.data);
                var ykeyslabels = JSON.parse(value.ykeyslabels);
                var linecolors = JSON.parse(value.linecolors);
                var area = new Morris.Area({
                    element: 'single_product_chart',
                    resize: true,
                    data: resp,
                    xkey: 'period',
                    // xLabels: 'month',
                    parseTime: false,
                    //yLabelFormat:'Day 1',
                    ykeys: ykeyslabels,
                    labels: ykeyslabels,
                    lineColors: linecolors,
                    hideHover: 'auto'
                });

                var storesName = value.store_channel_names;
                var i = 0;
                var colorNo = 0;
                var t = 1;
                //foreach acc to coonected store names
                storesName.forEach(function (storeitem, storeindex) {
                    //every time to empty array for store sthe new values
                    var color2 = tinycolor(App.color.primary).lighten(colorNo).toString();
//                    console.log(color2);
                    if (i < storesName.length) {
                        i++;
                        $('[data-color="main-chart-color' + t + '"]').css({'background-color': color2});
                    }
                    t++;
                    colorNo = colorNo + 10;
                });
            }
        });
    }
    $('.singleProductGraph').on('click', function () {
        $this = $(this);
        id = $this.attr('data-id');
        $('.singleProductGraph').removeClass('active');
        $this.addClass('active');
        getSingleProductGraph(id, hidden_product_value, 'no');
    });
    $('.getLiGraph').on('click', function () {

        $('.custOMPerf').css('display', 'none');

        $this = $(this);
        getSingleProductGraph('month', hidden_product_value, 'no');
    });
}

// connected channel products
$(function () {

    /*Fields Product Image*/
    $('.pimg_lbl').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('.pimg_alt_tag').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('.pimg_html_video').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('.pimg_360_video').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    /*Add More Image - Product*/
    $('.addimg-btn').click(function (e) {
        e.preventDefault();
        var previousDivCount = $('#imgDivCount').val();
        var nextDivCount = parseInt(previousDivCount) + 1;
        var html = '';
        html = '<div id="pimgDiv_' + nextDivCount + '" class="col-sm-3 pimg-div' + nextDivCount + ' be-loading draggable-element" >'
        html += '<div class="bs-grid-block product_create_image product-texthover-default">'
        html += '<div class="user-display-bg">'
        html += '<div class="content dropzone pImg_Create_Drop1" id="pImageDrop_' + nextDivCount + '">'
        html += '</div>'
        html += '</div>'
        html += '</div>'
        html += '<input type="hidden" id="upld_img' + nextDivCount + '" class="upld_img" value="">'
        html += '<div class="setimg">'
        html += '<i class="icon icon-left mdi mdi-image"></i> Set as Default Image'
        html += '<div class="be-radio">'
        html += '<input name="pimg_radio" id="pimgRad_' + nextDivCount + '" type="radio">'
        html += '<label for="pimgRad_' + nextDivCount + '"></label>'
        html += '</div>'
        html += '</div>'
        html += '<table id="pimg_tbl' + nextDivCount + '" style="clear: both" class="table table-striped table-borderless">'
        html += '<tbody>'
        html += '<tr>'
        html += '<td width="45%">Image Label</td>'
        html += '<td width="55%"><a id="pimg_lbl' + nextDivCount + '" class="pimg_lbl" href="#" data-type="text" data-title="Please Enter value"></a></td>'
        html += '</tr>'
        html += '<tr>'
        html += '<td width="45%">Alt Tag</td>'
        html += '<td width="55%"><a id="pimg_alt_tag' + nextDivCount + '" class="pimg_alt_tag" href="#" data-type="text" data-title="Please Enter value"></a></td>'
        html += '</tr>'
        html += '<tr>'
        html += '<td width = "45%">HTML Video Link </td>'
        html += '<td width = "55%"><a id="pimg_html_video' + nextDivCount + '" class = "pimg_html_video" href = "#" data - type = "text" data - title = "Please Enter value(Only support Youtube and Vimeo)" > </a></td >'
        html += '</tr>'
        html += '</tbody>'
        html += '</table>'
        html += '<div class="vfile"><input type="file" name="pimg_360_video" id="pimg_360_video' + nextDivCount + '" data-multiple-caption="{count} files selected" multiple class="inputfile" accept="video/*">'
        html += '<label for="pimg_360_video' + nextDivCount + '" class="btn-default"> <i class="mdi mdi-upload"></i>'
        html += '<span> Select 360 - degree video </span>'
        html += '</label>'
        html += '</div>'
        html += '<div class="progress" id="pimg_360_video_progress_wrapper' + nextDivCount + '">'
        html += '<div id="pimg_360_video_progress' + nextDivCount + '" class="progress-bar progress-bar-primary progress-bar-striped"></div>'
        html += '</div>'
        html += '<div class="vupld">'
        html += '<button id="vupldbtn_' + nextDivCount + '" class="btn btn-rounded btn-space btn-default vupld_btn">Upload</button>'
        html += '</div>'
        html += '<div class="pimg_save_btns"> <button id="pimgSaveBtn_' + nextDivCount + '" class="btn btn-space btn-primary btn-sm pimg_save_btn">Save</button></div>'
        html += '<div class="be-spinner"><svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg"><circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"/></svg></div>'
        html += '</div>';
        $(html).insertAfter(".pimg-div" + previousDivCount);
        $('#imgDivCount').val(nextDivCount);
        $('.pimg_lbl').editable({
            validate: function (value) {
                if ($.trim(value) == '') {
                    return 'This field is required';
                }
            }
        });
        $('.pimg_alt_tag').editable({
            validate: function (value) {
                if ($.trim(value) == '') {
                    return 'This field is required';
                }
            }
        });
        $('.pimg_html_video').editable({
            validate: function (value) {
                if ($.trim(value) == '') {
                    return 'This field is required';
                }
            }
        });
        $('.pimg_360_video').editable({
            validate: function (value) {
                if ($.trim(value) == '') {
                    return 'This field is required';
                }
            }
        });
        $("#pImageDrop_" + nextDivCount).dropzone({
            url: "product-image-upload",
            maxFilesize: 2, // MB
            maxFiles: 1,
            acceptedFiles: 'image/*',
            dictDefaultMessage: "Add Product Image",
            dictInvalidFileType: "Please upload Image files.",
            dictMaxFilesExceeded: "You can not upload any more files.",
            init: function () {
                var img_div_id = $(this.element).attr("id");
                var id_arr = img_div_id.split('_');
                var imgdivnum = id_arr[1];
                this.on("sending", function (file, xhr, formData) {
                    var productId = $('#pID_created').val();
                    // Will send the Product Id(Temp) along with the file as POST data.
                    formData.append("pid", productId);
                });
                this.on("success", function (file, responseText) {
                    var responsetext = JSON.parse(file.xhr.responseText);
                    if (responsetext.status == 'success') {
                        var imgId = responsetext.imgId;
                        var imgLbl = responsetext.imgLabel;
                        $('#upld_img' + imgdivnum).val(imgId);
                        $('#pimg_lbl' + imgdivnum).html(imgLbl);
                        $('.pimg-div' + imgdivnum + ' .bs-grid-block').css('border', '2px dashed #c3c3c3');
                    }
                });
            }
        });
        $('.draggable-element').arrangeable();
    });
    /*Product Create Wizard 1st Step Validation & Move to Next Step*/
    $(".wizard-next1").click(function (e) {
        var id = $(this).data("wizard");
        //General Step Fields
        var pName = $('#pName_create input').val();
        var pSKU = $('#pSKU_create input').val();
        var pHTS = $('#pHTS_create input').val();
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

        //General Step Required Fields Validation
        var isValid = true;
        if (pName == '') {
            $('#pName_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pName_create').hasClass('has-error')) {
                $('#pName_create').removeClass('has-error');
            }
        }
        if (pSKU == '') {
            $('#pSKU_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pSKU_create').hasClass('has-error')) {
                $('#pSKU_create').removeClass('has-error');
            }
        }
        if (pHTS == '') {
            $('#pHTS_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pHTS_create').hasClass('has-error')) {
                $('#pHTS_create').removeClass('has-error');
            }
        }
        if (pUPC == '') {
            $('#pUPC_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pUPC_create').hasClass('has-error')) {
                $('#pUPC_create').removeClass('has-error');
            }
        }
        if (pEAN == '') {
            $('#pEAN_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pEAN_create').hasClass('has-error')) {
                $('#pEAN_create').removeClass('has-error');
            }
        }
        if (pJAN == '') {
            $('#pJAN_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pJAN_create').hasClass('has-error')) {
                $('#pJAN_create').removeClass('has-error');
            }
        }
        if (pISBN == '') {
            $('#pISBN_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pISBN_create').hasClass('has-error')) {
                $('#pISBN_create').removeClass('has-error');
            }
        }
        if (pMPN == '') {
            $('#pMPN_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pMPN_create').hasClass('has-error')) {
                $('#pMPN_create').removeClass('has-error');
            }
        }
        if (pBrand == '') {
            $('#pBrand_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pBrand_create').hasClass('has-error')) {
                $('#pBrand_create').removeClass('has-error');
            }
        }
        if (pWeight == '') {
            $('#pWght_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pWght_create').hasClass('has-error')) {
                $('#pWght_create').removeClass('has-error');
            }
        }

        if (isValid) {
            $.ajax({
                type: 'post',
                url: 'create-product',
                data: {
                    pName: pName,
                    pSKU: pSKU,
                    pHTS: pHTS,
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
                },
                success: function (pid) {
                    $('#pID_created').val(pid);
                    $(id).wizard('next');
                },
            });
        }
        e.preventDefault();

    });

    $(".wizard-next2").click(function (e) {
        var id = $(this).data("wizard");

        $(id).wizard('next');
        e.preventDefault();
    });

    $(".wizard-next3").click(function (e) {
        var id = $(this).data("wizard");
        var category_id = $('#category_create select option:selected').val();
        if (category_id != 'Please Select') {
            $('.category_add').addClass('be-loading-active');
            var pID = $('#pID_created').val();
            $.ajax({
                type: 'post',
                url: 'category-add',
                data: {
                    pID: pID,
                    category_id: category_id
                },
                success: function (pid) {
                    $('.category_add').removeClass('be-loading-active');
                    $(id).wizard('next');
                },
            });
        }
        else {
            $("#category_ajax_header_error_msg").html("Please select Category!");
            $('.category_ajax_request_error').modal('show');
        }
        e.preventDefault();
    });

    $(".wizard-next4").click(function (e) {
        var id = $(this).data("wizard");
        var channel_id = $('#channel_create select option:selected').val();
        if (channel_id != 'Please Select') {
            $('.channel_add').addClass('be-loading-active');
            var pID = $('#pID_created').val();
            $.ajax({
                type: 'post',
                url: 'channel-add',
                data: {
                    pID: pID,
                    channel_id: channel_id
                },
                success: function (pid) {
                    $('.channel_add').removeClass('be-loading-active');
                    $(id).wizard('next');
                },
            });
        }
        else {
            $(id).wizard('next');
        }
        e.preventDefault();
    });

    $(".wizard-next5").click(function (e) {
        var id = $(this).data("wizard");
        var stock_level = $('#pstk_lvl_create select option:selected').text();
        var stock_status = $('#pstk_sts_create select option:selected').text();
        if (stock_level == 'Please Select')
            stock_level = "In Stock";
        if (stock_status == 'Please Select')
            stock_status = "Visible";
        var low_stock_notification = $('#plw_stk_ntf_create input').val();
        if (low_stock_notification == '') {
            $('#plw_stk_ntf_create').addClass('has-error');

        } else {
            if ($('#plw_stk_ntf_create').hasClass('has-error')) {
                $('#plw_stk_ntf_create').removeClass('has-error');
            }
            var pID = $('#pID_created').val();
            $('.inventory_add').addClass('be-loading-active');
            $.ajax({
                type: 'post',
                url: 'inventory-add',
                data: {
                    pID: pID,
                    stock_level: stock_level,
                    stock_status: stock_status,
                    low_stock_notification: low_stock_notification,
                },
                success: function (pid) {
                    $('.inventory_add').removeClass('be-loading-active');
                    $(id).wizard('next');
                },
            });
        }

        e.preventDefault();
    });

    $(".wizard-next6").click(function (e) {
        var id = $(this).data("wizard");
        $(id).wizard('next');
        e.preventDefault();
    });

    $(".wizard-next7").click(function (e) {

        var pID = $('#pID_created').val();
        var price = $('#pprice_create input').val();
        var sale_price = $('#psale_price_create input').val();
        var schedule_date = $('#psched_date1_create input').val();
        var isValid = true;
        if (price == '') {
            $('#pprice_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#pprice_create').hasClass('has-error')) {
                $('#pprice_create').removeClass('has-error');
            }
        }
        if (sale_price == '') {
            $('#psale_price_create').addClass('has-error');
            isValid = false;
        } else {
            if ($('#psale_price_create').hasClass('has-error')) {
                $('#psale_price_create').removeClass('has-error');
            }
        }
        if (isValid) {
            $.ajax({
                type: 'post',
                url: 'sale-add',
                data: {
                    pID: pID,
                    price: price,
                    sale_price: sale_price,
                    schedule_date: schedule_date,
                },
                success: function (pid) {
                    window.location.href = '/product';
                },
            });
        }
        e.preventDefault();
    });

    $(".wizard-previous1").click(function(e){

    });

    $(".wizard-previous2").click(function(e){
        var id = $(this).data("wizard");
        $(id).wizard('previous');
        e.preventDefault();
    });

    $(".wizard-previous3").click(function(e){
        var id = $(this).data("wizard");
        $(id).wizard('previous');
        e.preventDefault();
    });

    $(".wizard-previous4").click(function(e){
        var id = $(this).data("wizard");
        $(id).wizard('previous');
        e.preventDefault();
    });

    $(".wizard-previous5").click(function(e){
        var id = $(this).data("wizard");
        $(id).wizard('previous');
        e.preventDefault();
    });

    $(".wizard-previous6").click(function(e){
        var id = $(this).data("wizard");
        $(id).wizard('previous');
        e.preventDefault();
    });

    $(".wizard-previous7").click(function(e){
        var id = $(this).data("wizard");
        $(id).wizard('previous');
        e.preventDefault();
    });

    /* END*/

    $(".datetimepicker1").datetimepicker({
        autoclose: true,
        componentIcon: '.mdi.mdi-calendar',
        navIcons:{
            rightIcon: 'mdi mdi-chevron-right',
            leftIcon: 'mdi mdi-chevron-left'
        }
    });

    //Select2
    $(".select2").select2({
        width: '100%'
    });

    //Select2 tags
    $(".tags").select2({tags: true, width: '100%'});

    /*Adding Product Variations*/
    $(document).on("change", "#pvar_create select", function (event) {
        var pVar_Id = $('#pvar_create select option:selected').val();
        var pVar_Name = $('#pvar_create select option:selected').text();
        var current_user_id = $('#current_user_id').val();
        $("#pvaritems_create select").empty();
        $('#pvaritems_create select').append($('<option>', {
            value: '',
            text: 'Please Select'
        }));
        if (pVar_Name !== 'Please Select') {
            $.ajax({
                type: 'post',
                url: 'get-variant-items',
                datatype: 'json',
                data: {
                    pVar_Id: pVar_Id,
                    current_user_id: current_user_id,
                },
                success: function (data) {
                    var jsonObject = JSON.parse(data);
                    var result = $.map(jsonObject, function (val, key) {
                        $('#pvaritems_create select').append($('<option>', {
                            value: key,
                            text: val
                        }));
                    });
                }
            });
        }
    });

    //    $('.pvarSKU').editable();
    $('.pvarSKU').editable({
        success: function (data, value) {
            var changedObjectKey = $(this).attr('data-key');
            var changedObjectField = $(this).attr('data-field');
            $.ajax({
                type: 'post',
                url: '/product/variation-update',
                data: {
                    itemKey: changedObjectKey,
                    itemVal: value,
                    itemField: changedObjectField,
                },
                success: function (result) {
                    console.log('update success');
                },
            });
        }
    });
    //    $('.pvarInventory').editable();
    $('.pvarPrice').editable({
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        },
        success: function (data, value) {
            var changedObjectKey = $(this).attr('data-key');
            var changedObjectField = $(this).attr('data-field');
            var changedItemValue = parseFloat(Math.round(value * 100) / 100).toFixed(2);
            $.ajax({
                type: 'post',
                url: '/product/variation-update',
                data: {
                    itemKey: changedObjectKey,
                    itemVal: changedItemValue,
                    itemField: changedObjectField,
                },
                success: function (result) {
                    console.log('update success');
                },
            });
        }
    });
    $('.pvarWeight').editable({
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        },
        success: function (data, value) {
            var changedObjectKey = $(this).attr('data-key');
            var changedObjectField = $(this).attr('data-field');
            var changedItemValue = parseFloat(Math.round(value * 100) / 100).toFixed(2);
            $.ajax({
                type: 'post',
                url: '/product/variation-update',
                data: {
                    itemKey: changedObjectKey,
                    itemVal: changedItemValue,
                    itemField: changedObjectField,
                },
                success: function (result) {
                    console.log('update success');
                },
            });
        }
    });

    $('.pPrice').editable({
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        },
        success: function (data, value) {
            var pId = $('#product_id').val();
            var changedPriceValue = parseFloat(Math.round(value * 100) / 100).toFixed(2);
            var items = $('.price_connect');
            for (i = 0; i < items.length; i++) {
                var rate =  $(items[i]).attr("data-rate");
                $(items[i]).text(parseFloat(Math.round(rate * changedPriceValue * 100) / 100).toFixed(2));
            }
            $.ajax({
                type: 'post',
                url: '/product/price-update',
                data: {
                    pId: pId,
                    pPrice: changedPriceValue,
                },
                success: function (result) {
                    console.log('update success');
                },
            });
            console.log(items);
        }
    });

    $('.pSalePrice').editable({
        success: function (data, value) {
            var pId = $('#product_id').val();
            var changedPriceValue = parseFloat(Math.round(value * 100) / 100).toFixed(2);
            var items = $('.sale_price_connect');
            for (i = 0; i < items.length; i++) {
                var rate =  $(items[i]).attr("data-rate");
                $(items[i]).text(parseFloat(Math.round(rate * changedPriceValue * 100) / 100).toFixed(2));
            }
            console.log(items);
        }
    });

    $('.price_connect').editable({
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        },
        success: function (data, value) {
            var pId = $('#product_id').val();
            var changedPriceValue = parseFloat(Math.round(value * 100) / 100).toFixed(2);
            var origin_rate = $(this).attr("data-rate");
            $('.pPrice').text(parseFloat(Math.round(changedPriceValue /origin_rate * 100) / 100).toFixed(2));
            var items = $('.price_connect');
            for (i = 0; i < items.length; i++) {
                if ($(this) == $(items[i]))
                    continue;
                var rate =  $(items[i]).attr("data-rate");
                $(items[i]).text(parseFloat(Math.round(rate * changedPriceValue * 100) / 100).toFixed(2));
            }
            $.ajax({
                type: 'post',
                url: '/product/price-update',
                data: {
                    pId: pId,
                    pPrice: parseFloat(Math.round(changedPriceValue /origin_rate * 100) / 100).toFixed(2),
                },
                success: function (result) {
                    console.log('update success');
                },
            });
            console.log(items);
        }
    });

    $('.sale_price_connect').editable({
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        },
        success: function (data, value) {
            var pId = $('#product_id').val();
            var changedPriceValue = parseFloat(Math.round(value * 100) / 100).toFixed(2);
            var origin_rate = $(this).attr("data-rate");
            $('.pSalePrice').text(parseFloat(Math.round(changedPriceValue /origin_rate * 100) / 100).toFixed(2));

            var items = $('.sale_price_connect');
            for (i = 0; i < items.length; i++) {
                if ($(this) == $(items[i]))
                    continue;
                var rate =  $(items[i]).attr("data-rate");
                $(items[i]).text(parseFloat(Math.round(rate * changedPriceValue * 100) / 100).toFixed(2));
            }
            console.log(items);
        }
    });
//    $('.pvaritems').editable();

    $('.pvariation_tbl_body').hide();

    /*Variations Step - Product Create*/
    $(document).on("click", ".add_pvar", function (event) {
        var pVar_Id = $('#pvar_create select option:selected').val();
        var pVar_Name = $('#pvar_create select option:selected').text();
        var pVarItem_Id = $('#pvaritems_create select option:selected').val();
        var pVarItem_Name = $('#pvaritems_create select option:selected').text();
        var columns_added_bfr = $('#columns_added').val();
        var columns_count_bfr = $('#columns_count').val();
        var total_rows = $('#rows_count').val();
        var current_user_id = $('#current_user_id').val();
        var paramString = "";

        if (pVar_Name == 'Please Select') {
            $('#pvar_create .select2-container--default').css('border', '1px solid red');
            $('#pvaritems_create .select2-container--default').css('border', '1px solid #dedede');
        } else if (pVarItem_Name == 'Please Select') {
            $('#pvar_create .select2-container--default').css('border', '1px solid #dedede');
            $('#pvaritems_create .select2-container--default').css('border', '1px solid red');
        } else {
            var varheadersArr = [];
            $('.pvariation_tbl_body').show();
            $('#pvar_create .select2-container--default').css('border', '1px solid #dedede');
            $('#pvaritems_create .select2-container--default').css('border', '1px solid #dedede');

            //var pVar_Name_Arr = pVar_Name.split('|');
            //var real_pVar_Name = pVar_Name_Arr[0].trim();
            var real_pVar_Name = "";

            $.ajax({
                type: 'post',
                url: 'get-variation-item-name',
                dataType: "json",
                data: {
                    pItemValueId: pVarItem_Id,
                    current_user_id: current_user_id,
                },
                success: function (response) {
                    //$('.pvariation_tbl_body').removeClass('be-loading-active');
                    /*HTML for SKU,Inventory,Price,Weight,Actions Columns*/
                    real_pVar_Name = response['item_name'];

                    var sku_html = '<a id="pvarSKU_' + pVarItem_Id + '" class="pvarSKU" href="#" data-type="text" data-title="Please Enter value (must be unique)" ></a>';
                    var inv_html = '<a id="pvarInv_' + pVarItem_Id + '" class="pvarInventory" href="#" data-type="text" data-title="Please Enter value (must be unique)" ></a>';
                    var price_html = '<a id="pvarPrice_' + pVarItem_Id + '" class="pvarPrice" href="#" data-type="text" data-title="Please Enter value (must be unique)" ></a>';
                    var weight_html = '<a id="pvarWeight_' + pVarItem_Id + '" class="pvarWeight" href="#" data-type="text" data-title="Please Enter value (must be unique)" ></a>';
                    var actions_html = '<a id="pvarActions_' + pVarItem_Id + '" class="pvarActions icon" href="#" title="Remove Variant"><i class="mdi mdi-delete"></i></a>';
                    var table_th = $('#pvariation_tbl th').eq(0).text();
                    //When 1st variation Item Added
                    if (table_th == "Variant Name") {
                        $('#pvariation_tbl th').eq(0).text(real_pVar_Name);
                        $('#pvariation_tbl tbody tr:first').html('<td>'
                            + pVarItem_Name + '</td><td>'
                            + sku_html + '</td><td>'
                            + inv_html + '</td><td>'
                            + price_html + '</td><td>'
                            + weight_html + '</td><td>'
                            + actions_html + '</td>');

                        $('#pvariation_tbl tbody tr:first').attr('id', pVar_Id + "-" + pVarItem_Id);
                        $('#rows_count').val(parseInt(total_rows) + 1);
                        if (columns_added_bfr == '') {
                            var columns_added_new = real_pVar_Name;
                        } else {
                            var columns_added_new = columns_added_bfr + ',' + real_pVar_Name;
                        }
                        $('#columns_added').val(columns_added_new);
                        $('#columns_count').val(parseInt(columns_count_bfr) + 1);
                    } else {
                        var varArr = [];
                        $('#pvariation_tbl th').each(function (i) {
                            var th_val = $('#pvariation_tbl th').eq(i).text();
                            varArr.push(th_val);
                        });
                        //When new variation column added before pre Variation Item
                        if ($.inArray(real_pVar_Name, varArr) == -1) {
                            $('#pvariation_tbl thead th:first-child').before('<th class="' + real_pVar_Name + '">' + real_pVar_Name + '</th>');
                            $('#pvariation_tbl tbody tr').each(function () {
                                $(this).find('td:first').before('<td>' + pVarItem_Name + '</td>');
                            });
                            if (columns_added_bfr == '') {
                                var columns_added_new = real_pVar_Name;
                            } else {
                                var columns_added_new = columns_added_bfr + ',' + real_pVar_Name;
                            }
                            $('#columns_added').val(columns_added_new);
                            $('#columns_count').val(parseInt(columns_count_bfr) + 1);
                        } else {
                            var columnsArr = [];
                            if (columns_added_bfr.indexOf(',') == -1) {
                                columnsArr.push(columns_added_bfr);
                            } else {
                                columnsArr = columns_added_bfr.split(',');
                            }
                            var countArr = columnsArr.length;
                            var html = '';
                            var tr_id_str = pVar_Id + "-" + pVarItem_Id;
                            html = '<tr id="' + tr_id_str + '">';
                            for (var i = parseInt(countArr) - 1; i >= 0; i--) {
                                if (columnsArr[i] == real_pVar_Name) {
                                    html += '<td>' + pVarItem_Name + '</td>';
                                } else {
                                    paramString = "get-varitems?var_name=" + columnsArr[i] + "&user_id=" + current_user_id;
                                    html += '<td><a id="pvar_' + columnsArr[i]
                                        + '_' + pVarItem_Id
                                        + '" class="pvaritems" href="javascript:" data-title="Please Select" data-type="select" data-value="" data-pk="1" class="editable editable-click" data-source="' + paramString + '"></a></td>';
                                }
                            }
                            html += '<td>' + sku_html + '</td>';
                            html += '<td>' + inv_html + '</td>';
                            html += '<td>' + price_html + '</td>';
                            html += '<td>' + weight_html + '</td>';
                            html += '<td>' + actions_html + '</td>';
                            html += '</tr>';
                            $('#pvariation_tbl tr:last').after(html);
                            $('#rows_count').val(parseInt(total_rows) + 1);
                        }
                    }

                    //Editable Fields
                    $('.pvarSKU').editable();
                    $('.pvarInventory').editable({
                        validate: function (value) {
                            if ($.isNumeric(value) == '') {
                                return 'Only numbers are allowed';
                            }
                        }});
                    $('.pvarPrice').editable({
                        validate: function (value) {
                            if ($.isNumeric(value) == '') {
                                return 'Only numbers are allowed';
                            }
                        }});
                    $('.pvarWeight').editable({
                        validate: function (value) {
                            if ($.isNumeric(value) == '') {
                                return 'Only numbers are allowed';
                            }
                        }});
                    $('.pvaritems').editable();


                },
            });

        }
        event.preventDefault();
    });
    /*End*/

    /*Product Variations Save*/
    $(document).on("click", ".save_pvar", function (event) {
        $('.pvariation_tbl_body').addClass('be-loading-active');
        var pID = $('#pID_created').val();
        var current_user_id = $('#current_user_id').val();
        var varHeaderArr = [];
        $('#pvariation_tbl th:not(:last-child)').each(function (i) {
            var th_val = $('#pvariation_tbl th').eq(i).text();
            varHeaderArr.push(th_val);
        });

        var variationData = [];

        $('#pvariation_tbl tbody tr').each(function (i) {
            var variation_row = [];
            $(this).find('td:not(:last-child)').each(function (j) {
                variation_row.push($(this).text());
            });
            variationData.push(variation_row);
        });

        $.ajax({
            type: 'post',
            url: 'save-product-variations',
            data: {
                pID: pID,
                current_user_id: current_user_id,
                varHeaderArr: varHeaderArr,
                varRowArr: variationData,
            },
            success: function (value) {
                $('.pvariation_tbl_body').removeClass('be-loading-active');
            },
        });

        event.preventDefault();
    });
    /*End*/

    /*Remove the Variant Row - Product Create*/
    $(document).on("click", ".pvarActions", function (event) {

        var pID = $('#pID_created').val();
        var tr_id = $(this).closest('tr').attr('id');
        $('table#pvariation_tbl tr#' + tr_id).remove();
        //Remove Table row data from product_variations table
        /*
        $('.pvariation_tbl_body').addClass('be-loading-active');
        $.ajax({
            type: 'post',
            url: 'remove-product-variations',
            data: {
                pID: pID,
                tr_id: tr_id,
            },
            success: function (value) {
                $('.pvariation_tbl_body').removeClass('be-loading-active');
            },
        });
        */
        event.preventDefault();
    });
    /*End*/
    //$("#skus_tbl").dataTable();
    /*Product Variations Save - Product Update*/
    $(document).on("click", ".save_pvar_update", function (event) {

//        $('#variations .skus').addClass('be-loading-active');
        var pID = $('#pID_created').val();
        var varHeaderArr = [];
//        $('#skus_tbl thead th:not(:first-child):not(:last-child)').each(function (i) {
        $('#skus_tbl thead th:not(:first-child)').each(function (i) {
            var th_val = $(this).text();
            varHeaderArr.push(th_val);
        });
        console.log(varHeaderArr);
        $('#skus_tbl tbody tr').each(function (i) {
            var row_id = $(this).attr('id');
            var varRowArr = [];
            var varOptionNamesArr = [];
            var varOptionValuesArr = [];
            var varSKU = $(this).find('.pvarSKU').text();
            $(this).find('.op_div').each(function (k) {
                var op_name = $(this).find('.option_name').text();
                var op_val = $(this).find('.pvaritems').text();
                varOptionNamesArr.push(op_name);
                varOptionValuesArr.push(op_val);
            });
//             console.log(varOptionNamesArr);
//             console.log(varOptionValuesArr);
//            $(this).find('td:not(:first-child):not(:last-child)').each(function (j) {
            $(this).find('td:not(:first-child)').each(function (j) {
                varRowArr.push($(this).text());
            });
//            console.log(varRowArr);
//             alert(varSKU);
//             alert(row_id);
//             alert(pID);
//            return false;
            //Save each Table row data to product_variations table
            $.ajax({
                type: 'post',
                url: 'save-product-variations',
                data: {
                    pID: pID,
                    varHeaderArr: varHeaderArr,
                    row_id: row_id,
                    varSKU: varSKU,
                    varOptionNamesArr: varOptionNamesArr,
                    varOptionValuesArr: varOptionValuesArr,
                    varRowArr: varRowArr,
                },
                success: function (value) {
                    $('#variations .skus').removeClass('be-loading-active');
                },
            });
        });
        event.preventDefault();
    });
    /*End*/

    /*Remove the Variant Row - Product Update*/
    $(document).on("click", ".pvarActions_update", function (event) {
        $('#variations .skus').addClass('be-loading-active');
        var pID = $('#pID_created').val();
        var tr_id = $(this).closest('tr').attr('id');
        $('table#skus_tbl tr#' + tr_id).remove();
        //Remove Table row data from product_variations table
        $.ajax({
            type: 'post',
            url: 'remove-product-variations',
            data: {
                pID: pID,
                tr_id: tr_id,
            },
            success: function (value) {
                $('#variations .skus').removeClass('be-loading-active');
            },
        });

        event.preventDefault();
    });
    /*End*/


    /*Variations Step - Product Update - Add Variation*/
    $(document).on("click", ".addsku-btn", function (event) {
        var var_set = $('#var_set_tbl #op_set').text();
        var current_user_id = $('#current_user_id').val();
        //Get the Variant Combinations row using Variation Set value
        $.ajax({
            type: 'post',
            url: 'add-product-variations-row',
            data: {
                var_set: var_set,
            },
            success: function (value) {
                var op_arr = JSON.parse(value);
                var op_html = '';
                var paramString = '';
                $.each(op_arr, function (index, val) {

                    paramString = "get-varitems?var_name=" + val + "&user_id=" + current_user_id;

                    op_html += '<div class="op_div">';
                    op_html += '<span class="option_name">' + val + ': </span>';
                    op_html += '<a class="pvaritems" href="javascript:" data-title="Please Select" data-type="select" data-value="" data-pk="1" class="editable editable-click" data-source="' + paramString + '"></a>';
                    op_html += '</div>';
                });
                /*HTML for SKU,Inventory,Price,Weight,Actions Columns*/
                var sku_html = '<a class="pvarSKU" href="#" data-type="text" data-title="Please Enter value (must be unique)" ></a>' + op_html + '';
                var inv_html = '<a class="pvarInventory" href="#" data-type="text" data-title="Please Enter value (must be unique)" ></a>';
                var price_html = '<a class="pvarPrice" href="#" data-type="text" data-title="Please Enter value (must be unique)" ></a>';
                var weight_html = '<a class="pvarWeight" href="#" data-type="text" data-title="Please Enter value (must be unique)" ></a>';
                var actions_html = '<a class="pvarActions_update icon" href="#" title="Remove Variant"><i class="mdi mdi-delete"></i></a>';
                var row_id = $.now();
                $('#skus_tbl tbody tr:first').before('<tr id=Elli_' + row_id + '><td>' + sku_html + '</td><td>' + inv_html + '</td><td>' + price_html + '</td><td>' + weight_html + '</td><td>' + actions_html + '</td></tr>');
                //Editable Fields
                $('.pvarSKU').editable();
                $('.pvarInventory').editable();
                $('.pvarPrice').editable();
                $('.pvarWeight').editable();
                $('.pvaritems').editable();
            },
        });
        event.preventDefault();
    });
    /*End*/

    /*Media Step - Product Create Wizard*/
    $(".pImg_Create_Drop1").dropzone({
        url: "product-image-upload",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "Add Product Image",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            var img_div_id = $(this.element).attr("id");
            var id_arr = img_div_id.split('_');
            var imgdivnum = id_arr[1];
            this.on("sending", function (file, xhr, formData) {
                var productId = $('#pID_created').val();
                // Will send the Product Id(Temp) along with the file as POST data.
                formData.append("pid", productId);
            });
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    var imgId = responsetext.imgId;
                    var imgLbl = responsetext.imgLabel;
                    $('#upld_img' + imgdivnum).val(imgId);
                    $('#pimg_lbl' + imgdivnum).html(imgLbl);
                    $('.pimg-div' + imgdivnum + ' .bs-grid-block').css('border', '2px dashed #c3c3c3');
                }
            });
        }
    });
    /*Upload 360-degree video*/
    $(document).on("click", ".vupld_btn", function (event) {
        event.preventDefault();
        var btn_id = this.id;
        var id_arr = btn_id.split('_');
        var id_num = id_arr[1];
        //Get Image Id
        var img_Id = $('#upld_img' + id_num).val();
        if (img_Id == '') {
            $('.pimg-div' + id_num + ' .bs-grid-block').css('border', '2px dashed red');
            return false;
        } else {
            var img_360 = $('#pimg_360_video' + id_num)[0];
            var videos_count = img_360.files.length;
            if (videos_count == 0) {
                $('.pimg-div' + id_num + ' .inputfile + label').css('border', '1px solid red');
            } else {
                $('.pimg-div' + id_num + ' .inputfile + label').css('border', '1px solid #dedede');
                var file_type = img_360.files[0].type;
                console.log(file_type);
                if (file_type !== "video/x-quicktime" && file_type !== "video/quicktime" &&
                    file_type !== "video/m4v" && file_type !== "video/x-m4v" && file_type !== "video/avi" &&
                    file_type !== "video/mov" && file_type !== "video/x-msvideo" && file_type !== "video/wmv" &&
                    file_type !== "video/x-ms-wmv" && file_type !== "video/mpg" && file_type !== "video/mpeg" &&
                    file_type !== "video/ogv" && file_type !== "video/ogm" && file_type !== "video/ogx" &&
                    file_type !== "video/mp4" && file_type !== "video/ogg" &&
                    file_type !== "video/flv" && file_type !== "video/rm" && file_type !== "video/rmvb" &&
                    file_type !== "video/webm" && file_type !== "video/wmv" && file_type !== "video/xvid" && file_type !== "video/divx") {
                    $('#mod-360video-alert').modal('show');
                } else
                {
                    var img_360_video;
                    img_360_video = new FormData();
                    img_360_video.append('file', img_360.files[0]);
                    $.ajax({
                        xhr: function ()
                        {
                            var xhr = new window.XMLHttpRequest();
                            var prgs = 0;
                            $('#pimg_360_video_progress_wrapper' + id_num).show();
                            //Upload progress
                            xhr.upload.addEventListener("progress", function (event) {
                                if (event.lengthComputable) {
                                    console.log(event.lengthComputable);
                                    console.log(event.loaded);
                                    console.log(event.total);
                                    var percentComplete = event.loaded / event.total;
                                    if (percentComplete < 1) {
                                        if (prgs < 90) {
                                            prgs = parseInt(prgs) + 10;
                                        }
                                    } else {
                                        prgs = 100;
                                    }
                                    //Do something with upload progress
                                    $("#pimg_360_video_progress" + id_num).width(prgs + '%');
                                }
                            }, false);
                            return xhr;
                        },
                        type: 'post',
                        url: 'upload-product-image-video360?imgId=' + img_Id,
                        data: img_360_video,
                        processData: false,
                        contentType: false,
                        success: function (value) {
                            if (value.video_src != '') {
                                $('#pimgDiv_' + id_num + ' .vupld').hide();
                                console.log(value);
                            }
                        },
                    });
                }
            }
        }
    });
    /*Save The Media Step Uploaded Image with Fields - Product Create*/
    $(document).on("click", ".pimg_save_btn", function (e) {

        e.preventDefault();
        var btn_id = this.id;
        var id_arr = btn_id.split('_');
        var id_num = id_arr[1];
        //Get Image Id

        var img_Id = $('#upld_img' + id_num).val();
        if (img_Id == '') {
            $('.pimg-div' + id_num + ' .bs-grid-block').css('border', '2px dashed red');
            return false;
        } else {
            $('.pimg-div' + id_num).addClass('be-loading-active');
            //Get the Particular Image Attributes
            var img_selected_default = $('input:radio[id=pimg_rad' + id_num + ']:checked').length;
            var img_label = $('#pimg_lbl' + id_num).text();
            var img_alt = $('#pimg_alt_tag' + id_num).text();
            var img_html_video = $('#pimg_html_video' + id_num).text();
            $.ajax({
                type: 'post',
                url: 'save-product-image',
                data: {
                    img_Id: img_Id,
                    img_default: img_selected_default,
                    img_label: img_label,
                    img_alt: img_alt,
                    img_html_video: img_html_video,
                },
                success: function (value) {
                    if (value == 'success') {
                        $('.pimg-div' + id_num).removeClass('be-loading-active');
                    }
                },
            });
        }

    });
    /*END*/

    /*connected Products Listing DataTable */
    /*get current action in hidden field*/
    var user_connection_id = $("#user_connection_id").val();
    $("#connectedproducts_table1").dataTable({
        responsive: true,
        "order": [[5, "desc"]],
        pageLength: 25,
        "columnDefs": [
            {"orderable": false, "targets": 0},
            {"targets": 2, "orderable": false}
        ],
        "columns": [
            null,
            null,
            {className: "Custom_SCroll"},
            null,
            null,
            null,
            null
        ],
        "oLanguage": {
            sProcessing: function () {
                $(".ConnectedProDUCTS").first().addClass('be-loading-active');
            }
        },
        "initComplete": function () {
            $(".ConnectedProDUCTS").first().removeClass('be-loading-active');
        },
        "processing": true,
        "serverSide": true,
        "ajax": "/product/products-ajax?user_connection_id=" + user_connection_id,
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });

    /* END */
});

function updatedTranslation(smartling_id){
    var smartling_editor = $('#smartling_editor' + smartling_id).summernote('code');
    var product_translate_title = $('#product_translate_title' + smartling_id).text();
    var product_translate_brand = $('#product_translate_brand' + smartling_id).text();
    $.ajax({
        type: 'post',
        url: '/product/update-translation',
        data: {
            smartling_id: smartling_id,
            description: smartling_editor,
            brand: product_translate_brand,
            title: product_translate_title,
        },
        beforeSend: function (xhr) {
            $(".be-wrapper").addClass("be-loading-active");
        },
        success: function (value) {
            $(".be-wrapper").removeClass("be-loading-active");

            
        },
    });
}

function updateLazadaCategory() {
    if(lazada_category_id && lazada_product_id) {
        $(".be-loading").first().addClass("be-loading-active");
        $.ajax({
            url: '/product/set-lazada-category',
            type: 'POST',
            data: {
                lazada_category_id: lazada_category_id,
                lazada_product_id: lazada_product_id
            },
            success: function (response) {
                $(".be-loading").first().removeClass("be-loading-active");
                $('.be-wrapper').removeClass('be-loading-active');
                response = JSON.parse(response);
                if(response.success) {
                    processUpdateProduct();
                } else {
                    $(".be-loading").first().removeClass("be-loading-active");
                    $('.be-wrapper').removeClass('be-loading-active');
                    $('#lazada_category_ajax_error_modal').modal('show');
                    $("#lazada_category_ajax_msg_eror").html(response.message);
                }
            },
            error: function(error) {
                $(".be-loading").first().removeClass("be-loading-active");
                $('.be-wrapper').removeClass('be-loading-active');
                console.log('internal server error');
                console.log(error)
            }
        });
    } else {
        $(".be-loading").first().addClass("be-loading-active");
        processUpdateProduct();
    }
}

function updateProduct() {
    updateLazadaCategory();
}

function processUpdateProduct() {
    var pId = $('#product_id').val();
    //General Tab Fields
    var pName = $('#product_name').text();
    var pSKU = $('#SKU').text();
    var pHTS = $('#HTS').text();
    var pUPC = $('#UPC').text();
    var pEAN = $('#EAN').text();
    var pJAN = $('#JAN').text();
    var pISBN = $('#ISBN').text();
    var pMPN = $('#MPN').text();
    var pDescription = $('#product-update-description').summernote('code');
    var pAdult = $('#adult').text();
    var pAgeGroup = $('#age_group').text();
    var pAvail = $('#availability').text();
    var pBrand = $('#brand').text();
    var pCond = $('#condition').text();
    var pGender = $('#gender').text();
    var pWeight = $('#weight').text();
    var package_length = $('#package_length').text();
    var package_height = $('#package_height').text();
    var package_width = $('#package_width').text();
    var package_box = $('#package_box').text();

    var elli_allocate_str = $('#elli_allocate_inv').text();
    var elli_allocate_inv = elli_allocate_str.replace(/\,/g, "");

    var pcategories = $('#cats_list').text();
    if (pcategories != 'Empty') {
        var pcategories_html = $('#cats_list').html();
        var pcat = pcategories_html.split('<br>');

    }
    var channel_sale = '';
    var temp = $('#channelsonproductview').val();
    var channel_manager_list = $('#channelsonproductview').text();
    if (channel_manager_list != 'Empty') {
        var channel_manager_list = $('#channelsonproductview').html();
        var n = channel_manager_list.indexOf(",");
        if (n != -1) {
            channel_sale = channel_manager_list.split(',');
        } else {
            channel_sale = channel_manager_list.split('<br>');
        }
    }

    /*Starts For connected pricing*/
    var Connected_price = {};
    var Connected_sale_price = {};
    var Connected_schedule_date = {};
    var Connected_stock_qty = {};
    var Connected_stock_level = {};
    var Connected_stock_status = {};
    var Connected_stock_notif = {};
    var Connected_allocate_inv = {};
    var Connected_warranty_type = {};
    var Connected_warranty_period = {};

    $(".price_connect").each(function () {

        var connect_price_str = $(this).text();
        var connect_price = connect_price_str.replace(/\,/g, "");
        var connect_price_channel = $(this).attr('data-connectname');

        Connected_price[connect_price_channel] = connect_price;
    });

    $(".sale_price_connect").each(function () {

        var connect_sale_price_str = $(this).text();
        var connect_sale_price = connect_sale_price_str.replace(/\,/g, "");
        var connect_salePrice_channel = $(this).attr('data-connectname');

        Connected_sale_price[connect_salePrice_channel] = connect_sale_price;
    });

    $(".schedule_date_connect").each(function () {

        var schedule_date_connect = $(this).text();
        var schedule_date_connect_channel = $(this).attr('data-scheduledate');

        Connected_schedule_date[schedule_date_connect_channel] = schedule_date_connect;
    });

    $(".stock_qty_connect").each(function () {

        var stock_qty_connect = $(this).text();
        var stock_qty_connect_channel = $(this).attr('data-stock-qty-connect');

        Connected_stock_qty[stock_qty_connect_channel] = stock_qty_connect.trim();
    });

    $(".stock_level_connect").each(function () {

        var stock_level_connect = $(this).text();
        var stock_level_connect_channel = $(this).attr('data-stock-level-connect');

        Connected_stock_level[stock_level_connect_channel] = stock_level_connect;
    });

    $(".stock_status_connect").each(function () {

        var stock_status_connect = $(this).text();
        var stock_status_connect_channel = $(this).attr('data-stock-status-connect');

        Connected_stock_status[stock_status_connect_channel] = stock_status_connect;
    });

    $(".allocate_inv_connect").each(function () {

        var allocate_inv_connect = $(this).text();
        var allocate_inv_connect_channel = $(this).attr('data-allocate-inv-connect');

        Connected_allocate_inv[allocate_inv_connect_channel] = allocate_inv_connect;
    });
    var warranty_type_empty_check = 'no';
    var warranty_type_channel = '';
    $(".warranty_type_select").each(function () {
        var warranty_type_connect = $(this).val();
        var warranty_type_connect_channel = $(this).attr('data-connectname');
        Connected_warranty_type[warranty_type_connect_channel] = warranty_type_connect;
    });
    var warranty_period_empty_check = 'no';
    var warranty_channel = '';
    $(".warranty_period_select").each(function () {
        var warranty_period_connect = $(this).val();
        var warranty_period_connect_channel = $(this).attr('data-connectname');
        Connected_warranty_period[warranty_period_connect_channel] = warranty_period_connect;
    });

    //Inventory Management Tab Fields
    var pStk_qty_var = $('#sum_stk_qty').text();
    var pStk_qty = pStk_qty_var.trim();
    var pStk_lvl = $('#stk_lvl').text();
    var pStk_status = $('#stk_status').text();
    var plow_stk_ntf = $('#low_stk_ntf').text();

    //Pricing Tab Fields
    var pPrice_str = $('#price').text();
    var pPrice = pPrice_str.replace(/\,/g, "");
    var pSale_price_str = $('#sale_price').text();
    var pSale_price = pSale_price_str.replace(/\,/g, "");
    var pSchedule_date = $('#schedule_date2').html();
    $('.be-wrapper').addClass('be-loading-active');
   // console.log(pPrice+"uu");
   // return false;
    $.ajax({
        type: 'post',
        url: 'update-product?pId=' + pId,
        data: {
            pId: pId,
            pName: pName,
            pSKU: pSKU,
            pHTS: pHTS,
            pUPC: pUPC,
            pEAN: pEAN,
            pJAN: pJAN,
            pISBN: pISBN,
            pMPN: pMPN,
            pDescription: pDescription,
            pAdult: pAdult,
            pAgeGroup: pAgeGroup,
            pAvail: pAvail,
            pBrand: pBrand,
            pCond: pCond,
            pGender: pGender,
            pWeight: pWeight,
            package_length: package_length,
            package_height: package_height,
            package_width: package_width,
            package_box: package_box,

//            pOccasion: pOccas,
//            pWeather: pWthr,
            pcat: pcat,
            channel_sale: channel_sale,
            pStk_qty: pStk_qty,
            pStk_lvl: pStk_lvl,
            pStk_status: pStk_status,
            plow_stk_ntf: plow_stk_ntf,
            pPrice: pPrice,
            pSale_price: pSale_price,
            Connected_price: Connected_price,
            Connected_sale_price: Connected_sale_price,
            pSchedule_date: pSchedule_date,
            Connected_schedule_date: Connected_schedule_date,
            Connected_stock_qty: Connected_stock_qty,
            Connected_stock_level: Connected_stock_level,
            Connected_stock_status: Connected_stock_status,
            Connected_stock_notif: Connected_stock_notif,
            elli_allocate_inv: elli_allocate_inv,
            Connected_allocate_inv: Connected_allocate_inv,
            Connected_warranty_type: Connected_warranty_type,
            Connected_warranty_period: Connected_warranty_period,
        },
//        dataType: "json",

        success: function (value) {
            $('.be-wrapper').removeClass('be-loading-active');
            var response = $.parseJSON(value);
            var status_completed = response.success;
            var message = response.message;
            $("#product_update_ajax_msg_eror").html(message);
            $('.product_update_ajax_request_error').modal('show');
        },
    });
}

$('#product_publish_check').click(function () {
    var checkedProductId = $('input[name=product_check]:checked').map(function () {
        return $(this).val();
    }).get();
    if (checkedProductId.length == 0) {
        $('.product_select_error').modal('show');
    }
    else {
        $('#product-publish-modal-warning').modal('show');
    }
});

$('#product_publish_button').click(function () {

    var checkedChannelId = $('input[name=publish_check]:checked').map(function () {
        return $(this).val();
    }).get();
    if (checkedChannelId.length == 0) {
        $("#product_select_header_error_msg").html("Please select Channel!");
        $('.product_select_error').modal('show');
    }
    else {
        var checkedProductId = $('input[name=product_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $("#product_publish_close").click();
        $('.productTABLE').addClass('be-loading-active');
        $.ajax({
            type: 'POST',
            url: '/product/ajax-product-delete',
            data: {
                productIds: checkedProductId,
                channelIds: checkedChannelId,
            },
        }).done(function (data) {
            $('.productTABLE').removeClass('be-loading-active');
        });
    }
});

$('#product_delete_check').click(function () {
    var checkedProductId = $('input[name=product_check]:checked').map(function () {
        return $(this).val();
    }).get();
    if (checkedProductId.length == 0) {
        $('.product_select_error').modal('show');
    }
    else {
        $('#product-delete-modal-warning').modal('show');
    }
});
/*For product error modal close*/
$('.product_update_error_modal_close').click(function () {
    $('.product_update_ajax_request_error').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
});

function validate(evt) {
    var theEvent = evt || window.event;
    var key = theEvent.keyCode || theEvent.which;
    key = String.fromCharCode( key );
    var regex = /[0-9]|\./;
    if( !regex.test(key) ) {
        theEvent.returnValue = false;
        if(theEvent.preventDefault) theEvent.preventDefault();
    }
}