<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\AttributeType */

$this->title = 'Update Attribute Type: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Attributes', 'url' => ['/attributes']];
$this->params['breadcrumbs'][] = ['label' => 'Attribute Types', 'url' => ['/attribute-type']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="attribute-type-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
