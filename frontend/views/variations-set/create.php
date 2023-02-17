<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\VariationsSet */

$this->title = 'Create Variations Set';
$this->params['breadcrumbs'][] = ['label' => 'Variations Sets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="variations-set-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
