
/*********************************DASHBOARD CHART START HERE************************************/
var daterange = $('.daterange').val();

(function ($) {
    $(function () {
        "use strict";
        if ($('#dashboard-chart').hasClass('dashboard-chart') == true) {
            var user_id = $('#connected_user').val();
            getAllAjaxNewDashboard(user_id, 'month', '', '', daterange);
            getRecentOrders(user_id, 'month', '', '', daterange);
            getLatestEngagements(user_id, 'month', '', '', daterange);
            getTopProducts(user_id, 'month', '', '', daterange);
            //$(window).on('load',function () {
            $('.OrderChaRT').addClass('be-loading-active');
            $.ajax({
                'method': 'post',
                'url': '/dashboard/areachartondashboard',
                dataType: "json",
                data: {user_id: user_id, data: 'dashboardchartmonth', daterange: daterange},
                success: function (res) {
                    if (res == 'Invalid') {
                        $('.OrderChaRT').removeClass('be-loading-active');
                        $('#dashboard-chart').html('');
                        $('#dashboard-chart').html('<div style="font-size:15px;text-align: center; margin-top: 11%;">Data  Not Available!!</div>');

                    } else {
                        $('.OrderChaRT').removeClass('be-loading-active');
                        $('#chartInfoPeople').html('');
                        $('#chartInfoPeople').html(res.showcolorhtml);
                        $('.customPeople').removeClass('active');
                        $('#dashboardchartmonth').addClass('active');
                        $('#dashboardchartmonthmob').addClass('active');
                        var resp = JSON.parse(res.data);
                        var ykeyslabels = JSON.parse(res.ykeyslabels);
                        var linecolors = JSON.parse(res.linecolors);
                        var area = new Morris.Area({
                            element: 'dashboard-chart',
                            resize: true,
                            data: resp,
                            xkey: 'period',
                            xLabels: 'day',
                            parseTime: false,
                            ykeys: ykeyslabels,
                            labels: ykeyslabels,
                            lineColors: linecolors,
                            xLabelAngle: 0.5,
                            lineWidth: 2,
                            hideHover: 'auto'
                        });
                    }
                }
            });
        }
    });
})(jQuery);

var sales_html = '';
function getAllAjaxNewDashboard(user_id, data_to_send, connection_id = '', type = '', daterange = '') {
    $.ajax({
        'method': 'post',
        'url': '/dashboard/dashboard-graph-orders',
        dataType: "json",
        data: {user_id: user_id, data: data_to_send, connection_id: connection_id, type: type, daterange: daterange},
        success: function (res) {
            if (data_to_send == 'month') {
                sales_html = 'Monthly Sales';
            } else if (data_to_send == 'week') {
                sales_html = 'Weekly Sales';
            } else if (data_to_send == 'quarter') {
                sales_html = 'Quarterly Sales';
            } else if (data_to_send == 'year') {
                sales_html = 'Yearly Sales';
            } else if (data_to_send == 'today') {
                sales_html = 'Today Sales';
            } else if (data_to_send == 'dateRange') {
                sales_html = 'Date Range';
            }


            $('.fourgraph1').removeClass('be-loading-active');
            $('.fourgraph2').removeClass('be-loading-active');
            $('.sales').html('');
            $('.sales').html(sales_html);
            $('.newsparknm1').html('');
            $('.newsparknm2').html('');
            $('.numbersales').html('');
            $('.numberneworders').html('');
            $('.numbersales').html(res.ordercountsales + '%');
            $('.numberneworders').html(res.ordercount);
            $('.newsparknm1').sparkline(res.data, {
                width: '85',
                height: '35',
                lineColor: '#3c8dbc',
                highlightSpotColor: '#3c8dbc',
                highlightLineColor: '#3c8dbc',
                fillColor: false,
                spotColor: false,
                minSpotColor: false,
                maxSpotColor: false,
                lineWidth: 1.15
            });

            $(".newsparknm2").sparkline(res.data, {
                type: 'bar',
                width: '85',
                height: '35',
                barWidth: 3,
                barSpacing: 3,
                chartRangeMin: 0,
                barColor: '#f39c12'
            });
        }
    });
    //ajax hit for 3rd one
    $.ajax({
        'method': 'post',
        'url': '/dashboard/dashboard-graph-product-sold',
        dataType: "json",
        data: {user_id: user_id, data: data_to_send, connection_id: connection_id, type: type, daterange: daterange},
        success: function (res) {
            $('.fourgraph3').removeClass('be-loading-active');
            $('.newsparknm3').html('');
            $('.numberproductsold').html('');
            $('.numberproductsold').html(res.ordercount);
            $('.newsparknm3').sparkline(res.data, {
                type: 'discrete',
                width: '85',
                height: '35',
                lineHeight: 20,
                lineColor: '#00a65a',
                xwidth: 18
            });
        }
    });
    //ajax hit for 4th graph
    $.ajax({
        'method': 'post',
        'url': '/dashboard/dashboard-graph-average-order-value',
        dataType: "json",
        data: {user_id: user_id, data: data_to_send, connection_id: connection_id, type: type, daterange: daterange},
        success: function (res) {
            $('.fourgraph4').removeClass('be-loading-active');
            $('.numberavgorder').html('');
            $('.newsparknm4').html('');
            $('.numberavgorder').html(res.ordercount);
            $('.newsparknm4').sparkline(res.data, {
                width: '85',
                height: '35',
                lineColor: '#dd4b39',
                highlightSpotColor: '#dd4b39',
                highlightLineColor: '#dd4b39',
                fillColor: false,
                spotColor: false,
                minSpotColor: false,
                maxSpotColor: false,
                lineWidth: 1.15
            });

        }
    });

}

