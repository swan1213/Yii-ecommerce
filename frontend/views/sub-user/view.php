<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\models\UserRole;
/* @var $this yii\web\View */
/* @var $model backend\models\User */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['/user']];
//$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'View';

//echo'<pre>';
//print_r($model);
//die;
$userrole = UserRole::find()->Where(['id' => $model->role_id])->one();
if ($userrole != null) {
    $model->role = $userrole->title;
}
else if ($model->role == 0) {
    $model->role = "SuperAdmin";
}
else {
    $model->role = "";
}
?>
<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [  
            'id',
            'parent_id',
            'username',
            'first_name',
            'last_name',
            'email:email',
            'password_hash',
            'account_status',
            'password_reset_token',
            'auth_key',
            'tax_rate',
            'default_language',
            'default_weight_preference',
            'date_last_login',
            'status',
            'role',
            'trial_period_status',
            'subscription_plan_id',
            'plan_status',
            'account_confirm_status',
            'corporate_add_street1',
            'corporate_add_street2',
            'corporate_add_city',
            'corporate_add_state',
            'corporate_add_zipcode',
            'corporate_add_country',
            'billing_address_street1',
            'billing_address_street2',
            'billing_address_city',
            'billing_address_state',
            'billing_address_zipcode',
            'billing_address_country',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>

<?php
    //$this->registerJsFile('@web/js/user/user_permission.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>