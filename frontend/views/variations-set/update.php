<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\VariationsSet */

$this->title = 'Update Variations Set: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Variations Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="variations-set-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
