<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\models\Variation;
use common\models\VariationValue;
use common\models\VariationSet;
use common\models\VariationItem;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Product Variations Sets';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Variations', 'url' => ['/variations']];
$this->params['breadcrumbs'][] = ['label' => 'Variations Sets', 'url' => ['/variations-set']];
$this->params['breadcrumbs'][] = 'View All';
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
<div class="product_variation_body be-loading">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default panel-table">
                <div class="panel-heading"><?php echo $this->title; ?>
                    <div class="tools"><span class="icon mdi mdi-download"></span><span class="icon mdi mdi-more-vert"></span></div>
                </div>
                <div class="panel-body">
                    <table id="variations_table" class="table table-striped table-hover table-fw-widget">
                        <thead>
                        <tr>
                            <th>Variations Set Name</th>
                            <th>Variations Set Label</th>
                            <th>Variations Set Values</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($variationSets as $variationSet): ?>
                            <tr>
                                <td><?= $variationSet->name ?></td>
                                <td><?= $variationSet->description ?></td>
                                <td><?= $variationSet->getItemNames() ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>