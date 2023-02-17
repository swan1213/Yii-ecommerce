<?php

	use yii\helpers\Html;
	use yii\widgets\Breadcrumbs;
	use yii\widgets\ActiveForm;
	use common\models\UserConnection;

	$this->title = 'Add Your Amazon Store';
	$this->params['breadcrumbs'][] = 'Add Your Amazon Store';

?>
<div class="page-head">
	<h2 class="page-head-title">Add Your Amazon Store</h2>
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
                        		'action' => ['/amazon/authorize', 'id' => $id],
								'id' => 'amazon_connection_form',
                        		'class' => 'form-horizontal group-border-dashed'
                        	]); ?>
								<div class="form-group row no-margin no-padding main-title">
                                    <div class="col-sm-12">
                                        <label class="control-label">Please provide your <?php echo $connection_name; ?> API details for Authorizing the Integration:</label>
                                        <p> To integrate Amazon channel with Elliot, you'll need to create an Amazon Seller Account. Please follow this <a target="_blank" href="https://sellercentral.amazon.com/gp/homepage.html">link</a>.</p>  
                                    </div>
                                </div>

                                <div class="form-group row no-margin">
									<label class="col-sm-3 control-label">
										Seller ID *
									</label>
									<div class="col-sm-6">
										<?= $form->field($model, 'merchant_id')->textInput(['placeholder' => 'Please enter your seller id', 'class' => 'form-control', 'autofocus' => true])->label(false) ?>
										<em class="notes">Example: A23GD93Q17XSA9</em>
									</div>
								</div>

								<div class="form-group row no-margin">
									<label class="col-sm-3 control-label">
										Access Key *
									</label>
									<div class="col-sm-6">
										<?= $form->field($model, 'access_key')->textInput(['placeholder' => 'Please enter app access key', 'class' => 'form-control'])->label(false) ?>
										<em class="notes">Example: AKIAJWD3F4SZTWIP2WKA</em>
									</div>
								</div>

								<div class="form-group row no-margin">
									<label class="col-sm-3 control-label">
										Secret Key *
									</label>
									<div class="col-sm-6">
										<?= $form->field($model, 'secret_key')->passwordInput(['placeholder' => 'Please enther app secret key', 'class' => 'form-control'])->label(false) ?>
										<em class="notes">Example: uMShRNIK1ohNRi3ffRFYzGqk/sxdahcP4Cfrmkx4</em>
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
                                    <label class="control-label">Amazon store is connected. </label>
                                </div>
                            </div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="amazon_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close amazon_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
					<h3 id="ajax_header_msg">Success!</h3>
					<p id="amazon_ajax_msg"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default amazon_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>
<div id="amazon_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in amazon_ajax_request_error" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close amazon_error_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
					<h3 id='amazon_ajax_header_error_msg'></h3>
					<p id="amazon_ajax_msg_eror"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default amazon_error_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>

<?php
    $this->registerJsFile('@web/js/channels/amazon.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>