<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use common\models\Variation;
use common\models\VariationValue;

/* @var $this yii\web\View */

$this->title = 'Product Variations';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Variations', 'url' => ['/variations']];
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
                                <th>Variation Name</th>
                                <th>Label</th>
                                <th>Variation Values</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($variationItems as $variation): ?>
                              <tr>
                                  <td><?= $variation->name ?></td>
                                  <td><?= $variation->description ?></td>
                                  <?php
                                  $variation_items = VariationValue::find()
                                      ->select(['label', 'value'])
                                      ->where(['variation_item_id' => $variation->id, 'user_id' => $userId])->all();
                                  $items = '';
                                  if (!empty($variation_items)):
                                    foreach ($variation_items as $variation_item):
                                      $item_name = isset($variation_item->label)? $variation_item->label.'('.$variation_item->value.')' :$variation_item->value ;
                                      $items .= $item_name . '<br>';
                                    endforeach;
                                  endif;
                                  ?>
                                  <td><?= $items ?></td>
                              </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>






