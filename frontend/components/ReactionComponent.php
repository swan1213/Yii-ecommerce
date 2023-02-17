<?php
namespace frontend\components;


use common\models\Category;
use common\models\Customer;
use common\models\Order;
use common\models\Product;
use common\models\ProductCategory;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use yii\base\Component;


class ReactionComponent extends Component
{
    const timeOutLimit = 1;
    const errorTimeOutLimit = 20;
    const storeName = "reaction";

    public static function createReactionClient($user_connection_id){

        $store_reaction = UserConnection::findOne(['id' => $user_connection_id]);

        $reactionClientInfo = $store_reaction->connection_info;

        if ( !empty($reactionClientInfo) ){
            $reactionClient = new ReactionSimpleRestClient($reactionClientInfo);

            if ( $reactionClient->checkValidate() ){
                return $reactionClient;
            }
        }


        return null;

    }

    public static function importShop($user_connection_id, $shopDetails){

        $user_Reaction_connection = UserConnectionDetails::findOne(
            [
                'user_connection_id' => $user_connection_id
            ]
        );


        if (empty($user_Reaction_connection)) {
            $user_Reaction_connection = new UserConnectionDetails();
            $user_Reaction_connection->user_connection_id = $user_connection_id;
        }

        $user_Reaction_connection->store_name = $shopDetails['shop_name'];
        $user_Reaction_connection->store_url = $shopDetails['reaction_url'];
        $user_Reaction_connection->country = $shopDetails['country_name'];
        $user_Reaction_connection->country_code = $shopDetails['country_code'];
        $user_Reaction_connection->currency = $shopDetails['currency_code'];
        $user_Reaction_connection->currency_symbol = $shopDetails['currency_symbol'];

        $others_details = array(
            "shop_currency" => $shopDetails['shop_currency'],
            "shop_id" => $shopDetails['shop_id'],
            "shop_account_id" => $shopDetails['shop_account_id'],
        );


        $user_Reaction_connection->others = @json_encode($others_details);;

        $userConnectionSettings = $user_Reaction_connection->settings;

        if(empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
            $userConnectionSettings['currency'] = $user_Reaction_connection->currency;
        }

        $user_Reaction_connection->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);


