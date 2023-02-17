
/*Starts Account Profile About Me */

//make Account firstname editable
$('#profile_first_name').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Account lastname editable
$('#profile_last_name').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }

    },
});

//make Account email editable
$('#profile_email_add').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
        var profile_email_add = value;
        var atpos = profile_email_add.indexOf("@");
        var dotpos = profile_email_add.lastIndexOf(".");
        if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= profile_email_add.length)
        {
            return 'Email is Not valid';
        }


    }
});
//make Gender editable
$('#profile_gender').editable({
    //prepend:value,
    source: [
        {value: 'Unisex', text: 'Unisex'},
        {value: 'Male', text: 'Male'},
        {value: 'Female', text: 'Female'}
    ],
    display: function (value, sourceData) {
        var colors = {"Female": "pink", "Male": "blue", "Unisex": "gray"},
            elem = $.grep(sourceData, function (o) {
                return o.value == value;
            });
        if (elem.length) {
            $(this).text(elem[0].text).css("color", colors[value]);
        } else {
            $(this).empty();
        }
    },
});

$("#profile_timezone").editable({
    source: '/integrations/timezones',
});


//make Account PhoneNo editable
$('#profile_Phone_no').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
        if (/^\d{10}$/.test(value)) {
            // value is ok, use it
        } else {
            return 'Invalid number; must be ten digits';

        }

    }
});

//make Account corporate_street1 editable
$('#profile_corporate_street1').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Account corporate_street2 editable
$('#profile_corporate_street2').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Account dob editable
$('#profile_dob').editable({
    format: 'dd-mm-yyyy',
    viewformat: 'dd/mm/yyyy',
    datepicker: {
        weekStart: 1
    }
});

//make city editable
$('#profile_corporate_city').editable({
    typeahead: {
        name: 'city',
        remote: 'get-cities?query=%QUERY',
        // local: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"]
    }
});

//make state editable
$('#profile_corporate_state').editable({
    typeahead: {
        name: 'state',
        remote: 'get-states?query=%QUERY',
    },
});

//make ZipCode editable
$('#profile_corporate_zip').editable({
    value: '143001',
    typeahead: {
        name: 'zip code',
        local: ["143001", "0001", "14250", "123", "5725", "65565", "000", "5454", "787887", "253354", "788787", "545454"]
    }
});

//make Country editable
$('#profile_corporate_country').editable({
    typeahead: {
        name: 'country',
        remote: 'get-countries?query=%QUERY',
    },
//            local: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"]

});

//make Ship_street1 editable
$('#profile_ship_street1').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Ship_street2 editable
$('#profile_ship_street2').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Ship City editable
$('#profile_ship_city').editable({
    typeahead: {
        name: 'city',
        remote: 'get-cities?query=%QUERY',
        // local: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"]
    }
});

//make Ship State editable
$('#profile_ship_state').editable({
    typeahead: {
        name: 'state',
        remote: 'get-states?query=%QUERY',
        // local: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"]
    }
});

//make Ship Zipcode editable
$('#profile_ship_zip').editable({
    value: '143001',
    typeahead: {
        name: 'zip code',
        local: ["143001", "0001", "14250", "123", "5725", "65565", "000", "5454", "787887", "253354", "788787", "545454"]
    }
});

//make Ship Country editable
$('#profile_ship_country').editable({
    typeahead: {
        name: 'country',
        remote: 'get-countries?query=%QUERY',
    },
});

/*End User Account Profile About Me */


/*Starts Customer Account Profile About Me */

//make Customer Account firstname editable
$('#customer_first_name').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Account lastname editable
$('#customer_last_name').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Account email editable
$('#customer_email_add').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
        var profile_email_add = value;
        var atpos = profile_email_add.indexOf("@");
        var dotpos = profile_email_add.lastIndexOf(".");
        if (atpos < 1 || dotpos < atpos + 2 || dotpos + 2 >= profile_email_add.length)
        {
            return 'Email is Not valid';
        }

    }
});
//make Gender editable
$('#customer_gender').editable({
    //prepend:value,
    source: [
        {value: 'Unisex', text: 'Unisex'},
        {value: 'Male', text: 'Male'},
        {value: 'Female', text: 'Female'}
    ],
    display: function (value, sourceData) {
        var colors = {"Female": "pink", "Male": "blue", "Unisex": "gray"},
            elem = $.grep(sourceData, function (o) {
                return o.value == value;
            });
        if (elem.length) {
            $(this).text(elem[0].text).css("color", colors[value]);
        } else {
            $(this).empty();
        }
    },
});

