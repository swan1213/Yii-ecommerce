$(document).on("click", "#fb_feed_submit", function (event) {
    var name_chk = false;
    var cate_chk = false;
    var ctry_chk = false;
    $(".customer_validate").each(function () {
        var val = $.trim($(this).val());
        if (!val) {
            $(this).addClass("inpt_required");
            return false;
        } else {
            name_chk = true;
            $(this).removeClass("inpt_required");
        }
    })
    if (!name_chk ) {
        alert("Please enter feed name!")
        return false;
    }
    var fb_feed_categories = $(".fb_feed_categories");
    for (let i=0; i<fb_feed_categories.length; i++) {
        var category = fb_feed_categories[i];
        if(category.checked){
            cate_chk = true;
            break;
        }
    }
    if (!cate_chk ) {
        alert("Please select the categories!")
        return false;
    }
    var fb_feed_countries = $(".fb_feed_countries");
    for (let i=0; i<fb_feed_countries.length; i++) {
        var country = fb_feed_countries[i];
        if(country.checked){
            ctry_chk = true;
            break;
        }
    }
    if (!ctry_chk ) {
        alert("Please select the countries!")
        return false;
    }
});