function getRecentOrders(user_id, data_to_send, connection_id = '', type = '', daterange = '') {
    $('.recentorders').addClass('be-loading-active');
    $.ajax({
        type: 'post',
        url: '/dashboard/get-recent-orders',
        data: {user_id: user_id, data: data_to_send, connection_id: connection_id, type: type, daterange: daterange},
        success: function (data) {
            $('.recentOrderData').html(data);
            $('.recentorders').removeClass('be-loading-active');
        }
    });
}
function getLatestEngagements(user_id, data_to_send, connection_id = '', type = '', daterange = '') {
    $('.latestengagements').addClass('be-loading-active');

    $.ajax({
        type: 'post',
        url: '/dashboard/get-latest-enagagements',
        data: {user_id: user_id, data: data_to_send, connection_id: connection_id, type: type, daterange: daterange},
        success: function (data) {
            $('.renderlatestengagement').html(data);
            $('.latestengagements').removeClass('be-loading-active');

        }
    });
}
function getTopProducts(user_id, data_to_send, connection_id = '', type = '', daterange = '') {
    $('.topProductsByVolume').addClass('be-loading-active');
    $('.topProductsByRevenue').addClass('be-loading-active');

    $.ajax({
        type: 'post',
        url: '/dashboard/get-filtered-products-by-volume',
        data: {user_id: user_id, data: data_to_send, connection_id: connection_id, type: type, daterange: daterange},
        success: function (data) {
            if (!$.trim(data)) {
                $('.volumeProducts').html('<h3 style="text-align: center;">No data available in table.</h3>');
            } else {
                $('.volumeProducts').html(data);
            }
            $('#volume').carouseller();
            $('.topProductsByVolume').removeClass('be-loading-active');
        }
    });
    $.ajax({
        type: 'post',
        url: '/dashboard/get-filtered-products-by-revenue',
        data: {user_id: user_id, data: data_to_send, connection_id: connection_id, type: type, daterange: daterange},
        success: function (data) {
            if (!$.trim(data)) {
                $('.revenueProducts').html('<h3 style="text-align: center;">No data available in table.</h3>');
            } else {
                $('.revenueProducts').html(data);
            }
            $('#revenue').carouseller();
            $('.topProductsByRevenue').removeClass('be-loading-active');
        }
    });
}

