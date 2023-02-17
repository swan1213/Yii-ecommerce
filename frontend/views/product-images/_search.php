<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ProductImagesSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="product-images-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'image_ID') ?>

    <?= $form->field($model, 'product_ID') ?>

    <?= $form->field($model, 'label') ?>

    <?= $form->field($model, 'link') ?>

    <?= $form->field($model, 'tag_status') ?>

    <?php // echo $form->field($model, 'tag') ?>

    <?php // echo $form->field($model, 'priority') ?>

    <?php // echo $form->field($model, 'default_image') ?>

    <?php // echo $form->field($model, 'alternative_image') ?>

    <?php // echo $form->field($model, 'html_video_link') ?>

    <?php // echo $form->field($model, '_360_degree_video_link') ?>

    <?php // echo $form->field($model, 'image_status') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
