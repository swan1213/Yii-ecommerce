<?php
namespace frontend\components;


use common\models\Category;
use common\models\Country;
use common\models\CurrencyConversion;
use common\models\Customer;
use common\models\Order;
use common\models\Product;
use common\models\ProductCategory;
use common\models\ProductConnection;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use SoapClient;
use SoapFault;
use yii\base\Component;


class MagentoComponent extends Component
{
    const timeOutLimit = 1;
    const errorTimeOutLimit = 20;
    const storeName = "magento";
    const pageLimit = 8;

    public static function createMagentoClient($user_connection_id){

        $store_shopify = UserConnection::findOne(['id' => $user_connection_id]);

        $magentoClientInfo = $store_shopify->connection_info;

        $magento_soap_url = $magentoClientInfo['magento_soap_url'];
        $magento_soap_user = $magentoClientInfo['magento_soap_user'];
        $magento_soap_api = $magentoClientInfo['magento_soap_api'];

        $cli = new SoapClient($magento_soap_url, array('trace' => true, 'exceptions' => true));
        $session_id = $cli->login($magento_soap_user, $magento_soap_api);

        return $session_id;
    }

    public static function addMagentoHooks($user_connection_id, $session_id) {

        $hookBaseUrl = env('SEVER_URL');

        $sc = self::createShopifyClient($user_connection_id);

        $shop_update_hook = [
            "webhook" => [
                "topic" => "shop/update",
                "address" => $hookBaseUrl . "hooklistener/shopify/shop-update?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];
        $product_create_hook = [
            "webhook" => [
                "topic" => "products/create",
                "address" => $hookBaseUrl . "hooklistener/shopify/product-create?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $product_delete_hook = [
            "webhook" => [
                "topic" => "products/delete",
                "address" => $hookBaseUrl . "hooklistener/shopify/product-delete?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $product_update_hook = [
            "webhook" => [
                "topic" => "products/update",
                "address" => $hookBaseUrl . "hooklistener/shopify/product-update?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $order_create_hook = [
            "webhook" => [
                "topic" => "orders/create",
                "address" => $hookBaseUrl . "hooklistener/shopify/order-create?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $order_update_hook = [
            "webhook" => [
                "topic" => "orders/updated",
                "address" => $hookBaseUrl . "hooklistener/shopify/order-update?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $customer_create_hook = [
            "webhook" => [
                "topic" => "customers/create",
                "address" => $hookBaseUrl . "hooklistener/shopify/customer-create?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];
        $customer_update_hook = [
            "webhook" => [
                "topic" => "customers/update",
                "address" => $hookBaseUrl . "hooklistener/shopify/customer-update?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $total_hooks_count = $sc->call('GET', '/admin/webhooks/count.json');
        $call_left = $sc->callsLeft();
        if ($call_left <= 8) {
            sleep(self::timeOutLimit);
        }
        $api_call_count = ceil($total_hooks_count / 250);

        $myhooks = array();

        for ($i = 1; $i <= $api_call_count; $i++) {
            $hooks_tag = $sc->call('GET', '/admin/webhooks.json?page=' . $i .'&limit=250');
            $call_left = $sc->callsLeft();
            if ($call_left <= 8) {
                sleep(self::timeOutLimit);
            }
            foreach ($hooks_tag as $exist_hooks) {
                $myhooks[] = $exist_hooks['address'];
            }
        }

        if (!in_array($hookBaseUrl . "hooklistener/shopify/shop-update?id=" . $user_connection_id, $myhooks)) {
            $hooks_add = $sc->call('POST', '/admin/webhooks.json', $shop_update_hook);
        }

        if (!in_array($hookBaseUrl . "hooklistener/shopify/product-create?id=" . $user_connection_id, $myhooks)) {
            $hooks_add = $sc->call('POST', '/admin/webhooks.json', $product_create_hook);
        }
        if (!in_array($hookBaseUrl . "hooklistener/shopify/product-delete?id=" . $user_connection_id, $myhooks)) {
            $hooks_add = $sc->call('POST', '/admin/webhooks.json', $product_delete_hook);
        }
        if (!in_array($hookBaseUrl . "hooklistener/shopify/product-update?id=" . $user_connection_id, $myhooks)) {
            $hooks_add = $sc->call('POST', '/admin/webhooks.json', $product_update_hook);
        }
        if (!in_array($hookBaseUrl . "hooklistener/shopify/order-create?id=" . $user_connection_id, $myhooks)) {
            $hooks_add = $sc->call('POST', '/admin/webhooks.json', $order_create_hook);
        }
        if (!in_array($hookBaseUrl . "hooklistener/shopify/order-update?id=" . $user_connection_id, $myhooks)) {
            $hooks_add = $sc->call('POST', '/admin/webhooks.json', $order_update_hook);
        }

        if (!in_array($hookBaseUrl . "hooklistener/shopify/customer-create?id=" . $user_connection_id, $myhooks)) {
            $hooks_add = $sc->call('POST', '/admin/webhooks.json', $customer_create_hook);
        }
        if (!in_array($hookBaseUrl . "hooklistener/shopify/customer-update?id=" . $user_connection_id, $myhooks)) {
            $hooks_add = $sc->call('POST', '/admin/webhooks.json', $customer_update_hook);
        }

    }


    public static function magentoCategoryImport($userConnectionModel,  $session_id, $cli) {
        $importUser = $userConnectionModel->user;
        $user_id = $importUser->id;

        // All Category of magento store
        $category_result = $cli->call($session_id, 'catalog_category.tree');
        if(count($category_result)>0){
            self::insertCagegory($userConnectionModel, $user_id,  $session_id, $cli, $category_result);
        }
    }

    public static function insertCagegory($userConnectionModel, $user_id,  $session_id, $cli, $cate_data, $connection_parent_id=0) {
        $mag_cat_id = $cate_data['category_id'];
        $mag_parent_id = $cate_data['parent_id'];
        $mag_cat_name = $cate_data['name'];
        $cat_child = $cate_data['children'];
        $detail_result = $cli->call($session_id, 'catalog_category.info', $mag_cat_id);
        $category_data = [
            'name' => $mag_cat_name, // Give category name
            'description' => $detail_result['description'], // Give category body html
            'parent_id' => $connection_parent_id,
            'user_id' => $user_id, // Give Elliot user id,
            'user_connection_id' => $userConnectionModel->id, // Give Channel/Store prefix id
            'connection_category_id' => $mag_cat_id, // Give category id of Store/channels
            'connection_parent_id' => $mag_parent_id, //Give parent id of Store/channels
            'created_at' => $detail_result['created_at'], // Give Created at date if null then give current date format date('Y-m-d H:i:s')
            'updated_at' => $detail_result['updated_at'], // Give Updated at date if null then give current date format date('Y-m-d H:i:s')
        ];

        $categroy_id = Category::categoryImportingCommon($category_data);
        if (count($cat_child) > 0) {
            foreach ($cat_child as $value) {
                self::insertCagegory($userConnectionModel, $user_id,  $session_id, $cli, $value, $connection_parent_id=$categroy_id) ;
            }
        }
    }

    public static function magentoProductImport($userConnectionModel, $session_id, $cli, $conversion_rate = 1) {
        $magentoClientInfo = $userConnectionModel->connection_info;

        $importUser = $userConnectionModel->user;
        $store_connection_details = $userConnectionModel->userConnectionDetails;
        $user_id = $importUser->id;

        $store_country_code = $store_connection_details->country_code;
        $store_currency_code = $store_connection_details->currency;

        //All Product list of magento store
        $product_result = $cli->call($session_id, 'catalog_product.list');

        foreach ($product_result as $_product) {

            $_product['magento_soap_url'] = $magentoClientInfo['magento_soap_url'];
            $_product['user_id'] = $user_id;
            $_product['store_currency_code'] = $store_currency_code;
            $_product['store_country_code'] = $store_country_code;
            $_product['user_connection_id'] = $userConnectionModel->id;
            $_product['conversion_rate'] = $conversion_rate;

            MagentoComponent::insertProduct($_product, $session_id, $cli);
        }
    }

    public static function insertProduct($_product, $session_id, $cli){
        $magento_shop = $_product['magento_soap_url'];
        $user_id = $_product['user_id'];
        $store_currency_code = $_product['store_currency_code'];
        $store_country_code = $_product['store_country_code'];
        $user_connection_id = $_product['user_connection_id'];
        $conversion_rate = $_product['conversion_rate'];
        $product_id = $_product['product_id'];
        $product_info = $cli->call($session_id, 'catalog_product.info', $product_id);

        $title = $product_info['name'];
        $product_sku = $product_info['sku'];
        $product_categories_ids = $product_info['category_ids'];
        $description = $product_info['description'];
        $product_url_path = $product_info['url_path'];
        $product_type = $product_info['type'];
        $product_weight = '';
        if (array_key_exists('weight', $product_info)) {
            $product_weight = $product_info['weight'];
        }
        $product_url = $magento_shop . '/' . $product_url_path;
        $websites = $product_info['websites'];
        $created_date = date('Y-m-d h:i:s', strtotime($product_info['created_at']));
        $updated_date = date('Y-m-d h:i:s', strtotime($product_info['updated_at']));
        $product_status = $product_info['status'];
        $product_price = $product_info['price'];
        $stock_qty = $cli->call($session_id, 'cataloginventory_stock_item.list', $product_id);
        $product_quantity = $stock_qty[0]['qty'];
        $stock_status = ($stock_qty[0]['is_in_stock'] == 1) ? $stock_qty[0]['is_in_stock'] : 0;

        $product_images = $cli->call($session_id, 'catalog_product_attribute_media.list', $product_id);
        $product_image_data = array();
        foreach ($product_images as $_image) {
            $image_id ='';
            $image_src = $_image['url'];
            $image_position = $_image['position'];
            $image_type = $_image['types'];
            $base_image = '';
            if (in_array('image', $image_type)) {
                $base_image = $image_src;
            }
            $product_image_data[] = array(
                'connection_image_id' => $image_id,
                'image_url' => $image_src,
                'label' => '',
                'position' => $image_position,
                'base_img' => $base_image,
            );
        }
        /* Fields which are required but not avaialable @Shopify */

        $p_upc = '';
        $p_ean = '';
        $p_jan = '';
        $p_isbn = '';
        $p_mpn = '';
        $variants_data = [];
        $options_set_data = [];

        $product_data = [
            'user_id' => $user_id, // Elliot user id,
            'name' => $title, // Product name,
            'sku' => $product_sku, // Product SKU,
            'url' => $product_url, // Product url if null give blank value,
            'upc' => $p_upc, // Product upc if any,
            'ean' => $p_ean,
            'jan' => $p_jan, // Product jan if any,
            'isbn' => $p_isbn, // Product isban if any,
            'mpn' => $p_mpn, // Product mpn if any,
            'description' => $description, // Product Description,
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
            'stock_quantity' => $product_quantity, //Product quantity,
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
            'user_connection_id' => $user_connection_id,
            'connection_product_id' => $product_id, // Stores/Channel product ID,
            'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s'),
            'updated_at' => $updated_date, // Product updated at date format date('Y-m-d H:i:s'),
            'type' => $product_type, // Product Type
            'images' => $product_image_data, // Product images data
            'variations' => $variants_data,
            'options_set' => $options_set_data,
            'websites' => $websites, //This is for only magento give only and blank array
            'conversion_rate' => $conversion_rate,
            'categories' => $product_categories_ids, // Product categroy array. If null give a blank array
        ];
        if ( !empty($product_data) ) {
            Product::productImportingCommon($product_data);
        }
    }


    public static function magentoCustomerImport($userConnectionModel,  $session_id, $cli)
    {


        $importUser = $userConnectionModel->user;
        $user_id = $importUser->id;
        //All Customer list of magento store
        $customer_list = $cli->call($session_id, 'customer.list');
        foreach ($customer_list as $_customer) {
            $_customer['user_id'] = $user_id;
            $_customer['user_connection_id'] = $userConnectionModel->id;

            self::insertCustomer($_customer,  $session_id, $cli);
        }
    }

    public static function insertCustomer($_customer,  $session_id, $cli){

        $user_id = $_customer['user_id'];
        $user_connection_id = $_customer['user_connection_id'];


        $customer_id = $_customer['customer_id'];
        $customer_email = isset($_customer['email']) ? $_customer['email'] : '';
        $customer_f_name = isset($_customer['firstname']) ? $_customer['firstname'] : '';
        $customer_l_name = isset($_customer['lastname']) ? $_customer['lastname'] : '';
        $customer_create_date = isset($_customer['created_at']) ? $_customer['created_at'] : time();
        $customer_update_date = isset($_customer['updated_at']) ? $_customer['updated_at'] : time();
        $cus_dob = isset($_customer['dob']) ? $_customer['dob'] : '';
        $cus_gender = isset($_customer['gender']) ? $_customer['gender'] : 'Unisex';

        $addr_fname = $addr_lname = $company = $add_1 = $add_2 = $city = $state = $country = $zip = $add_phone = $country_code = '';
        if (isset($_customer['default_billing'])){
            $customer_default_bill_id = $_customer['default_billing'];
            $customer_bill_address = $cli->call($session_id, 'customer_address.info', $customer_default_bill_id);
            $addr_fname = $customer_f_name;
            $addr_lname = $customer_l_name;
            $company = isset($customer_bill_address['company']) ? $customer_bill_address['company'] : '';
            $add_1 = isset($customer_bill_address['street']) ? $customer_bill_address['street'] : '';
            $add_2 =  '';
            $city = isset($customer_bill_address['city']) ? $customer_bill_address['city'] : '';
            $state = isset($customer_bill_address['region']) ? $customer_bill_address['region'] : '';
            $country_code = isset($customer_bill_address['country_id']) ? $customer_bill_address['country_id'] : '';
            $country_info = Country::countryInfoFromCode($country_code);
            if (!empty($country_info)) {
                $country = $country_info->name;
            }else{
                $country = "";
            }
            $zip = isset($customer_bill_address['postcode']) ? $customer_bill_address['postcode'] : '';
            $add_phone = isset($customer_bill_address['telephone']) ? $customer_bill_address['telephone'] : '';
        }

        $customer_data = array(
            'connection_customerId' => $customer_id,
            'user_id' => $user_id,
            'first_name' => $customer_f_name,
            'last_name' => $customer_l_name,
            'email' => $customer_email,
            'dob' => $cus_dob,
            'gender' => $cus_gender,
            'phone' => $add_phone,
            'user_connection_id' => $user_connection_id,
            'customer_created' => $customer_create_date,
            'updated_at' => $customer_update_date,
            'addresses' => [
                [
                    'first_name' => $addr_fname,
                    'last_name' => $addr_lname,
                    'company' => $company,
                    'country' => $country,
                    'country_iso' => $country_code,
                    'street_1' => $add_1,
                    'street_2' => $add_2,
                    'state' => $state,
                    'city' => $city,
                    'zip' => $zip,
                    'phone' => $add_phone,
                    'address_type' => 'Default',
                ],
            ]
        );

        Customer::customerImportingCommon($customer_data);

    }
    /**
     * Shopify Customer Import
     */

    public static function magentoOrderImport($userConnectionModel, $session_id, $cli, $conversion_rate=1) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $importUser = $userConnectionModel->user;
        $store_connection_details = $userConnectionModel->userConnectionDetails;
        $user_id = $importUser->id;

        $store_currency_code = $store_connection_details->currency;
        $store_country_code = $store_connection_details->country_code;
        //All Order list of magento store
        $orders_list = $cli->call($session_id, 'sales_order.list');
        foreach ($orders_list as $_order) {
            $order_increment_id = $_order['increment_id'];
            $order_data = $cli->call($session_id, 'sales_order.info', $order_increment_id);

            $order_data['user_id'] = $user_id;
            $order_data['user_connection_id'] = $userConnectionModel->id;
            $order_data['store_currency_code'] = $store_currency_code;
            $order_data['store_country_code'] = $store_country_code;
            $order_data['conversion_rate'] = $conversion_rate;

            self::insertOrder($order_data);
        }
    }

    public static function insertOrder($order_data){

        $user_id = $order_data['user_id'];
        $user_connection_id = $order_data['user_connection_id'];
        $store_currency_code = $order_data['store_currency_code'];
        $store_country_code = $order_data['store_country_code'];
        $conversion_rate = $order_data['conversion_rate'];

        $order_id = $order_data['order_id'];
        $order_state = $order_data['state'];
        $order_status = $order_data['status'];
        $order_store_id = $order_data['store_id'];
        $order_shipping_description = $order_data['shipping_description'];
        $order_total = $order_data['base_grand_total'];
        $customer_email = $order_data['customer_email'];
        $order_shipping_amount = $order_data['base_shipping_amount'];
        $order_tax_amount = $order_data['base_tax_amount'];
        $order_product_qty = $order_data['total_qty_ordered'];
        $customer_id = $order_data['customer_id'];
        $order_created_at = $order_data['created_at'];
        $order_updated_at = $order_data['updated_at'];
        $payment_method = $order_data['payment']['method'];
        $refund_amount = $order_data['subtotal_refunded'];
        $discount_amount = $order_data['discount_amount'];

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

        $ship_state = $ship_zip = $ship_address = $ship_city = $ship_customer_email = $ship_phone = $ship_country_id = $ship_firstname = $ship_lastname = $ship_middlename = '';
        if (isset($order_data['shipping_address'])) {
            $order_shipping_add = $order_data['shipping_address'];
            $ship_state = isset($order_shipping_add['region']) ? $order_shipping_add['region'] : '';
            $ship_zip = isset($order_shipping_add['postcode']) ? $order_shipping_add['postcode'] : 0000;
            $ship_address = isset($order_shipping_add['street']) ? $order_shipping_add['street'] : '';
            $ship_city = isset($order_shipping_add['city']) ? $order_shipping_add['city'] : '';
            $ship_country_id = isset($order_shipping_add['country_id']) ? $order_shipping_add['country_id'] : '';
        }

        $bill_state = $bill_zip = $bill_address = $bill_city = $bill_customer_email = $bill_phone = $bill_country_id = $bill_firstname = $bill_lastname = $bill_middlename =  '';
        if (isset($order_data['billing_address'])) {
            $order_billing_add = $order_data['billing_address'];
            $bill_state = isset($order_billing_add['region']) ? $order_billing_add['region'] : '';
            $bill_zip = isset($order_billing_add['postcode']) ? $order_billing_add['postcode'] : 0000;
            $bill_address = isset($order_billing_add['street']) ? $order_billing_add['street'] : '';
            $bill_city = isset($order_billing_add['city']) ? $order_billing_add['city'] : '';
            $bill_phone = isset($order_billing_add['telephone']) ? $order_billing_add['telephone'] : '';
            $bill_country_id = isset($order_billing_add['country_id']) ? $order_billing_add['country_id'] : '';
        }

        $order_items = $order_data['items'];
        $order_product_data = array();
        foreach ($order_items as $_items) {
            $title = $_items['name'];
            $quantity = $_items['qty_ordered'];
            $price = $_items['price'];
            $sku = '';
            if ($_items['sku'] != "") {
                $sku = $_items['sku'];
            }
            $product_id = isset($_items['product_id']) ? 0 : $_items['product_id'];
            $variant_id = isset($_items['item_id']) ? 0 : $_items['item_id'];
            $product_weight = isset($_items['row_weight']) ? $_items['row_weight'] : 0;
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
        $customer_id = ($customer_id == '') ? 0 : $customer_id;
        $customer_data = array(
            'connection_customerId' => $customer_id,
            'user_id' => $user_id,
            'first_name' => $order_data['customer_firstname'],
            'last_name' => $order_data['customer_lastname'],
            'email' => $order_data['customer_email'],
            'phone' => $bill_phone,
            'user_connection_id' => $user_connection_id,
            'customer_created' => $order_data['created_at'],
            'updated_at' => $order_data['updated_at'],
            'addresses' => [
                [
                    'first_name' => $order_data['customer_firstname'],
                    'last_name' => $order_data['customer_lastname'],
                    'company' => '',
                    'country' => $bill_country,
                    'country_iso' => $bill_country_id,
                    'street_1' => $bill_address,
                    'street_2' => '',
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
            'user_connection_id' => $user_connection_id,
            'connection_order_id' => $order_id,
            'status' => $order_status_2,
            'product_quantity' => $order_product_qty,
            'ship_street_1' => $ship_address,
            'ship_street_2' => '',
            'ship_city' => $ship_city,
            'ship_state' => $ship_state,
            'ship_zip' => $ship_zip,
            'ship_country' => $ship_country,
            'ship_country_iso' => $ship_country_id,
            'bill_street_1' => $bill_address,
            'bill_street_2' => '',
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

    public static function submitUpdatedProduct($user_connection_id, $prodcut_id){
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        ini_set('soap.wsdl_cache_enabled',0);
        ini_set('soap.wsdl_cache_ttl',0);
        ini_set('default_socket_timeout', 7200);

        $store_magento= UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_magento)) {
            $importUser = $store_magento->user;
            $store_connection_details = $store_magento->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency) ? $importUser->currency : 'USD';
            $store_currency_code = $store_connection_details->currency;

            $conversion_rate = 0;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($userCurrency, $store_currency_code);
            }
            $magentoClientInfo = $store_magento->connection_info;

            $magento_soap_url = $magentoClientInfo['magento_soap_url'];
            $magento_soap_user = $magentoClientInfo['magento_soap_user'];
            $magento_soap_api = $magentoClientInfo['magento_soap_api'];

            $product =  Product::findOne(["id"=>$prodcut_id]);

            try {
                $cli = new SoapClient($magento_soap_url);
                $session_id = $cli->login($magento_soap_user, $magento_soap_api);
                $result = $cli->call($session_id, 'catalog_product.update', array($product->sku, array(
                    'name' => $product->name,
                    'description' => $product->description,
                    'weight' => $product->weight,
                    'status' => $product->status==Product::STATUS_ACTIVE?1:0,
                    'price' => number_format((float)$conversion_rate * $product->price, 2, '.', ''),
                )));

                if(isset($result['product_id']))
                    return json_encode(array(
                        'success' => true,
                        'product_id' => $result['product_id'],
                        'user_connection_id' => $user_connection_id,
                        'message' => "Product has been updated!"
                    ));
                else
                    return json_encode(array(
                        'success' => false,
                        'product_id' => -1,
                        'user_connection_id' => $user_connection_id,
                        'message' => "Couldn't find a user connection"
                    ));

            } catch (SoapFault $e) {
                $e->faultstring;
                return json_encode(array(
                    'success' => false,
                    'product_id' => -1,
                    'user_connection_id' => $user_connection_id,
                    'message' => $e->getMessage()
                ));
            }
        }
        else{
            return json_encode(array(
                'success' => false,
                'product_id' => -1,
                'user_connection_id' => $user_connection_id,
                'message' => "Couldn't find a user connection"
            ));
        }
    }

    public static function submitNewProduct($user_connection_id, $prodcut_id){
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        ini_set('soap.wsdl_cache_enabled',0);
        ini_set('soap.wsdl_cache_ttl',0);
        ini_set('default_socket_timeout', 7200);

        $store_magento= UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_magento)) {
            $importUser = $store_magento->user;
            $store_connection_details = $store_magento->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency) ? $importUser->currency : 'USD';
            $store_currency_code = $store_connection_details->currency;

            $conversion_rate = 0;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($userCurrency, $store_currency_code);
            }
            $magentoClientInfo = $store_magento->connection_info;

            $magento_soap_url = $magentoClientInfo['magento_soap_url'];
            $magento_soap_user = $magentoClientInfo['magento_soap_user'];
            $magento_soap_api = $magentoClientInfo['magento_soap_api'];

            $product =  Product::findOne(["id"=>$prodcut_id]);
            try {
                $cli = new SoapClient($magento_soap_url);
                $session_id = $cli->login($magento_soap_user, $magento_soap_api);
                $attributeSets = $cli->call($session_id, 'product_attribute_set.list');
                $attributeSet = current($attributeSets);
                $connection = \Yii::$app->db;

                $model = $connection->createCommand("SELECT connection_category_id FROM category, product_category WHERE product_category.product_id = $prodcut_id AND category.`id` = product_category.category_id");
                $categories = $model->queryAll();

                $result = $cli->call($session_id, 'catalog_product.create', array('simple', $attributeSet['set_id'], $product->sku, array(
                    'categories' => $categories,
                    'name' => $product->name,
                    'description' => $product->description,
                    'short_description' => $product->description,
                    'weight' => $product->weight,
                    'status' => $product->status==Product::STATUS_ACTIVE?1:0,
                    'visibility' => '4',
                    'price' => number_format((float)$conversion_rate * $product->price, 2, '.', ''),
                )));
                if(isset($result['product_id']))
                    return json_encode(array(
                        'success' => true,
                        'product_id' => $result['product_id'],
                        'user_connection_id' => $user_connection_id,
                        'message' => "Product has been exported!"
                    ));

            } catch (SoapFault $e) {
                $e->faultstring;
            }
        }
        return json_encode(array(
            'success' => false,
            'product_id' => -1,
            'user_connection_id' => $user_connection_id,
            'message' => "No connections!"
        ));
    }


    public static function deleteProduct($user_connection_id, $prodcut_id){
        ini_set("memory_limit", "-1");
        set_time_limit(0);
        ini_set('soap.wsdl_cache_enabled',0);
        ini_set('soap.wsdl_cache_ttl',0);
        ini_set('default_socket_timeout', 7200);

        $product = ProductConnection::findOne(['product_id' =>$prodcut_id, "user_connection_id" =>$user_connection_id]);
        $store_magento= UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_magento) && !empty($product)) {
            $magentoClientInfo = $store_magento->connection_info;

            $magento_soap_url = $magentoClientInfo['magento_soap_url'];
            $magento_soap_user = $magentoClientInfo['magento_soap_user'];
            $magento_soap_api = $magentoClientInfo['magento_soap_api'];
            try {
                $cli = new SoapClient($magento_soap_url);
                $session_id = $cli->login($magento_soap_user, $magento_soap_api);
                $cli->call($session_id, 'catalog_product.delete', $product->connection_product_id);
                return true;

            } catch (SoapFault $e) {
                $e->faultstring;
                return false;
            }
        }
        return false;
    }
}