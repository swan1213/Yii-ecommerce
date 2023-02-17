<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use backend\models\Channels;
use yii\db\Query;
use backend\models\ChannelConnection;

$this->title = 'Add Your Facebook Store';
$this->params['breadcrumbs'][] = 'Add Your Facebook Store';

$id = Yii::$app->user->identity->id;
$channel = ChannelConnection::find()->Where(['user_id'=>$id])->one();


?>
<div class="page-head">
    <h2 class="page-head-title">Add Your Facebook Store</h2>
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
              <li data-step="1" class="<?php echo isset($channel) && $channel->wechat_admin_approve=='yes'?'complete':'active'; ?>" <?php echo isset($channel) && $channel->wechat_admin_approve=='yes'?'disabled':''; ?>>Step 1<span class="chevron"></span></li>
              <li data-step="2" class="<?php echo isset($channel) && $channel->wechat_admin_approve=='yes'?'active':''; ?>">Step 2<span class="chevron"></span></li>
            </ul>
            <!--div class="actions">
              <button type="button" class="btn btn-xs btn-prev btn-default" disabled="disabled"><i class="icon mdi mdi-chevron-left"></i>Prev</button>
              <button type="button" data-last="Finish" class="btn btn-xs btn-next btn-default" disabled="disabled">Next<i class="icon mdi mdi-chevron-right"></i></button>
            </div-->
            <div class="step-content">
              <div data-step="1" class="step-pane active">
                  <p class="afterwechatrequestmsg">
                  <?php 
                  $users_Id=Yii::$app->user->identity->id;
                  $channel_connection=ChannelConnection::find()->where(['user_id' => $users_Id])->one();
                  if(!empty($channel_connection)){
                      if($channel_connection->wechat_already=='yes'){
                             echo "<p>Thank you for your submission for an Official Facebook Store. Your request is currently being proceed and an Elliot team member will reach out within 24 hours to confirm your store creation.</p>";
                        }else{
                             echo "</p>Thank you for your submission for an Official Facebook Store. Your account is currently being created and an Elliot team member will reach out within 24 hours to confirm your store creation.</p>";
                        }
                   }else{
                  ?>
                      </p>
                       <p class="afterwechatrequestmsg" style="display:none;">Thank you for your submission for an Official Facebook Store. Your account is currently being created and an Elliot team member will reach out within 24 hours to confirm your store creation.</p>
                    <p class="afterwechatrequestmsg_already" style="display:none;">Thank you for your submission for an Official Facebook Store. Your request is currently being proceed and an Elliot team member will reach out within 24 hours to confirm your store creation.</p>
              <form action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed wechatrequestform">
                <div class="form-group no-padding">
                  <div class="col-sm-12">
                    <label class="control-label"><?php echo isset($_GET['already']) && $_GET['already']=='yes'?'Please provide Facebook store username and password for Authorizing the Integration:':'Please provide a suggested Facebook store name and password for Authorizing the Integration:'; ?></label>
                    <p>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label"><?php echo isset($_GET['already']) && $_GET['already']=='yes'?'Username':'Suggest Store Name'; ?></label>
                  <div class="col-sm-6">
                    <input type="text" placeholder="User name" class="form-control" id="wechat_storename" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label"><?php echo isset($_GET['already']) && $_GET['already']=='yes'?'Password':'Suggest Password'; ?></label>
                  <div class="col-sm-6">
                    <input type="password" placeholder="Password" class="form-control" id="wechat_password" required>
                    <input type="hidden" class="form-control" id="wechat_type" value="<?php echo isset($_GET['type'])?$_GET['type']:''; ?>">
                  </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                      <?php if(isset($_GET['already']) && $_GET['already']=='yes') { ?>
                      <input type="buttom" class="btn btn-primary btn-space wechat_sbmt1_already" value="Submit">
                      <?php }else{ ?>
                    <input type="buttom" class="btn btn-primary btn-space wechat_sbmt1" value="Submit">
                    <a href="?already=yes<?php echo isset($_GET['type'])?'&type='.$_GET['type']:''; ?>" class="btn btn-primary btn-space wechat_sbmt_already">Already Have Facebook Account?</a>
                    <!--button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-next wechat_sbmt">Next Step</button-->
                      <?php } ?>
                  </div>
                </div>
              </form>
                  <?php } ?>
            </div>
              <div data-step="2" class="step-pane <?php echo isset($channel) && $channel->wechat_admin_approve=='yes'?'active':''; ?>">
                <form action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                    <div class="form-group no-padding">
                        <div class="col-sm-12">
                          <!--label class="control-label">Your Facebook store has been created by Elliot team and connected with the Facebook Store. Please click on the button to import data from Facebook store to Elliot.</label-->

                        </div>
                     </div>
                  <div class="form-group no-padding">
                      <div class="col-sm-10 text-center">
                            <!--button class="btn btn-primary btn-space wechat_import">Import Facebook Data</button-->
                            </p>Thank you for your submission for an Official Facebook Store. Your account is currently being created and an Elliot team member will reach out within 24 hours to confirm your store creation.</p>
                      </div>
                  </div>
                  
                  <!--div class="form-group">
                    <div class="col-sm-12">
                      <button data-wizard="#wizard1" class="btn btn-default btn-space wizard-previous">Previous</button>
                      <button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-next">Next Step</button>
                    </div>
                  </div-->
                </form>
              </div>
            </div>
          </div>
            
        <div id="walkthechat_request" tabindex="-1" role="dialog" style="display: none;" class="modal fade">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
                </div>
                <div class="modal-body">
                  <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3>Success!</h3>
                    <p>Your Facebook application is being processed and you will receive an email containing documentation necessary to sell across borders. Additionally, an Elliot representative will be in touch 
                        within 24 hours to complete your on boarding. Please have your documentation ready to expedite this process.</p>
                    <div class="xs-mt-50">
                      <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Close</button>
                    </div>
                  </div>
                </div>
                <div class="modal-footer"></div>
              </div>
            </div>
          </div>
            
        <div id="walkthechat_request_error" tabindex="-1" role="dialog" class="modal fade" style="display: none;">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
                </div>
                <div class="modal-body">
                  <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3>Error!</h3>
                    <p>Please provide valid login details</p>
                    <div class="xs-mt-50">
                      <button type="button" data-dismiss="modal" class="btn btn-space btn-default">Close</button>
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
