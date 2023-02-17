<?php

use frontend\components\CustomFunction;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use common\models\Connection;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use common\models\User;
use yii\db\Query;
use common\models\FulfillmentList;
use common\models\Order;
use common\models\SmartlingPrice;
use common\models\Customer;
use common\models\CurrencySymbol;
use common\models\Smartling;

$connection_name = '';
$import_status = '';
$loader_class = '';
$fulfillment_sfexpress_id = '';


$users_Id = Yii::$app->user->identity->id;


$user_connection = UserConnection::findOne(['id' => $user_connection_id]);
$user_connection_detail = $user_connection->userConnectionDetails;
$connection_details = $user_connection->getConnection()->one();
$currency_setting = isset($user_connection_detail->settings['currency']) ? $user_connection_detail->settings['currency'] : $user_connection_detail->currency;

$fulfillment = FulfillmentList::find()->where(['name' => 'SF Express'])->one();
if (!empty($fulfillment)) {
    $fulfillment_sfexpress_id = $fulfillment->id;
}

$connection_name = $user_connection->getPublicName();
$import_status = $user_connection->import_status;
$performance_order = Order::find()->Where(['user_connection_id' => $user_connection_id, 'user_id' => $users_Id])->with('customer')->orderBy(['created_at' => SORT_DESC,])->limit(5)->all();

if ($import_status==UserConnection::IMPORT_STATUS_COMPLETED || $import_status==UserConnection::IMPORT_STATUS_COMPLETED_READ) {
    $loader_class = '';
} else {
    $loader_class = 'be-loading be-loading-active';
    $loader_class = '';
}
//For perfomence tab latest order


$this->title = 'Connected Channel - ' . $connection_name ;
$this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = ['label' => 'Channels', 'url' => ['/channels']];
$this->params['breadcrumbs'][] = ['label' => 'Connected Channels', 'url' => 'javascript: void(0)', 'class' => 'non_link'];
$this->params['breadcrumbs'][] = $connection_name ;


function isValidPermission($key) {
    if (Yii::$app->user->identity->getPermission() == User::USER_LEVEL_MERCHANT) {
        return false;
    }
    return true;
}
?>
<script>
    var user_connection_id = "<?= $user_connection_id ?>"
    var u = "<?= $id ?>"
    var type = "<?= $type ?>"
    var fulfillment_sfexpress_id = "<?= $fulfillment_sfexpress_id ?>"
</script>
<div class="page-head">
    <h2 class="page-head-title"><?php echo 'Connected Channel - ' . $connection_name; ?></h2>
    <ol class="breadcrumb page-head-nav">
        <?php
        echo Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]);
        ?>
    </ol>
</div>

<style>
    .select2-results__option[aria-selected=true] {
        display: none;
    }
</style>

