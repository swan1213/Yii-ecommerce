<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use common\models\Integration;
use frontend\models\search\IntegrationSearch as Businesstools;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\BusinesstoolsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'NetSuite';
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Integrations', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'ERP', 'url' => ['/integrations/erplist']];
$this->params['breadcrumbs'][] = $this->title;
$user_id=Yii::$app->user->identity->id;


$data=Businesstools::find()->where(['name' => 'NetSuite','user_id'=>$user_id])->all();

?>

<div class="page-head">
    <h2 class="page-head-title">NetSuite Integration</h2>
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
			<?php 
                     if(!empty($data)){ ?> <li data-step="1" class="active">Connected<span class="chevron"></span></li>
					 <?php } else {  ?>
              <li data-step="1" class="active">Step 1<span class="chevron"></span></li>
              <li data-step="2" class="">Step 2<span class="chevron"></span></li>
              <li data-step="3" class="">Step 3<span class="chevron"></span></li> 
					 <?php } ?>
              
            </ul>
			  <?php 
                     if(!empty($data)){ ?>
					 <div class="step-content">
						<div data-step="1" class="step-pane active">
			  <?php 
                          echo "Your NetSuite account has been integrated"; ?>
						  </div></div>
                      <?php } else{ 
                  
?>
            <div class="step-content">
              <div data-step="1" class="step-pane active">
                
                <form role="form"   class="form-horizontal group-border-dashed wechatrequestform" method="post">
                    <div class="form-group no-padding">
                      <div class="col-sm-12">
                        <label class="control-label">Please provide your NetSuite account details for authorizing Elliot:</label><br>
                         <label class="control-label">Service for Customers: URL&module=customer.</label><br>
						 <label class="control-label">Service for SalesOrders: URL&module=salesorder.</label>
                      </div>
                    </div>
                    <div class="form-group">
					<?php if(!empty($data)){ 
							$datavalcompanyid=Businesstools::find()->where(['name' => 'NetSuite','user_id'=>$user_id,'key_name'=>'netsuite_compnay_id'])->one();
							if(!empty($datavalcompanyid)){
								$companyid = $datavalcompanyid->value;
							}
					}  ?>
                      <label class="col-sm-3 control-label">NetSuite Company ID:</label>
                      <div class="col-sm-6">
                        <input type="text" placeholder="User name" value="<?php if(!empty($companyid)){ echo $companyid; } ?>"   class="form-control" id="netsuitecompanyid"  required="required" name="app_url">
                      </div>
                    </div>
					<?php if(!empty($data)){ 
							$datavalcompanyid=Businesstools::find()->where(['name' => 'NetSuite','user_id'=>$user_id,'key_name'=>'netsuite_email'])->one();
							if(!empty($datavalcompanyid)){
								$companyemail = $datavalcompanyid->value;
							}
					}  ?>
                    <div class="form-group">
                      <label class="col-sm-3 control-label">NetSuite Employee Email:</label>
                      <div class="col-sm-6">
                        <input type="email" placeholder="Email" value="<?php if(!empty($companyemail)){ echo $companyemail; } ?>" id="netsuiteempemail" class="form-control" i required="required" name="email">

                      </div>
                    </div>
					<?php if(!empty($data)){ 
							$datavalcompanyid=Businesstools::find()->where(['name' => 'NetSuite','user_id'=>$user_id,'key_name'=>'netsuite_password'])->one();
							if(!empty($datavalcompanyid)){
								$companypass = $datavalcompanyid->value;
							}
					}  ?>
                    <div class="form-group">
                      <label class="col-sm-3 control-label">NetSuite Employee Password:</label>
                      <div class="col-sm-6">
                        <input type="password" placeholder="Password" value="<?php if(!empty($companypass)){ echo $companypass; } ?>" id="netsuitemppass" class="form-control"  required="required" name="password">
                        
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                          <input type="button" data-wizard="#wizard1" class="btn btn-primary wizard-next btn-space netsuitecredentialupdate" value="Submit" name="submit"/>
                      </div>
                    </div>
              </form>
                      
            </div>
			<div data-step="2" class="step-pane" >
			<div class="col-sm-6">
			<?php if(!empty($data)){ 
							$datavalcompanyid=Businesstools::find()->where(['name' => 'NetSuite','user_id'=>$user_id,'key_name'=>'netsuite_role'])->one();
							if(!empty($datavalcompanyid)){
								$companyrole = $datavalcompanyid->value;
							}
					}  ?>
			<div class="form-group">
                            <label class="control-label">NetSuite Role</label>
							 <!--<input type="text" placeholder="Role" value="<?php //if(!empty($companyrole)){ echo $companyrole; } ?>" class="form-control" id="netsuiterole" required="required" name="netsuiterole"/> -->
							 <p>Roles</p>
                            <select class="select2" id="netsuiterole">
                              <optgroup label="Translations">
                               <!-- <option value="translation1">Administrator</option>
                                <option value="translation2">User</option>-->
                              </optgroup>
                            </select>
							 </div>
					<div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                          <input type="button" data-wizard="#wizard1"  class="btn btn-primary wizard-next btn-space " value="Submit" name="submit"/>
                      </div>
                    </div>
                            <!--<p>Roles</p>
                            <select class="select2" id="netsuiterole">
                              <optgroup label="Translations">
                                <option value="translation1">Administrator</option>
                                <option value="translation2">User</option>
                              </optgroup>
                            </select>-->
                </div>
			</div>
			<div data-step="3" class="step-pane" >
			 <form role="form1"   class="form-horizontal group-border-dashed wechatrequestform1" method="post">
                    <?php if(!empty($data)){ 
							$datavalcompanyid=Businesstools::find()->where(['name' => 'NetSuite','user_id'=>$user_id,'key_name'=>'netsuite_elliot_url'])->one();
							if(!empty($datavalcompanyid)){
								$companyurl = $datavalcompanyid->value;
							}
					}  ?>
                    <div class="form-group">
                      <label class="col-sm-3 control-label">Elliot Restlet Url:</label>
                      <div class="col-sm-6">
                        <input type="text" id="elliotresturl" value="<?php if(!empty($companyurl)){ echo $companyurl; } ?>"  placeholder="URL" class="form-control"  required="required" name="elliotresturl">
                      </div>
                    </div>
                    
                    <div class="form-group">
                      <div class="col-sm-offset-2 col-sm-10">
                          <input type="button" data-wizard="#wizard1" class="btn btn-primary wizard-next btn-space netsuitewizardfinish" value="Finish" name="submit"/>
                      </div>
                    </div>
              </form>
			</div>
			
			
          </div>
		  <?php  }  ?>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in netsuite-success" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close netsuite-success_modal_close"></span></button>
            </div>
			 <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="ajax_header_msg">Success!</h3>
                    <p id="magento_ajax_msg">Netsuite Configured Properly!</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default netsuite-success_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
