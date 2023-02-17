<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\User */

$this->title = 'Create Role';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['/user']];
$this->params['breadcrumbs'][] = ['label' => 'User Roles', 'url' => ['/user-role']];
$this->params['breadcrumbs'][] = 'Add New';
?>
<!--Flash Page Header Notification Start-->
<?php
if (Yii::$app->session->hasFlash('success')):
    $page_header = Yii::$app->session->getFlash('success');
    ?>
    <!--//app ui sticky notification check-->
    <script>
        var message='User Role Create Successfully';
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
        var message='User Role are not Created Successfully';
        setTimeout(function(){
            jQuery(function () {
                DangerStickyNotification(message)
            });
        }, 3000);
    </script>
    <?php
endif;
if (Yii::$app->session->hasFlash('exist')):
    $page_header = Yii::$app->session->getFlash('exist');
    ?>
    <script>
        var message='User Role Exist';
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
        'role_menu_permission' => $role_menu_permission,
        'role_channel_permission' => $role_channel_permission,
        'role_other_permission' => $role_other_permission,
    ]) ?>

</div>