<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use common\models\Order;
use common\models\User;
use common\models\UserConnection;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrdersSerach */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Orders';
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/order']];
$this->params['breadcrumbs'][] = 'View All';
?>

<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
</div>
<?php
$loader_active = '';
$users_Id = Yii::$app->user->identity->id;
$user_level = Yii::$app->user->identity->level;
if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
    $users_Id = Yii::$app->user->identity->parent_id;
}
$importing_count = UserConnection::find()->Where(['user_id' => $users_Id, 'import_status'=>UserConnection::IMPORT_STATUS_PROCESSING])->available()->count();
if ($importing_count==0) {
    $loader_active = '';
} else {
    $loader_active = 'be-loading be-loading-active';
    $loader_active = '';
}
?>
<!--*******code for showing loader in the chart when first store or channel is importing ends here********-->
<!--***********************CHAT START HERE************************************-->
<?php
$count = UserConnection::find()->where(['user_id' => $users_Id])->available()->count();
if($count>0){
    ?>
<div class="row">
    <div class="col-md-12">
        <div class="cust be-loading <?php echo $loader_active; ?>">
            <div class="panel panel-default OrderChaRT <?php
            if (empty($loader_active)) {
                echo 'be-loading';
            }
            ?>" style="z-index:999;">
                <div class="panel-heading panel-heading-divider">
                    <div class="widget-head">
                        <div class="tools">
                            <div class="dropdown"><span data-toggle="dropdown" class="icon mdi mdi-more-vert visible-xs-inline-block dropdown-toggle" aria-expanded="false"></span>
                                <ul role="menu" class="dropdown-menu">
                                    <li><a id="orderchartweekmob" class="customPeople" style="cursor:pointer">Week</a></li>
                                    <li><a id="orderchartmonthmob" class="customPeople" style="cursor:pointer">Month</a></li>
                                    <li><a id="orderchartQuatermob" class="customPeople" style="cursor:pointer">Quarter</a></li>
                                    <li><a id="orderchartyearmob" class="customPeople" style="cursor:pointer">Year</a></li>
                                    <li><a id="orderchartannualmob" class="customPeople" style="cursor:pointer">Annual</a></li>
                                     <li class="divider"></li>
                                    <li><a id="ordercharttodaymob" class="customPeople" style="cursor:pointer">Today</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="button-toolbar hidden-xs">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default customPeople " id="orderchartweek">Week</button>
                                <button type="button" class="btn btn-default customPeople" id="orderchartmonth">Month</button>
                                <button type="button" class="btn btn-default customPeople" id="orderchartQuater">Quarter</button>
                                <button type="button" class="btn btn-default customPeople" id="orderchartyear">Year</button>
                                <button type="button" class="btn btn-default customPeople" id="orderchartannual">Annual</button>
                                </div>
                                  <div class="btn-group">
                                    <button type="button" class="btn btn-default customPeople" id="ordercharttoday">Today</button>
                                  </div>
                                <input name="" value="Year" id="hidden_graph" type="hidden">
                            

                        </div>
                        <span class="title">Orders by Channel</span> 
                    </div>
                    <div class="be-spinner" style="width:100%; text-align: center; right:auto">
                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">

                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                        </svg>
                        <?php if (isset($loader_active) and !empty($loader_active) and $loader_active == 'be-loading-active') {
                            ?><span style="display:block; padding-top:30px;">Your data is loading.</span><?php } else {
                            ?>

                            <span style="display:block; padding-top:30px;">Your data is loading.</span>
                        <?php }
                        ?>
                    </div>

                    <span class="panel-subtitle space"><ul id="chartInfoPeople" class="chart-legend-horizontal">

                        </ul>
                    </span>

                </div>
                <div id="padding-panel-body" class="panel-body"> 

                    <div class="order-chart" id="order-chart" style="height: 250px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php } 
?>
<!--*****************CHART END HERE**************************-->
<div class="orderListTAble be-loading">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default panel-table">
                <div class="panel-heading">
                    <div class="tools">
                        <a href="<?php //echo $new_basepath         ?>#"><span class="icon mdi mdi-download"></span></span></a>
                        <!--<span class="icon mdi mdi-more-vert"></span>-->
                        <div id="dropdown_delete" class="dropdown icon">
                            <span data-toggle="dropdown" class="icon mdi mdi-more-vert dropdown-toggle" aria-expanded="true"></span>
                            <ul role="menu" class="dropdown-menu multiple-delete-dropdown">
                                <li><a id="" data-toggle="modal" data-target="#order-delete-modal-warning" class="btn btn-default customPeople custom_delete_btn">Delete</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel-body table-responsive" style="width:100%"> 
                    <div class="be-spinner">
                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                        </svg>
                    </div>
                    <table id="order_index_table" class="table table-striped table-hover table-fw-widget" style="width:100%">
                        <thead>
                            <tr>
                                <th>
                                    <div class="be-checkbox">
                                        <input id="ck_main_1" type="checkbox" data-parsley-multiple="groups" value="bar" data-parsley-mincheck="2" data-parsley-errors-container="#error-container1" class="order_multiple_check">
                                            <label for="ck_main_1"></label>
                                    </div>
                                </th>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Sold On</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Destination</th>
                                <th>Status</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div> 
</div>


<div id="order-delete-modal-warning" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-warning"><span class="modal-main-icon mdi mdi-alert-triangle"></span></div>
                    <h3>Warning!</h3>
                    <p>Are you sure you want to delete all selected product !</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Cancel</button>
                        <button type="button" id="order_delete_button" class="btn btn-space btn-warning">Proceed</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