<div class="main-content container-fluid be-loading" id="channel_disable">
    <!--Tabs-->
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">Settings</div>
                <div class="tab-container">
                    <ul class="nav nav-tabs">
                        <li <?php if ($refresh==0) echo 'class="active"'; ?>><a href="#home" data-toggle="tab">General</a></li>
                        <li <?php if (!CustomFunction::checkPermissionOther(3)) echo 'style="display: none"'; ?>><a href="#Fulfillment" data-toggle="tab">Fulfillment</a></li>
                        <li><a href="#Translations" data-toggle="tab">Translations</a></li>
                        <li><a href="#Performance" data-toggle="tab">Performance</a></li>
                        <li <?php if (!CustomFunction::checkPermissionOther(5)) echo 'style="display: none"'; ?>><a href="#Currency" data-toggle="tab">Currency Setting</a></li>
                        <li <?php if ($refresh==1) echo 'class="active"'; ?>><a href="#Mapping" data-toggle="tab">Product Feed Mapper</a></li>
                    </ul>
                    <div class="tab-content col-sm-12">
                        <div id="home" class="tab-pane <?php if ($refresh==0) echo 'active'; ?> cont <?= $loader_class ?>">
                            <div class="main-content container-fluid">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="panel panel-default panel-table">
                                            <div class="panel-heading">Disable Channels </div>
                                            <div class="panel-body">
                                                <?php
                                                $all_connection_info = $user_connection->connection_info;
                                                if (!empty($all_connection_info)) { ?>
                                                    <label class="col-sm-3 control-label panel-heading">
                                                        <?php
                                                            echo $connection_name;
                                                        ?>
                                                    </label>
                                                    <div class="TransLable switch-button switch-button-lg <?php echo str_replace(" ", "", $connection_name); ?>">
                                                        <input type="checkbox" checked="" name="swt4" id="swt4">
                                                        <span>
                                                            <label
                                                                    for="swt4"
                                                                    class="chnl_dsbl"
                                                                    data-connid="<?php echo $user_connection_id; ?>"
                                                                    data-cname="<?php echo $connection_name; ?>"
                                                                    data-type="channel"
                                                                    data-cls="<?php echo str_replace(" ", "", $connection_details->name); ?>">
                                                            </label>
                                                        </span>
                                                    </div>
                                                    <?php
                                                    foreach($all_connection_info as $key=>$data) { ?>
                                                        <div class="col-md-12">
                                                            <label class="col-sm-3 control-label panel-heading"><?php echo $key; ?></label>
                                                            <input type="text" class="form-control" style="width:60%"  disabled="disabled" value="<?php echo $data; ?>" />
                                                        </div>
                                                    <?php }
                                                }
                                                ?>
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
                                                                    <p>Are you sure, you want to disable your <span class="modalmsg">Store/</span> channel? If you do so, no data will sync until re-enabled.</p>
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
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--For loader Spinner!-->
                            <div class="be-spinner" style="width:100%; text-align: center; right:auto">
                                <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                                    <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                                </svg>
                                <span style="display:block; padding-top:30px;">Your data is importing, you will be able to access these settings once importing will be done.</span>
                            </div>
                            <!--End Loader Spinner!-->
                        </div>

                        <div id="Fulfillment" class="tab-pane">
                            <div class="panel-body">
                                <form action="#" style="border-radius: 0px;" class="form-horizontal group-border-dashed">
                                    <div class="form-group">
                                        <div class="col-sm-6">
                                            <label class="col-sm-12 panel-heading">Enable Fulfillment</label>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="switch-button switch-button-success FullLable xs-mt-20">
                                                <input <?php
                                                if ($user_connection->fulfillment_list_id!=0) {
                                                    echo 'checked=""';
                                                }
                                                ?>  type="checkbox" name="channelfulfillment" id="channelfulfillment" <?php if (!isValidPermission('fulfillment')) echo "disabled"; ?>>
                                                <span>
                                                    <label for="channelfulfillment"></label>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="col-sm-6 xs-pt-5" id="newfullfill" <?php
                                            if ($user_connection->fulfillment_list_id==0) {
                                                echo 'style="display:none;"';
                                            }
                                            ?>>
                                            <select  class="select2" id="fulfillmentlist" name="fulfillmentlist"
                                                <?php
                                                if ($user_connection->fulfillment_list_id==0) {
                                                    echo 'style="display:none;"';
                                                }
                                                ?> >
                                                <optgroup label="Fulfillments">
                                                    <option value="0">Select Fulfillment</option>
                                                    <?php
                                                    $FulFillmentList = FulfillmentList::find()->all();
                                                    foreach ($FulFillmentList as $fulfillment) {
                                                        //if ($fulfillment->type == 'Software') { ?>
                                                            <option
                                                                <?php
                                                                if ($fulfillment->id == $user_connection->fulfillment_list_id) {
                                                                    echo "SELECTED";
                                                                }
                                                                ?> value="<?php echo $fulfillment->id; ?>"><?php echo $fulfillment->name; ?>
                                                            </option>
                                                        <?php //}
                                                    }
                                                    ?>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                </form>
                                <div id="disable-warning-fulfilled" tabindex="-1" role="dialog" class="modal fade">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close todlt_fulfillment_close"></span></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="text-center">
                                                    <div class="text-warning"><span class="modal-main-icon mdi mdi-alert-triangle"></span></div>
                                                    <h3>Warning!</h3>
                                                    <p>Are you sure, you want to disable your <span class="modalmsg">Fulfillment</span> If you do so, no data will sync until re-enabled.</p>
                                                    <div class="xs-mt-50">
                                                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default cacnle_btn todlt_fulfillment_close">Cancel</button>
                                                        <button type="button" class="btn btn-space btn-warning proceed_todlt_fulfillment" data-connid="">Proceed</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        if (!empty($user_connection->smartling_status == UserConnection::SMARTLING_ENABLED)) { ?>
                            <div id="Translations" class="tab-pane">
                                <div class="col-sm-12">
                                    <div class="">
                                        <div class="form-group">
                                            <label style="padding:20px 0px 20px !important;" class="col-sm-6 control-label panel-heading custom_Transs">Enable Smartling Translations</label>
                                            <div class="col-sm-6 xs-pt-5">
                                                <div class="switch-button switch-button-success TransLable">
                                                    <input type="checkbox" <?php if ($user_connection->smartling_status == UserConnection::SMARTLING_ENABLED) {
                                                    echo "checked=''";
                                                    } ?> name="channelsmartlingyes" id="channelsmartlingyes" <?php if (!isValidPermission('translation')) echo "disabled"; ?>>
                                                    <span><label for="channelsmartlingyes"></label></span>
                                                </div>
                                            </div>
                                         </div>
                                    </div>
                                </div>
                                <?php
                                $smartling_price_model = SmartlingPrice::find()->select('locale_id')->all();
                                $smart_setting_model = Smartling::findOne(["user_connection_id" =>$user_connection_id]);
                                ?>
                                <div id="channel_id_translation_data">
                                    <div class="col-sm-12">
                                        <div class="">
                                        <label class="control-label">Choose your target language for translation</label>
                                        <select class="select2" id="selectTranslationsetting">
                                            <optgroup label="Translations Language">
                                                <?php
                                                    foreach ($smartling_price_model as $smart_price) {
                                                        $selected = '';
                                                        if ($smart_setting_model->target_locale == $smart_price->locale_id) {
                                                            $selected = "SELECTED";
                                                        }
                                                        ?>
                                                        <option <?php echo $selected; ?> value="<?php echo $smart_price->locale_id; ?>"> <?php echo $smart_price->locale_id; ?></option>
                                                <?php }?>
                                            </optgroup>
                                        </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 xs-p-10">
                                        <label class="control-label">Choose your translation method</label>
                                        <p>Translation Services powered by Smartling</p>
                                        <select class="select2" id="selectTranslation">
                                            <optgroup label="Translations">
                                                <option <?php
                                                if ($smart_setting_model->translation_type == 'Google MT') {
                                                    echo "SELECTED";
                                                }
                                                ?> value="Google MT">Machine Translations</option>
                                                <option <?php
                                                if ($smart_setting_model->translation_type == 'Translation with Edit') {
                                                    echo "SELECTED";
                                                }
                                                ?> value="Translation with Edit">Human Translations</option>
                                                <option value="Google MT with Edit" <?php
                                                if ($smart_setting_model->translation_type == 'Google MT with Edit') {
                                                    echo "SELECTED";
                                                }
                                                ?> >Hybrid</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="col-sm-6 panel-heading" style="padding:0px !Important;margin:22px 5px !important"><a href="/terms-conditions" target="_blank">You accept our Translations Terms and Conditions.</a></label>
                                            <div class="col-sm-4 xs-pt-20">
                                                <div class="switch-button switch-button-success">
                                                    <input type="checkbox"
                                                        <?php
                                                        if ($smart_setting_model->connected == Smartling::CONNECTED_YES) {
                                                            echo "checked=''";
                                                        }
                                                        ?> name="smartlingyes" id="smartlingyes">
                                                    <span>
                                                        <label for="smartlingyes"></label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label style="margin:8px 5px !important; padding:0px;" class="col-sm-3 control-label panel-heading"><a href="javascript: void(0)" id="getRateTable">View translations rates</a></label>
                                        </div>
                                    </div>
                                </div>
    			            </div>
                            <?php } else { ?>
    			            <div id="Translations" class="tab-pane">
                                <div class="col-sm-12">
                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label style="" class="col-sm-6 control-label panel-heading custom_Transs">Enable Smartling Translations</label>
                                            <div class="col-sm-6 xs-pt-5">
                                                <div class="switch-button switch-button-success TransLable">
                                                    <input type="checkbox" name="channelsmartlingyes" id="channelsmartlingyes" <?php if (!isValidPermission('translation')) echo "disabled"; ?>>
                                                    <span>
                                                        <label for="channelsmartlingyes"></label>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
    			                <div id="channel_id_translation_data">
                                    <div class="col-sm-12 ">
                                        <div class="">
                                        <label class="control-label">Choose your target language for translation</label>
                                        <p>Translation Services powered by Smartling</p>
                                                        <?php $smartling_price_model = SmartlingPrice::find()->select('locale_id')->all(); ?>
                                        <select class="select2" id="selectTranslationsetting">
                                            <optgroup label="Translations Language">
                                                                <?php foreach ($smartling_price_model as $smart) { ?>
                                                                <option  value="<?php echo $smart->locale_id; ?>"><?php echo $smart->locale_id; ?></option>
                                                                <?php } ?>
                                            </optgroup>
                                        </select>
                                        </div>
                                    </div>
                                    <?php
                                    $smartlingData = Smartling::find()->Where(['user_id' => $users_Id])->one();
                                    ?>
                                    <div class="col-sm-12 xs-p-10">
                                        <label class="control-label">Choose your translation method</label>
                                        <p>Translation Services powered by Smartling</p>
                                        <select class="select2" id="selectTranslation">
                                            <optgroup label="Translations">
                                                <option value="Google MT">Machine Translations</option>
                                                <option value="Translation with Edit">Human Translations</option>
                                                <option value="Google MT with Edit" >Hybrid</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label class="col-sm-6 panel-heading" style="padding:0px !Important;margin:22px 5px !important;"><a href="/terms-conditions" target="_blank">You accept our Translations Terms and Conditions.</a></label>
                                            <div class="col-sm-4 xs-pt-20">
                                                <div class="switch-button switch-button-success">
                                                    <input type="checkbox" name="smartlingyes" id="smartlingyes">
                                                    <span><label for="smartlingyes"></label></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12" style="display:none;">
                                        <div class="be-checkbox">
                                            <input type="checkbox" value="bar" id="channeltranslationcat" data-parsley-multiple="group1" data-parsley-errors-container="#error-container2">
                                            <label class="col-sm-3 control-label panel-heading" for="channeltranslationcat">Category Translation</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label style="margin:8px 5px !important; padding:0px;" class="col-sm-3 control-label panel-heading"><a href="javascript: void(0)" id="getRateTable">View translations rates</a></label>
                                        </div>
                                    </div>
    				                <button style="margin:16px 3px 0px 21px;" type="submit" class="btn btn-space btn-primary custom_Button_Class" id="translation_corporate_id">Submit</button>
                                </div>
                            </div>
                            <?php } ?>
                        <div id="Performance" class="tab-pane">
                            <div class="col-md-12">
                                <div class="panel panel-default panel-table">
                                    <div class="panel-heading">
                                        <div class="title">Recent Orders</div>
                                    </div>
                                    <div class="panel-body table-responsive ">
                                        <table id="recent_orders_dashboard" class="table-borderless table table-striped table-hover table-fw-widget dataTable">
                                            <thead>
                                            <tr>
                                                <th >Order ID</th>
                                                <th>Customer Name</th>
                                                <th class="number12" style="text-align:left;">Amount</th>
                                                <th >Status</th>
                                            </tr>
                                            </thead>
                                            <tbody class="no-border-x">
                                            <?php if (empty($performance_order)): ?>
                                                <td valign="top" colspan="7" class="dataTables_empty">No data available in table.</td>
                                            <?php
                                            else :
                                                usort($performance_order, function($a, $b) {
                                                    $t1 = strtotime($a->order_date);
                                                    $t2 = strtotime($b->order_date);
                                                    return $t2 - $t1;
                                                });
                                                foreach ($performance_order as $orders_data_value) :
                                                    $channel_abb_id = isset($orders_data_value->channel_abb_id) ? $orders_data_value->channel_abb_id : "";
                                                    $firstname = $orders_data_value->customer->first_name;
                                                    $lname = $orders_data_value->customer->last_name;
                                                    $order_amount = isset($orders_data_value->total_amount) ? $orders_data_value->total_amount : 0;
                                                    $order_value = number_format((float) $order_amount, 2, '.', '');
                                                    $date_order = date('M-d-Y', strtotime($orders_data_value->order_date));
                                                    $order_status = $orders_data_value->status;
                                                    $label = '';
                                                    if ($order_status == 'Completed') :
                                                        $label = 'label-success';
                                                    endif;

                                                    if ($order_status == 'Returned' || $order_status == 'Refunded' || $order_status == 'Cancel' || $order_status == 'Partially Refunded') :
                                                        $label = 'label-danger';
                                                    endif;

                                                    if ($order_status == 'In Transit' || $order_status == 'On Hold'):
                                                        $label = 'label-primary';
                                                    endif;

                                                    if ($order_status == 'Awaiting Fulfillment' || $order_status == 'Awaiting Shipment' || $order_status == 'Incomplete' || $order_status == 'waiting-for-shipment' || $order_status == 'Pending' || $order_status == 'Awaiting Payment' || $order_status == 'On Hold'):
                                                        $label = 'label-warning';
                                                    endif;
                                                    if ($order_status == 'Shipped' || $order_status == 'Partially Shipped'):
                                                        $label = 'label-primary';
                                                    endif;


                                                    $conversion_rate = 1;
                                                    $user = Yii::$app->user->identity;
                                                    $conversion_rate = 1;
                                                    if (isset($user->currency) and $user->currency != 'USD') {
                                                        if (isset($user->currency)) {
                                                            $conversion_rate = Stores::getDbConversionRate($user->currency);
                                                            //$order_value = $order_value * $conversion_rate;
                                                            $order_value = number_format((float) $order_value, 2, '.', '');
                                                        }
                                                    }
                                                    $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user->currency)])->select(['id', 'symbol'])->asArray()->one();
                                                    if (isset($selected_currency) and ! empty($selected_currency)) {
                                                        $currency_symbol = $selected_currency['symbol'];
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><a href="order/view?id=<?php echo $orders_data_value->id; ?>"><?= $orders_data_value->connection_order_id; ?></a></td>
                                                        <td class="captialize"><?= $firstname . ' ' . $lname; ?></td>
                                                        <td class="number12" style="text-align:left;"><?php echo $currency_symbol ?><?= number_format($order_value, 2); ?></td>
                                                        <td><?= $date_order; ?></td>
                                                        <td><span class="label <?= $label; ?>"><?= $order_status; ?></span></td>
                                                    </tr>
                                                <?php
                                                endforeach;
                                            endif;
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="Currency" class="tab-pane">
                            <p><b> Channel Default Currency</b></p>
                            <?php if (isValidPermission("currency")) { ?>
                                <span width="65%">
                                    <a id="channel_CurrencyPreference" href="javascript:" class="editable editable-click editable-empty" data-type="select" data-value="<?php echo (!empty($currency_setting)) ? $currency_setting : 'USD' ?>" data-title="Enter Default Currency"><?php echo (!empty($currency_setting)) ? $currency_setting : 'USD' ?></a>
                                </span>
                            <?php } else { ?>
                                <p><?php echo (!empty($currency_setting)) ? $currency_setting : 'USD' ?></p>
                            <?php } ?>
                        </div>
                        <div id="Mapping" class="tab-pane <?php if ($refresh==1) echo 'active'; ?>">
                            <div class="col-sm-12">
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label style="" class="col-sm-6 control-label panel-heading custom_Mapping">Enable Custom Mapping</label>
                                        <div class="col-sm-6 xs-pt-5">
                                            <div class="switch-button switch-button-success TransLable">
                                                <input type="checkbox" name="channelmappingyes" id="channelmappingyes"<?php
                                                if ($user_connection->mapping_status == 1) {
                                                    echo "checked=''";
                                                }
                                                ?>>
                                                <span>
                                                    <label for="channelmappingyes"></label>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12" id="mapping_body" <?php
                            if ($user_connection->mapping_status == 0) {
                                echo 'style="display:none;"';
                            }
                            ?>>
                                <div class="col-sm-12">
                                    <div class="col-sm-4">
                                        <label style="" class="control-label custom_Mapping">Elliot Product Attributes</label>
                                    </div>
                                    <div class="col-sm-4">
                                        <label style="" class="control-label custom_Mapping"><?=$connection_name;?> Product Attributes</label>
                                    </div>
                                    <div class="col-sm-2">
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div id="elliot_select_attribute_list" class="col-sm-4">
                                        <select class="col-sm-3 select2" id="elliot_attribute_list" name="elliot_attribute_list">
                                            <optgroup label="Elliot Attribute">
                                                <?php
                                                foreach ($elliot_attrs as $elliot_attr) { ?>
                                                    <option value="<?php echo $elliot_attr->id; ?>"><?php echo $elliot_attr->name; ?>
                                                    </option>
                                                    <?php
                                                }
                                                ?>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-sm-4">
                                        <select  class="col-sm-3 select2" id="store_attribute_list" name="store_attribute_list">
                                            <optgroup label="Channel Attribute">
                                                <?php

                                                foreach ($store_attrs as $store_attr) { ?>
                                                    <option value="<?php echo $store_attr->id; ?>"><?php echo $store_attr->name; ?>
                                                    </option>
                                                    <?php
                                                }
                                                ?>
                                            </optgroup>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="panel-body">
                                            <div class="col-sm-10">
                                            </div>
                                            <div class="col-sm-2">
                                                <button class="btn btn-primary btn-space mapping_mapped">Add</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- List button -->

                                <div id="product_feed_down" class="col-sm-12" <?php if (sizeof($mapped_elliot_attrs) == 0) echo 'style="display: none"'?>>
                                    <div class="col-sm-4">
                                        <div class="showcase">
                                            <div class="dropdown">
                                                <ul id="elliot_label" style="display: block; position: relative;" class="dropdown-menu">
                                                    <?php
                                                    $i = 0;
                                                    foreach ($mapped_elliot_attrs as $mapped_elliot_attr) {
                                                        $i ++;
                                                        echo '<li class="mapped_elliot_label" id ="' . $mapped_elliot_attr->id . '" value="' . $mapped_elliot_attr->name . '"><a>'. $mapped_elliot_attr->name .'</a></li>';
                                                        //if (sizeof($mapped_elliot_attrs) != $i) echo '<li class="divider"></li>';
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="showcase">
                                            <div class="dropdown">
                                                <ul id="store_label" style="display: block; position: relative;" class="dropdown-menu">
                                                    <?php
                                                    $i = 0;
                                                    foreach ($mapped_store_attrs as $mapped_store_attr) {
                                                        $i ++;
                                                        echo '<li class="mapped_store_label" id ="' . $mapped_store_attr->id . '" value="' . $mapped_store_attr->name . '"><a>'. $mapped_store_attr->name .'</a></li>';
                                                        //if (sizeof($mapped_store_attrs) != $i) echo '<li class="divider"></li>';
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-2">
                                        <div class="panel-body">
                                            <div class="col-sm-10">
                                            </div>
                                            <div class="col-sm-2">
                                                <button class="btn btn-space btn-danger mapping_delete" style="display: none">delete</button>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Save button -->
                                <div id="product_feed_save" class="col-sm-12" <?php if (sizeof($mapped_elliot_attrs) == 0) echo 'style="display: none"'?>>
                                    <div class="panel-body">
                                        <div class="col-sm-8">
                                        </div>
                                        <div class="col-sm-4">
                                            <button class="btn btn-primary btn-space mapping_save">Update Product Mapping</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div tabindex="-1" role="dialog" class="modal fade in channelfulfillment-success" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close channelfulfillment_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="ajax_header_msg">Success!</h3>
                    <p id="magento_ajax_msg">Current Channel/Store order is enabled to fullfilled.</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default channelfulfillment_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div tabindex="-1" role="dialog" class="modal fade in channelfulfillment-success-not-found" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close channelfulfillment_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id="ajax_header_msg">Error!</h3>
                    <p id="magento_ajax_msg">No Fullfillment found !</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default channelfulfillment_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in mapping_ajax_request_error" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close mapping_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='mapping_ajax_header_error_msg'>Error</h3>
                    <p id="mapping_ajax_msg_eror"></p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default mapping_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div tabindex="-1" role="dialog" class="modal fade in channelfulfillment-success-error-found" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close channelfulfillment_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id="ajax_header_msg">Error!</h3>
                    <p id="magento_ajax_msg">No Fullfillment found !</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default channelfulfillment_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div tabindex="-1" role="dialog" class="modal fade in translatoinfulfillment-success" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content credit_card_modal be-loading" style="overflow: visible;">
            <div class="modal-header" style="padding-top: 50px; padding-bottom: 0;">
                <div class="text-center">
                    <p id="ajax_header_msg"><h3 id="">Translation Service Fee</h3> </p>
                </div>
            </div>
            <div class="smartling_logo">
                <img src="/img/elliot-logo-thumbnail-01.jpg" alt="Smartling Logo">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close credit_card_close"></span></button>
            </div>
            <div class="modal-body">
                <?php
                $userData = User::find()->Where(['id' => $users_Id])->one();
                $credit = $userData->access_token;
                if (empty($credit)) {
                    ?>
                    <input type="hidden" id="checkCredit" value="1" />
                    <div class="text-center">
                        <form id="credit-card-authorize-form" action="" data-parsley-namespace="data-parsley-" data-parsley-validate="" novalidate="" class="form-horizontal group-border-dashed">
                            <div class="form-group no-padding main-title">
                                <div id="smartling_creditemail" class="col-sm-12 input_icon">
                                    <div class="icon">
                                        <span class="mdi mdi-email"></span>
                                    </div>
                                    <input id="creditemail" type="text" placeholder="Email" class="form-control input-padd">
                                </div>
                            </div>
                            <div class="form-group no-padding main-title">
                                <div id="smartling_creditcart" class="col-sm-12 input_icon">
                                    <div class="icon"><span class="mdi mdi-card"></span></div>
                                    <input id="creditcart" type="text" placeholder="Card Number" class="form-control input-padd">
                                </div>
                            </div>
                            <div class="form-group no-padding main-title">
                                <div id="smartling_creditdate" class="col-sm-6 input_icon">
                                    <div class="icon"><span class="mdi mdi-calendar-check"></span></div>
                                    <input id="creditdate" type="text" placeholder="MM/YY" class="form-control input-padd">
                                </div>
                                <div id="smartling_creditcvc" class="col-sm-6 input_icon">
                                    <div class="icon"><span class="mdi mdi-lock-outline"></span></div>
                                    <input id="creditcvc" type="text" placeholder="CVC" class="form-control input-padd">
                                </div>
                            </div>
                            <div class="col-md-12 xs-mt-20" id="getValuePrice">
                                <button type="button" data-dismiss="modal" class=" smartling_enble_controller btn btn-space btn-default  btn-primary btn-lg">Confirm
                                    <img src="img/spinner.gif" alt="" style="width: 30px; height: 30px;"/></button>
                            </div>
                        </form>
                    </div>
                <?php } else { ?>
                    <div class="text-center">
                        <h3>You will be charged from your saved card. Click confirm to Start Translation.</h3>
                        <div class="col-md-12">
                            <div class="button-middle">
                                <div class="button-middle-inline xs-mt-50" id="getValuePrice">
                                    <button type="button" data-dismiss="modal" class="smartling_enble_controller btn btn-space btn-default  btn-primary">Confirm
                                        <img src="img/spinner.gif" alt="" style="width: 30px; height: 30px;"/>
                                    </button>
                                </div>
                                <div class="button-middle-inline xs-mt-50">
                                    <button type="button" data-dismiss="modal" class="btn btn-space btn-default translatoinfulfillment_modal_close btn-primary">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="modal-footer"></div>
            </div>
            <div class="be-spinner" style="width:100%; text-align: center; right:auto">
                <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                    <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                </svg>
                <span style="display:block; padding-top:30px;">We will process your file.. Please wait...</span>
            </div>
        </div>
    </div>
</div>
<div tabindex="-1" role="dialog" class="modal fade in translatoinfulfillment1-success" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close translatoinfulfillment_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="ajax_header_msg">Success!</h3>
                    <p id="magento_ajax_msg " class="alldataadd">Your Translations Setting Saved!</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default translatoinfulfillment1_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<div tabindex="-1" role="dialog" class="modal fade in translatoinfulfillment12-success" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close translatoinfulfillment_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-success"><span class="modal-main-icon mdi mdi-check"></span></div>
                    <h3 id="ajax_header_msg">Success!</h3>
                    <p id="magento_ajax_msg " class="alldataadd">Your Translations Setting Saved!</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default translatoinfulfillment12_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in channelfulfillment-error" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close channelfulfillment_error_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="text-danger"><span class="modal-main-icon mdi mdi-close-circle-o"></span></div>
                    <h3 id='ajax_header_error_msg'>Success!</h3>
                    <p id="magento_ajax_msg_eror">Current Channel/Store order will not fulfilled by SF Express.</p>
                    <div class="xs-mt-50">
                        <button type="button" data-dismiss="modal" class="btn btn-space btn-default channelfulfillment_error_modal_close">Close</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>
<?php
$SmartlingPrice = SmartlingPrice::find()->all();
?>
<div id="mod-danger" tabindex="-1" role="dialog" class="modal fade in translationmod-rate" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close trans_rate_modal_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="form-group">
                        <div class="panel-body table-responsive ">
                            <table id="table1" class="table table-striped table-hover table-fw-widget">
                                <thead>
                                <tr>
                                    <th>Language(Locale ID)</th>
                                    <th>Human Translations Rate/Words</th>
                                    <th>Hybrid Rate/Words</th>
                                </tr>
                                </thead>
                                <tbody class="no-border-x">
                                <?php
                                foreach ($SmartlingPrice as $data) {
                                    ?>
                                    <tr>
                                        <td class="captialize"><?php echo $data->target_language . '(' . $data->locale_id . ')'; ?></td>
                                        <td><a href="javascript: void(0)"><?php echo $data->editing ?></a></td>
                                        <td><a href="javascript: void(0)"><?php echo $data->post_edit ?></a></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>

<div tabindex="-1" role="dialog" class="modal fade in google-machine-transaltion-modal" style="display: none; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog">
        <div class="modal-content credit_card_modal be-loading" style="overflow: visible;">
            <div class="modal-header">
            </div>
            <div class="smartling_logo">
                <img src="/img/elliot-logo-thumbnail-01.jpg" alt="Smartling Logo">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close"><span class="mdi mdi-close credit_card_close"></span></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <h3 id="ajax_header_msg">Translation Service Fee</h3>
                    <div class="text-center">
                        <h4>Machine translation is free.</h4>
                        <h4>Click proceed to continue. </h4>
                        <button class="smartling_enble_controller_google_machine btn btn-space btn-default  btn-primary btn-lg">Proceed</button>
                    </div>
                </div>
            </div>
            <div class="be-spinner" style="width:100%; text-align: center; right:auto">
                <svg width="40px" height="40px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
                    <circle fill="none" stroke-width="4" stroke-linecap="round" cx="33" cy="33" r="30" class="circle"></circle>
                </svg>
                <span style="display:block; padding-top:30px;">We will process your file.. Please wait...</span>
            </div>
            <div class="modal-footer"></div>
        </div>
    </div>
</div>