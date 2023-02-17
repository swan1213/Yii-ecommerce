<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use common\models\AttributionType;

/* @var $this yii\web\View */
/* @var $model backend\models\AttributeType */

$this->title = 'Add Attribute';
$this->params['breadcrumbs'][] = ['label' => 'Products', 'url' => ['/products']];
$this->params['breadcrumbs'][] = ['label' => 'Attributes', 'url' => ['/attributes']];
$this->params['breadcrumbs'][] = 'Add New';

$attr_types = AttributionType::find()->where(['user_id' => Yii::$app->user->identity->id])->all();
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
                            <label class="col-sm-3 control-label">Attribute Type</label>
                            <div id="attr-type" class="col-sm-6">
                                <select name="attr_type" class="select2">
                                    <option>Please Select</option>
                                    <?php foreach ($attr_types as $attr_type): ?>
                                        <option value="<?= $attr_type->id ?>"><?= ucfirst($attr_type->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <?php if (empty($attr_types)): ?>
                            <div class="form-group">
                                <div class="col-sm-3"></div>
                                <div id="cr-attr-type" class="col-sm-6">
                                    <a class="btn btn-space btn-primary" href="/attribute-type/create">Add an Attribute Type</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Attribute Name</label>
                            <div id="attr-name" class="col-sm-6">
                                <input  name="attr_name" type="text" class="form-control" placeholder="Please Enter Value" value="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Label</label>
                            <div id="attr-label" class="col-sm-6">
                                <input  name="attr_label" type="text" class="form-control" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Description</label>
                            <div id="pDes_create" class="col-sm-6">
                                <div id="attr-desc"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-xs-6">
                                <p class="text-right">
                                    <input type="submit" class="btn btn-space btn-primary" id="attr_submit" value="Save">
                                    <a class="btn btn-space btn-default" href="/attributes">Cancel</a>
                                </p>
                            </div>
                        </div>
                        <input type="hidden" name="attr_desc" id="attr_desc" value="" />
                        <input type="hidden" name="_csrf" value="<?= Yii::$app->request->getCsrfToken() ?>" />
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
