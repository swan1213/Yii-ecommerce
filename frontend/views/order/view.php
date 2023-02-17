<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Breadcrumbs;
use common\models\Customer;
use common\models\Order;
use common\models\CurrencyConversion;
use common\models\CurrencySymbol;
use common\models\OrderProduct;
use common\models\Product;
use common\models\Connection;
use frontend\controllers\OrderController;

$userProfile = Yii::$app->user->identity->userProfile;
$company_street1 = $userProfile->corporate_addr_street1;
$company_street2 = $userProfile->corporate_addr_street2;
$company_country = $userProfile->corporate_addr_country;
$company_state = $userProfile->corporate_addr_state;
$company_city = $userProfile->corporate_addr_city;
$company_zipcode = $userProfile->corporate_addr_zipcode;

$connection_order_id = $model->connection_order_id;
$this->title = 'Invoice #' . $connection_order_id . '';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['/order']];
$this->params['breadcrumbs'][] = $this->title;
$customer = $model->customer;
// For amount Invoice;
$orderFee = $model->fee;
$marketplace_fees = $orderFee->market_place_fee;
$creditcard_fees = $orderFee->credit_card_fee;
$total_amount = $model->total_amount;
$base_shipping_cost = $orderFee->base_shippping_cost;
$shipping_cost_tax = $orderFee->shipping_cost_tax;
$base_handling_cost = $orderFee->base_handling_cost;
$handling_cost_tax = $orderFee->handling_cost_tax;
$base_wrapping_cost = $orderFee->base_wrapping_cost;
$wrapping_cost_tax = $orderFee->wrapping_cost_tax;
$payment_method = $orderFee->payment_method;
$discount_amount = $orderFee->discount_amount;
$coupon_discount = $orderFee->coupon_discount;

$cost = $total_amount + $discount_amount;
$subtotal = $marketplace_fees + $creditcard_fees + $total_amount;

//For status label
$order_status = $model->status;
$label='';
if ($order_status == 'Completed') :
    $label = 'label-success';
endif;

if ($order_status == 'Returned' || $order_status == 'Refunded' || $order_status == 'Partially Refunded') :
    $label = 'label-danger';
endif;

if ($order_status == 'In Transit'):
    $label = 'label-primary';
endif;

if ($order_status == 'Awaiting FulFillment' || $order_status == 'Awaiting Shipment' || $order_status == 'Incomplete'):
    $label = 'label-warning';
endif;
if ($order_status == 'Shipped' || $order_status == 'Partially Shipped'):
    $label = 'label-primary';
//                           $check='checked';
endif;
$fulfillment_enabled = true;

$orders_data = $model->orderProducts;

$billing_country = explode(",", "$model->bill_street_1 $model->bill_street_1 $model->bill_city $model->bill_state $model->bill_zip $model->bill_country");
$shipping_country = explode(",", "$model->ship_street_1 $model->ship_street_1 $model->ship_city $model->ship_state $model->ship_zip $model->ship_country");

