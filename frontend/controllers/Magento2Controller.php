<?php

namespace frontend\controllers;

use common\models\Connection;
use common\models\Country;
use common\models\Customer;
use common\models\CustomerAddress;
use common\models\Order;
use common\models\Product;
use common\models\UserConnection;
use frontend\components\Magento2Component;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;    
use common\models\User;
use common\models\StoresConnection;
use common\models\Stores;
use common\models\Categories;
use common\models\Variations;
use common\models\VariationsItemList;
use common\models\Products;
use common\models\OrdersProducts;
use common\models\OrderChannel;
use common\models\CustomerUser;
use common\models\Notification;
use common\models\ProductCategories;
use common\models\ProductVariation;
use common\models\ProductImages;
use common\models\ProductChannel;
use common\models\Orders;
use common\models\Channels;
use common\models\MagentoStores;
use common\models\CustomFunction;
use common\models\ProductAbbrivation;
use common\models\CategoryAbbrivation;
use common\models\Countries;
use common\models\StoreDetails;
use common\models\CustomerAbbrivation;
use common\models\Channelsetting;
use common\models\CurrencyConversion;

class Magento2Controller extends \yii\web\Controller
{
    /**
    * @inheritdoc
    */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index','login', 'auth-magento2', 'magento2-importing', 'magento2-product-create', 'magento2-order-create', 'magento2-customer-create', 'magento2-customer-address-update', 'magento2x-configuration', 'test'],
                'rules' => [
                        [
                        /* action hit without log-in */
                        'actions' => ['login', 'magento2-importing', 'magento2-product-create', 'magento2-order-create', 'magento2-customer-create', 'magento2-customer-address-update', 'test'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                        [
                        /* action hit only with log-in */
                        'actions' => ['index', 'auth-magento2', 'magento2-importing', 'magento2-product-create', 'magento2-order-create', 'magento2-customer-create', 'magento2-customer-address-update', 'magento2x-configuration', 'test'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    /**
     * Magento 2 configuration action
     */
    
    public function actionMagento2xConfiguration() {
        return $this->render('magento2xconfiguration');
    }


    /**
     * Magento 2 Customer hooking
     */
    public function actionMagento2CustomerCreate() {
        $url_data = $_GET['data'];
        $url_data = urldecode($url_data);
        $customer_data = json_decode($url_data, true);
        $shop_url = $customer_data['magento_url'];
        $shop_url = explode("://", $shop_url)[1];
        if(substr($shop_url, -1)){
            $shop_url = substr($shop_url, 0, -1);
        }
        $connection = Connection::find()->select('id')->where(['name' => 'Magento2'])->one();
        $connectionId = $connection->id;
        $user_connections = UserConnection::findAll(["connection_id"=> $connectionId]);
        foreach ($user_connections as $_userConnection){
            $importUser = $_userConnection->user;
            $user_id = $importUser->id;
            $magentoClientInfo = $_userConnection->connection_info;
            $magento_shop = $magentoClientInfo['magento_shop'];
            $magento_shop = explode("://", $magento_shop)[1];
            if(substr($magento_shop, -1)){
                $magento_shop = substr($magento_shop, 0, -1);
            }
            if($shop_url == $magento_shop){
                $customer_data = array(
                    'connection_customerId' => $customer_data['customer_id'],
                    'user_id' => $user_id,
                    'first_name' => $customer_data['firstname'],
                    'last_name' => $customer_data['lastname'],
                    'email' => $customer_data['email'],
                    'dob' => '',
                    'gender' => '',
                    'phone' => '',
                    'user_connection_id' => $_userConnection->id,
                    'customer_created' => date('Y-m-d h:i:s', strtotime($customer_data['created_at'])),
                    'updated_at' => date('Y-m-d h:i:s', strtotime($customer_data['updated_at'])),
                    'addresses' => [
                        [
                            'first_name' => '',
                            'last_name' => '',
                            'company' => '',
                            'country' => '',
                            'country_iso' => '',
                            'street_1' => '',
                            'street_2' => '',
                            'state' => '',
                            'city' => '',
                            'zip' => '',
                            'phone' => '',
                            'address_type' => 'Default',
                        ],
                    ]
                );
                Customer::customerImportingCommon($customer_data);
            }
        }
    }
    /**
     * Magento 2 Customer address hooking
     */
    public function actionMagento2CustomerAddressUpdate() {
        $url_data = $_GET['data'];
        $url_data = urldecode($url_data);
        $customer_data = json_decode($url_data, true);
        $shop_url = $customer_data['magento_url'];
        $shop_url = explode("://", $shop_url)[1];
        if(substr($shop_url, -1)){
            $shop_url = substr($shop_url, 0, -1);
        }
        $street_add_1 = $customer_data['street'][0];
        $street_add_2 = '';
        if (array_key_exists(1, $customer_data['street'])) {
            $street_add_2 = $customer_data['street'][1];
        }
        $connection = Connection::find()->select('id')->where(['name' => 'Magento2'])->one();
        $connectionId = $connection->id;
        $user_connections = UserConnection::findAll(["connection_id"=> $connectionId]);

        foreach ($user_connections as $_userConnection){
            $magentoClientInfo = $_userConnection->connection_info;
            $magento_shop = $magentoClientInfo['magento_shop'];
            $magento_shop = explode("://", $magento_shop)[1];
            if(substr($magento_shop, -1)){
                $magento_shop = substr($magento_shop, 0, -1);
            }
            if($shop_url == $magento_shop){
                $customer = Customer::findOne(["connection_customerId" => $customer_data['customer_id'], "user_connection_id"=>$_userConnection->id]);
                if(!empty($customer)){
                    $customerAddress = $customer->customerAddress;
                    if(empty($customerAddress))
                        $customerAddress = new CustomerAddress();
                    $customerAddress->first_name = $customer->first_name;
                    $customerAddress->last_name = $customer->last_name;
                    $customerAddress->street_1 = $street_add_1;
                    $customerAddress->street_2 = $street_add_2;
                    $customerAddress->city = $customer_data['city'];
                    $country_code = $customer_data['country_id'];
                    $country_info = Country::countryInfoFromCode($country_code);
                    if (!empty($country_info)) {
                        $country = $country_info->name;
                    }else{
                        $country = "";
                    }
                    $customerAddress->country_iso = $country_code;
                    $customerAddress->country = $country;
                    $customerAddress->state = $customer_data['region'];
                    $customerAddress->zip = $customer_data['postcode'];
                    $customerAddress->phone = $customer_data['telephone'];
                    $customerAddress->save(false);
                }
            }
        }
    }

    public function actionMagento2ProductCreate()
    {
        $url_data = $_GET['data'];
        $url_data = urldecode($url_data);
        $product_info = json_decode($url_data, true);

        $shop_url = $product_info['magento_url'];
        $shop_url = explode("://", $shop_url)[1];
        if(substr($shop_url, -1)){
            $shop_url = substr($shop_url, 0, -1);
        }
        $connection = Connection::find()->select('id')->where(['name' => 'Magento2'])->one();
        $connectionId = $connection->id;
        $user_connections = UserConnection::findAll(["connection_id"=> $connectionId]);

        foreach ($user_connections as $_userConnection) {
            $importUser = $_userConnection->user;
            $user_id = $importUser->id;
            $store_connection_details = $_userConnection->userConnectionDetails;
            $store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;
            $conversion_rate = 0;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, isset($importUser->currency)?$importUser->currency:'USD');
            }

            $magentoClientInfo = $_userConnection->connection_info;
            $magento_shop = $magentoClientInfo['magento_shop'];
            $magento_shop = explode("://", $magento_shop)[1];


            if (substr($magento_shop, -1)) {
                $magento_shop = substr($magento_shop, 0, -1);
            }
            if ($shop_url == $magento_shop) {
                $mage_product_id = $product_info['product_id'];
                $product_name = $product_info['name'];
                $product_sku = $product_info['sku'];
                $product_categories_ids = $product_info['categories'];
                $product_description = $product_info['description'];
                $product_weight = $product_info['weight'];
                $product_status = $product_info['status'];
                $product_created_at = $product_info['product_created_at'];
                $product_updated_at = $product_info['product_updated_at'];
                $product_price = $product_info['price'];
                $qty = $product_info['qty'];
                $stock_status = $product_info['is_in_stock'];
                $websites = $product_info['store_id'];
                $product_url = $product_info['product_url'];

                $product_image = $product_info['images'];
                $product_image_pos = $product_info['position'];
                $i = 0;
                $product_image_data = array();
                if(!empty($product_image)){
                    foreach ($product_image as $_image) {
                        $position = $product_image_pos[$i];
                        $product_image_data[] = array(
                            'connection_image_id' => 0,
                            'image_url' => $_image,
                            'label' => '',
                            'position' => $position,
                            'base_img' => $_image,
                        );
                        $i++;
                    }
                }
                $product_data = [
                    'user_id' => $user_id, // Elliot user id,
                    'name' => $product_description, // Product name,
                    'sku' => $product_sku, // Product SKU,
                    'url' => $product_url, // Product url if null give blank value,
                    'upc' => "", // Product upc if any,
                    'ean' => "",
                    'jan' => "", // Product jan if any,
                    'isbn' => "", // Product isban if any,
                    'mpn' => "", // Product mpn if any,
                    'description' => $product_description, // Product Description,
                    'adult' => null,
                    'age_group' => null,
                    'brand' => null,
                    'condition' => null,
                    'gender' => null,
                    'weight' => $product_weight, // Product weight if null give blank value,
                    'package_length' => null,
                    'package_height' => null,
                    'package_width' => null,
                    'package_box' => null,
                    'stock_quantity' => $qty, //Product quantity,
                    'allocate_inventory' => null,
                    'currency' => $store_currency_code,
                    'country_code' => $store_country_code,
                    'stock_level' => ($stock_status>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                    'stock_status' => ($stock_status>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                    'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                    'price' => $product_price, // Porduct price,
                    'sales_price' => $product_price, // Product sale price if null give Product price value,
                    'schedule_sales_date' => null,
                    'status' => ($product_status == 1)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
                    'published' => ($product_status == 1)?Product::PRODUCT_PUBLISHED_YES:Product::PRODUCT_PUBLISHED_NO,
                    'permanent_hidden' => Product::STATUS_NO,
                    'user_connection_id' => $_userConnection->id,
                    'connection_product_id' => $mage_product_id, // Stores/Channel product ID,
                    'created_at' => $product_created_at, // Product created at date format date('Y-m-d H:i:s'),
                    'updated_at' => $product_updated_at, // Product updated at date format date('Y-m-d H:i:s'),
                    'type' => "", // Product Type
                    'images' => $product_image_data, // Product images data
                    'variations' => array(),
                    'options_set' => array(),
                    'websites' => $websites, //This is for only magento give only and blank array
                    'conversion_rate' => $conversion_rate,
                    'categories' => $product_categories_ids, // Product categroy array. If null give a blank array
                ];
                if ( !empty($product_data) ) {
                    Product::productImportingCommon($product_data);
                }
            }
        }
    }
    
    /**
     * Magento Order hooking
     */
    public function actionMagento2OrderCreate() {
        $url_data = $_GET['data'];
        $url_data = urldecode($url_data);
        $order_data = json_decode($url_data, true);
        $shop_url = $order_data['magento_url'];
        $shop_url = explode("://", $shop_url)[1];
        if(substr($shop_url, -1)){
            $shop_url = substr($shop_url, 0, -1);
        }
        $connection = Connection::find()->select('id')->where(['name' => 'Magento2'])->one();
        $connectionId = $connection->id;
        $user_connections = UserConnection::findAll(["connection_id"=> $connectionId]);

        foreach ($user_connections as $_userConnection) {
            $importUser = $_userConnection->user;
            $user_id = $importUser->id;
            $store_connection_details = $_userConnection->userConnectionDetails;
            $store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;
            $conversion_rate = 0;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, isset($importUser->currency) ? $importUser->currency : 'USD');
            }

            $magentoClientInfo = $_userConnection->connection_info;
            $magento_shop = $magentoClientInfo['magento_shop'];
            $magento_shop = explode("://", $magento_shop)[1];


            if (substr($magento_shop, -1)) {
                $magento_shop = substr($magento_shop, 0, -1);
            }
            if ($shop_url == $magento_shop) {
                $order_id = isset($order_data['order_id'])?$order_data['order_id']:0;
                $order_state = isset($order_data['order_state'])?$order_data['order_state']:'';
                $order_status = isset($order_data['order_status'])?$order_data['order_status']:'';
                $order_store_id = isset($order_data['order_store_id'])?$order_data['order_store_id']:0;
                $order_total = isset($order_data['order_total'])?$order_data['order_total']:0;
                $customer_email = isset($order_data['customer_email'])?$order_data['customer_email']:'';
                $order_shipping_amount = isset($order_data['order_shipping_amount'])?$order_data['order_shipping_amount']:0;
                $order_tax_amount = isset($order_data['base_tax_amount'])?$order_data['base_tax_amount']:0;
                $order_product_qty = isset($order_data['order_product_qty'])?$order_data['order_product_qty']:'';
                $customer_id = isset($order_data['customer_id'])?$order_data['customer_id']:'';
                $order_created_at = $order_data['order_created_at'];
                $order_updated_at = $order_data['order_updated_at'];
                $refund_amount = isset($order_data['refund_amount'])?$order_data['refund_amount']:0;
                $payment_method = isset($order_data['payment_method'])?$order_data['payment_method']:'';
                $discount_amount = isset($order_data['discount_amount'])?$order_data['discount_amount']:0;
                $order_items = $order_data['order_items'];

                switch (strtolower($order_status)) {
                    case "pending":
                        $order_status_2 = 'Pending';
                        break;
                    case "complete":
                        $order_status_2 = "Completed";
                        break;
                    case "canceled":
                        $order_status_2 = "Cancel";
                        break;
                    case "closed":
                        $order_status_2 = "Closed";
                        break;
                    case "fraud":
                        $order_status_2 = "Cancel";
                        break;
                    case "holded":
                        $order_status_2 = "On Hold";
                        break;
                    case "payment_review":
                        $order_status_2 = "Pending";
                        break;
                    case "paypal_canceled_reversal":
                        $order_status_2 = "Cancel";
                        break;
                    case "paypal_canceled_reversal":
                        $order_status_2 = "Cancel";
                        break;
                    case "paypal_reversed":
                        $order_status_2 = "Cancel";
                        break;
                    case "pending_payment":
                        $order_status_2 = "Pending";
                        break;
                    case "pending_paypal":
                        $order_status_2 = "Pending";
                        break;
                    case "processing":
                        $order_status_2 = "In Transit";
                        break;
                    default:
                        $order_status_2 = "Pending";
                }

                /**
                 * Order Shipping address
                 */
                $order_shipping_add = $order_data['shipping_detail'];
                $ship_state = isset($order_shipping_add['region'])?$order_shipping_add['region']:'';
                $ship_zip = $order_shipping_add['postcode'];
                $ship_address = $order_shipping_add['street'];
                $ship_address_1 = $ship_address[0];
                $ship_address_2 = '';
                if (array_key_exists('1', $ship_address)) {
                    $ship_address_2 = $ship_address[1];
                }
                $ship_city = $order_shipping_add['city'];
                $ship_customer_email = $order_shipping_add['email'];
                $ship_phone = $order_shipping_add['telephone'];
                $ship_country_id = $order_shipping_add['country_id'];
                $ship_first_name = $order_shipping_add['firstname'];
                $ship_last_name = $order_shipping_add['lastname'];

                /**
                 * Order Billing address
                 */
                $order_billing_add = $order_data['billing_detail'];
                $bill_state = isset($order_billing_add['region'])?$order_billing_add['region']:'';
                $bill_zip = $order_billing_add['postcode'];
                $bill_address = $order_billing_add['street'];
                $bill_address_1 = $bill_address[0];
                $bill_address_2 = '';
                if (array_key_exists('1', $bill_address)) {
                    $bill_address_2 = $ship_address[1];
                }
                $bill_city = $order_billing_add['city'];
                $bill_customer_email = $order_billing_add['email'];
                $bill_phone = $order_billing_add['telephone'];
                $bill_country_id = $order_billing_add['country_id'];
                $bill_firstname = $order_billing_add['firstname'];
                $bill_lastname = $order_billing_add['lastname'];


                $order_product_data = array();
                foreach($order_items as $_items){
                    $product_id = $_items['product_id'];
                    $title = $_items['name'];
                    $sku = $_items['sku'];
                    $price = $_items['price'];
                    $quantity = $_items['qty_ordered'];
                    $product_weight = isset($_items['weight'])?$_items['weight']:0;
                    $variant_id = isset($_items['item_id']) ? 0 : $_items['item_id'];
                    $order_product_data[] = array(
                        'user_id' => $user_id,
                        'connection_product_id' => $product_id,
                        'connection_variation_id' => $variant_id,
                        'name' => $title,
                        'order_product_sku' => $sku,
                        'price' => $price,
                        'qty' => $quantity,
                        'weight' => $product_weight,
                    );
                }
                $country_info = Country::countryInfoFromCode($ship_country_id);
                if (!empty($country_info)) {
                    $ship_country = $country_info->name;
                }else{
                    $ship_country = "";
                }
                $country_info = Country::countryInfoFromCode($bill_country_id);
                if (!empty($country_info)) {
                    $bill_country = $country_info->name;
                }else{
                    $bill_country = "";
                }
                $customer_data = array(
                    'connection_customerId' => $customer_id,
                    'user_id' => $user_id,
                    'first_name' => $bill_firstname,
                    'last_name' => $bill_lastname,
                    'email' => $bill_customer_email,
                    'phone' => $bill_phone,
                    'user_connection_id' => $_userConnection->id,
                    'customer_created' => $order_created_at,
                    'updated_at' => $order_updated_at,
                    'addresses' => [
                        [
                            'first_name' => $bill_firstname,
                            'last_name' => $bill_lastname,
                            'company' => '',
                            'country' => $bill_country,
                            'country_iso' => $bill_country_id,
                            'street_1' => $bill_address_1,
                            'street_2' => $bill_address_2,
                            'state' => $bill_state,
                            'city' => $bill_city,
                            'zip' => $bill_zip,
                            'phone' => $bill_phone,
                            'address_type' => 'Default',
                        ],
                    ]
                );

                $order_data = array(
                    'user_id' => $user_id,
                    'user_connection_id' => $_userConnection->id,
                    'connection_order_id' => $order_id,
                    'status' => $order_status_2,
                    'product_quantity' => $order_product_qty,
                    'ship_street_1' => $ship_address_1,
                    'ship_street_2' => $ship_address_2,
                    'ship_city' => $ship_city,
                    'ship_state' => $ship_state,
                    'ship_zip' => $ship_zip,
                    'ship_country' => $ship_country,
                    'ship_country_iso' => $ship_country_id,
                    'bill_street_1' => $bill_address_1,
                    'bill_street_2' => $bill_address_2,
                    'bill_city' => $bill_city,
                    'bill_state' => $bill_state,
                    'bill_country' => $bill_country,
                    'bill_zip' => $bill_zip,
                    'bill_country_iso' => $bill_country_id,
                    'fee' => [
                        'base_shippping_cost' => $order_shipping_amount,
                        'shipping_cost_tax' => $order_tax_amount,
                        'refunded_amount' => $refund_amount,
                        'discount_amount' => $discount_amount,
                        'payment_method' => $payment_method,
                    ],
                    'total_amount' => $order_total,
                    'order_date' => $order_created_at,
                    'created_at' => $order_created_at,
                    'updated_at' => $order_updated_at,
                    'customer_email' => $customer_email,
                    'currency_code' => $store_currency_code,
                    'country_code' => $store_country_code,
                    'conversion_rate' => $conversion_rate,
                    'order_products' => $order_product_data,
                    'order_customer' => $customer_data,
                );
                Order::orderImportingCommon($order_data);
            }
        }
    }

    public function actionTest(){
        Magento2Component::submitUpdatedProdcut(10,180);
    }


}

                    