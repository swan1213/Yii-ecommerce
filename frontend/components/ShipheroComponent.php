<?php

namespace frontend\components;


use common\models\Fulfillment;
use common\models\Order;
use common\models\OrderFee;
use common\models\UserConnection;
use yii\base\Component;

class ShipheroComponent extends Component
{
    public $apiKey;
    public $apiSecret;
    public $api_base_url = "https://api-gateway.shiphero.com/v1.2/general-api/order-creation";

    public function __construct($config)
    {
        $this->apiKey = $config['apiKey'];
        $this->apiSecret = $config['apiSecret'];
    }

    public function sendOrder($user_connection_id, $user_fulfillment_id) {
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $shipheroConection = Fulfillment::findOne(['id' => $user_fulfillment_id]);

        if (!empty($shipheroConection)) {

            $orders = Order::find()
                ->where(['user_connection_id' => $user_connection_id])
                ->andWhere(['visible' => Order::ORDER_VISIBLE_ACTIVE])
                ->andWhere(['fulfill_status' => Order::ORDER_FULFILL_STATUS_NO])
                //->andWhere(['in', 'status', ["Awaiting Shipment", "Awaiting FulFillment"]])
                ->all();

            if (!empty($orders)) {

                foreach ($orders as $order) {
                    if ($this->makeExportData($order)) {
                        $order->fulfill_status = Order::ORDER_FULFILL_STATUS_YES;
                        $order->save(false);
                    }
                }
            }
        }
    }

    public function makeExportData($order) {
        $itemsData = [];
        $feed_row = OrderFee::find()->where(['id' => $order->fee_id])->one();
        $status = 'cancelled';
        switch (strtolower($order->status)) {
            case 'return':
                $status = 'backorder';
                break;
            case 'pending':
                $status = 'pending';
                break;
            case 'shipped':
                $status = 'fulfilled';
                break;
            case 'delivered':
                $status = 'fulfilled';
                break;
            case 'cancelled':
                $status = 'canceled';
                break;
        }

        $orderProducts = $order->orderProducts;
        if (!empty($orderProducts)) {
            foreach ($orderProducts as $orderProduct) {
                $itemsData[] = [
                    "id" => $orderProduct->id,
                    "sku" => $orderProduct->product->sku,
                    "name" => $orderProduct->product->name,
                    "price" => $orderProduct->product->price,
                    "quantity" => $orderProduct->qty,
                    "product_id" => $orderProduct->product->id
                ];
            }
        }
        $ordersData =
            [
                "token" => $this->apiKey,
                "email" => $order->customer->email,
                "line_items" => $itemsData,
                "shipping_lines" => [
                    "title" => '',
                    "price" => !empty($feed_row)?$feed_row->base_shippping_cost:0
                ],
                "note_attributes" => [
                    "name" => '',
                    "value" =>''
                ],
                "shipping_address" => [
                    "first_name" => $order->ship_fname,
                    "last_name" => $order->ship_lname,
                    "company" => !empty($order->ship_company)?$order->ship_company:"Elliot",
                    "phone" => $order->ship_phone,
                    "address1" => $order->ship_street_1,
                    "address2" => $order->ship_street_2,
                    "city" => $order->ship_city,
                    "province" => $order->ship_state,
                    "province_code" => $order->ship_country_iso,
                    "zip" => $order->ship_zip,
                    "country" => $order->ship_country,
                    "country_code" => $order->ship_country_iso
                ],
                "billing_address" => [
                    "first_name" => $order->bill_fname,
                    "last_name" => $order->bill_lname,
                    "company" => !empty($order->bill_company)?$order->bill_company:"Elliot",
                    "phone" => $order->bill_phone,
                    "address1" => $order->bill_street_1,
                    "address2" => $order->bill_street_2,
                    "city" => $order->bill_city,
                    "province" => $order->bill_state,
                    "province_code" => $order->bill_country_iso,
                    "zip" => $order->bill_zip,
                    "country" => $order->bill_country,
                    "country_code" => $order->bill_country_iso
                ],
                "total_tax" => !empty($feed_row)?$feed_row->shipping_cost_tax:0,
                "order_id" => $order->id,
                "profile" => $order->getShipName(),
                "subtotal_price" => $order->total_amount,
                "created_at" => date('Y-m-d h:i:s', time()),
                "fulfillment_status" => $status,
                "required_ship_date" => $order->order_date,
                "total_discounts" => '0',
                "total_price" => $order->total_amount
            ];
        $header = array(
            'Content-Type: application/json',
            'X-Api-Key: ' . $this->apiKey
        );

        $response = CustomFunction::curlHttp($this->api_base_url, $ordersData, 'POST', $header, 1);
        $json_response = json_decode($response, true);
        var_dump($json_response);
        if(isset($json_response['error'])) {
            throw new \Exception($json_response['error']);
            return false;
        }
        return true;
    }
}