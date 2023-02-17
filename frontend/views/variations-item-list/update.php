<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\VariationsItemList */

$this->title = 'Update Variations Item List: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Variations Item Lists', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="variations-item-list-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
