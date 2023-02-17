<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Fulfillment */

$this->title = 'Update Fulfillment: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Fulfillments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="warehouse-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
