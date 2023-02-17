if ($('.stripe-button-el').hasClass('stripe-button-el')) {
    $('.stripe-button-el').addClass('btn btn-space btn-primary remove-stripe-button-el');
    $('.remove-stripe-button-el').removeClass('stripe-button-el');
//        $('.remove-stripe-button-el').show();

}

Dropzone.autoDiscover = false;
$(document).ready(function () {

    // var current_url =  window.location.hostname;
    //         alert(current_url);

    //Customer index Datatables NEED To customer life value show descending order


    /*Starts Connected Channel Or Store customer page */
    /*get current action in hidden field*/
    var action = $('#current_action').val();
    




    //NEDD TO CHANGE ACCORDING TO THE DOMAIN Make menus/submenu active based on parent from the URL
    var arr = window.location.href.split('/');
    var arr2 = window.location.href.split('elliot/');
    var page_val = arr[3];
    var page_val2 = arr2[1];

    if ($('.left-sidebar-content .sidebar-elements li a[href="/' + page_val2 + '"]').length <= 0) {
        if (!$('.left-sidebar-content .sidebar-elements li').hasClass("active")) {
            $('.left-sidebar-content .sidebar-elements li a[href="/' + page_val + '"]').parent("li").addClass("active");
        }
    }



    if ($("body").find('.be-color-header').length == 1) {
        if ($("body").find('.be-color-header.be-color-header-success').length == 1) {
            setTimeout(function () {
                $(".be-wrapper.be-fixed-sidebar").removeClass("be-color-header be-color-header-success");
                $(".flash-message.title").removeClass("hide");
                $(".flash-message.title").addClass("show");
                $(".flash-message.msg").removeClass("show");
                $(".flash-message.msg").addClass("hide");
            }, 5000);
        } else if ($("body").find('.be-color-header.be-color-header-danger').length == 1) {
            setTimeout(function () {
                $(".be-wrapper.be-fixed-sidebar").removeClass("be-color-header be-color-header-danger");
                $(".flash-message.title").removeClass("hide");
                $(".flash-message.title").addClass("show");
                $(".flash-message.msg").removeClass("show");
                $(".flash-message.msg").addClass("hide");
            }, 5000);
        } else if ($("body").find('.be-color-header.be-color-header-warning').length == 1) {
            setTimeout(function () {
                $(".be-wrapper.be-fixed-sidebar").removeClass("be-color-header be-color-header-warning");
                $(".flash-message.title").removeClass("hide");
                $(".flash-message.title").addClass("show");
                $(".flash-message.msg").removeClass("show");
                $(".flash-message.msg").addClass("hide");
            }, 5000);
        } else if ($("body").find('.be-color-header.be-color-header-info').length == 1) {
            setTimeout(function () {
                $(".be-wrapper.be-fixed-sidebar").removeClass("be-color-header be-color-header-info");
                $(".flash-message.title").removeClass("hide");
                $(".flash-message.title").addClass("show");
                $(".flash-message.msg").removeClass("show");
                $(".flash-message.msg").addClass("hide");
            }, 5000);
        }
        //Right header Toggle for notifications  
//        $('.notif').click(function () {
//            $('.notif').toggleClass('open');
//        });
//
//        //Right header Toggle for connect Stores 
//        $('.connect').click(function () {
//            $('.connect').toggleClass('open');
//        });
//
//        //Right header Toggle for Account
//        $('.acount-dropdown').click(function () {
//            $('.acount-dropdown').toggleClass('open');
//        });

    }

    //For orders is open connected stores
    $('#see').click(function (e) {

        $('.connect').toggleClass('open');
        e.stopPropagation();

    });
    $('#see-overview').click(function (e) {

        $('.connect').toggleClass('open');
        e.stopPropagation();

    });


});

