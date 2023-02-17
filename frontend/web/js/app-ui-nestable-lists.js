var App = (function () {
    'use strict';

    App.uiNestableLists = function ( ) {

        $('.dd').nestable();
        //Watch for list changes and show serialized output
        function update_out(selector, sel2) {
            var out = $(selector).nestable('serialize');
            $(sel2).html(window.JSON.stringify(out));

        }

        update_out('#list1', "#out1");
        update_out('#list2', "#out2");

        $('#list1').on('change', function () {
            update_out('#list1', "#out1");
        });

        $('#list2').on('change', function () {
            update_out('#list2', "#out2");
//            var val = $("#out2").text();
//            console.log(val);
//            jQuery.ajax({
//
//                type: 'post',
//                url: 'categories/update-nested-cat',
//                data: {data: val, },
//                dataType: "json",
//                success: function (value) {
//
//                },
//            });
            
        });

    };

    return App;
})(App || {});





