<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\UserSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'parent_id') ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'first_name') ?>

    <?= $form->field($model, 'last_name') ?>

    <?php // echo $form->field($model, 'email') ?>

    <?php // echo $form->field($model, 'password_hash') ?>

    <?php // echo $form->field($model, 'account_status') ?>

    <?php // echo $form->field($model, 'password_reset_token') ?>

    <?php // echo $form->field($model, 'auth_key') ?>

    <?php // echo $form->field($model, 'tax_rate') ?>

    <?php // echo $form->field($model, 'default_language') ?>

    <?php // echo $form->field($model, 'default_weight_preference') ?>

    <?php // echo $form->field($model, 'date_last_login') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'role') ?>

    <?php // echo $form->field($model, 'trial_period_status') ?>

    <?php // echo $form->field($model, 'subscription_plan_id') ?>

    <?php // echo $form->field($model, 'plan_status') ?>

    <?php // echo $form->field($model, 'account_confirm_status') ?>

    <?php // echo $form->field($model, 'corporate_add_street1') ?>

    <?php // echo $form->field($model, 'corporate_add_street2') ?>

    <?php // echo $form->field($model, 'corporate_add_city') ?>

    <?php // echo $form->field($model, 'corporate_add_state') ?>

    <?php // echo $form->field($model, 'corporate_add_zipcode') ?>

    <?php // echo $form->field($model, 'corporate_add_country') ?>

    <?php // echo $form->field($model, 'billing_address_strret1') ?>

    <?php // echo $form->field($model, 'billing_address_strret2') ?>

    <?php // echo $form->field($model, 'billing_address_city') ?>

    <?php // echo $form->field($model, 'billing_address_state') ?>

    <?php // echo $form->field($model, 'billing_address_zipcode') ?>

    <?php // echo $form->field($model, 'billing_address_country') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
