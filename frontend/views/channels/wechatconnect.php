<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use backend\models\Channels;
use backend\models\ChannelConnection;
use yii\db\Query;

$this->title = 'Connect With WeChat';
$this->params['breadcrumbs'][] = 'Connect With WeChat';

$curr_url = explode(".","{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
$domain_name=$curr_url[0];
$uname= $curr_url[0]."_elliot";
              
              
$connection = \Yii::$app->db;
//$model1 = $connection->createCommand('SELECT DISTINCT parent_name stripe_Channel_id,channel_image,parent_name FROM channels'); 
 $new_db_name = 'Elli';
        //        $new_db_name = 'ravim3_elliot';
                $config = [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'mysql:host=localhost;dbname=' . $uname . '',
                    'username' => Yii::$app->params['DB_USERNAME'],
                    'password' => Yii::$app->params['DB_PASSWORD'],
                    'charset' => 'utf8',
                ];

                $db_merchant = Yii::createObject($config);
                $wechat= $db_merchant->createCommand("SELECT channel_ID,channel_name,parent_name FROM `channels` WHERE `parent_name` LIKE '%wechat%'")->queryAll();
                
                $user_data= $db_merchant->createCommand("SELECT id FROM `user` WHERE `domain_name` ='$domain_name'")->queryOne();
               $user_id=$user_data['id'];
                
                
     //get wechat Service account id 
  $get_big_id = Channels::find()->select('channel_ID')->where(['channel_name' => 'Service Account'])->one();
  $channel_id = $get_big_id->channel_ID;
  $chennel_connection= $db_merchant->createCommand("SELECT wechat_admin_approve,user_id FROM `channel_connection` WHERE `user_id` =$user_id")->queryOne();
  
//  $channel = ChannelConnection::find()->Where(['channel_id'=>$channel_id])->one();
//  print_r($chennel_connection['wechat_admin_approve']);
//  echo $channel->wechat_admin_approve."uuu";
        
?>
<div class="page-head">
    <h2 class="page-head-title">Connect With WeChat</h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
</div>
<div class="main-content container-fluid wechat_connect_container">
    <div class="row wizard-row">
      <div class="col-md-12 fuelux">
        <div class="block-wizard panel panel-default">
          <div id="wizard1" class="wizard wizard-ux">
            <ul class="steps">
              <li data-step="1" class="active">Authorize<span class="chevron"></span></li>
            </ul>
            <div class="step-content">
              <div data-step="1" class="step-pane active">
              <?php 
                      if(isset($chennel_connection['wechat_admin_approve']) && $chennel_connection['wechat_admin_approve']=='yes'){ ?>
                    <label class="control-label">Your WeChat account has been connected.</label>
                    <?php  } else{ ?>
                    <label class="control-label wechat_adminafterform">Your WeChat account has been connected.</label>
              <form action="#" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed wechat_adminform">
                <div class="form-group no-padding">
                  <div class="col-sm-12">
                      
                    <label class="control-label">Please provide WeChat store username and password for Authorizing the Integration:</label>
                    
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">WeChat Username</label>
                  <div class="col-sm-6">
                    <input type="text" placeholder="User name" class="form-control initi" id="wechat_username" required>
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">WeChat Password</label>
                  <div class="col-sm-6">
                    <input type="password" placeholder="Password" class="form-control initi" id="wechat_password" required>
                     <input type="hidden" class="form-control" id="wechat_userid" value="<?php echo $chennel_connection['user_id']; ?>">
                  </div>
                </div>
                <!--div class="form-group">
                  <label class="col-sm-3 control-label">User Account Email</label>
                  <div class="col-sm-6">
                    <input type="text" placeholder="User account Email" class="form-control initi" id="elliot_useremail" required>
                  </div>
                </div-->
                <div class="form-group">
                      <label class="col-sm-3 control-label">WeChat Account Type</label>
                      <div class="col-sm-6">
                        <select class="form-control initi" id="acctype">
                          <?php foreach($wechat as $we){
                              $parent=explode("_",$we['parent_name']);
                              $sl='';
                              if(isset($_GET['type']) && $_GET['type']==$we['channel_name']){
                                  $sl="selected";
                              }
                              echo "<option value='".$we['channel_ID']."' $sl>".$parent[1]." - ".$we['channel_name']."</option>";
                          }
?>
                        </select>
                      </div>
                </div>
                <div class="form-group">
                  <div class="col-sm-offset-2 col-sm-10">
                    <input type="buttom" class="btn btn-primary btn-space wechat_inti" value="Submit">
                     <!--input type="buttom" class="btn btn-primary btn-space wechat_import" value="Import WeChat Data"-->
                    <!--button data-wizard="#wizard1" class="btn btn-primary btn-space wizard-next wechat_sbmt">Next Step</button-->
                    
                  </div>
                </div>
              </form>
                    <?php } ?>
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
                    <p>You have successfully connected Elliot to WeChat, the Elliot Customer will be notified once the import is complete.</p>
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
