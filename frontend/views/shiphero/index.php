<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use common\models\UserConnection;
use yii\db\Query;

$this->title = 'Add Your Shiphero Store';
$this->params['breadcrumbs'][] = 'Add Your Shiphero Store';

?>
<div class="page-head">
    <h2 class="page-head-title">Add Your Shiphero Store</h2>
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
                    </ul>
                    <div class="step-content">
                        <?php if ($is_connect_channel == false): ?>
                            <?php if ($connect_status == UserConnection::IMPORT_STATUS_PROCESSING): ?>
                                <div class="alert alert-light">
                                    Current status is importing...
                                </div>
                            <?php else: ?>
                                <div data-step="1" class="step-pane active">
                                    <?php $form = ActiveForm::begin(['method' => 'post', 'class' => 'form-horizontal group-border-dashed wechatrequestform']); ?>
                                    <div class="form-group row no-margin no-padding main-title">
                                        <div class="col-sm-12">
                                            <label class="control-label">Please provide your Shiphero API details for Authorizing the Integration:</label>
                                        </div>
                                    </div>

                                    <div class="form-group row no-margin">
                                        <label class="col-sm-3 control-label">
                                            API Key
                                        </label>
                                        <div class="col-sm-6">
                                            <?= $form->field($model, 'api_key')->textInput(['placeholder' => 'Please enter your api key', 'class' => 'form-control', 'value' =>'', 'autofocus' => true])->label(false) ?>
                                        </div>
                                    </div>

                                    <div class="form-group row no-margin">
                                        <label class="col-sm-3 control-label">
                                            API Secret
                                        </label>
                                        <div class="col-sm-6">
                                            <?= $form->field($model, 'api_secret')->textInput(['placeholder' => 'Please enter your api secret', 'class' => 'form-control', 'value' =>''])->label(false) ?>
                                        </div>
                                    </div>

                                    <div class="form-group row no-margin">
                                        <a href="/channels" class="btn btn-default btn-space">Cancel</a>
                                        <?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space']) ?>
                                    </div>
                                    <?php ActiveForm::end(); ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div id="bgc_conn_text" class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Shiphero store is connected. </label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
