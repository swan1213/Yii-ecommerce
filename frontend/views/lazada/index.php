<?php
    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use yii\widgets\Breadcrumbs;
    use common\models\UserConnection;

    $channel_full_name = 'Lazada ' . $connection_name;
    $this->title = 'Add Your ' . $channel_full_name . ' Store';
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

<div class="LazadaCustom">
    <div class="row wizard-row">
        <div class="col-md-12 fuelux">
            <div class="block-wizard panel panel-default">
                <div id="wizard-store-connection" class="wizard wizard-ux">
                    <ul class="steps">
                        <li data-step="1" class="active">Authorize<span class="chevron"></span></li>
                        <li data-step="2" <?php if($is_connect_channel) echo 'class="active"'?>>Connected<span class="chevron"></span></li>
                    </ul>
                    <div class="step-content">
                        <?php if ($is_connect_channel == false): ?>
                            <?php $form = ActiveForm::begin([
                                'method' => 'post',
                                'action' => ['/lazada/authorize', 'id' => $id],
                                'id' => 'lazada_connection_form',
                                'class' => 'form-horizontal group-border-dashed'
                            ]); ?>
                                <div class="form-group row no-padding main-title">
                                    <div class="col-sm-12">
                                        <label class="control-label">Please provide your <?php echo $channel_full_name; ?> API details for Authorizing the Integration:</label>

                                    </div>
                                </div>
                                <div class="be-spinner" style="width:83%; text-align: center; right:auto">
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 control-label"><?php echo $channel_full_name; ?> User Email  *</label>
                                    <div id="lazada_url" class="col-sm-6">
                                        <?= $form->field($model, 'email')->textInput(['placeholder' => 'Please Enter your email', 'class' => 'form-control customer_validate', 'autofocus' => true])->label(false) ?>
                                        <em class="notes">Example: info@fauxfreckles.com</em>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 control-label"><?php echo $channel_full_name; ?> Store API  Key *</label>
                                    <div id="lazada_consumer" class="col-sm-6">
                                        <?= $form->field($model, 'api_key')->passwordInput(['placeholder' => 'Please Enter value', 'class' => 'form-control customer_validate'])->label(false) ?>
                                        <em class="notes">Example: JeVfOHaBm0RdndrqR1HsZGyL20xHwGSxi3gngcsvOaCnPE_1nsf2Yb7f</em>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <a href="/channels" class="btn btn-default btn-space">Cancel</a>
                                        <?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space wizard-next-auth-store-lazada']) ?>
                                    </div> 
                                </div>
                            <?php ActiveForm::end(); ?>
                        <?php else: ?>
                            <div id="bgc_conn_text" class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Your <?php echo $channel_full_name ?> store is connected. </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="lazada_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close lazada_modal_close"></span></button>
            </div>

            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="ajax_header_msg">Success!</h3>
                    <p id="lazada_ajax_msg"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default lazada_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="lazada_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in lazada_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close lazada_error_modal_close"></span></button>
            </div>

            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='lazada_ajax_header_error_msg'></h3>
                    <p id="lazada_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default lazada_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<?php
    $this->registerJsFile('@web/js/channels/lazada.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>