<?php
/* @var $this yii\web\View */


use common\models\Product;
use common\models\Order;
use common\models\UserConnection;
use common\models\User;



use common\models\Channels;

use common\models\OrderChannel;
use common\models\OrdersProducts;
use common\models\StoresConnection;
use common\models\Stores;
use common\models\ChannelConnection;
use common\models\CronTasks;
use common\models\CustomerUser;

use common\models\ProductChannel;
use common\models\UserPermission;
use yii\web\Session;
use yii\web\View;

$this->title = 'Dashboard | Elliot';
$store_img = '';
$user_id = Yii::$app->user->identity->id;
if (Yii::$app->user->identity->level == User::USER_LEVEL_MERCHANT_USER) {
    $permission_id = Yii::$app->user->identity->permission_id;
    $user_permission = UserPermission::find()->where(['id' => $permission_id])->one();
    if (!empty($user_permission)) {
        $channel_ids = $user_permission->channel_permission;
        $items = explode(", ", $channel_ids);
        if (sizeof($items) > 0) {
            $menu_channels = array();
            $userConnections = UserConnection::find()
                ->where(['user_id' => Yii::$app->user->identity->parent_id])
                ->andWhere(['and',
                    ['in', 'connection_id', $items],])->all();
        } else
            $userConnections = array();
    }
}
else {
    $userConnections = UserConnection::find(['user_id' => $user_id])->available()->all();
}