        $user_Reaction_connection->save(false);

    }

    /*
     * Reaction add hooks
     */

    public static function addReactionHooks($user_connection_id) {

        $hookBaseUrl = env('SEVER_URL');

        $rc = self::createReactionClient($user_connection_id);

        $shop_update_hook = [
            "webhook" => [
                "topic" => "shop/update",
                "address" => $hookBaseUrl . "hooklistener/reaction/shop-update?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];
        $product_create_hook = [
            "webhook" => [
                "topic" => "products/create",
                "address" => $hookBaseUrl . "hooklistener/reaction/product-create?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $product_delete_hook = [
            "webhook" => [
                "topic" => "products/delete",
                "address" => $hookBaseUrl . "hooklistener/reaction/product-delete?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $product_update_hook = [
            "webhook" => [
                "topic" => "products/update",
                "address" => $hookBaseUrl . "hooklistener/reaction/product-update?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $order_create_hook = [
            "webhook" => [
                "topic" => "orders/create",
                "address" => $hookBaseUrl . "hooklistener/reaction/order-create?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $order_update_hook = [
            "webhook" => [
                "topic" => "orders/updated",
                "address" => $hookBaseUrl . "hooklistener/reaction/order-update?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];

        $customer_create_hook = [
            "webhook" => [
                "topic" => "customers/create",
                "address" => $hookBaseUrl . "hooklistener/reaction/customer-create?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];
        $customer_update_hook = [
            "webhook" => [
                "topic" => "customers/update",
                "address" => $hookBaseUrl . "hooklistener/reaction/customer-update?id=" . $user_connection_id . "&action=".self::storeName,
                "format" => "json"
            ]
        ];


    }

    public static function reactionProductImport($user_connection_id, $conversion_rate = 1) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $rc = self::createReactionClient($user_connection_id);

        $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);


        $importUser = $userConnectionModel->user;
        $store_connection_details = $userConnectionModel->userConnectionDetails;
        $user_id = $importUser->id;

        $store_country_code = $store_connection_details->country_code;
        $store_currency_code = $store_connection_details->currency;
        $reaction_shop = $rc->getStoreUrl();

        if ( $rc->checkValidate() ){

            $response_result = $rc->call('GET', 'publications/Products');

            if ( isset($response_result['Products']) ){

                $product_result = $response_result['Products'];
                $product_image_result = $response_result['cfs.Media.filerecord'];

                foreach ($product_result as $_product) {

                    $_product['reaction_shop'] = $reaction_shop;
                    $_product['user_id'] = $user_id;
                    $_product['store_currency_code'] = $store_currency_code;
                    $_product['store_country_code'] = $store_country_code;
                    $_product['user_connection_id'] = $user_connection_id;
                    $_product['conversion_rate'] = $conversion_rate;

                    $connection_product_id = $_product['_id'];
                    $product_images = self::reactionProductImageById($product_image_result, $connection_product_id);


                    ReactionComponent::reactionUpsertProduct($_product, $product_images);

                }

            }


        }
    }

    public static function reactionProductImageById($productImages, $connection_product_id){

        $productImages = array();



        return $productImages;

    }

    public static function reactionUpsertProduct($_product, $product_images){

        $reaction_shop = $_product['reaction_shop'];
        $user_id = $_product['user_id'];
        $store_currency_code = $_product['store_currency_code'];
        $store_country_code = $_product['store_country_code'];
        $user_connection_id = $_product['user_connection_id'];
        $conversion_rate = $_product['conversion_rate'];


        $product_id = $_product['_id'];
        $title = $_product['title'];
        $description = $_product['body_html'];
        $product_handle = $_product['handle'];
        $created_date = date('Y-m-d h:i:s', strtotime($_product['createdAt']));
        $updated_date = date('Y-m-d h:i:s', strtotime($_product['updatedAt']));
        $product_url = $reaction_shop . 'product/' . $product_handle;


        $product_type = $_product['product_type'];
        $product_options = $_product['options'];
        $published_at = $_product['published_at'];
        /* Fields which are required but not avaialable @Reaction */
        $p_ean = '';
        $p_jan = '';
        $p_isbn = '';
        $p_mpn = '';
        $variants_barcode = '';
        $product_variants = $_product['variants'];

        $product_images = $_product['images'];

        $product_image_data = array();
        foreach ($product_images as $_image) {
            $image_id = $_image['id'];
            $image_src = $_image['src'];
            $image_position = $_image['position'];
            //$image_variant_id_array = $_image['variant_ids'];
            $default_img_id = $_product['image']['id'];
            if ($default_img_id == $image_id) {
                $base_image = $_product['image']['src'];
            }
            $product_image_data[] = array(
                'connection_image_id' => $image_id,
                'image_url' => $image_src,
                'label' => '',
                'position' => $image_position,
                'base_img' => $base_image,
            );
        }

        $product_quantity = 0;

        $options_set_data = [];
        foreach ($product_options as $eachOption){

            $eachOptionValues = [];
            foreach($eachOption['values'] as $eachOptionValue){
                $eachOptionValues[] = [
                    'label' => '',
                    'value' => $eachOptionValue
                ];
            }
            $oneOptions_set_data = [
                'name' => $eachOption['name'],
                'values' => $eachOptionValues
            ];
            $options_set_data[] = $oneOptions_set_data;
        }


        $variants_data = [];
        foreach ($product_variants as $_variants) {
            $variants_id = $_variants['id'];
            $variants_price = $_variants['price'];

            $variant_sku = ($_variants['sku'] == '') ? '' : $_variants['sku'];
            $variants_barcode = !empty($_variants['barcode'])?$_variants['barcode']:'';

            $variants_qty = $_variants['inventory_quantity'];
            $product_quantity += intval($_variants['inventory_quantity']);
            if ($product_quantity > 0) {
                $stock_status = 1;
            } else {
                $stock_status = 0;
            }
            $variants_weight = $_variants['weight'];
            $variants_created_at = $_variants['created_at'];
            $variants_updated_at = $_variants['updated_at'];
            //$variants_title_value = $_variants['title'];

            $variants_options = [];
            if ( isset($_variants['option1']) && !empty($_variants['option1']) ){
                $value_data = [
                    'label' => '',
                    'value' => $_variants['option1']
                ];
                $oneVariantOption = [
                    'name' => $options_set_data[0]['name'],
                    'value' => $value_data
                ];
                $variants_options[] = $oneVariantOption;
            }
            if ( isset($_variants['option2']) && !empty($_variants['option2']) ){
                $value_data = [
                    'label' => '',
                    'value' => $_variants['option2']
                ];
                $oneVariantOption = [
                    'name' => $options_set_data[1]['name'],
                    'value' => $value_data
                ];
                $variants_options[] = $oneVariantOption;

            }
            if ( isset($_variants['option3']) && !empty($_variants['option3']) ){
                $value_data = [
                    'label' => '',
                    'value' => $_variants['option3']
                ];
                $oneVariantOption = [
                    'name' => $options_set_data[2]['name'],
                    'value' => $value_data
                ];
                $variants_options[] = $oneVariantOption;

            }

            $oneVariantData = [
                'connection_variation_id' => $variants_id,
                'sku_key' => 'sku',
                'sku_value' => $variant_sku,
                'inventory_key' => 'inventory_quantity',
                'inventory_value' => $variants_qty,
                'price_key' => 'price',
                'price_value' => $variants_price,
                'weight_key' => 'weight',
                'weight_value' => $variants_weight,
                'upc' => $variants_barcode,
                'options' => $variants_options,
                'created_at' => $variants_created_at, // Variant created at date format date('Y-m-d H:i:s')
                'updated_at' => $variants_updated_at, // Variant updated at date format date('Y-m-d H:i:s')
            ];
            $variants_data[] = $oneVariantData;
        }

        $product_data = [
            'user_id' => $user_id, // Elliot user id,
            'name' => $title, // Product name,
            'sku' => $variants_data[0]['sku_value'], // Product SKU,
            'url' => $product_url, // Product url if null give blank value,
            'upc' => $variants_data[0]['upc'], // Product upc if any,
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
            'weight' => $variants_data[0]['weight_value'], // Product weight if null give blank value,
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
            'price' => $variants_data[0]['price_value'], // Porduct price,
            'sales_price' => $variants_data[0]['price_value'], // Product sale price if null give Product price value,
            'schedule_sales_date' => null,
            'status' => isset($published_at)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
            'published' => isset($published_at)?Product::PRODUCT_PUBLISHED_YES:Product::PRODUCT_PUBLISHED_NO,
            'permanent_hidden' => Product::STATUS_NO,
            'user_connection_id' => $user_connection_id,
            'connection_product_id' => $product_id, // Stores/Channel product ID,
            'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s'),
            'updated_at' => $updated_date, // Product updated at date format date('Y-m-d H:i:s'),
            'type' => $product_type, // Product Type
            'images' => $product_image_data, // Product images data
            'variations' => $variants_data,
            'options_set' => $options_set_data,
            'websites' => array(), //This is for only magento give only and blank array
            'conversion_rate' => $conversion_rate,
            'categories' => array(), // Product categroy array. If null give a blank array
        ];

        if ( !empty($product_data) ) {
            Product::productImportingCommon($product_data);
        }
    }

    public static function reactionUpsertCustomer($_customer){

        $user_id = $_customer['user_id'];
        $user_connection_id = $_customer['user_connection_id'];


        $customer_id = $_customer['id'];
        $customer_email = isset($_customer['email']) ? $_customer['email'] : '';
        $customer_f_name = isset($_customer['first_name']) ? $_customer['first_name'] : '';
        $customer_l_name = isset($_customer['last_name']) ? $_customer['last_name'] : '';
        $customer_create_date = isset($_customer['created_at']) ? $_customer['created_at'] : time();
        $customer_update_date = isset($_customer['updated_at']) ? $_customer['updated_at'] : time();
        $cus_phone = isset($_customer['phone']) ? $_customer['phone'] : '';

        $addr_fname = $addr_lname = $company = $add_1 = $add_2 = $city = $state = $country = $zip = $add_phone = $country_code = '';
        if (array_key_exists('default_address', $_customer)) {
            $addr_fname = isset($_customer['default_address']['first_name']) ? $_customer['default_address']['first_name'] : '';
            $addr_lname = isset($_customer['default_address']['last_name']) ? $_customer['default_address']['last_name'] : '';
            $company = isset($_customer['default_address']['company']) ? $_customer['default_address']['company'] : '';
            $add_1 = isset($_customer['default_address']['address1']) ? $_customer['default_address']['address1'] : '';
            $add_2 = isset($_customer['default_address']['address2']) ? $_customer['default_address']['address2'] : '';
            $city = isset($_customer['default_address']['city']) ? $_customer['default_address']['city'] : '';
            $state = isset($_customer['default_address']['province']) ? $_customer['default_address']['province'] : '';
            $country = isset($_customer['default_address']['country']) ? $_customer['default_address']['country'] : '';
            $country_code = isset($_customer['default_address']['country_code']) ? $_customer['default_address']['country_code'] : '';
            $zip = isset($_customer['default_address']['zip']) ? $_customer['default_address']['zip'] : '';
            $add_phone = isset($_customer['default_address']['phone']) ? $_customer['default_address']['phone'] : '';
        }

        $customer_data = array(
            'connection_customerId' => $customer_id,
            'user_id' => $user_id,
            'first_name' => $customer_f_name,
            'last_name' => $customer_l_name,
            'email' => $customer_email,
            'phone' => $cus_phone,
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
     * Reaction Customer Import
     */
    public static function reactionCustomerImport($user_connection_id) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $rc = self::createReactionClient($user_connection_id);

        $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

        $importUser = $userConnectionModel->user;
        $user_id = $importUser->id;


        try {
            $total_customer_count = $rc->call('GET', '/admin/customers/count.json');
            $call_left = $rc->callsLeft();
            if ($call_left <= 8) {
                sleep(self::timeOutLimit);
            }
            $count = ceil($total_customer_count / 250);
            for ($i = 1; $i <= $count; $i++) {
                $customer_result = $rc->call('GET', '/admin/customers.json?page=' . $i . '&limit=250');
                $call_left = $rc->callsLeft();
                if ($call_left <= 8) {
                    sleep(self::timeOutLimit);
                }

                foreach ($customer_result as $_customer) {

                    $_customer['user_id'] = $user_id;
                    $_customer['user_connection_id'] = $user_connection_id;

                    self::reactionUpsertCustomer($_customer);
                }
            }
        } catch (ReactionApiException $reactionEx) {
            $msg = $reactionEx->getMessage();
            sleep(self::errorTimeOutLimit);
        }
    }

    public static function reactionOrderImport($user_connection_id, $conversion_rate = 1) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $rc = self::createReactionClient($user_connection_id);

        $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

        $importUser = $userConnectionModel->user;
        $store_connection_details = $userConnectionModel->userConnectionDetails;
        $user_id = $importUser->id;

        $store_currency_code = $store_connection_details->currency;
        $store_country_code = $store_connection_details->country_code;


        try {
            $total_order_count = $rc->call('GET', '/admin/orders/count.json?status=any');
            $call_left = $rc->callsLeft();
            if ($call_left <= 8) {
                sleep(self::timeOutLimit);
            }
            $count = ceil($total_order_count / 250);
            for ($i = 1; $i <= $count; $i++) {
                $order_result = $rc->call('GET', '/admin/orders.json?page=' . $i . '&limit=250&status=any');
                $call_left = $rc->callsLeft();
                if ($call_left <= 8) {
                    sleep(self::timeOutLimit);
                }

                foreach ($order_result as $_order) {

                    $_order['user_id'] = $user_id;
                    $_order['user_connection_id'] = $user_connection_id;
                    $_order['store_currency_code'] = $store_currency_code;
                    $_order['store_country_code'] = $store_country_code;
                    $_order['conversion_rate'] = $conversion_rate;

                    self::reactionUpsertOrder($_order);
                    //                else {
                    //error_log('Followig product are not imported from reaction herer the id ' . $_order['id'] . "+++++", 3, 'order_import_error.log');
                    //                }
                }
            }
        } catch (ReactionApiException $reactionApiEx) {

            $msg = $reactionApiEx->getMessage();
            sleep(self::errorTimeOutLimit);

        }
    }

    public static function reactionUpsertOrder($_order){

        $user_id = $_order['user_id'];
        $user_connection_id = $_order['user_connection_id'];
        $store_currency_code = $_order['store_currency_code'];
        $store_country_code = $_order['store_country_code'];
        $conversion_rate = $_order['conversion_rate'];


        $order_id = $_order['id'];
        $customer_email = $_order['email'];
        $customer_id = '';
        if (isset($_order['customer']['id'])) {
            $customer_id = $_order['customer']['id'];

            $_customer = $_order['customer'];
            $customer_email = isset($_customer['email']) ? $_customer['email'] : '';
            $customer_f_name = isset($_customer['first_name']) ? $_customer['first_name'] : '';
            $customer_l_name = isset($_customer['last_name']) ? $_customer['last_name'] : '';
            $customer_create_date = isset($_customer['created_at']) ? $_customer['created_at'] : time();
            $customer_update_date = isset($_customer['updated_at']) ? $_customer['updated_at'] : time();
            $cus_phone = isset($_customer['phone']) ? $_customer['phone'] : '';


            $addr_fname = $addr_lname = $company = $add_1 = $add_2 = $city = $state = $country = $zip = $add_phone = $country_code = '';
            if (array_key_exists('default_address', $_customer)) {
                $addr_fname = isset($_customer['default_address']['first_name']) ? $_customer['default_address']['first_name'] : '';
                $addr_lname = isset($_customer['default_address']['last_name']) ? $_customer['default_address']['last_name'] : '';
                $company = isset($_customer['default_address']['company']) ? $_customer['default_address']['company'] : '';
                $add_1 = isset($_customer['default_address']['address1']) ? $_customer['default_address']['address1'] : '';
                $add_2 = isset($_customer['default_address']['address2']) ? $_customer['default_address']['address2'] : '';
                $city = isset($_customer['default_address']['city']) ? $_customer['default_address']['city'] : '';
                $state = isset($_customer['default_address']['province']) ? $_customer['default_address']['province'] : '';
                $country = isset($_customer['default_address']['country']) ? $_customer['default_address']['country'] : '';
                $country_code = isset($_customer['default_address']['country_code']) ? $_customer['default_address']['country_code'] : '';
                $zip = isset($_customer['default_address']['zip']) ? $_customer['default_address']['zip'] : '';
                $add_phone = isset($_customer['default_address']['phone']) ? $_customer['default_address']['phone'] : '';
            }

            $customer_data = array(
                'connection_customerId' => $customer_id,
                'user_id' => $user_id,
                'first_name' => $customer_f_name,
                'last_name' => $customer_l_name,
                'email' => $customer_email,
                'phone' => $cus_phone,
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
        }
        if ($customer_email != '' && $customer_id != '') {

            $order_created_at = isset($_order['created_at']) ? $_order['created_at'] : time();;
            $order_updated_at = isset($_order['updated_at']) ? $_order['updated_at'] : time();;

            $payment_gateway = $_order['gateway'];
            $order_total = $_order['total_price'];
            $subtotal = $_order['subtotal_price'];
            $total_tax = $_order['total_tax'];
            $order_status = $_order['financial_status'];
            $cancelled_at = $_order['cancelled_at'];
            if ($cancelled_at != '') {
                $order_status = 'voided';
            }

            $total_discount = $_order['total_discounts'];
            $total_product_count = count($_order['line_items']);
            $base_shipping_cost = isset($_order['shipping_lines'][0]['price']) ? $_order['shipping_lines'][0]['price'] : 0;
            $refund = $_order['refunds'];
            /*
             * Refund total amount
             */
            $refund_amount = 0;
            foreach ($refund as $_refund) {
                $refund_line_items = $_refund['refund_line_items'];
                foreach ($refund_line_items as $_refund_items) {
                    $refund_amount += $_refund_items['subtotal'];
                }
            }

            /*
             * Order status change according to status
             */
            switch ($order_status) {
                case "pending":
                    $order_status_2 = 'Pending';
                    break;
                case "authorized":
                    $order_status_2 = "In Transit";
                    break;
                case "partially_paid":
                    $order_status_2 = "Completed";
                    break;
                case "paid":
                    $order_status_2 = "Completed";
                    break;
                case "partially_refunded":
                    $order_status_2 = "Refunded";
                    break;
                case "refunded":
                    $order_status_2 = "Refunded";
                    break;
                case "voided":
                    $order_status_2 = "Cancel";
                    break;
                default:
                    $order_status_2 = "Pending";
            }

            /** Order Billing Address */
            $billing_f_name = $billing_l_name = $billing_street_1 = $billing_street_2 = $billing_phone = $billing_city = $billing_zip = $billing_state = $billing_country = $billing_country_code = '';
            if (isset($_order['billing_address'])) {
                $order_billing_add = $_order['billing_address'];
                $billing_f_name = $order_billing_add['first_name'];
                $billing_l_name = $order_billing_add['last_name'];
                $billing_street_1 = $order_billing_add['address1'];
                $billing_street_2 = $order_billing_add['address2'];
                $billing_phone = $order_billing_add['phone'];
                $billing_city = $order_billing_add['city'];
                $billing_zip = $order_billing_add['zip'];
                $billing_state = $order_billing_add['province'];
                $billing_country = $order_billing_add['country'];
                $billing_country_code = $order_billing_add['country_code'];
            }

            /** Order Shipping Address */
            $shipping_f_name = $shipping_l_name = $shipping_street_1 = $shipping_street_2 = $shipping_phone = $shipping_city = $shipping_zip = $shipping_state = $shipping_country = $shipping_country_code = '';
            if (isset($_order['shipping_address'])) {
                $order_shipping_add = $_order['shipping_address'];
                $shipping_f_name = $order_shipping_add['first_name'];
                $shipping_l_name = $order_shipping_add['last_name'];
                $shipping_street_1 = $order_shipping_add['address1'];
                $shipping_street_2 = $order_shipping_add['address2'];
                $shipping_phone = $order_shipping_add['phone'];
                $shipping_city = $order_shipping_add['city'];
                $shipping_zip = $order_shipping_add['zip'];
                $shipping_state = $order_shipping_add['province'];
                $shipping_country = $order_shipping_add['country'];
                $shipping_country_code = $order_shipping_add['country_code'];
            }

            $order_items = $_order['line_items'];
            $order_product_data = array();
            foreach ($order_items as $_items) {
                $title = $_items['title'];
                $quantity = $_items['quantity'];
                $price = $_items['price'];
                $sku = '';
                if ($_items['sku'] != "") {
                    $sku = $_items['sku'];
                }
                $product_id = ($_items['product_id'] == '') ? 0 : $_items['product_id'];
                $variant_id = ($_items['variant_id'] == '') ? 0 : $_items['variant_id'];
                $product_weight = $_items['grams'];
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

            $customer_id = ($customer_id == '') ? 0 : $customer_id;

            $order_data = array(
                'user_id' => $user_id,
                'user_connection_id' => $user_connection_id,
                'connection_order_id' => $order_id,
                'status' => $order_status_2,
                'product_quantity' => $total_product_count,
                'ship_street_1' => $shipping_street_1,
                'ship_street_2' => $shipping_street_2,
                'ship_city' => $shipping_city,
                'ship_state' => $shipping_state,
                'ship_zip' => $shipping_zip,
                'ship_country' => $shipping_country,
                'ship_country_iso' => $shipping_country_code,
                'bill_street_1' => $billing_street_1,
                'bill_street_2' => $billing_street_2,
                'bill_city' => $billing_city,
                'bill_state' => $billing_state,
                'bill_country' => $billing_country,
                'bill_zip' => $billing_zip,
                'bill_country_iso' => $billing_country_code,
                'fee' => [
                    'base_shippping_cost' => $base_shipping_cost,
                    'shipping_cost_tax' => $total_tax,
                    'refunded_amount' => $refund_amount,
                    'discount_amount' => $total_discount,
                    'payment_method' => $payment_gateway,
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


    //////////////////////////////////////// speed up functions //////////////////////////////////////////////////

    public static function reactionSpeedProductImport($user_connection_id, $conversion_rate = 1) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $rc = self::createReactionClient($user_connection_id);

        $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);
        $reactionClientInfo = $userConnectionModel->connection_info;

        $importUser = $userConnectionModel->user;
        $store_connection_details = $userConnectionModel->userConnectionDetails;
        $user_id = $importUser->id;

        $store_country_code = $store_connection_details->country_code;
        $store_currency_code = $store_connection_details->currency;

        $reaction_shop = $reactionClientInfo['url'];


        $total_product_count = $rc->call('GET', '/admin/products/count.json');
        $call_left = $rc->callsLeft();
        if ($call_left > 8) {
            sleep(self::timeOutLimit);
        }
        $product_count = ceil($total_product_count / 250);
        for ($i = 1; $i <= $product_count; $i++) {
            $product_result = $rc->call('GET', '/admin/products.json?page=' . $i . '&limit=250');
            $call_left = $rc->callsLeft();
            if ($call_left > 8) {
                sleep(self::timeOutLimit);
            }
            foreach ($product_result as $_product) {

                $_product['reaction_shop'] = $reaction_shop;
                $_product['user_id'] = $user_id;
                $_product['store_currency_code'] = $store_currency_code;
                $_product['store_country_code'] = $store_country_code;
                $_product['user_connection_id'] = $user_connection_id;
                $_product['conversion_rate'] = $conversion_rate;

                ReactionComponent::reactionUpsertProduct($_product);

            }
        }
    }


    public static function reactionSpeedAssignProductToCollection($user_connection_id) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $rc = self::createReactionClient($user_connection_id);

        $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

        $importUser = $userConnectionModel->user;
        $user_id = $importUser->id;

        $total_collection_count = $rc->call('GET', '/admin/custom_collections/count.json');
        $call_left = $rc->callsLeft();
        if ($call_left <= 8) {
            sleep(self::timeOutLimit);
        }
        $count = ceil($total_collection_count / 250);
        for ($i = 1; $i <= $count; $i++) {
            $collection_result = $rc->call('GET', '/admin/custom_collections.json?page=' . $i . '&limit=250');
            $call_left = $rc->callsLeft();
            if ($call_left <= 8) {
                sleep(self::timeOutLimit);
            }
            foreach ($collection_result as $_collection) {
                $collection_id = $_collection['id'];
                $collection_title = $_collection['title'];
                $collection_description = $_collection['body_html'];
                $collection_created_at = $_collection['published_at'];
                $collection_updated_at = $_collection['updated_at'];

                $category_data = [
                    'name' => $collection_title, // Give category name
                    'description' => $collection_description, // Give category body html
                    'parent_id' => 0,
                    'user_id' => $user_id, // Give Elliot user id,
                    'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                    'connection_category_id' => $collection_id, // Give category id of Store/channels
                    'connection_parent_id' => '0', // Give Category parent id of Elliot if null then give 0
                    'created_at' => $collection_created_at, // Give Created at date if null then give current date format date('Y-m-d H:i:s')
                    'updated_at' => $collection_updated_at, // Give Updated at date if null then give current date format date('Y-m-d H:i:s')
                ];

                $categroy_id = Category::categoryImportingCommon($category_data);    // Then call store modal function and give $category data

                $collection_product_list = $rc->call('GET', '/admin/products.json?collection_id=' . $collection_id);
                $call_left = $rc->callsLeft();
                if ($call_left <= 8) {
                    sleep(self::timeOutLimit);
                }
                foreach ($collection_product_list as $product) {
                    $connection_product_id = $product['id'];
                    $created_at = $product['created_at'];

                    $checkProduct = Product::findOne([
                        'user_connection_id' => $user_connection_id,
                        'connection_product_id' => $connection_product_id
                    ]);
                    if (!empty($checkProduct)) {

                        $productCategory = ProductCategory::findOne([
                            'category_id' => $categroy_id,
                            'product_id' => $checkProduct->id
                        ]);

                        if ( empty($productCategory) ) {
                            $productCategory = new ProductCategory();
                            $productCategory->user_id = $user_id;
                            $productCategory->category_id = $categroy_id;
                            $productCategory->product_id = $checkProduct->id;
                            $productCategory->created_at = date('Y-m-d h:i:s', strtotime($created_at));
                            $productCategory->save(false);

                        }
                    }
                }
            }
        }

    }

    public static function reactionSpeedCustomerImport($user_connection_id) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $rc = self::createReactionClient($user_connection_id);

        $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

        $importUser = $userConnectionModel->user;
        $user_id = $importUser->id;


        try {
            $total_customer_count = $rc->call('GET', '/admin/customers/count.json');
            $count = ceil($total_customer_count / 250);
            for ($i = 1; $i <= $count; $i++) {

                self::reactionMultiCustomerImport($user_connection_id, 1, 8);

                $customer_result = $rc->call('GET', '/admin/customers.json?page=' . $i . '&limit=250');
                $call_left = $rc->callsLeft();
                if ($call_left <= 8) {
                    sleep(self::timeOutLimit);
                }

                foreach ($customer_result as $_customer) {

                    $_customer['user_id'] = $user_id;
                    $_customer['user_connection_id'] = $user_connection_id;

                    self::reactionUpsertCustomer($_customer);
                }
            }
        } catch (ReactionApiException $reactionEx) {
            $msg = $reactionEx->getMessage();
            sleep(self::errorTimeOutLimit);
        }
    }

    public static function reactionMultiCustomerImport($user_connection_id, $fromPage, $toPage) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $rc = self::createReactionClient($user_connection_id);

        $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

        $importUser = $userConnectionModel->user;
        $user_id = $importUser->id;


        try {

            for ($i = $fromPage; $i <= $toPage; $i++) {
                $customer_result = $rc->call('GET', '/admin/customers.json?page=' . $i . '&limit=250');
                $call_left = $rc->callsLeft();
                if ($call_left <= 8) {
                    sleep(self::timeOutLimit);
                }

                foreach ($customer_result as $_customer) {

                    $_customer['user_id'] = $user_id;
                    $_customer['user_connection_id'] = $user_connection_id;

                    self::reactionUpsertCustomer($_customer);
                }
            }
        } catch (ReactionApiException $reactionEx) {
            $msg = $reactionEx->getMessage();
            sleep(self::errorTimeOutLimit);
        }
    }

    public static function reactionSpeedOrderImport($user_connection_id, $conversion_rate = 1) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $rc = self::createReactionClient($user_connection_id);

        $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

        $importUser = $userConnectionModel->user;
        $store_connection_details = $userConnectionModel->userConnectionDetails;
        $user_id = $importUser->id;

        $store_currency_code = $store_connection_details->currency;
        $store_country_code = $store_connection_details->country_code;


        try {
            $total_order_count = $rc->call('GET', '/admin/orders/count.json?status=any');
            $call_left = $rc->callsLeft();
            if ($call_left <= 8) {
                sleep(self::timeOutLimit);
            }
            $count = ceil($total_order_count / 250);
            for ($i = 1; $i <= $count; $i++) {
                $order_result = $rc->call('GET', '/admin/orders.json?page=' . $i . '&limit=250&status=any');
                $call_left = $rc->callsLeft();
                if ($call_left <= 8) {
                    sleep(self::timeOutLimit);
                }

                foreach ($order_result as $_order) {

                    $_order['user_id'] = $user_id;
                    $_order['user_connection_id'] = $user_connection_id;
                    $_order['store_currency_code'] = $store_currency_code;
                    $_order['store_country_code'] = $store_country_code;
                    $_order['conversion_rate'] = $conversion_rate;

                    self::reactionUpsertOrder($_order);
                    //                else {
                    //error_log('Followig product are not imported from reaction herer the id ' . $_order['id'] . "+++++", 3, 'order_import_error.log');
                    //                }
                }
            }
        } catch (ReactionApiException $reactionApiEx) {

            $msg = $reactionApiEx->getMessage();
            sleep(self::errorTimeOutLimit);

        }
    }


}