
$(function () {
    /*Starts Add categoreis */

    //make Category Name editable
    $('#add_cat_name').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        },
        emptytext: 'Please Enter'
    });
    //make Parent Category Name
    $('#add_parent_cat_name').editable({
        emptytext: 'Please Select'
    });

    //make Category Name editable
    $('#update_cat_name').editable({
        validate: function (value) {
            if ($.trim(value) == '') {
                return 'This field is required';
            }
        },
        emptytext: 'Please Enter'
    });
    //make Parent Category Name
    $('#update_parent_cat_name').editable({
        emptytext: 'Please Select'
    });
});
//Add Categories
function addcategories() {

    var add_cat_name = $('#add_cat_name').text();
    var add_parent_cat_name = $('#add_parent_cat_name').text();
    if (add_cat_name == 'Empty') {
        document.getElementById("span_err_add_cat_name").innerHTML = "* Please Enter Category Name";
        return false;
    } else {
        document.getElementById("span_err_add_cat_name").innerHTML = "";
    }

    jQuery.ajax({
        type: 'post',
        url: 'create',
        data: {
            add_cat_name: add_cat_name,
            add_parent_cat_name: add_parent_cat_name,
        },
        dataType: "json",
        success: function (value) {
            if (value.status == 'success') {
                //window.location = "profile";
            } else {

            }
        },
    });
}

//Add Categories
function updatecategories(id) {

    var update_cat_name = $('#update_cat_name').text();

    var update_parent_cat_name = $('#update_parent_cat_name').text();
    var id = id;


    if (update_cat_name == 'Empty') {
        document.getElementById("span_err_update_cat_name").innerHTML = "* Please Enter Category Name";
        return false;
    } else {
        document.getElementById("span_err_update_cat_name").innerHTML = "";
    }

    jQuery.ajax({
        type: 'post',
        url: '/categories/updatecat',
        data: {
            id: id,
            update_cat_name: update_cat_name,
            update_parent_cat_name: update_parent_cat_name,
        },
        dataType: "json",
        success: function (value) {
            if (value.status == 'success') {
                //window.location = "updatecat";
            } else {

            }
        },
    });
}