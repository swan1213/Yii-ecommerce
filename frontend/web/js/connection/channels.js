// Regarding channels

$(document).ready(function () {
    // Regarding channels
    $("#channels_tbl_view").dataTable({
        pageLength: 50,
        buttons: [
            'excel', 'pdf',
        ],
        dom: "<'row be-datatable-header'<'col-sm-6'l><'col-sm-6 text-right'B><'col-sm-12 text-right'f>>" +
        "<'row be-datatable-body'<'col-sm-12'tr>>" +
        "<'row be-datatable-footer'<'col-sm-5'i><'col-sm-7'p>>"
    });

    $('#amazonconnectionform-marketplace_ids').multiSelect();


    $('.subscribeChannel').on('click', function () {
        $this = $(this);

        var link_url = "";
        var link_type = $this.attr('data-type');

        switch (link_type)
        {
            case "Facebook":
                link_url = '/facebook';
                break;
            case "Jet":
                link_url = '/channels/jet';
                break;
            case "Flipkart":
                link_url = '/channels/flipkart';
                break;
            case "Rakuten":
                link_url = '/channels/rakuten';
                break;

        }
        if ( link_url.length > 0) {
            window.location.href = link_url;
        }
    });
});
$('.rakuten_modal_close').click(function () {
    $('.rakuten_modal').css({
        'display': 'none',
        'background': 'rgba(0,0,0,0.6)',
    });
});