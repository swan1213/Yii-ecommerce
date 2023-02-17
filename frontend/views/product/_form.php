<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Products */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="products-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sku')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ean')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'jan')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mpn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'adult')->dropDownList([ 'no' => 'No', 'yes' => 'Yes', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'age_group')->dropDownList([ 'Newborn' => 'Newborn', 'Infant' => 'Infant', 'Toddler' => 'Toddler', 'Kids' => 'Kids', 'Adult' => 'Adult', ], ['prompt' => '']) ?>

<!--    --><?//= $form->field($model, 'availability')->dropDownList([ 'In Stock' => 'In Stock', 'Out of Stock' => 'Out of Stock', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'brand')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'condition')->dropDownList([ 'New' => 'New', 'Used' => 'Used', 'Refurbished' => 'Refurbished', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'gender')->dropDownList([ 'Female' => 'Female', 'Male' => 'Male', 'Unisex' => 'Unisex', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'weight')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'stock_quantity')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'stock_level')->dropDownList([ 'In Stock' => 'In Stock', 'Out of Stock' => 'Out of Stock', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'stock_status')->dropDownList([ 'Visible' => 'Visible', 'Hidden' => 'Hidden', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'low_stock_notification')->textInput() ?>

    <?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sales_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'schedule_sales_date')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

<!--    --><?//= $form->field($model, 'occasion')->textInput(['maxlength' => true]) ?>
<!---->
<!--    --><?//= $form->field($model, 'weather')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>