<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\User;
use yii\widgets\Breadcrumbs;
use common\models\UserPermission;


$username=Yii::$app->user->identity->domain;
$new_basepath = 'https://' . $username . '.'.Yii::$app->params['globalDomain'].'/';
/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['/user']];
$this->params['breadcrumbs'][] = 'View All';
/* Get user data*/
//$userdata = User::find()->Where(['parent_id' => Yii::$app->user->identity->id])->all();
$userdata = array();
array_push($userdata, Yii::$app->user->identity);
$children = User::find()->where(['parent_id' => Yii::$app->user->identity->id])->all();
foreach ($children as $child) {
    array_push($userdata, $child);
}
?>

<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
    <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
    ?>
    </ol>
    <?= Html::a('Create User', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<?php if(empty($userdata)) {?>
<p>
        <?= Html::a('Add Member', ['create'], ['class' => 'btn btn-space btn-primary']) ?>
</p>
<?php }?>


<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table">
            <div class="panel-heading">Default
                <div class="tools">

                    <a href="<?php echo $new_basepath ?>export-user"><span class="icon mdi mdi-download"></span></span></a>
                    <span class="icon mdi mdi-more-vert"></span>
                </div>
            </div>
            <div class="panel-body">
                <table id="user_table" class="table table-striped table-hover table-fw-widget">
                    <thead>

                        <tr>
                            <th>Name</th>
                            <th>User Role</th>
                            <th>Date Created</th>
                            <th>Date Last Logged In</th>
                            <th>Manage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($userdata as $user_value) {
                            ?>
                            <tr class="odd gradeX">
                                <td><?php echo $user_value->userProfile->firstname . ' ' . $user_value->userProfile->lastname; ?></td>
                                <td><?php
                                    $userrole = UserPermission::find()->Where(['id' => $user_value->permission_id])->one();
                                    if ($userrole != null) {
                                        echo $userrole->title;
                                    }
                                    else {
                                        echo "Site Admin";
                                    }
                                    ?>
                                </td>
                                <td><?php $dt = new DateTime("@$user_value->created_at"); echo $dt->format('Y-m-d'); ?></td>
                                <td><?php
                                    if (!empty($user_value->logged_at)) {
                                        $dt = new DateTime("@$user_value->logged_at");
                                        echo $dt->format('Y-m-d');
                                    }
                                    ?></td>
                                <td class="center">
                                    <?php
                                    if ($user_value->level == User::USER_LEVEL_MERCHANT_USER) {
                                        echo Html::a('', ['delete', 'class' => "icon", 'id' => $user_value->id, 'email' => $user_value->email], [
                                            'class' => 'mdi mdi-delete',
                                            'data' => [
                                                'confirm' => 'Are you sure you want to delete this item?',
                                                'method' => 'post',
                                            ],
                                        ]);
                                        echo '&nbsp;&nbsp;&nbsp;&nbsp;' . Html::a('', ['sub-user/update/', 'id' => $user_value->id], [
                                                // Url::to(['user/delete', 'id' => $user_value->id]),

                                                'class' => 'mdi mdi-edit',
                                            ]);
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                        } 
                        ?>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>








