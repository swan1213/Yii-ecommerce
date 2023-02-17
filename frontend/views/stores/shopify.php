<?php

use common\models\Country;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

$this->title = 'Add Your Shopify Store';
$this->params['breadcrumbs'][] = 'Shopify';

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
                    <form id="shopify-authorize-form" action="" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                        <div class="form-group no-padding main-title">
                            <div class="col-sm-12">
                                <label class="control-label">Please provide your Shopify API Account details for Authorizing the Integration :</label>
                                <p>To integrate Shopify with Elliot, you'll need to create API credentials in your Shopify account via App, Manage private apps. Please <a target="_blank" href="https://help.shopify.com/api/getting-started/authentication/private-authentication">Follow</a> this link.</p>                             
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Shopify Shop Name *</label>
                            <div id="shopify_shop" class="col-sm-6">
                                <input type="text" id="shop_url" placeholder="Please Enter value" class="form-control">
                                <em class="notes">Please use shop url including <b>.myshopify.com</b> without https://</em>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Shopify API Key *</label>
                            <div id="shopify_api" class="col-sm-6">
                                <input type="text" placeholder="Please Enter value" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Password *</label>
                            <div id="shopify_pass" class="col-sm-6">
                                <input type="text" placeholder="Please Enter value" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Shared Secret *</label>
                            <div id="shopify_shared_secret" class="col-sm-6">
                                <input type="text" placeholder="Please Enter value" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Store Country *</label>
                            <div id="wizard_shopify_country" class="col-sm-6">
                                <select class="form-control customer_validate" id="shopify_country"  name="shopify_country" style="color:#989696;">
                                    <option  value="">Please Select Value</option>
                                    <?php $countries = Country::find()->orderBy(['name' => SORT_ASC])->all(); foreach($countries as $val) { ?>
                                        <option value="<?php echo $val->sortname; ?>"><?php echo $val->name; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Enable Shopify Plus</label>
                            <div class="col-sm-6">
                                <div class="be-checkbox inline">
                                    <input id="enable_shopify_plus" type="checkbox" <?php echo $plusCheck;?>>
                                    <label for="enable_shopify_plus"></label>
                                </div>
                            </div>
                        </div>
                        <input id="user_id" type="hidden" value="<?= Yii::$app->user->identity->id; ?>" />
                        <input id="connection_id" type="hidden" value="<?= $connection_id; ?>" />
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button class="btn btn-default btn-space reset-shopify-form-on-cancel"><a href='/'>Cancel</a></button>
                                <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space wizard-next-auth-store-shopify">Authorize & Connect</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="shipify_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close shipify_modal_close"></span></button>
        </div>
        <div class="modal-body">
          <div class="text-center">
            <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
            <h3 id="ajax_header_msg">Success!</h3>
            <p id="shopify_ajax_msg"></p>
            <div class="xs-mt-50">
              <button type="button" data-dismiss="modal" class="btn btn-space btn-default shipify_modal_close">Close</button>
            </div>
          </div>
        </div>
        <div class="modal-footer"></div>
      </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in shipify_ajax_request_error" style="display: none;">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close shipify_error_modal_close"></span></button>
          </div>
          <div class="modal-body">
            <div class="text-center">
              <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
              <h3 id='ajax_header_error_msg'></h3>
              <p id="shopify_ajax_msg_eror"></p>
              <div class="xs-mt-50">
                <button type="button" data-dismiss="modal" class="btn btn-space btn-default shipify_error_modal_close">Close</button>
              </div>
            </div>
          </div>
          <div class="modal-footer"></div>
        </div>
      </div>
</div>

<?php
$this->registerJsFile('@web/js/store/shopify.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
