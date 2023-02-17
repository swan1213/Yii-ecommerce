<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use common\models\UserConnection;
use yii\db\Query;

$this->title = 'Add Your Newegg Store';
$this->params['breadcrumbs'][] = 'Add Your Newegg Store';

?>
<div class="page-head">
	<h2 class="page-head-title">Add Your Newegg Store</h2>
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
							<div data-step="1" class="step-pane active">
								<?php $form = ActiveForm::begin([
									'method' => 'post',
									'action' => ['/newegg/authorize', 'id' => $id],
									'id' => 'newegg_connection_form',
									'class' => 'form-horizontal group-border-dashed'
								]); ?>
									<div class="form-group row main-title">
	                                    <div class="col-sm-12">
	                                        <label class="control-label">Please provide your <?php echo $connection_name ?> Newegg API details for Authorizing the Integration:</label>
	                                    </div>
	                                </div>

	                                <div class="form-group row">
										<label class="col-sm-3 control-label">
											Seller ID
										</label>
										<div class="col-sm-6">
											<?= $form->field($model, 'seller_id')->textInput(['placeholder' => 'Please enter your seller id', 'class' => 'form-control', 'autofocus' => true])->label(false) ?>
											<em class="notes">Example: AF7X</em>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-3 control-label">
											API Key
										</label>
										<div class="col-sm-6">
											<?= $form->field($model, 'api_key')->textInput(['placeholder' => 'Please enter app api key', 'class' => 'form-control'])->label(false) ?>
											<em class="notes">Example: 06c44f1eae344d8d9bea8cd5be7931fb</em>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-3 control-label">
											Secret Key 
										</label>
										<div class="col-sm-6">
											<?= $form->field($model, 'secret_key')->passwordInput(['placeholder' => 'Please enther app secret key', 'class' => 'form-control'])->label(false) ?>
											<em class="notes">Example: 12df94bb-f52b-4d95-9e73-5d3926da48ec</em>
										</div>
									</div>

									<div class="form-group row">
										<label class="col-sm-3 control-label">
											<div>Please provide the item zip file to get detailed product list.</div>
											<div>(*) You can download it in <a href="https://sellerportal.newegg.com/manage-items/batchcreateitem">your seller account</a>. Please read <a href="https://mkpl.newegg.com/wiki/doku.php/manage_items#batch_update_items_contents_basic_information_and_subcategory_attributes">this guide</a>. We only accept csv format in Template File Type dropdown.</div>
										</label>
										<div class="col-sm-6">
											<?= $form->field($model, 'item_file')->fileInput() ?>
											<em class="notes">Example: AF7X_CompleteItemFiles_20171205_10_55_25_131569737265045400.zip</em>
										</div>
									</div>

									<div class="form-group row">
										<a href="/channels" class="btn btn-default btn-space">Cancel</a>
										<?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space']) ?>
									</div>
								<?php ActiveForm::end(); ?>
							</div>
						<?php else: ?>
							<div id="newegg_file_update" class="form-group main-title">
                                <div class="row form-group">
                                    <label class="col-sm-12 control-label">Newegg store is connected. </label>
                                </div>

                                <div class="row form-group">
                                    <label class="col-sm-3 control-label">
										<div>Please uppload the item zip file when the items were updated.</div>
										<div>(*) You can download it in <a href="https://sellerportal.newegg.com/manage-items/batchcreateitem">your seller account</a>. Please read <a href="https://mkpl.newegg.com/wiki/doku.php/manage_items#batch_update_items_contents_basic_information_and_subcategory_attributes">this guide</a>. We only accept csv format in Template File Type dropdown.</div>
									</label>
									<div class="col-sm-6">
										<input id="user_connection_id" type="hidden" value="<?= $user_connection_id; ?>" />
										<input type="file" id="update_batch_file">
										<em class="notes">Example: AF7X_CompleteItemFiles_20171205_10_55_25_131569737265045400.zip</em>
									</div>
                                </div>

                                <div class="row form-group">
                                	<div class="col-sm-12">
                                		<?= Html::button('Update', ['class' => 'btn btn-primary btn-space']) ?>
                                	</div>
                                </div>
                            </div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="newegg_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close newegg_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
					<h3 id="ajax_header_msg">Success!</h3>
					<p id="newegg_ajax_msg"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default newegg_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>
<div id="newegg_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in newegg_ajax_request_error" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close newegg_error_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
					<h3 id='newegg_ajax_header_error_msg'></h3>
					<p id="newegg_ajax_msg_eror"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default newegg_error_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>

<?php
    $this->registerJsFile('@web/js/channels/newegg.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>