var defaultIdleTimeOut = 899;
var idleTimeOut = defaultIdleTimeOut;
$(function () {
    $(window).on('load', function () {
        setTimeout(function(){ checkTimeOut(); },1000);
    });
    $("body").on('click',  function () {
        idleTimeOut = defaultIdleTimeOut;
    });
    // $("body").on('change',  function () {
    //     idleTimeOut = defaultIdleTimeOut;
    // });
    $("body").on('keyup',  function () {
        idleTimeOut = defaultIdleTimeOut;
    });
    function checkTimeOut() {
        idleTimeOut--;
        var url = document.URL;
        if(!url.includes("sign-in")) {
            if (idleTimeOut < 1) {
                window.location = '/user/sign-in/logout?idle=' + defaultIdleTimeOut;
            }
            else {
                setTimeout(function () {
                    checkTimeOut();
                }, 1000);
            }
        }
    } ;
    // for the content add new page
    //$("#page_title").editable();
    $("#page_description").summernote();
    $("#page_description_update").summernote();


//Left Sidebar Menus sublinks 
    $('.be-left-sidebar a').each(function (index) {
        if (this.href.trim() == window.location)
            $(this).parent().addClass("active"); //this will add active class to li containing the anchor tag
    });
    /*Single Product Layout Page Editable Fields*/
    //General Tab
    $('#product_name').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    // $('#SKU').editable({
    //     validate: function (value) {
    //         if ($.trim(value) == '') {
    //             return 'This field is required';
    //         }
    //     }
    // });
    $('#HTS').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('#UPC').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('#EAN').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('#JAN').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('#ISBN').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('#MPN').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('#adult').editable({
        source: [
            {value: 'no', text: 'No'},
            {value: 'yes', text: 'Yes'}
        ],
        display: function (value, sourceData) {
            var colors = {"": "gray", no: "green", yes: "red"},
                    elem = $.grep(sourceData, function (o) {
                        return o.value == value;
                    });
            if (elem.length) {
                $(this).text(elem[0].text).css("color", colors[value]);
            } else {
                $(this).empty();
            }
        }
    });

    $('#age_group').editable({
        source: [
            {value: 'Newborn', text: 'Newborn'},
            {value: 'Infant', text: 'Infant'},
            {value: 'Toddler', text: 'Toddler'},
            {value: 'Kids', text: 'Kids'},
            {value: 'Adult', text: 'Adult'}
        ],
    });

    $('#availability').editable({
        source: [
            {value: 'In Stock', text: 'In Stock'},
            {value: 'Out of Stock', text: 'Out of Stock'}
        ],
        display: function (value, sourceData) {
            var colors = {"": "gray", "In Stock": "green", "Out of Stock": "red"},
                    elem = $.grep(sourceData, function (o) {
                        return o.value == value;
                    });
            if (elem.length) {
                $(this).text(elem[0].text).css("color", colors[value]);
            } else {
                $(this).empty();
            }
        }
    });

    $('#condition').editable({
        source: [
            {value: 'New', text: 'New'},
            {value: 'Used', text: 'Used'},
            {value: 'Refurbished', text: 'Refurbished'},
        ],
    });

    $('#gender').editable({
        source: [
            {value: 'Female', text: 'Female'},
            {value: 'Male', text: 'Male'},
            {value: 'Unisex', text: 'Unisex'}
        ],
        display: function (value, sourceData) {
            var colors = {"": "gray", "Female": "pink", "Male": "blue", "Unisex": "gray"},
                    elem = $.grep(sourceData, function (o) {
                        return o.value == value;
                    });
            if (elem.length) {
                $(this).text(elem[0].text).css("color", colors[value]);
            } else {
                $(this).empty();
            }
        }
    });

    $('#weight').editable();
    $('#package_length').editable();
    $('#package_height').editable();
    $('#package_width').editable();
    $('#package_box').editable();
    /*For Inventory IQ*/
    $('.allocate_inventory').editable({
        type: 'text',
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        }

    });

    // $(document).on('click', '.custom-dyn-modal', function (e) {
    //
    //     var dataKeyId = $(this).attr('data-key');
    //     var dataKeyValue = $(this).attr('data-value');
    //     $('#sum_stk_qty').text(dataKeyValue);
    //     $('#custom_item_key').val(dataKeyId);
    //     $('#count_item_value').val(dataKeyValue);
    //     $('.channel_stock').hide();
    //     console.log("dataKeyId = " + dataKeyId);
    //
    //
    // });
    //
    // $(document).on('click', '.custom-org-modal', function (e) {
    //     $('.channel_stock').show();
    //     var org_val = $('#sum_product_abb').val();
    //     $('#sum_stk_qty').text(org_val);
    //     $('#custom_item_key').val('');
    //     $('#count_item_value').val('');
    // });
    //
    //Attribution Tab
    $('#cat1').editable({
        typeahead: {
            name: 'category',
            remote: '/google-shopping/google-product-category?query=%QUERY'
        },
        success: function (response, newValue) {
            // alert(newValue);
            var request = $.ajax({
                url: "/google-shopping/google-product-category",
                type: "GET",
                data: {querymain: newValue},
                dataType: "html"
            });

            request.done(function (msg) {
                // alert(msg);
                var sources = {
                    1: msg
                };
                $('#cat2').editable('option', 'source', sources[1]);
                $('#cat2').editable('setValue', null);
            });

            //remote: '/google-shopping/google-product-category?querymain='+newValue;
            //$('#cat2').editable('option', 'source', sources[newValue]);  
            //$('#cat2').editable('setValue', null);
        }
    });

    $('#cat2').editable({
        typeahead: {
            name: 'category',
            remote: '/google-shopping/google-product-category?query=%QUERY'
        },
        success: function (response, newValue) {
            // alert(newValue);
            $('#gcatname').remove();
            $('#attr_tbl').append('<input type="hidden" value="' + newValue + '" id="gcatname">');
        }

    });

    $('#cat3').editable({
        typeahead: {
            name: 'category',
            remote: '/google-product-category?query=%QUERY'
        }
    });

    $('#occasion').editable({
        source: [
            {value: 'Athletic', text: 'Athletic'},
            {value: 'Baby Shower', text: 'Baby Shower'},
            {value: 'BBQ', text: 'BBQ'},
            {value: 'Beach', text: 'Beach'},
            {value: 'Date Night', text: 'Date Night'},
            {value: 'Interview', text: 'Interview'},
            {value: 'Outdoors', text: 'Outdoors'},
            {value: 'Wedding', text: 'Wedding'},
            {value: 'Work', text: 'Work'}
        ]
    });

    $('#weather').editable({
        source: [
            {value: 'Breezy', text: 'Breezy'},
            {value: 'Cold', text: 'Cold'},
            {value: 'Dry', text: 'Dry'},
            {value: 'Hot', text: 'Hot'},
            {value: 'Humid', text: 'Humid'},
            {value: 'Rain', text: 'Rain'},
            {value: 'Snow', text: 'Snow'}
        ]
    });

    $('#stk_lvl').editable({
        source: [
            {value: 'In Stock', text: 'In Stock'},
            {value: 'Out of Stock', text: 'Out of Stock'}
        ],
        display: function (value, sourceData) {
            var colors = {"": "gray", "In Stock": "green", "Out of Stock": "red"},
                    elem = $.grep(sourceData, function (o) {
                        return o.value == value;
                    });
            if (elem.length) {
                $(this).text(elem[0].text).css("color", colors[value]);
            } else {
                $(this).empty();
            }
        }
    });

    /*For Conected Stock Level*/
    $('.stock_level_connect').editable({
        source: [
            {value: 'In Stock', text: 'In Stock'},
            {value: 'Out of Stock', text: 'Out of Stock'}
        ],
        display: function (value, sourceData) {
            var colors = {"": "gray", "In Stock": "green", "Out of Stock": "red"},
                    elem = $.grep(sourceData, function (o) {
                        return o.value == value;
                    });
            if (elem.length) {
                $(this).text(elem[0].text).css("color", colors[value]);
            } else {
                $(this).empty();
            }
        }
    });
    /*For End Connected Stock Level*/

    $('#stk_status').editable({
        source: [
            {value: 'Visible', text: 'Visible'},
            {value: 'Hidden', text: 'Hidden'}
        ],
        display: function (value, sourceData) {
            var colors = {"": "gray", "Visible": "green", "Hidden": "red"},
                    elem = $.grep(sourceData, function (o) {
                        return o.value == value;
                    });
            if (elem.length) {
                $(this).text(elem[0].text).css("color", colors[value]);
            } else {
                $(this).empty();
            }
        }
    });
    /*For Start Connected Stock status*/
    $('.stock_status_connect').editable({
        source: [
            {value: 'Visible', text: 'Visible'},
            {value: 'Hidden', text: 'Hidden'}
        ],
        display: function (value, sourceData) {
            var colors = {"": "gray", "Visible": "green", "Hidden": "red"},
                    elem = $.grep(sourceData, function (o) {
                        return o.value == value;
                    });
            if (elem.length) {
                $(this).text(elem[0].text).css("color", colors[value]);
            } else {
                $(this).empty();
            }
        }
    });
    /*For eND Connected Stock status*/

    //$('#low_stk_ntf').editable();
    $('#low_stk_ntf').editable({
        type: 'text',
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        }
    });
    $('.connect_low_stk_ntf').editable({
        type: 'text',
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        }
    });
    //$('.connect_low_stk_ntf').editable();

    //Pricing Tab
    // $('#price_currency').editable();

