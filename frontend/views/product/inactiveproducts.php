<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use backend\models\Products;
use backend\models\ProductChannel;
use backend\models\OrdersProducts;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$connected=$_GET['product'];
$this->title = $connected.' Hidden Products';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Connected Channels', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => $connected, 'url' => ['/products/inactive-products?product='.$connected]];
$this->params['breadcrumbs'][] = 'Hidden Products';

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
<!--Input hiden Fied !-->
<input type="hidden" value="<?=$connected;?>" id="inactive_products">
<div class="be-loading InactiVeProDUCTS">
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table">
            <div class="panel-heading"><?php echo $this->title; ?>
                <div class="tools">
                        <span class="icon mdi mdi-download"></span>
                        <!--span data-toggle="dropdown" class="icon mdi mdi-more-vert dropdown-toggle"></span-->
                        <div id="dropdown_delete" class="dropdown icon">
                            <span data-toggle="dropdown" class="icon mdi mdi-more-vert dropdown-toggle" aria-expanded="true"></span>
                            <ul role="menu" class="dropdown-menu multiple-delete-dropdown">
                                <li><a id="" data-toggle="modal" data-target="#inactive-product-delete-modal-warning" class="btn btn-default customPeople custom_delete_btn">Undo Delete</a></li>
                            </ul>
                        </div>
                    </div>
            </div>
            <div class="panel-body table-responsive">
			<div class="be-spinner">
                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                        </svg>
                    </div>
                <div class="tbl_container_custom">
                <table id="inactiveproducts_table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                        <tr>
                            <th>
                            <div class="be-checkbox">
                            <input id="ck_main_1" type="checkbox" data-parsley-multiple="groups" value="bar" data-parsley-mincheck="2" data-parsley-errors-container="#error-container1" class="product_multiple_check">
                            <label for="ck_main_1"></label>
                            </div>
                            </th>
                            <th>Product Name</th>
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

<div id="inactive-product-delete-modal-warning" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-warning"><span class="modal-main-icon mdi mdi-alert-triangle"></span></div>
                    <h3>Warning!</h3>
                    <p>Are you sure you want to undo delete all selected products !</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Cancel</button>
                        <button type="button" id="inactive_product_delete_button" class="btn btn-space btn-warning">Proceed</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>






 