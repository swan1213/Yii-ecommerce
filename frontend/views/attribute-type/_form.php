<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model backend\models\AttributeType */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="attribute-type-form">

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
    <div class="col-sm-10" >
        <div class="panel panel-default panel-border-color panel-border-color-primary">
            <div class="panel-body">    

                <?php $form = ActiveForm::begin(); ?>
                <div class="form-group xs-pt-10">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="form-group xs-pt-10">
                    <?= $form->field($model, 'label')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="form-group xs-pt-10">
                    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="form-group">
                    <label class="col-sm-3 control-label">Description</label>
                    <div id="pDes_create" class="col-sm-6">
                        <div id="product-create-description"></div>
                    </div>
                </div>
                <div class="form-group">
                    <?= Html::submitButton($model->isNewRecord ? 'Save' : 'Update', ['class' => 'btn btn-space btn-primary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

