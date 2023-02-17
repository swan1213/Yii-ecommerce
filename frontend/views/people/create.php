<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Breadcrumbs;
use common\models\Country;
use common\models\State;
use common\models\City;
/* @var $this yii\web\View */
/* @var $model backend\models\CustomerUser */

$this->title = 'Create Customer';
//$this->params['breadcrumbs'][] = ['label' => 'Customer Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'People', 'url' => ['/people/index']];
$this->params['breadcrumbs'][] = 'Add New';


//Get Stores
$db_connection_data=\common\models\Connection::find()->all();
$arr = array();
foreach($db_connection_data as $val) {
    $arr[] = array('name'=>$val->connectionName, 'id'=>$val->id);
}

sort($arr);

$countries = Country::find()->all();
$states = State::find()->limit(12,25)->all();
$city = City::find()->limit(12,25)->all();



?>
<div class="customer-user-create">
    <div class="page-head">
        <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
        <ol class="breadcrumb page-head-nav">
            <?php
            echo Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]);
            ?>
        </ol>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default panel-border-color panel-border-color-primary">
                <div class="panel-body">
                    <form action="create" method="post" style="border-radius: 0px;" class="form-horizontal group-border-dashed" novalidate="">
                        <div class="form-group">
                            <label class="col-sm-3 control-label">FirstName</label>
                            <div id="customer-first-name " class="col-sm-6 ">
                                <input  name="cu-first-name" type="text" class="form-control customer_validate" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">LastName</label>
                            <div id="customer-last-name" class="col-sm-6 ">
                                <input  name="cu-last-name" type="text" class="form-control customer_validate" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Email Address</label>

                            <div id="customer-email" class="col-sm-6 ">
                                <input  name="cu-email" type="email" class="form-control customer_validate" placeholder="Please Enter Value" value="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">DOB</label>
                            <div class="col-md-2 col-xs-7" style="width: 21%;">
                                <div data-min-view="2" data-date-format="yyyy-mm-dd" class="input-group date datetimepicker">
                                    <input size="16" name="cu-DOB" type="text" value="" class="form-control" data-mask="date">
                                    <span class="input-group-addon btn btn-primary"><i class="icon-th mdi mdi-calendar"></i></span>
                                </div>
                            </div>
                            <label class="col-sm-1 control-label" style="width: 5%;">Phone</label>
                            <div class="col-md-3 col-xs-7" style="padding-top: 6px; width: 24%;">
                                <input  name="cu-Phone-Number" type="text" class="form-control" placeholder="Please Enter Value" data-mask="phone" value="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Gender</label>
                            <div class="col-sm-6">
                                <div class="be-radio inline">
                                    <input type="radio" checked="" value="Male" name="cu-Gender" id="rad6">
                                    <label for="rad6">Male</label>
                                </div>
                                <div class="be-radio inline">
                                    <input type="radio" checked="" value="Female" name="cu-Gender" id="rad7">
                                    <label for="rad7">Female</label>
                                </div>
                                <div class="be-radio inline">
                                    <input type="radio" checked="" value="Unisex" name="cu-Gender" id="rad8">
                                    <label for="rad8">UniSex</label>
                                </div>
                            </div>                     
                        </div>

                        <div class="panel-heading profile-panel-heading"> 
                            <div class="title">Billing Address</div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Street Line 1</label>
                            <div id="customer-Street-1" class="col-sm-6 ">
                                <input  name="cu-Street-1" type="text" class="form-control customer_validate" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Street Line 2</label>
                            <div id="customer-Street-2" class="col-sm-6 ">
                                <input  name="cu-Street-2" type="text" class="form-control customer_validate" placeholder="Please Enter Value" value="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Country</label>
                            <div id="customer-Country" class="col-sm-6 ">
                            
                            <select class="form-control customer_validate" id="CreateCustomerCountRY"  name="cu-Country" style="color:#989696;">
                            <option  value="Please Select Value">Please Select Value</option>
                            <?php
                            foreach($countries as $val) {
                                ?>
                                <option value="<?php echo $val->name; ?>"><?php echo $val->name; ?></option>
                               <?php } ?>

                            </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label ">State</label>
                            <div id="customer-State" class="col-sm-6 ">
                            <select class="form-control customer_validate" id="CreateCustomerStatES" name="cu-State" style="color:#989696;">
                            <option value="Please Select Country First">Please Select Country First</option>
                            <?php 
                            //foreach($states as $val) {
                                ?>
                                <!-- <option value="<?php //echo $val->id; ?>"><?php //echo $val->name; ?></option> -->

                            <?php //}
                            ?>
                            </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label ">City</label>
                            <div id="customer-City" class="col-sm-6 ">
                            <select class="form-control customer_validate" id="CreateCustomerCitiES"  name="cu-City" style="color:#989696;">
                            <option value="Please Select State First">Please Select State First</option>
                             <?php
                             //foreach($city as $val) {
                                ?>
                                <!-- <option value="<?php //echo $val->name; ?>"><?php //echo $val->name; ?></option> -->
                            <?php //} 
                             ?>
                            </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label ">Zip Code</label>
                            <div id="customer-Zip" class="col-sm-6 ">
                                <input  name="cu-Zip" type="text" class="form-control customer_validate" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        <div class="panel-heading profile-panel-heading"> 
                            <div class="title">Shipping Address</div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Street Line 1</label>
                            <div id="customer-ship-Street-1" class="col-sm-6">
                                <input  name="cu-ship-Street-1" type="text" class="form-control" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Street Line 2</label>
                            <div id="customer-ship-Street-2" class="col-sm-6">
                                <input  name="cu-ship-Street-2" type="text" class="form-control" placeholder="Please Enter Value" value="">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Country</label>
                            <div id="customer-Country-ship" class="col-sm-6 ">
                            
                            <select class="form-control customer_validate" id="createShipCountRY" style="color:#989696;" name="cu-ship-Country">
                            <option>Please Select Value</option>
                            <?php
                            foreach($countries as $val) {
                                ?>
                                <option value="<?php echo $val->name; ?>"><?php echo $val->name; ?></option>
                               <?php } ?>

                            </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label ">State</label>
                            <div id="customer-State-ship" class="col-sm-6 ">
                            <select class="form-control customer_validate" id="createShipStatES" name="cu-ship-State" style="color:#989696;">
                            <option>Please Select Country First</option>
                            <?php 
                            //foreach($states as $val) {
                                ?>
                                <!-- <option value="<?php //echo $val->name; ?>"><?php //echo $val->name; ?></option> -->

                            <?php //}
                            ?>
                            </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label ">City</label>
                            <div id="customer-City-ship" class="col-sm-6 ">
                            <select class="form-control customer_validate" id="createShipCitiES" name="cu-ship-City" style="color:#989696;">
                            <option>Please Select State First</option>
                             <?php
                             //foreach($city as $val) {
                                ?>
                               <!--  <option value="<?php //echo $val->name; ?>"><?php //echo $val->name; ?></option> -->
                            <?php //} 
                             ?>
                            </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Zip Code</label>
                            <div id="customer-ship-Zip" class="col-sm-6">
                                <input  name="cu-ship-Zip" type="text" class="form-control" placeholder="Please Enter Value" value="">
                            </div>
                        </div>
                        <div class="form-group">
                            <?php if(count($user_connections)>0){ ?> <label class="col-sm-3 control-label">Channel Acquired</label> <?php } ?>
                            <div class="col-sm-9">



                                <?php foreach($arr as $val) {
                                    if($val['name'] == 'Google shopping') {

                                    }else if(in_array($val['id'], $user_connections)){ ?>
                                        <div class="be-checkbox inline col-sm-6 col-md-4 col-lg-3 margin-left-0">
                                            <input class="channel_accquired" id="<?= $val['name'] ;?>" type="checkbox" name="channel_accquired[]" value="<?= $val['id']; ?>">
                                            <label for="<?= $val['name'] ;?>"><?php if($val['name'] == 'MercadoLibre') {echo 'Mercado Libre';} else {
                                                    echo $val['name'];
                                                } ?></label>
                                        </div>

                                    <?php }
                                    ?>
                                <?php   } ?>




                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-xs-11">
                                <p class="text-right">
                                    <input type="submit" class="btn btn-space btn-primary" id="people_submit" value="Save">
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
