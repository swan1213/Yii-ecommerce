
$(document).ready(function () {

    $('.proceedToDocuments').on('click', function () {
        console.log('here');
        $this = $(this);
        window.location.href = '/documents';
    });
    $('.documents_fields').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        }
    });
    $('.documents_fields_account_type').editable({
        value: '2',
        source: [
            {value: 'checking', text: 'Checking'},
            {value: 'savings', text: 'Savings'}
        ],
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        },
        success: function (k, val) {
            $('#account_type').attr('data-select-val', val);
        }
    });
    /*
    $('.documents_fields_dob').editable({
        format: 'dd-mm-yyyy',
        viewformat: 'dd/mm/yyyy',
        combodate: {
            minYear: 1950,
            maxYear: 2017,
            minuteStep: 1
        }
    });*/
    $('.payment_account_country').editable({
        typeahead: {
            name: 'country',
            remote: 'get-countries?query=%QUERY',
        },
    });

    //make general setting general_corporate_state editable
    $('.payment_account_state').editable({
        typeahead: {
            name: 'state',
            remote: 'get-states?query=%QUERY',
        }
    });

    //make general setting general_corporate_city editable
    $('.payment_account_city').editable({
        typeahead: {
            name: 'city',
            remote: 'get-cities?query=%QUERY',
        }
    });


    $i = 0;
    $('.addMoreDirectors').on('click', function () {

        $i++;
        $this = $(this);
        director_count = parseInt($i) + parseInt(1);
        addMoreDirectors(director_count);
    });
    function addMoreDirectors(director_count) {


        $('#directors').children(':first').append('<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title"><a data-toggle="collapse" class="collapsed" data-parent="#accordionChannelS" href="#director_' + director_count + '"><i class="icon mdi mdi-chevron-down"></i>Director ' + director_count + '</a></h4></div><div id="director_' + director_count + '" class="panel-collapse collapse"><div class="panel-body"><label> Director </label> <input type="hidden" class="hidden_directors_file" id="hidden_directors_file_id_' + director_count + '" data-index="' + director_count + '"> <input type="hidden" class="hidden_user_id" id="" value="0"> <table id="cats_tbl" style="clear: both" class="table table-striped table-borderless"> <tbody> <tr> <td width="35%"> First name</td> <td width="65%"><a id="" class="documents_fields first_name" href="#" data-type="text" data-title="Please Enter value"></a></td> </tr> <tr> <td width="35%"> Last name</td> <td width="65%"><a id="last_name"class="documents_fields last_name" href="#" data-type="text" data-title="Please Enter value"></a></td> </tr> <tr> <td width="35%"> Date of Birth</td> <td width="65%"><a class="documents_fields_dob dob" href="#" data-pk="1" data-template="D / MMM / YYYY" data-viewformat="DD/MM/YYYY" data-format="YYYY-MM-DD"data-type="combodate" data-title="Please Enter value"></a></td> </tr> <tr> <td width="35%"> Address</td> <td width="65%"><a id="" class="documents_fields address" href="#" data-type="text" data-title="Please Enter value"></a></td> </tr> <tr> <td width="35%"> Last 4 of Social</td> <td width="65%"> <input type="text" data-mask="last_4_social" id="last_4_social" placeholder="9999" class="form-control last_4_social"></td> </tr> </tbody> </table> <div class="main-content container-fluid"> <form action="" class="directorsform' + director_count + ' dropzone"> <div class="dz-message"> <div class="icon"><span class="mdi mdi-cloud-upload"></span></div> <h2>Drag and Drop files here</h2><span class="note">(This is just a demo dropzone. Selected files are <strong>not</strong> actually uploaded.)</span> </div> </form> </div></div></div></div>');
        $('.documents_fields').editable({
            validate: function (value) {
                if ($.trim(value) == '') {
                    return 'This field is required';
                }
            }
        });
        $('.documents_fields_dob').editable({
            format: 'dd-mm-yyyy',
            viewformat: 'dd/mm/yyyy',
            combodate: {
                minYear: 1950,
                maxYear: 2017,
                minuteStep: 1
            }
        });
        Dropzone.autoDiscover = false;
        var myDropzone3 = new Dropzone(".directorsform" + director_count,
            {
                url: "/documents/upload-documents?type=directors",
                init: function () {
                    this.on("success", function (file, responseText) {
                        console.log(responseText);
                        data = $.parseJSON(responseText);
                        console.log(data);
                        if (data.id) {

                            console.log($('#hidden_directors_file_id_' + director_count));
                            $('#hidden_directors_file_id_' + director_count).val(data.id);
                            $('.directorsform' + director_count).css('pointer-events', 'none');
                        }
                    });
                }
            });
    }

    $director_saved_count = $('#countDirector').val();
    $('.updateMoreDirectors').on('click', function () {
        $director_saved_count++;
        director_count = parseInt($director_saved_count) + parseInt(1);

        $this = $(this);
        addMoreDirectors(director_count);
    });



    $bankingform = $('#bankingform');
    $businessform = $('#businessform');
    $directorsform = $('.directorsform');
    if ($.contains(document, $bankingform[0])) {

        var myDropzone = new Dropzone("#bankingform", {
            maxFiles: 1,
            maxfilesexceeded: function (file) {
                // this.removeFile(file);
                //this.addFile(file);
            },
            url: "/documents/upload-documents?type=banking"
        });
        myDropzone.on("complete", function (file) {

            $('#bankingform').css('pointer-events', 'none');
        });
    }
    if ($.contains(document, $businessform[0])) {

        var myDropzone1 = new Dropzone("#businessform", {url: "/documents/upload-documents?type=business"});
        myDropzone1.on("complete", function (file) {
            console.log('done');
            $('#businessform').css('pointer-events', 'none');
        });
    }
    if ($.contains(document, $directorsform[0])) {

        var myDropzone2 = new Dropzone(".directorsform",
            {
                url: "/documents/upload-documents?type=directors",
                init: function () {
                    this.on("success", function (file, responseText) {
                        data = $.parseJSON(responseText);

                        if (data.id) {
                            var id = $('#hidden_directors_file_id_0').val(data.id);
                            $('.directorsform').css('pointer-events', 'none');

                        }
                    });
                }
            });

    }
});

