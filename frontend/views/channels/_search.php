<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ChannelsSerach */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="channels-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'channel_ID') ?>

    <?= $form->field($model, 'channel_name') ?>

    <?= $form->field($model, 'channel_url') ?>

    <?= $form->field($model, 'channel_amount') ?>

    <?= $form->field($model, 'channel_image') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'channel_revenue') ?>

    <?php // echo $form->field($model, 'channel_sales') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
