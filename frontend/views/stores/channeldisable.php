<?php

use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use backend\models\Channels;
use yii\db\Query;

$this->title = 'Disable Channels';
$this->params['breadcrumbs'][] = ['label' => 'Stores', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Disable';
?>
<div class="page-head">
    <h2 class="page-head-title">Disable Channels</h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
</div>

<div class="main-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-table">
                <div class="panel-heading">Disable Channels
                  <div class="tools"><span class="icon mdi mdi-download"></span><span class="icon mdi mdi-more-vert"></span></div>
                </div>
                <div class="panel-body">
                  <form action="#" style="border-radius: 0px;" class="form-horizontal group-border-dashed">
                      <table id="table1" class="table table-striped table-hover table-fw-widget">
                    <thead>
                      <tr>
                        <th>Channels</th>
                        <th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                    <?php 
                    if(!empty($connections)){
                    foreach($connections["store"] as $conn){ ?>  
                    <tr class="odd gradeX">
                       
                        <td class="connection_name"><?php echo  $conn['store_name']; ?></td>
                        <td>
                            <div class="switch-button switch-button-lg <?php echo  str_replace(" ","",$conn['store_name']); ?>">
                                <input type="checkbox" checked="" name="swt4" id="swt4"><span>
                                <label for="swt4" class="chnl_dsbl" data-connid="<?php echo  $conn['connection_id']; ?>" data-cname="<?php echo  $conn['store_name']; ?>" data-type="store" data-cls="<?php echo  str_replace(" ","",$conn['store_name']); ?>"></label></span>
                            </div>
                        </td>
                       </tr>
                    
                    
                     
                    <?php }
                     foreach($connections["channel"] as $conn1){ ?>  
                    <tr class="odd gradeX">
                        <!--td><img src="<?php //echo  $conn1['channel_image']; ?>" class="ch_img"></td-->
                        <td class="connection_name"><?php  $p_name=explode("_",$conn1['parent_name']); echo $p_name[1]." - ".$conn1['channel_name']; ?></td>
                        <td>
                            <div class="switch-button switch-button-lg <?php echo  str_replace(" ","",$conn1['channel_name']); ?>">
                                <input type="checkbox" checked="" name="swt4" id="swt4"><span>
                                <label for="swt4" class="chnl_dsbl" data-connid="<?php echo  $conn1['connection_id']; ?>" data-cname="<?php echo  $p_name[1]." - ".$conn1['channel_name']; ?>" data-type="channel" data-cls="<?php echo  str_replace(" ","",$conn1['channel_name']); ?>"></label></span>
                            </div>
                        </td>
                       </tr>
                    
                    
                     
                    <?php }
                    } ?>
                       <div id="disable-warning" tabindex="-1" role="dialog" class="modal fade">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close"></span></button>
                            </div>
                            <div class="modal-body">
                              <div class="text-center">
                                <div class="text-warning"><span class="modal-main-icon mdi mdi-alert-triangle"></span></div>
                                <h3>Warning!</h3>
                                <p>Are you sure, you want to disable your <span class="modalmsg">BigCommerce</span> channel? If you do so, no data will sync until re-enabled.</p>
                                <div class="xs-mt-50">
                                  <button type="button" data-dismiss="modal" class="btn btn-space btn-default cacnle_btn">Cancel</button>
                                  <button type="button" class="btn btn-space btn-warning proceed_todlt" data-connid="">Proceed</button>
                                </div>
                              </div>
                            </div>
                            <div class="modal-footer"></div>
                          </div>
                        </div>
                      </div> 
                    </tbody>
                  </table>
                   </form>
                </div>
              </div>
         </div>
    </div>
</div>