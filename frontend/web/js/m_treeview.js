$(document).ready(function() {
    $('.fb_feed_categories').change(function() {
        $(this).parent().parent().find("input").prop("checked", $(this).is(":checked"));
        if(!$(this).is(":checked")) {
            $(this).parent().parent().parents("li").children('div').children('input').prop("checked", $(this).is(":checked"));
        }
    });
});