function addUpdateDocuments() {
    var account_no = $('#account_no').text();
    var routing_no = $('#routing_no').text();
    var bank_code = $('#bank_code').text();
    var bank_name = $('#bank_name').text();
    var bank_address = $('#bank_address').text();
    var swift = $('#swift').text();
//    var account_type = $('#account_type').text();
    var account_type = $('#account_type').attr('data-select-val');
    var tax_id = $('#tax_id').text();
    var alipay_payment_account_no = $('#alipay_payment_account_no').text();
    var alipay_payment_account_id = $('#alipay_payment_account_id').text();
    var alipay_payment_account_email = $('#alipay_payment_account_email').text();
    var alipay_payment_address_1 = $('#alipay_payment_address_1').text();
    var alipay_payment_address_2 = $('#alipay_payment_address_2').text();
    var alipay_payment_account_city = $('#alipay_payment_account_city').text();
    var alipay_payment_account_state = $('#alipay_payment_account_state').text();
    var alipay_payment_account_country = $('#alipay_payment_account_country').text();
    var alipay_payment_account_zip_code = $('#alipay_payment_account_zip_code').text();

    var dinpay_payment_account_no = $('#dinpay_payment_account_no').text();
    var dinpay_payment_account_id = $('#dinpay_payment_account_id').text();
    var dinpay_payment_account_email = $('#dinpay_payment_account_email').text();
    var dinpay_payment_address_1 = $('#dinpay_payment_address_1').text();
    var dinpay_payment_address_2 = $('#dinpay_payment_address_2').text();
    var dinpay_payment_account_city = $('#dinpay_payment_account_city').text();
    var dinpay_payment_account_state = $('#dinpay_payment_account_state').text();
    var dinpay_payment_account_country = $('#dinpay_payment_account_country').text();
    var dinpay_payment_account_zip_code = $('#dinpay_payment_account_zip_code').text();

    var payoneer_payment_account_no = $('#payoneer_payment_account_no').text();
    var payoneer_payment_account_id = $('#payoneer_payment_account_id').text();
    var payoneer_payment_account_email = $('#payoneer_payment_account_email').text();
    var payoneer_payment_address_1 = $('#payoneer_payment_address_1').text();
    var payoneer_payment_address_2 = $('#payoneer_payment_address_2').text();
    var payoneer_payment_account_city = $('#payoneer_payment_account_city').text();
    var payoneer_payment_account_state = $('#payoneer_payment_account_state').text();
    var payoneer_payment_account_country = $('#payoneer_payment_account_country').text();
    var payoneer_payment_account_zip_code = $('#payoneer_payment_account_zip_code').text();

    var worldfirst_payment_account_no = $('#worldfirst_payment_account_no').text();
    var worldfirst_payment_account_id = $('#worldfirst_payment_account_id').text();
    var worldfirst_payment_account_email = $('#worldfirst_payment_account_email').text();
    var worldfirst_payment_address_1 = $('#worldfirst_payment_address_1').text();
    var worldfirst_payment_address_2 = $('#worldfirst_payment_address_2').text();
    var worldfirst_payment_account_city = $('#worldfirst_payment_account_city').text();
    var worldfirst_payment_account_state = $('#worldfirst_payment_account_state').text();
    var worldfirst_payment_account_country = $('#worldfirst_payment_account_country').text();
    var worldfirst_payment_account_zip_code = $('#worldfirst_payment_account_zip_code').text();



    /*Starts For connected pricing*/
    var first_names = [];
    var last_names = [];
    var dobs = [];
    var addresses = [];
    var last_4_socials = [];
    var hidden_user_ids = [];
    $(".first_name").each(function () {

        var first_name = $(this).text();

        first_names.push(first_name);
    });
    $(".last_name").each(function () {

        var last_name = $(this).text();

        last_names.push(last_name);
    });
    $(".dob").each(function () {

        var dob = $(this).text();

        dobs.push(dob);
    });
    $(".address").each(function () {

        var address = $(this).text();

        addresses.push(address);
    });
    $(".last_4_social").each(function () {

        var last_4_social = $(this).val();

        last_4_socials.push(last_4_social);
    });
    $(".hidden_user_id").each(function () {

        var userid = $(this).val();
        if (userid == '') {
            userid = "0";
        }

        hidden_user_ids.push(userid);
    });
    $j = 0;
    var hidden_directors_file_values = {};
    $(".hidden_directors_file").each(function () {

        var val = $(this).val();
        hidden_directors_file_values[$(this).attr('data-index')] = val;
    });


    $.ajax({
        type: 'post',
        url: '/documents/add-update-document',
        data: {
            account_no: account_no,
            routing_no: routing_no,
            bank_code: bank_code,
            bank_name: bank_name,
            bank_address: bank_address,
            swift: swift,
            account_type: account_type,
            tax_id: tax_id,
            alipay_payment_account_no: alipay_payment_account_no,
            alipay_payment_account_id: alipay_payment_account_id,
            alipay_payment_account_email: alipay_payment_account_email,
            alipay_payment_address_1: alipay_payment_address_1,
            alipay_payment_address_2: alipay_payment_address_2,
            alipay_payment_account_city: alipay_payment_account_city,
            alipay_payment_account_state: alipay_payment_account_state,
            alipay_payment_account_country: alipay_payment_account_country,
            alipay_payment_account_zip_code: alipay_payment_account_zip_code,

            dinpay_payment_account_no: dinpay_payment_account_no,
            dinpay_payment_account_id: dinpay_payment_account_id,
            dinpay_payment_account_email: dinpay_payment_account_email,
            dinpay_payment_address_1: dinpay_payment_address_1,
            dinpay_payment_address_2: dinpay_payment_address_2,
            dinpay_payment_account_city: dinpay_payment_account_city,
            dinpay_payment_account_state: dinpay_payment_account_state,
            dinpay_payment_account_country: dinpay_payment_account_country,
            dinpay_payment_account_zip_code: dinpay_payment_account_zip_code,

            payoneer_payment_account_no: payoneer_payment_account_no,
            payoneer_payment_account_id: payoneer_payment_account_id,
            payoneer_payment_account_email: payoneer_payment_account_email,
            payoneer_payment_address_1: payoneer_payment_address_1,
            payoneer_payment_address_2: payoneer_payment_address_2,
            payoneer_payment_account_city: payoneer_payment_account_city,
            payoneer_payment_account_state: payoneer_payment_account_state,
            payoneer_payment_account_country: payoneer_payment_account_country,
            payoneer_payment_account_zip_code: payoneer_payment_account_zip_code,

            worldfirst_payment_account_no: worldfirst_payment_account_no,
            worldfirst_payment_account_id: worldfirst_payment_account_id,
            worldfirst_payment_account_email: worldfirst_payment_account_email,
            worldfirst_payment_address_1: worldfirst_payment_address_1,
            worldfirst_payment_address_2: worldfirst_payment_address_2,
            worldfirst_payment_account_city: worldfirst_payment_account_city,
            worldfirst_payment_account_state: worldfirst_payment_account_state,
            worldfirst_payment_account_country: worldfirst_payment_account_country,
            worldfirst_payment_account_zip_code: worldfirst_payment_account_zip_code,
            directors: {first_names: first_names, last_names: last_names, dobs: dobs, addresses: addresses,
                last_4_socials: last_4_socials, hidden_ids: hidden_user_ids},
            hidden_directors_file_values: hidden_directors_file_values
        },
//        dataType: "json",

        success: function (value) {
            $('#documentssaved').modal('show');
            $('.be-wrapper').removeClass('be-loading-active');

        },
    });
}

