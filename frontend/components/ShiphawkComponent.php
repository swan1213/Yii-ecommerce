<?php

namespace frontend\components;

use yii\base\Component;

use common\models\OrderFee;
use common\models\OrderProduct;
use common\models\Connection;
use common\models\Order;
use common\models\UserConnection;
use frontend\components\CustomFunction;

class ShiphawkComponent extends Component{
	private $user_id;
	private $product_key;
	private $fullment_list_id;

	public function __construct($user_id, $product_key, $fullment_list_id) {
		$this->user_id = $user_id;
		$this->product_key = $product_key;
		$this->fullment_list_id = $fullment_list_id;
	}

	public function sendOrders() {
		$user_connection_instances = UserConnection::find()->where([
			'user_id' => $this->user_id,
			'fulfillment_list_id' => $this->fullment_list_id
		])->all();

		if(!empty($user_connection_instances) and count($user_connection_instances) > 0) {
			foreach ($user_connection_instances as $single_user_connection) {
				$order_instances = Order::find()->where([
					'user_connection_id' => $single_user_connection->id,
					'fulfill_status' => Order::ORDER_FULFILL_STATUS_NO
				])->all();

				if(!empty($order_instances) and count($order_instances) > 0) {
					foreach ($order_instances as $single_order_instance) {

						$this->makeExportData($single_order_instance);
	                    $single_order_instance->fulfill_status = Order::ORDER_FULFILL_STATUS_YES;
	                    $single_order_instance->save(false);
					}
				}
			}
		}
	}

    public function makeExportData(
		$order
    ) {
		$feed_row = OrderFee::find()->where(['id' => $order->fee_id])->one();
		$order_product_list = OrderProduct::find()->where(['order_id' => $order->id])->all();
		$price = 0;
		$order_line_items = [];

		$staus = 'cancelled';
		switch (strtolower($order->status)) {
			case 'new':
				$status = 'new';
				break;
			
			case 'partially_shipped':
				$status = 'partially_shipped';
				break;

			case 'shipped':
				$status = 'shipped';
				break;

			case 'delivered':
				$status = 'delivered';
				break;

			case 'cancelled':
				$status = 'cancelled';
				break;
		}

		if(!empty($order_product_list) and count($order_product_list) > 0) {
			$price = $order_product_list[0]->price;

			foreach ($order_product_list as $singe_order_product) {
				$order_line_items[] = array(
					'source_system_id' => $singe_order_product->id,
					'name' => '',
					'sku' => $singe_order_product->order_product_sku,
					'quantity' => $singe_order_product->qty,
					'value' => $singe_order_product->price
				);
			}
		}

		$order_template = array(
			'order_number' => $order->connection_order_id,
			'source_system' => '',
			'source_system_id' => '',
			'source_system_processed_at' => date('Y-m-d h:i:s', time()),
			'origin_address' => array(
				'name' => $order->bill_fname." ".$order->bill_lname,
				'company' => $order->bill_company,
				'street1' => $order->bill_street_1,
				'street2' => $order->bill_street_2,
				'city' => $order->bill_city,
				'state' => $order->bill_state,
				'zip' => $order->bill_zip,
				'phone_number' => $order->bill_phone,
				'email' => ''
			),
			'destination_address' => array(
				'name' => $order->ship_fname." ".$order->ship_lname,
				'company' => $order->ship_company,
				'street1' => $order->ship_street_1,
				'street2' => $order->ship_street_2,
				'city' => $order->ship_city,
				'state' => $order->ship_state,
				'zip' => $order->ship_zip,
				'phone_number' => $order->ship_phone,
				'email' => ''
			),
			'order_line_items' => $order_line_items,
			'total_price' => $order->total_amount,
			'shipping_price' => !empty($feed_row)?$feed_row->base_shippping_cost:0,
			'tax_price' => !empty($feed_row)?$feed_row->shipping_cost_tax:0,
			'items_price' => $price,
			'status' => $staus,
			'reference_numbers'=> array()
		);

	  	$addresses_api = 'https://shiphawk.com/api/v4/orders';
		$header = array(
			'Content-Type: application/json',
			'X-Api-Key: '.$this->product_key
		);

		$response = CustomFunction::curlHttp($addresses_api, $order_template, 'POST', $header, 1);
		$json_response = json_decode($response, true);
		if(isset($json_response['error'])) {
            throw new \Exception($json_response['error']);
        }
    }
}