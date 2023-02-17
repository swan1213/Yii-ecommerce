<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use backend\models\AttributeType;

/* @var $this yii\web\View */
/* @var $model backend\models\AttributeType */

$this->title = $model->isNewRecord ? 'Add Product Type': 'Update Product Type';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Types', 'url' => ['/products-type']];
$this->params['breadcrumbs'][] = $model->isNewRecord ? 'Add New' : 'Update';

?>
<div class="product-type-create">
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
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
                <div class="panel-body">

                        <?php $form = ActiveForm::begin(['options' => ['class' => 'form-horizontal group-border-dashed']]); ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Name</label>
                            <div id="product_type_name_label" class="col-sm-6">
                                <input  name="ProductType[name]" id="product_type_name" type="text" class="form-control" placeholder="Please, Input Product Type Name" value="<?=$model->isNewRecord?'':$model->name?>">
                                <input  name="ProductType[storeName]" id="product_type_storeName" type="hidden" class="form-control" value="<?=$model->isNewRecord?'Elliot':$model->storeName?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-xs-6">
                                <p class="text-right">
                                    <input type="submit" class="btn btn-space btn-primary" id="product_type_submit" value="<?=$model->isNewRecord ?'Save':'Update'?>">
                                    <a class="btn btn-space btn-default" href="/products-type">Cancel</a>
                                </p>
                            </div>
                        </div>
                        <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
    </div>
</div>
