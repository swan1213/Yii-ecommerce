<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;

/* @var $this yii\web\View */
/* @var $model backend\models\User */
/* @var $form yii\widgets\ActiveForm */
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

                    <?php // $form->field($model, 'parent_id')->textInput() ?>
                    <div class="form-group xs-pt-10">
                        <?=$form->field($userProfile, 'firstname')->textInput(['maxlength' => true]) ?>
                    </div>

                    <div class="form-group xs-pt-10">
                        <?= $form->field($userProfile, 'lastname')->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="form-group xs-pt-10">
                        <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>
                    </div>    
                        <?php // $form->field($model, 'password_hash')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'account_status')->dropDownList([ 'activate' => 'Activate', 'deactivate' => 'Deactivate', ], ['prompt' => '']) ?>

                        <?php // $form->field($model, 'password_reset_token')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'auth_key')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'tax_rate')->textInput() ?>

                        <?php // $form->field($model, 'default_language')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'default_weight_preference')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'date_last_login')->textInput() ?>

                        <?php // $form->field($model, 'status')->textInput() ?>
                    <div class="form-group xs-pt-10">
                        <?php
                        if (sizeof($roledata) == 0) {

                        }
                        else {
                            $lst = array();
                            foreach ($roledata as $role_value) {
                                //array_push($lst, $role_value->role);
                                $lst[$role_value->id] = $role_value->title;
                            }
                            echo $form->field($model, 'permission_id')->dropDownList( $lst, ['prompt' => 'Please select a role']);
                        }
                        ?>
                    </div>
                        <?php // $form->field($model, 'trial_period_status')->dropDownList([ 'activate' => 'Activate', 'deactivate' => 'Deactivate', ], ['prompt' => '']) ?>

                        <?php // $form->field($model, 'subscription_plan_id')->textInput() ?>

                        <?php // $form->field($model, 'plan_status')->dropDownList([ 'activate' => 'Activate', 'deactivate' => 'Deactivate', ], ['prompt' => '']) ?>

                        <?php // $form->field($model, 'account_confirm_status')->dropDownList([ 'pending' => 'Pending', 'approved' => 'Approved', ], ['prompt' => '']) ?>

                        <?php //$form->field($model, 'corporate_add_street1')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'corporate_add_street2')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'corporate_add_city')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'corporate_add_state')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'corporate_add_zipcode')->textInput() ?>

                        <?php // $form->field($model, 'corporate_add_country')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'billing_address_strret1')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'billing_address_strret2')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'billing_address_city')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'billing_address_state')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'billing_address_zipcode')->textInput(['maxlength' => true]) ?>

                        <?php // $form->field($model, 'billing_address_country')->textInput(['maxlength' => true]) ?>

                        <?php //$form->field($model, 'created_at')->textInput() ?>

                        <?php // $form->field($model, 'updated_at')->textInput() ?>

                   <div class="form-group">
                        <?php if (sizeof($roledata) == 0) {
                            //echo Html::submitButton('Create Role', ['class' => 'btn btn-space btn-primary']);
                            echo Html::a('Create role', ['user-permission/create/'], ['class' => 'btn btn-space btn-primary']);
                        } else {
                            echo Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-space btn-primary' : 'btn btn-primary']);
                        } ?>
                    </div>
<!--                    <div class="form-group">
                        <?php // Html::submitButton($model->isNewRecord ? 'Create' : 'Create', ['class' => $model->isNewRecord ? 'btn btn-space btn-primary' : 'btn btn-space btn-primary']) ?>
                    </div>-->

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
