<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use common\models\Category;
use common\models\ProductCategory;

$this->title = 'Add Your Google Shop Store';
$this->params['breadcrumbs'][] = 'Add Your Google Shop Store';

?>
<style type="text/css">
	.googleshopping-connection-container,
	.googleshopping-feed-container {
		margin: 0;
	}

	#create_google_feed_modal .modal-footer {
		padding: 0 20px;
	}
</style>
<div class="page-head">
	<h2 class="page-head-title">Add Your Google Shop Store</h2>
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
                        <li data-step="2" <?php if($is_authorize) echo 'class="active"'?>>
                        	<?php if ($is_connect_channel == false): ?>
                        		Disconnected
                        	<?php else: ?>
                        		Connected
                        	<?php endif; ?>
                        	<span class="chevron"></span>
                        </li>
                    </ul>
					<div class="step-content">
						<!-- <ul class="nav nav-tabs" role="tablist">
							<li class="nav-item <?php if(!$is_authorize) echo 'active'?>">
								<a class="nav-link data-toggle="tab" href="#authorize_google" role="tab">Authorize</a>
							</li>
							<li class="nav-item <?php if($is_authorize) echo 'active'?>">
								<a class="nav-link data-toggle="tab" href="#feed_panel" role="tab">Feed Panel</a>
							</li>
						</ul> -->

						<div class="tab-content">
							<div class="tab-pane <?php if(!$is_authorize) echo 'active'?>" id="authorize_google" role="tabpanel">
								<?php $connection_form = ActiveForm::begin([
									'method' => 'post',
									'class' => 'form-horizontal group-border-dashed'
								]); ?>
										<div class="form-group row no-margin no-padding main-title">
		                                    <div class="col-sm-12">
		                                        <label class="control-label">Please provide your Google Shopping account details for Authorizing the Integration :</label>
	                                            <p> To integrate Google Shopping channel with Elliot, you'll need to create an Google Merchant Account. Please follow this <a target="_blank" href="https://www.google.com/retail/solutions/merchant-center/">link</a>.</p>  
		                                    </div>
		                                </div>

										<div class="form-group row">
											<label class="col-sm-3 control-label">
												Merchant ID
											</label>
											<div class="col-sm-6">
												<?= $connection_form->field($connection_model, 'merchant_id')->textInput(['placeholder' => 'Please enter merchat id', 'class' => 'form-control', 'autofocus' => true])->label(false) ?>
												<em class="notes">Example: 114050942</em>
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

							<div class="tab-pane <?php if($is_authorize) echo 'active'?>" id="feed_panel" role="tabpanel">
								<div class="tab-container">
									<ul class="nav nav-tabs">
										<li <?php if(!$is_connect_channel) echo 'class="active"'; ?>><a data-toggle="tab" href="#setting">Setting</a></li>
										<li <?php if($is_connect_channel) {
												echo 'class="active"';
											} else {
												echo 'class="disabled disabledTab"';
											}
											?>><a <?php if($is_connect_channel) echo 'data-toggle="tab" href="#feeds"'; ?>>Feeds</a></li>
									</ul>

									<div class="tab-content">
										<div id="setting" class="tab-pane <?php if(!$is_connect_channel) echo 'in active'; ?>">
											<div class="switch-button switch-button-success TransLable">
												<input type="hidden" id="google_shopping_connection_id" value="<?= $id ?>">
	                                            <input type="checkbox" id="google_shopping_setting" <?php if($is_connect_channel) echo 'checked' ?>>
	                                            <span><label for="google_shopping_setting"></label></span>
	                                        </div>
										</div>
										<div id="feeds" class="tab-pane <?php if($is_connect_channel) echo 'in active'; ?>">
											<table id="google_shopping_feed_list" class="table table-striped table-bordered table-hover table-fw-widget">
												<thead>
													<tr>
														<th>Name</th>
														<th>Fetch Url</th>
														<th>Actions</th>
													</tr>
												</thead>

												<tbody>
													
												</tbody>
											</table>

											<div>
												<?= Html::a('Create new feed', ['/googleshopping/create', 'id' => $id], ['class'=>'btn btn-primary']) ?>
												<?= Html::a('Re-Authorize', ['/googleshopping/re-authorize', 'id' => $id], ['class'=>'btn btn-success']) ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="google_shopping_confirm_dialog" title="Confirmation Required">
	Are you sure to delete this feed?
</div>

<div id="google_shopping_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close google_shopping_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
					<h3 id="ajax_header_msg">Success!</h3>
					<p id="google_shopping_ajax_msg"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default google_shopping_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>
<div id="google_shopping_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in google_shopping_ajax_request_error" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close google_shopping_error_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
					<h3 id='google_shopping_ajax_header_error_msg'></h3>
					<p id="google_shopping_ajax_msg_eror"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default google_shopping_error_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>

<?php
    $this->registerJsFile('@web/js/channels/google-shopping.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>