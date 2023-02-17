<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model backend\models\Channels */

$this->title = 'Update Feed';
$this->params['breadcrumbs'][] = ['label' => 'Facebook', 'url' => ['/facebook']];
$this->params['breadcrumbs'][] = $this->title;
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
<div class="facebook-create">
    <?= $this->render('_form', [
        "action"=> $action,
        "feed_name" => $feed_name,
        "selected_categories" => $selected_categories,
        "selected_countries" => $selected_countries,
        "categories"=>$categories,
        "countries"=>$countries
    ]) ?>

</div>
