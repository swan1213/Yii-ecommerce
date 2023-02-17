<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\ProductChannel */

$this->title = 'Create Product Channel';
$this->params['breadcrumbs'][] = ['label' => 'Product Channels', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-channel-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
