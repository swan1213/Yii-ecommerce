<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\OrdersSerach */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="orders-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'order_ID') ?>

    <?= $form->field($model, 'customer_id') ?>

    <?= $form->field($model, 'stripe_transaction_ID') ?>

    <?= $form->field($model, 'order_status') ?>

    <?= $form->field($model, 'product_qauntity') ?>

    <?php // echo $form->field($model, 'shipping_address') ?>

    <?php // echo $form->field($model, 'billing_address') ?>

    <?php // echo $form->field($model, 'brand_logo_image') ?>

    <?php // echo $form->field($model, 'tracking_link') ?>

    <?php // echo $form->field($model, 'market_place_fees') ?>

    <?php // echo $form->field($model, 'credit_card_fees') ?>

    <?php // echo $form->field($model, 'processing_fees') ?>

    <?php // echo $form->field($model, 'total_amount') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
