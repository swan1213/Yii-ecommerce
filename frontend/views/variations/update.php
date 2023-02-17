<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model backend\models\Variations */

$this->title = 'Update Variations: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Variations', 'url' => ['/variations']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="page-head variations-update">

    <h1 class="page-head-title"><?= Html::encode($this->title) ?></h1>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
          'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
