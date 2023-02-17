<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\Breadcrumbs;
use backend\models\StoresConnection;
use backend\models\Stores;

$this->title = 'Add Your Google Channel';
$this->params['breadcrumbs'][] = $this->title;
$get_woocommerce_row = Stores::find()->select('store_id')->where(['store_name' => 'Google'])->one();
$woocommerce_store_id = $get_woocommerce_row->store_id;
$checkConnection = StoresConnection::find()->where(['store_id' => $woocommerce_store_id, 'user_id' => Yii::$app->user->identity->id])->one();
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
                    <li data-step="1" class="active">Authorize<span class="chevron"></span></li>
                    <li data-step="2">Connect<span class="chevron"></span></li>
                </ul>
                <div class="step-content">
                    <?php if (empty($checkConnection)) : ?>
                    <div id="bgc_text1" class="form-group no-padding main-title" style="display: none">
                        <div class="col-sm-12">
                            <label class="control-label">Your WooCommerce store is connecting, you will be notified via email when complete.</label>
                        </div>
                    </div>
                    <form id="woocommerce-authorize-form" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                        <div class="form-group no-padding main-title">
                            <div class="col-sm-12">
                                <label class="control-label">Please provide your WooCommerce API details for Authorizing the Integration :</label>
                                <p> To integrate WooCommerce store with Elliot, you'll need to Enable the Rest API<b> WooCommerce > Settings > API :</b>Please <a target="_blank" href="https://help.shopify.com/api/getting-started/authentication/private-authentication">Follow</a> this link.</p>                             
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">WooCommerce Store Name</label>
                            <div id="woocommerce_url" class="col-sm-6">
                                <input type="text" id="woocommerce_store_url" placeholder="Please Enter value" class="form-control customer_validate">
                                <em class="notes">Please use full store url ex "http://WooCommerce.com"</em>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">WooCommerce API Consumer Key *</label>
                            <div id="woocommerce_consumer" class="col-sm-6">
                                <input type="text" placeholder="Please Enter value" class="form-control customer_validate">
                                <em class="notes">Example: ck_2b5b96242b5asdfeqerr8f7c2</em>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">WooCommerce API Consumer Secret *</label>
                            <div id="woocommerce_secret" class="col-sm-6">
                                <input type="text" placeholder="Please Enter value" class="form-control customer_validate">
                                 <em class="notes">Example: cs_42klj23hzh3j32uy9sii37191</em>
                            </div>
                        </div>
                        <input id="woocommerce_user_id" type="hidden" value="<?= Yii::$app->user->identity->id; ?>" />
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button class="btn btn-default btn-space">Cancel</button>
                                <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space wizard-next-auth-store-woocommerce">Authorize & Connect</button>
                            </div> 
                        </div>
                    </form>
                    <?php else: ?>
                    <div id="bgc_conn_text" class="form-group no-padding main-title">
                        <div class="col-sm-12">
                            <label class="control-label">Your WooCommerce store is connected. When data is import you will get a notify message</label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="woocommerce_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close woocommerce_modal_close"></span></button>
        </div>
        <div class="modal-body">
          <div class="text-center">
            <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
            <h3 id="ajax_header_msg">Success!</h3>
            <p id="woocommerce_ajax_msg"></p>
            <div class="xs-mt-50">
              <button type="button" data-dismiss="modal" class="btn btn-space btn-default woocommerce_modal_close">Close</button>
            </div>
          </div>
        </div>
        <div class="modal-footer"></div>
      </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in woocommerce_ajax_request_error" style="display: none;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close woocommerce_error_modal_close"></span></button>
          </div>
          <div class="modal-body">
            <div class="text-center">
              <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
              <h3 id='ajax_header_error_msg'></h3>
              <p id="woocommerce_ajax_msg_eror"></p>
              <div class="xs-mt-50">
                <button type="button" data-dismiss="modal" class="btn btn-space btn-default woocommerce_error_modal_close">Close</button>
              </div>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
    </div>
            