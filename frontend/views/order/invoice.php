<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use common\models\Order;
use common\models\BillingInvoice;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\OrdersSerach */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Invoices';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Billing', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = 'View Invoices';
$user_id = Yii::$app->user->identity->id;
$user_level = Yii::$app->user->identity->level;
if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
    $user_id = Yii::$app->user->identity->parent_id;
}
/* Get Billing invoice according to user */
$invoice_data = BillingInvoice::find(['user_id' => $user_id])->all();
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
                    <a href="<?php //echo $new_basepath   ?>#"><span class="icon mdi mdi-download"></span></span></a>
                    <span class="icon mdi mdi-more-vert"></span>
                </div>
            </div>
            <div class="panel-body">
                <table id="channels_view_table" class="table table-striped table-hover table-fw-widget">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Invoice Amount</th>
                            <th>View Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        /* Get Orders data */
                        if (!empty($invoice_data)) :
                            foreach ($invoice_data as $invoice_data_value) :
                               
                                $amount= number_format((float) $invoice_data_value->amount, 2, '.', '');
                                $stripe_amount = $amount / 100
                                ?>
                                <tr class="odd gradeX">
                                    <td><b><?= $invoice_data_value->stripe_id;?></b></td>
                                    <td>$<?= $stripe_amount;?></td>
                                    <td>
                                        <div class="icon"><a href="javascript:"><span class="mdi mdi-eye"></span></a></div>
                                    </td>
                                </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div> 
        </div>
    </div>
</div>
    