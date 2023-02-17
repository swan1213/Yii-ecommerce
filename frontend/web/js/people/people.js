// Regarding channels

$(document).ready(function () {
    // Regarding channels
    var connection_id = $("#connection_id").val();

    $('#inactive_people_tbl_view').DataTable({
        "order": [[5, "desc"]],
        pageLength: 25,
        "columnDefs": [
            {"orderable": false, "targets": 0},
            {"targets": 4, "orderable": false}
        ],
        "columns": [
            null,
            null,
            {className: "Custom_SCroll"},
            null,
            null,
            null,
            null
        ],
        "processing": true,
        "serverSide": true,
        "ajax": "/people/inactive-customerajax?connection_id=" + connection_id,
        oLanguage: {
            sProcessing: function () {
                $(".customerListTAble").addClass('be-loading-active');
            },
        },
        initComplete: function () {
            $(".customerListTAble").removeClass('be-loading-active');
            $(".dataTables_processing").remove();
        },
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });

    $('#connected_people_tbl_view').DataTable({
        "order": [[5, "desc"]],
        pageLength: 25,
        "columnDefs": [
            {"orderable": false, "targets": 0},
            {"targets": 4, "orderable": false}
        ],
        "columns": [
            null,
            null,
            {className: "Custom_SCroll"},
            null,
            null,
            null,
            null
        ],
        "processing": true,
        "serverSide": true,
        "ajax": "/people/connnected-customerajax?connection_id=" + connection_id,
        oLanguage: {
            sProcessing: function () {
                $(".customerListTAble").addClass('be-loading-active');
            },
        },
        initComplete: function () {
            $(".customerListTAble").removeClass('be-loading-active');
            $(".dataTables_processing").remove();
        },
        buttons: [
            'copy', 'excel', 'pdf', 'print'
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });

    $(".people_multiple_check").change(function () {  //"select all" change
        $(".people_row_check").prop('checked', $(this).prop("checked"));
    });

    $("body").on('change', ".people_row_check", function () {
        var atLeastOneIsChecked = $('.people_row_check:checkbox:checked').length;
        if (false == $(this).prop("checked")) {
            $(".people_multiple_check").prop('checked', false);
        }
        if ($('.people_row_check:checked').length == $('.people_row_check').length) {
            $(".people_multiple_check").prop('checked', true);
        }
    });
    $("body").on('click', "#people_delete_button", function () {
        $("#people-delete-modal-warning .close").click();
        $('.customerListTAble').addClass('be-loading-active');
        var checkedPeopleId = $('input[name=people_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            type: 'POST',
            url: '/channels/ajax-people-delete',
            data: {
                peopleIds: checkedPeopleId,
            },
        }).done(function (data) {
            setTimeout(function () {
                $('.customerListTAble').removeClass('be-loading-active');
                window.location.reload();
            }, 4000);

        });
    });
    $("body").on("click", "#connected_people_delete_button", function () {
        $("#connected-people-delete-modal-warning .close").click();
        $('.connectedcustomerLIST').addClass('be-loading-active');
        var checkedPeopleId = $('input[name=people_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            type: 'POST',
            url: '/channels/ajax-people-delete',
            data: {
                peopleIds: checkedPeopleId,
            },
        }).done(function (data) {
            setTimeout(function () {
                $('.connectedcustomerLIST').removeClass('be-loading-active');
                window.location.reload();
            }, 4000);

        });


    });
    $("body").on('click', '#inactive_people_delete_button', function () {
        $("#inactive-people-delete-modal-warning .close").click();
        $('.inactivecustomerLIST').addClass('be-loading-active');
        var checkedPeopleId = $('input[name=people_check]:checked').map(function () {
            return $(this).val();
        }).get();
        $.ajax({
            type: 'POST',
            url: '/channels/ajax-people-undo-delete',
            data: {
                Inactive_peopleIds: checkedPeopleId,
            },
        }).done(function (data) {
            setTimeout(function () {
                $('.inactivecustomerLIST').removeClass('be-loading-active');
                window.location.reload();
            }, 4000)

        });

    });
});

$(document).on("click", "#people_submit", function (event) {
    var chk = false;
    $(".customer_validate").each(function () {
        var val = $.trim($(this).val());
        if (!val || val == 'Please Select Value' || val == 'Please Select Country First' || val == 'Please Select State First') {
            $(this).addClass("inpt_required");
            chk = false;
        } else {
            $(this).removeClass("inpt_required");
        }
    })
    var channel_accquired = $(".channel_accquired");
    for (let i=0; i<channel_accquired.length; i++) {
        var channel = channel_accquired[i];
        if(channel.checked){
            chk = true;
            break;
        }
    }
    if (!chk) {
        alert("Please select the channels!")
        return false;
    }
});

$('#people_tbl_view').DataTable({
    "order": [[5, "desc"]],
    pageLength: 25,
    "columnDefs": [
        {"orderable": false, "targets": 0},
        {"targets": 4, "orderable": false}
    ],
    "columns": [
        null,
        null,
        {className: "Custom_SCroll"},
        null,
        null,
        null,
        null
    ],
    "processing": true,
    "serverSide": true,
    "ajax": "/people/customerajax",
    oLanguage: {
        sProcessing: function () {
            $(".customerListTAble").addClass('be-loading-active');
        },
    },
    initComplete: function () {
        $(".customerListTAble").removeClass('be-loading-active');
        $(".dataTables_processing").remove();
    },
    buttons: [
        'copy', 'excel', 'pdf', 'print'
    ],
    dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
    "<'row be-datatable-body'<'col-sm-12'tr>>" +
    "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
});