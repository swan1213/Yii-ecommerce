<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\models\Country;

$this->title = 'Add Your Vtex Store';
$this->params['breadcrumbs'][] = 'Vtex';
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
                        <form id="vtex-authorize-form" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Please provide your Vtex store Credentials to integrate with Elliot:</label>
                                    <p> To integrate Vtex store with Elliot, you'll need to create App Key and App Token in your Vtex store. Please <a target="_blank" href="http://help.vtex.com/en/tutorial/authentication-with-user-and-password-on-the-rest-interface">Follow</a> this link.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Vtex Account Name *</label>
                                <div id="vtex_account" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                    <em class="notes">Please use only account name ex "example.vtexcommercestable"</em>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">App Key *</label>
                                <div id="vtex_app_key" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">App Token *</label>
                                <div id="vtex_app_token" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Store Country *</label>
                                <div id="wizard_vtex_country" class="col-sm-6">
                                    <select class="form-control customer_validate" id="vtex_country"  name="vtex_country" style="color:#989696;">
                                        <option  value="">Please Select Value</option>
                                        <?php $countries = Country::find()->orderBy(['name' => SORT_ASC])->all(); foreach($countries as $val) { ?>
                                            <option value="<?php echo $val->sortname; ?>"><?php echo $val->name; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <input id="user_id" type="hidden" value="<?= Yii::$app->user->identity->id; ?>" />
                            <input id="connection_id" type="hidden" value="<?= $connection_id; ?>" />
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button class="btn btn-default btn-space"><a href='/'>Cancel</a></button>
                                    <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space vtex_auth_connection">Authorize & Connect</button>
                                </div>
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="vtex_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close vtex_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="vtex_ajax_header_msg"></h3>
                    <p id="vtex_ajax_msg"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default vtex_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in vtex_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close vtex_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='vtex_ajax_header_error_msg'></h3>
                    <p id="vtex_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default vtex_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<?php
    $this->registerJsFile('@web/js/store/vtex.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
