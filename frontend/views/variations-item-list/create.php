<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\VariationsItemList */

$this->title = 'Create Variations Item List';
$this->params['breadcrumbs'][] = ['label' => 'Variations Item Lists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="variations-item-list-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
