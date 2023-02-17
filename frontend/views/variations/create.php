<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model backend\models\Variations */

$this->title = 'Create Variations';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Variations', 'url' => ['/variations']];
$this->params['breadcrumbs'][] = 'Add New';
?>
<div class="page-head variations-create">
    <h2><?= Html::encode($this->title) ?></h2>
    <ol class="page-head-title breadcrumb page-head-nav">
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
