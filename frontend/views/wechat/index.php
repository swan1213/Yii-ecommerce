<?php
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use common\models\UserConnection;
$this->title = 'Add Your Wechat Store';
$this->params['breadcrumbs'][] = 'Add Your WeChat Store';
?>
<div class="page-head">
	<h2 class="page-head-title">Add Your WeChat Store</h2>
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
                        <li data-step="2" <?php if($is_connect_channel) echo 'class="active"' ?>>Connected<span class="chevron"></span></li>
                    </ul>
					<div class="step-content">
						<?php if ($is_connect_channel == false): ?>
							<ul class="nav nav-tabs" role="tablist">
								<li class="nav-item active">
									<a class="nav-item nav-link" data-toggle="tab" href="#nav-standard-connection" role="tab" aria-controls="nav-standard-connection" aria-selected="true">Standard Connection</a>
								</li>
								<li class="nav-item">
									<a class="nav-item nav-link" data-toggle="tab" href="#nav-walkthechat-connection" role="tab" aria-controls="nav-walkthechat-connection" aria-selected="false">Connect via WalktheChat</a>
								</li>
							</ul>
							<div class="tab-content">
								<div class="tab-pane fade active in" id="nav-standard-connection" role="tabpanel" aria-labelledby="nav-standard-connection">
									<?php $wechat_form = ActiveForm::begin([
										'method' => 'post',
										'action' => ['/wechat/authorize-wechat', 'id' => $id],
										'id' => 'wechat_connection_form',
										'class' => 'form-horizontal group-border-dashed'
									]); ?>
										<div class="form-group row no-margin no-padding main-title">
		                                    <div class="col-sm-12">
		                                        <label class="control-label">Please provide your WeChat <span class="text-lowercase"><?php echo $connection_name; ?></span> API details to authorize the integration</label>
		                                    </div>
		                                </div>

										<div class="form-group row no-margin">
											<label class="col-sm-3 control-label">
												 App ID
											</label>
											<div class="col-sm-6">
												<?= $wechat_form->field($wechat_model, 'client_id')->textInput(['placeholder' => 'Please enter app id', 'class' => 'form-control'])->label(false) ?>
												<em class="notes">Example: wxd7afbc2619e31969</em>
											</div>
										</div>

										<div class="form-group row no-margin">
											<label class="col-sm-3 control-label">
												App Key
											</label>
											<div class="col-sm-6">
												<?= $wechat_form->field($wechat_model, 'client_secret')->passwordInput(['placeholder' => 'Please enter your app key', 'class' => 'form-control'])->label(false) ?>
												<em class="notes">Example: 253be92b1d883568126a15ffad8b1dcf</em>
											</div>
										</div>

										<div class="form-group row no-margin">
											<div class="col-sm-12">
												<a href="/channels" class="btn btn-default btn-space">Cancel</a>
												<?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space']) ?>
											</div>
										</div>
									<?php ActiveForm::end(); ?>
								</div>

								<div class="tab-pane fade" id="nav-walkthechat-connection" role="tabpanel" aria-labelledby="nav-walkthechat-connection">
									<?php $walkthechat_form = ActiveForm::begin([
										'method' => 'post',
										'action' => ['/wechat/authorize-walkthechat', 'id' => $id],
										'id' => 'walkthechat_connection_form',
										'class' => 'form-horizontal group-border-dashed'
									]); ?>
										<div class="form-group row no-margin no-padding main-title">
		                                    <div class="col-sm-12">
		                                        <label class="control-label">Please provide your WalktheChat <span class="text-lowercase"><?php echo $connection_name; ?></span> API details to authorize the integration</label>
		                                    </div>
		                                </div>

										<div class="form-group row no-margin">
											<label class="col-sm-3 control-label">
												Username
											</label>
											<div class="col-sm-6">
												<?= $walkthechat_form->field($walkthechat_model, 'username')->textInput(['placeholder' => 'Please enter app username', 'class' => 'form-control'])->label(false) ?>
												<em class="notes">Example: august.getty@studio86.co</em>
											</div>
										</div>

										<div class="form-group row no-margin">
											<label class="col-sm-3 control-label">
												Password
											</label>
											<div class="col-sm-6">
												<?= $walkthechat_form->field($walkthechat_model, 'password')->passwordInput(['placeholder' => 'Please enter your app password', 'class' => 'form-control'])->label(false) ?>
											</div>
										</div>

										<div class="form-group row no-margin">
											<div class="col-sm-12">
												<a href="/channels" class="btn btn-default btn-space">Cancel</a>
												<?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space']) ?>
											</div>
										</div>
									<?php ActiveForm::end(); ?>
								</div>
							</div>
						<?php else: ?>
							<div id="bgc_conn_text" class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Wechat store is connected. </label>
                                </div>
                            </div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="wechat_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close wechat_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
					<h3 id="ajax_header_msg">Success!</h3>
					<p id="wechat_ajax_msg"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default wechat_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>
<div id="wechat_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in wechat_ajax_request_error" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close wechat_error_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
					<h3 id='wechat_ajax_header_error_msg'></h3>
					<p id="wechat_ajax_msg_eror"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default wechat_error_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>

<?php
    $this->registerJsFile('@web/js/channels/wechat.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>