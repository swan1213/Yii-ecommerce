<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use yii\db\Query;
use common\models\User;

$this->title = 'Upload Your Flipkart Store Data';
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
<div class="main-content container-fluid">
    <input type="hidden" id="user_connection_id" value="<?php echo $user_connection_id?>">
    <div class="row wizard-row">
            <div class="panel panel-default">
                <div class="panel-heading">Flipkart</div>
                <div class="tab-container">
                  <ul class="nav nav-tabs">
                    <li class="active"><a href="#home" data-toggle="tab">Products</a></li>
<!--                    <li><a href="#profile" data-toggle="tab">Orders</a></li>-->
                  </ul>
                  <div class="tab-content">
                    <div id="home" class="tab-pane active cont">
                        1) <b>Firstly Download xls file of Products from Your Flipkart Store. </b><br><br>
                        2) <b>Then Upload that xls file Here.</b><br><br>
                        3) <b>Doing so will import Your Flipkart Products to Elliot. At the moment, Order importing will be start.</b>
                            <!--<p class="afterwechatrequestmsg">-->
                            <?php if (Yii::$app->user->identity->level != User::USER_LEVEL_SUPERADMIN){ ?>
                            <form role="form" class="form-group group-border-dashed">
                                <div class="form-group no-padding">
                                    <div class="col-sm-10 text-center">
                                        <label class="control-label pull-left">Upload Flipkart Products xls</label>
                                        <br>
                                        <input type="file" name="flipkart_products_xls" id="flipkart_products_xls" style="margin-top:20px;" >
                                        <ul class="parsley-errors-list filled" id="error_flipkart_products_xls" style="display:none">
                                            <li class="parsley-required">This value is required.</li>
                                        </ul>
                                    </div>
                                </div>
                                <input type="submit" id="upload_products" class="btn btn-primary btn-space" style="margin-top:30px;" >
                            </form>
                            <?php } ?>
                       
                    </div>
                    <div id="profile" class="tab-pane cont">
                        1) <b>Firstly Download CSV file of Orders from Your Flipkart Store. </b><br><br>
                        2) <b>Then Upload that CSV file Here.</b><br><br>
                        3) <b>Doing so will import Your Flipkart Orders to Elliot.</b>
                            <!--<p class="afterwechatrequestmsg">-->
                            <?php if (Yii::$app->user->identity->level != User::USER_LEVEL_SUPERADMIN){ ?>
                            <form role="form" class="form-group group-border-dashed">
                                <div class="form-group no-padding">
                                    <div class="col-sm-10 text-center">
                                        <label class="control-label pull-left">Upload Flipkart Orders CSV</label>
                                        <br>
                                        <input type="file" name="flipkart_orders_csv"  id="flipkart_orders_csv" style="margin-top:20px;" >
                                        <ul class="parsley-errors-list filled" id="error_flipkart_orders_csv" style="display:none">
                                            <li class="parsley-required">This value is required.</li>
                                        </ul>
                                    </div>
                                </div>
                                <input type="submit" id="upload_orders" class="btn btn-primary btn-space" style="margin-top:30px;" >
                            </form>
                            <?php } ?>
                    </div>
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
    $this->registerJsFile('@web/js/channels/flipkart-upload.js', ['depends' => [yii\web\JqueryAsset::className()]]);
?>