$bill_to_country = $model->bill_country;
$ship_to_country = $model->ship_country;
$currency_symbol = "$";
?>  
<div class="page-head">
    <h2 class="page-head-title"><?= Html::encode($this->title) ?></h2>
    <ol class="breadcrumb page-head-nav">
	<?php echo Breadcrumbs::widget([ 'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],]);?>
    </ol>
    <?php
    if(!empty($model->sf_data) && $fulfillment_enabled){
        $sf_array = json_decode($model->sf_data, true);
        $data =  $sf_array['Data'];
        $hawbs = $data['Hawbs'];
        if ($sf_array['Success']) {
            ?>
            <div class="col-xs-12 invoice-order">
                <h3 style="float: left;">Tracking ID : <a target="_blank" href="https://track.aftership.com/sf-express/<?=$hawbs;?>"><?=$hawbs;?></a></h3>
                <!--<span style="display: block;">Click this button to view order tracking status</span>-->
                <!--	    <a target="_blank" href="http://www.sf-express.com/us/en/dynamic_functions/waybill/">
                        <button class="btn btn-lg btn-space btn-primary">Track Order</button>
                        </a>-->
                <!--	    <a target="_blank" href="--><?//=$InvoiceToPrint;?><!--">-->
                <!--		<button class="btn btn-lg btn-space btn-primary">Print Invoice</button>-->
                <!--	    </a>-->
                <?php if(isset($data['AgentLabelToPrint'])) {?>
                    <a target="_blank" href="<?=$data['AgentLabelToPrint'];?>">
                        <button class="btn btn-lg btn-space btn-primary">First Mile Label</button>
                    </a>
                <?php } ?>

                <?php if(isset($data['LabelToPrint'])) {?>
                    <a target="_blank" href="<?=$data['LabelToPrint'];?>">
                        <button class="btn btn-lg btn-space btn-primary">Last Mile Label</button>
                    </a>
                <?php } ?>

                <?php if(isset($data['InvoiceToPrint'])) {?>
                    <a target="_blank" href="<?=$data['InvoiceToPrint'];?>">
                        <button class="btn btn-lg btn-space btn-primary">Invoice</button>
                    </a>
                <?php } ?>
            </div>
        <?php }
    } ?>
</div>    


<div class="row">
    <div class="col-md-12">
        <div class="invoice">
            <div class="row invoice-header">
                <div class="col-xs-7">
                    <div class="invoice-logo"></div>
                    <div class="invoice-person company-details-invoice">
                        <span><?php echo preg_replace('/^([^a-zA-Z0-9])*/', '', $company_street1 . ' ' . $company_street2); ?></span>
                        <span><?php echo preg_replace('/^([^a-zA-Z0-9])*/', '', $company_city . ' ' . $company_state); ?></span>
                        <span><?php echo preg_replace('/^([^a-zA-Z0-9])*/', '', $company_country); ?></span>
                        <span><?php echo preg_replace('/^([^a-zA-Z0-9])*/', '', $company_zipcode); ?></span>
                    </div>
                </div>
                <div class="col-xs-5 invoice-order">
                    <span class="incoice-staus incoice-date"><span class="label  <?php echo $label; ?>"><?php echo $order_status; ?></span></span>
                    <span class="invoice-id">Invoice #<?php echo $model->connection_order_id; ?></span>
                    <span class="incoice-date"><?php echo date('F j, Y', strtotime($model->created_at)); ?></span>
                </div>
            </div>
            <div class="row invoice-data">
                <div class="col-xs-5 invoice-person">
                    <span class="f-22">Bill To :</span>
                    <span><?php  echo preg_replace('/^([^a-zA-Z0-9])*/', '', $model->bill_street_1.', '.$model->bill_street_2); ?> </span>
                     <span><?php echo preg_replace('/^([^a-zA-Z0-9])*/', '', $model->bill_city.', '.$model->bill_state.', '.$model->bill_zip.', '.$model->bill_country); ?> </span>
                     
                </div>
                <div class="col-xs-2 invoice-payment-direction"><i class="icon mdi mdi-chevron-right"></i></div>
                <div class="col-xs-5 invoice-person">
                    <span class="f-22">Ship To :</span>
                    <span><?php echo preg_replace('/^([^a-zA-Z0-9])*/', '', $model->ship_street_1.', '.$model->ship_street_2)?> </span>
                     <span><?php echo preg_replace('/^([^a-zA-Z0-9])*/', '', $model->ship_city.', '.$model->ship_state.', '.$model->ship_zip.', '.$model->ship_country); ?> </span>
                    
                    <span><?php // echo preg_replace('/^([^a-zA-Z0-9])*/', '', $model->bill_street_1.', '.$model->bill_street_2); ?></span>
                </div>
            </div>

            <!-- for products section !-->
            <div class="row">
                <div class="col-md-12">
                    <table class="invoice-details">
                        <tr>
                            <th>Item Description </th>
                            <th>OTY</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
	    <?php
		$user = Yii::$app->user->identity;
		$currency_unit = $customer->user->currency;
//		if (isset($currency_unit)) {
//            $conversion_rate = CurrencyConversion::getDbConversionRate($currency_unit);
//		}

		foreach ($orders_data as $order_value) :
		    $product_name = isset($order_value->product->name)?$order_value->product->name:'';
		    $price = isset($order_value->price)?$order_value->price:'0';
		    $qty = isset($order_value->qty)?$order_value->qty:'0';
		    $total = isset($order_value->price)?$order_value->price * $qty:'0';
//		    $price = $price ;
//		    $price = number_format((float) $price, 2, '.', '');
//		    $total = $total ;
//		    $total = number_format((float) $total, 2, '.', '');
		    $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($currency_unit)])->select(['id', 'symbol'])->asArray()->one();
			if (isset($selected_currency) and ! empty($selected_currency)) {
			    $currency_symbol=$selected_currency['symbol'];
			}
			else
                $currency_symbol = "$";
	    ?>
			<tr>
			    <td class="description"><?php echo $product_name; ?></td>
			    <td class="hours"><?php echo $qty; ?></td>
			    <td class="amount"><?php echo $currency_symbol; ?><?php echo $price; ?></td>
			    <td class="amount"><?php echo $currency_symbol; ?><?php echo $total; ?></td>
			</tr>
	    <?php endforeach; ?>
                    </table>
                </div>
            </div>
            <!-- End for products section !-->

            <div class="row invoice-table-2">
                <div class="col-md-12">
                    <table class="invoice-details">
                        <tr>
                            <th style="width:60%">Fees</th>
                            <th style="width:15%" class="amount"></th>
                            <th style="width:17%" class="amount">Amount</th>
                        </tr>
                        <tr>
                            <td class="description">Marketplace Fees</td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($marketplace_fees, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="description">Shipping Cost</td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($base_shipping_cost, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="description">Shipping Cost Tax </td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($shipping_cost_tax, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="description">Handling Cost</td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($base_handling_cost, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="description">Handling Cost Tax </td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($handling_cost_tax, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="description">Wrapping Cost</td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($base_wrapping_cost, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="description">Wrapping Cost Tax</td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($wrapping_cost_tax, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="description">Credit Card Fees</td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($creditcard_fees, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="description">Cost</td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($cost, 2); ?></td>
                        </tr>
                        <tr>
                            <td class="description">Discount Amount</td>
                            <td class="hours"></td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($discount_amount, 2); ?></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="summary">Subtotal</td>
                            <td class="amount"><?php echo  $currency_symbol;?><?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="summary total">Total</td>
                            <td class="amount total-value"><?php echo  $currency_symbol;?><?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="row invoice-footer">
                <div class="col-md-12" id="pdf">
                    <a target="_blank" href="/order/savepdf?id=<?=$model->id;?>"><button class="btn btn-lg btn-space btn-primary" >Save PDF</button></a>
                    <button class="btn btn-lg btn-space btn-primary" onclick="printFunction()">Print</button>
                </div>
            </div>
        </div>
    </div>
</div>