/******************************************AT BUTTON CLICK ON DASHBOARD*********************************/
(function ($) {
    $(function () {
        "use strict";
        if ($('#dashboard-chart').hasClass('dashboard-chart') == true) {

            var user_id = $('#connected_user').val();
            $('body').on('click', '.customPeople', function () {
                var value = $(this).attr('id');
//                console.log(value);
                $('.show-selected-channel-dashboard').html('Sort by Channel');
                if (value == 'dateRange') {
                    return false;
                } else {
                    $('.showdaterange_elliot').addClass('hide');
                }
                $('.fourgraph1').addClass('be-loading-active');
                $('.fourgraph2').addClass('be-loading-active');
                $('.fourgraph3').addClass('be-loading-active');
                $('.fourgraph4').addClass('be-loading-active');
                $('.latestengagements').addClass('be-loading-active');
                $('.recentorders').addClass('be-loading-active');
                $('.OrderChaRT').addClass('be-loading-active');
                $('.ProductsDaSH').addClass('be-loading-active');
                $('.orderDaSH').addClass('be-loading-active');
                $('.avgOrderDaSH').addClass('be-loading-active');

                if (value == 'dashboardchartweek' || value == 'dashboardchartweekmob') {
                    var data_to_send = 'week';
                }
                if (value == 'dashboardchartmonth' || value == 'dashboardchartmonthmob') {
                    var data_to_send = 'month';
                }
                if (value == 'dashboardchartyear' || value == 'dashboardchartyearmob') {
                    var data_to_send = 'year';
                }
                if (value == 'dashboardchartannual' || value == 'dashboardchartannualmob') {
                    var data_to_send = 'annual';
                }
                if (value == 'dashboardcharttoday' || value == 'dashboardcharttodaymob') {
                    var data_to_send = 'today';
                }
                if (value == 'dashboardchartquarter' || value == 'dashboardchartquartermob') {
                    var data_to_send = 'quarter';
                }
                if (value == 'dateRange') {
                    var data_to_send = 'dateRange';
                }
                getAllAjaxNewDashboard(user_id, data_to_send, '', '', daterange);

                getRecentOrders(user_id, data_to_send, '', '', daterange);
                getLatestEngagements(user_id, data_to_send, '', '', daterange);
                getTopProducts(user_id, data_to_send, '', '', daterange);
                $.ajax({
                    'method': 'post',
                    'url': '/dashboard/areachartondashboard',
                    dataType: "json",
                    data: {user_id: user_id, data: value, daterange: daterange},
                    success: function (res) {

                        $('.customPeople').removeClass('active');
                        $('#' + value).addClass('active');
                        if (res == 'Invalid') {
                            $('.OrderChaRT').removeClass('be-loading-active');
                            $('#dashboard-chart').html('');
                            $('#dashboard-chart').html('<div style="font-size:15px;text-align: center; margin-top: 11%;">Data  Not Available!!</div>');
                        } else {
                            $('.OrderChaRT').removeClass('be-loading-active');
                            $('.orderDaSH').removeClass('be-loading-active');
                            $('.ProductsDaSH').removeClass('be-loading-active');
                            $('.avgOrderDaSH').removeClass('be-loading-active');
                            $('.latestengagements').removeClass('be-loading-active');
                            $('.recentorders').removeClass('be-loading-active');
                            $('#chartInfoPeople').html('');
                            $('#chartInfoPeople').html(res.showcolorhtml);
                            $('#dashboard-chart').html('');
                            var resp = '';
                            var ykeyslabels = '';
                            var linecolors = '';
                            var resp = JSON.parse(res.data);
                            var ykeyslabels = JSON.parse(res.ykeyslabels);
                            var linecolors = JSON.parse(res.linecolors);
                            var area = new Morris.Area({
                                element: 'dashboard-chart',
                                resize: true,
                                data: resp,
                                xkey: 'period',
                                parseTime: false,
                                ykeys: ykeyslabels,
                                labels: ykeyslabels,
                                lineColors: linecolors,
                                xLabelAngle: 0.5,
                                lineWidth: 2,
                                hideHover: 'auto',
                            });
                        }
                    }
                });
            });

            $('.SortChannelList li').on('click', function () {

                var $this = $(this);
                var connection_id = $this.attr('attr-value');
                var type = $this.attr('attr-type');
                var selected_country = $this.text();
                var daterange = $('.daterange').val();
                $('.show-selected-channel-dashboard').html(selected_country);

                $('.fourgraph1').addClass('be-loading-active');
                $('.fourgraph2').addClass('be-loading-active');
                $('.fourgraph3').addClass('be-loading-active');
                $('.fourgraph4').addClass('be-loading-active');
                $('.OrderChaRT').addClass('be-loading-active');
                $('.latestengagements').addClass('be-loading-active');
                $('.recentorders').addClass('be-loading-active');
                $('.ProductsDaSH').addClass('be-loading-active');
                $('.orderDaSH').addClass('be-loading-active');
                $('.avgOrderDaSH').addClass('be-loading-active');
                //var value = $(this).attr('id');
                var value = $('.customPeople.active').attr('id');

                if (value == 'dashboardchartweek' || value == 'dashboardchartweekmob') {
                    var data_to_send = 'week';
                }
                if (value == 'dashboardchartmonth' || value == 'dashboardchartmonthmob') {
                    var data_to_send = 'month';
                }
                if (value == 'dashboardchartyear' || value == 'dashboardchartyearmob') {
                    //ajax hit for first 2 graph
                    var data_to_send = 'year';                        }
                if (value == 'dashboardcharttoday' || value == 'dashboardcharttodaymob') {
                    var data_to_send = 'today';
                }
                if (value == 'dashboardchartquarter' || value == 'dashboardchartquartermob') {
                    var data_to_send = 'quarter';
                }
                if (value == 'dateRange') {
                    value = 'dateRange';
                    var data_to_send = 'dateRange';
                }
                var daterange = $('.daterange').val();
                getAllAjaxNewDashboard(user_id, data_to_send, connection_id, type, daterange);

                getRecentOrders(user_id, data_to_send, connection_id, type, daterange);
                getLatestEngagements(user_id, data_to_send, connection_id, type, daterange);
                getTopProducts(user_id, data_to_send, connection_id, type, daterange);

                $.ajax({
                    'method': 'post',
                    'url': '/dashboard/areachartondashboard',
                    dataType: "json",
                    data: {user_id: user_id, data: value, connection_id: connection_id, type: type, daterange: daterange},
                    success: function (res) {

                        $('.customPeople').removeClass('active');
                        $('#' + value).addClass('active');

                        if (res == 'Invalid') {
                            $('.OrderChaRT').removeClass('be-loading-active');
                            $('#dashboard-chart').html('');
                            $('#dashboard-chart').html('<div style="font-size:15px;text-align: center; margin-top: 11%;">Data  Not Available!!</div>');
                        } else {
                            $('.OrderChaRT').removeClass('be-loading-active');
                            $('.orderDaSH').removeClass('be-loading-active');
                            $('.ProductsDaSH').removeClass('be-loading-active');
                            $('.avgOrderDaSH').removeClass('be-loading-active');
                            $('.latestengagements').removeClass('be-loading-active');
                            $('.recentorders').removeClass('be-loading-active');
                            $('#chartInfoPeople').html('');
                            $('#chartInfoPeople').html(res.showcolorhtml);
                            $('#dashboard-chart').html('');
                            if (value == 'dateRange') {
                                $('.dateRange').addClass('active');
                            }
                            var resp = '';
                            var ykeyslabels = '';
                            var linecolors = '';
                            var resp = JSON.parse(res.data);
                            var ykeyslabels = JSON.parse(res.ykeyslabels);
                            var linecolors = JSON.parse(res.linecolors);
                            var area = new Morris.Area({
                                element: 'dashboard-chart',
                                resize: true,
                                data: resp,
                                xkey: 'period',
                                parseTime: false,
                                ykeys: ykeyslabels,
                                labels: ykeyslabels,
                                lineColors: linecolors,
                                xLabelAngle: 0.5,
                                lineWidth: 2,
                                hideHover: 'auto',
                            });
                        }
                    }
                });
            });

            $('body').on('click', '.newdashchartNew', function () {
                $('.fourgraph1').addClass('be-loading-active');
                $('.fourgraph2').addClass('be-loading-active');
                $('.fourgraph3').addClass('be-loading-active');
                $('.fourgraph4').addClass('be-loading-active');
                $('.OrderChaRT').addClass('be-loading-active');
                $('.ProductsDaSH').addClass('be-loading-active');
                $('.orderDaSH').addClass('be-loading-active');
                $('.avgOrderDaSH').addClass('be-loading-active');
                $('.latestengagements').addClass('be-loading-active');
                $('.recentorders').addClass('be-loading-active');
                var $this = $(this);
                var daterange = $('.daterange').val();
                getAllAjaxNewDashboard(user_id, 'dateRange', '', '', daterange);
                getRecentOrders(user_id, 'dateRange', '', '', daterange);
                getLatestEngagements(user_id, 'dateRange', '', '', daterange);
                getTopProducts(user_id, 'dateRange', '', '', daterange);

                $.ajax({
                    'method': 'post',
                    'url': '/dashboard/areachartondashboard',
                    dataType: "json",
                    data: {user_id: user_id, data: 'dateRange', daterange: daterange},
                    success: function (res) {

                        $('.customPeople').removeClass('active');
                        $('.dateRange').addClass('active');
                        if (res == 'Invalid') {
                            $('.OrderChaRT').removeClass('be-loading-active');
                            $('#dashboard-chart').html('');
                            $('#dashboard-chart').html('<div style="font-size:15px;text-align: center; margin-top: 11%;">Data  Not Available!!</div>');
                        } else {
                            $('.OrderChaRT').removeClass('be-loading-active');
                            $('.orderDaSH').removeClass('be-loading-active');
                            $('.ProductsDaSH').removeClass('be-loading-active');
                            $('.avgOrderDaSH').removeClass('be-loading-active');
                            $('.latestengagements').removeClass('be-loading-active');
                            $('.recentorders').removeClass('be-loading-active');
                            $('#chartInfoPeople').html('');
                            $('#chartInfoPeople').html(res.showcolorhtml);
                            $('#dashboard-chart').html('');
                            var resp = '';
                            var ykeyslabels = '';
                            var linecolors = '';
                            var resp = JSON.parse(res.data);
                            var ykeyslabels = JSON.parse(res.ykeyslabels);
                            var linecolors = JSON.parse(res.linecolors);
                            var area = new Morris.Area({
                                element: 'dashboard-chart',
                                resize: true,
                                data: resp,
                                xkey: 'period',
                                parseTime: false,
                                ykeys: ykeyslabels,
                                labels: ykeyslabels,
                                lineColors: linecolors,
                                xLabelAngle: 0.5,
                                lineWidth: 2,
                                hideHover: 'auto',
                            });
                        }
                    }
                });
            });
        };
    });
})(jQuery);
