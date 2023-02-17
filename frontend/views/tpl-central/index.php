<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use common\models\Fulfillment;
use common\models\FulfillmentList;

$this->title = 'Add Your 3PL Store';
$this->params['breadcrumbs'][] = '3PL';
          
$checkConnection = Fulfillment::find()->select('id')->where(['name' => '3PL Central', 'user_id' => Yii::$app->user->identity->id])->one();
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
                    <li data-step="1" class="active">Authorize<span class="chevron"></span></li>
                    <!--li data-step="2">Connect<span class="chevron"></span></li-->
                </ul>
                <div class="step-content">
                    <input id="user_id" type="hidden" value="<?= Yii::$app->user->identity->id; ?>" />
                    <?php if (empty($checkConnection)) : ?>
                        <form id="tpl-authorize-form" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Please provide your 3PL store Credentials to integrate with Elliot:</label>
                                    <p> To integrate 3PL store with Elliot, you'll need to create App Key and App Secret in your 3PL store. Please <a target="_blank" href="http://3plcentral.com/">Follow</a> this link.</p>
                                </div>
                            </div>      
                            <div class="form-group" style="display: none;">
                                <label class="col-sm-3 control-label">Client ID</label>
                                <div id="tpl_client_id" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                </div>
                            </div>           
                            <div class="form-group" style="display: none;">
                                <label class="col-sm-3 control-label">Client Secret</label>
                                <div id="tpl_client_secret" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                </div>
                            </div>           
                            <div class="form-group">
                                <label class="col-sm-3 control-label">3PL Key</label>
                                <div id="tpl_key" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">                   
                                </div>
                            </div>           
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Encoded Client ID and Secret</label>
                                <div id="tpl_encoded" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                </div>
                            </div>           
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button class="btn btn-default btn-space"><a href='/channels'>Cancel</a></button>
                                    <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space tpl_auth_connection">Authorize & Connect</button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div id="bgc_conn_text" class="form-group no-padding main-title">
                            <div class="col-sm-12">
                                <label class="control-label">Your 3PL store is now connected. When orders has been fullfilled to 3PL Centrals, you will receive a confirmation notification.</label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>                 
<div id="tpl_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close tpl_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="tpl_ajax_header_msg">Success!</h3>
                    <p id="tpl_ajax_msg">Success! Your 3PL has been connected successfully. Orders has been fullfilled to 3PL Central.</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default tpl_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in tpl_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close tpl_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='tpl_ajax_header_error_msg'>Error</h3>
                    <p id="tpl_ajax_msg_eror">Error Something went wrong. Please try again</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default tpl_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
