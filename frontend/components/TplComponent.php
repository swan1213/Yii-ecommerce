<?php

namespace frontend\components;
use Yii;
use backend\models\ChannelConnection;     
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use common\models\User;
use common\models\Notification;
use common\models\FulfillmentList;
use common\models\Fulfillment;
use common\models\Order;
use common\models\Customer;
use common\models\UserConnection;


class TplComponent{
    public $server_url;
    private $tpl_key;
    private $tpl_encoded;
    public $token;
    private $paylod_headers = null;
    private $customers = [];
    private $user_id;
    private $tpl_db_id;
    private $customerIdentifier = null;
    private $facilityIdentifier = null;
    
    public function __construct($tpl_key, $tpl_encoded, $user_id, $tpl_db_id) {
        $this->name = "Tpl";
        $this->server_url          = "http://secure-wms.com";
        $this->tpl_key              = $tpl_key;      
        $this->tpl_encoded          = $tpl_encoded;
        $this->user_id          = $user_id;
        $this->tpl_db_id          = $tpl_db_id;


        $data = array(
            "grant_type" => "client_credentials",
            "tpl" =>  $tpl_key,
            "user_login_id" => $user_id
        );

        $data_string = json_encode($data);
        $ch = curl_init($this->server_url. '/AuthServer/api/Token');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Length: ' . strlen($data_string),
                "Connection: keep-alive",
                "Content-Type: application/json; charset=utf-8",
                "Accept : application/json",
                "User-Agent : Fiddler",
                "Authorization : Basic " . $tpl_encoded,
                "Accept-Encoding : gzip,deflate,sdch",
                "Accept-Language : en-US,en;q=0.8")
        );
        $response  = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            return null;
        } else {
            $response_data = json_decode($response);
            if(isset($response_data->access_token)) {
                $this->token = $response_data->access_token;
                $this->createHeader();
            }
            else{
                return null;
            }
        }
    }


    private function createHeader(){  
        if($this->paylod_headers == null){      
            $this->paylod_headers = array(                                                                              
                'Content-Type : application/hal+json; charset=utf-8',
                'Accept : application/hal+json ',
                'Authorization : Bearer ' . $this->token,     
                'Accept-Encoding : gzip,deflate,sdch',
                'Accept-Language : en-US,en;q=0.8'
            );
        }
    }
    
    private function CallAPI($method, $url, $data = false){
        $curl = curl_init();
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }                                                                                              
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);       
        curl_setopt($curl, CURLOPT_URL, $url);               
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, FALSE);                         
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);                           
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);                           
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if($this->paylod_headers != null)
            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->paylod_headers);
        $result = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "";
        }
        else{
            return $result; 
        }         
    }    


    
    public function sendOrders(){      
        if($this->importCustomers()){
            $userConnections = UserConnection::find()->where(['user_id' => $this->user_id, 'fulfillment_list_id' => $this->tpl_db_id])->all();
            foreach ($userConnections as $userConnection) {
                $user_connection_id = $userConnection->id;
                $orders_data = Order::find()->where(['user_connection_id' => $user_connection_id, 'fulfill_status' => Order::ORDER_FULFILL_STATUS_NO])->all();
                if(empty($orders_data))
                    continue;
                foreach ($orders_data as $_order_data)  {
                    $this->makeExportData($_order_data);
                    $_order_data->fulfill_status = Order::ORDER_FULFILL_STATUS_YES;
                    $_order_data->save(false);
                }
            }
            return true;
        }
    }
    

    
    private function makeExportData($order){
        $template = array(
            "customerIdentifier" => array(
                "id" => $this->customerIdentifier
            ),
            "facilityIdentifier" => array(
                "id" => $this->facilityIdentifier
            ),                           
            "referenceNum" => $order->connection_order_id,
            "upsServiceOptionCharge" => $order->total_amount,
            "billingCode" => $order->status,
            "routingInfo" => array(   
                "mode" => $order->fee->payment_method,
            ),
            "shipTo" => array(
                "companyName" => $order->userConnection->getPublicName(),
                "name" => $order->customer->first_name . " " . $order->customer->last_name,
                "address1" => $order->ship_street_1,
                "address2" => $order->ship_street_2,
                "city" => $order->ship_city,
                "state" => $order->ship_state,
                "zip" => $order->ship_zip,
                "country" => $order->ship_country_iso
            ),
            "earliestShipDate" => $order->created_at,      
            "fulfillInvInfo" => array(
                "fulfillInvShippingAndHandling" => 0.0,
                "fulfillInvTax" => 1.0,
                "fulfillInvSalePrice" => $order->total_amount, 
                "fulfillInvDiscountAmt" => $order->fee->discount_amount,
                "fulfillInvGiftMessage" => "",
                "fulfillInvGiftMessage" => ""      
            ),
            "billing" => array(
                "billingCharges" => array(
                    array(
                        "chargeType" => 1,
                        "subtotal" => $order->total_amount,
                        "details" =>  array(
                            array(
                                "numUnits" => $order->product_quantity,
                                "chargeLabel" => "",
                                "unitDescription" => "",
                                "glAcctNum" => "",
                                "ptItem" => "",
                                "ptItemDescription" => "",
                                "ptArAcct" => "",
                                "systemGenerated" => true
                                )
                        ) 
                    )
                )
            )
        ); 
        $this->sendOrderTo3PL($template);                                    
    }
    
    private function sendOrderTo3PL($order) {
        $data_string = json_encode($order);
        $ch = curl_init($this->server_url . '/orders');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/hal+json; charset=utf-8",
            "Accept : application/hal+json",
            "Authorization : Bearer " . $this->token,
            "Accept-Encoding : en-US,en;q=0.8")
        );
        $response  = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
        } else {
//            $response_data = json_decode($response, true);
//            var_dump($response_data);
        }
    }

    public function importCustomers(){
        $result = $this->CallAPI("GET", $this->server_url . "/customers");
        $this->customers = [];
        if($result != ""){
            $response_data = json_decode($result, true);
            $count = $response_data['totalResults'];
            if(isset($response_data['_embedded']['http://api.3plCentral.com/rels/customers/customer'])){
                $this->customers = $response_data['_embedded']['http://api.3plCentral.com/rels/customers/customer'];
            }
        }
        return $this->checkCustomers();
        //$this->insertCustomers();
    }

    private function checkCustomers(){
        foreach ($this->customers as $_customer){
            $this->customerIdentifier =  $_customer['readOnly']['customerId'];
            $this->facilityIdentifier =  $_customer['primaryFacilityIdentifier']['id'];
            break;
        }
        if($this->customerIdentifier == null || $this->facilityIdentifier == null)
            return false;
        else
            return true;
    }

