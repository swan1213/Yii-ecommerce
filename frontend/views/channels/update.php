<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Channels */

$this->title = 'Update Channels: ' . $model->channel_ID;
$this->params['breadcrumbs'][] = ['label' => 'Channels', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->channel_ID, 'url' => ['view', 'id' => $model->channel_ID]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="channels-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
