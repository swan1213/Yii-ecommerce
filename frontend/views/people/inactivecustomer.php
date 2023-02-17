<?php

use yii\helpers\Html;
use backend\models\CustomerUser;
use yii\widgets\Breadcrumbs;
use backend\models\Stores;
use backend\models\Orders;
use backend\models\Channels;
use common\models\User;
use yii\db\Query;
use backend\models\MagentoStores;


$this->title = $label.' Hidden People';
$this->params['breadcrumbs'][] = ['label' => 'People', 'url' => ['/people']];
$this->params['breadcrumbs'][] = ['label' => 'Connected Channels', 'url' => "javascript: void(0)", 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => $label, 'url' => [basename($_SERVER['REQUEST_URI'])]];
$this->params['breadcrumbs'][] = 'Hidden People';


$user_id = Yii::$app->user->identity->id;
$user_level = Yii::$app->user->identity->level;
if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
    $user_id = Yii::$app->user->identity->parent_id;
}
?>

<input type="hidden" value="<?=$connection_id;?>" id="connection_id">
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
<div class="customerListTAble be-loading">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default panel-table">
                <div class="panel-heading"><?php echo $this->title; ?>
                    <div class="tools">
                        <a href="<?php //echo $new_basepath                 ?>#"><span class="icon mdi mdi-download"></span></span></a>
                       
                         <div id="dropdown_delete" class="dropdown icon">
                            <span data-toggle="dropdown" class="icon mdi mdi-more-vert dropdown-toggle" aria-expanded="true"></span>
                            <ul role="menu" class="dropdown-menu multiple-delete-dropdown">
                                <li><a id="" data-toggle="modal" data-target="#inactive-people-delete-modal-warning" class="btn btn-default customPeople custom_delete_btn">Undo Delete</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

            <div class="panel-body table-responsive">
                <div class="be-spinner">
                    <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                        <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                    </svg>
                </div>
                <table id="inactive_people_tbl_view" class="table table-striped table-hover table-fw-widget" style="width:100%">
                    <thead>
                    <tr>
                        <th>
                            <div class="be-checkbox">
                                <input id="ck_main_1" type="checkbox" data-parsley-multiple="groups" value="bar" data-parsley-mincheck="2" data-parsley-errors-container="#error-container1" class="people_multiple_check">
                                <label for="ck_main_1"></label>
                            </div>
                        </th>
                        <th>Name</th>
                        <th>Acquired On</th>
                        <th>Location</th>
                        <th>Customer Rating</th>
                        <th>Customer Lifetime Value</th>
                        <th># of Orders</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>


 
<!--*****************************MODAL START HERE**********************************************-->

<div id="inactive-people-delete-modal-warning" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-warning"><span class="modal-main-icon mdi mdi-alert-triangle"></span></div>
                    <h3>Warning!</h3>
                    <p>Are you sure you want to undo delete all selected product !</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Cancel</button>
                        <button type="button" id="inactive_people_delete_button" class="btn btn-space btn-warning">Proceed</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>








