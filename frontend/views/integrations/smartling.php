<?php

use backend\models\User;
use yii\widgets\Breadcrumbs;
use common\models\Smartling;
use common\models\UserConnection;

$this->title = 'Translations';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Integrations', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Translations', 'url' => ['/integrations/translation-all']];
$this->params['breadcrumbs'][] = 'Smartling';

$user_id = Yii::$app->user->identity->id;
$account_id = $sm_user_id = $secret_key = '';
$smartlingData = Smartling::find()->Where(['user_id' => $user_id])->one();
if (!empty($smartlingData)) {
    $account_id = $smartlingData->account_id;
    $sm_user_id = $smartlingData->sm_user_id;
    $secret_key = $smartlingData->secret_key;
}
$smartling_details = Smartling::find()->Where(['user_id' => $user_id])->all();
?>
<div class="page-head">
    <h2 class="page-head-title">Smartling</h2>
    <ol class="breadcrumb page-head-nav">
<?php
echo Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]);
?>
    </ol>
</div>

<!--Swtich Component-->
<div class="row wizard-row proidget">
    <div class="col-md-12 fuelux">
        <div class="main-content container-fluid">
            <!--Tabs-->
            <div class="row">
                <!--Default Tabs-->
                <div class="col-sm-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">General Information </div>
                        <div class="tab-container">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab1" data-toggle="tab">Account Specific Details</a></li>
                                <li><a href="#tab2" data-toggle="tab">Jobs History</a></li>
                            </ul>
                            <div class="tab-content">
                                <div id="tab1" class="tab-pane active cont">
                                    <div class="panel-body">
                                        <div class="col-md-12">
                                            <label class="col-sm-3 control-label panel-heading">Account ID</label>
                                            <input type="text" class="form-control" style="width:60%"  disabled="disabled" value="<?= $account_id; ?>" />
                                        </div>
                                        <div class="col-md-12">
                                            <label class="col-sm-3 control-label panel-heading">Keys</label>
                                            <input type="text" class="form-control" style="width:60%"  disabled="disabled" value="<?= $sm_user_id; ?>" />
                                        </div>
                                        <div class="col-md-12">
                                            <label class="col-sm-3 control-label panel-heading">Secret Key</label>
                                            <input type="text" class="form-control" style="width:60%"  disabled="disabled" value="<?= $secret_key; ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div id="tab2" class="tab-pane cont">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="panel panel-default panel-table">
                                                <div class="panel-heading">Default
                                                    <div class="tools"><span class="icon mdi mdi-download"></span><span class="icon mdi mdi-more-vert"></span></div>
                                                </div>
                                                <div class="panel-body">
                                                    <table id="table1" class="table table-striped table-hover table-fw-widget">
                                                        <thead>
                                                            <tr>
                                                                <th>Job ID</th>
                                                                <th>Selected Channel</th>
                                                                <th>Selected Language</th>
                                                                <th>Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
							<?php if (!empty($smartling_details)) { ?>
								<?php
								foreach ($smartling_details as $Smartling_value) {
								    $status='';
                                    $label='';
								    $user_connection = UserConnection::find()->Where(['id' => $Smartling_value->user_connection_id])->one();
                                    $channel = $user_connection->getPublicName();
                                    $status = $Smartling_value->job_download_status;
                                    if($status=="pending"){
                                        $label= 'label-primary';
                                        $status='In Progress';
                                    }
								    else{
                                        $label= 'label-success';
                                    }
								    ?>
                                    <tr class="odd gradeX">
                                        <td><?= $channel ?></td>
                                        <td><?= $Smartling_value->token; ?></td>
                                        <td class="center"><?= $Smartling_value->target_locale ?></td>
                                        <td class="center"><span class="label  <?= $label; ?>"><?= $status; ?></td>
                                    </tr>
							<?php }
							}else{
                                    ?>

                                    <tr class="odd"><td valign="top" colspan="6" style="text-align:center;" class="dataTables_empty">No data available in table</td></tr>

                                    <?php
                            }

							?>
						    </table>
						</div>
					    </div>
					</div>
				    </div>
				</div>
			    </div>
			</div>
		    </div>
		</div>
	    </div>
	</div>
    </div>
</div>







