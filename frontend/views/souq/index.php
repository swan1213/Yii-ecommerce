<?php
    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use yii\widgets\Breadcrumbs;
    use common\models\UserConnection;

    $channel_full_name = 'Souq ' . $connection_name;
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

<div class="main-content container-fluid">
    <div class="row wizard-row">
        <div class="col-md-12 fuelux">
            <div class="block-wizard panel panel-default">
                <div id="wizard-store-connection" class="wizard wizard-ux">
                    <ul class="steps">
                        <li data-step="1" class="active">Authorize<span class="chevron"></span></li>
                        <!--<li data-step="2">Connect<span class="chevron"></span></li>-->
                    </ul>
                    <div class="step-content">
                        <?php if ($is_connect_channel == false): ?>
                            <?php if ($connect_status == UserConnection::IMPORT_STATUS_PROCESSING): ?>
                                <div class="alert alert-light">
                                    We are importing...
                                </div>
                            <?php else: ?>
                                <?php $form = ActiveForm::begin(['class' => 'form-horizontal group-border-dashed']); ?>
                                    <div class="form-group row no-padding main-title">
                                        <div class="col-sm-12">
                                            <label class="control-label">Please provide your <?php echo $channel_full_name; ?> API details for Authorizing the Integration:</label>

                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-3 control-label">Your <?php echo $channel_full_name; ?> App Id  *</label>
                                        <div class="col-sm-6">
                                            <?= $form->field($model, 'client_id')->textInput(['placeholder' => 'App Id', 'class' => 'form-control', 'value' =>'63499381', 'autofocus' => true])->label(false) ?>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-3 control-label">Your <?php echo $channel_full_name; ?> App Secret *</label>
                                        <div class="col-sm-6">
                                            <?= $form->field($model, 'client_secret')->passwordInput(['placeholder' => 'App Secret', 'class' => 'form-control', 'value' =>'Tyoh35opRGe8RP4gHt0A'])->label(false) ?>
                                        </div>
                                    </div>
      
                                    <?php if ($error_status == true) : ?>
                                        <div class="form-group row">
                                            <div class="col-sm-9">
                                                <div class="alert alert-danger">
                                                    <?= $error_msg ?>
                                                </div>  
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <a href="/channels" class="btn btn-default btn-space">Cancel</a>
                                            <?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space']) ?>
                                        </div> 
                                    </div>
                                <?php ActiveForm::end(); ?>
                            <?php endif; ?>
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
