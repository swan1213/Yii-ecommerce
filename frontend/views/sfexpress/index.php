<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use common\models\Fulfillment;
use common\models\SfexpressRate;

$this->title = 'Your SF Express fulfillment';
$this->params['breadcrumbs'][] = 'SF Express';
$checkConnection = '';
$Fulfillment_check = Fulfillment::find()->where(['name' => 'SF Express'])->one();

//echo  '<pre>'; print_r($Fulfillment_check); echo '</pre>';
?>
<input type="hidden" id="hidURL" value="<?php echo $_SERVER['HTTP_HOST']; ?>">
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
<div class="row wizard-row">
    <div class="col-md-12 fuelux">
        <div class="block-wizard panel panel-default">
            <div id="wizard-store-connection" class="wizard wizard-ux">
                <ul class="steps">
				<?php if(!empty($Fulfillment_check)){ ?> 
				<li data-step="1" class="active">Authorize<span class="chevron"></span></li>
				<?php } else { ?>
                    <li data-step="1" class="active">Authorize<span class="chevron"></span></li>
				<?php } ?>
                </ul>
                <div class="step-content">
                    <input id="user_id" type="hidden" value="<?= Yii::$app->user->identity->id; ?>" />
                    <div data-step="1" class="step-pane active">
                         <form novalidate=""  id="SfExpress-authorize-form" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                                <div class="form-group no-padding main-title">
                                    <div class="col-sm-12">
                                        <label class="control-label">Please provide your SF Express  details for Authorizing the Integration:</label>
                                        <p>To integrate SF Express Fullfillment with Elliot, </p>                             
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">SF Express UserName:</label>
                                    <div id="SfExpress_username" class="col-sm-6">
                                        <input type="text" placeholder="Please Enter value" class="form-control mercado_validate" value="<?php echo !empty($connectionModel)?$connectionModel->connection_info['username']:''?>">
                                      
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">SF Express Password:</label>
                                    <div id="SfExpress_password" class="col-sm-6">
                                        <input type="text" placeholder="Please Enter value" class="form-control mercado_validate" value="<?php echo !empty($connectionModel)?$connectionModel->connection_info['password']:''?>">
                                        
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <button class="btn btn-default btn-space"><a href='/fulfillment/carriers'>Cancel</a></button>
                                        <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space sf_express_auth_connection">Authorize & Connect</button>
                                    </div>
                                </div>
								
                            </form>
                         </div>
                     </div>
            </div>
        </div>
    </div>
</div>
<div id="SfExpress_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close SfExpress_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="ajax_header_msg">Success!</h3>
                    <p id="SfExpress_ajax_msg">Success! Your SF-Express has been connected successfully.</p>
                    <div class="xs-mt-50">
<!--                        <a href="sfexpress">Next</a>-->
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default sf_express_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in SfExpress_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close SfExpress_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='ajax_header_error_msg'>Error</h3>
                    <p id="SfExpress_ajax_msg_eror">Error Something went wrong. Please try again</p>
                    <div class="xs-mt-50">
                      <button type="button" data-dismiss="modal" class="btn btn-space btn-default SfExpress_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
