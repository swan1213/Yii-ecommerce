<?php

use common\models\UserConnection;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use common\models\Product;
use common\models\OrderProduct;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProductsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$user_id = Yii::$app->user->identity->id;
$user_connection_id = $_GET['user_connection_id'];
$user_connection = UserConnection::find()->where(['id' => $user_connection_id])->one();
$channel_name = $user_connection->getPublicName();

$this->title = 'Products';
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/product']];
$this->params['breadcrumbs'][] = ['label' => 'Connected Channels', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => $channel_name, 'url' => ['/product/connected-products/?user_connection_id='.$user_connection_id]];
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
<!--Input hiden Fied !-->
<input type="hidden" value="<?=$user_connection_id;?>" id="user_connection_id">
<div class="be-loading ConnectedProDUCTS" style="z-index:999;">
<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table" >
            <div class="panel-heading"><?php echo $this->title; ?>
                <div class="tools">
                <span class="icon mdi mdi-download"></span>
                <div id="dropdown_delete" class="dropdown icon">
                            <span data-toggle="dropdown" class="icon mdi mdi-more-vert dropdown-toggle" aria-expanded="true"></span>
                            <ul role="menu" class="dropdown-menu multiple-delete-dropdown">
                                <li><a id="product_publish_check"><span class="icon mdi mdi-upload"></span> Publish to Channel</a></li>
                                <li><a id="product_delete_check"><span class="icon mdi mdi-delete"></span> Delete</a></li>
                            </ul>
                        </div></div>
            </div>
            <div class="panel-body table-responsive">
			<div class="be-spinner">
                        <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                        </svg>
                    </div>
                <div class="tbl_container_custom">
                <table id="connectedproducts_table1" class="table table-striped table-hover table-fw-widget">
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
