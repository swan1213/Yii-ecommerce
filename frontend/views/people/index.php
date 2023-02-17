<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\Customer;
use yii\widgets\Breadcrumbs;
use common\models\Connection;
use common\models\Order;
use yii\db\Query;
use common\models\UserConnection;
use common\models\User;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\CustomerUserSerach */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'People';
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['/people/index']];
$this->params['breadcrumbs'][] = 'View All';

/* include css and js only for this file */

//$this->registerCssFile(Yii::$app->homeUrl . 'lib/areachart/css/morris.css');
//$this->registerJsFile(Yii::$app->homeUrl . 'lib/areachart/js/app-charts-morris.js');
//$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js');
//$this->registerJsFile(Yii::$app->homeUrl . 'lib/areachart/js/morris.min.js');

/* Get Customers data */
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
</div>
<!--******************code for showing loader in chart if first store or channel is currently importing start here****************************-->
<?php
$users_Id = Yii::$app->user->identity->id;
$user_level = Yii::$app->user->identity->level;
if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
    $users_Id = Yii::$app->user->identity->parent_id;
}
$loader_active = '';
$importing_count = UserConnection::find()->Where(['user_id' => $users_Id, 'import_status'=>UserConnection::IMPORT_STATUS_PROCESSING])->available()->count();
if ($importing_count==0) {
    $loader_active = '';
} else {
    $loader_active = 'be-loading be-loading-active';
    $loader_active = '';
}
?>
<?php
$count = UserConnection::find()->Where(['user_id' => $users_Id])->available()->count();
if($count>0){
?>
<!--******************code for showing loader in chart if first store or channel is currently importing ends here****************************-->
<!--***********************CHART START HERE************************************-->

<div class="row">
    <div class="col-md-12">
	<div class="cust be-loading <?php echo $loader_active; ?>">
        <div class="panel panel-default <?php if(empty($loader_active)) {echo 'be-loading'; } ?> PeopleCHarT" style="">

            <div class="panel-heading panel-heading-divider">
                <div class="widget-head">
                    <div class="tools">
                        <div class="dropdown"><span data-toggle="dropdown" class="icon mdi mdi-more-vert visible-xs-inline-block dropdown-toggle" aria-expanded="false"></span>
                            <ul role="menu" class="dropdown-menu">
                                <li><a class="customPeoples" id="areachartweekmob" style="cursor:pointer;">Week</a></li>
                                <li><a class="customPeoples" id="areachartmonthmob" style="cursor:pointer;">Month</a></li>
                                <li><a class="customPeoples" id="areachartQuartermob" style="cursor:pointer;">Quarter</a></li>
                                <li><a class="customPeoples" id="areachartyearmob" style="cursor:pointer;">Year</a></li>
                                <li><a class="customPeoples" id="areachartAnnualmob" style="cursor:pointer;">Annual</a></li>
                                   <li class="divider"></li>
                                <li><a class="customPeoples" id="areacharttodaymob" style="cursor:pointer;">Today</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="button-toolbar hidden-xs">
                        <div class="btn-group">
                            <button type="button" class="btn btn-default customPeoples " id="areachartweek">Week</button>
                            <button type="button" class="btn btn-default customPeoples" id="areachartmonth">Month</button>
                            <button type="button" class="btn btn-default customPeoples" id="areachartQuarter">Quarter</button>
                            <button type="button" class="btn btn-default customPeoples" id="areachartyear">Year</button>
                            <button type="button" class="btn btn-default customPeoples" id="areachartAnnual">Annual</button>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-default customPeoples" id="areacharttoday">Today</button>                                
                            </div>
                            <input name="" value="Year" id="hidden_graph" type="hidden">
                        
                       
                    </div>
                    <span class="title">People by Channel</span>  
                 
                </div>
                <div class="be-spinner" style="width:100%; text-align: center; right:auto">
                            <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">

                            <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                            </svg>
                            <?php if (isset($loader_active) and ! empty($loader_active) and $loader_active=='be-loading-active') {
                                ?><span style="display:block; padding-top:30px;">Your data is loading.</span><?php } else {
									?>
								<span style="display:block; padding-top:30px;">Your data is loading.</span>	
							<?php	}
                            ?>
                        </div>
                   <span class="panel-subtitle"><ul id="chartInfoPeople" class="chart-legend-horizontal">

                        </ul>
                    </span>
            </div>
            <div id="padding-panel-body" class="panel-body">               
                <div class="area-chart" id="area-chart" style="height: 250px;"></div>
            </div>
        </div>
		</div>
    </div>
</div>
<?php } ?>
<!--*****************CHART END HERE**************************-->
<div class="customerListTAble be-loading">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default panel-table">
                <div class="panel-heading"><?php echo $this->title; ?>
                    <div class="tools">
                        <a href="<?php //echo $new_basepath                 ?>"><span class="icon mdi mdi-download"></span></span></a>
                      
                         <div id="dropdown_delete" class="dropdown icon">
                            <span data-toggle="dropdown" class="icon mdi mdi-more-vert dropdown-toggle" aria-expanded="true"></span>
                            <ul role="menu" class="dropdown-menu multiple-delete-dropdown">
                                <li><a id="" data-toggle="modal" data-target="#people-delete-modal-warning" class="btn btn-default customPeople custom_delete_btn">Delete</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="panel-body table-responsive">
                    <table id="people_tbl_view" class="table table-striped table-hover table-fw-widget" style="width:100%">
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
    <div class="be-spinner">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 66 66" height="40px" width="40px">

                                <circle class="circle" r="25" cy="33" cx="33" stroke-linecap="round" stroke-width="4" fill="none"></circle>
                                </svg>
                            </div>
</div>

 
<!--*****************************MODAL START HERE**********************************************-->

<div id="people-delete-modal-warning" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-warning"><span class="modal-main-icon mdi mdi-alert-triangle"></span></div>
                    <h3>Warning!</h3>
                    <p>Are you sure you want to delete all selected product !</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Cancel</button>
                        <button type="button" id="people_delete_button" class="btn btn-space btn-warning">Proceed</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>



 