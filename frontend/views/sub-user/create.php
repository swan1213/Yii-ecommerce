<?php

use yii\helpers\Html;
use common\models\UserPermission;

/* @var $this yii\web\View */
/* @var $model backend\models\User */

$this->title = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['/user']];
//$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Add New';

$roledata = UserPermission::find()->where(["user_id" => Yii::$app->user->identity->id])->all();
?>
<!--Flash Page Header Notification Start-->
<?php
if (Yii::$app->session->hasFlash('success')):
   $page_header = Yii::$app->session->getFlash('success');
?>
<!--//app ui sticky notification check-->
<script>
    var message='User Create Successfully';
    setTimeout(function(){
        jQuery(function () {
        SuccessStickyNotification(message) 
    });
 }, 3000);
 
</script>
<?php
endif;
if (Yii::$app->session->hasFlash('danger')):
  $page_header = Yii::$app->session->getFlash('danger');
?>
<script>
    var message='User are not Created Successfully';
    setTimeout(function(){
        jQuery(function () {
        DangerStickyNotification(message) 
    });
 }, 3000);
</script>
<?php
endif;
?>

<div class="user-create">

    <?= $this->render('_form', [
        'model' => $model,
        'userProfile' => $userProfile,
        'roledata' => $roledata
    ]) ?>

</div>
