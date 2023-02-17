<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model backend\models\Channels */

$this->title = 'Create Board';
$this->params['breadcrumbs'][] = ['label' => 'Pinterest', 'url' => ['/pinterest']];
$this->params['breadcrumbs'][] = 'Create Board';
?>
<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
</div>
<div class="pinterest-create">
    <?= $this->render('_form', [
        "id"=> $id,
        "user_id" => $user_id,
        "token" => $token,
        "board_name" => $board_name,
        "selected_categories" => $selected_categories,
        "selected_countries" => $selected_countries,
        "categories"=>$categories,
        "countries"=>$countries
    ]) ?>
</div>
