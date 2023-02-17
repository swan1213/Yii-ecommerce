
/*Starts General settings page */

//make AccountOwner editable
$('#AccountOwner').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
        var AccountOwner = value;
        var atpos = AccountOwner.indexOf("@");
        var dotpos = AccountOwner.lastIndexOf(".");
        if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= AccountOwner.length)
        {
            return 'Email is Not valid';
        }
    }
});

//make general_corporate_street1 editable
$('#general_corporate_street1').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make general_corporate_street2 editable
$('#general_corporate_street2').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});
$('#general_phone_number').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});
$('#general_company').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make general setting general_corporate_country editable
$('#general_corporate_country').editable({
    typeahead: {
        name: 'country',
        remote: 'get-countries?query=%QUERY',
    },
});

//make general setting general_corporate_state editable
$('#general_corporate_state').editable({
    typeahead: {
        name: 'state',
        remote: 'get-states?query=%QUERY',
    }
});

//make general setting general_corporate_city editable
$('#general_corporate_city').editable({
    typeahead: {
        name: 'city',
        remote: 'get-cities?query=%QUERY',
    }
});

//make eneral setting general_corporate_Zipcode editable
$('#general_corporate_zip').editable({
    value: '143001',
    typeahead: {
        name: 'zip code',
        local: ["143001", "0001", "14250", "123", "5725", "65565", "000", "5454", "787887", "253354", "788787", "545454"]
    }
});

//make subscription_billing_street1 editable
$('#subscription_billing_street1').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make subscription_billing_street2 editable
$('#subscription_billing_street2').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make general setting subscription_billing_country editable
$('#subscription_billing_country').editable({
    typeahead: {
        name: 'country',
        remote: 'get-countries?query=%QUERY',
    },
});

//make general setting subscription_billing_state editable
$('#subscription_billing_state').editable({
    typeahead: {
        name: 'state',
        remote: 'get-states?query=%QUERY',
    }
});

//make general setting subscription_billing_city editable
$('#subscription_billing_city').editable({
    typeahead: {
        name: 'city',
        remote: 'get-cities?query=%QUERY',
    }
});

//make eneral setting subscription_billing_zipcode editable
$('#subscription_billing_zip').editable({
    value: '143001',
    typeahead: {
        name: 'zip code',
        local: ["143001", "0001", "14250", "123", "5725", "65565", "000", "5454", "787887", "253354", "788787", "545454"]
    }
});

//make general setting tax rate editable
$('#general_TaxRate').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make eneral setting general_Language editable
$('#general_Language').editable({
    value: 'English',
    typeahead: {
        remote: 'get-languages?query=%QUERY',
    }
});

//make general setting WeightPreference editable
$('#general_WeightPreference').editable({
    source: [
        {value: 'LBs', text: 'LBs'},
        {value: 'OZs', text: 'OZs'},
        {value: 'KGs', text: 'KGs'}
    ],
});

//make eneral setting general_Timezone editable
$('#general_Timezone').editable();
//    $('#general_Timezone').editable({
//            prepend: "not selected",
//            source: function() {
//                $.ajax({
//                    url: 'get-timezone',
//                    dataType: 'json',
//                    success: function(data) {
//                    [{text: "group1", children: [{value: 1, text: "text1"}, {value: 2, text: "text2"}]}];
//
//                    }
//                });
//            },
////            source: [
////
////            {value: 1, text: 'Male'}
////            ],
//
//    });

$('#general_category').editable({
    source: '/general/categories',
    success: function (response, newValue) {
        var request = $.ajax({
            url: "/general/save-category",
            type: "POST",
            data: {category: newValue},
            dataType: "json"
        });
        request.done(function (msg) {
            //$('#general_category').editable('setValue', msg.annual_revenue);
        });
    }
});

$('#general_CurrencyPreference').editable({
    source: '/integrations/currencies',
    success: function (response, newValue) {
        var request = $.ajax({
            url: "/general/savecurrency",
            type: "POST",
            data: {currency: newValue},
            dataType: "json"
        });
        request.done(function (msg) {
            $('.currency_symbol').html(msg.currency_symbol);
            $('#annual_revenue').editable('setValue', msg.annual_revenue);
        });
    }
});
$('#annual_revenue').editable({

    success: function (response, newValue) {
        var request = $.ajax({
            url: "/general/saverevenue",
            type: "POST",
            data: {revenue: newValue},
//                dataType: "html"
        });

        request.done(function (msg) {
            window.location.href = '/general'
        });
    }
});

//Save General Information
function savegeneralinfo() {

    var AccountOwner = $('#AccountOwner').text();
    var currency = $('#general_CurrencyPreference').text();
    var general_corporate_street1 = $('#general_corporate_street1').text();
    var general_corporate_street2 = $('#general_corporate_street2').text();
    var general_phone_number = $('#general_phone_number').text();
    var general_company = $('#general_company').text();
    var general_corporate_country = $('#general_corporate_country').text();
    var general_corporate_state = $('#general_corporate_state').text();
    var general_corporate_city = $('#general_corporate_city').text();
    var general_corporate_zip = $('#general_corporate_zip').text();
    var subscription_billing_street1 = $('#subscription_billing_street1').text();
    var subscription_billing_street2 = $('#subscription_billing_street2').text();
    var subscription_billing_country = $('#subscription_billing_country').text();
    var subscription_billing_state = $('#subscription_billing_state').text();
    var subscription_billing_city = $('#subscription_billing_city').text();
    var subscription_billing_zip = $('#subscription_billing_zip').text();
    var general_TaxRate = $('#general_TaxRate').text();
    var general_Language = $('#general_Language').text();
    var general_WeightPreference = $('#general_WeightPreference').text();
    var general_Timezone = $('#general_Timezone').text();

    jQuery.ajax({
        type: 'post',
        url: '/dashboard/save-general-info',
        data: {
            AccountOwner: AccountOwner,
            currency: currency,
            general_corporate_street1: general_corporate_street1,
            general_corporate_street2: general_corporate_street2,
            general_phone_number: general_phone_number,
            general_company: general_company,
            general_corporate_country: general_corporate_country,
            general_corporate_state: general_corporate_state,
            general_corporate_city: general_corporate_city,
            general_corporate_zip: general_corporate_zip,
            subscription_billing_street1: subscription_billing_street1,
            subscription_billing_street2: subscription_billing_street2,
            subscription_billing_country: subscription_billing_country,
            subscription_billing_state: subscription_billing_state,
            subscription_billing_city: subscription_billing_city,
            subscription_billing_zip: subscription_billing_zip,
            general_TaxRate: general_TaxRate,
            general_Language: general_Language,
            general_WeightPreference: general_WeightPreference,
            general_Timezone: general_Timezone,
        },
        dataType: "json",
        success: function (value) {
            if (value.status == 'success') {
                window.location = "general";
            } else {

            }
        },
    });
}