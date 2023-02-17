<?php
namespace frontend\components;


use common\models\Fulfillment;
use common\models\Order;
use common\models\Product;
use common\models\ProductVariation;
use common\models\UserConnection;
use common\models\VariationValue;
use frontend\components\ShipstationClient;
use yii\base\Component;


class ShipstationComponent extends Component
{
    const timeOutLimit = 1;
    const errorTimeOutLimit = 20;
    const connectionName = "ShipStation";
    const requestDataLimit = 100;


    public static function sendOrder($user_connection_id, $user_fulfillment_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $shipstationConection = Fulfillment::findOne(['id' => $user_fulfillment_id]);

        $connection_info = $shipstationConection->connection_info;
        $shipStationClient = new ShipstationClient($connection_info);


        $connectionOrders = Order::find()
            ->where(['user_connection_id' => $user_connection_id ])
            ->andWhere(['visible' => Order::ORDER_VISIBLE_ACTIVE])
            ->andWhere(['fulfill_status' => Order::ORDER_FULFILL_STATUS_NO])
            ->andWhere(['in', 'status', ["Awaiting Shipment", "Awaiting FulFillment"]])
            ->all();


        $orderConnection = UserConnection::findOne([
            'id' => $user_connection_id
        ]);
        $ordersData = [];
        $orderIds = [];

        if ( !empty($connectionOrders) ) {

            $userOrdersCount = 1;

            foreach ($connectionOrders as $userOrder){

                $itemsData = [];

                $orderProducts = $userOrder->orderProducts;

                if ( !empty($orderProducts) ){

                    foreach($orderProducts as $orderProduct){

                        $orderProduct_id = $orderProduct->product_id;
                        $productDetails = Product::findOne(['id' => $orderProduct_id]);

                        $productName = $productDetails->name;

                        $productImages = $productDetails->productImages;

                        $productImageUrl = null;
                        if ( !empty($productImages) ){

                            foreach($productImages as $p_image){

                                if ( !empty($p_image) ){
                                    $productImageUrl = $p_image->link;
                                    break;
                                }

                            }

                        }

                        $orderProductSku = $orderProduct->order_product_sku;
                        $productVariation = ProductVariation::findOne([
                            'product_id' => $productDetails->id,
                            'sku_value' => $orderProductSku
                        ]);

                        $optionsData = [];

                        if ( !empty($productVariation) ){

                            $order_variation = $productVariation->variation;
                            $variationItems = $order_variation->getVariationItemList();

                            if ( !empty($variationItems) ){

                                $variationsData = VariationValue::findAll(['in', 'id', $variationItems]);

                                if ( !empty($variationsData) ){

                                    foreach ($variationsData as $each_var_data){
                                        $optionsData[] = [
                                            "name" => $each_var_data->getVariationItem->name,
                                            "value" => $each_var_data->value
                                        ];

                                    }

                                }

                            }

                        }

                        $itemLineItemKey = "Od-P-" . $orderProduct->id;
                        $itemsData[] = [
                            "lineItemKey" => $itemLineItemKey,
                            "sku" => $orderProductSku,
                            "name" => $productName,
                            "imageUrl" => $productImageUrl,
                            "weight" => [
                                "value" => !empty($productDetails->weight)?$productDetails->weight:'0',
//                                "units" => "ounces"
                            ],
                            "quantity" => $orderProduct->qty,
                            "unitPrice" => $orderProduct->price,
//                            "taxAmount" => 2.50,
//                            "shippingAmount" => 5.00,
//                            "warehouseLocation" => "Aisle 1, Bin 7",
                            "options" => $optionsData,
                            "productId" => $productDetails->id,
//                            "fulfillmentSku" => null,
//                            "adjustment" => false,
                            "upc" => $productDetails->upc
                        ];
                    }

                }

                $orderNumber = $orderConnection->connection->getConnectionName() . "-" . $userOrder->connection_order_id;

                $ordersData[] =
                    [
                        "orderNumber" => $orderNumber,
                        "orderKey" => $userOrder->id,
                        "orderDate" => $userOrder->order_date,
//                        "paymentDate" => "2015-06-29T08:46:27.0000000",
//                        "shipByDate" => "2015-07-05T00:00:00.0000000",
                        "orderStatus" => "awaiting_shipment",
                        "customerId" => $userOrder->customer_id,
                        "customerUsername" => $userOrder->customer->getCustomerFullName(),
                        "customerEmail" => $userOrder->customer->email,
                        "billTo" => [
                            "name" => !empty($userOrder->getBillName())?$userOrder->getBillName():$userOrder->customer->getCustomerFullName(),
                            "company" => $userOrder->bill_company,
                            "street1" => $userOrder->bill_street_1,
                            "street2" => $userOrder->bill_street_2,
                            "street3" => null,
                            "city" => $userOrder->bill_city,
                            "state" => $userOrder->bill_state,
                            "postalCode" => $userOrder->bill_zip,
                            "country" => $userOrder->bill_country_iso,
                            "phone" => $userOrder->bill_phone,
                            "residential" => null
                        ],
                        "shipTo" => [
                            "name" => !empty($userOrder->getShipName())?$userOrder->getShipName():$userOrder->customer->getCustomerFullName(),
                            "company" => $userOrder->ship_company,
                            "street1" => $userOrder->ship_street_1,
                            "street2" => $userOrder->ship_street_2,
                            "street3" => null,
                            "city" => $userOrder->ship_city,
                            "state" => $userOrder->ship_state,
                            "postalCode" => $userOrder->ship_zip,
                            "country" => $userOrder->ship_country_iso,
                            "phone" => $userOrder->ship_phone,
                            "residential" => true
                        ],
                        "items" => $itemsData,
                        "amountPaid" => $userOrder->total_amount,
                        "taxAmount" => $userOrder->fee->shipping_cost_tax,
                        "shippingAmount" => $userOrder->fee->base_shippping_cost,
                        "paymentMethod" => $userOrder->fee->payment_method,
//                                    "customerNotes" => "Thanks for ordering!",
//                                    "internalNotes" => "Customer called and would like to upgrade shipping",
//                                    "gift" => true,
//                                    "giftMessage" => "Thank you!",
//                                    "requestedShippingService" => "Priority Mail",
//                                    "carrierCode" => "fedex",
//                                    "serviceCode" => "fedex_2day",
//                                    "packageCode" => "package",
//                                    "confirmation" => "delivery",
//                                    "shipDate" => "2015-07-02",
//                                    "weight" => [
//                                        "value" => 25,
//                                        "units" => "ounces"
//                                    ],
//                                    "dimensions" => [
//                                        "units" => "inches",
//                                        "length" => 7,
//                                        "width" => 5,
//                                        "height" => 6
//                                    ],
//                                    "insuranceOptions" => [
//                                        "provider" => "carrier",
//                                        "insureShipment" => true,
//                                        "insuredValue" => 200
//                                    ],
//                                    "internationalOptions" => [
//                                        "contents" => null,
//                                        "customsItems" => null
//                                    ],
//                                    "advancedOptions" => [
//                                        "warehouseId" => 98765,
//                                        "nonMachinable" => false,
//                                        "saturdayDelivery" => false,
//                                        "containsAlcohol" => false,
//                                        "mergedOrSplit" => false,
//                                        "mergedIds" => [],
//                                        "parentId" => null,
//                                        "storeId" => 12345,
//                                        "customField1" => "Custom data that you can add to an order. See Custom Field #2 & #3 for more info!",
//                                        "customField2" => "Per UI settings, this information can appear on some carrier's shipping labels. See link below",
//                                        "customField3" => "https://help.shipstation.com/hc/en-us/articles/206639957",
//                                        "source" => "Webstore",
//                                        "billToParty" => null,
//                                        "billToAccount" => null,
//                                        "billToPostalCode" => null,
//                                        "billToCountryCode" => null
//                                    ]
                ];

                if ( $userOrdersCount == self::requestDataLimit ) {

                    $sendOrdersData = $ordersData;
                    $response = $shipStationClient->call('post', 'orders/createorders', $sendOrdersData);
                    $json_response = @json_decode($response, true);

                    $hasErrors = isset($json_response['hasErrors'])?$json_response['hasErrors']:true;

                    if ( $hasErrors === false ) {
                        Order::updateAll(
                            [
                                'fulfill_status' => Order::ORDER_FULFILL_STATUS_YES
                            ],
                            ['in', 'id', $orderIds]
                        );
                    }


                    $userOrdersCount = 1;
                    $ordersData = [];
                    $orderIds = [];
                }

                $userOrdersCount ++;
                array_push($orderIds, $userOrder->id);
            }

            if ( !empty($ordersData) ){
                $sendOrdersData = $ordersData;
                $response = $shipStationClient->call('post', 'orders/createorders', $sendOrdersData);

                $json_response = @json_decode($response, true);

                $hasErrors = isset($json_response['hasErrors'])?$json_response['hasErrors']:true;

                if ( $hasErrors === false ) {
                    Order::updateAll(
                        [
                            'fulfill_status' => Order::ORDER_FULFILL_STATUS_YES
                        ],
                        ['in', 'id', $orderIds]
                    );
                }

            }

        }


    }

}