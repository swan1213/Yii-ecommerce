<?php
/**
 * Created by PhpStorm.
 * User: whitedove
 * Date: 1/15/2018
 * Time: 9:29 AM
 */

namespace frontend\components;


use common\models\Order;
use common\models\Product;
use common\models\UserConnection;
use common\models\UserProfile;
use yii\base\Component;

class SfexpressComponent extends Component
{
    public $username;
    public $password;
    public $api_base_url = "https://sit.api.sf-express-us.com/api/orderservice/submitorder";
    //public $server_url = "https://sit.api.sf-express-us.com/api/orderservice/agentorder";

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function sendOrder($user_connection_id) {
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $user_connection = UserConnection::find()->where(['id' => $user_connection_id])->one();
        if (!empty($user_connection)) {

            $userData = UserProfile::find()->where(['user_id' => $user_connection->user_id])->one();
            $sf_orders_data = Order::find()->where(['user_id' => $user_connection->user_id, 'user_connection_id' => $user_connection_id])->all();

            $KEYJsonArrayData = array('UserName' => $this->username, 'Password' => $this->password);
            //echo '<pre>'; print_r($sf_orders_data); echo '</pre>'; die('sdfa');
            $orderJsonArrayData = array();
            $url = 'https://sit.api.sf-express-us.com/api/orderservice/submitorder';
            $count = 0;
            foreach ($sf_orders_data as $sf):
                //echo '<pre>'; print_r($sf_orders_data_all); echo '</pre>'; die('sdfa');
                if (!empty($sf->sf_data)) {
                    $sf_array = json_decode($sf->sf_data, true);
                    if ($sf_array['Success']) {
                        continue;
                    }
                }
                $product_data = $sf->orderProducts;
                $customer_data = $sf->customer;
                $orderIdForSF = $sf->id . '-' . $sf->customer_id;
                $orderJsonArrayData['CneeContactName'] = $customer_data->first_name;
                $orderJsonArrayData['CneeCompany'] = 'Sf Express';
                $orderJsonArrayData['CneeAddress'] = $customer_data->customerAddress->street_1;
                $orderJsonArrayData['CneeCity'] = $customer_data->customerAddress->city;
                $orderJsonArrayData['CneeProvince'] = $customer_data->customerAddress->state;
                $orderJsonArrayData['CneeCountry'] = $customer_data->customerAddress->country;
                $orderJsonArrayData['CneePostCode'] = $customer_data->customerAddress->zip;
                $orderJsonArrayData['CneePhone'] = $customer_data->customerAddress->phone;

                $orderJsonArrayData['SenderContactName'] = $userData->firstname;
                $orderJsonArrayData['SenderPhone'] = $userData->phoneno;
                $orderJsonArrayData['SenderAddress'] = $userData->corporate_addr_street1;
                $orderJsonArrayData['SenderCity'] = $userData->corporate_addr_city;
                $orderJsonArrayData['SenderProvince'] = $userData->corporate_addr_state;
                $orderJsonArrayData['SenderCountry'] = $userData->corporate_addr_country;
                $orderJsonArrayData['SenderPostCode'] = $userData->corporate_addr_zipcode;

                $orderJsonArrayData['ReferenceNo1'] = $orderIdForSF;
                $orderJsonArrayData['ReferenceNo2'] = 'IZIEF4342324324D23434';
                $orderJsonArrayData['ExpressType'] = '101';
                $orderJsonArrayData['ParcelQuantity'] = '1';
                $orderJsonArrayData['PayMethod'] = '3';
                $orderJsonArrayData['TaxPayType'] = '2';
                $orderJsonArrayData['Currency'] = 'USD';

                $i = 0;
                foreach ($product_data as $prodata):
                    $id = $prodata->product_id;
                    $productData = Product::find()->Where(['id' => $id])->one();
                    if (!empty($productData->name)) {
                        $orderJsonArrayData['Items'][$i]['Name'] = $productData->name;
                    } else {
                        $orderJsonArrayData['Items'][$i]['Name'] = 'Testing';
                    }
                    if (!empty($productData->stock_quantity)) {
                        $orderJsonArrayData['Items'][$i]['Count'] = str_replace(',', '', number_format($productData->stock_quantity, 0));
                    } else {
                        $orderJsonArrayData['Items'][$i]['Count'] = '1';
                    }
                    $orderJsonArrayData['Items'][$i]['Unit'] = 'pcs';
                    if (!empty($productData->price)) {
                        $orderJsonArrayData['Items'][$i]['Amount'] = $productData->price;
                    } else {
                        $orderJsonArrayData['Items'][$i]['Amount'] = '0';
                    }
                    $orderJsonArrayData['Items'][$i]['SourceArea'] = 'US';
                    if (!empty($productData->weight)) {
                        $orderJsonArrayData['Items'][$i]['Weight'] = $productData->weight;
                    } else {
                        $orderJsonArrayData['Items'][$i]['Weight'] = '1.0';
                    }
                    $i++;
                endforeach;

                $order_data_json = array(
                    'Order' => $orderJsonArrayData,
                    'Gateway' => 'JFK',
                    'NetworkCredential' => $KEYJsonArrayData,
                );
                $header = array(
                    "cache-control: no-cache",
                    "charset: utf-8",
                    "content-type: application/json"
                );

                $response = CustomFunction::curlHttp($this->api_base_url, $order_data_json, 'POST', $header, 1);
                $json_response = json_decode($response, true);
                if(isset($json_response['error'])) {
                    throw new \Exception($json_response['error']);
                    return false;
                }
                if ($json_response['Success']) {
                    $sf->sf_data = $json_response;
                    $sf->save(false);
                }
            endforeach;
        }
        return true;
    }
}