$(".delete_files").click(function () {
    var imgpath = $(this).attr('data-imgattr');
    var bank_id = $(this).attr('data-bank_doc_id');
    var type = $(this).attr('data-directory-type');

    $('#modal_corporate_image_path').val(imgpath);
    $('#modal_corporate_bank_id').val(bank_id);
    $('#modal_corporate_type').val(type);

    $('#corporate-delete-modal-warning').modal('show');



});

$("#corporate_proceed_button").click(function () {

    var imgpath = $('#modal_corporate_image_path').val();
    var bank_id = $('#modal_corporate_bank_id').val();
    var type = $('#modal_corporate_type').val();
    $.ajax({
        type: 'post',
        url: '/documents/delete-banking-documents',
        data: {
            imgpath: imgpath,
            bank_id: bank_id,
            type: type,
        },
        success: function (value) {
            location.reload();
        },
    });

});
//
///*Delete Corporate Banking Files*/
//$(".delete_banking_files").click(function(){
//   var imgpath=$(this).attr('data-imgattr');
//   var bank_id=$(this).attr('data-bank_doc_id');
//   var type='banking';
//   $.ajax({
//            type: 'post',
//            url: '/documents/delete-banking-documents',
//            data: {
//                imgpath: imgpath,
//                bank_id: bank_id,
//                type: type,
//            },
//            success: function (value) {
//               location.reload();
//            },
//        });
//
//});

