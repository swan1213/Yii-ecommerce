
/*********************PEOPLE CHART JS START HERE*********************************/
(function ($) {
    $(function () {
        "use strict";
        if ($('#area-chart').hasClass('area-chart') == true) {
                $('.PeopleCHarT').addClass('be-loading-active');
                $.ajax({
                    'method': 'post',
                    'url': '/people/areachartonpeople',
                    dataType: "json",
                    data: {data: 'areachartmonth'},
                    success: function (res) {
                        $('.PeopleCHarT').removeClass('be-loading-active');
                        $('#chartInfoPeople').html('');
                        $('#chartInfoPeople').html(res.showcolorhtml);
                        // $('.customPeoples').removeClass('active');
                        $('#areachartmonth').addClass('active');
                        var resp = JSON.parse(res.data);
                        var ykeyslabels = JSON.parse(res.ykeyslabels);
                        var linecolors = JSON.parse(res.linecolors);
                        var area = new Morris.Area({
                            element: 'area-chart',
                            resize: true,
                            data: resp,
                            xkey: 'period',
                            xLabels: 'day',
                            parseTime: false,
                            //yLabelFormat:'Day 1',
                            ykeys: ykeyslabels,
                            labels: ykeyslabels,
                            lineColors: linecolors,
                            // xLabelMargin: 0,
                            lineWidth: 2,
//                            xLabelFormat: function(x) {
//                                return x.toDateString();
//                            },
                            xLabelAngle: 0.5,
                            // ykeys: ['WeChat_user','BigCommerce_user','Magento_user'],
                            // labels: ['WeChat_user', 'BigCommerce_user', 'Magento_user'],
                            // lineColors: ['#a0d0e0', '#bfa0f9', '#9ff8be'],
                            hideHover: 'auto'
                        });

                    }
                });



//            // AREA CHART
//            var area = new Morris.Area({
//                element: 'area-chart',
//                resize: true,
//                data: [
//                    {y: '2006', user: 2500, amount: 112.3},
//                    {y: '2007', user: 75000, amount: 2000.3},
//                    {y: '2008', user: 30, amount: 3000.3},
//                    {y: '2009', user: 40, amount: 8000.3},
//                    {y: '2010', user: 50000, amount: 12000.3},
////                    {y: '2011 Q2', item1: 2778, item2: 2294},
////                    {y: '2011 Q3', item1: 4912, item2: 1969},
////                    {y: '2011 Q4', item1: 3767, item2: 3597},
////                    {y: '2012 Q1', item1: 6810, item2: 1914},
////                    {y: '2012 Q2', item1: 5670, item2: 4293},
////                    {y: '2012 Q3', item1: 4820, item2: 3795},
////                    {y: '2012 Q4', item1: 15073, item2: 5967},
////                    {y: '2013 Q1', item1: 10687, item2: 4460},
////                    {y: '2013 Q2', item1: 8432, item2: 5713}
//                ],
//                xkey: 'y',
//                //xLabels:'day',
//                //yLabelFormat:'Day 1',
//                ykeys: ['user','amount'],
//                labels: ['Total User', 'Total Amount'],
//                lineColors: ['#a0d0e0', '#3c8dbc'],
//                hideHover: 'auto',
//                parseTime: false,
//            });


        }

    });
})(jQuery);
/******************************************AT BUTTON CLICK*********************************/
(function ($) {
    $(function () {
        "use strict";
        if ($('#area-chart').hasClass('area-chart') == true) {
            $('body').on('click', '.customPeoples', function () {
                $('.PeopleCHarT').addClass('be-loading-active');
                var value = $(this).attr('id');
                $.ajax({
                    'method': 'post',
                    'url': '/people/areachartonpeople',
                    dataType: "json",
                    data: {data: value},
                    success: function (res) {
                        $('.PeopleCHarT').removeClass('be-loading-active');
                        $('#chartInfoPeople').html('');
                        $('#chartInfoPeople').html(res.showcolorhtml);
                        $('#area-chart').html('');
                        $('.customPeoples').removeClass('active');
                        $('#' + value).addClass('active');
                        var resp = '';
                        var ykeyslabels = '';
                        var linecolors = '';
                        var resp = JSON.parse(res.data);
                        var ykeyslabels = JSON.parse(res.ykeyslabels);
                        var linecolors = JSON.parse(res.linecolors);
                        var area = new Morris.Area({
                            element: 'area-chart',
                            resize: true,
                            data: resp,
                            xkey: 'period',
                            // xLabels: 'month',
                            parseTime: false,
                            //yLabelFormat:'Day 1',
                            ykeys: ykeyslabels,
                            labels: ykeyslabels,
                            lineColors: linecolors,
                            lineWidth: 2,
//                              xLabelFormat: function(x) {
//                                return x.toDateString();
//                            },
                            xLabelAngle: 0.5,
                            hideHover: 'auto'
                        });
                    }
                });
            });
        }
        ;
    });
})(jQuery);


