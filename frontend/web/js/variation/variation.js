
$(function () {
    /* Products Listing DataTable */
    $("#product_variation_table").dataTable({
        responsive: true,
        "order": [[1, "desc"]],
        pageLength: 25,
        "columnDefs": [
            {"orderable": false, "targets": 0},
            {"targets": 2, "orderable": false}
        ],
        "columns": [
            null,
            {className: "Custom_SCroll"},
            null,
        ],
        "oLanguage": {
            sProcessing: function () {
                $(".product_variation_body").first().addClass('be-loading-active');
            }
        },
        "initComplete": function () {
            $(".product_variation_body").first().removeClass('be-loading-active');

        },
        drawCallback: function (settings) {
            var api = this.api();
            $('[data-toggle="tooltip"]').tooltip();
            $("[data-toggle='popover']").popover();
        },
        "processing": true,
        "serverSide": true,
        "ajax": "/variations/variations-ajax",
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });
});