if (Yii::$app->user->identity->level == User::USER_LEVEL_SUPERADMIN) {
    echo "<p>Admin Dashboards</p>";
} else {
    ?>
    <?php if (empty($userConnections)): ?>
        <div class="row be-connections store_logo100px">
            <div class="col-md-12">
                <div class="widget widget-fullwidth be-loading">
                    <div class="widget-head">

                        <span class="title">Connect Your Store</span>
                        <div class="list">
                            <p class="connectstore_line">Choose from the eCommerce platforms below to easily integrate your existing catalog and storefront data.</p>
                            <div class="content store_listing_container">

                                <?php
                                $connections_index = 0;
                                $enabled_Stores_count = count($enabledStores) - 1;
                                foreach ($enabledStores as $each_Stores){
                                    $connection_Img = $each_Stores->image_url;
                                    $connection_Name = $each_Stores->name;
                                    $connectionId = $each_Stores->id;
                                    $rowDivFlag = false;
                                    ?>
                                    <?php
                                    if ($connections_index % 4 == 0){
                                        echo "<div class='row'>";
                                        $rowDivFlag = true;
                                    }
                                    ?>
                                    <div class="col-sm-3 text-center store_listing_container_sub">
                                        <a href="/stores/<?php echo strtolower($connection_Name);?>?id=<?php echo $connectionId;?>" class="connection-item">
                                            <img src="<?php echo $connection_Img?>" alt="<?php echo $connection_Name;?>">
                                            <p><?php echo $connection_Name?></p>
                                        </a>
                                    </div>
                                    <?php
                                    if ($connections_index % 4 == 3 || $connections_index == $enabled_Stores_count){
                                        echo "</div>";
                                    }
                                    ?>
                                    <?php
                                    $connections_index ++;
                                }

                                ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!--END if channel and store is not connected Only show Stores In Index Page!-->
        <?php
        /*         * for showing loader for first time when the data is importing* */
        $curr_userid = Yii::$app->user->identity->id;
        $connection = \Yii::$app->db;
        $check_store = $connection->createCommand("SELECT COUNT(connection.id) FROM user_connection LEFT JOIN connection ON user_connection.connection_id = connection.id WHERE type_id=1 AND user_id='" . $curr_userid . "'");
        $check_store_query = $check_store->queryAll();
        $store_count = @$check_store_query[0]['COUNT(connection.id)'];

        $check_channel = $connection->createCommand("SELECT COUNT(connection.id) FROM user_connection LEFT JOIN connection ON user_connection.connection_id = connection.id WHERE type_id=2 AND user_id='" . $curr_userid . "'");
        $check_channel_query = $check_channel->queryAll();
        $channel_count = @$check_channel_query[0]['COUNT(connection.id)'];


        $store_import_status = $connection->createCommand("SELECT import_status FROM user_connection LEFT JOIN connection ON user_connection.connection_id = connection.id WHERE type_id=1 AND user_id='" . $curr_userid . "'");
        $store_import = $store_import_status->queryAll();
        $store_status = @$store_import[0]['import_status'];

        $channel_import_status = $connection->createCommand("SELECT import_status, connection.id FROM user_connection LEFT JOIN connection ON user_connection.connection_id = connection.id WHERE type_id=2 AND user_id='" . $curr_userid . "'");
        $channel_import = $channel_import_status->queryAll();
        $channel_status = @$channel_import[0]['import_status'];

        ?>
        <!--END if channel and store is connected show Index Page!-->
        <?php $loader_active = ''; ?>
        <?php
        if ($store_count == 1 && $channel_count == 0) {
            if ($store_status == '') {
                $loader_active = 'be-loading-active';
            }
        } elseif ($store_count == 0 && $channel_count == 1) {
            if ($channel_status == '') {
                $loader_active = 'be-loading-active';
            }
        }
        ?>
        <div class="be-loading <?php echo $loader_active; ?>" style="z-index:999;">

        <!--For Date range hidden input!-->
        <div class="row">
            <div class="col-md-8"></div>
            <div class="col-md-4 dashboard_daterange">
                <div class="form-group">
                    <div class="col-md-12">
                        <!-- <input style="opacity:0;"type="text" name="daterange" value="01/01/2017 - 01/31/2017" class="form-control daterange">-->
                        <input id="date_range_graph" style="opacity:0;"type="text" name="daterange" value="<?php echo date('m/d/Y') . '-' . date('m/d/Y') ?>"  class="form-control daterange">
                    </div>
                </div>
            </div>

        </div>
        <!--*********************************DASHBOARD MAIN CHART START HERE********************************************************************************-->

        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default OrderChaRT <?php
                if (empty($loader_active)) {
                    echo 'be-loading';
                }
                ?>" style="z-index:999;">
                    <div class="panel-heading panel-heading-divider">
                        <div class="widget-head">
                            <div class="tools">
                                <div class="dropdown responsive_dropdown"><span data-toggle="dropdown" class="icon mdi mdi-more-vert visible-xs-inline-block dropdown-toggle" aria-expanded="false"></span>
                                    <ul role="menu" class="dropdown-menu">
                                        <li><a  id="dashboardchartweekmob" class="btn btn-default customPeople  "  style="cursor:pointer">Week</a></li>
                                        <li><a  id="dashboardchartmonthmob" class="btn btn-default customPeople " style="cursor:pointer">Month</a></li>
                                        <li><a  id="dashboardchartquartermob" class="btn btn-default customPeople " style="cursor:pointer">Quarter</a></li>
                                        <li><a  id="dashboardchartyearmob" class="btn btn-default customPeople " style="cursor:pointer">Year</a></li>
                                        <li><a  id="dashboardchartannualmob" class="btn btn-default customPeople " style="cursor:pointer">Annual</a></li>
                                        <li class="divider"></li>
                                        <li><a  id="dashboardcharttodaymob" class="btn btn-default customPeople  "  style="cursor:pointer">Today</a></li>
                                        <li><a   id="dateRange"  class="btn btn-default daterangeBoth customPeople dateRange" style="cursor:pointer">Date Range</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="btn-group btn-space pull-right">
                                <button class="btn btn-default show-selected-channel-dashboard" type="button">Filter by Channel</button>
                                <button class="btn btn-default dropdown-toggle" data-toggle="dropdown" type="button" aria-expanded="true"><span class="mdi mdi-chevron-down"></span><span class="sr-only">Toggle Dropdown</span></button>
                                <ul class="dropdown-menu  SortChannelList" role="menu">
                                    <li  attr-value=""><a>View All</a></li>
                                    <?php
                                    if (isset($userConnections) and ! empty($userConnections)) {
                                        foreach ($userConnections as $key => $each_detail_data) {
                                            $connectionId = $each_detail_data->id;
                                            $type = $each_detail_data->getConnectionType();
                                            $connectionName = $each_detail_data->getPublicName();
                                            ?>
                                            <li attr-value="<?php echo $connectionId; ?>"  attr-type="<?php echo $type ?>" class=""><a><?php echo $connectionName; ?></a></li>
                                            <?php
                                        }
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="button-toolbar hidden-xs">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default customPeople " id="dashboardchartweek">Week</button>
                                    <button type="button" class="btn btn-default customPeople " id="dashboardchartmonth">Month</button>
                                    <button type="button" class="btn btn-default customPeople " id="dashboardchartquarter">Quarter</button>
                                    <button type="button" class="btn btn-default customPeople " id="dashboardchartyear">Year</button>
                                    <button type="button" class="btn btn-default customPeople " id="dashboardchartannual">Annual</button>
                                    <input name="" value="Week" id="hidden_graph" type="hidden">
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default customPeople " id="dashboardcharttoday">Today</button>
                                    <button id="dateRange" type="button" class="btn btn-default dateRange customPeople daterangeBoth ">Date Range</button>
                                </div>
                            </div>
                            <span class="title">Global Performance - Orders</span>
                        </div>
                        <div class="col-sm-12 custom_class showdaterange_elliot hide">

                        </div>
                        <div class="be-spinner" style="width:100%; text-align: center; right:auto">
                            <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">

                                <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                            </svg>
                            <?php if (isset($loader_active) and ! empty($loader_active) and $loader_active == 'be-loading-active') {
                                ?><span style="display:block; padding-top:30px;">Your data is importing, you will be emailed when it is complete.</span><?php } else {
                                ?>
                                <span style="display:block; padding-top:30px;">Your data is loading.</span>
                            <?php }
                            ?>
                        </div>
                        <span class="panel-subtitle space">
                                <ul id="chartInfoPeople" class="chart-legend-horizontal"></ul>
                            </span>
                    </div>
                    <div id="padding-panel-body" class="panel-body">
                        <div class="dashboard-chart" id="dashboard-chart" style="height: 250px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <!--NEW DASHBOARD FOUR GRAPHS START HERE-->
        <div class="row"  style="display:block;">
            <div class="col-xs-12 col-md-6">
                <div class="widget widget-tile be-loading fourgraph1">
                    <div id="newsparknm1" class="chart sparkline newsparknm1"></div>
                    <?php if (empty($loader_active)) { ?>
                        <div class="be-spinner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66 66" height="40px" width="40px">

                                <circle class="circle" r="25" cy="33" cx="33" stroke-linecap="round" stroke-width="4" fill="none"/>
                            </svg>
                        </div>
                    <?php } ?>
                    <div class="data-info">
                        <div class="desc">Revenue Earned</div>
                        <div class="value"><span class="indicator indicator-equal mdi mdi-chevron-right"></span>
                            <span class="numberneworders"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-6">
                <div class="widget widget-tile be-loading fourgraph2">
                    <div id="newsparknm2" class="chart sparkline newsparknm2"></div>
                    <?php if (empty($loader_active)) { ?>
                        <div class="be-spinner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66 66" height="40px" width="40px">

                                <circle class="circle" r="25" cy="33" cx="33" stroke-linecap="round" stroke-width="4" fill="none"/>
                            </svg>
                        </div>
                    <?php } ?>
                    <div class="data-info">
                        <div class="desc sales">Monthly Sales</div>
                        <div class="value"><span class="indicator indicator-positive mdi mdi-chevron-up"></span>
                            <span data-toggle="counter" data-end="" data-suffix="%" class="numbersales"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row"  style="display:block;">
            <div class="col-xs-12 col-md-6">
                <div class="widget widget-tile be-loading fourgraph4">
                    <div id="newsparknm3" class="chart sparkline newsparknm3"></div>
                    <?php if (empty($loader_active)) { ?>
                        <div class="be-spinner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66 66" height="40px" width="40px">

                                <circle class="circle" r="25" cy="33" cx="33" stroke-linecap="round" stroke-width="4" fill="none"/>
                            </svg>
                        </div>
                    <?php } ?>
                    <div class="data-info">
                        <div class="desc">Products Sold</div>
                        <div class="value"><span class="indicator indicator-positive mdi mdi-chevron-up"></span><span class="numberproductsold"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-6">
                <div class="widget widget-tile be-loading fourgraph4">
                    <div id="newsparknm4" class="chart sparkline newsparknm4"></div>
                    <?php if (empty($loader_active)) { ?>
                        <div class="be-spinner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66 66" height="40px" width="40px">

                                <circle class="circle" r="25" cy="33" cx="33" stroke-linecap="round" stroke-width="4" fill="none"/>
                            </svg>
                        </div>
                    <?php } ?>
                    <div class="data-info">
                        <div class="desc">Avg. Order Value</div>
                        <div class="value"><span class="indicator indicator-negative mdi mdi-chevron-down"></span>
                            <span data-toggle="counter" data-end="" class="numberavgorder"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default panel-table be-loading recentorders">
                    <div class="panel-heading">
                        <a href="/order"><button type="button" class="btn btn-default btn-primary pull-right">View All</button></a>
                        <div class="title">Recent Orders</div>

                    </div>
                    <?php if (empty($loader_active)) { ?>
                        <div class="be-spinner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66 66" height="40px" width="40px">
                                <circle class="circle" r="25" cy="33" cx="33" stroke-linecap="round" stroke-width="4" fill="none"/>
                            </svg>
                        </div>
                    <?php } ?>
                    <?php if (empty($userConnections)) : ?>
                        <center><button class="btn btn-space btn-primary" id="see">Connect Your Store to see Data</button></center>
                    <?php else : ?>
                        <div class="panel-body table-responsive ">
                            <table id="recent_orders_dashboard_ajax" class="table-borderless table table-striped table-hover table-fw-widget dataTable">
                                <thead>
                                <tr>

                                    <th>Customer Name</th>
                                    <th class="number12" style="text-align:left;">Amount</th>
                                    <!--  <th style="width:20%;">Date</th> -->
                                    <th >Status</th>
                                    <th >View Order</th>
                                </tr>
                                </thead>
                                <tbody class="no-border-x recentOrderData">
                                </tbody>
                            </table>
                        </div>
                    <?php
                    endif;
                    ?>

                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-default panel-table be-loading latestengagements">
                    <div class="panel-heading">
                        <div class="title">Latest Engagements</div>
                    </div>
                    <?php if (empty($loader_active)) { ?>
                        <div class="be-spinner">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66 66" height="40px" width="40px">

                                <circle class="circle" r="25" cy="33" cx="33" stroke-linecap="round" stroke-width="4" fill="none"/>
                            </svg>
                        </div>
                    <?php } ?>
                    <div class="panel-body table-responsive">
                        <table id="latest_engagement_dashboard" class="table-borderless table table-striped table-hover table-fw-widget dataTable latest-engagements">
                            <thead>
                            <tr>
                                <th style="width:37%;">Name</th>
                                <th style="width:36%;">Engagement Type</th>
                                <th>Channel</th>
                            </tr>
                            </thead>
                            <tbody class="renderlatestengagement">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


        <!--*********************************Carousels********************************************************************************************-->

        <div class="row wizard-row panel newdashboard_container">
            <div class="col-md-12 fuelux be-loading topProductsByRevenue">
                <?php if (empty($loader_active)) { ?>
                    <div class="be-spinner">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66 66" height="40px" width="40px">

                            <circle class="circle" r="25" cy="33" cx="33" stroke-linecap="round" stroke-width="4" fill="none"/>
                        </svg>
                    </div>
                <?php } ?>
                <div id="revenue" class="carouseller">
                    <div class="widget-head panel-heading-divider ">
                        <div class="tools">

                            <div class="col-md-4">
                                <a href="/product" class="btn btn-space btn-primary">View All</a>
                            </div>
                        </div>
                        <span class="title">Top Products</span>
                        <span class="description">By Revenue</span>
                    </div>
                    <div class="revenueProducts">
                        <?php if (isset($products_by_revenue_count) and ! empty($products_by_revenue_count)) {
                            ?>
                            <!--                    <a href="javascript:void(0)" class="carouseller__left">‹</a>-->
                            <?php if ($products_by_revenue_count > 4) { ?>
                                <a class="icon-container carouseller__left carouseller__left1" href="javascript:void(0)">
                                    <span class="icon"><span class="mdi mdi-arrow-left"></span></span>
                                </a>
                            <?php } ?>
                            <div class="carouseller__wrap">
                                <div class="carouseller__list ">
                                    <?php
                                    if (isset($products_by_revenue) and ! empty($products_by_revenue)) {
                                        foreach ($products_by_revenue as $key => $single) {
                                            if (empty($single['product']))
                                                continue;
                                            ?>
                                            <div class="car__3" data-value="<?php echo $single['product_price'] ?>">
                                                <?php
                                                if (isset($single['product']['productImages']) and ! empty($single['product']['productImages']) and isset($single['product']['productImages'][0]) and ! empty($single['product']['productImages'][0])) {
                                                    $src = $single['product']['productImages'][0]['link'];
                                                } else {
                                                    $src = '/img/elliot-logo.svg';
                                                }
                                                ?>    <a href="/product/view?id=<?php echo $single['product']['id']; ?>" ><img src="<?php echo $src ?>" class="product-img-slide"></a>
                                                <?php ?>
                                                <br>
                                                <h5><a href="/product/view?id=<?php echo $single['product']['id'] ?>"><?php echo @$single['product']['product_name'] ?></a></h5>

                                                <h6><?php echo $symbol . ' ' . number_format(@$single['product']['price'], 2) ?></h6>

                                            </div>

                                            <?php
                                        }
                                    }
                                    ?>

                                </div>
                            </div>
                            <!--                        <a href="javascript:void(0)" class="carouseller__right">›</a>-->
                            <?php if ($products_by_revenue_count > 4) { ?>
                                <a class="icon-container carouseller__right carouseller__right1" href="javascript:void(0)">
                                    <span class="icon"><span class="mdi mdi-arrow-right"></span></span>
                                </a>
                            <?php } ?>
                        <?php } else {
                            ?> <h3 style="text-align: center;">No data available in table.</h3><?php
                        }
                        ?>

                    </div>
                </div>
            </div>
        </div>
        <div class="row wizard-row panel newdashboard_container">
            <div class="col-md-12 fuelux be-loading topProductsByVolume">
                <?php if (empty($loader_active)) { ?>
                    <div class="be-spinner">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66 66" height="40px" width="40px">

                            <circle class="circle" r="25" cy="33" cx="33" stroke-linecap="round" stroke-width="4" fill="none"/>
                        </svg>
                    </div>
                <?php } ?>
                <div id="volume" class="carouseller">
                    <div class="widget-head panel-heading-divider ">
                        <div class="tools">

                            <div class="col-md-4">
                                <a href="/product" class="btn btn-space btn-primary">View All</a>
                            </div>
                        </div>
                        <span class="title">Top Products</span>
                        <span class="description">By Volume</span>
                    </div>
                    <div class="volumeProducts">
                        <?php
                        if (isset($products_by_volume_count) and ! empty($products_by_volume_count)) {
                            if ($products_by_volume_count > 4) {
                                ?> <a href="javascript:void(0)" class="icon-container carouseller__left carouseller__left1">
                                    <span class="icon"><span class="mdi mdi-arrow-left"></span></span>
                                </a><?php }
                            ?>

                            <div class="carouseller__wrap">
                                <div class="carouseller__list">
                                    <?php
                                    if (isset($products_by_volume) and ! empty($products_by_volume)) {
                                        foreach ($products_by_volume as $key => $single) {
                                            if (empty($single['product']))
                                                continue;
                                            ?>
                                            <div class="car__3" data-value="<?php echo $single['qty'] ?>">
                                                <?php
                                                if (isset($single['product']['productImages']) and ! empty($single['product']['productImages']) and isset($single['product']['productImages'][0]) and ! empty($single['product']['productImages'][0])) {
                                                    $src = $single['product']['productImages'][0]['link'];
                                                } else {
                                                    $src = '/img/elliot-logo.svg';
                                                }
                                                ?> <a href="/product/view?id=<?php echo $single['product']['id']; ?>" ><img src="<?php echo $src ?>" class="product-img-slide"></a>
                                                <?php ?>
                                                <br>
                                                <h5><a href="/product/view?id=<?php echo $single['product']['id'] ?>"><?php echo @$single['product']['product_name'] ?></a></h5>

                                                <h6><?php echo $symbol . ' ' . number_format(@$single['product']['price'], 2) ?></h6>

                                            </div>
                                    <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php
                            if ($products_by_volume_count > 4) {
                                ?>  <a href="javascript:void(0)" class="icon-container carouseller__right carouseller__right1">
                                    <span class="icon"><span class="mdi mdi-arrow-right"></span></span>
                                </a>         <?php
                            }
                        } else {
                            ?>  <h3 style="text-align: center;">No data available in table.</h3><?php }
                        ?>
                    </div>
                </div>
            </div>
        </div>

    <?php
    $insertJs = <<< SCRIPT
            $(function () {
                $('#revenue').carouseller();
                $('#volume').carouseller();
                $('.CountriesLists li').on('click', function () {

                    var elementObj = $(this);
                    country_code = elementObj.attr('attr-value');
                    id = elementObj.attr('attr-id');
                    selected_country = elementObj.text();

                    country_code_volume = '';
                    country_code_revenue = '';
                    if (id == 'volumeSelect') {
                        $('.show-selected-country-volume').html(selected_country);
                        country_code_volume = country_code;
                        divClass = 'volumeProducts';

                    }
                    if (id == 'revenueSelect') {
                        $('.show-selected-country-revenue').html(selected_country);
                        country_code_revenue = country_code;
                        divClass = 'revenueProducts';

                    }
                    $.ajax({
                        type: 'Post',
                        //                dataType: 'json',
                        data: {country_code_revenue: country_code_revenue, country_code_volume: country_code_volume, id: id},
                        url: '/lazada/get-filtered-products',
                        success: function (data) {
                            if (data) {

                                $('.' + divClass).html(data);
                                console.log($('.' + divClass).children().length);
                                if ($('.' + divClass).children().length <= 4) {
                                    $('#' + id).find('.icon-container').hide();
                                } else {
                                    $('#' + id).find('.icon-container').show();

                                }
                            } else {
                                //                                $('#' + id).prop('selectedIndex', 0);
                                $('.product_ajax_request_error').modal('show');
                            }
                        }
                    });
                });
            });

SCRIPT;

    $this->registerJs($insertJs, View::POS_READY);

    ?>
    <?php endif; ?>
<?php } ?>
    </div>
    <div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in product_ajax_request_error" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close lazada_error_modal_close"></span></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                        <h3 id=''>Error</h3>
                        <p id="">No data Found for the selected Region.</p>
                        <div class="xs-mt-50">
                            <button type="button" data-dismiss="modal" class="btn btn-space btn-default lazada_error_modal_close">Close</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>
    <style>
        .SortChannelList li a{
            cursor: pointer;
        }
    </style>

    <input name="connected_user"  id="connected_user" type="hidden" value="<?=$user_id?>">

<?php
    $this->registerJsFile('@web/js/dashboard/index.js', ['depends' => [yii\web\JqueryAsset::className()]]);
    $this->registerJsFile('@web/js/dashboard/dashboard.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>