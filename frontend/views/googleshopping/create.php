<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model backend\models\Channels */

$this->title = 'Create Feed';
$this->params['breadcrumbs'][] = ['label' => 'Googgle Shopping', 'url' => ['/googleshopping', 'id' => $id]];
$this->params['breadcrumbs'][] = 'Create Feed';
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

<?= $this->render('_form', [
    'id' => $id,
    'type' => 'Create',
    'feed_model' => $feed_model,
    'action' => $action,
    'timezone_list' => $timezone_list,
    'category_list' => $category_list,
    'selected_categories' => $selected_categories
]) ?>
