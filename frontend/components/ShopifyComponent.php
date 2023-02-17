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
use common\models\ProductImage;
use common\models\ProductVariation;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use common\models\Variation;
use common\models\VariationItem;
use common\models\VariationSet;
use common\models\VariationValue;
use yii\base\Component;
use frontend\components\ElliShopifyClient as Shopify;
use common\components\order\OrderStatus;

class ShopifyComponent extends Component
{
    const timeOutLimit = 20;
    const errorTimeOutLimit = 30;
    const storeName = "shopify";
    const pageLimit = 8;

    public static function createShopifyClient($user_connection_id){

        $store_shopify = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($store_shopify) ){

            $shopifyClientInfo = $store_shopify->connection_info;

            if( isset($shopifyClientInfo['url']) && isset($shopifyClientInfo['api_key']) && isset($shopifyClientInfo['key_password']) && isset($shopifyClientInfo['shared_secret'] ) ){

                $shopify_shop = $shopifyClientInfo['url'];
                $shopify_api_key = $shopifyClientInfo['api_key'];
                $shopify_api_password = $shopifyClientInfo['key_password'];
                $shopify_shared_secret = $shopifyClientInfo['shared_secret'];

                $shopifyClient = new Shopify($shopify_shop, $shopify_api_password, $shopify_api_key, $shopify_shared_secret);

                return $shopifyClient;

            }


        }

