<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use common\models\ProductType;


$this->title = 'Product Types';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Types', 'url' => ['/products-type']];
$this->params['breadcrumbs'][] = 'View All';
//$this->title
$productTypes = ProductType::find()->all();
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
    <?= Html::a('Add Product Type', ['create'], ['class' => 'btn btn-primary']) ?>
</div>
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
                            <th>Name</th>
                            <th>Publisher</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php
                                foreach ($productTypes as $eachType) :
                            ?>
                            <tr>
                              <td><a href="products-type/update/<?=$eachType->id?>"><?= $eachType->name; ?></a></td>
                              <td><a href="#"><?= $eachType->storeName; ?></a></td>
                            </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>






