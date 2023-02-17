<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model backend\models\AttributeType */

$this->title = 'Add Attribute Type';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Attributes', 'url' => ['/attributes']];
$this->params['breadcrumbs'][] = ['label' => 'Attribute Type', 'url' => ['/attribute-type']];
$this->params['breadcrumbs'][] = 'Add New';
//$this->params['breadcrumbs'][] = ['label' => 'Attributes', 'url' => ['/attributes']];
//$this->params['breadcrumbs'][] = ['label' => 'Attribute Types', 'url' => ['/attribute-type']];
?>
<div class="attribute-type-create">
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
                    <form action="create" method="post" style="border-radius: 0px;" class="form-horizontal group-border-dashed">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Attribute Type Name</label>
                            <div id="attr-type-name" class="col-sm-6">
                                <input  name="attr_type_name" type="text" class="form-control" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Label</label>
                            <div id="attr-type-label" class="col-sm-6">
                                <input  name="attr_type_label" type="text" class="form-control" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Description</label>
                            <div id="pDes_create" class="col-sm-6">
                                <div id="attr-type-desc"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-xs-6">
                                <p class="text-right">
                                    <input type="submit" class="btn btn-space btn-primary" id="attr_type_submit" value="Save">
                                    <a class="btn btn-space btn-default" href="/attribute-type">Cancel</a>
                                </p>
                            </div>
                        </div>
                        <input type="hidden" name="attr_type_desc" id="attr_type_desc" value="" />
                        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>" />


                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