        return null;
    }

    public static function importShop($user_connection_id, $shopDatails, $countryInfo=array()){

        $store_shopName = $shopDatails['name'];
        $store_email = $shopDatails['email'];
        $store_url = $shopDatails['myshopify_domain'];
        $api_store_country = $shopDatails['country_name'];
        $api_store_country_code = $shopDatails['country_code'];
        $api_store_currency = $shopDatails['currency'];
        $store_owner = $shopDatails['shop_owner'];
        $store_add = $shopDatails['address1'];
        $store_city = $shopDatails['city'];
        $store_province = $shopDatails['province'];
        $store_zip = $shopDatails['zip'];
        $store_phone = $shopDatails['phone'];

        if ( !empty($countryInfo) ){

            $store_country = $countryInfo->name;
            $store_country_code = $countryInfo->sortname;
            $store_currency = $countryInfo->currency_code;
            $store_currency_symbol = $countryInfo->currency_symbol;

        } else {

            if ( !empty($api_store_country_code) ){
                $countryInfo = Country::countryInfoFromCode($api_store_country_code);

                $store_country = $countryInfo->name;
                $store_country_code = $countryInfo->sortname;
                $store_currency = $countryInfo->currency_code;
                $store_currency_symbol = $countryInfo->currency_symbol;

            }
        }



        $others_details = array(
            "shop_owner" => $store_owner,
            "email" => $store_email,
            "address" => $store_add,
            "city" => $store_city,
            "province" => $store_province,
            "shop_country" => $api_store_country,
            "shop_country_code" => $api_store_country_code,
            "post_code" => $store_zip,
            "phone" => $store_phone,
            "shop_currency" => $api_store_currency,
        );

        $others_details = json_encode($others_details);
        $user_Shopify_connection = UserConnectionDetails::findOne(
            [
                'user_connection_id' => $user_connection_id
            ]
        );
        if (empty($user_Shopify_connection)) {

            $user_Shopify_connection = new UserConnectionDetails();

            $user_Shopify_connection->user_connection_id = $user_connection_id;

            $user_Shopify_connection->country = $store_country;
            $user_Shopify_connection->country_code = $store_country_code;
            $user_Shopify_connection->currency = $store_currency;
            $user_Shopify_connection->currency_symbol = $store_currency_symbol;

        }

        $user_Shopify_connection->store_name = $store_shopName;
        $user_Shopify_connection->store_url = $store_url;
        $user_Shopify_connection->others = $others_details;

        $userConnectionSettings = $user_Shopify_connection->settings;

        if(empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
            $userConnectionSettings['currency'] = $user_Shopify_connection->currency;
        }

        $user_Shopify_connection->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);


        $user_Shopify_connection->save(false);

    }

    /*
     * Shopify add hooks
     */

    public static function addShopifyHooks($user_connection_id) {

        $hookBaseUrl = env('SEVER_URL');

        $sc = self::createShopifyClient($user_connection_id);

        if ( !empty($sc) ){

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

            if (!in_array($shop_update_hook['webhook']['address'], $myhooks)) {
                $hooks_add = $sc->call('POST', '/admin/webhooks.json', $shop_update_hook);
            }

            if (!in_array($product_create_hook['webhook']['address'], $myhooks)) {
                $hooks_add = $sc->call('POST', '/admin/webhooks.json', $product_create_hook);
            }
            if (!in_array($product_delete_hook['webhook']['address'], $myhooks)) {
                $hooks_add = $sc->call('POST', '/admin/webhooks.json', $product_delete_hook);
            }
            if (!in_array($product_update_hook['webhook']['address'], $myhooks)) {
                $hooks_add = $sc->call('POST', '/admin/webhooks.json', $product_update_hook);
            }
            if (!in_array($order_create_hook['webhook']['address'], $myhooks)) {
                $hooks_add = $sc->call('POST', '/admin/webhooks.json', $order_create_hook);
            }
            if (!in_array($order_update_hook['webhook']['address'], $myhooks)) {
                $hooks_add = $sc->call('POST', '/admin/webhooks.json', $order_update_hook);
            }

            if (!in_array($customer_create_hook['webhook']['address'], $myhooks)) {
                $hooks_add = $sc->call('POST', '/admin/webhooks.json', $customer_create_hook);
            }
            if (!in_array($customer_update_hook['webhook']['address'], $myhooks)) {
                $hooks_add = $sc->call('POST', '/admin/webhooks.json', $customer_update_hook);
            }


        }


    }

    public static function removeShopifyHooks($user_connection_id) {

        $hookBaseUrl = env('SEVER_URL');

        $sc = self::createShopifyClient($user_connection_id);

        if ( !empty($sc) ){

            $shop_update_hook = $hookBaseUrl . "hooklistener/shopify/shop-update?id=" . $user_connection_id . "&action=".self::storeName;

            $product_create_hook = $hookBaseUrl . "hooklistener/shopify/product-create?id=" . $user_connection_id . "&action=".self::storeName;
            $product_delete_hook = $hookBaseUrl . "hooklistener/shopify/product-delete?id=" . $user_connection_id . "&action=".self::storeName;
            $product_update_hook = $hookBaseUrl . "hooklistener/shopify/product-update?id=" . $user_connection_id . "&action=".self::storeName;

            $order_create_hook = $hookBaseUrl . "hooklistener/shopify/order-create?id=" . $user_connection_id . "&action=".self::storeName;
            $order_update_hook = $hookBaseUrl . "hooklistener/shopify/order-update?id=" . $user_connection_id . "&action=".self::storeName;

            $customer_create_hook = $hookBaseUrl . "hooklistener/shopify/customer-create?id=" . $user_connection_id . "&action=".self::storeName;
            $customer_update_hook = $hookBaseUrl . "hooklistener/shopify/customer-update?id=" . $user_connection_id . "&action=".self::storeName;


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

                    $exist_hook_url = $exist_hooks['address'];

                    if ( $exist_hook_url === $shop_update_hook  ) {
                        $remove_hook_id = $exist_hooks['id'];

                        $remove_call_url = '/admin/webhooks/'. $remove_hook_id .'.json';

                        $hooks_remove = $sc->call('DELETE', $remove_call_url);
                    }

                    if ( $exist_hook_url === $product_create_hook  ) {
                        $remove_hook_id = $exist_hooks['id'];

                        $remove_call_url = '/admin/webhooks/'. $remove_hook_id .'.json';

                        $hooks_remove = $sc->call('DELETE', $remove_call_url);
                    }

                    if ( $exist_hook_url === $product_delete_hook  ) {
                        $remove_hook_id = $exist_hooks['id'];

                        $remove_call_url = '/admin/webhooks/'. $remove_hook_id .'.json';

                        $hooks_remove = $sc->call('DELETE', $remove_call_url);
                    }

                    if ( $exist_hook_url === $product_update_hook  ) {
                        $remove_hook_id = $exist_hooks['id'];

                        $remove_call_url = '/admin/webhooks/'. $remove_hook_id .'.json';

                        $hooks_remove = $sc->call('DELETE', $remove_call_url);
                    }

                    if ( $exist_hook_url === $order_create_hook  ) {
                        $remove_hook_id = $exist_hooks['id'];

                        $remove_call_url = '/admin/webhooks/'. $remove_hook_id .'.json';

                        $hooks_remove = $sc->call('DELETE', $remove_call_url);
                    }

                    if ( $exist_hook_url === $order_update_hook  ) {
                        $remove_hook_id = $exist_hooks['id'];

                        $remove_call_url = '/admin/webhooks/'. $remove_hook_id .'.json';

                        $hooks_remove = $sc->call('DELETE', $remove_call_url);
                    }

                    if ( $exist_hook_url === $customer_create_hook  ) {
                        $remove_hook_id = $exist_hooks['id'];

                        $remove_call_url = '/admin/webhooks/'. $remove_hook_id .'.json';

                        $hooks_remove = $sc->call('DELETE', $remove_call_url);
                    }

                    if ( $exist_hook_url === $customer_update_hook  ) {
                        $remove_hook_id = $exist_hooks['id'];

                        $remove_call_url = '/admin/webhooks/'. $remove_hook_id .'.json';

                        $hooks_remove = $sc->call('DELETE', $remove_call_url);
                    }


                }
            }



        }


    }

    public static function shopifyProductImport($user_connection_id, $conversion_rate = 1) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $sc = self::createShopifyClient($user_connection_id);

        if ( !empty($sc) ){

            $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);
            $shopifyClientInfo = $userConnectionModel->connection_info;

            $importUser = $userConnectionModel->user;
            $store_connection_details = $userConnectionModel->userConnectionDetails;
            $user_id = $importUser->id;

            $store_country_code = $store_connection_details->country_code;
            $store_currency_code = $store_connection_details->currency;

            $shopify_shop = $shopifyClientInfo['url'];


            $total_product_count = $sc->call('GET', '/admin/products/count.json');
            $call_left = $sc->callsLeft();
            if ($call_left > 8) {
                sleep(self::timeOutLimit);
            }
            $product_count = ceil($total_product_count / 250);
            for ($i = 1; $i <= $product_count; $i++) {
                $product_result = $sc->call('GET', '/admin/products.json?page=' . $i . '&limit=250');
                $call_left = $sc->callsLeft();
                if ($call_left > 8) {
                    sleep(self::timeOutLimit);
                }
                foreach ($product_result as $_product) {

                    $_product['shopify_shop'] = $shopify_shop;
                    $_product['user_id'] = $user_id;
                    $_product['store_currency_code'] = $store_currency_code;
                    $_product['store_country_code'] = $store_country_code;
                    $_product['user_connection_id'] = $user_connection_id;
                    $_product['conversion_rate'] = $conversion_rate;

                    ShopifyComponent::shopifyUpsertProduct($_product);

                }
            }

        }

    }

    public static function shopifyUpsertProduct($_product){

        $shopify_shop = $_product['shopify_shop'];
        $user_id = $_product['user_id'];
        $store_currency_code = $_product['store_currency_code'];
        $store_country_code = $_product['store_country_code'];
        $user_connection_id = $_product['user_connection_id'];
        $conversion_rate = $_product['conversion_rate'];


        $product_id = $_product['id'];
        $title = $_product['title'];
        $description = $_product['body_html'];
        $product_handle = $_product['handle'];
        $product_type = $_product['product_type'];
        $product_url = 'https://' . $shopify_shop . '/products/' . $product_handle;
        $product_options = $_product['options'];
        $created_date = date('Y-m-d h:i:s', strtotime($_product['created_at']));
        $updated_date = date('Y-m-d h:i:s', strtotime($_product['updated_at']));
        $published_at = $_product['published_at'];
        /* Fields which are required but not avaialable @Shopify */
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
            $variants_sales_price = isset($_variants['compare_at_price'])?$_variants['compare_at_price']:$variants_price;

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
                'sales_price' => $variants_sales_price,
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
            'sales_price' => $variants_data[0]['sales_price'], // Product compare at price if null give Product price value,
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


    public static function shopifyAssignProductToCollection($user_connection_id) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $sc = self::createShopifyClient($user_connection_id);

        if ( !empty($sc) ){

            $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

            $importUser = $userConnectionModel->user;
            $user_id = $importUser->id;

            $total_collection_count = $sc->call('GET', '/admin/custom_collections/count.json');
            $call_left = $sc->callsLeft();
            if ($call_left <= 8) {
                sleep(self::timeOutLimit);
            }
            $count = ceil($total_collection_count / 250);
            for ($i = 1; $i <= $count; $i++) {
                $collection_result = $sc->call('GET', '/admin/custom_collections.json?page=' . $i . '&limit=250');
                $call_left = $sc->callsLeft();
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
                        'user_connection_id' => $user_connection_id, // Give Channel/Store connection id
                        'connection_category_id' => $collection_id, // Give category id of Store/channels
                        'connection_parent_id' => '0', // Give Category parent unique id of Store if null then give 0
                        'created_at' => $collection_created_at, // Give Created at date if null then give current date format date('Y-m-d H:i:s')
                        'updated_at' => $collection_updated_at, // Give Updated at date if null then give current date format date('Y-m-d H:i:s')
                    ];

                    $categroy_id = Category::categoryImportingCommon($category_data);    // Then call store modal function and give $category data

                    $collection_product_list = $sc->call('GET', '/admin/products.json?collection_id=' . $collection_id);
                    $call_left = $sc->callsLeft();
                    if ($call_left <= 8) {
                        sleep(self::timeOutLimit);
                    }
                    foreach ($collection_product_list as $product) {
                        $connection_product_id = $product['id'];
                        $created_at = $product['created_at'];

                        $checkProduct = ProductConnection::findOne([
                            'user_connection_id' => $user_connection_id,
                            'connection_product_id' => $connection_product_id
                        ]);
                        if (!empty($checkProduct)) {

                            $productCategory = ProductCategory::findOne([
                                'user_id' => $user_id,
                                'category_id' => $categroy_id,
                                'product_id' => $checkProduct->product_id
                            ]);

                            if ( empty($productCategory) ) {
                                $productCategory = new ProductCategory();
                                $productCategory->user_id = $user_id;
                                $productCategory->category_id = $categroy_id;
                                $productCategory->product_id = $checkProduct->product_id;
                                $productCategory->created_at = date('Y-m-d h:i:s', strtotime($created_at));
                                $productCategory->save(false);

                            }
                        }
                    }
                }
            }

        }



    }

    public static function shopifyAssignCollectionByProduct($user_connection_id, $shopify_product_id) {

        $sc = self::createShopifyClient($user_connection_id);

        if ( !empty($sc) ){

            $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

            $importUser = $userConnectionModel->user;
            $user_id = $importUser->id;


            $collection_result = $sc->call('GET', '/admin/custom_collections.json?product_id=' . $shopify_product_id);

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

                $connection_product_id = $shopify_product_id;

                $checkProduct = ProductConnection::findOne([
                    'user_connection_id' => $user_connection_id,
                    'connection_product_id' => $connection_product_id
                ]);
                if (!empty($checkProduct)) {

                    $created_at = $checkProduct['created_at'];

                    $productCategory = ProductCategory::findOne([
                        'user_id' => $user_id,
                        'category_id' => $categroy_id,
                        'product_id' => $checkProduct->product_id
                    ]);

                    if ( empty($productCategory) ) {
                        $productCategory = new ProductCategory();
                        $productCategory->user_id = $user_id;
                        $productCategory->category_id = $categroy_id;
                        $productCategory->product_id = $checkProduct->product_id;
                        $productCategory->created_at = date('Y-m-d h:i:s', strtotime($created_at));
                        $productCategory->save(false);

                    }
                }
            }

        }



    }


    public static function shopifyUpsertCustomer($_customer){

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

    public static function shopifyInsertCustomer($_customer){

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
        Customer::customerInsertCommon($customer_data);

    }
    /**
     * Shopify Customer Import
     */
    public static function shopifyCustomerImport($user_connection_id) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $sc = self::createShopifyClient($user_connection_id);

        if ( !empty($sc) ) {

            $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

            $importUser = $userConnectionModel->user;
            $user_id = $importUser->id;


            try {
                $total_customer_count = $sc->call('GET', '/admin/customers/count.json');
                $call_left = $sc->callsLeft();
                if ($call_left <= 8) {
                    sleep(self::timeOutLimit);
                }
                $count = ceil($total_customer_count / 250);
                for ($i = 1; $i <= $count; $i++) {
                    $customer_result = $sc->call('GET', '/admin/customers.json?page=' . $i . '&limit=250');
                    $call_left = $sc->callsLeft();
                    if ($call_left <= 8) {
                        sleep(self::timeOutLimit);
                    }

                    foreach ($customer_result as $_customer) {

                        $_customer['user_id'] = $user_id;
                        $_customer['user_connection_id'] = $user_connection_id;

                        self::shopifyUpsertCustomer($_customer);
                    }
                }
            } catch (ShopifyApiException $shopifyEx) {
                $msg = $shopifyEx->getMessage();
                sleep(self::errorTimeOutLimit);
            }

        }


    }

    public static function shopifyOrderImport($user_connection_id, $conversion_rate = 1) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $sc = self::createShopifyClient($user_connection_id);

        if ( !empty($sc) ) {

            $userConnectionModel = UserConnection::findOne(['id' => $user_connection_id]);

            $importUser = $userConnectionModel->user;
            $store_connection_details = $userConnectionModel->userConnectionDetails;
            $user_id = $importUser->id;

            $store_currency_code = $store_connection_details->currency;
            $store_country_code = $store_connection_details->country_code;


            try {
                $total_order_count = $sc->call('GET', '/admin/orders/count.json?status=any');
                $call_left = $sc->callsLeft();
                if ($call_left <= 8) {
                    sleep(self::timeOutLimit);
                }
                $count = ceil($total_order_count / 250);
                for ($i = 1; $i <= $count; $i++) {
                    $order_result = $sc->call('GET', '/admin/orders.json?page=' . $i . '&limit=250&status=any');
                    $call_left = $sc->callsLeft();
                    if ($call_left <= 8) {
                        sleep(self::timeOutLimit);
                    }

                    foreach ($order_result as $_order) {

                        $_order['user_id'] = $user_id;
                        $_order['user_connection_id'] = $user_connection_id;
                        $_order['store_currency_code'] = $store_currency_code;
                        $_order['store_country_code'] = $store_country_code;
                        $_order['conversion_rate'] = $conversion_rate;

                        self::shopifyUpsertOrder($_order);

                    }
                }
            } catch (ShopifyApiException $shopifyApiEx) {

                $msg = $shopifyApiEx->getMessage();
                sleep(self::errorTimeOutLimit);

            }

        }


    }

    public static function shopifyUpsertOrder($_order){

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

            $customer_id = ($customer_id == '') ? 0 : $customer_id;

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
            //$total_product_count = count($_order['line_items']);
            $total_product_count = 0;
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
                    $order_status_2 = OrderStatus::PENDING;
                    break;
                case "authorized":
                    $order_status_2 = OrderStatus::IN_TRANSIT;
                    break;
                case "partially_paid":
                    $order_status_2 = OrderStatus::COMPLETED;
                    break;
                case "paid":
                    $order_status_2 = OrderStatus::COMPLETED;
                    break;
                case "partially_refunded":
                    $order_status_2 = OrderStatus::REFUNDED;
                    break;
                case "refunded":
                    $order_status_2 = OrderStatus::REFUNDED;
                    break;
                case "voided":
                    $order_status_2 = OrderStatus::CANCEL;
                    break;
                default:
                    $order_status_2 = OrderStatus::PENDING;
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

                $total_product_count += $quantity;
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

            $order_data = array(
                'user_id' => $user_id,
                'user_connection_id' => $user_connection_id,
                'connection_order_id' => $order_id,
                'status' => $order_status_2,
                'product_quantity' => $total_product_count,
                'ship_fname' => $shipping_f_name,
                'ship_lname' => $shipping_l_name,
                'ship_phone' => $shipping_phone,
                'ship_company' => '',
                'ship_street_1' => $shipping_street_1,
                'ship_street_2' => $shipping_street_2,
                'ship_city' => $shipping_city,
                'ship_state' => $shipping_state,
                'ship_zip' => $shipping_zip,
                'ship_country' => $shipping_country,
                'ship_country_iso' => $shipping_country_code,
                'bill_fname' => $billing_f_name,
                'bill_lname' => $billing_l_name,
                'bill_phone' => $billing_phone,
                'bill_company' => '',
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

            if ( !empty($order_data) ){

                Order::orderImportingCommon($order_data);

            }
        }

    }


    public static function shopifyUpstreamProduct($user_connection_id, $product_id, $is_update = true, $p_variant_id = 0){

        if ( $is_update ){
            return self::shopifyUpdateProduct($user_connection_id, $product_id, $p_variant_id);
        }

        return self::shopifyNewProduct($user_connection_id, $product_id);

    }

    public static function shopifyUpdateProduct($user_connection_id, $product_id, $p_variant_id = 0){

        $sc = self::createShopifyClient($user_connection_id);
        $connection_product_id = 0;

        $response = [
            'success' => false,
            'product_id' => $product_id,
            'connection_product_id' => $connection_product_id,
            'user_connection_id' => $user_connection_id,
            'message' => "Couldn't find a user connection"
        ];


        if ( !empty($sc) ) {

            $shopify_connection = UserConnection::findOne(['id' => $user_connection_id]);
            $shopifyClientInfo = $shopify_connection->connection_info;
            $shopify_shop = $shopifyClientInfo['url'];

            $shopify_connection_details = $shopify_connection->userConnectionDetails;
            $importUser = $shopify_connection->user;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';
            $shopDetails = $shopify_connection_details->others;
            $shopCurrency = $shopDetails['shop_currency'];


            $shopify_currency_code = $shopify_connection_details->currency;
            $conversion_rate = 1;
            if ($shopify_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($userCurrency, $shopCurrency);
                //$conversion_rate = CurrencyConversion::getCurrencyConversionRate($userCurrency, $shopify_currency_code);
            }


            if ( $p_variant_id > 0 ){

                return self::shopifyUpdateVariants($user_connection_id, $p_variant_id, $conversion_rate);

            } else {

                $product = Product::findOne(['id' => $product_id]);


                if ( !empty($product) ){
                    $shopifyConnectionProduct = ProductConnection::findOne([
                       'user_connection_id' => $user_connection_id,
                       'product_id' => $product_id
                    ]);

                    if ( !empty($shopifyConnectionProduct) ){

                        $connection_product_id = $shopifyConnectionProduct->connection_product_id;

                        if ( $connection_product_id > 0 ) {

                            $productShopifyCallUrl = '/admin/products/' . $connection_product_id . '.json';

                            $product_prefix_url = 'https://' . $shopify_shop . '/products/';
                            $product_handle = str_replace($product_prefix_url, '', $product->url);

                            $product_published = (strtolower($product->published) === strtolower(Product::PRODUCT_PUBLISHED_YES))?true:false;

                            $productVariantOne = ProductVariation::findOne([
                                'product_id' => $product_id,
                                'user_connection_id' => $user_connection_id
                            ]);

                            if ( empty($productVariantOne) ){

                                $originProductConnection = ProductConnection::findOne(['product_id' => $product_id]);

                                if ( !empty($originProductConnection) ) {
                                    $originUserConnectionId = $originProductConnection->user_connection_id;

                                    $productVariantOne = ProductVariation::findAll([
                                        'product_id' => $product_id,
                                        'user_connection_id' => $originUserConnectionId
                                    ]);

                                }
                            }

                            $updateParam = [
                                "product" => [
                                    "id" => $connection_product_id,
                                    "title" => $product->name,
                                    "body_html" => $product->description,
                                    "product_type" => $product->type,
                                    "handle" => $product_handle,
                                    "published" => $product_published,
                                ]
                            ];

                            if ( !empty($productVariantOne) ){
                                $p_option_set = [];
                                $p_variant_set_id = $productVariantOne->variation_set_id;

                                if ( !empty($p_variant_set_id) ){
                                    $p_option_set = self::shopifyOptions($p_variant_set_id);

                                    if ( !empty($p_option_set) ){
                                        $postParam['product']['options'] = $p_option_set;
                                    }
                                }

                            }

                            try{

                                $result = $sc->call('PUT', $productShopifyCallUrl, $updateParam);

                                $productVariantObjects = ProductVariation::findAll(
                                    [
                                        'product_id' => $product_id,
                                        'user_connection_id' => $user_connection_id
                                    ]
                                );

                                $loopCount = 0;
                                $allSyncFlag = true;
                                foreach ($productVariantObjects as $pVariantOne){

                                    $update_p_variant_id = $pVariantOne->primaryKey;
//                                    if ( $loopCount > 0 ){
//                                        $allSyncFlag = false;
//                                    }
                                    self::shopifyUpdateVariants($user_connection_id, $update_p_variant_id, $conversion_rate, $connection_product_id, $allSyncFlag);
                                    $loopCount ++;
                                    sleep(self::timeOutLimit);
                                }

                                $response['success'] = true;
                                $response['connection_product_id'] = $connection_product_id;
                                $response['message'] = "";

                            } catch (ShopifyApiException $shopifyApiEx) {

                                $msg = $shopifyApiEx->getMessage();

                                $response['message'] = $msg;


                            }

                        }

                    }

                }

            }

        }

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }


    public static function shopifyUpdateVariants($user_connection_id, $p_variant_id, $conversion_rate, $connection_product_id=null, $allSync=false){

        $shopifyClient = self::createShopifyClient($user_connection_id);

        $parent_price = 0;
        $parent_sales_price = 0;

        if ( !empty($shopifyClient) ){

            $pVariantModel = ProductVariation::findOne(['id' => $p_variant_id]);

            if ( empty($connection_product_id) ) {
                $product_id = $pVariantModel->product_id;

                $productConnection = ProductConnection::findOne([
                    'product_id' => $product_id,
                    'user_connection_id' => $user_connection_id
                ]);

                if ( !empty($productConnection) ){
                    $connection_product_id = $productConnection->connection_product_id;
                }

            }

            if ( !empty($pVariantModel) ){

                $connection_variant_id = $pVariantModel->connection_variation_id;

                $parent_product_id = $pVariantModel->product_id;
                $parent_product = Product::findOne([
                    'id' => $parent_product_id
                ]);

                if ( !empty($parent_product) ){
                    $parent_price = $parent_product->price;
                    $parent_sales_price = $parent_product->sales_price;

                    $parent_price = number_format((float)$conversion_rate * $parent_price, 2, '.', '');
                    $parent_sales_price = number_format((float)$conversion_rate * $parent_sales_price, 2, '.', '');
                }


                if ( $connection_variant_id == "-1" || $connection_variant_id == -1 ){
                    if ( !empty($connection_product_id) ) {

                        //$sku_key = $pVariantModel->sku_key;
                        $sku_value = $pVariantModel->sku_value;

                        //$inventory_key = $pVariantModel->inventory_key;
                        $inventory_value = $pVariantModel->inventory_value;

                        //$price_key = $pVariantModel->price_key;
                        $price_value = $pVariantModel->price_value;

                        //$weight_key = $pVariantModel->weight_key;
                        $weight_value = $pVariantModel->weight_value;

                        $p_variant_set_id = $pVariantModel->variation_set_id;
                        $p_variant_id = $pVariantModel->variation_id;
                        $p_v_option = [];


                        if ( !empty($price_value) ){
                            $price_value = number_format((float)$conversion_rate * $price_value, 2, '.', '');
                        } else {
                            $price_value = 0;
                        }

                        if ( !empty($p_variant_id) ){
                            $p_v_option = self::shopifyVariantOption($p_variant_id, $p_variant_set_id);
                        }

                        if ( $price_value == 0  ){
                            if ( $parent_price > 0 ){
                                $price_value = $parent_price;
                            }
                        }
                        $postParam = [
                            'variant' => [
                                'sku' => $sku_value,
                                'inventory_management' => self::storeName,
                                'inventory_quantity' => $inventory_value,
                                'price' => $price_value,
                                'weight' => $weight_value,
                                'compare_at_price' => $parent_sales_price,
                            ]
                        ];

                        if ( !empty($p_v_option) ){

                            if ( isset($p_v_option['option1']) ){
                                $oneVariant['variant']['option1'] = $p_v_option['option1'];
                            }

                            if ( isset($p_v_option['option2']) ){
                                $oneVariant['variant']['option2'] = $p_v_option['option2'];
                            }

                            if ( isset($p_v_option['option3']) ){
                                $oneVariant['variant']['option3'] = $p_v_option['option3'];
                            }

                        }

                        $pVariantShopifyCallUrl = '/admin/products/' . $connection_product_id . '/variants.json';

                        try{

                            $result = $shopifyClient->call('POST', $pVariantShopifyCallUrl, $postParam);

                            if ( !empty($result) && isset($result['variant']) ){
                                $response_variant_id = $result['variant']['id'];

                                $pVariantModel->user_connection_id = $user_connection_id;
                                $pVariantModel->connection_variation_id = $response_variant_id;
                                $pVariantModel->save(true, ['user_connection_id', 'connection_variation_id']);

                            }

                        } catch (ShopifyApiException $shopifyApiEx) {

                            $msg = $shopifyApiEx->getMessage();

                            return 0;

                        }

                        return $connection_product_id;

                    }

                } else {

                    if ( !empty($connection_product_id) ){

                        $sku_key = $pVariantModel->sku_key;
                        $sku_value = $pVariantModel->sku_value;

                        $inventory_key = $pVariantModel->inventory_key;
                        $inventory_value = $pVariantModel->inventory_value;

                        $price_key = $pVariantModel->price_key;
                        $price_value = $pVariantModel->price_value;

                        $weight_key = $pVariantModel->weight_key;
                        $weight_value = $pVariantModel->weight_value;

                        $p_variant_set_id = $pVariantModel->variation_set_id;
                        $p_variant_id = $pVariantModel->variation_id;
                        $p_v_option = [];

                        if ( !empty($price_value) ){
                            $price_value = number_format((float)$conversion_rate * $price_value, 2, '.', '');
                        } else {
                            $price_value = 0;
                        }

                        if ( !empty($p_variant_id) ){
                            $p_v_option = self::shopifyVariantOption($p_variant_id, $p_variant_set_id);
                        }

                        if ( $price_value == 0 ){
                            if ( $parent_price > 0 ){
                                $price_value = $parent_price;
                            }
                        }
                        $updateParam = [
                            'variant' => [
                                'id' => $connection_variant_id,
                                //$sku_key => $sku_value,
                                'inventory_management' => self::storeName,
                                $inventory_key => $inventory_value,
                                $price_key => $price_value,
                                $weight_key => $weight_value,
                                'compare_at_price' => $parent_sales_price,
                            ]
                        ];

                        if ( !empty($p_v_option) ){

                            if ( isset($p_v_option['option1']) ){
                                $oneVariant['variant']['option1'] = $p_v_option['option1'];
                            }

                            if ( isset($p_v_option['option2']) ){
                                $oneVariant['variant']['option2'] = $p_v_option['option2'];
                            }

                            if ( isset($p_v_option['option3']) ){
                                $oneVariant['variant']['option3'] = $p_v_option['option3'];
                            }

                        }

                        $pVariantShopifyCallUrl = '/admin/variants/' . $connection_variant_id . '.json';

                        try{

                            $result = $shopifyClient->call('PUT', $pVariantShopifyCallUrl, $updateParam);

                        } catch (ShopifyApiException $shopifyApiEx) {

                            $msg = $shopifyApiEx->getMessage();

                            return 0;

                        }

                        return $connection_product_id;


                    }

                }

            }

        }

        return 0;
    }


    public static function shopifyNewProduct($user_connection_id, $product_id){

        $sc = self::createShopifyClient($user_connection_id);
        $connection_product_id = 0;
        $response = [
            'success' => false,
            'product_id' => $product_id,
            'connection_product_id' => $connection_product_id,
            'user_connection_id' => $user_connection_id,
            'message' => "Couldn't find a user connection."
        ];

        if ( !empty($sc) ){

            $shopify_connection = UserConnection::findOne(['id' => $user_connection_id]);
            //$shopifyClientInfo = $shopify_connection->connection_info;

            $shopify_connection_details = $shopify_connection->userConnectionDetails;
            $importUser = $shopify_connection->user;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';
            $shopDetails = $shopify_connection_details->others;
            $shopCurrency = $shopDetails['shop_currency'];


            $shopify_currency_code = $shopify_connection_details->currency;
            $conversion_rate = 1;
            if ($shopify_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($userCurrency, $shopCurrency);
                //$conversion_rate = CurrencyConversion::getCurrencyConversionRate($userCurrency, $shopify_currency_code);
            }

            $product = Product::findOne(['id' => $product_id]);

            if ( !empty($product) ){

                $productShopifyCallUrl = '/admin/products.json';

                $postParam = [
                    "product" => [
                        "title" => $product->name,
                        "body_html" => $product->description,
                    ]
                ];

                if ( !empty($product->type) ){
                    $postParam['product']['product_type'] = $product->type;
                }

                $stock_manage = $product->stock_manage;

                $parent_sales_price = 0;
                if (!empty($product->sales_price)){
                    $parent_sales_price = $product->sales_price;
                    $parent_sales_price = number_format((float)$conversion_rate * $parent_sales_price, 2, '.', '');
                }
                $parent_price = 0;
                if (!empty($product->price)){
                    $parent_price = $product->price;
                    $parent_price = number_format((float)$conversion_rate * $parent_price, 2, '.', '');
                }

                $productVariants = ProductVariation::findAll([
                    'product_id' => $product_id,
                    'connection_variation_id' => '-1'
                ]);

                if ( empty($productVariants) ){

                    $originProductConnection = ProductConnection::findOne(['product_id' => $product_id]);

                    if ( !empty($originProductConnection) ) {
                        $originUserConnectionId = $originProductConnection->user_connection_id;

                        $productVariants = ProductVariation::findAll([
                            'product_id' => $product_id,
                            'user_connection_id' => $originUserConnectionId
                        ]);

                    }
                }
                $p_variant_Param = [];
                $p_option_set = [];
                foreach ($productVariants as $productVariant){

                    $sku_value = $productVariant->sku_value;
                    $inventory_value = $productVariant->inventory_value;

                    if ( $stock_manage === Product::STOCK_MANAGE_NO ){
                        $inventory_value = 0;
                    }

                    $price_value = $productVariant->price_value;
                    $p_variant_set_id = $productVariant->variation_set_id;
                    $p_variant_id = $productVariant->variation_id;
                    $p_v_option = [];
                    if ( !empty($price_value) ){
                        $price_value = number_format((float)$conversion_rate * $price_value, 2, '.', '');
                    } else {
                        $price_value = 0;
                    }

                    if ( !empty($p_variant_id) ){
                        $p_v_option = self::shopifyVariantOption($p_variant_id, $p_variant_set_id);
                    }

                    $weight_value = $productVariant->weight_value;

                    if ( $price_value == 0 ){
                        if ( $parent_price > 0 ){
                            $price_value = $parent_price;
                        }
                    }
                    $oneVariant = [
                        'sku' => $sku_value,
                        'inventory_management' => self::storeName,
                        'inventory_quantity' => $inventory_value,
                        'price' => $price_value,
                        'weight' => $weight_value,
                        'compare_at_price' => $parent_sales_price,
                    ];
                    if ( !empty($p_v_option) ){

                        if ( isset($p_v_option['option1']) ){
                            $oneVariant['option1'] = $p_v_option['option1'];
                        }

                        if ( isset($p_v_option['option2']) ){
                            $oneVariant['option2'] = $p_v_option['option2'];
                        }

                        if ( isset($p_v_option['option3']) ){
                            $oneVariant['option3'] = $p_v_option['option3'];
                        }

                    }
                    $p_variant_Param[] = $oneVariant;
                }


                if ( isset($p_variant_set_id) ){
                    $p_option_set = self::shopifyOptions($p_variant_set_id);

                    if ( !empty($p_option_set) ){
                        $postParam['product']['options'] = $p_option_set;
                    }
                }

                if ( !empty($p_variant_Param) ){
                    $postParam['product']['variants'] = $p_variant_Param;
                }

                $p_images = [];
                $productImages = ProductImage::findAll([
                    'product_id' => $product_id
                ]);

                if ( !empty($productImages) ){

                    foreach ($productImages as $productImage) {

                        $p_images[] = [
                            'src' => $productImage->link
                        ];

                    }
                }
                if ( !empty($p_images) ){
                    $postParam['product']['images'] = $p_images;
                }

                try{

                    $result = $sc->call('POST', $productShopifyCallUrl, $postParam);

                    if ( !empty($result) && isset($result['id']) ){
                        $connection_product_id = $result['id'];

                        $response['success'] = true;
                        $response['connection_product_id'] = $connection_product_id;
                        $response['message'] = "";
                    }

                } catch (ShopifyApiException $shopifyApiEx) {

                    $msg = $shopifyApiEx->getMessage();
                    $response['message'] = $msg;
                }

            }

        }

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public static function shopifyDeleteProduct($user_connection_id, $connection_product_id){

        $shopifyClient = self::createShopifyClient($user_connection_id);

        if ( !empty($shopifyClient) && strlen($connection_product_id) > 4 ){

            $shopify_p_delete_url = '/admin/products/' . $connection_product_id . '.json';

            try{

                $shopifyClient->call('DELETE', $shopify_p_delete_url);

            } catch (ShopifyApiException $shopifyApiEx) {

                $msg = $shopifyApiEx->getMessage();

                return false;

            }

            return true;

        }

        return false;
    }

    public static function shopifyOptions($option_set_id){

        $p_options = [];
        $pVarSet = VariationSet::findOne(['id' => $option_set_id]);
        if ( !empty($pVarSet) ){
            $pVarSet_Name = $pVarSet->name;

            $pVarSet_Values = $pVarSet->items;
            $split_values = explode('-', $pVarSet_Values);
            $values_array = array();
            foreach ($split_values as $p_option_value){

                $value = VariationValue::findOne(['id' => $p_option_value]);

                if ( !empty($value) ){

                    $value_item_id = $value->variation_item_id;
                    $value_itme_value = !empty($value->label)?$value->label:$value->value;

                    $valueItem = VariationItem::findOne(['id' => $value_item_id]);

                    if ( !empty($valueItem) ){

                        $itemName = ucfirst(strtolower(trim($valueItem->name)));

                        $values_array[$itemName][] = $value_itme_value;

                    }

                }
            }

            $split_names = explode('/', $pVarSet_Name);
            foreach ($split_names as $name){

                $p_option_name = ucfirst(strtolower(trim($name)));

                foreach ($values_array as $value_key => $value_data){
                    if ( $p_option_name === $value_key ){

                        $p_options[] = [
                            "name" => $p_option_name,
                            "values" => $value_data,
                        ];

                    }
                }

            }

        }

        return $p_options;

    }

    public static function shopifyVariantOption($p_variant_id, $p_var_set_id){

        $p_option = [];

        $pVariant = Variation::findOne(['id' => $p_variant_id]);

        if ( !empty($pVariant) && !empty($p_var_set_id) && !empty($pVariant->items) ){

            $pVarSet = VariationSet::findOne(['id' => $p_var_set_id]);
            if ( !empty($pVarSet) ){

                $pVarSetName = $pVarSet->name;
                $split_names = explode('/', $pVarSetName);
                $p_variant_value = $pVariant->items;
                $split_values = explode('-', $p_variant_value);

                $p_option_data = [];
                foreach ($split_values as $split_value){

                    $p_value = VariationValue::findOne(['id' => $split_value]);

                    $value_item_id = $p_value->variation_item_id;
                    $value = !empty($p_value->label)?$p_value->label:$p_value->value;

                    $valueItem = VariationItem::findOne(['id' => $value_item_id]);

                    $value_item_name = ucfirst(strtolower(trim($valueItem->name)));

                    $p_option_data[] = [
                        "name" => $value_item_name,
                        "value" => $value
                    ];
                }

                foreach ($p_option_data as $p_option_value){

                    $count = 1;
                    foreach ($split_names as $name){
                        $p_var_set_name = ucfirst(strtolower(trim($name)));
                        if ( $p_var_set_name === $p_option_value['name'] ){
                            $key_name = "option" . $count;
                            $p_option[$key_name] = $p_option_value['value'];
                        }

                        $count ++;
                    }


                }
            }


        }

        return $p_option;
    }
}