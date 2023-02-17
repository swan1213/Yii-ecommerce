<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Orders */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="orders-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'customer_id')->textInput() ?>

    <?= $form->field($model, 'stripe_transaction_ID')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'order_status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'product_qauntity')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'shipping_address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'billing_address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'brand_logo_image')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tracking_link')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'market_place_fees')->textInput() ?>

    <?= $form->field($model, 'credit_card_fees')->textInput() ?>

    <?= $form->field($model, 'processing_fees')->textInput() ?>

    <?= $form->field($model, 'total_amount')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
