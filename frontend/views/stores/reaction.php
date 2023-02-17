<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\models\Country;

$this->title = 'Add Your Reaction Store';
$this->params['breadcrumbs'][] = 'Reaction';
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
                        <form id="reaction-authorize-form" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Please provide your Reaction store url to integrate with Elliot:</label>
                                    <p> To integrate Reaction store with Elliot, you'll need to install meteor:simple package in your Reaction store console. Please <a target="_blank" href="https://github.com/stubailo/meteor-rest/blob/master/packages/rest/README.md">Follow</a> this link.</p>
                                    <p> If you need an help. Please contact with <a target="_blank" href="mailto:<?php echo env('ADMIN_EMAIL')?>">Elliot Support Team</a>.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Your Reaction Store Url *</label>
                                <div id="reaction_url" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                    <em class="notes">Please use your store full url ex: "http://example.reaction.store"</em>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Your Account Email *</label>
                                <div id="reaction_user_email" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Your Account Password *</label>
                                <div id="reaction_user_pwd" class="col-sm-6">
                                    <input type="text" placeholder="Please Enter value" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Store Country *</label>
                                <div id="wizard_reaction_country" class="col-sm-6">
                                    <select class="form-control customer_validate" id="reaction_country"  name="cu-Country" style="color:#989696;">
                                        <option  value="">Please Select Value</option>
                                        <?php
                                            $countries = Country::find()->orderBy(['name' => SORT_ASC])->all();
                                            foreach($countries as $val) {
                                        ?>
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
                                    <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space reaction_auth_connection">Authorize & Connect</button>
                                </div>
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="reaction_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close reaction_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="reaction_ajax_header_msg"></h3>
                    <p id="reaction_ajax_msg"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default reaction_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in reaction_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close reaction_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='reaction_ajax_header_error_msg'></h3>
                    <p id="reaction_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default reaction_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<?php
    $this->registerJsFile('@web/js/store/reaction.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
