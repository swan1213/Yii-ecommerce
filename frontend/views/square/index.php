<?php
    use yii\helpers\Html;
    use yii\widgets\Breadcrumbs;
    use yii\widgets\ActiveForm;
    use common\models\UserConnection;

    $this->title = 'Add Your Square Store';
    $this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
    $this->params['breadcrumbs'][] = ['label' => 'Integrations', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
    $this->params['breadcrumbs'][] = ['label' => 'POS', 'url' => ['/integrations/pos-all']];
    $this->params['breadcrumbs'][] = 'Square';

?>
<style type="text/css">
    #square_publish_form .check-list {
        max-height: 300px;
        overflow-y: auto;
    }

    #square_publish_form #souqpublishform-connections label {
        width: 100%;
    }
</style>
<div class="page-head">
    <h2 class="page-head-title">Add Your Square Store</h2>
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
                        <li data-step="2" <?php if($is_connect_channel) echo 'class="active"'?>>Connected<span class="chevron"></span></li>
                    </ul>
                    <div class="step-content">
                        <?php if ($is_connect_channel == false): ?>
                            <?php $form = ActiveForm::begin([
                                'method' => 'post',
                                'action' => ['/square/authorize', 'id' => $id],
                                'id' => 'square_connection_form',
                                'class' => 'form-horizontal group-border-dashed'
                            ]); ?>
                                <div class="form-group row no-margin no-padding main-title">
                                    <div class="col-sm-12">
                                        <label class="control-label">Please provide your Square APP account details for Authorizing the Integration :</label>
                                        <p> To integrate Square channel with Elliot, you'll need to create an Application on Square account Please <a target="_blank" href="https://docs.connect.squareup.com/">Follow</a> this link.</p>  
                                    </div>
                                </div>

                                <div class="form-group row no-margin">
                                    <label class="col-sm-3 control-label">
                                        Application ID
                                    </label>
                                    <div class="col-sm-6">
                                        <?= $form->field($model, 'app_id')->textInput(['placeholder' => 'Please enter your application id', 'class' => 'form-control', 'autofocus' => true])->label(false) ?>
                                        <em class="notes">Example: sq0idp-v4bwgtoYy6LQ1Dr0rud67g</em>
                                    </div>
                                </div>

                                <div class="form-group row no-margin">
                                    <label class="col-sm-3 control-label">
                                        Personal Access Token
                                    </label>
                                    <div class="col-sm-6">
                                        <?= $form->field($model, 'access_token')->textInput(['placeholder' => 'Please enter app access token', 'class' => 'form-control'])->label(false) ?>
                                        <em class="notes">Example: sq0atp-vhSaMlFhSdZT5wbovp2QeA</em>
                                    </div>
                                </div>

                                <div class="form-group row no-margin">
                                    <a href="/integrations/pos-all" class="btn btn-default btn-space">Cancel</a>
                                    <?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space']) ?>
                                </div>
                            <?php ActiveForm::end(); ?>
                        <?php else: ?>
                            <div id="bgc_conn_text" class="form-group row main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Square store is connected. Please select the channels to publish to square.</label>
                                </div>
                            </div>

                            <?php $publish_form = ActiveForm::begin([
                                'method' => 'post',
                                'action' => ['/square/publish', 'id' => $id],
                                'id' => 'square_publish_form',
                                'class' => 'form-horizontal group-border-dashed'
                            ]); ?>
                                <div class="form-group row main-title">
                                    <div class="col-sm-12">
                                        <label class="control-label">Please select the channels to publish to square</label>
                                    </div>
                                </div>

                                <div class="form-group row check-list">
                                    <div class="col-sm-12">
                                        <?= $publish_form->field($publish_model, 'connections')->checkboxList(
                                            $connection_list,
                                            [
                                                'item'=>function ($index, $label, $name, $checked, $value){
                                                    return Html::checkbox($name, $checked, [
                                                        'value' => $value,
                                                        'label' => $label,
                                                        'class' => 'channel-item',
                                                    ]);
                                                }    
                                            ]
                                        ) ?>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <a href="/integrations/pos-all" class="btn btn-default btn-space">Cancel</a>
                                    <?= Html::submitButton('Publish', ['class' => 'btn btn-primary btn-space']) ?>
                                </div>
                            <?php ActiveForm::end(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="square_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close square_modal_close"></span></button>
            </div>

            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="ajax_header_msg">Success!</h3>
                    <p id="square_ajax_msg"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default square_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="square_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in square_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close square_error_modal_close"></span></button>
            </div>

            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='square_ajax_header_error_msg'></h3>
                    <p id="square_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default square_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<?php
    $this->registerJsFile('@web/js/channels/square.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>