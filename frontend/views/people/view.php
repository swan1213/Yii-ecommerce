<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Breadcrumbs;
use common\models\Order;
use common\models\Connection;
use common\models\User;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use common\models\Customer;
use common\models\CustomerAddress;

/* @var $this yii\web\View */
/* @var $model backend\models\CustomerUser */

/* * **RATING code for the view starts here**** */
$connection = \Yii::$app->db;
$star_array = array();
$stores_query = array();
$orders_id_array = array();
$order_IDS = array();
$clv_array = array();
$ten_highest_clv = array();
$user_ID = Yii::$app->user->identity->id;
$user_level = Yii::$app->user->identity->level;
if ($user_level == User::USER_LEVEL_MERCHANT_USER) {
    $user_ID = Yii::$app->user->identity->parent_id;
}
$str_rate = '';
//if (isset($_GET['str_rate'])) {
//    $str_rate = $_GET['str_rate'];
//}

 $str_rate = $starRate;

$html_code = '<span class="mdi mdi-star"></span><span class="mdi mdi-star"></span><span class="mdi mdi-star"></span><span class="mdi mdi-star"></span><span class="mdi mdi-star"></span>';
if ($str_rate == 5) {
    $html_code = '<span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span>';
} elseif ($str_rate == 3) {
    $html_code = '<span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span><span class="mdi mdi-star black"></span><span class="mdi mdi-star black"></span>';
} elseif ($str_rate == 2) {
    $html_code = '<span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span><span class="mdi mdi-star black"></span><span class="mdi mdi-star black"></span><span class="mdi mdi-star black"></span>';
} elseif ($str_rate == 4) {
    $html_code = '<span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span><span class="mdi mdi-star yellow"></span><span class="mdi mdi-star black"></span>';
} elseif ($str_rate == 1) {
    $html_code = '<span class="mdi mdi-star yellow"></span><span class="mdi mdi-star black"></span><span class="mdi mdi-star black"></span><span class="mdi mdi-star black"></span><span class="mdi mdi-star black"></span>';
}

/* * **RATING code for the view ends here**** */


