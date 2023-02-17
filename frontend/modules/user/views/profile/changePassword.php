<?php 
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
//use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;


$this->title = 'Change Password';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="user-form">


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
                        <?= $form->field($model,'oldpass')->passwordInput(['maxlength' => true ,'class' => 'form-control'])->label('Old Password') ?>
                    </div>

                    <div class="form-group xs-pt-10">
                        <?= $form->field($model, 'newpass')->passwordInput(['maxlength' => true])->label('New Password') ?>
                    </div>
                    <div class="form-group xs-pt-10">
                        <?= $form->field($model, 'repeatnewpass')->passwordInput(['maxlength' => true])->label('Confirm Password') ?>
                    </div>    
                   
                    <div class="form-group">
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-space btn-primary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>

