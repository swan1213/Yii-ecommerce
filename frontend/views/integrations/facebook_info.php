<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\models\UserConnection;
use common\models\Connection;
use yii\db\Query;
$this->title = 'Facebook';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => "javascript: void(0)", 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Channels', 'url' => "javascript: void(0)", 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'View All', 'url' => "/channels/create"];
$this->params['breadcrumbs'][] = 'Facebook';

$id = Yii::$app->user->identity->id;
$facebook = Connection::find()->where(['name' => "Facebook"])->one();
//echo'<pre>';
//print_r($lazada);die;
$channel_id = $facebook->id;
//echo $channel_id;die;
//$checkConnection = UserConnection::find()->Where(['user_id' => Yii::$app->user->identity->id, 'connection_id' => $channel_id, 'connected' => UserConnection::CONNECTED_YES])->one();
$checkConnection = UserConnection::find()->Where(['user_id' => Yii::$app->user->identity->id, 'connection_id' => $channel_id])->one();
?>
<div class="page-head">
    <h2 class="page-head-title">Facebook</h2>
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
        <div class="col-md-12 fuelux wechat12">
            <div class="block-wizard panel panel-default">
                <div id="wizard1" class="wizard wizard-ux">
                    <ul class="steps">
                        <li data-step="1" class="active   " >Facebook Connection<span class="chevron"></span></li>
                    </ul>
                    <div class="step-content">
                        <div data-step="1" class="active">
                            <h4>Instructions: </h4>
                            <ol>
                                <li>Firstly create a catalog in your facebook Business Account.</li>
                                <li>Then Add a Product feed. You will have to add Url <b>"<?= env('SEVER_URL'). 'facebook/feed/?u_id=' . base64_encode(base64_encode($id))?>"</b> in Feed Url.</li>
                                <li>Doing this will enable Facebook Products Feed.</li>
                            </ol>
                            
                        </div>
                        <?php         
                        $style='';
                        if (empty($checkConnection)) {
                               $style='style="display:none;"';
                        }  ?>
                        <div class="panel-body code " <?php echo $style ?>>
                            <div class=" col-sm-4 control-label panel-heading custom_Transs">Schedule File</div> 
                            <!--label class=" control-label panel-heading"></label-->
                            <div class="col-md-6" style="margin-top:7px;">
                            <input type="text" readonly="readonly" value="<?php echo $_SERVER['SERVER_NAME'].'/facebook/feed/?u_id=' . base64_encode(base64_encode($id)) ?>" class="form-control"></div>
                        </div>

                        <div class="panel-body">
                            <form action="#" style="border-radius: 0px;" class="group-border-dashed">
                                <div class="form-group">
                                    <div class=" col-sm-4 control-label panel-heading custom_Transs">Product Feed URL</div>   
                                    <!--label class="col-sm-4 control-label panel-heading">Facebook</label-->
                                    <div class="col-sm-6 xs-pt-5">
                                        <div class="switch-button switch-button-success TransLable">
                                            <input type="checkbox" <?php
                                            if ($checkConnection) {
                                                echo 'checked=""';
                                            }
                                            ?>  name="facebookfeedcheck" id="facebookfeedcheck">
                                            <span><label for="facebookfeedcheck"></label></span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
                <div tabindex="-1" role="dialog" class="modal fade in fbmod-success" style="display: none; background: rgba(0,0,0,0.6);">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close fb_modal_close"></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center">
                                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                                    <h3 id="ajax_header_msg">Success!</h3>
                                    <p id="magento_ajax_msg">Your Facebook Feed account is now enabled and available for use.</p>
                                    <div class="xs-mt-50">
                                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default fb_modal_close">Close</button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer"></div>
                        </div>
                    </div>
                </div>
                <div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in fbmod-error" style="display: none; background: rgba(0,0,0,0.6);">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close fb_error_modal_close"></span></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center">
                                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                                    <h3 id='ajax_header_error_msg'>Success!</h3>
                                    <p id="magento_ajax_msg_eror">You have disconnected your FacebookFeed. Your file will still be available; however, it will not be updated. To re-enable, simply switch Facebook Shopping back on.</p>
                                    <div class="xs-mt-50">
                                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default fb_error_modal_close">Close</button>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer"></div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div> 