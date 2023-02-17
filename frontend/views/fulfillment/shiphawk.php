<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\Breadcrumbs;
use yii\widgets\ActiveForm;
use common\models\UserConnection;
use yii\db\Query;

$this->title = 'Add Your Shiphawk Store';
$this->params['breadcrumbs'][] = 'Add Your Shiphawk Store';

?>
<div class="page-head">
	<h2 class="page-head-title">Add Your Shiphawk Store</h2>
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
                        <li data-step="2" <?php if($is_connect_fulfillment) echo 'class="active"'?>>Connected<span class="chevron"></span></li>
                    </ul>
					<div class="step-content">
						<?php if ($is_connect_fulfillment == false): ?>
							<?php $form = ActiveForm::begin([
								'method' => 'post',
								'action' => ['/fulfillment/shiphawk-authorize'],
								'id' => 'shiphawk_connection_form',
								'class' => 'form-horizontal group-border-dashed'
							]); ?>
								<div class="form-group row main-title">
                                    <div class="col-sm-12">
                                        <label class="control-label">Please provide your Shiphawk API details for Authorizing the Integration:</label>
                                    </div>
                                </div>

                                <div class="form-group row no-margin">
									<label class="col-sm-3 control-label">
										Product Key
									</label>
									<div class="col-sm-6">
										<?= $form->field($model, 'product_key')->textInput(['placeholder' => 'Please enter your product key', 'class' => 'form-control', 'autofocus' => true])->label(false) ?>
										<em class="notes">Example: 8677aa2b5122f269b16db18d51f2627b</em>
									</div>
								</div>

								<div class="form-group row no-margin">
									<a href="/fulfillment/software" class="btn btn-default btn-space">Cancel</a>
									<?= Html::submitButton('Authorize & Connect', ['class' => 'btn btn-primary btn-space']) ?>
								</div>
							<?php ActiveForm::end(); ?>
						<?php else: ?>
							<div id="bgc_conn_text" class="form-group no-padding main-title">
                                <div class="col-sm-12">
                                    <label class="control-label">Shiphawk fulfillment is connected. </label>
                                </div>
                            </div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="shiphawk_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close shiphawk_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
					<h3 id="ajax_header_msg">Success!</h3>
					<p id="shiphawk_ajax_msg"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default shiphawk_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>
<div id="shiphawk_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in shiphawk_ajax_request_error" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close shiphawk_error_modal_close"></span></button>
			</div>

			<div class="modal-body">
				<div class="text-center">
					<div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
					<h3 id='shiphawk_ajax_header_error_msg'></h3>
					<p id="shiphawk_ajax_msg_eror"></p>
					<div class="xs-mt-50">
						<button type="button" data-dismiss="modal" class="btn btn-space btn-default shiphawk_error_modal_close">Close</button>
					</div>
				</div>
			</div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>

<?php
    $this->registerJsFile('@web/js/fulfillment/shiphawk.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>