//    $('#price').editable({success: function (response, newValue) {
////        updateAccount(this.id, newValue);
//            $("#price").text(123);
//        }});

    $('#price').editable();
    $('#sale_price').editable();

    /*Starts For connected pricing*/
    $('.price_connect').editable();
    $('.sale_price_connect').editable();

    $('#schedule_date1').editable({
        format: 'dd-mm-yyyy',
        viewformat: 'dd/mm/yyyy',
        combodate: {
            minYear: 2000,
            maxYear: 2020,
            minuteStep: 1
        }
    });


    $('#schedule_date2').editable({
        format: 'dd-mm-yyyy',
        viewformat: 'dd/mm/yyyy',
        combodate: {
            minYear: 2000,
            maxYear: 2020,
            minuteStep: 1
        }
    });

    $('.schedule_date_connect').editable({
        format: 'dd-mm-yyyy',
        viewformat: 'dd/mm/yyyy',
        combodate: {
            minYear: 2000,
            maxYear: 2020,
            minuteStep: 1
        }
    });

    $('#marketplaces').editable({
        source: [
            {value: 'Breezy', text: 'Breezy'},
            {value: 'Cold', text: 'Cold'},
            {value: 'Dry', text: 'Dry'},
            {value: 'Hot', text: 'Hot'},
            {value: 'Humid', text: 'Humid'},
            {value: 'Rain', text: 'Rain'},
            {value: 'Snow', text: 'Snow'},
        ]
    });

    // Starts Sub user page

    $("#first_name_sub").editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });

    $("#last_name_sub").editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });

    $("#email_add_sub").editable({
        // alert(window.location.pathname);
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
            var sub_profile_email_add = value;
            var atpos = sub_profile_email_add.indexOf("@");
            var dotpos = sub_profile_email_add.lastIndexOf(".");
            var current_url = window.location.hostname;
            var parts = current_url.split("." + DOMAIN_NAME.slice(0, -1));
            var current_admin = parts[0];
            var entered_domain = sub_profile_email_add.substr(sub_profile_email_add.indexOf("@") + 1);
            if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= sub_profile_email_add.length)
            {
                return 'Email is Not valid';
            }
            if (entered_domain != current_admin + '.com') {

                return 'Email should be of (' + current_admin + '.com) domain only';
            }




        }
    });

    /* Users Listing DataTable */
    $("#user_table").dataTable({
        pageLength: 50,
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B>>" +
                "<'row be-datatable-body'<'col-sm-12'tr>>" +
                "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    /* END */

    /*Starts Cover image Dropzone*/
    $("#cover_image_dropzone").dropzone({
        url: "save-cover-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });

    /*Starts Cover image Dropzone 1*/
    $("#cover_image_dropzone1").dropzone({
        url: "save-cover-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });
    /*End Cover image Dropzone*/

    /*Starts Cover image Default*/
    $("#cover_image_dropzone_default").dropzone({
        url: "save-cover-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });
    /*End Cover image Default Dropzone*/

    /*Starts Profile image Dropzone*/
    $("#profile_image_dropzone").dropzone({
        url: "/user/profile/save-profile-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }
            });
        }
    });
    /*End Cover image Dropzone*/

    /*Starts Profile image Dropzone1*/
    $("#profile_image_dropzone1").dropzone({
        url: "/user/profile/save-profile-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }
            });
        }
    });
    /*End Profile image Dropzone1 */

    /*Starts product_image_dropzone*/
    $("#product_image_dropzone").dropzone({
        url: "save-product-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });
    /*End product_image_dropzone */

    /*Starts product_image_dropzone 1*/
    $("#product_image_dropzone1").dropzone({
        url: "save-product-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });
    /*End product_image_dropzone1 */

    /*Starts product_image_dropzone-default*/
    $("#product_image_dropzone-default").dropzone({
        url: "save-product-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });
    /*Endproduct_image_dropzone-default*/

    /*Starts product_image_dropzone-default1*/
    $("#product_image_dropzone-default1").dropzone({
        url: "save-product-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });
    /*End product_image_dropzone-default1*/



    $(".google_error_modal_close").click(function () {
        $('.googlemod-success').hide();
    });
    $(".google_modal_close").click(function () {
        $('.googlemod-success').hide();
    });
    /* $('#channelfulfillment').click(function(){
     //console.log($(this).prop("checked"));
     if ($(this).prop("checked") == 'true') {
     //alert($('#fulfillmentlist option[value=""]').html());
     //$('#fulfillmentlist option[value=""]').attr('selected','selected');
     $("#fulfillmentlist").val("1");
     }
     }); */


    //Product Section//

    /*Starts Customer Cover image Dropzone1*/
    $("#cover_image_dropzone1").dropzone({
        url: "save-cover-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });
    /*End Customer Cover image Dropzone1*/

    /*Starts customer-cover image Dropzone*/
    $("#customer-cover_image_dropzone").dropzone({
        url: "save-cover-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });
    /*End customer-cover image Dropzone*/

    /*Starts customer-cover_image_dropzone_default*/
    $("#customer-cover_image_dropzone_default").dropzone({
        url: "save-cover-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }

            });
        }
    });
    /*End customer-cover_image_dropzone_default*/

    /*Starts Profile image Dropzone*/
    $("#customer-profile_image_dropzone").dropzone({
        url: "save-profile-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }
            });
        }
    });
    /*End Cover image Dropzone*/

    /*Starts Profile image Dropzone1*/
    $("#customer-profile_image_dropzone1").dropzone({
        url: "save-profile-image",
        maxFilesize: 2, // MB
        maxFiles: 1,
        acceptedFiles: 'image/*',
        dictDefaultMessage: "+",
        dictInvalidFileType: "Please upload Image files.",
        dictMaxFilesExceeded: "You can not upload any more files.",
        init: function () {
            this.on("success", function (file, responseText) {
                var responsetext = JSON.parse(file.xhr.responseText);
                if (responsetext.status == 'success') {
                    window.location = "profile";
                }
            });
        }
    });
    /*End Profile image Dropzone1 */

    //Summernote products-text-decription
    $('#product-update-description').summernote();

    //Summernote products-text-decription
    $('#product-create-description').summernote();



//console.log($(".runwechatimport"))
//function runwechatimport(user_id){
    if ($('.runwechatimport').length > 0) {
        user_id = $(".runwechatimport").attr("data-id");
//alert("its working fine"+user_id); return false;
        $.ajax({
            type: 'post',
            url: '/channels/wechatimportonlogin',
            data: {
                user_id: user_id,
                task: 'import',
                task_source: 'WeChat'
            },
            beforeSend: function (xhr) {
//                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                if (value != "error") {
                    $.ajax({
                        type: 'get',
                        url: 'channels/wechatimport/' + user_id,
                        data: {
                            type: value,
                            process: 'backend',
                        },
                        beforeSend: function (xhr) {
                        },
                        success: function (value) {
                        },
                    });
                }

            },
        });
    }


    $(".wechat_sbmt1_already").click(function () {

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
            url: '/channels/wechatconnect',
            data: {
                user: storename,
                password: password,
                type: type,
                already: 'yes',
                email: '',
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
                if (value == 'error') {
                    $('#walkthechat_request_error').modal('show');
                }
                //else if (value) {
                else {
                    $('#walkthechat_request').modal('show');
                    $(".afterwechatrequestmsg_already").show();
                    $(".wechatrequestform").hide();
                    console.log("test");
                    // $('#walkthechat_request').modal('show');
                    // $(".afterwechatrequestmsg").show();
                    // $(".wechatrequestform").hide();
                }

//           location.reload();
            },
        });
    })


    $(".sfexpress_sbmt1_already").click(function () {
        username = $.trim($('#SfExpress_channel_email').val());
        password = $.trim($('#SfExpress_channel_password').val());
        if (!username || !password) {
            $("#SfExpress_channel_email").addClass("inpt_required");
            $("#SfExpress_channel_password").addClass("inpt_required");
            return false;
        } else {
            $("#SfExpress_channel_email").removeClass("inpt_required");
            $("#SfExpress_channel_password").removeClass("inpt_required");
        }
        $.ajax({
            type: 'post',
            url: '/sfexpress/save/',
            data: {
                username: username,
                password: password,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
                if (value == 'error') {
                    $('#SfExpress_ajax_request_error').modal('show');
                } else if (value) {

                    $("#SfExpress_ajax_request").modal('show');

                }

//           location.reload();
            },
        });
    });

    $(".sfexpress_disconnect_already").click(function () {

        $.ajax({
            type: 'post',
            url: '/sfexpress/deletesf/',
            data: {
                keyid: 'SF Express'
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (value) {
                $(".be-wrapper").removeClass("be-loading-active");
                location.reload();
                if (value == 'error') {
                    $('#SfExpress_ajax_request_error').modal('show');
                } else if (value) {
                    location.reload();
                    $("#SfExpress_ajax_request").modal('show');
                    window.location.href = '/sfexpress'

                }

//           location.reload();
            },
        });
    });

    $(".Google_sbmt1").click(function () {
        clientID = $.trim($("#Google_clientid").val());
        secretKey = $.trim($("#Google_secretkey").val());
        merchantID = $.trim($("#Google_merchantid").val());
        if (!clientID || !secretKey || !merchantID) {
            $("#Google_clientid").addClass("inpt_required");
            $("#Google_secretkey").addClass("inpt_required");
            $("#Google_merchantid").addClass("inpt_required");
            return false;
        } else {
            $("#Google_clientid").removeClass("inpt_required");
            $("#Google_secretkey").removeClass("inpt_required");
            $("#Google_merchantid").removeClass("inpt_required");
        }

        $.ajax({
            type: 'post',
            url: '/google-shopping/save/',
            data: {
                clientID: clientID,
                secretKey: secretKey,
                merchantID: merchantID,
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
                    $(".Googlerequestform").hide();
                }

//           location.reload();
            },
        });
    })

});


function lazadaCatError(db_id, channel) {
    db_lazada_category='';
//    var db_lazada_category = $('#' + db_id).val();
//    if (db_lazada_category == '' || db_lazada_category == null || (typeof (db_lazada_category) == "undefined" || db_lazada_category==false)) {
//        db_lazada_category='';
//        $("#product_update_ajax_msg_eror").html('Please select any ' + channel + ' category');
//        $("#product_update_header_error_msg").html('Error!');
//        $('.product_update_ajax_request_error').css({
//            'display': 'block',
//            'background': 'rgba(0,0,0,0.6)',
//        });
//    } else {
//        $('.product_update_ajax_request_error').css({
//            'display': 'none',
//            'background': 'rgba(0,0,0,0.6)',
//        });
//    }
    return db_lazada_category;
}

//save sub user profile

function savesubprofile() {

    var sub_firstname = $("#first_name_sub").text();
    var sub_lastname = $("#last_name_sub").text();
    var sub_email = $("#email_add_sub").text();
    //var sub_role = $("#role_sub").attr('data-value');
    var sub_role = $("#role_sub").text();
    var sub_user_id = $("#sub_profile_id").val();
    console.log("sub_role: " + sub_role + " sub_user_id: " + sub_user_id);

    jQuery.ajax({
        type: 'post',
        url: '/user/savesubprofile',
        data: {
            sub_firstname: sub_firstname,
            sub_lastname: sub_lastname,
            sub_email: sub_email,
            sub_role: sub_role,
            sub_user_id: sub_user_id
        },
        dataType: "json",
        success: function (value) {
            if (value.status == 'success') {
                console.log(value);
            } else {

            }
        },
    })
}


//Save Customer Profile Information
function savecustomer() {

    var customer_first_name = $('#customer_first_name').text();
    var customer_last_name = $('#customer_last_name').text();
    var customer_email_add = $('#customer_email_add').text();
    var customer_dob = $('#customer_dob').text();
    var gender = $('#customer_gender').text();
    var customer_Phone_no = $('#customer_Phone_no').text();
    var corporate_street1 = $('#customer_corporate_street1').text();
    var corporate_street2 = $('#customer_corporate_street2').text();
    var corporate_city = $('#customer_corporate_city').text();
    var corporate_state = $('#customer_corporate_state').text();
    var corporate_zip = $('#customer_corporate_zip').text();
    var corporate_country = $('#customer_corporate_country').text();
    var ship_street1 = $('#customer_ship_street1').text();
    var ship_street2 = $('#customer_ship_street2').text();
    var ship_city = $('#customer_ship_city').text();
    var ship_state = $('#customer_ship_state').text();
    var ship_zip = $('#customer_ship_zip').text();
    var ship_country = $('#customer_ship_country').text();
    var customer_id = document.getElementById("customer_id").value;

    var bill_street1 = {};
    var bill_street2 = {};
    var bill_city = {};
    var bill_state = {};
    var bill_zip = {};
    var bill_country = {};

    var ship_street1 = {};
    var ship_street2 = {};
    var ship_city = {};
    var ship_state = {};
    var ship_zip = {};
    var ship_country = {};
    /*For Billing Address*/
    $(".cus_bill_street1").each(function () {

        var cus_bill_street1 = $(this).text();
        var connect_cus_bill_street1 = $(this).attr('data-connectname');

        bill_street1[connect_cus_bill_street1] = cus_bill_street1;
    });

    $(".cus_bill_street2").each(function () {

        var cus_bill_street2 = $(this).text();
        var connect_cus_bill_street2 = $(this).attr('data-connectname');

        bill_street2[connect_cus_bill_street2] = cus_bill_street2;
    });

    $(".cus_bill_city").each(function () {

        var cus_bill_city = $(this).text();
        var connect_cus_bill_city = $(this).attr('data-connectname');

        bill_city[connect_cus_bill_city] = cus_bill_city;
    });

    $(".cus_bill_state").each(function () {

        var cus_bill_state = $(this).text();
        var connect_cus_bill_state = $(this).attr('data-connectname');

        bill_state[connect_cus_bill_state] = cus_bill_state;
    });

    $(".cus_bill_zip").each(function () {

        var cus_bill_zip = $(this).text();
        var connect_cus_bill_zip = $(this).attr('data-connectname');

        bill_zip[connect_cus_bill_zip] = cus_bill_zip;
    });

    $(".cus_bill_country").each(function () {

        var cus_bill_country = $(this).text();
        var connect_cus_bill_country = $(this).attr('data-connectname');

        bill_country[connect_cus_bill_country] = cus_bill_country;
    });

    /*For Shipping Address*/

    $(".cus_ship_street1").each(function () {

        var cus_ship_street1 = $(this).text();
        var connect_cus_ship_street1 = $(this).attr('data-connectname');

        ship_street1[connect_cus_ship_street1] = cus_ship_street1;
    });

    $(".cus_ship_street2").each(function () {

        var cus_ship_street2 = $(this).text();
        var connect_cus_ship_street2 = $(this).attr('data-connectname');

        ship_street2[connect_cus_ship_street2] = cus_ship_street2;
    });

    $(".cus_ship_city").each(function () {

        var cus_ship_city = $(this).text();
        var connect_cus_ship_city = $(this).attr('data-connectname');

        ship_city[connect_cus_ship_city] = cus_ship_city;
    });

    $(".cus_ship_state").each(function () {

        var cus_ship_state = $(this).text();
        var connect_cus_ship_state = $(this).attr('data-connectname');

        ship_state[connect_cus_ship_state] = cus_ship_state;
    });

    $(".cus_ship_zip").each(function () {

        var cus_ship_zip = $(this).text();
        var connect_cus_ship_zip = $(this).attr('data-connectname');

        ship_zip[connect_cus_ship_zip] = cus_ship_zip;
    });

    $(".cus_ship_country").each(function () {

        var cus_ship_country = $(this).text();
        var connect_cus_ship_country = $(this).attr('data-connectname');

        ship_country[connect_cus_ship_country] = cus_ship_country;
    });




    $('.be-wrapper').addClass('be-loading-active');

    jQuery.ajax({
        type: 'post',
        url: 'savecustomer',
        data: {
            customer_first_name: customer_first_name,
            customer_last_name: customer_last_name,
            customer_email_add: customer_email_add,
            customer_dob: customer_dob,
            gender: gender,
            customer_Phone_no: customer_Phone_no,
//            corporate_street1: corporate_street1,
//            corporate_street2: corporate_street2,
//            corporate_city: corporate_city,
//            corporate_state: corporate_state,
//            corporate_zip: corporate_zip,
//            corporate_country: corporate_country,
//            ship_street1: ship_street1,
//            ship_street2: ship_street2,
//            ship_city: ship_city,
//            ship_state: ship_state,
//            ship_zip: ship_zip,
//            ship_country: ship_country,
            customer_id: customer_id,

            bill_street1: bill_street1,
            bill_street2: bill_street2,
            bill_city: bill_city,
            bill_state: bill_state,
            bill_zip: bill_zip,
            bill_country: bill_country,

            ship_street1: ship_street1,
            ship_street2: ship_street2,
            ship_city: ship_city,
            ship_state: ship_state,
            ship_zip: ship_zip,
            ship_country: ship_country,
        },
        dataType: "json",
        success: function (value) {
            if (value.status == 'success') {
                window.location = "view?id=" + customer_id;
            } else {

            }
        },
    });
}



/*Start For Corporate Documents form*/


$('#TaxID').editable();

$("#corporate_form_id").on('submit', (function (e) {
    e.preventDefault();
    $('.be-wrapper').addClass('be-loading-active');
    var tax_id_value = $('#TaxID').text();
    $('#tax_val').val(tax_id_value);
    $.ajax({
        url: "/upload-documents",
        type: "POST",
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
            var obj_data = JSON.parse(data);
            if (obj_data['error_msg'] != undefined) {
                var html_data_error = obj_data['error_msg'];
                $("#corporate_ajax_msg_eror").html(html_data_error);
                $("#ajax_header_error_msg").html('Error!');
                $('.corporate_ajax_request_error').css({
                    'display': 'block',
                    'background': 'rgba(0,0,0,0.6)',
                });
            }
            $('.be-wrapper').removeClass('be-loading-active');
            if (obj_data["success_msg"] != undefined) {
                var html_data = obj_data['success_msg'];
                $("#corporate_ajax_msg").html(html_data);
                $("#ajax_header_msg").html('Success!');
                $('#corporate_ajax_request').css({
                    'display': 'block',
                    'background': 'rgba(0,0,0,0.6)',
                });
            }
        },
        error: function () {
        }
    });
}));

$('.corporate_error_modal_close').click(function () {
    $('.corporate_ajax_request_error').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
});
$('.corporate_modal_close').click(function () {
    $('#corporate_ajax_request').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
    window.location.reload()
});

/*End For Corporate Documents form*/

//app ui sticky notifiactions starts here
function stickynotification(remainingdays) {
    if (remainingdays == '7' || remainingdays == '6') {
        $.extend($.gritter.options, {position: 'top-right'});
        $.gritter.add({
            title: 'Success',
            text: remainingdays + " days remains in your trial period.",
            image: '../img/elliot-logo-small.svg',
            class_name: 'clean color success',
            time: '10000'
        });
        return false;
    } else if (remainingdays == '5' || remainingdays == '4' || remainingdays == '3') {
        $.extend($.gritter.options, {position: 'top-right'});
        $.gritter.add({
            title: 'Warning',
            text: remainingdays + " days remains in your trial period.",
            image: '../img/elliot-logo-small.svg',
            class_name: 'clean color warning',
            time: '10000'
        });
        return false;
    } else if (remainingdays == '2' || remainingdays == '1') {
        $.extend($.gritter.options, {position: 'top-right'});
        $.gritter.add({
            title: 'Danger',
            text: remainingdays + " day(s) remains in your trial period.",
            image: '../img/elliot-logo-small.svg',
            class_name: 'clean color danger',
            time: '10000'
        });
        return false;
    }

}
//app ui Success sticky notifiactions starts here
function SuccessStickyNotification(message) {
    $.extend($.gritter.options, {position: 'top-right'});
    $.gritter.add({
        title: 'Success',
        text: message,
        image: '../img/elliot-logo-small.svg',
        class_name: 'clean color success',
        time: '10000'
    });
    return false;
}

//app ui Danger sticky notifiactions starts here
function DangerStickyNotification(message) {
    $.extend($.gritter.options, {position: 'top-right'});
    $.gritter.add({
        title: 'Danger',
        text: message,
        image: '../img/elliot-logo-small.svg',
        class_name: 'clean color danger',
        time: '10000'
    });
    return false;
}

//app ui sticky notifiactions starts here
function sticky_ntf_connection_import(import_done, import_channel) {
    if (import_done == 'true') {
        $.extend($.gritter.options, {position: 'top-right'});
        $.gritter.add({
            title: 'Success',
            text: "Your " + import_channel + " data has been successfully imported.",
            image: '../img/elliot-logo-small.svg',
            class_name: 'clean color success',
            time: '10000'
        });
        return false;
    } else if (import_done == 'false') {
        $.extend($.gritter.options, {position: 'top-right'});
        $.gritter.add({
            title: 'Danger',
            text: "Your " + import_channel + " data import has been failed.",
            image: '../img/elliot-logo-small.svg',
            class_name: 'clean color danger',
            time: '10000'
        });
        return false;
    }
}






//function see_store_data() {
// 
//  $(".connect").addClass("open");
//}

// for NestedUi categories

var App = (function () {
    'use strict';

    App.uiNestableLists = function ( ) {

        $('.dd').nestable();
        //Watch for list changes and show serialized output
        function update_out(selector, sel2) {
            var out = $(selector).nestable('serialize');
            $(sel2).html(window.JSON.stringify(out));

        }
        update_out('#list2', "#out2");
        $('#list2').on('change', function () {
            update_out('#list2', "#out2");
            var val = $("#out2").text();
            console.log(val);
            jQuery.ajax({
                type: 'post',
                url: 'categories/update-nested-cat',
                data: {data: val, },
                dataType: "json",
                success: function (value) {

                },
            });

        });

    };
    App.masks = function ( ) {
        $("#last_4_social").mask("9999");

    };
    return App;
})(App || {});