/*********************PEOPLE CHART JS END HERE*********************************/

/*********************ORDER CHART JS START HERE*********************************/

(function ($) {
    $(function () {
        "use strict";
        if ($('#order-chart').hasClass('order-chart') == true) {
                $('.OrderChaRT').addClass('be-loading-active');
                $.ajax({
                    'method': 'post',
                    'url': '/order/areachartonorders',
                    dataType: "json",
                    data: {data: 'orderchartmonth'},
                    success: function (res) {
                        $('.OrderChaRT').removeClass('be-loading-active');
                        $('#chartInfoPeople').html('');
                        $('#chartInfoPeople').html(res.showcolorhtml);
                        $('.customPeople').removeClass('active');
                        $('#orderchartmonth').addClass('active');
                        var resp = JSON.parse(res.data);
                        var ykeyslabels = JSON.parse(res.ykeyslabels);
                        var linecolors = JSON.parse(res.linecolors);
                        var area = new Morris.Area({
                            element: 'order-chart',
                            resize: true,
                            data: resp,
                            xkey: 'period',
                            // xkey: 'period',
                            xLabels: 'month',
                            parseTime: false,
                            //yLabelFormat:'Day 1',
                            ykeys: ykeyslabels,
                            labels: ykeyslabels,
                            lineColors: linecolors,
                            xLabelAngle: 0.5,
                            lineWidth: 2,
//                            xLabelFormat: function(x) {
//                                return x.getMonth();
//                            },
//                            dateFormat: function(x) {
//                                return new Date(x);
//                            },                   
                            hideHover: 'auto'
                        });

                    }
                });

        }
        ;
    });
})(jQuery);
/******************************************AT BUTTON CLICK*********************************/
(function ($) {
    $(function () {
        "use strict";
        if ($('#order-chart').hasClass('order-chart') == true) {
            $('body').on('click', '.customPeople', function () {
                $('.OrderChaRT').addClass('be-loading-active');
                var value = $(this).attr('id');
                var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                $.ajax({
                    'method': 'post',
                    'url': '/order/areachartonorders',
                    dataType: "json",
                    data: {data: value},
                    success: function (res) {
                        $('.OrderChaRT').removeClass('be-loading-active');
                        $('#chartInfoPeople').html('');
                        $('#chartInfoPeople').html(res.showcolorhtml);
                        $('#order-chart').html('');
                        $('.customPeople').removeClass('active');
                        $('#' + value).addClass('active');
                        var resp = '';
                        var ykeyslabels = '';
                        var linecolors = '';
                        var resp = JSON.parse(res.data);
                        var ykeyslabels = JSON.parse(res.ykeyslabels);
                        var linecolors = JSON.parse(res.linecolors);
                        var area = new Morris.Area({
                            element: 'order-chart',
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
                });
            });
        }
        ;
    });
})(jQuery);


/******************************AT BUTTON CLICK ON PIE CHART ON PRODUCT PAGE END  HERE***********************************************************/


/******************************NEW DASHBOARD GRAPH START HERE ***********************************************************/

//line chart 2 and 1


//on  button CLICK on orders chart on new dashboard 
(function ($) {
    $(function () {
        "use strict";
        if ($('#line-chart1').hasClass('line-chart1') == true) {
            getAjaxAllGraphs('month', 'month', '');
            $('body').on('click', '.newdashchart', function () {
                $('.Loader_Orders').addClass('be-loading-active');
                $('.Loader_Reven').addClass('be-loading-active');
                $('.Loader_Rev_Ch').addClass('be-loading-active');
                $('.Loader_Rev_Region').addClass('be-loading-active');
                $('.Loader_Avg_order_val').addClass('be-loading-active');
                $('.Loader_Cust_acq').addClass('be-loading-active');
                var value = $(this).attr('data');
                var val = $(this).attr('class').split(' ').join('.');
                var daterange = $('.daterange').val();
                //get All ajax graphs of dashboard
                getAjaxAllGraphs(value, val, daterange);
            });
        }
        ;
    });

    function  getAjaxAllGraphs(value, val, daterange) {
        //line chart 2
        $.ajax({
            method: 'post',
            url: '/site/newdashordersgraph',
            dataType: "json",
            data: {data: value, daterange: daterange},
            success: function (res) {
                $('.Loader_Orders').removeClass('be-loading-active');
                $('#line-chart2').html('');
                $('#updownorders').html('');
                $('.newdashchart').removeClass('active');
                $('.' + val).addClass('active');
                var resp = JSON.parse(res.data);
                var ykeyslabels = JSON.parse(res.ykeyslabels);
                var linecolors = JSON.parse(res.linecolors);
                $('#OrderchartInfo').html(res.showcolorhtml);
                if (res.up_down < 0) {
                    $('#updownorders').html('<span style="padding:0px;" class="icon-container"><span style="background-color:#34a853;" class="icon"><span class="mdi mdi-chevron-down"></span></span></span>' + res.up_down + '% (from same ' + value + ' last year)');

                } else if (res.up_down == 0) {
                    $('#updownorders').html('');
                    $('#updownorders').html(res.up_down + '% (from same ' + value + ' last year)');

                } else {
                    $('#updownorders').html('<span style="padding:0px;" class="icon-container"><span style="background-color:#34a853;" class="icon"><span class="mdi mdi-chevron-up"></span></span></span>' + res.up_down + '% (from same ' + value + ' last year)');

                }
                var line = new Morris.Line({
                    element: 'line-chart2',
                    resize: true,
                    data: resp,
                    xkey: 'period',
                    parseTime: false,
//                            ykeys: ['2017', '2016'],
//                             ykeys: ['item1', 'item2'],
                    ykeys: ykeyslabels,
                    labels: ykeyslabels,
//                            labels: ['2017', '2016'],
                    lineColors: linecolors,
//                            lineColors: ['#3c8dbc', 'blue'],
                    //events: ['2011 Q1', '2011 Q2', '2011 Q3', '2011 Q4', '2012 Q1', '2012 Q2', '2012 Q3', '2012 Q4', '2013 Q1', '2013 Q2'],
                    //eventLineColors: ['#D3D3D3'],
                    lineWidth: 1,
                    //xLabelMargin: 1,
                    //gridTextSize: 100,
                    //gridTextColor: '#000',
                    //axes: false,
                    // grid: true,
                    //gridTextWeight: 'bold',
                    hideHover: 'auto'
                });
            }
        });
        //line chart 1
        $.ajax({
            method: 'post',
            url: '/site/newdashrevenuegraph',
            dataType: "json",
            data: {data: value, daterange: daterange},
            success: function (res) {
                $('.Loader_Reven').removeClass('be-loading-active');
                $('#line-chart1').html('');
                $('#updownrevenue').html('');
                var resp = JSON.parse(res.data);
                var ykeyslabels = JSON.parse(res.ykeyslabels);
                var linecolors = JSON.parse(res.linecolors);
                $('#revchartInfo').html(res.showcolorhtml);
                if (res.up_down < 0) {
                    $('#updownrevenue').html('<span style="padding:0px;" class="icon-container"><span style="background-color:#34a853;" class="icon"><span class="mdi mdi-chevron-down"></span></span></span>' + res.up_down + '% (from same ' + value + ' last year)');
                } else if (res.up_down == 0) {
                    $('#updownrevenue').html('');
                    $('#updownrevenue').html(res.up_down + '% (from same ' + value + ' last year)');
                } else {
                    $('#updownrevenue').html('<span style="padding:0px;" class="icon-container"><span style="background-color:#34a853;" class="icon"><span class="mdi mdi-chevron-up"></span></span></span>' + res.up_down + '% (from same ' + value + ' last year)');

                }
                var line = new Morris.Line({
                    element: 'line-chart1',
                    resize: true,
                    data: resp,
//                            data: [
//                                {y: '2011 Q1', item1: 2666, item2: 200},
//                                {y: '2011 Q2', item1: 2778, item2: 300},
//                                {y: '2011 Q3', item1: 4912, item2: 400},
//                                {y: '2011 Q4', item1: 3767, item2: 500},
//                                {y: '2012 Q1', item1: 6810, item2: 600},
//                                {y: '2012 Q2', item1: 5670, item2: 700},
//                                {y: '2012 Q3', item1: 4820, item2: 800},
//                                {y: '2012 Q4', item1: 15073, item2: 900},
//                                {y: '2013 Q1', item1: 10687, item2: 1000},
//                                {y: '2013 Q2', item1: 8432, item2: 1100}
//                            ],
//                            xkey: 'y',
                    xkey: 'period',
                    parseTime: false,
//                            ykeys: ['2017', '2016'],
//                             ykeys: ['item1', 'item2'],
                    ykeys: ykeyslabels,
                    labels: ykeyslabels,
//                            labels: ['2017', '2016'],
                    lineColors: linecolors,
//                            lineColors: ['#3c8dbc', 'blue'],
                    //events: ['2011 Q1', '2011 Q2', '2011 Q3', '2011 Q4', '2012 Q1', '2012 Q2', '2012 Q3', '2012 Q4', '2013 Q1', '2013 Q2'],
                    //eventLineColors: ['#D3D3D3'],
                    lineWidth: 1,
                    //xLabelMargin: 1,
                    //gridTextSize: 100,
                    //gridTextColor: '#000',
                    //axes: false,
                    // grid: true,
                    //gridTextWeight: 'bold',
                    hideHover: 'auto'
                });
            }
        });
        //pie chart 1
        $.ajax({
            method: 'post',
            url: '/site/newdashrevenuebychannel',
            dataType: "json",
            data: {data: value, daterange: daterange},
            success: function (res) {
                if (res == 'invalid') {
                    $('.Loader_Rev_Ch').removeClass('be-loading-active');
                    $('#custompanel').html('');
                    $('#custompanel').html('<p>Data Not Available for ' + value + '!</p>');
                    $('#custompanel').css('padding', '20% 0 20% 42%');
                } else {
                    $('#custompanel').css('padding', '0px');
                    $('#custompanel').css('padding', '15px');
                    $('.Loader_Rev_Ch').removeClass('be-loading-active');
                    var resp = JSON.parse(res.data);
                    var resplabel = JSON.parse(res.label);
                    var respcolor = JSON.parse(res.color);
                    var color1 = 'red';
                    var color2 = 'blue';
                    var color3 = 'green';
                    $('#custompanel').html('');
                    $('#custompanel').html('<canvas id="pie-chart1" class="pie-chart1" height="180"></canvas>');
                    var ctx = document.getElementById("pie-chart1");
                    var data = {
                        //labels: ["WeChat","Square","Lazada Malaysia","BigCommerce"],
                        labels: resplabel,
                        datasets: [
                            {
                                //data: [26,20,1,34],
                                data: resp,
                                backgroundColor: respcolor
                                ,
                                hoverBackgroundColor: respcolor
                            }]
                    };
                    var pie = new Chart(ctx, {
                        type: 'pie',
                        // resize: true,
                        //onResize:true,
                        responsive: true,
                        options: {
                            responsive: true,
                        },
                        data: data
                                // data: data
                    });
                }
            }
        });
        //pie chart 2
        $.ajax({
            method: 'post',
            url: '/site/newdashrevenuebyregion',
            dataType: "json",
            data: {data: value, daterange: daterange},
            success: function (res) {
                if (res == 'invalid') {
                    $('.Loader_Rev_Region').removeClass('be-loading-active');
                    $('#custompanel2').html('');
                    $('#custompanel2').html('<p>Data Not Available for ' + value + '!</p>');
                    $('#custompanel2').css('padding', '20% 0 20% 42%');
                } else {
                    $('#custompanel2').css('padding', '0px');
                    $('#custompanel2').css('padding', '15px');
                    $('.Loader_Rev_Region').removeClass('be-loading-active');
                    var resp = JSON.parse(res.data);
                    var resplabel = JSON.parse(res.label);
                    var respcolor = JSON.parse(res.color);
                    var color1 = 'red';
                    var color2 = 'blue';
                    var color3 = 'green';
                    $('#custompanel2').html('');
                    $('#custompanel2').html('<canvas id="pie-chart2" class="pie-chart1" height="180"></canvas>');
                    var ctx = document.getElementById("pie-chart2");
                    var data = {
                        //labels: ["WeChat","Square","Lazada Malaysia","BigCommerce"],
//                        labels: resplabel,
                        datasets: [
                            {
                                //data: [26,20,1,34],
                                data: resp,
                                backgroundColor: respcolor
                                ,
                                hoverBackgroundColor: respcolor
                            }], labels: resplabel,
                    };
                    var pie = new Chart(ctx, {
                        type: 'pie',
//                         resize: true,
                        //onResize:true,
                        responsive: true,
                        options: {
                            responsive: true,
                        },
                        data: data
                                // data: data
                    });
                }
            }
        });
        //bar-chart 2
        $.ajax({
            method: 'post',
            url: '/site/newdashavgorderval',
            //dataType: "json",
            data: {data: value, daterange: daterange},
            success: function (res) {
                console.log(res);
                if (res == 'invalid') {
                    console.log('in');
//                    $('.Loader_Avg_order_val').removeClass('be-loading-active');
//                    $('.Loader_Cust_acq').removeClass('be-loading-active')
                    $('#custombargraph5').html('');
                    $('#custombargraph5').html('<p>Data Not Available for ' + value + '!</p>');
                    // $('#custombargraph5').css('padding', '20% 0 20% 42%');
                } else {
//                    $('#custombargraph2').css('padding', 'opx');
//                    $('#custombargraph2').css('padding', '15px');
                    $('.Loader_Avg_order_val').removeClass('be-loading-active');
                    $('.Loader_Cust_acq').removeClass('be-loading-active');
                    $('#custombargraph5').html('');
                    $('#custombargraph5').html('<div id="bar-chart5" style="height: 340px;"></div>');
                    var resp = JSON.parse(res);
                    //  var resplabel = JSON.parse(res.label);
                    Morris.Bar({
                        element: 'bar-chart5',
                        resize: true,
                        xkey: 'device',
                        data: resp,
                        // data: [
                        // {device: 'iPhone', geekbench: 136},
                        // {device: 'iPhone 3G', geekbench: 137},
                        // {device: 'iPhone 3GS', geekbench: 275},
                        // {device: 'iPhone 4', geekbench: 380},
                        // {device: 'iPhone 4S', geekbench: 655},
                        // {device: 'iPhone 5', geekbench: 1571}
                        // ],
                        ykeys: ['data'],
                        labels: ['data'],
                        barColors: ['red'],
                        barRatio: 0.4,
                        xLabelAngle: 35,
                        hideHover: 'auto'
                    });

                }
            }

        });

        //bar-chart 1 CustomerAcquired graph

        $.ajax({
            url: '/lazada/graph1',
            type: 'post',
            dataType: 'json',
            data: {type: value, daterange: daterange},
            success: function (response) {
                var windowWidth = $(window).width();

                $('#customer_acquisition').html('');


                $('.Loader_Cust_acq').removeClass('be-loading-active');
//                $('#updownrevenue').html('');
                var showcolorhtml = '<li><span data-color="main-chart-color" class="BigCommerce" style="background-color: #74ACDF;"></span><span style="position:relative;top:-8px;width:22px;">New</span></li><li><span data-color="main-chart-color" class="BigCommerce" style="background-color: #00578a";></span><span style="position:relative;top:-8px;width:22px;">Repeat</span></li>';
                $('#customer_chart_info').html(showcolorhtml);

                console.log(response);
                var customers_data = response.customers_data;
                var up_down = response.data_up_down;
                if (up_down < 0) {
                    $('#updown_customer_acquisition').html('<span style="padding:0px;" class="icon-container"><span style="background-color:#34a853;" class="icon"><span class="mdi mdi-chevron-down"></span></span></span>' + up_down + '% (from same ' + value + ' last year)');
                } else if (up_down == 0) {
                    $('#updown_customer_acquisition').html('');
                    $('#updown_customer_acquisition').html(up_down + '% (from same ' + value + ' last year)');
                } else {
                    $('#updown_customer_acquisition').html('<span style="padding:0px;" class="icon-container"><span style="background-color:#34a853;" class="icon"><span class="mdi mdi-chevron-up"></span></span></span>' + up_down + '% (from same ' + value + ' last year)');

                }
                Morris.Bar({
                    element: 'customer_acquisition',
                    resize: true,
                    data: customers_data,
                    xkey: 'date',
                    parseTime: false,
                    ykeys: ['new_customer_count', 'repeat_customer_count'],
                    labels: ['New Customers', 'Repeated Customers'],
                    barColors: ['#74ACDF', '#00578a'],
                    barRatio: 0.4,
                    xLabelAngle: 35,
                    hideHover: 'auto'
                });

            }
        });

    }

})(jQuery);






//////////////////////////////BAR CHART FLOT JS//////////////////////////////////////////////////////////////////////////


//Bar Chart 1


/////////////////////////////////////BAR CHART CHART JS//////////////////////////////////////////////////////////
(function ($) {
    $(function () {
        "use strict";
        var d = new Date();
        var month = d.getMonth() + 1;
        var day = d.getDate();

        var maxdate = (('' + month).length < 2 ? '0' : '') + month + '/' + (('' + day).length < 2 ? '0' : '') + day + '/' + d.getFullYear();

        console.log(maxdate);
        setTimeout(function () {
            $('#date_range_graph').daterangepicker({
                "maxDate": maxdate
            }, function (start, end, label) {
                $('.showdaterange_elliot').removeClass('hide');
                console.log("New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')");
                $(".showdaterange_elliot").html('<div class="pull-right testing_aaa line_space"><span class="date_title_aa"> End date</span><span>' + end.format('MM/DD/YY') + '</span></div>' + " " + '<div class="pull-right testing_aaa"><span class="date_title_aa">Start date </span> <span>' + start.format('MM/DD/YY') + '</span></div>');



            });

            $('.daterangeBoth').click(function () {
                $("#date_range_graph").val("");
                $('#date_range_graph').data('daterangepicker').setStartDate(maxdate);
//					$('input[name="daterangepicker_start"]').val(maxdate);
                $('#date_range_graph').data('daterangepicker').setEndDate(maxdate);
                $(".daterange").trigger('click');
                $('.applyBtn.btn.btn-sm.btn-success').attr("data", "dateRange");
                $('.applyBtn.btn.btn-sm.btn-success').addClass('newdashchartNew');
            });

        }, 3000);
    });
})(jQuery);


(function ($) {
    $(function () {
        "use strict";
        if ($('#pie-chart2').hasClass('pie-chart2') == true) {
            $.ajax({
                method: 'post',
                url: '/site/newdashavgordervall',
                // dataType: "json",
                data: {data: 'month'},
                success: function (res) {
                    var resp = JSON.parse(res);
                    Morris.Bar({
                        element: 'bar-chart5',
                        resize: true,
                        xkey: 'device',
                        data: resp,
                        // data: [
                        // {device: 'iPhone', geekbench: 136},
                        // {device: 'iPhone 3G', geekbench: 137},
                        // {device: 'iPhone 3GS', geekbench: 275},
                        // {device: 'iPhone 4', geekbench: 380},
                        // {device: 'iPhone 4S', geekbench: 655},
                        // {device: 'iPhone 5', geekbench: 1571}
                        // ],
                        ykeys: ['data'],
                        labels: ['data'],
                        barColors: ['red'],
                        barRatio: 0.4,
                        xLabelAngle: 35,
                        hideHover: 'auto'
                    });
                }

            });

        }
        ;
    });
})(jQuery);



