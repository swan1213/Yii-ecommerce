<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Businesstools */

$this->title = 'Create Businesstools';
$this->params['breadcrumbs'][] = ['label' => 'Businesstools', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="businesstools-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
