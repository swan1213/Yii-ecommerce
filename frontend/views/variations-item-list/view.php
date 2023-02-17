<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\VariationsItemList */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Variations Item Lists', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="variations-item-list-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'variation_id',
            'item_name',
            'item_value',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
