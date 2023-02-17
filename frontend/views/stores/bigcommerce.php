<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = 'Add Your BigCommerce Store';
$this->params['breadcrumbs'][] = $this->title;

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
                </ul>
                <div class="step-content">
                    <div data-step="1" class="step-pane active">
                          <div id="bgc_text1" class="form-group no-padding main-title" style="display: none">
                              <div class="col-sm-12">
                                  <label class="control-label">Your BigCommerce store is connecting, you will be notified via email when complete.</label>
                              </div>
                          </div>
                          <form id="bigcommerce-authorize-form" action="" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                              <div class="form-group no-padding main-title">
                                  <div class="col-sm-12">
                                      <label class="control-label">Please provide your Bigcommerce API Account details for Authorizing the Integration :</label>
                                      <p> To integrate BigCommerce with Elliot, you'll need to create API credentials in your Bigcommerce account via <b> Advanced Settings > API Accounts > Create API Account </b>.</p>                             </div>
                              </div>
                              <div class="form-group">
                                  <label class="col-sm-3 control-label">BigCommerce API Path *</label>
                                  <div id="big_api_path" class="col-sm-6">
                                      <input type="text" placeholder="Please Enter value" class="form-control">
                                      <em class="notes">Please use full url <b>https://api.bigcommerce.com/stores/xxxxxxxxxx/v3/</b></em>
                                  </div>
                              </div>
                              <div class="form-group">
                                  <label class="col-sm-3 control-label">BigCommerce API Access Token *</label>
                                  <div id="big_access_token" class="col-sm-6">
                                      <input type="text" placeholder="Please Enter value" class="form-control">

                                  </div>
                              </div>
                              <div class="form-group">
                                  <label class="col-sm-3 control-label">BigCommerce API Client ID *</label>
                                  <div id="big_client_id" class="col-sm-6">
                                      <input type="text" placeholder="Please Enter value" class="form-control">
                                      <a href="bigcommerce.php"></a>
                                  </div>
                              </div>
                              <div class="form-group">
                                  <label class="col-sm-3 control-label">BigCommerce API Client Secret *</label>
                                  <div id="big_client_secret" class="col-sm-6">
                                      <input type="text" placeholder="Please Enter value" class="form-control">
                                  </div>
                              </div>
                              <div class="form-group">
                                  <div class="col-sm-12">
                                      <input id="user_id" type="hidden" value="<?= Yii::$app->user->identity->id; ?>" />
                                      <input id="connection_id" type="hidden" value="<?= $connection_id; ?>" />
                                      <button class="btn btn-default btn-space empty-the-bigcommerce-form">Cancel</button>
                                      <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space wizard-next-auth-store-bgcmrc">Authorize & Connect</button>
                                  </div>
                              </div>
                          </form>
                    </div>
                    <div data-step="2" class="step-pane">

                        <div id="bgc_text2" class="form-group no-padding main-title" style="display: none">
                            <div class="col-sm-12">
                                <label class="control-label">Your BigCommerce store is connecting, you will be notified via email when complete.</label>
                            </div>
                        </div>

                        <form id="bigcommerce-connect-form" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Connect Your Store:</label>
                                </div> 
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Your BigCommerce Store Hash</label>
                                <div id="big_store_hash" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button class="btn btn-default btn-space">Cancel</button>
                                    <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space wizard-next-connect-store-bgcmrc">Connect</button>
                                </div>
                            </div>
                            <input id="user_id" type="hidden" value="<?= Yii::$app->user->identity->id; ?>" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<button id="click-connect-modal" data-toggle="modal" data-target="#connect-modal" type="button" class="btn btn-space btn-primary" style="display: none;">Primary</button>
<div id="connect-modal" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-primary"><span class="modal-main-icon mdi mdi-info-outline"></span></div>
                    <h3>Information!</h3>
                    <p>Elliot is importing your data, you can close this and navigate away from this page.<br> You will be notified when your import is complete.</p>

                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div id="bigcommerce_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in bigcommerce_ajax_request_error" style="display: none;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close bigcommerce_error_modal_close"></span></button>
          </div>
          <div class="modal-body">
            <div class="text-center">
              <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
              <h3 id='bigcommerce_ajax_header_error_msg'></h3>
              <p id="bigcommerce_ajax_msg_eror"></p>
              <div class="xs-mt-50">
                <button type="button" data-dismiss="modal" class="btn btn-space btn-default bigcommerce_error_modal_close">Close</button>
              </div>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>

<?php
    $this->registerJsFile('@web/js/store/bigcommerce.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
