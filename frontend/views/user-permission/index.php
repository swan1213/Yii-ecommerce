<?php
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\models\UserPermission;
use common\models\User;
use common\component\Helpers;

$this->title = 'User Roles';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'User Management', 'url' => ['/user']];
$this->params['breadcrumbs'][] = ['label' => 'User Roles', 'url' => ['/user-role']];
//$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'View All';

?>
<!--Flash Page Header Notification Start-->
<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
    <?= Html::a('Create Role', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<div class="row">
    <div class="col-sm-12">
        <div class="panel panel-default panel-table">

            <div class="panel-body">
                <table id="user_table" class="table table-striped table-hover table-fw-widget">
                    <thead>

                    <tr>
                        <th style="width:20%;">Role Title</th>
                        <th style="width:30%;">Menu Role</th>
                        <th style="width:40%;">Channel Role</th>
                        <th style="width:10%;">Manage</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($role_data as $role_value) {
                        ?>
                        <tr class="odd gradeX">
                            <td><?php echo $role_value['title']; ?></td>
                            <td><?php echo $role_value['menu_permission']; ?></td>
                            <td><?php echo $role_value['channel_permission']; ?></td>
                            <td class="center">
                                <?=
                                Html::a('', ['delete', 'class' => "icon", 'id' => $role_value['id']], [
                                    'class' => 'mdi mdi-delete',
                                    'data' => [
                                        'confirm' => 'Are you sure you want to delete this item?',
                                        'method' => 'post',
                                    ],
                                ]);
                                ?>
                                <?php
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;'.Html::a('', ['user-permission/update/', 'id' => $role_value['id']], [
                                        // Url::to(['user/delete', 'id' => $user_value->id]),
                                        'class' => 'mdi mdi-edit',
                                    ]);
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