/*Delete Corporate Banking Files*/
//$(".delete_banking_files").click(function(){
//   var imgpath=$(this).attr('data-imgattr');
//   var bank_id=$(this).attr('data-bank_doc_id');
//   var type='banking';
//   $.ajax({
//            type: 'post',
//            url: '/documents/delete-banking-documents',
//            data: {
//                imgpath: imgpath,
//                bank_id: bank_id,
//                type: type,
//            },
//            success: function (value) {
//               location.reload();
//            },
//        });
//
//});

/*Delete Corporate Buisness Files*/
//$(".delete_buisness_files").click(function(){
//   var imgpath=$(this).attr('data-imgattr');
//   var bank_id=$(this).attr('data-bank_doc_id');
//   var type='business';
//   $.ajax({
//            type: 'post',
//            url: '/documents/delete-banking-documents',
//            data: {
//                imgpath: imgpath,
//                bank_id: bank_id,
//                type: type,
//            },
//            success: function (value) {
//               location.reload();
//            },
//        });
//
//});



/*Delete Corporate Buisness Files*/
//$(".delete_director_files").click(function(){
//
//   $('#corporate-delete-modal-warning').modal('show');
//
//   var imgpath=$(this).attr('data-imgattr');
//   var bank_id=$(this).attr('data-bank_doc_id');
//   var type='directors';
//   $.ajax({
//            type: 'post',
//            url: '/documents/delete-banking-documents',
//            data: {
//                imgpath: imgpath,
//                bank_id: bank_id,
//                type: type,
//            },
//            success: function (value) {
//               location.reload();
//            },
//        });
//
//});

$(".close_corporate").click(function () {
    location.reload();

});