
$(document).ready(function(){
    App.init();
    App.formMultiselect();
    $('#role_menu_opt').multiSelect();
    $('#role_channel1_select').multiSelect();
    $('#role_setting_optgroup').multiSelect();

});



/*
$(document).ready(function () {

    $("#role_sub").editable({
        source: $.parseJSON($("#sub_role_id").text()),
        display: function (value, sourceData) {
            var colors = {"merchant": "pink", "merchant_user": "blue"},
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
});
*/