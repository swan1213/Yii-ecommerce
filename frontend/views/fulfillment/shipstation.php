<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = 'Add Your ShipStation';
$this->params['breadcrumbs'][] = 'ShipStation';


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
<div class="row wizard-row">
    <div class="col-md-12 fuelux">
        <div class="block-wizard panel panel-default">
            <div id="wizard-store-connection" class="wizard wizard-ux">
                <ul class="steps">
                    <li data-step="1" class="active">Connect<span class="chevron"></span></li>
                    <!--li data-step="2">Connect<span class="chevron"></span></li-->
                </ul>
                <div class="step-content">
                    <input id="user_id" type="hidden" value="<?php echo $userId; ?>" />
                    <?php if (empty($checkConnection)) : ?>
                        <form id="shipstation-authorize-form" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Please provide your ShipStation API Credentials to integrate with Elliot:</label>
                                    <p> To integrate ShipStation with Elliot, you'll need to create API Key and API Secret on your ShipStation Account. Please <a target="_blank" href="https://help.shipstation.com/hc/en-us/articles/206638917-How-can-I-get-access-to-ShipStation-s-API">Follow</a> this link.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">ShipStation API Key *</label>
                                <div id="shipstation_key" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">ShipStation API Secret *</label>
                                <div id="shipstation_secret" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button class="btn btn-default btn-space"><a href='/channels'>Cancel</a></button>
                                    <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space shipstation_auth_connection">Authorize & Connect</button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div id="bgc_conn_text" class="form-group no-padding main-title">
                            <div class="col-sm-12">
                                <label class="control-label">Your ShipStation is now connected. When orders has been fullfilled to ShipStation, you will receive a confirmation notification.</label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="shipstation_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close shipstation_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="shipstation_ajax_header_msg">Success!</h3>
                    <p id="shipstation_ajax_msg">Success! Your ShipStation has been connected successfully. Orders has been fullfilled to ShipStation.</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default shipstation_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in shipstation_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close shipstation_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='shipstation_ajax_header_error_msg'>Error</h3>
                    <p id="shipstation_ajax_msg_eror">Error Something went wrong. Please try again</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default shipstation_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<?php
$this->registerJsFile('@web/js/fulfillment/shipstation.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
