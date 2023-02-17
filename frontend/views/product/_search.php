<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\ProductsSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="products-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'product_name') ?>

    <?= $form->field($model, 'SKU') ?>

    <?= $form->field($model, 'EAN') ?>

    <?= $form->field($model, 'Jan') ?>

    <?php // echo $form->field($model, 'ISBN') ?>

    <?php // echo $form->field($model, 'MPN') ?>

    <?php // echo $form->field($model, 'description') ?>

    <?php // echo $form->field($model, 'adult') ?>

    <?php // echo $form->field($model, 'age_group') ?>

    <?php // echo $form->field($model, 'availability') ?>

    <?php // echo $form->field($model, 'brand') ?>

    <?php // echo $form->field($model, 'condition') ?>

    <?php // echo $form->field($model, 'gender') ?>

    <?php // echo $form->field($model, 'weight') ?>

    <?php // echo $form->field($model, 'stock_quantity') ?>

    <?php // echo $form->field($model, 'stock_level') ?>

    <?php // echo $form->field($model, 'stock_status') ?>

    <?php // echo $form->field($model, 'low_stock_notification') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'sales_price') ?>

    <?php // echo $form->field($model, 'schedules_sales_date') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'occasion') ?>

    <?php // echo $form->field($model, 'weather') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
