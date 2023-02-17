<?php
namespace frontend\components;

use common\models\Category;
use common\models\Country;
use common\models\Customer;
use common\models\Order;
use common\models\Product;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use yii\base\Component;
use frontend\components\VtexClient;
use common\components\order\OrderStatus;


class VtexComponent extends Component
{
    const timeOutLimit = 1;
    const errorTimeOutLimit = 20;
    const storeName = "vtex";

    public static function vtexCurl($url, $appkey, $apptoken) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => FALSE,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "x-vtex-api-appkey: " . $appkey,
                "x-vtex-api-apptoken: " . $apptoken
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #: " . $err;
        } else {
            return json_decode($response, true);
        }
    }

    public static function createVtexClient($user_connection_id) {

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientInfo = $store_vtex->connection_info;

        $vtexClient = new VtexClient($vtexClientInfo);

        return $vtexClient;


    }

    public static function importShop($user_connection_id, $shopDetails){

        $user_Vtex_connection = UserConnectionDetails::findOne(
            [
                'user_connection_id' => $user_connection_id
            ]
        );


        if (empty($user_Vtex_connection)) {
            $user_Vtex_connection = new UserConnectionDetails();
            $user_Vtex_connection->user_connection_id = $user_connection_id;
        }

        $user_Vtex_connection->store_name = $shopDetails['account'];
        $user_Vtex_connection->store_url = "http://" . $shopDetails['account'] . "." . $shopDetails['account_env'] . ".com.br/";
        $user_Vtex_connection->country = $shopDetails['country_name'];
        $user_Vtex_connection->country_code = $shopDetails['country_code'];
        $user_Vtex_connection->currency = $shopDetails['currency_code'];
        $user_Vtex_connection->currency_symbol = $shopDetails['currency_symbol'];
        $user_Vtex_connection->others = '';

        $userConnectionSettings = $user_Vtex_connection->settings;

        if(empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
            $userConnectionSettings['currency'] = $user_Vtex_connection->currency;
        }

        $user_Vtex_connection->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);


        $user_Vtex_connection->save(false);


    }

    /**
     * Insert category from Vtex store
     * @param array $category_list
     * @param int $user_id
     */
    public static function vtexCategoryImporting($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientConfig = $store_vtex->connection_info;
        $user_id = $store_vtex->user_id;

        $vtexClient = new VtexClient($vtexClientConfig);

        $category_response = $vtexClient->call('get', 'catalog_system/pub/category/tree/1000');

        foreach($category_response as $cat){

            $cat_id = $cat['id'];
            $cat_name = $cat['name'];

            $category_data = [
                'name' => $cat_name, // Give category name
                'description' => '', // Give category body html
                'parent_id' => 0,
                'user_id' => $user_id, // Give Elliot user id,
                'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                'connection_category_id' => $cat_id, // Give category id of Store/channels
                'connection_parent_id' => '0', // Give Category parent id of Elliot if null then give 0
            ];

            Category::categoryImportingCommon($category_data);

            $child = $cat['children'];
            if(count($child)>0){
                self::vtexChildCategoryImporting($child, $user_id, $user_connection_id, $cat_id);
            }

        }
    }

    public static function vtexChildCategoryImporting($category_list, $user_id, $user_connection_id, $cat_parent_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        foreach ($category_list as $cat){

            $cat_id = $cat['id'];
            $cat_name = $cat['name'];

            $category_data = [
                'name' => $cat_name, // Give category name
                'description' => '', // Give category body html
                'parent_id' => 0,
                'user_id' => $user_id, // Give Elliot user id,
                'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                'connection_category_id' => $cat_id, // Give category id of Store/channels
                'connection_parent_id' => $cat_parent_id, // Give Category parent id of Elliot if null then give 0
            ];

            Category::categoryImportingCommon($category_data);

            $child = $cat['children'];
            if(count($child)>0){
                self::vtexChildCategoryImporting($child, $user_id, $user_connection_id, $cat_id);
            }

        }


    }
    /**
     * Vtex product importing
     * @param int $user_connection_id
     * @param float $conversion_rate
     */
    public static function vtexProductImporting($user_connection_id, $conversion_rate)
    {
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientConfig = $store_vtex->connection_info;

        $vtexClient = new VtexClient($vtexClientConfig);

        $category_list = Category::findAll(['user_connection_id' => $user_connection_id]);

        $count = 1;
        $product_ids = array();
        foreach($category_list as $cat){
            $cat_id = $cat->connection_category_id;
            $_from = 1;
            $_to = 50;
            $product_path = "catalog_system/pvt/products/GetProductAndSkuIds?categoryId=".$cat_id."&_from=".$_from."&_to=".$_to;

            $product_data = $vtexClient->call('get', $product_path);

            if ( !empty($product_data['data']) ) {
                foreach($product_data['data'] as $key => $value){
                    if(!in_array($key, $product_ids)){
                        $product_ids[] = $key;
                    }
                }

                $total_product_count = $product_data['range']['total'];

                if ( $total_product_count > 50 ) {
                    $total_loop = ceil($total_product_count/50);
                    for($page=2;$page<=$total_loop;$page++){
                        $_from = $_from+50;
                        $_to = $_to+50;

                        $product_path = "catalog_system/pvt/products/GetProductAndSkuIds?categoryId=".$cat_id."&_from=".$_from."&_to=".$_to;

                        $product_data = $vtexClient->call('get', $product_path);
                        if ( !empty($product_data['data']) ) {
                            foreach($product_data['data'] as $key => $value){
                                if(!in_array($key, $product_ids)){
                                    $product_ids[] = $key;
                                }
                            }

                        }
                    }

                }
            }


        }
        $brand_list = self::getVtexBrandList($user_connection_id);

        self::productInitialInsert($user_connection_id, $product_ids, $brand_list, $conversion_rate);
    }

    public static function productInitialInsert($user_connection_id, $product_ids, $brand_list, $conversion_rate){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientConfig = $store_vtex->connection_info;
        $user_id = $store_vtex->user_id;

        $store_connection_details = $store_vtex->userConnectionDetails;

        $store_country_code = $store_connection_details->country_code;
        $store_currency_code = $store_connection_details->currency;

        $vtexClient = new VtexClient($vtexClientConfig);
        $product_base_url = "https://".$vtexClientConfig['account'].".com.br/";

        sort($product_ids);

        $product_image_data = [];
        foreach($product_ids as $pid){
            $product_path = 'catalog_system/pvt/products/ProductGet/'.$pid;
            $product_variation_path = 'catalog_system/pub/products/variations/'.$pid;
            $product_data = $vtexClient->call('get', $product_path);
            $product_variation = $vtexClient->call('get', $product_variation_path);
            //echo "<pre>"; print_r($product_data); print_r($product_variation);
            $product_id = $product_data['Id'];
            $name = $product_data['Name'];
            $url_link_id = $product_data['LinkId'];
            $category_id = $product_data['CategoryId'];
            $brand_id = $product_data['BrandId'];
            $description = $product_data['Description'];
            $created_at = date("Y-m-d H:i:s", isset($product_data['ReleaseDate'])?strtotime($product_data['ReleaseDate']):time());
            $product_status = $product_data['IsVisible'];
            $product_active_status = $product_data['IsActive'];
            $product_type = $product_data['Title'];
            $brand_name = '';
            foreach($brand_list as $_brand){
                if($_brand['id']==$brand_id){
                    $brand_name = $_brand['name'];
                }
            }
            $product_quantity = 0;
            $product_stock = false;

            $variants_data = [];
            if(!is_scalar($product_variation)){

                if(isset($product_variation['skus'])){
                    $product_sku_data = $product_variation['skus'];
                    foreach($product_sku_data as $_product_sku){
                        $sku_id = $_product_sku['sku'];
                        $sku_name = $_product_sku['skuname'];
                        $product_price = $_product_sku['bestPrice']/100;
                        $product_image_url = $_product_sku['image'];
                        if ( $_product_sku['available'] ){
                            //$product_stock = ($_product_sku['available']==1) ? $_product_sku['available'] : 0;
                            $product_stock = true;
                        }

                        $variants_qty = $_product_sku['availablequantity'];
                        $variants_weight = $_product_sku['measures']['cubicweight'];

                        $product_quantity += intval($variants_qty);

                        if($product_image_url!=''){
                            $product_image_data[] = array(
                                'connection_image_id' => 0,
                                'image_url' => $product_image_url,
                                'label' => '',
                                'position' => 1,
                                'base_img' => $product_image_url,
                            );

                        }

                        $oneVariantData = [
                            'connection_variation_id' => $sku_id,
                            'sku_key' => 'skuname',
                            'sku_value' => $sku_name,
                            'inventory_key' => 'availablequantity',
                            'inventory_value' => $variants_qty,
                            'price_key' => 'bestPrice',
                            'price_value' => $product_price,
                            'weight_key' => '["measures"]["cubicweight"]',
                            'weight_value' => $variants_weight,
                            'upc' => '',
                            'options' => [],
                        ];
                        $variants_data[] = $oneVariantData;


                    }
                }
            }

            $product_data = [
                'user_id' => $user_id, // Elliot user id,
                'name' => $name, // Product name,
                'sku' => isset($variants_data[0]['sku_value'])?$variants_data[0]['sku_value']:'', // Product SKU,
                'url'=>$product_base_url.$url_link_id."/p",   // Product url if null give blank value
                'upc' => '',
                'ean' => '',
                'jan' => '', // Product jan if any,
                'isbn' => '', // Product isban if any,
                'mpn' => '', // Product mpn if any,
                'description' => $description, // Product Description,
                'adult' => null,
                'age_group' => null,
                'brand' => $brand_name,
                'condition' => null,
                'gender' => null,
                'weight' => isset($variants_data[0]['weight_value'])?$variants_data[0]['weight_value']:null, // Product weight if null give blank value,
                'package_length' => null,
                'package_height' => null,
                'package_width' => null,
                'package_box' => null,
                'stock_quantity' => $product_quantity, //Product quantity,
                'allocate_inventory' => null,
                'currency' => $store_currency_code,
                'country_code' => $store_country_code,
                'stock_level' => ($product_stock)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                'stock_status' => ($product_status)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                'price' => isset($variants_data[0]['price_value'])?$variants_data[0]['price_value']:null, // Porduct price,
                'sales_price' => isset($variants_data[0]['price_value'])?$variants_data[0]['price_value']:null, // Product sale price if null give Product price value,
                'schedule_sales_date' => null,
                'status' => ($product_active_status)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
                'published' => ($product_active_status)?Product::PRODUCT_PUBLISHED_YES:Product::PRODUCT_PUBLISHED_NO,
                'permanent_hidden' => Product::STATUS_NO,
                'user_connection_id' => $user_connection_id,
                'connection_product_id' => $product_id, // Stores/Channel product ID,
                'created_at' => $created_at, // Product created at date format date('Y-m-d H:i:s'),
                'updated_at' => $created_at, // Product updated at date format date('Y-m-d H:i:s'),
                'type' => $product_type, // Product Type
                'images'=>$product_image_data,  // Product images data
                'variations' => $variants_data,
                'options_set' => array(),
                'websites' => array(), //This is for only magento give only and blank array
                'conversion_rate' => $conversion_rate,
                'categories'=>array($category_id), // Product categroy array. If null give a blank array
            ];

            if ( !empty($product_data) ) {
                Product::productImportingCommon($product_data);
            }

        }
    }

    public static function getVtexBrandList($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientConfig = $store_vtex->connection_info;

        $vtexClient = new VtexClient($vtexClientConfig);

        $brand_path = 'catalog_system/pvt/brand/list';

        $brand_list = $vtexClient->call('get', $brand_path);
        return $brand_list;
    }
    /**
     * Vtex Order Importing
     * @param integer $user_connection_id
     * @param float $conversion_rate
     */
    public static function vtexOrderImport($user_connection_id, $conversion_rate) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientConfig = $store_vtex->connection_info;
        $user_id = $store_vtex->user_id;
        $store_connection_details = $store_vtex->userConnectionDetails;

        $store_currency_code = $store_connection_details->currency;
        $store_country_code = $store_connection_details->country_code;


        $vtexClient = new VtexClient($vtexClientConfig);


        $order_list_path = "oms/pvt/orders?per_page=100";
        $order_collection = $vtexClient->call('get', $order_list_path);
        $total_pages = $order_collection['paging']['pages'];
        for($i=1; $i<=$total_pages;$i++){
            $order_list_path = "oms/pvt/orders?per_page=100&page=".$i;

            $order_collection = $vtexClient->call('get', $order_list_path);
            //echo count($order_collection_list['list']);
            $order_collection_list = $order_collection['list'];
            foreach($order_collection_list as $order_data){
                $order_id = $order_data['orderId'];
                $order_data_path = "oms/pvt/orders/".$order_id;
                $order_collection = $vtexClient->call('get', $order_data_path);
                //echo "<pre>"; print_r($order_collection); die('End herere!!');
                $order_id = $order_collection['orderId'];
                $origin = $order_collection['origin'];
                $salesChannel = $order_collection['salesChannel'];
                $order_status = $order_collection['status'];
                $order_total = self::vtexPriceFormat($order_collection['value']);
                $order_created_at = date("Y-m-d h:i:s", strtotime($order_collection['creationDate']));
                $order_updated_at = date("Y-m-d h:i:s", strtotime($order_collection['lastChange']));
                $group_price = $order_collection['totals'];

                switch (strtolower($order_status)) {
                    case "handling":
                        $order_status_2 = OrderStatus::IN_TRANSIT;
                        break;
                    case "ready-for-handling":
                        $order_status_2 = OrderStatus::IN_TRANSIT;
                        break;
                    case "invoiced":
                        $order_status_2 = OrderStatus::IN_TRANSIT;
                        break;
                    case "payment-pending":
                        $order_status_2 = OrderStatus::PENDING;
                        break;
                    case "order-completed":
                        $order_status_2 = OrderStatus::COMPLETED;
                        break;
                    case "payment-approved":
                        $order_status_2 = OrderStatus::IN_TRANSIT;
                        break;
                    case "cancel":
                        $order_status_2 = OrderStatus::CANCEL;
                        break;
                    case "cancellation-requested":
                        $order_status_2 = OrderStatus::CANCEL;
                        break;
                    default:
                        $order_status_2 = OrderStatus::PENDING;
                }

                $discount_price = '';
                $shipping_price = '';
                $tax_price = '';
                foreach($group_price as $_price)
                {
                    $price_type = $_price['id'];
                    if($price_type=='Discounts')
                    {
                        $discount_price = self::vtexPriceFormat($_price['value']);
                    }
                    if($price_type=='Shipping')
                    {
                        $shipping_price = self::vtexPriceFormat($_price['value']);
                    }
                    if($price_type=='Tax')
                    {
                        $tax_price = self::vtexPriceFormat($_price['value']);
                    }
                }

                $payment_method = $order_collection['paymentData']['transactions'][0]['payments'][0]['paymentSystemName'];

                $customer_data = $order_collection['clientProfileData'];
                $email = $customer_data['email'];
                $firstName = $customer_data['firstName'];
                $lastName = $customer_data['lastName'];
                $phone = $customer_data['phone'];
                $vtex_customer_profile_id = $customer_data['userProfileId'];

                $shipping_data = $order_collection['shippingData'];
                $receiver_name = $shipping_data['address']['receiverName'];
                $zip_code = $shipping_data['address']['postalCode'];
                $city = $shipping_data['address']['city'];
                $state = $shipping_data['address']['state'];
                $country = $shipping_data['address']['country'];
                $street = $shipping_data['address']['street'];

                $total_quantity_ordered = 0;

                $order_items = $order_collection['items'];
                $order_product_data = array();
                foreach($order_items as $_items){
                    $product_id = $_items['productId'];
                    $quantity_ordered = $_items['quantity'];
                    $total_quantity_ordered += $quantity_ordered;
                    $product_name = $_items['name'];
                    $product_price = self::vtexPriceFormat($_items['price']);

                    $product_detail_path = 'catalog_system/pub/products/variations/'.$product_id;
                    $product_detail = $vtexClient->call('get', $product_detail_path);

                    $product_sku = '';
                    $product_weight = '';
                    if(!is_scalar($product_detail)){
                        $product_sku = $product_detail['skus'][0]['sku'];
                        $product_weight = isset($product_detail['skus'][0]['measures']['cubicweight'])?$product_detail['skus'][0]['measures']['cubicweight']:'';
                    }

                    $order_product_data[] = array(
                        'user_id' => $user_id,
                        'connection_product_id' => $product_id,
                        'name' => $product_name,
                        'order_product_sku' => $product_sku,
                        'price' => $product_price,
                        'qty' => $quantity_ordered,
                        'weight' => $product_weight,
                    );

                }

                if($vtex_customer_profile_id == '' || empty($vtex_customer_profile_id)){
                    $vtex_customer_profile_id = 0;
                }

                $order_customer_data = array(
                    'connection_customerId' => $vtex_customer_profile_id,
                    'user_id' => $user_id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                    'user_connection_id' => $user_connection_id,
                    'customer_created' => $order_created_at,
                    'updated_at' => $order_created_at,
                    'addresses' => [
                        [
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'company' => '',
                            'country' => $country,
                            'country_iso' => $country,
                            'street_1' => $street,
                            'street_2' => '',
                            'state' => $state,
                            'city' => $city,
                            'zip' => $zip_code,
                            'phone' => $phone,
                            'address_type' => 'Default',
                        ],
                    ]
                );

                $order_data = array(
                    'user_id' => $user_id,
                    'user_connection_id' => $user_connection_id,
                    'connection_order_id' => $order_id,
                    'status' => $order_status_2,
                    'product_quantity' => $total_quantity_ordered,
                    'ship_fname' => $receiver_name,
                    'ship_lname' => '',
                    'ship_phone' => '',
                    'ship_company' => '',
                    'ship_street_1' => $street,
                    'ship_street_2' => '',
                    'ship_city' => $city,
                    'ship_state' => $state,
                    'ship_zip' => $zip_code,
                    'ship_country' => $country,
                    'ship_country_iso' => $country,
                    'bill_fname' => $firstName,
                    'bill_lname' => $lastName,
                    'bill_phone' => $phone,
                    'bill_company' => '',
                    'bill_street_1' => $street,
                    'bill_street_2' => '',
                    'bill_city' => $city,
                    'bill_state' => $state,
                    'bill_country' => $country,
                    'bill_zip' => $zip_code,
                    'bill_country_iso' => $country,
                    'fee' => [
                        'base_shippping_cost' => $shipping_price,
                        'shipping_cost_tax' => $tax_price,
                        'discount_amount' => $discount_price,
                        'payment_method' => $payment_method,
                    ],
                    'total_amount' => $order_total,
                    'order_date' => $order_created_at,
                    'created_at' => $order_created_at,
                    'updated_at' => $order_updated_at,
                    'customer_email' => $email,
                    'currency_code' => $store_currency_code,
                    'country_code' => $store_country_code,
                    'conversion_rate' => $conversion_rate,
                    'order_products' => $order_product_data,
                    'order_customer' => $order_customer_data,
                );
                Order::orderImportingCommon($order_data);


            }
        }
    }

    /**
     * Vtex Customer Importing
     * @param integer $user_connection_id
     */
    public static function vtexCustomerImport($user_connection_id) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientConfig = $store_vtex->connection_info;
        $user_id = $store_vtex->user_id;


        $api_base = 'http://api.vtex.com/' . $vtexClientConfig['account'] . '/';
        $vtexClient = new VtexClient($vtexClientConfig, $api_base);

        $customer_data_path = 'dataentities/CL/scroll';
        $customer_data = $vtexClient->call('get', $customer_data_path);
        if(count($customer_data)== 4 && array_key_exists('ExceptionMessage', $customer_data)){
            return false;
        } else {
            if(count($customer_data)==1 && array_key_exists('Message', $customer_data)){
                return false;
            }
            foreach($customer_data as $customer_list){
                //$email = $customer_list['email'];
                $id = $customer_list['id'];
                $customer_id_path = 'dataentities/CL/documents/'.$id.'?_fields=_all';
                $_customer = $vtexClient->call('get', $customer_id_path);
                $homePhone = $_customer['homePhone'];
                $phone = $_customer['phone'];
                $first_name = $_customer['firstName'];
                $last_name = $_customer['lastName'];
                $email = $_customer['email'];
                $dob = $_customer['birthDate'];
                $gender = $_customer['gender'];
                $vtext_customer_profile_id = $_customer['id'];
                $vtext_customer_id = $_customer['userId'];
                $customer_created_at = date("Y-m-d h:i:s", isset($_customer['createdIn'])?strtotime($_customer['createdIn']):time());
                $customer_updated_at = date("Y-m-d h:i:s", isset($_customer['updatedIn'])?strtotime($_customer['updatedIn']):time());
                if($vtext_customer_id == ''){
                    $vtext_customer_id = '0';
                }

                $customer_data = array(
                    'connection_customerId' => $vtext_customer_id,
                    'user_id' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $homePhone,
                    'dob' => $dob,
                    'gender' => $gender,
                    'user_connection_id' => $user_connection_id,
                    'customer_created' => $customer_created_at,
                    'updated_at' => $customer_updated_at,
                    'addresses' => [
                        [
                            'first_name' => $first_name,
                            'last_name' => $last_name,
                            'company' => '',
                            'country' => '',
                            'country_iso' => '',
                            'street_1' => '',
                            'street_2' => '',
                            'state' => '',
                            'city' => '',
                            'zip' => '',
                            'phone' => $phone,
                            'address_type' => 'Default',
                        ],
                    ]
                );
                Customer::customerImportingCommon($customer_data);


            }
        }

        return true;
    }
    /**
     * vtex Price format
     * @param integer $price
     * @return float price (25.21)
     */
    public static function vtexPriceFormat($price) {
        $final_price = 0;
        if ( $price > 0 ) {
            $price_format = $price/100;
            $final_price = number_format($price_format, 2, '.', '');
        }
        return $final_price;
    }
    /**
     * Get Vtex warehoses
     * @param integer $user_connection_id
     * @return array Fulfillment
     */
    public static function getVtexFulfillments($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientConfig = $store_vtex->connection_info;

        $vtexClient = new VtexClient($vtexClientConfig);


        $api_path = 'logistics/pvt/configuration/warehouses?an='.$vtexClientConfig['account'];
        $warehouses_result = $vtexClient->call('get', $api_path);
        $warehouses_ids = array();
        foreach($warehouses_result as $_warehouses){
            $warehouses_ids[] = $_warehouses['id'];
        }
        return $warehouses_ids;
    }
    /**
     * Change Product Price To oVtex
     */
    public static function vtexChangeProductPrice($user_connection_id, $sku, $price){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientConfig = $store_vtex->connection_info;

        $vtexClient = new VtexClient($vtexClientConfig);



        $url = 'http://rnb.'.$vtexClientConfig['account'].'.com.br/api/pricing/pvt/price-sheet/?an='.$vtexClientConfig['account'];

        $price_data = array(
            '0'=> array(
                'itemId'=>$sku,
                'salesChannel'=>'1',
                'sellerId'=>null,
                'price'=>$price,
                'listPrice'=>'1',
                'validFrom'=>'',
                'validTo'=>'',
            ),
        );

        $data = json_encode($price_data);

        $result = $vtexClient->call('post', $url, $price_data);
        //echo "<pre>"; print_r($result); echo "</pre>";
    }

    public static function vtexChangeProductQty($user_connection_id, $sku, $qty){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_vtex = UserConnection::findOne(['id' => $user_connection_id]);

        $vtexClientConfig = $store_vtex->connection_info;

        $vtexClient = new VtexClient($vtexClientConfig);


        $warehouses = self::getVtexFulfillments($user_connection_id);


        foreach($warehouses as $_warehouses){
            $url = 'http://logistics.'.$vtexClientConfig['account'].'.com.br/api/logistics/pvt/inventory/skus/'.$sku.'/warehouses/'.$_warehouses.'?an='.$vtexClientConfig['account'];
            $method = 'PUT';
            $data = array(
                'unlimitedQuantity'=>FALSE,
                'dateUtcOnBalanceSystem'=>null,
                'quantity'=>$qty,
            );
            $post_data = json_encode($data);
            //$result = self::vtexPostCurl($url, $method, $post_data, $vtex_app_key, $vtex_app_token);
            $result = $vtexClient->call('put', $url, $data);
        }
    }

}