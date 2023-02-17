<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\ProductChannel */

$this->title = 'Update Product Channel: ' . $model->product_channel_id;
$this->params['breadcrumbs'][] = ['label' => 'Product Channels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->product_channel_id, 'url' => ['view', 'id' => $model->product_channel_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="product-channel-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
