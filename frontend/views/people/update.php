<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\CustomerUser */

$this->title = 'Update Customer User: ' . $model->customer_ID;
$this->params['breadcrumbs'][] = ['label' => 'Customer Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->customer_ID, 'url' => ['view', 'id' => $model->customer_ID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="customer-user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
