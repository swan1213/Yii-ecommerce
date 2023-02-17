<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Fulfillment */

$this->title = 'Create Fulfillment';
$this->params['breadcrumbs'][] = ['label' => 'Fulfillments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="warehouse-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
