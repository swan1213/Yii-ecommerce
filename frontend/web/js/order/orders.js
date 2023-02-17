// Regarding channels

$(document).ready(function () {

    var connection_id = $("#connection_id").val();
    $('#order_index_table').DataTable({
        "order": [[5, "desc"]],
        pageLength: 25,
        "columnDefs": [
            {"orderable": false, "targets": 0},
        ],
        "columns": [
            null,
            null,
            {className: "Custom_SCroll"},
            null,
            null,
            null,
            null,
            null
        ],
        "processing": true,
        "serverSide": true,
        "ajax": "/order/order-all-ajax",
        oLanguage: {
            sProcessing: function () {
                $(".orderListTAble").addClass('be-loading-active');
            },
        },
        initComplete: function () {
            $(".orderListTAble").removeClass('be-loading-active');
            $(".dataTables_processing").remove();
        },
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });

    $('#order_connected_table').DataTable({
        "order": [[5, "desc"]],
        pageLength: 25,
        "columnDefs": [
            {"orderable": false, "targets": 0},
        ],
        "columns": [
            null,
            null,
            {className: "Custom_SCroll"},
            null,
            null,
            null,
            null,
            null
        ],
        "processing": true,
        "serverSide": true,
        "ajax": "/order/order-connected-ajax?connection_id=" + connection_id,
        oLanguage: {
            sProcessing: function () {
                $(".orderListTAble").addClass('be-loading-active');
            },
        },
        initComplete: function () {
            $(".orderListTAble").removeClass('be-loading-active');
            $(".dataTables_processing").remove();
        },
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });

    $('#order_inactive_table').DataTable({
        "order": [[5, "desc"]],
        pageLength: 25,
        "columnDefs": [
            {"orderable": false, "targets": 0},
        ],
        "columns": [
            null,
            null,
            {className: "Custom_SCroll"},
            null,
            null,
            null,
            null,
            null
        ],
        "processing": true,
        "serverSide": true,
        "ajax": "/order/order-inactive-ajax?connection_id=" + connection_id,
        oLanguage: {
            sProcessing: function () {
                $(".orderListTAble").addClass('be-loading-active');
            },
        },
        initComplete: function () {
            $(".orderListTAble").removeClass('be-loading-active');
            $(".dataTables_processing").remove();
        },
        buttons: [
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
    /*

  * multiple selection of the data table on order
  */

    $(".order_multiple_check").change(function () {  //"select all" change
        $(".order_row_check").prop('checked', $(this).prop("checked")); //change all ".checkbox" checked status
//        if($(".order_multiple_check").prop('checked')==true){
//            $('#dropdown_delete').addClass('open');
//        }
//        else{
//            $('#dropdown_delete').removeClass('open');
//        }
    });

    $("body").on('change', ".order_row_check", function () {
        var atLeastOneIsChecked = $('.order_row_check:checkbox:checked').length;
//        if(atLeastOneIsChecked==0){
//            $('#dropdown_delete').removeClass('open');
//        }
//        else{
//            $('#dropdown_delete').addClass('open');
//        }
        //uncheck "select all", if one of the listed checkbox item is unchecked
        if (false == $(this).prop("checked")) { //if this item is unchecked
            $(".order_multiple_check").prop('checked', false); //change "select all" checked status to false
        }
        //check "select all" if all checkbox items are checked
        if ($('.order_row_check:checked').length == $('.order_row_check').length) {
            $(".order_multiple_check").prop('checked', true);
        }
    });
    $("body").on('click', "#order_delete_button", function () {
        $("#order-delete-modal-warning .close").click();
        $('.orderListTAble ').addClass('be-loading-active');
        var checkedOrderId = $('input[name=order_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            type: 'POST',
            url: '/orders/ajax-orders-delete',
            data: {
                orderIds: checkedOrderId,
            },
        }).done(function (data) {
            setTimeout(function () {
                $('.orderListTAble').removeClass('be-loading-active');
                window.location.reload();
            }, 4000);

        });
    });

    $("body").on('click', "#connect_order_delete_button", function () {
        $("#connect-order-delete-modal-warning .close").click();
        $('.connectedOrDERS ').addClass('be-loading-active');
        var checkedOrderId = $('input[name=order_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            type: 'POST',
            url: '/orders/ajax-orders-delete',
            data: {
                orderIds: checkedOrderId,
            },
        }).done(function (data) {
            setTimeout(function () {
                $('.connectedOrDERS').removeClass('be-loading-active');
                window.location.reload();
            }, 4000);

        });
    });
    $("body").on("click", "#inactive_order_delete_button", function () {
        $("#inactive-order-delete-modal-warning .close").click();
        $('.inactiveOrDERS').addClass('be-loading-active');
        var checkedOrder = $('input[name=inactiveorder_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            type: 'POST',
            url: '/orders/ajax-inactive-orders-deletion',
            data: {
                order_id: checkedOrder
            }
        }).done(function (data) {
            setTimeout(function () {
                $('.inactiveOrDERS').removeClass('be-loading-active');
                window.location.reload();
            }, 4000);

        });
    });
});