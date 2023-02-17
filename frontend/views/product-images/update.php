<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ProductImages */

$this->title = 'Update Product Images: ' . $model->image_ID;
$this->params['breadcrumbs'][] = ['label' => 'Product Images', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->image_ID, 'url' => ['view', 'id' => $model->image_ID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-images-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
