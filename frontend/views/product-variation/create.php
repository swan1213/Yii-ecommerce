<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\ProductVariation */

$this->title = 'Create Product Variation';
$this->params['breadcrumbs'][] = ['label' => 'Product Variations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-variation-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