//    public function importItems(){
//        for($i = 0; $i < count($this->customers); $i++){
//            if(isset($this->customers[$i]['readOnly']['customerId'])){
//                $total_count = 2000;
//                $client_id = $this->customers[$i]['readOnly']['customerId'];
//                $url = $this->server_url . "/customers/{$client_id}/items?pgsiz={$total_count}";
//                $this->importItemsByClient($client_id, $url);
//            }
//        }
//    }
//
//    private function importItemsByClient($client_id, $url){
//        $result = $this->CallAPI("GET", $url);
//        $items = [];
//        if($result != ""){
//            $response_data = json_decode($result, true);
//            if(isset($response_data['_embedded']['http://api.3plCentral.com/rels/customers/item'])){
//                $items = $response_data['_embedded']['http://api.3plCentral.com/rels/customers/item'];
//                foreach($items as $_item){
//                    $this->insertItemByClient($client_id, $_item);
//                }
//            }
//            if(isset($response_data['_links']['next']['href'])){
//                $this->importItemsByClient($client_id, $this->server_url . $response_data['_links']['next']['href']);
//            }
//        }
//    }
//
//    private function insertItemByClient($client_id, $product_data){
//        $sku_id = $product_data['sku'];
//        $sku_name = $product_data['description'];
//        $reorderQuantity = isset($product_data['options']['inventoryUnit']['reorderQuantity']) ? $product_data['options']['inventoryUnit']['reorderQuantity'] : 0;
//        $checkProductModel = Products::find()->where(['product_name' => $sku_name, 'SKU' => $sku_id])->one();
//        $last_pro_id = 0;
//        if (empty($checkProductModel)) {
//            //Create Model for Each new Product
//            $productModel = new Products (Yii::$app->user->identity->id);
//            $productModel->elliot_user_id = Yii::$app->user->identity->id;
//            $productModel->product_name = $sku_name;
//            $productModel->SKU = $sku_id;
//            $productModel->product_url = $this->server_url . $product_data['_links']['self']['href'];
//            $productModel->UPC = "";
//            $productModel->EAN = "";
//            $productModel->Jan = "";
//            $productModel->ISBN = "";
//            $productModel->MPN = "";
//            $productModel->description = $product_data['description2'];
//            $productModel->adult = "no";
//            $productModel->age_group = NULL;
//            if($reorderQuantity>0)
//                $productModel->availability = 'In Stock';
//            else
//                $productModel->availability = 'Out of Stock';
//            $productModel->brand = "";
//            $productModel->condition = "New";
//            $productModel->weight = 0;
//            $productModel->stock_quantity = $reorderQuantity;
//            if($reorderQuantity>0)
//                $productModel->stock_level = 'In Stock';
//            else
//                $productModel->stock_level = 'Out of Stock';
//            $productModel->stock_status = "Visible";
//            $productModel->price = isset($product_data['price']) ? $product_data['price'] : 0;
//            $productModel->sales_price = isset($product_data['cost']) ? $product_data['cost'] : 0;
//            $productModel->created_at = $product_data['readOnly']['creationDate'];
//            $productModel->updated_at = $product_data['readOnly']['lastModifiedDate'];
//            $productModel->product_status = $product_data['readOnly']['deactivated'] == false ? "active" : "in_active";
//            if ($productModel->save(false)):
//                $last_pro_id = $productModel->id;
//            endif;
//
//        }else{
//            $last_pro_id = $checkProductModel->id;
//        }
//        $prefix_product_id = "3PL" . $product_data['itemId'];
//        if($last_pro_id !=0){
//            $check_abbrivation = ProductAbbrivation::find()->where(['channel_abb_id' => $prefix_product_id])->one();
//            if (empty($check_abbrivation)) {
//                /* Save Product Abbrivation Id */
//                $product_abberivation = new ProductAbbrivation();
//                $product_abberivation->channel_abb_id = $prefix_product_id;
//                $product_abberivation->product_id = $last_pro_id;
//                $product_abberivation->channel_accquired = '3PL';
//                $product_abberivation->created_at = date('Y-m-d H:i:s', time());
//                $product_abberivation->save(false);
//            }
//
//            $get_tpl_id = FulfillmentList::find()->select('id')->where(['fulfillment_name' => '3PL Central'])->one();
//            $tpl_channel_id = $get_tpl_id->id;
//
//            $check_productchannel = ProductChannel::find()->where(['product_id' => $last_pro_id, 'channel_id' => $tpl_channel_id])->one();
//            if (empty($check_productchannel)) {
//                $productChannelModel = new ProductChannel();
//                $productChannelModel->elliot_user_id = Yii::$app->user->identity->id;
//                $productChannelModel->fulfillment_id = $tpl_channel_id;
//                $productChannelModel->product_id = $last_pro_id;
//                $productChannelModel->created_at = $product_data['readOnly']['creationDate'];
//                $productChannelModel->updated_at = date('Y-m-d h:i:s', time());
//                $productChannelModel->save(false);
//            }
//        }
//    }
//
//    public function importOrders( $url= "https://secure-wms.com/orders/digests?pgsiz=1000"){
//        $result = $this->CallAPI("GET", $url);
//        $orders = [];
//        if($result != ""){
//            $response_data = json_decode($result, true);
//            if(isset($response_data['_embedded']['item'])){
//                $orders = $response_data['_embedded']['item'];
//                foreach($orders as $_order){
//                    $this->insertOrder($_order);
//                }
//            }
//            if(isset($response_data['_links']['next']['href'])){
//                $this->importOrders($this->server_url . $response_data['_links']['next']['href']);
//            }
//        }
//    }
//
//    private function insertOrder($order){
//        $tpl_customer_id = $order['customerIdentifier']['id'];
//        $customerCheckModel = CustomerUser::find()->Where(['channel_abb_id' => '3PL'. $tpl_customer_id])->one();
//        if(empty($customerCheckModel))
//        {
//            if($tpl_customer_id == '')
//            {
//                $tpl_customer_id = '0000';
//            }
//            $customerCheckAnotherModel = CustomerUser::find()->Where(['channel_abb_id' => '3PL'.$tpl_customer_id])->one();
//            if(empty($customerCheckAnotherModel))
//            {
//                $Customers_create_model = new CustomerUser();
//                $Customers_create_model->channel_abb_id = '3PL'.$tpl_customer_id;
//                $Customers_create_model->first_name = $order['customerIdentifier']['name'];
//                $Customers_create_model->last_name = "";
//                $Customers_create_model->email = "";
//                $Customers_create_model->channel_acquired = '3PL Central';
//                $Customers_create_model->date_acquired = '';
//                $Customers_create_model->created_at = date('Y-m-d h:i:s', time());
//                $Customers_create_model->elliot_user_id = Yii::$app->user->identity->id;
//                $Customers_create_model->save(false);
//                $elliot_customer_id = $Customers_create_model->customer_ID;
//            }
//            else
//            {
//                $elliot_customer_id = $customerCheckAnotherModel->customer_ID;
//            }
//        }
//        else
//        {
//            $elliot_customer_id = $customerCheckModel->customer_ID;
//        }
//        $check_order = Orders::find()->Where(['channel_abb_id' => 'TM'.$order_id])->one();
//        if(empty($check_order))
//        {
//            $order_model = new Orders();
//            $order_model->elliot_user_id = Yii::$app->user->identity->id;
//            $order_model->channel_abb_id = '3PL' . $order['orderId'];
//            $order_model->customer_id = $elliot_customer_id;
//            $order_model->order_status = $order['billingCode'];
//            $order_model->product_qauntity = $order['totPackages'];
//            $order_model->shipping_address = $order['shipTo']['address1'] . ' ' . $order['shipTo']['address2'] . ' ' . $order['shipTo']['city'] . ' ' . $order['shipTo']['state'] . ' ' . $order['shipTo']['zip'] . ' ' . $order['shipTo']['country'];
//            $order_model->ship_street_1 = $order['shipTo']['address1'];
//            $order_model->ship_street_2 = $order['shipTo']['address2'];
//            $order_model->ship_city = $order['shipTo']['city'];
//            $order_model->ship_state = $order['shipTo']['state'];
//            $order_model->ship_zip = $order['shipTo']['zip'];
//            $order_model->ship_country = $order['shipTo']['country'];
//            $order_model->billing_address = $order['shipTo']['address1'] . ' ' . $order['shipTo']['address2'] . ' ' . $order['shipTo']['city'] . ' ' . $order['shipTo']['state'] . ' ' . $order['shipTo']['zip'] . ' ' . $order['shipTo']['country'];
//            $order_model->bill_street_1 = $order['shipTo']['address1'];
//            $order_model->bill_street_2 = $order['shipTo']['address2'];
//            $order_model->bill_city = $order['shipTo']['city'];
//            $order_model->bill_state = $order['shipTo']['state'];
//            $order_model->bill_zip = $order['shipTo']['zip'];
//            $order_model->bill_country = $order['shipTo']['country'];
//            $order_model->base_shipping_cost = $shipping_price;
//            $order_model->shipping_cost_tax = $tax_price;
//            $order_model->payment_method = $payment_method;
//            $order_model->payment_status = $order_status_2;
//            $order_model->refunded_amount = '';
//            $order_model->discount_amount = $discount_price;
//            $order_model->total_amount = $order_total;
//            $order_model->order_date = $order_created_at;
//            $order_model->updated_at = $order_updated_at;
//            //$order_model->created_at = date('Y-m-d h:i:s', time());
//            $order_model->created_at = $order_created_at;
//            $order_model->save(false);
//        }
//    }
//
//    private function insertCustomers(){
//        foreach ($this->customers as $_customer){
//            $channel_name = '3PL Central';
//            $customer_id = $_customer['readOnly']['customerId'];
//            $Prefix_customer_abb_id = '3PL' . $customer_id;
//            $customer_email = $_customer['companyInfo']['emailAddress'];
//            $customer_f_name = $_customer['companyInfo']['companyName'];
//            $customer_create_date = $_customer['readOnly']['creationDate'];
//            $add_1 = $add_2 = $city = $state = $country = $zip = $phone = '';
//            $add_1 = $_customer['companyInfo']['address1'];
//            $add_2 = $_customer['companyInfo']['address2'];
//            $city = $_customer['companyInfo']['city'];
//            $state = $_customer['companyInfo']['state'];
//            $country = $_customer['companyInfo']['country'];
//            $zip = $_customer['companyInfo']['zip'];
//            $phone = $_customer['companyInfo']['phoneNumber'];
//            $checkCustomerModel = CustomerUser::find()->where(['channel_abb_id' => $Prefix_customer_abb_id])->one();
//            if (empty($checkCustomerModel)){
//                $Customers_model = new CustomerUser(Yii::$app->user->identity->id);
//                $Customers_model->channel_abb_id = $Prefix_customer_abb_id;
//                $Customers_model->first_name = $customer_f_name;
//                $Customers_model->last_name = "";
//                $Customers_model->email = $customer_email;
//                $Customers_model->channel_acquired = $channel_name;
//                $Customers_model->date_acquired = date('Y-m-d h:i:s', strtotime($customer_create_date));
//                $Customers_model->street_1 = $add_1;
//                $Customers_model->street_2 = $add_2;
//                $Customers_model->city = $city;
//                $Customers_model->country = $country;
//                $Customers_model->state = $state;
//                $Customers_model->zip = $zip;
//                $Customers_model->phone_number = $phone;
//                $Customers_model->ship_street_1 = $add_1;
//                $Customers_model->ship_street_2 = $add_2;
//                $Customers_model->ship_city = $city;
//                $Customers_model->ship_state = $state;
//                $Customers_model->ship_zip = $zip;
//                $Customers_model->ship_country = $country;
//                $Customers_model->created_at = date('Y-m-d h:i:s', strtotime($customer_create_date));
//                //Save Elliot User id
//                $Customers_model->elliot_user_id = Yii::$app->user->identity->id;
//                $Customers_model->save(false);
//            }
//        }
//    }
}

?>