//make Account PhoneNo editable
$('#customer_Phone_no').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
//            if (/^\d{10}$/.test(value)) {
//                // value is ok, use it
//            }
//            else {
//                return 'Invalid number; must be ten digits';
//
//            }

    }
});

//make Account corporate_street1 editable

$('.cus_bill_street1').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Account corporate_street2 editable
$('.cus_bill_street2').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Account dob editable
$('#customer_dob').editable({
    format: 'dd-mm-yyyy',
    viewformat: 'dd/mm/yyyy',
    datepicker: {
        weekStart: 1
    }
});

//make city editable
$('.cus_bill_city').editable({
    typeahead: {
        name: 'city',
        remote: '/get-cities?query=%QUERY',
        // local: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"]
    }
});

//make state editable
$('.cus_bill_state').editable({
    typeahead: {
        name: 'state',
        remote: '/get-states?query=%QUERY',
    },
});

//make ZipCode editable
$('.cus_bill_zip').editable({
    typeahead: {
        name: 'zip code',
        local: ["143001", "0001", "14250", "123", "5725", "65565", "000", "5454", "787887", "253354", "788787", "545454"]
    }
});

//make Country editable
$('.cus_bill_country').editable({
    typeahead: {
        name: 'country',
        remote: '/get-countries?query=%QUERY',
    },
//            local: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"]

});

//make Ship_street1 editable
$('.cus_ship_street1').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Ship_street2 editable
$('.cus_ship_street2').editable({
    validate: function (value) {
        if ($.trim(value) == '') {
            return 'This field is required';
        }
    }
});

//make Ship City editable
$('.cus_ship_city').editable({
    typeahead: {
        name: 'city',
        remote: '/get-cities?query=%QUERY',
        // local: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"]
    }
});

//make Ship State editable
$('.cus_ship_state').editable({
    typeahead: {
        name: 'state',
        remote: '/get-states?query=%QUERY',
        // local: ["Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida", "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine", "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska", "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Dakota", "North Carolina", "Ohio", "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas", "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming"]
    }
});

//make Ship Zipcode editable
$('.cus_ship_zip').editable({
    typeahead: {
        name: 'zip code',
        local: ["143001", "0001", "14250", "123", "5725", "65565", "000", "5454", "787887", "253354", "788787", "545454"]
    }
});

//make Ship Country editable
$('.cus_ship_country').editable({
    typeahead: {
        name: 'country',
        remote: '/get-countries?query=%QUERY',
    },
});

//Save Account Profile Information
function saveprofile() {

    var profile_first_name = $('#profile_first_name').text();
    var profile_last_name = $('#profile_last_name').text();
    var profile_email_add = $('#profile_email_add').text();
    var profile_dob = $('#profile_dob').text();
    var gender = $('#profile_gender').text();
    var profile_Phone_no = $('#profile_Phone_no').text();
    var profile_timezone = $('#profile_timezone').text();
    var corporate_street1 = $('#profile_corporate_street1').text();
    var corporate_street2 = $('#profile_corporate_street2').text();
    var corporate_city = $('#profile_corporate_city').text();
    var corporate_state = $('#profile_corporate_state').text();
    var corporate_zip = $('#profile_corporate_zip').text();
    var corporate_country = $('#profile_corporate_country').text();
    var ship_street1 = $('#profile_ship_street1').text();
    var ship_street2 = $('#profile_ship_street2').text();
    var ship_city = $('#profile_ship_city').text();
    var ship_state = $('#profile_ship_state').text();
    var ship_zip = $('#profile_ship_zip').text();
    var ship_country = $('#profile_ship_country').text();

    jQuery.ajax({
        type: 'post',
        url: '/user/profile/saveprofile',
        data: {
            profile_first_name: profile_first_name,
            profile_last_name: profile_last_name,
            profile_email_add: profile_email_add,
            profile_dob: profile_dob,
            gender: gender,
            profile_Phone_no: profile_Phone_no,
            profile_timezone: profile_timezone,
            corporate_street1: corporate_street1,
            corporate_street2: corporate_street2,
            corporate_city: corporate_city,
            corporate_state: corporate_state,
            corporate_zip: corporate_zip,
            corporate_country: corporate_country,
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
                window.location = "profile";
            } else {

            }
        },
    });
}