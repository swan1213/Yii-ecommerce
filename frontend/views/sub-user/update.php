<?php

use common\models\UserPermission;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\grid\GridView;


/* @var $this yii\web\View */
/* @var $model backend\models\User */

$this->title = 'Update User: ' . $user_profile->firstname . ' ' . $user_profile->lastname;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $user_profile->firstname . ' ' . $user_profile->lastname, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
$role_data = UserPermission::find()->where(["user_id" => Yii::$app->user->identity->id])->all();
$lst = array();
foreach ($role_data as $role_value) {
    //array_push($lst, $role_value->role);
    $item = array();
    $item["value"] = $role_value->id;
    $item["text"] = $role_value->title;
    array_push($lst, $item);
}

?>
<div class="user-profile">
    <div class="row">
<div class="col-md-5">


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

    <div class="subuser-info-list panel panel-default">
        <div class="panel-body">
            <div class="table-responsive">
                <table id="sub_user" style="clear: both" class="table table-striped table-borderless">
                    <tbody>
                    <tr>
                        <td width="35%">First Name </td>
                        <td width="65%"><a href="javascript:" data-type="text" class="editable editable-click" data-title="Enter First Name"  id="first_name_sub" ><?php echo $user_profile->firstname; ?></a></td>
                        <input type="hidden" name="sub_profile_id" value="<?php echo $model->id; ?>" id="sub_profile_id" />
                    </tr>
                    <tr>
                        <td width="35%">Last Name</td>
                        <td width="65%"><a href="javascript:" data-type="text" class="editable editable-click" data-title="Enter Last Name" id="last_name_sub"><?php echo $user_profile->lastname; ?></a></td>
                    </tr>
                    <tr>
                        <td width="35%">Email Address</td>
                        <td width="65%"><a id="email_add_sub" href="javascript:" class="editable editable-click" data-type="text" data-title="Enter Email Address"><?php echo $model->email; ?></a></td>
                    </tr>
                    <tr>
                        <td width="35%">Role</td>
                        <td width="65%"><a id="role_sub" href="javascript:" class="editable editable-click" data-value="<?php echo $model->permission_id; ?>" data-type="select" data-title="Enter Role"><?php echo $model->permission_id; ?></a></td>
                        <span style="display:none;" id="sub_role_id"><?php echo json_encode($lst); ?></span>
                    </tr>
                    <tr>
                        <td><button class="btn btn-space btn-primary" onclick="savesubprofile()">Save</button></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<?php
    $this->registerJsFile('@web/js/user/user_permission.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
