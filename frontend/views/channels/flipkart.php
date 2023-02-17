<?php 
use yii\widgets\Breadcrumbs;

$this->title = 'Add Your Flipkart Channel';
$this->params['breadcrumbs'][] = ['label' => 'Channels', 'url' => ['/channels']];
$this->params['breadcrumbs'][] = $this->title;


?>
<div class="page-head">
    <h2 class="page-head-title"><?php echo $this->title?></h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
</div>
<div class="row wizard-row">
    <div class="col-md-12 fuelux">
        <div class="block-wizard panel panel-default">
            <div id="wizard-store-connection" class="wizard wizard-ux">
                <ul class="steps">
                    <li data-step="1" class="active">Authorize<span class="chevron"></span></li>
                </ul>
                <div class="step-content">
                    <?php if(empty($channel)): ?>
                    <form id="flipkart-authorize-form" action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                        <div class="form-group no-padding main-title">
                            <div class="col-sm-12">
                                <label class="control-label">Please provide your Flipkart API details for Authorizing and Synchronize :</label>
                                <p> To synchronize Flipkart with Elliot, you'll need to create the Application Id and Secret on your Flipkart Seller Account. Please <a target="_blank" href="https://seller.flipkart.com/api-docs/FAQ.html">Follow</a> this link.</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Application Id *</label>
                            <div id="flipkart_app_id" class="col-sm-6">
                                <input type="text" placeholder="Please Enter value" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Application Secret *</label>
                            <div id="flipkart_app_secret" class="col-sm-6">
                                <input type="text" placeholder="Please Enter value" class="form-control">
                            </div>
                        </div>
                        <input id="user_id" type="hidden" value="<?= $user_id; ?>" />
                        <input id="connection_id" type="hidden" value="<?= $connection_id; ?>" />
                        <input id="user_connection_id" type="hidden" value="0" />
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button class="btn btn-default btn-space"><a href="/">Cancel</a></button>
                                <button data-wizard="#wizard-store-connection" class="btn btn-primary btn-space flipkart-auth-channel">Authorize & Connect</button>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                    <div id="bgc_conn_text" class="form-group no-padding main-title">
                        <div class="col-sm-12">
                            <label class="control-label">Your Flipkart channel is connected.</label>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="flipkart_ajax_request" tabindex="-1" role="dialog" class="modal fade in">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close flipkart_modal_close"></span></button>
        </div>
        <div class="modal-body">
          <div class="text-center">
            <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
            <h3 id="flipkart_ajax_header_msg">Success!</h3>
            <p id="flipkart_ajax_msg"></p>
            <div class="xs-mt-50">
              <button type="button" data-dismiss="modal" class="btn btn-space btn-default flipkart_modal_close">Close</button>
            </div>
          </div>
        </div>
        <div class="modal-footer"></div>
      </div>
    </div>
</div>
<div id="flipkart_ajax_error_modal" tabindex="-1" role="dialog" class="modal fade in flipkart_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close">
                    <span class="mdi mdi-close flipkart_error_modal_close"></span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='flipkart_ajax_header_error_msg'></h3>
                    <p id="flipkart_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default flipkart_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<?php
$this->registerJsFile('@web/js/channels/flipkart.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
