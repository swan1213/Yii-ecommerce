<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use backend\models\Orders;
use backend\models\Stores;
use backend\models\OrderChannel;
use backend\models\Channels;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrdersSerach */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Orders';
$this->params['breadcrumbs'][] = $this->title;



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

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table">
            <div class="panel-heading">
                <div class="tools">
                    <a href="<?php //echo $new_basepath  ?>/export-user"><span class="icon mdi mdi-download"></span></span></a>
                    <span class="icon mdi mdi-more-vert"></span>
                </div>
            </div>
            <div class="panel-body">
                <table id="order_index_table" class="table table-striped table-hover table-fw-widget">
                    <thead> 
                        <tr>
                            <th>Order #</th>
                            <th>Customer Name</th>
                            <th>Channel Sold On</th>
                            <th>Order Value</th>
                            <th>Date Ordered</th>
                            <th>Shipping to Location</th>
                            <th>Status</th>
<!--                            <th>Shipped</th>   -->
                            
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div> 
