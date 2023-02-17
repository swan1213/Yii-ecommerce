<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use common\models\Product;
use common\models\OrderProduct;
use common\models\Order;
use common\models\UserConnection;
use common\models\Connection;

$user_id = Yii::$app->user->identity->id;
$loader_active = '';

$js = <<< 'SCRIPT'
$(function () { 
    $("[data-toggle='tooltip']").tooltip(); 
});
$(function () { 
    $("[data-toggle='popover']").popover(); 
});
SCRIPT;
// Register tooltip/popover initialization javascript
$this->registerJs($js);

$this->registerJs("$('img[title=\"Yii Forum\"]').tooltip()");

$this->title = 'Products';
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => "javascript: void(0)", 'class' => 'non_link'];
$this->params['breadcrumbs'][] = 'View All';

$user_connections = UserConnection::find()->where(['user_id' => $user_id])->all();
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

<!--******code for showing loader when first store or channel is importing ends here*****-->
<!--***********************************CHART START HERE**********************************************-->
<input type="hidden" id="user_id" value="<?php echo $user_id; ?>"/>
<div class="row">
    <div class="cust be-loading <?php echo $loader_active; ?>"> 
        <div class="col-sm-12 col-md-12">
            <div class="panel panel-default <?php
            if (empty($loader_active)) {
                echo 'be-loading';
            }
            ?> ProductsCharTS" style="z-index:999;">
                <div class="panel-heading panel-heading-divider">
                    <div class="widget-head">
                        <div class="tools">
                            <div class="dropdown"><span data-toggle="dropdown" class="icon mdi mdi-more-vert visible-xs-inline-block dropdown-toggle" aria-expanded="false"></span>
                                <ul role="menu" class="dropdown-menu">
                                    <li><a style="cursor:pointer;" id="donutchartweekmob" class="customPeople">Week</a></li>
                                    <li><a style="cursor:pointer;" id="donutchartmonthmob" class="customPeople">Month</a></li>
                                    <li><a style="cursor:pointer;" id="donutchartQuartermob" class="customPeople">Quarter</a></li>
                                    <li><a style="cursor:pointer;" id="donutchartyearmob" class="customPeople">Year</a></li>
                                    <li><a style="cursor:pointer;" id="donutchartannualmob" class="customPeople">Annual</a></li>
                                    <li class="divider"></li>
                                    <li><a style="cursor:pointer;" id="donutcharttodaymob" class="customPeople">Today</a></li>  
                                </ul>
                            </div>
                        </div>
                        <div class="button-toolbar hidden-xs">
                            <div class="btn-group">
                                <button type="button" class="btn btn-default customPeople " id="donutchartweek">Week</button>
                                <button type="button" class="btn btn-default customPeople" id="donutchartmonth">Month</button>
                                <button type="button" class="btn btn-default customPeople" id="donutchartQuarter">Quarter</button>
                                <button type="button" class="btn btn-default customPeople" id="donutchartyear">Year</button>
                                <button type="button" class="btn btn-default customPeople" id="donutchartannual">Annual</button>
                                <input name="" value="Year" id="hidden_graph" type="hidden">
                            </div>  
                            <div class="btn-group">
                                <button type="button" class="btn btn-default customPeople" id="donutcharttoday">Today</button>
                            </div>
                        </div>
                        <span class="title">Top Performing Categories</span>    
                    </div>                
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
                <div class="panel-body">
                    <div class="donut-chart" id="donut-chart" style="height:250px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<!--***********************************CHART END HERE*****************************-->
<div class="productTABLE be-loading">
    <div class="row ">
        <div class="col-sm-12">
            <div class="panel panel-default panel-table">
                <div class="panel-heading"><?php echo $this->title; ?>
                    <div class="tools">
                        <span class="icon mdi mdi-download"></span>
                        <!--span data-toggle="dropdown" class="icon mdi mdi-more-vert dropdown-toggle"></span-->
                        <div id="dropdown_delete" class="dropdown icon">
                            <span data-toggle="dropdown" class="icon mdi mdi-more-vert dropdown-toggle" aria-expanded="true"></span>
                            <ul role="menu" class="dropdown-menu multiple-delete-dropdown">
                                <li><a id="product_publish_check"><span class="icon mdi mdi-upload"></span> Publish to Channel</a></li>
                                <li><a id="product_delete_check"><span class="icon mdi mdi-delete"></span> Delete</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="be-spinner">
                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                        <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                        </svg>
                    </div>
                    <div class="tbl_container_custom table-responsive">
                        <table id="products_table1" class="table table-striped table-hover table-fw-widget be-loading product_tbl12" style="width:100%">
                            <thead>
                                <tr>
                                    <th>
                            <div class="be-checkbox">
                                <input id="ck_main_1" type="checkbox" data-parsley-multiple="groups" value="bar" data-parsley-mincheck="2" data-parsley-errors-container="#error-container1" class="product_multiple_check">
                                <label for="ck_main_1"></label>
                            </div>
                            </th>
                            <th style="width:25%;">Product Name</th>
                            <th>Channels Listed On</th>
                            <th>SKU</th>
                            <th>Price</th>
                            <th># of Orders</th>
                            <th># of Returns</th>
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
</div>

<div id="product-publish-modal-warning" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" id="product_publish_close" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
                <div class="panel-heading panel-heading-divider">Connected Channel<span class="panel-subtitle"></span></div>
            </div>
            <div class="modal-body tc-scroll">
                <div class="form-group">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-9">
                        <?php foreach ($user_connections as $user_connection) {
                            if ($user_connection->id == \common\models\User::getDefaultConnection($user_id))
                                continue;
                            ?>
                            <div class="be-checkbox">
                                <input name="publish_check" id="publish_ck_<?= $user_connection->id?>" value="<?= $user_connection->connection_id?>" type="checkbox">
                                <label for="publish_ck_<?= $user_connection->id?>"><?= $user_connection->getPublicName()?></label>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="text-center">
                    <button type="button" id="product_publish_button" class="btn btn-space btn-default">Publish</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="product-delete-modal-warning" tabindex="-1" role="dialog" class="modal fade">
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
                        <button type="button" id="product_delete_button" class="btn btn-space btn-warning">Proceed</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in product_select_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close product_select_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='product_select_header_error_msg'>Please select Products!</h3>
                    <p id="product_select_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default product_select_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<?php
    //$this->registerJsFile('@web/js/product/product.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>





