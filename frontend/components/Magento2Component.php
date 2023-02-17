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
use yii\base\Exception;


class Magento2Component extends Component
{
    const timeOutLimit = 1;
    const errorTimeOutLimit = 20;
    const storeName = "magento2";
    const pageLimit = 8;


    public static function magentoCategoryImport($userConnectionModel, $magento_shop, $access_token) {
        $importUser = $userConnectionModel->user;
        $user_id = $importUser->id;

        $ch = curl_init($magento_shop . "index.php/rest/V1/categories");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $category_result = curl_exec($ch);
        $category_result=  json_decode($category_result, TRUE);
        if(count($category_result)>0){
            self::insertCagegory($userConnectionModel, $user_id, $userConnectionModel->id, $magento_shop, $access_token, $category_result);
        }
    }

    public static function insertCagegory($userConnectionModel,$user_id, $store_connection_id, $magento_shop, $access_token, $cate_data, $connection_parent_id=0) {
        $mag_cat_id = $cate_data['id'];
        $mag_parent_id = $cate_data['parent_id'];
        $mag_cat_name = $cate_data['name'];
        $cat_child = $cate_data['children_data'];
        $ch = curl_init($magento_shop . "index.php/rest/V1/categories/{$mag_cat_id}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $detail_result = curl_exec($ch);
        $detail_result=  json_decode($detail_result, TRUE);
        $category_data = [
            'name' => $mag_cat_name, // Give category name
            'description' => '', // Give category body html
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
                self::insertCagegory($userConnectionModel, $user_id, $userConnectionModel->id, $magento_shop, $access_token, $value, $categroy_id);
            }
        }
    }

    public static function magentoProductImport($userConnectionModel, $magento_shop, $access_token, $conversion_rate = 1) {
        $importUser = $userConnectionModel->user;
        $store_connection_details = $userConnectionModel->userConnectionDetails;
        $user_id = $importUser->id;

        $store_country_code = $store_connection_details->country_code;
        $store_currency_code = $store_connection_details->currency;

        $ch = curl_init($magento_shop . "index.php/rest/V1/products?searchCriteria=");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $product_list = curl_exec($ch);
        $product_list=  json_decode($product_list, TRUE);
        $product_result = $product_list['items'];
        foreach ($product_result as $_product) {

            $_product['user_id'] = $user_id;
            $_product['store_currency_code'] = $store_currency_code;
            $_product['store_country_code'] = $store_country_code;
            $_product['user_connection_id'] = $userConnectionModel->id;
            $_product['conversion_rate'] = $conversion_rate;
            $_product['access_token'] = $access_token;

            self::insertProduct($_product, $magento_shop);
        }
    }