$this->title = $model->first_name . ' ' . $model->last_name;
$this->params['breadcrumbs'][] = ['label' => 'People', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
//$this->params['breadcrumbs'][] =  $this->$html_code;
$base_url = "";
//$baseDomain = $baseDomain = env('GLOBAL_DOMAIN');
//$userDomain = Yii::$app->user->identity->domain;
//$base_url = $userDomain.$baseDomain;

$customer_id = $model->id;



/* Starts For Billing Address! */
$Order_bill_street1 = $Order_bill_street2 = $Order_bill_cityname = $Order_bill_countryname = $Order_bill_statename = $Order_bill_zipnumber = '';
$Billorders_customer = Order::find()->Where(['customer_id' => $model->id])->orderBy(['order_date' => SORT_DESC])->one();
if (!empty($Billorders_customer)) {
    $Order_bill_street1 = $Billorders_customer->bill_street_1;
    $Order_bill_street2 = $Billorders_customer->bill_street_2;
    $Order_bill_cityname = $Billorders_customer->bill_city;
    $Order_bill_countryname = $Billorders_customer->bill_country;
    $Order_bill_statename = $Billorders_customer->bill_state;
    $Order_bill_zipnumber = $Billorders_customer->bill_zip;
}
/* -End For Billing Address */

/* Starts For Shipping Address! */
$order_ship_street1 = $order_ship_street2 = $order_ship_state = $order_ship_city = $order_ship_country = $order_ship_zip = '';
$orders_customer = Order::find()->Where(['customer_id' => $model->id])->orderBy(['order_date' => SORT_DESC])->one();
if (!empty($orders_customer)) {
    $order_ship_street1 = $orders_customer->ship_street_1;
    $order_ship_street2 = $orders_customer->ship_street_2;
    $order_ship_state = $orders_customer->ship_state;
    $order_ship_city = $orders_customer->ship_city;
    $order_ship_country = $orders_customer->ship_country;
    $order_ship_zip = $orders_customer->ship_zip;
}

/* -End For Billing Address */
?>



<!--<div class="customer-user-view">-->
<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
<?php
echo Breadcrumbs::widget([
    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
]);
?>    
        <li><?php echo $html_code; ?></li>        
    </ol>
</div>

<div class="user-profile">
    <div class="row">
        <div class="col-md-5">
            <div class="user-display ">
                <!--Starts Change cover image !-->
                <div class="user-display-bg texthover">
<?php if (!empty($model->cover_img)) { ?>
    		    <img src="<?php echo $model->cover_img; ?>"  alt="Profile Background" id="customer-cover_image_dropzone1"  class="custom_drozone_css dropzone">
    		    <div class="overlay"><br />
    			<span class="panel-heading profile-panel-heading span-cover-image-css custom_drozone_css dropzone" id="customer-cover_image_dropzone"></span>
    		    </div>
<?php } else { ?>
    		    <img src="<?php echo $base_url; ?>/img/demo_cover_image.jpg"  alt="Profile Background" id="customer-cover_image_dropzone_default"  class="custom_drozone_css dropzone">
    		    <div class="overlay"><br />
    			<span class="panel-heading profile-panel-heading span-cover-image-css custom_drozone_css dropzone" id="customer-cover_image_dropzone"></span>
    		    </div>
<?php } ?>
                </div>
                <!--End Code Change cover image !-->


                <!--Starts Profile image !-->
                <div class="user-display-bottom " >
<?php if (!empty($model->photo_image)) { ?>
    		    <div class="user-display-avatar profile_img_avatar"><img src="<?php echo $model->photo_image; ?>" alt="Avatar" id="customer-profile_image_dropzone1" class="custom_drozone_css dropzone">
    			<div class="overlay1"><br />
    			    <span class="panel-heading profile-panel-heading span-profile-image-css custom_drozone_css dropzone" id="customer-profile_image_dropzone"></span>
    			</div>
    		    </div>
<?php } else { ?>
    		    <div class="user-display-avatar profile_img_avatar"><img src="<?php echo $base_url; ?>/img/avatar-150.png" alt="Avatar" id="customer-profile_image_dropzone1" class="custom_drozone_css dropzone">
    			<div class="overlay1"><br />
    			    <span class="panel-heading profile-panel-heading span-profile-image-css custom_drozone_css dropzone" id="customer-profile_image_dropzone"></span>
    			</div>
    		    </div>

<?php } ?>
                    <div class="user-display-info">
                        <div class="name"><?php echo Yii::$app->user->identity->username; ?></div>
                        <div class="nick"><span class="mdi mdi-account"></span><?php echo $model->first_name . ' ' . $model->last_name; ?></div>
                    </div>
                </div>
                <!--End Code Change cover image !-->
            </div>
            <div class="user-info-list panel panel-default">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table id="user" style="clear: both" class="table table-striped table-borderless">
                            <tbody>
                                <tr>
                                    <td width="35%">Customer ID </td>
                                    <td width="65%"><a  href="javascript:" id="customer_customer_id">CTID10000456<?php echo $model->id; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">First Name</td>
                                    <td width="65%"><a id="customer_first_name" href="javascript:" data-type="text" data-title="Enter Firstname"><?php echo $model->first_name; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Last Name</td>
                                    <td width="65%"><a id="customer_last_name" href="javascript:" data-type="text" data-title="Enter Lastname"><?php echo $model->last_name; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Email Address</td>
                                    <td width="65%"><a id="customer_email_add" href="javascript:" data-type="text" data-title="Enter Email Address"><?php echo $model->email; ?></a></td>
                                </tr>
                                <tr>
                                    <td>DOB</td>
                                    <td><a id="customer_dob" href="javascript:" data-title="Select Date of birth" data-pk="1" data-template="D/MM/YYYY" data-viewformat="DD/MM/YYYY" data-format="D/MM/YYYY" data-value="<?php echo $model->dob; ?>" data-type="combodate" class="editable editable-click"><?php echo Yii::$app->user->identity->userProfile->dob; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Gender</td>
                                    <td><a id="customer_gender" data-title="Select sex"  data-value="<?php echo $model->gender; ?>" data-pk="1" data-type="select" href="#" style="color: gray;" class="editable editable-click"></a></td>
                                </tr>
                                <tr>
                                    <td>Date Acquired</td>
                                    <td width="65%"><a  href="javascript:"><?php echo $model->customer_created; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%" >Phone Number</td>
                                    <td width="65%"><a id="customer_Phone_no" href="javascript:" data-type="text" data-value="<?php echo $model->phone; ?>" data-title="Enter Phone No"><?php echo $model->phone; ?></a></td>
                                </tr>

                            </tbody>
                        </table>
                        <!--Start for accordian!-->
                        <div id="accordian_customer_address" class="panel-group accordion">
                        <?php
                        $customerAddress  = $model->getCustomerAddresses()->one();
                        $final_channel_accquired = $model->userConnection->connection->connectionName;
                        $final_channel_accquired_id = $model->userConnection->getPublicName();
                        /* For Billing And Shipping */
                        $bill_street1 = $Order_bill_street1;
                        $bill_street2 = $Order_bill_street2;
                        $bill_cityname = $Order_bill_cityname;
                        $bill_countryname = $Order_bill_countryname;
                        $bill_statename = $Order_bill_statename;
                        $bill_zipnumber = $Order_bill_zipnumber;

                        $ship_street1 = (empty($customerAddress->street_1)) ? $order_ship_street1 : $customerAddress->street_1;
                        $ship_street2 = (empty($customerAddress->street_2)) ? $order_ship_street2 : $customerAddress->street_2;
                        $ship_state = (empty($customerAddress->state)) ? $order_ship_state : $customerAddress->state;
                        $ship_city = (empty($customerAddress->city)) ? $order_ship_city : $customerAddress->city;
                        $ship_country = (empty($customerAddress->country)) ? $order_ship_country : $customerAddress->country;
                        $ship_zip = (empty($customerAddress->zip)) ? $order_ship_zip : $customerAddress->zip;
                        ?>

                        <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title"><a class="collapsed" data-toggle="collapse" data-parent="#accordian_customer_address" href="#<?= str_replace(' ', '', $final_channel_accquired_id); ?>"><i class="icon mdi mdi-chevron-down"></i><?= $final_channel_accquired; ?></a></h4>
                        </div>
                        <div id="<?= str_replace(' ', '', $final_channel_accquired_id); ?>" class="panel-collapse collapse">
                            <div class="panel-body">
                                                <div class="panel-heading profile-panel-heading">
                                                    <div class="title">Billing Address</div>
                                                </div>
                            <table id="user" style="clear: both" class="table table-striped table-borderless">
                                                    <tbody>
                                                        <tr>
                                                            <td width="35%" >Street Line 1</td>
                                                            <td width="65%">
                                                                <a id="customer_corporate_street1" class="cus_bill_street1" data-connectname="<?= $final_channel_accquired ?>" href="#" data-type="text" data-title="Enter street line 1"><?php echo $bill_street1; ?></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                    <td width="35%" >Street Line 2</td>
                                                            <td width="65%"><a id="customer_corporate_street2" class="cus_bill_street2" data-connectname="<?= $final_channel_accquired ?>" href="#" data-type="text" data-title="Enter street line 2"><?php echo $bill_street2; ?></a></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Country</td>
                                                            <td>
                                                                <a id="customer_corporate_country" class="cus_bill_country" data-connectname="<?= $final_channel_accquired ?>" data-title="Start typing Country.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click">
        <?php echo $bill_countryname; ?></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>State</td>
                                                            <td>
                                                                <a id="customer_corporate_state" class="cus_bill_state" data-connectname="<?= $final_channel_accquired ?>" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click">
        <?= $bill_statename; ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>City</td>
                                                            <td>
                                                                <a id="customer_corporate_city" class="cus_bill_city" data-connectname="<?= $final_channel_accquired ?>" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click">
        <?= $bill_cityname; ?>
                                                                </a>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td>Zip Code</td>
                                                            <td>
                                                                <a id="customer_corporate_zip" class="cus_bill_zip" data-connectname="<?= $final_channel_accquired ?>" data-title="Start typing Zipcode.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click">
        <?= $bill_zipnumber ?>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                            <!--For Shipping address!-->
                            <div class="panel-heading  profile-panel-heading">
                                <div class="title">Shipping Address</div>
                            </div>

                            <table id="user" style="clear: both" class="table table-striped table-borderless">
                                <tbody>
                                <tr>
                                    <td width="35%">Street Line 1</td>
                                    <td width="65%"><a id="customer_ship_street1" class="cus_ship_street1" data-connectname="<?= $final_channel_accquired ?>" href="#" data-type="text" data-title="Enter Street Line 1"><?= $ship_street1; ?></a></td>
                                </tr>
                                <tr>
                                    <td width="35%">Street Line 2</td>
                                    <td width="65%"><a id="customer_ship_street2" class="cus_ship_street2" data-connectname="<?= $final_channel_accquired ?>" href="#" data-type="text" data-title="Enter Street Line 2"><?= $ship_street2; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Country</td>
                                    <td><a id="customer_ship_country" class="cus_ship_country" data-connectname="<?= $final_channel_accquired ?>" data-title="Start typing Country.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="javascript:" data-original-title="" title="" class="editable editable-click"><?= $ship_country; ?></a></td>
                                </tr>
                                <tr>
                                    <td> State</td>
                                    <td><a id="customer_ship_state" class="cus_ship_state" data-connectname="<?= $final_channel_accquired ?>" data-title="Start typing State.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="#" data-original-title="" title="" class="editable editable-click"><?= $ship_state; ?></a></td>
                                </tr>
                                <tr>
                                    <td>City</td>
                                    <td><a id="customer_ship_city" class="cus_ship_city" data-connectname="<?= $final_channel_accquired ?>" data-title="Start typing City.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="#" data-original-title="" title="" class="editable editable-click"><?= $ship_city; ?></a></td>
                                </tr>
                                <tr>
                                    <td>Zip Code</td>
                                    <td><a id="customer_ship_zip" class="cus_ship_zip" data-connectname="<?= $final_channel_accquired ?>" data-title="Start typing Zip Code.." data-placement="right" data-pk="1" data-type="typeaheadjs" href="#" data-original-title="" title="" class="editable editable-click"><?= $ship_zip; ?></a></td>
                                </tr>

        <!--                                                    <tr>
                                                                <td></td>
                                                            </tr>-->
                                </tbody>
                            </table>
                            </div>
                        </div>
                        </div>
                        </div>  

			<!--End for accordian!-->
                        <button class="btn btn-space btn-primary" onclick="savecustomer()">Update</button>
                        <input type="hidden" id='customer_id' value="<?php echo $model->id ?>">
                    </div>
                </div> 
            </div>
        </div>
        <div class="col-md-7">
            <div class="widget widget-fullwidth widget-small">
                <div class="widget-head xs-pb-30">
                    <!--                    <div class="tools">
                                            <span class="icon mdi mdi-chevron-down"></span><span class="icon mdi mdi-refresh-sync"></span><span class="icon mdi mdi-close"></span>
                                        </div>-->
                    <div class="title">Statistics for <?= date('Y')?></div>
                    <!--Customer view page customer id in hidden fiels for performance graph!-->
                    <input type="hidden" value="<?= $model->id; ?>" id="customer_view_id">
                </div>

                <div class="row">
                    <div class="col-xs-12 col-md-6 col-lg-4">
                        <div class="widget widget-tile">
                            <div id="customer_order_spark1" class="chart sparkline"></div>
                            <div class="data-info">
                                <div class="desc">Orders</div>
                                <?php
                                $current_d = date('m');
                                $current_y = date('Y');
                                $month = (int) $current_d;

                                $connection = \Yii::$app->db;
                                $orders_data = $connection->createCommand('SELECT * from `order` where customer_id=' . $customer_id . ' AND YEAR(order_date)="' . $current_y . '"  ');
                                $orders_count = count($orders_data->queryAll());

                                if (!empty($orders_count)):
                                    $new_orders_count = $orders_count;
                                else:
                                    $new_orders_count = 0;
                                endif;
                                ?>
                                <div class="value">
                                    <span class="indicator indicator-equal mdi mdi-chevron-right"></span>
                                    <span data-toggle="counter" data-end="<?= $new_orders_count; ?>" class="number"><?= $new_orders_count; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6 col-lg-4">
                        <div class="widget widget-tile">
                            <div id="customer_return_spark2" class="chart sparkline"></div>
                            <div class="data-info">
                                <div class="desc">Returns</div>

                                <?php
                                $current_d_return = date('m');
                                $current_y_return = date('Y');
                                $month_return = (int) $current_d_return;

                                $connection_return = \Yii::$app->db;
                                $orders_data_return = $connection_return->createCommand('SELECT * from `order`  Where customer_id=' . $customer_id . ' AND YEAR(created_at)="' . $current_y . '"  AND  status IN("Cancel","Refunded","Returned")');
                                $orders_count_return = count($orders_data_return->queryAll());
                                //

                                if (!empty($orders_count)):
                                    $new_orders_count_return = $orders_count_return;
                                else:
                                    $new_orders_count_return = 0;
                                endif;
                                ?>

                                <div class="value"><span class="indicator indicator-positive mdi mdi-chevron-up"></span>
                                    <span data-toggle="counter" data-end="<?= $new_orders_count_return; ?>" class="number"><?= $new_orders_count_return; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6 col-lg-4">
                        <div class="widget widget-tile">
                            <div id="customer_item_purchase_spark3" class="chart sparkline"></div>
                            <div class="data-info">
                                <div class="desc">Items Purchased</div>

<?php
$current_d_item = date('m');
$current_y_item = date('Y');
$month_item = (int) $current_d_item;

$connection_item = \Yii::$app->db;
$orders_data_item = $connection_item->createCommand('SELECT SUM(product_quantity) as value from `order` Where customer_id=' . $customer_id . ' AND YEAR(created_at)="' . $current_y_item . '" GROUP BY product_quantity');

$orders_count_item = $orders_data_item->queryAll();

if (!empty($orders_count_item)):
    $new_orders_count_return = $orders_count_item[0]['value'];
else:
    $new_orders_count_return = 0;
endif;
?>
                                <div class="value"><span class="indicator indicator-positive mdi mdi-chevron-up"></span>
                                    <span data-toggle="counter" data-end="<?= $new_orders_count_return; ?>" class="number"><?= $new_orders_count_return; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default panel-table">
                            <div class="panel-heading"> 
                                <div class="tools"><span class="icon mdi mdi-download"></span><span class="icon mdi mdi-more-vert"></span></div>
                                <div class="title">Order Activity : 5 most recent orders</div>
                            </div>
                            <div class="panel-body table-responsive">
                                <table class="table table-striped table-borderless">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Channel Sold On</th>
                                            <th>Order Value</th>
                                            <th>Date Ordered</th>
                                            <th>Order Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="no-border-x">
                                        <!-- check Customers Orders--> 
<?php
$customer_orders = Order::find()->Where(['customer_id' => $model->id])->orderBy(['id' => SORT_DESC,])->limit(5)->all();

if (count($customer_orders) == 0) {
    ?>

    					<tr class="odd"><td valign="top" colspan="6" style="text-align:center;" class="dataTables_empty">No data available in table</td></tr>

    <?php
}

foreach ($customer_orders as $customer_orders_data) {
    $user_connection = $customer_orders_data->userConnection;
    $channel_name = $user_connection->getPublicName();

    $total_amount = $customer_orders_data->total_amount;
    $order_value = number_format((float) $total_amount, 2, '.', '');
    //Get Store Id In Order Channel for channel sold on 

    $order_status = $customer_orders_data->status;
    $label = '';
    if ($order_status == 'Completed') {
	    $label = 'label-success';
    }

    if ($order_status == 'Returned' || $order_status == 'Refunded' || $order_status == 'Cancel' || $order_status == 'Partially Refunded') {
	    $label = 'label-danger';
    }

    if ($order_status == 'In Transit' || $order_status == 'On Hold') {
	    $label = 'label-default';
    }

    if ($order_status == 'Awaiting Fulfillment' || $order_status == 'Awaiting Shipment' || $order_status == 'Incomplete' || $order_status == 'waiting-for-shipment' || $order_status == 'Pending' || $order_status == 'Awaiting Payment' || $order_status == 'On Hold') {
	    $label = 'label-warning';
    }
    if ($order_status == 'Shipped' || $order_status == 'Partially Shipped') {
	    $label = 'label-primary';
    }

    $user = Yii::$app->user->identity;
//    if (isset($user->currency) and $user->currency != 'USD') {
//        $conversion_rate = Stores::getCurrencyConversionRate('USD', $user->currency);
//        $order_value = $order_value * $conversion_rate;
//        $order_value = number_format((float) $order_value, 2, '.', '');
//
//    }
    $selected_currency = \common\models\CurrencySymbol::find()->where(['name' => strtolower($user->currency)])->select(['id', 'symbol'])->asArray()->one();
    if (isset($selected_currency) and ! empty($selected_currency)) {
	    $currency_symbol = $selected_currency['symbol'];
    }
    ?>
    					<tr>
    					    <td><a  href="/order/view?id=<?php echo $customer_orders_data->id; ?>"><?php echo $customer_orders_data->connection_order_id; ?></a></td>
    					    <td class="center"><?= trim($channel_name); ?></td>
    					    <td class="center"><?= $currency_symbol; ?><?= number_format($order_value, 2); ?></td>
    					    <td class="center"><?= $customer_orders_data->order_date; ?></td>
    					    <td><span class="label  <?= $label; ?>"><?= $order_status; ?></span></td>
    					</tr>
<?php }; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!--</div>-->
