
$(function () {

    //Attributes & Attributes Type Section
    //Summernote products-text-decription
    $('#attr-desc').summernote();
    $('#update-attr-desc').summernote();
    $('#attr-type-desc').summernote();
    $('#update-attr-type-desc').summernote();
    //Create Attribute Type
    $(document).on("click", "#attr_type_submit", function (event) {
        var attr_type_name = $('#attr-type-name input').val();
        var attr_type_label = $('#attr-type-label input').val();
        var isValid = true;
        if (attr_type_name == '') {
            $('#attr-type-name').addClass('has-error');
            isValid = false;
        } else {
            if ($('#attr-type-name').hasClass('has-error')) {
                $('#attr-type-name').removeClass('has-error');
            }
        }
        if (attr_type_label == '') {
            $('#attr-type-label').addClass('has-error');
            isValid = false;
        } else {
            if ($('#attr-type-label').hasClass('has-error')) {
                $('#attr-type-label').removeClass('has-error');
            }
        }
        var attr_type_desc = $('#attr-type-desc').summernote('code');
        $('#attr_type_desc').val(attr_type_desc);
        if (!isValid) {
            return false;
        }

    });
    //Update Attribute Type
    $('#update-attr-type-name').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        },
        emptytext: 'Please Enter Value'
    });
    $('#update-attr-type-label').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        },
        emptytext: 'Please Enter Value'
    });

    //Create Attribute
    $(document).on("click", "#attr_submit", function (event) {
        var attr_name = $('#attr-name input').val();
        var attr_label = $('#attr-label input').val();
        var attr_type = $('#attr-type select option:selected').text();
        var isValid = true;
        if (attr_name == '') {
            $('#attr-name').addClass('has-error');
            isValid = false;
        } else {
            if ($('#attr-name').hasClass('has-error')) {
                $('#attr-name').removeClass('has-error');
            }
        }
        if (attr_type == 'Please Select') {
            $('#attr-type .select2-container--default').css('border', '1px solid #ea4335');
            isValid = false;
        } else {
            $('#attr-type .select2-container--default').css('border', '1px solid #dedede');
        }
        if (attr_label == '') {
            $('#attr-label').addClass('has-error');
            isValid = false;
        } else {
            if ($('#attr-label').hasClass('has-error')) {
                $('#attr-label').removeClass('has-error');
            }
        }
        var attr_desc = $('#attr-desc').summernote('code');
        $('#attr_desc').val(attr_desc);
        if (!isValid) {
            return false;
        }
    });
    //Update Attributes
    $('#update-attr-name').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        },
        emptytext: 'Please Enter Value'
    });
    $('#update-attr-label').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        },
        emptytext: 'Please Enter Value'
    });
    $('#update-attr-type').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        },
        emptytext: 'Please Enter Value'
    });
});