    public static function insertProduct($product_item, $magento_shop){
        $user_id = $product_item['user_id'];
        $store_currency_code = $product_item['store_currency_code'];
        $store_country_code = $product_item['store_country_code'];
        $user_connection_id = $product_item['user_connection_id'];
        $conversion_rate = $product_item['conversion_rate'];
        $access_token = $product_item['access_token'];
        $product_sku = $product_item['sku'];

        $ch = curl_init($magento_shop . "index.php/rest/V1/products/{$product_sku}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $_product = curl_exec($ch);
        $_product=  json_decode($_product, TRUE);

        $product_id = $_product['id'];
        $title = $_product['name'];
        $product_description = '';
        $product_url = '';
        $product_categories_ids = array();
        if(isset($_product['custom_attributes']))
        {
            $custom_attributes = $_product['custom_attributes'];
            foreach($custom_attributes as $attribute)
            {
                if($attribute['attribute_code']=='description')
                {
                    $product_description = $attribute['value'];
                }

                if($attribute['attribute_code']=='category_ids')
                {
                    $product_categories_ids = $attribute['value'];
                }

                if($attribute['attribute_code']=='url_key')
                {
                    $product_url = $magento_shop.$attribute['value'].'.html';
                }
            }
        }
        $product_type = $_product['type_id'];
        $product_weight = isset($_product['weight'])?$_product['weight']:0;
        $websites = array();
        $created_date = $_product['created_at'];
        $updated_date = $_product['updated_at'];
        $product_status = $_product['status'];
        $product_price = isset($_product['price'])?$_product['price']:0;
        $product_quantity = $_product['extension_attributes']['stock_item']['qty'];
        $stock_status = $_product['extension_attributes']['stock_item']['is_in_stock'];

        $product_images = isset($_product['media_gallery_entries'])?$_product['media_gallery_entries']:array();
        $product_image_data = array();
        foreach ($product_images as $_image) {
            $image_id =$_image['id'];
            $magento_product_image_url = $magento_shop.'/pub/media/catalog/product'.$_image['file'];
            $image_position = $_image['position'];
            $image_type = $_image['types'];
            $label = $_image['label'];
            $base_image = '';
            if (in_array('image', $image_type)) {
                $base_image = $magento_product_image_url;
            }
            $product_image_data[] = array(
                'connection_image_id' => $image_id,
                'image_url' => $magento_product_image_url,
                'label' => $label,
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

    public static function magentoCustomerImport($userConnectionModel, $magento_shop, $access_token)
    {
        $importUser = $userConnectionModel->user;
        $user_id = $importUser->id;
        //All Customer list of magento store
        $ch = curl_init($magento_shop . "index.php/rest/V1/customers/search?searchCriteria=");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $customer_list = curl_exec($ch);
        $customer_list=  json_decode($customer_list, TRUE);
        $customer_list = $customer_list['items'];
        foreach ($customer_list as $_customer) {
            $_customer['user_id'] = $user_id;
            $_customer['user_connection_id'] = $userConnectionModel->id;

            self::insertCustomer($_customer);
        }
    }

    public static function insertCustomer($_customer){
        $user_id = $_customer['user_id'];
        $user_connection_id = $_customer['user_connection_id'];


        $customer_id = $_customer['id'];
        $customer_email = $_customer['email'];
        $customer_f_name = isset($_customer['firstname']) ? $_customer['firstname'] : '';
        $customer_l_name = isset($_customer['lastname']) ? $_customer['lastname'] : '';
        $customer_create_date = isset($_customer['created_at']) ? $_customer['created_at'] : time();
        $customer_update_date = isset($_customer['updated_at']) ? $_customer['updated_at'] : time();
        $cus_dob = isset($_customer['dob']) ? $_customer['dob'] : '';
        $cus_gender = isset($_customer['gender']) ? $_customer['gender'] : 'Unisex';
        $addr_fname = $addr_lname = $company = $add_1 = $add_2 = $city = $state = $country_code = $country = $zip = $add_phone = "";
        if (isset($_customer['addresses'][0]['id'])){

            $customer_bill_address = $_customer['addresses'][0];
            $addr_fname = isset($customer_bill_address['firstname']) ? $customer_bill_address['firstname'] : $customer_f_name;
            $addr_lname = isset($customer_bill_address['lastname']) ? $customer_bill_address['lastname'] : $customer_l_name;
            $company =  '';
            $add_1 = isset($customer_bill_address['street'][0]) ? $customer_bill_address['street'][0] : '';
            $add_2 =  isset($customer_bill_address['street'][1]) ? $customer_bill_address['street'][1] : '';
            $city = isset($customer_bill_address['city']) ? $customer_bill_address['city'] : '';
            $state = isset($customer_bill_address['region']['region']) ? $customer_bill_address['region']['region'] : '';
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
            'connection_customerId' => "$customer_id",
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

    public static function magentoOrderImport($userConnectionModel, $magento_shop, $access_token, $conversion_rate=1) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $importUser = $userConnectionModel->user;
        $store_connection_details = $userConnectionModel->userConnectionDetails;
        $user_id = $importUser->id;

        $store_currency_code = $store_connection_details->currency;
        $store_country_code = $store_connection_details->country_code;
        //All Order list of magento store
        $ch = curl_init($magento_shop . "index.php/rest/V1/orders?searchCriteria=");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $order_list = curl_exec($ch);
        $order_list=  json_decode($order_list, TRUE);
        $order_list = $order_list['items'];
        foreach ($order_list as $_order) {

            $_order['user_id'] = $user_id;
            $_order['user_connection_id'] = $userConnectionModel->id;
            $_order['store_currency_code'] = $store_currency_code;
            $_order['store_country_code'] = $store_country_code;
            $_order['conversion_rate'] = $conversion_rate;

            self::insertOrder($_order);
        }
    }

    public static function insertOrder($_order){
        try{
            $user_id = $_order['user_id'];
            $user_connection_id = $_order['user_connection_id'];
            $store_currency_code = $_order['store_currency_code'];
            $store_country_code = $_order['store_country_code'];
            $conversion_rate = $_order['conversion_rate'];

            $order_id = isset($_order['entity_id'])?$_order['entity_id']:0;
            $order_status = isset($_order['status'])?$_order['status']:'';
            $order_store_id = isset($_order['store_id'])?$_order['store_id']:0;
            $order_total = isset($_order['base_grand_total'])?$_order['base_grand_total']:0;
            $customer_email = isset($_order['customer_email'])?$_order['customer_email']:'';
            $order_shipping_amount = isset($_order['base_shipping_amount'])?$_order['base_shipping_amount']:0;
            $order_tax_amount = isset($_order['base_tax_amount'])?$_order['base_tax_amount']:0;
            $order_product_qty = isset($_order['total_qty_ordered'])?$_order['total_qty_ordered']:0;
            $customer_id = isset($_order['customer_id'])?$_order['customer_id']:0;
            $order_created_at = isset($_order['created_at'])?$_order['created_at']:date('Y-m-d h:i:s');
            $order_updated_at = isset($_order['updated_at'])?$_order['updated_at']:date('Y-m-d h:i:s');
            $payment_method = $_order['payment']['additional_information'][0];
            $refund_amount = isset($_order['subtotal_refunded'])?$_order['subtotal_refunded']:0;
            $discount_amount = isset($_order['discount_amount'])?$_order['discount_amount']:0;

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
            $ship_state = $ship_zip = $ship_address_1 = $ship_address_2 = $ship_city = $ship_phone = $ship_country_id = $shipping_address = $ship_first_name = $ship_last_name = '';
            if(isset($_order['extension_attributes']['shipping_assignments'][0]['shipping']['address']))
            {
                $order_shipping_add = $_order['extension_attributes']['shipping_assignments'][0]['shipping']['address'];
                $ship_state = isset($order_shipping_add['region'])?$order_shipping_add['region']:'';
                $ship_zip = isset($order_shipping_add['postcode'])?$order_shipping_add['postcode']:'';
                $ship_address_1 = isset($order_shipping_add['street'][0])?$order_shipping_add['street'][0]:'';
                $ship_address_2 = '';
                if(array_key_exists(1, $order_shipping_add['street']))
                {
                    $ship_address_2 = $order_shipping_add['street'][1];
                }
                $ship_city = isset($order_shipping_add['city'])?$order_shipping_add['city']:'';
                $ship_phone = isset($order_shipping_add['telephone'])?$order_shipping_add['telephone']:'';
                $ship_country_id = isset($order_shipping_add['country_id'])?$order_shipping_add['country_id']:'';
                $ship_first_name = isset($order_shipping_add['firstname'])?$order_shipping_add['firstname']:'';
                $ship_last_name = isset($order_shipping_add['lastname'])?$order_shipping_add['lastname']:'';
            }

            /** Order Billing address */
            $bill_state = $bill_zip = $bill_address_1 = $bill_address_2 = $bill_city = $bill_customer_email = $bill_phone = $bill_country_id = $bill_firstname = $bill_lastname = $billing_address = '';
            if($_order['billing_address']){
                $order_billing_add = $_order['billing_address'];
                $bill_state = isset($order_billing_add['region'])?$order_billing_add['region']:'';
                $bill_zip = isset($order_billing_add['postcode'])?$order_billing_add['postcode']:'';
                $bill_address_1 = isset($order_billing_add['street'][0])?$order_billing_add['street'][0]:'';
                $bill_address_2 = '';
                if(array_key_exists(1, $order_billing_add['street'])){
                    $bill_address_2 = $order_billing_add['street'][1];
                }
                $bill_city = isset($order_billing_add['city'])?$order_billing_add['city']:'';
                $bill_customer_email = isset($order_billing_add['email'])?$order_billing_add['email']:'';
                $bill_phone = isset($order_billing_add['telephone'])?$order_billing_add['telephone']:'';
                $bill_country_id = isset($order_billing_add['country_id'])?$order_billing_add['country_id']:'';
                $bill_firstname = isset($order_billing_add['firstname'])?$order_billing_add['firstname']:'';
                $bill_lastname = isset($order_billing_add['lastname'])?$order_billing_add['lastname']:'';
            }


            $order_items = $_order['items'];
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
                $product_weight = isset($_items['weight']) ? $_items['weight'] : 0;
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
                'first_name' => isset($_order['customer_firstname']) ? $_order['customer_firstname'] : $bill_firstname,
                'last_name' => isset($_order['customer_lastname']) ? $_order['customer_lastname'] : $bill_lastname,
                'email' => isset($_order['customer_email']) ? $_order['customer_email'] : $bill_customer_email,
                'phone' => $bill_phone,
                'user_connection_id' => $user_connection_id,
                'customer_created' => $_order['created_at'],
                'updated_at' => $_order['updated_at'],
                'addresses' => [
                    [
                        'first_name' => isset($_order['customer_firstname']) ? $_order['customer_firstname'] : $bill_firstname,
                        'last_name' => isset($_order['customer_lastname']) ? $_order['customer_lastname'] : $bill_lastname,
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
                'user_connection_id' => $user_connection_id,
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
        catch (\yii\console\Exception $exception){
        }
    }

    public static function submitUpdatedProduct($user_connection_id, $prodcut_id){
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $store_magento= UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_magento)) {
            $importUser = $store_magento->user;
            $store_connection_details = $store_magento->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';
            $store_currency_code = $store_connection_details->currency;

            $conversion_rate = 0;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }
            $magentoClientInfo = $store_magento->connection_info;

            $magento_shop = $magentoClientInfo['magento_shop'];
            $admin_url = $magentoClientInfo['admin_url'];
            $magento_2_access_token = $magentoClientInfo['magento_2_access_token'];
            $magento_country = $magentoClientInfo['magento_country'];


            $access_token = array("Authorization: Bearer $magento_2_access_token");


            $product =  Product::findOne(["id"=>$prodcut_id]);
            $connection = \Yii::$app->db;
            $model = $connection->createCommand("SELECT connection_category_id FROM category, product_category WHERE product_category.product_id = $prodcut_id AND category.`id` = product_category.category_id");
            $categories = $model->queryAll();
            $sampleProductData = array(
                'sku'               => $product->sku,
                'name'              => $product->name,
                'visibility'        => 4, /*'catalog',*/
                'type_id'           => 'simple',
                'price'             => number_format((float)$conversion_rate * $product->price, 2, '.', ''),
                'status'            => $product->status==Product::STATUS_ACTIVE?1:0,
                'attribute_set_id'  => 4,
                'weight'            => $product->weight,
                'custom_attributes' => array(
                    array( 'attribute_code' => 'category_ids', 'value' => $categories ),
                    array( 'attribute_code' => 'description', 'value' => $product->description ),
                ),
                'extension_attributes' => array(
                    "stock_item"=>array(
                        "qty"=>$product->stock_quantity,
                        "is_in_stock"=>true,
                    ),

                )
            );
            $productData = json_encode(array('product' => $sampleProductData));
            $setHaders = array('Content-Type:application/json','Authorization:Bearer '.$magento_2_access_token);

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $magento_shop . "index.php/rest/V1/products");
            curl_setopt($ch,CURLOPT_POSTFIELDS, $productData);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                return json_encode(array(
                    'success' => false,
                    'product_id' => -1,
                    'user_connection_id' => $user_connection_id,
                    'message' => "Connection Error!"
                ));
            } else {
                $response_data = json_decode($response);
                if (isset($response_data->id)) {
                    return json_encode(array(
                        'success' => true,
                        'product_id' => $response_data->id,
                        'user_connection_id' => $user_connection_id,
                        'message' => "Product has been updated!"
                    ));
                } else {
                    return json_encode(array(
                        'success' => false,
                        'product_id' => -1,
                        'user_connection_id' => $user_connection_id,
                        'message' => "Unknown Request!"
                    ));
                }
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
        $product = ProductConnection::findOne(['product_id' =>$prodcut_id, "user_connection_id" =>$user_connection_id]);
        $store_magento= UserConnection::findOne(['id' => $user_connection_id]);

        if (!empty($store_magento) && !empty($product)) {
            $magentoClientInfo = $store_magento->connection_info;
            $magento_shop = $magentoClientInfo['magento_shop'];
            $magento_2_access_token = $magentoClientInfo['magento_2_access_token'];

            $setHaders = array('Content-Type:application/json','Authorization:Bearer '.$magento_2_access_token);

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $magento_shop . "index.php/rest/V1/products/{$product->connection_product_id}");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $setHaders);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }
}