<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use common\models\UserConnection;

$this->title = 'Add Your Rakuten Channel';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-head">
    <h2 class="page-head-title"><?=$this->title?></h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget(['links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [], ]);
        ?>
    </ol>
</div>

<div class="main-content container-fluid">
    <div class="row wizard-row">
        <div class="col-md-12 fuelux wechat12">
            <div class="block-wizard panel panel-default">
                <div id="wizard1" class="wizard wizard-ux">
                    <ul class="steps">
                        <li data-step="1" class="active">Authorize<span class="chevron"></span></li>
                        <li data-step="2" <?php if(!empty($userConnection)) echo 'class="active"'?>>Connected<span class="chevron"></span></li>
                    </ul>
                    <div class="step-content">
                        <?php if (empty($userConnection)):
                            $form = ActiveForm::begin(['method' => 'post', 'class' => 'form-horizontal group-border-dashed auth-rakuten']); ?>
                                <div class="form-group row no-margin no-padding main-title">
                                    <div class="col-sm-12">
                                        <label class="control-label">Please provide your Rakuten API details for Authorizing the Integration:</label>
                                        <p> To integrate Rakuten channel with Elliot, you'll need to create an Rakuten Seller Account. Please follow this <a target="_blank" href="https://mainmenu.rms.rakuten.co.jp/rms">link</a>.</p>

                                        <div role="alert" class="alert alert-primary alert-dismissible">
                                            <span class="icon mdi mdi-info-outline"></span>
                                            Also you need to agree the terms and allow api functions on your Rakuten. Click to
                                            <i><u><a style="color: #fff;" href="/guide/RMS.zip" download><b>Download</b></a></u></i> the guide.
                                        </div>
                                    </div>

                                </div>

                                <div class="form-group row no-margin">
                                    <label class="col-sm-3 control-label">
                                        Sevice Secret *
                                    </label>
                                    <div class="col-sm-6">
                                        <?= $form->field($model, 'sevice_secret')->textInput(['placeholder' => 'Please enter your service secret', 'class' => 'form-control', 'autofocus' => true])->label(false) ?>
                                    </div>
                                </div>

                                <div class="form-group row no-margin">
                                    <label class="col-sm-3 control-label">
                                        License Key *
                                    </label>
                                    <div class="col-sm-6">
                                        <?= $form->field($model, 'license_key')->textInput(['placeholder' => 'Please enter license key', 'class' => 'form-control'])->label(false) ?>
                                    </div>
                                </div>

                                <div class="form-group row no-margin">
                                    <a href="/channels" class="btn btn-default btn-space">Cancel</a>
                                    <?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space']) ?>
                                </div>
                                <?php ActiveForm::end(); ?>
                        <?php else: ?>
                            <div id="bgc_conn_text" class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Rakuten store is connected. </label>
                                </div>
                                <?php if ($userConnection->import_status == UserConnection::IMPORT_STATUS_PROCESSING): ?>
                                    <div class="alert alert-light">
                                        We are importing ...
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div tabindex="-1" role="dialog" class="modal fade in rakuten_modal" style="display: <?php echo($new_auth? "block":"none") ?>;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close tpl_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="tpl_ajax_header_msg">Success!</h3>
                    <p id="tpl_ajax_msg">Success! Your Rakuten has been connected successfully.</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default rakuten_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div tabindex="-1" role="dialog" class="modal fade in rakuten_modal" style="display: <?php echo($error_status?"block":"none") ?>;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close tpl_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='tpl_ajax_header_error_msg'>Error</h3>
                    <p id="tpl_ajax_msg_eror"><?= $error_msg?></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default rakuten_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>