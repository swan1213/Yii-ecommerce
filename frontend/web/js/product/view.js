function fixedPercenttoStr ( topNumber, bottomNumber, fixedFormat=2 ) {
    var formatStr = "0%";
    if ( bottomNumber > 0 ) {
        var formatNum = topNumber/bottomNumber * 100;
        formatStr = formatNum.toFixed(fixedFormat) + "%";

    }
    return formatStr;
}

function fixedCountStr ( topNumber, bottomNumber ) {
    return topNumber/bottomNumber.toFixed(0);
}

$(document).ready(function () {

    /*For Conected Stock Quantity*/
    $('.variation-store-stk-qty-value').editable({
        type: 'text',
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
            else {

                var originStockVal = parseInt($(this).text());
                var changedObjParentKey = $(this).attr('data-parent-key');
                var parentStoreObject = '#store_sum_stk_qty_' + changedObjParentKey;
                var parentStoreTotalStockVal = parseInt($(parentStoreObject).text());
                var topParent = $(this).parent().parent().parent();

                var calculated_inventory = 0;
                for (i = 0 ; i < topParent.children().length ; i ++) {
                    var store_variation = $(topParent.children().get(i));
                    var inventory = $(store_variation.children().get(1));
                    var inventory_count = parseInt($(inventory.children().get(0)).text());
                    calculated_inventory = calculated_inventory + inventory_count;
                }

                var changedStockVal = parseInt(value);
                var stockDiff = 0;
                if ( originStockVal > changedStockVal ) {
                    stockDiff = parseInt(originStockVal - changedStockVal);
                    calculated_inventory -= stockDiff;
                } else {
                    stockDiff = parseInt(changedStockVal - originStockVal);
                    calculated_inventory += stockDiff;
                }

                if (parentStoreTotalStockVal < calculated_inventory) {
                    return 'Your stock allocation by channel has exceeded your stock availability on the SKU.';
                }
            }
        },
        success: function(data, value){
            var changedObjKey = $(this).attr('data-key');
            var changedObjField = $(this).attr('data-field');
            var changedStockVal = parseInt(value);
            var stockManage = $('#qnt').prop('checked')?'Yes':'No';
            var productId = $('#product_id').val();

            $.ajax({
                type: 'post',
                url: '/product/stock-update',
                data: {
                    variationId: changedObjKey,
                    stockVal: changedStockVal,
                    stockMan: stockManage,
                    productId: productId,
                    stockField: changedObjField,
                },
                success: function (result) {
                    var response = $.parseJSON(result);
                    if ( response.success ) {
                        console.log('update success');
                    }
                },
            });
        }
    });

    $('.variation-store-stk-percent').editable({
        type: 'text',
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
            else {

                var originPercent = parseFloat($(this).text());
                var parentStoreTotalPercent = 100;
                var topParent = $(this).parent().parent().parent();

                var calculated_percent = 0;
                for (i = 0 ; i < topParent.children().length ; i ++) {
                    var store_variation = $(topParent.children().get(i));
                    var inventory = $(store_variation.children().get(2));
                    var inventory_percent = parseFloat($(inventory.children().get(0)).text());
                    calculated_percent = calculated_percent + inventory_percent;
                }

                var changedPercent = parseFloat(value);
                var stockDiff = 0;
                if ( originPercent > changedPercent ) {
                    stockDiff = parseFloat(originPercent - changedPercent);
                    calculated_percent -= stockDiff;
                } else {
                    stockDiff = parseFloat(changedPercent - originPercent);
                    calculated_percent += stockDiff;
                }

                if (parentStoreTotalPercent < calculated_percent) {
                    return 'Your stock allocation by channel has exceeded your stock availability on the SKU.';
                }
            }
        },
        success: function(data, value){
            var changedObjKey = $(this).attr('data-key');
            var changedObjField = $(this).attr('data-field');
            var changedPercentVal = parseFloat(value);
            var productId = $('#product_id').val();

            var changedObjParentKey = $(this).attr('data-parent-key');
            var changedObjChildrenKey = $(this).attr('data-children-key');
            var parentStoreObject = '#store_sum_stk_qty_' + changedObjParentKey;
            var currentStkObject = '#store_stk_qty_' + changedObjChildrenKey;

            var sumSku = $(parentStoreObject).text();
            var percent = (sumSku * changedPercentVal / 100).toFixed(0);
            $(currentStkObject).text(percent);

            $.ajax({
                type: 'post',
                url: '/product/stock-percent-update',
                data: {
                    variationId: changedObjKey,
                    percentVal: changedPercentVal,
                    productId: productId,
                    stockField: changedObjField,
                },
                success: function (result) {
                    var response = $.parseJSON(result);
                    if ( response.success ) {
                        console.log('update success');
                    }
                },
            });
        }
    });

    $('.channel-stk-qty-value').editable({
        type: 'text',
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        },
        success: function(data, value){
            var changedVal = parseInt(value);
            var productId = $('#product_id').val();

            $.ajax({
                type: 'post',
                url: '/product/stock-qty-update',
                data: {
                    stockVal: changedVal,
                    productId: productId,

                },
                success: function (result) {
                    var response = $.parseJSON(result);
                    if ( response.success ) {
                        console.log('update success');
                    }
                },
            });
        }

    });

    $('.variation-store-stk-sum-value').editable({
        type: 'text',
        validate: function (value) {
            if ($.isNumeric(value) == '') {
                return 'Only numbers are allowed';
            }
        },
        success: function(data, value){
            var changedObjKey = $(this).attr('data-key');
            var sku = $(this).attr('data-serialkey');
            var changedStockVal = parseInt(value);
            var productId = $('#product_id').val();

            $.ajax({
                type: 'post',
                url: '/product/stock-parent-update',
                data: {
                    variationId: changedObjKey,
                    stockVal: changedStockVal,
                    productId: productId,
                    sku: sku,
                },
                success: function (result) {
                    var response = $.parseJSON(result);
                    if ( response.success ) {
                        console.log('update success');
                    }
                },
            });
        }
    });

    $('.channel-stk-qty-value').editable('option', 'disabled', false);
    $('.variation-store-stk-qty-value').editable('option', 'disabled', false);
    $('.variation-store-stk-sum-value').editable('option', 'disabled', false);
    var checkedInitStatus = $('#qnt').prop('checked')?true:false;
    if (checkedInitStatus ) {

        $('.variation-store-stk-qty-value').editable('option', 'disabled', true);
        $('.variation-store-stk-sum-value').editable('option', 'disabled', true);
    }

    /*For Enable or disble inventory iq*/
    $(".chnl_dsbl_rv").click(function () {
        $(".ModalCustomClass").addClass('be-loading-active');

        var productId = $('#product_id').val();
        var stockManage = $('#qnt').prop('checked')?'No':'Yes';

        $.ajax({
            type: 'post',
            url: '/product/enable-iq',
            data: {
                stockMan: stockManage,
                productId: productId,
            },
            success: function (result) {
                $(".ModalCustomClass").removeClass('be-loading-active');
                if (stockManage == "No") {
                    $(".stock-view-percent").css('display', 'none');
                    $('.variation-store-stk-qty-value').editable('option', 'disabled', false);
                    $('.variation-store-stk-sum-value').editable('option', 'disabled', false);
                }
                else {
                    $(".stock-view-percent").css('display', 'block');
                    $('.variation-store-stk-qty-value').editable('option', 'disabled', true);
                    $('.variation-store-stk-sum-value').editable('option', 'disabled', true);

                    var topParent = $("#channel_store_accordion");
                    for (i = 0 ; i < topParent.children().length ; i ++) {
                        var variation = $(topParent.children().get(i));
                        var parentSku = $(variation.children().get(0));
                        var parentSku1 = $(parentSku.children().get(0));
                        var parentSku2 = $(parentSku1.children().get(1));
                        var sumSku = $(parentSku2.children().get(0)).text();

                        var childrenSku = $(variation.children().get(1));
                        var childrenSku1 = $(childrenSku.children().get(0));

                        var count = Math.floor((sumSku/childrenSku1.children().length));

                        for (j = 0 ; j < childrenSku1.children().length ; j ++) {
                            var store_variation = $(childrenSku1.children().get(j));
                            var inventory = $(store_variation.children().get(1));
                            $(inventory.children().get(0)).text(count);
                        }
                    }
                }
            },
        });
    });
    /*End For inventory iq dynamic*/

    $(document).on('click', '#form-stock-qty .stock-save', function (e) {

        var updatedFlag = false;
        var productId = $('#product_id').val();
        var orgStockManFlag = $('#stockManageFlag').val();
        var stockManage = $('#qnt').prop('checked')?'Yes':'No';
        console.log('stockManage = ' + stockManage);
        console.log('data change save');

        if ( orgStockManFlag != stockManage ){
            updatedFlag = true;
        }

        if (!updatedFlag){
            return true;
        }

        $.ajax({
            type: 'post',
            url: '/product/stock-update',
            data: {
                stockMan: stockManage,
                productId: productId,
            },
            beforeSend: function (xhr) {
                $(".be-wrapper").addClass("be-loading-active");
            },
            success: function (result) {
                $(".be-wrapper").removeClass("be-loading-active");
                $('#stockManageFlag').val(stockManage);
            },
        });
    });
});
