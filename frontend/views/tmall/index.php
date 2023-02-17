<?php

	use yii\helpers\Html;
	use yii\widgets\Breadcrumbs;
	use yii\widgets\ActiveForm;
	use common\models\UserConnection;

	$this->title = 'Add Your TMall Store';
	$this->params['breadcrumbs'][] = 'Add Your TMall Store';

?>
<div class="page-head">
	<h2 class="page-head-title">Add Your TMall Store</h2>
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
							<?php if ($connect_status == UserConnection::IMPORT_STATUS_PROCESSING): ?>
                                <div class="alert alert-light">
                                    We are importing...
                                </div>
                            <?php else: ?>
                            	<?php $form = ActiveForm::begin(['method' => 'post', 'class' => 'form-horizontal group-border-dashed wechatrequestform']); ?>
									<div class="form-group row no-margin no-padding main-title">
	                                    <div class="col-sm-12">
	                                        <label class="control-label">Please provide your <?php echo $connection_name; ?> API details for Authorizing the Integration:</label>
                                            <p> To integrate TMall channel with Elliot, you'll need to create an TMall Seller Account. Please follow this <a target="_blank" href="http://www.alibaba.com">link</a>.</p>  
	                                    </div>
	                                </div>

	                                <div class="form-group row no-margin">
										<label class="col-sm-3 control-label">
											App Key *
										</label>
										<div class="col-sm-6">
											<?= $form->field($model, 'app_key')->textInput(['placeholder' => 'Please enter your application key', 'class' => 'form-control', 'value' =>'1024582689', 'autofocus' => true])->label(false) ?>
										</div>
									</div>

									<div class="form-group row no-margin">
										<label class="col-sm-3 control-label">
											App Secret *
										</label>
										<div class="col-sm-6">
											<?= $form->field($model, 'app_secret')->textInput(['placeholder' => 'Please enter application secret', 'class' => 'form-control', 'value' =>'sandboxb58505c180cfe4593a974ccdf'])->label(false) ?>
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

									<div class="form-group row no-margin">
										<a href="/channels" class="btn btn-default btn-space">Cancel</a>
										<?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space']) ?>
									</div>
								<?php ActiveForm::end(); ?>
                            <?php endif; ?>	
						<?php else: ?>
							<div id="bgc_conn_text" class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">TMall store is connected. </label>
                                </div>
                            </div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
