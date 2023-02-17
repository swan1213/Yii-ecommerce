<?php
namespace frontend\components;


use Automattic\WooCommerce\HttpClient\HttpClientException;
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
use Yii;
use yii\base\Component;
use Automattic\WooCommerce\Client as Woocommerce;
use common\components\order\OrderStatus;

class WoocommerceComponent extends Component
{
    const timeOutLimit = 1;
    const errorTimeOutLimit = 20;
    const storeName = "woocommerce";


    public static function createWoocommerceClient($user_connection_id){

        $store_woocommerce = UserConnection::findOne(['id' => $user_connection_id]);
        $woocommerceClientInfo = $store_woocommerce->connection_info;

        $woocommerce_store_url = $woocommerceClientInfo['store_url'];
        $woocommerce_consumer = $woocommerceClientInfo['consumer'];
        $woocommerce_secret = $woocommerceClientInfo['consumer_secret'];

        $check_url = parse_url($woocommerce_store_url);
        $url_protocol = $check_url['scheme'];
        if ($url_protocol == 'http'){
            /* For Http Url */
            $wooCommerceClient = new Woocommerce(
                $woocommerce_store_url,
                $woocommerce_consumer,
                $woocommerce_secret,
                [
                    'wp_api' => true,
                    'version' => 'wc/v1',
                    'timeout' => 0,
                ]
            );

        } else {
            $wooCommerceClient = new Woocommerce(
                $woocommerce_store_url,
                $woocommerce_consumer,
                $woocommerce_secret,
                [
                    'wp_api' => true,
                    'version' => 'wc/v1',
                    "query_string_auth" => true,
                    'timeout' => 0,
                ]
            );

        }

        return $wooCommerceClient;
    }

    public static function importShop($user_connection_id, $shopDatails){

        $store_shopUrl = $shopDatails['store_url'];
        $store_addr_1 = "";
        $store_addr_2 = "";
        $store_city = "";
        $store_county_state = "";
        $store_zip = "";
        $store_currency = "";

        $wooCommerceDetails = @json_decode(@json_encode($shopDatails['details']), true);

        foreach ($wooCommerceDetails as $eachData){

            if ( $eachData['id'] === 'woocommerce_store_address' ){
                $store_addr_1 = $eachData['value'];
            }

            if ( $eachData['id'] === 'woocommerce_store_address_2' ){
                $store_addr_2 = $eachData['value'];
            }

            if ( $eachData['id'] === 'woocommerce_store_city' ){
                $store_city = $eachData['value'];
            }

            if ( $eachData['id'] === 'woocommerce_default_country' ){
                $store_county_state = $eachData['value'];
            }

            if ( $eachData['id'] === 'woocommerce_store_postcode' ){
                $store_zip = $eachData['value'];
            }

            if ( $eachData['id'] === 'woocommerce_currency' ){
                $store_currency = $eachData['value'];
            }

        }


        $store_county_state_info = explode(':', $store_county_state);
        $store_country_code = $store_county_state_info[0];

        /* For Country */
        $countryObject = Country::countryInfoFromCode($store_country_code);
        $store_country = $countryObject->name;
        $store_currency_symbol = $countryObject->currency_symbol;


        $others_details = array(
            "address1" => $store_addr_1,
            "address2" => $store_addr_2,
            "city" => $store_city,
            "country_state" => $store_county_state,
            "post_code" => $store_zip,
            "currency" => $store_currency,

        );

        $others_details = json_encode($others_details);

        $user_Woocommerce_connection = UserConnectionDetails::findOne(
            [
                'user_connection_id' => $user_connection_id
            ]
        );

        if (empty($user_Woocommerce_connection)) {
            $user_Woocommerce_connection = new UserConnectionDetails();
            $user_Woocommerce_connection->user_connection_id = $user_connection_id;
        }

        $user_Woocommerce_connection->store_name = $store_shopUrl;
        $user_Woocommerce_connection->store_url = $store_shopUrl;
        $user_Woocommerce_connection->country = $store_country;
        $user_Woocommerce_connection->country_code = $store_country_code;
        $user_Woocommerce_connection->currency = $store_currency;
        $user_Woocommerce_connection->currency_symbol = $store_currency_symbol;


        $user_Woocommerce_connection->others = $others_details;


        $userConnectionSettings = $user_Woocommerce_connection->settings;

        if(empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
            $userConnectionSettings['currency'] = $user_Woocommerce_connection->currency;
        }

        $user_Woocommerce_connection->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);


        $user_Woocommerce_connection->save(false);

    }

    private static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /*
     * woocommerce add hooks
     */

    public static function addWoocommerceHooks($user_connection_id) {

        $hookBaseUrl = env('SEVER_URL');

        $woocommerce = self::createWoocommerceClient($user_connection_id);
        $hook_secret = self::generateRandomString(40);

        /*
           $customer_delete = [
           'name' => 'customer deleted',
           'topic' => 'customer.deleted',
           'secret' => $woo_secret_key,
           'delivery_url' => Yii::$app->params['BASE_URL'] . 'people/wc-customer-delete-hook?id=' . $user_id."&connection_id=". $store_connection_id
           ]; */

        $customer_create = [
            'name' => 'customer created',
            'topic' => 'customer.created',
            'secret' => $hook_secret,
            'delivery_url' => $hookBaseUrl . 'hooklistener/woocommerce/customer-create?id=' . $user_connection_id ."&action=". self::storeName
        ];
        $customer_update = [
            'name' => 'customer updated',
            'topic' => 'customer.updated',
            'secret' => $hook_secret,
            'delivery_url' => $hookBaseUrl . 'hooklistener/woocommerce/customer-update?id=' . $user_connection_id ."&action=". self::storeName
        ];


        $product_created = [
            'name' => 'product created',
            'topic' => 'product.created',
            'secret' => $hook_secret,
            'delivery_url' => $hookBaseUrl . 'hooklistener/woocommerce/product-create?id=' . $user_connection_id . "&action=" . self::storeName
        ];
        $product_updated = [
            'name' => 'product updated',
            'topic' => 'product.updated',
            'secret' => $hook_secret,
            'delivery_url' => $hookBaseUrl . 'hooklistener/woocommerce/product-update?id=' . $user_connection_id . "&action=" . self::storeName
        ];
        $product_deleted = [
            'name' => 'product deleted',
            'topic' => 'product.deleted',
            'secret' => $hook_secret,
            'delivery_url' => $hookBaseUrl . 'hooklistener/woocommerce/product-delete?id=' . $user_connection_id . "&action=" . self::storeName
        ];
        $order_created = [
            'name' => 'order created',
            'topic' => 'order.created',
            'secret' => $hook_secret,
            'delivery_url' => $hookBaseUrl . 'hooklistener/woocommerce/order-create?id=' . $user_connection_id . "&action=" . self::storeName
        ];
        $order_updated = [
            'name' => 'order updated',
            'topic' => 'order.updated',
            'secret' => $hook_secret,
            'delivery_url' => $hookBaseUrl . 'hooklistener/woocommerce/order-update?id=' . $user_connection_id . "&action=" . self::storeName
        ];
        /*
          $order_deleted = [
          'name' => 'order deleted',
          'topic' => 'order.deleted',
          'secret' => $woo_secret_key,
          'delivery_url' => Yii::$app->params['BASE_URL'] . 'people/wc-order-delete-hook?id=' . $user_id."&connection_id=". $store_connection_id
          ]; */


        /* Get WebHooks For WOO Commerce */


        $currentHooks = array();
        $i = 1;

        while(true) {
            $pageFilter = ['page' => $i, 'per_page' => 100];
            try {
                $wooHooks = $woocommerce->get('webhooks', $pageFilter);
                $wooHooks = @json_decode(@json_encode($wooHooks), true);
                //if data is empty then loop break else data save
                if (empty($wooHooks)) {
                    break;
                } else {

                    foreach ($wooHooks as $_hook):
                        $currentHooks[] = $_hook['delivery_url'];
                    endforeach;
                }

            } catch (HttpClientException $e){
                $errorMsg = $e->getMessage();
                echo "webhook error = ".$errorMsg;
                continue;
            }

            $i ++;
        }


        /* Check hooks in Woocommerce exists or not */
        if (!in_array($customer_create['delivery_url'], $currentHooks)) {
            $woocommerce->post('webhooks', $customer_create);
        }
        if (!in_array($customer_update['delivery_url'], $currentHooks)) {
            $woocommerce->post('webhooks', $customer_update);
        }
        if (!in_array($product_created['delivery_url'], $currentHooks)) {
            $woocommerce->post('webhooks', $product_created);
        }
        if (!in_array($product_updated['delivery_url'], $currentHooks)) {
            $woocommerce->post('webhooks', $product_updated);
        }
        if (!in_array($product_deleted['delivery_url'], $currentHooks)) {
            $woocommerce->post('webhooks', $product_deleted);
        }
        if (!in_array($order_created['delivery_url'], $currentHooks)) {
            $woocommerce->post('webhooks', $order_created);
        }
        if (!in_array($order_updated['delivery_url'], $currentHooks)) {
            $woocommerce->post('webhooks', $order_updated);
        }

    }

    public static function wcCategoryImport($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $woocommerce = self::createWoocommerceClient($user_connection_id);

        $store_woocommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $importUser = $store_woocommerce->user;
        $user_id = $importUser->id;


        $i = 1;

        while(true) {
            $pageFilter = ['page' => $i, 'per_page' => 100];
            try {
                $categories_wc = $woocommerce->get('products/categories', $pageFilter);
                $categories_wc = @json_decode(@json_encode($categories_wc), true);
                //if data is empty then loop break else data save
                if (empty($categories_wc)) {
                    break;
                } else {
                    foreach ($categories_wc as $category) {
                        $cat_id = $category['id'];
                        $cat_name = $category['name'];
                        $cat_description = $category['description'];
                        $cat_parent_id = $category['parent'];

                        $category_data = [
                            'name' => $cat_name, // Give category name
                            'description' => $cat_description, // Give category body html
                            'parent_id' => 0,
                            'user_id' => $user_id, // Give Elliot user id,
                            'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                            'connection_category_id' => $cat_id, // Give category id of Store/channels
                            'connection_parent_id' => $cat_parent_id, // Give Category parent id of Elliot if null then give 0
                        ];

                        Category::categoryImportingCommon($category_data);
                    }
                }

            } catch (HttpClientException $e){
                $errorMsg = $e->getMessage();
                echo $errorMsg;
                continue;
            }

            $i ++;
        }

        return true;

    }

    public static function wcProductImport($user_connection_id, $conversion_rate = 1){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $woocommerce = self::createWoocommerceClient($user_connection_id);

        $store_woocommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $user_id = $store_woocommerce->user_id;
        $currency = $store_woocommerce->userConnectionDetails->currency;
        $country_code = $store_woocommerce->userConnectionDetails->country_code;

        $i = 1;
        while(true) {
            //if data is empty then loop break else data save
            $pageFilter = ['page' => $i, 'per_page' => 100];
            $wooProducts = $woocommerce->get('products', $pageFilter);

            $wooProducts = @json_decode(@json_encode($wooProducts), true);

            if (empty($wooProducts)) {
                break;
            } else {
                foreach ($wooProducts as $product) {

                    $product['user_id'] = $user_id;
                    $product['user_connection_id'] = $user_connection_id;
                    $product['conversion_rate'] = $conversion_rate;
                    $product['store_country_code'] = $country_code;
                    $product['store_currency_code'] = $currency;

                    self::wcUpsertProduct($product);

                }
            }

            $i ++;
        }
    }

    public static function wcUpsertProduct($product){

        $user_id = $product['user_id'];
        $user_connection_id = $product['user_connection_id'];
        $conversion_rate = $product['conversion_rate'];
        $store_country_code = $product['store_country_code'];
        $store_currency_code = $product['store_currency_code'];

        $product_quantity = 0;

        $p_id = $product['id'];
        $p_name = $product['name'];
        $p_sku = $product['sku'];
        $p_des = $product['description'];
        $product_url = $product['permalink'];
        $p_weight = $product['weight'];
        $product_status = $product['status'];
        $product_type = $product['type'];
        if ($product_status == 'publish') {
            $p_status = 1;
        } else {
            $p_status = 0;
        }

        $p_price = 0;
        if ( isset($product['regular_price']) && !empty($product['regular_price']) ){
            $p_price = $product ['regular_price'];
        }

        $p_saleprice = 0;
        /* For if sale price empty null or 0 then price value is  sale price value */
        if ($product['sale_price'] == '' || $product['sale_price'] == null || $product['sale_price'] == '0') {
            $p_saleprice = $p_price;
        } else {
            $p_saleprice = $product['sale_price'];
        }

        $p_stk_lvl = $product['stock_quantity'];

        $p_avail = $product['in_stock'];

        $create_date = ($product['date_created'] != "") ? $product['date_created'] : date('Y-m-d H:i:s');
        $update_date = ($product['date_modified'] != "") ? $product['date_modified'] : date('Y-m-d H:i:s');
        $p_created_date = date('Y-m-d H:i:s', strtotime($create_date));
        $p_updated_date = date('Y-m-d H:i:s', strtotime($update_date));
        /* For categories */
        $product_categories_ids = array();
        if (!empty($product['categories'])) {
            foreach ($product['categories'] as $key => $value) {
                $cat_id = $value['id'];
                $product_categories_ids[] = $cat_id;
            }
        }
        /* For images */
        $product_image_data = array();
        if (!empty($product['images'])) {
            foreach ($product['images'] as $product_image) {
                $image_id = $product_image['id'];
                $image_link = $product_image['src'];
                $position = $product_image['position'];
                $label = $product_image['name'];

                $product_image_data[] = array(
                    'connection_image_id' => $image_id,
                    'image_url' => $image_link,
                    'label' => $label,
                    'position' => $position,
                    'base_img' => $image_link,
                );
            }
        }

        $options_set_data = [];
        $product_option_set = $product['attributes'];

        if ( !empty($product_option_set) ) {

            foreach ($product_option_set as $eachOption){

                $eachOptionValues = [];
                foreach($eachOption['options'] as $eachOptionValue){
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

        }


        $variants_data = [];

        $product_variants = $product['variations'];

        if ( !empty($product_variants) ){
            foreach ($product_variants as $_variants) {
                $variants_id = $_variants['id'];
                $variants_price = $_variants['price'];

                $variant_sku = ($_variants['sku'] == '') ? '' : $_variants['sku'];

                $variants_qty = $_variants['stock_quantity'];
                $product_quantity += intval($variants_qty);

                $variants_weight = $_variants['weight'];

                //$variants_created_at = $_variants['created_at'];
                //$variants_updated_at = $_variants['updated_at'];
                //$variants_title_value = $_variants['title'];

                $variants_options = [];

                $variationOptions = $_variants['attributes'];
                foreach ($variationOptions as $eachVariationOption){

                    $varOptionName = $eachVariationOption['name'];

                    $value_data = [
                        'label' => '',
                        'value' => $eachVariationOption['option']
                    ];
                    $oneVariantOption = [
                        'name' => $varOptionName,
                        'value' => $value_data
                    ];

                    $variants_options[] = $oneVariantOption;

                }

                $oneVariantData = [
                    'connection_variation_id' => $variants_id,
                    'sku_key' => 'sku',
                    'sku_value' => $variant_sku,
                    'inventory_key' => 'stock_quantity',
                    'inventory_value' => $variants_qty,
                    'price_key' => 'price',
                    'price_value' => $variants_price,
                    'weight_key' => 'weight',
                    'weight_value' => $variants_weight,
                    'upc' => '',
                    'options' => $variants_options,
                ];
                $variants_data[] = $oneVariantData;
            }
        }



        $product_data = [
            'user_id' => $user_id, // Elliot user id
            'name' => $p_name, // Product name
            'type' => $product_type,
            'sku' => $p_sku, // Product SKU
            'url' => $product_url, // Product url if null give blank value
            'upc' => '', // Product barcode if any
            'ean' => '', // Product ean if any
            'jan' => '', // Product jan if any
            'isban' => '', // Product isban if any
            'mpn' => '', // Product mpn if any
            'description' => $p_des, // Product Description
            'brand' => '', // Product brand if any
            'weight' => $p_weight, // Product weight if null give blank value
            'stock_quantity' => $p_stk_lvl, //Product quantity
            'country_code' => $store_country_code, // if your are importing channel give channel id
            'currency' => $store_currency_code,
            'stock_level' => ($p_avail)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK,
            'stock_status' => ($p_stk_lvl>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN,
            'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value
            'price' => $p_price, // Porduct price
            'sales_price' => $p_saleprice, // Product sale price if null give Product price value
            'status' => ($product_status>0)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
            'permanent_hidden' => Product::STATUS_NO,
            'user_connection_id' => $user_connection_id, //Give connection store id
            'connection_product_id' => $p_id, // Stores/Channel product ID
            'websites' => array(), //This is for only magento give only and blank array
            'created_at' => $p_created_date, // Product created at date format date('Y-m-d H:i:s')
            'updated_at' => $p_updated_date, // Product updated at date format date('Y-m-d H:i:s')
            'conversion_rate' => $conversion_rate,
            'categories' => $product_categories_ids, // Product categroy array. If null give a blank array
            'images' => $product_image_data, // Product images data
            'variations' => $variants_data,
            'options_set' => $options_set_data,
        ];

        if ( !empty($product_data) ) {
            Product::productImportingCommon($product_data);
        }

    }

    public static function wcUpdateProduct($user_connection_id, $product_id, $is_update = true) {
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $return_response = [
            'success' => false,
            'product_id' => $product_id,
            'connection_product_id' => 0,
            'user_connection_id' => $user_connection_id,
            'message' => ""
        ];

        $woocommerce = self::createWoocommerceClient($user_connection_id);
        $store_wc = UserConnection::findOne(['id' => $user_connection_id]);
        if (!empty($store_wc)) {
            $importUser = $store_wc->user;
            $store_connection_details = $store_wc->userConnectionDetails;
            $shopName = $store_connection_details->store_name;
            $user_id = $importUser->id;
            $email = $importUser->email;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';
            $store_currency_code = $store_connection_details->currency;

            $conversion_rate = 1;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $userCurrency);
            }
            $product =  Product::findOne(["id"=>$product_id]);
            $productConnection = ProductConnection::findOne([
                'user_connection_id' => $user_connection_id,
                'product_id' => $product_id
            ]);

            $categories_data = array();
            $product_categories = ProductCategory::find()->where(['user_id' => $user_id, 'product_id' => $product_id])->all();
            foreach ($product_categories as $product_category) {
                $category = array(
                    'id'    => $product_category->category->connection_category_id,
                    'name'  => $product_category->category->name,
                    'slug'  => strtolower($product_category->category->name)
                );
                $categories_data[] = $category;
            }

            $images_data = array();
            if (!$is_update) {
                $product_images = ProductImage::find()->where(['user_id' => $user_id, 'product_id' => $product_id])->all();
                foreach ($product_images as $product_image) {
                    $image = array(
                        //'id'    => $product_image->connection_image_id,
                        'src' => $product_image->link,
                        'name' => $product_image->label
                    );
                    $images_data[] = $image;
                }
            }
            $variants_data = array();
            $product_variations = ProductVariation::find()->where(['user_id' => $user_id, 'product_id' => $product_id, 'user_connection_id' => $user_connection_id])->all();
            foreach ($product_variations as $product_variation) {
                $variation = array(
                    'id'    => $product_variation->connection_variation_id,
                    'sku'   => $product_variation->sku_value,
                    'price' => number_format((float)$conversion_rate * $product_variation->price_value, 2, '.', ''),
                    'weight'=> $product_variation->weight_value,
                    'stock_quantity'=> $product_variation->inventory_value
                );
                $variants_data[] = $variation;
            }

            $product_status = "private";
            if ( $product->status == Product::STATUS_ACTIVE ){
                $product_status = "publish";
            }
            $product_data = array(
                'sku'               => $product->sku,
                'name'              => $product->name,
                'type'              => 'simple',
                'status'            => $product_status,
                'catalog_visibility'        => 'visible',
                'description'       => $product->description,
                'price'             => number_format((float)$conversion_rate * $product->price, 2, '.', ''),
                'regular_price'     => number_format((float)$conversion_rate * $product->price, 2, '.', ''),
                'sale_price'        => number_format((float)$conversion_rate * $product->sales_price, 2, '.', ''),
                'attribute_set_id'  => 4,
                'weight'            => $product->weight,
                'dimensions'        => array(
                    'length'    => !empty($product->package_length) ? $product->package_length : "0",
                    'width'     => !empty($product->package_width) ? $product->package_width : "0",
                    'height'    => !empty($product->package_height) ? $product->package_height : "0",
                ),
                'categories'        => $categories_data,
                'images'            => $images_data,
                'variations'        => $variants_data
            );

            if ($is_update)
                $endpoint = 'products/' . $productConnection->connection_product_id;
            else
                $endpoint = 'products';
            try {
                $response = $woocommerce->post($endpoint, $product_data);
                $result = json_encode($response);
                $result = json_decode($result, true);
                if (isset($result['id']) && !empty($result['id']))
                {
                    $return_response['success'] = true;
                    $return_response['connection_product_id'] = $result['id'];
                    $return_response['message'] = "success";
                    return json_encode($return_response, JSON_UNESCAPED_UNICODE);
                }
            }
            catch (HttpClientException $e){
                $return_response['success'] = false;
                $return_response['connection_product_id'] = 0;
                $return_response['message'] =$e->getMessage();
                return json_encode($return_response, JSON_UNESCAPED_UNICODE);
            }
        }
        return json_encode($return_response, JSON_UNESCAPED_UNICODE);
    }

    public static function wcDeleteProduct($user_connection_id, $connection_product_id)
    {
        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $woocommerce = self::createWoocommerceClient($user_connection_id);
        $response = $woocommerce->delete("/products/" . $connection_product_id);
        if (isset($response->id) && !empty($response->id))
            return true;
        else
            return false;
    }

    public static function wcUpsertCustomer ( $_customer ) {

        $user_id = $_customer['user_id'];
        $user_connection_id = $_customer['user_connection_id'];



        $customer_id = $_customer['id'];
        $create_at = date('Y-m-d h:i:s', strtotime($_customer['date_created']));
        $update_at = date('Y-m-d h:i:s', strtotime($_customer['date_modified']));
        $customer_email = $_customer['email'];
        $first_name = $_customer['first_name'];
        $last_name = $_customer['last_name'];
        $photo_image = $_customer['avatar_url'];

        $customer_addresses = [];

        if ( !empty($_customer['billing']) ){
            $bill_address = $_customer['billing'];

            $customer_country = '';
            $customer_country_iso = $bill_address['country'];
            if ( !empty($customer_country_iso) ){
                $countryCodeInfo = Country::countryInfoFromCode($customer_country_iso);

                if ( !empty($countryCodeInfo) ){
                    $customer_country = $countryCodeInfo->name;
                }

            }
            $customer_addresses[] = [
                'first_name' => $bill_address['first_name'],
                'last_name' => $bill_address['last_name'],
                'company' => $bill_address['company'],
                'country' => $customer_country,
                'country_iso' => $customer_country_iso,
                'street_1' => $bill_address['address_1'],
                'street_2' => $bill_address['address_2'],
                'state' => $bill_address['state'],
                'city' => $bill_address['city'],
                'zip' => $bill_address['postcode'],
                'phone' => $bill_address['phone'],
                'address_type' => 'billing',

            ];
        }
//        if ( !empty($_customer['shipping']) ){
//            $ship_address = $_customer['shipping'];
//            $customer_addresses[] = [
//                'first_name' => $ship_address['first_name'],
//                'last_name' => $ship_address['last_name'],
//                'company' => $ship_address['company'],
//                'country' => $ship_address['country'],
//                'country_iso' => '',
//                'street_1' => $ship_address['address_1'],
//                'street_2' => $ship_address['address_2'],
//                'state' => $ship_address['state'],
//                'city' => $ship_address['city'],
//                'zip' => $ship_address['postcode'],
//                'phone' => '',
//                'address_type' => 'shipping',
//            ];
//
//        }

        $customer_data = [
            'connection_customerId' => $customer_id,
            'user_id' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $customer_email,
            'photo_image' => $photo_image,
            'phone' => '',
            'user_connection_id' => $user_connection_id,
            'customer_created' => $create_at,
            'updated_at' => $update_at,
            'addresses' => $customer_addresses
        ];

        if ( !empty($customer_data) ){
            Customer::customerImportingCommon($customer_data);
        }

    }


    public static function wcCustomerImport($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $woocommerce = self::createWoocommerceClient($user_connection_id);

        $store_woocommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $user_id = $store_woocommerce->user_id;


        $i = 1;
        while(true) {

            $pageFilter = ['page' => $i, 'per_page' => 100];
            $customer_list = $woocommerce->get('customers', $pageFilter);

            $customer_list = @json_decode(@json_encode($customer_list), true);
            //if data is empty then loop break else data save
            if (empty($customer_list)) {
                break;
            } else {
                foreach ($customer_list as $_customer) {

                    $_customer['user_id'] = $user_id;
                    $_customer['user_connection_id'] = $user_connection_id;

                    self::wcUpsertCustomer($_customer);

                }
            }

            $i ++;
        }
    }

    public static function wcUpsertOrder($orders_data){

        $user_connection_id = $orders_data['user_connection_id'];
        $user_id = $orders_data['user_id'];
        $currency = $orders_data['currency'];
        $conversion_rate = $orders_data['conversion_rate'];
        $country_code = $orders_data['country_code'];

        //get WooCommerce Order id
        $woocommerce_order_id = $orders_data['id'];
        $customer_id = $orders_data['customer_id'];
        //Fetch Ship details
        $order_status = $orders_data['status'];
        //$items_total = count($orders_data['line_items']);
        $product_qauntity = 0;

        //billing Address
        $bill_street_1 = isset($orders_data['billing']['address_1']) ? $orders_data['billing']['address_1'] : '';
        $bill_street_2 = isset($orders_data['billing']['address_2']) ? $orders_data['billing']['address_2'] : '';
        $bill_city = isset($orders_data['billing']['city']) ? $orders_data['billing']['city'] : '';
        $bill_state = isset($orders_data['billing']['state']) ? $orders_data['billing']['state'] : '';
        $bill_zip = isset($orders_data['billing']['postcode']) ? $orders_data['billing']['postcode'] : '';
        $bill_country = '';
        $bill_country_iso = isset($orders_data['billing']['country']) ? $orders_data['billing']['country'] : '';
        if ( !empty($bill_country_iso) ){
            $countryCodeInfo = Country::countryInfoFromCode($bill_country_iso);

            if ( !empty($countryCodeInfo) ){
                $bill_country = $countryCodeInfo->name;
            }

        }
        $bill_phone = isset($orders_data['billing']['phone']) ? $orders_data['billing']['phone'] : '';
        $bill_fname = isset($orders_data['billing']['first_name']) ? $orders_data['billing']['first_name'] : '';
        $bill_lname = isset($orders_data['billing']['last_name']) ? $orders_data['billing']['last_name'] : '';


        //Shipping Address
        //Shipping Address
        $ship_street_1 = isset($orders_data['shipping']['address_1']) ? $orders_data['shipping']['address_1'] : $orders_data['billing']['address_1'];
        $ship_street_2 = isset($orders_data['shipping']['address_2']) ? $orders_data['shipping']['address_2'] : $orders_data['billing']['address_2'];
        $ship_city = isset($orders_data['shipping']['city']) ? $orders_data['shipping']['city'] : $orders_data['billing']['city'];
        $ship_state = isset($orders_data['shipping']['state']) ? $orders_data['shipping']['state'] : $orders_data['billing']['state'];
        $ship_zip = isset($orders_data['shipping']['postcode']) ? $orders_data['shipping']['postcode'] : $orders_data['billing']['postcode'];
        $ship_country = '';
        $ship_country_iso = isset($orders_data['shipping']['country']) ? $orders_data['shipping']['country'] : $orders_data['billing']['country'];
        if ( !empty($ship_country_iso) ){
            $countryCodeInfo = Country::countryInfoFromCode($ship_country_iso);

            if ( !empty($countryCodeInfo) ){
                $ship_country = $countryCodeInfo->name;
            }

        }



        $total_amount = $orders_data['total'];
        $base_shipping_cost = $orders_data['shipping_total'];
        $shipping_cost_tax = $orders_data['shipping_tax'];
        $discount_amount = $orders_data['discount_total'];
        $payment_method = $orders_data['payment_method'];
        $payment_provider_id = $orders_data['transaction_id'];
        /* For refunded Amount */
        $refunded_amount = 0;
        if (!empty($orders_data['refunds'])) {
            foreach ($orders_data['refunds'] as $refund) {
                $refunded_amount = +$refund['total'];
            }
        }
        /* End refunded Amount */
        /*
         * Order status change according to status
         */
        switch ($order_status) {
            case "pending":
                $order_status = OrderStatus::PENDING;
                break;
            case "processing":
                $order_status = OrderStatus::IN_TRANSIT;
                break;
            case "on-hold":
                $order_status = OrderStatus::ON_HOLD;
                break;
            case "completed":
                $order_status = OrderStatus::COMPLETED;
                break;
            case "cancelled":
                $order_status = OrderStatus::CANCEL;
                break;
            case "refunded":
                $order_status = OrderStatus::REFUNDED;
                break;
            default:
                $order_status = OrderStatus::PENDING;
        }


        $order_date = date('Y-m-d H:i:s', strtotime($orders_data['date_created']));
        $order_last_modified_date = date('Y-m-d H:i:s', strtotime($orders_data['date_modified']));
        $customer_email = isset($orders_data['billing']['email']) ? $orders_data['billing']['email'] : "";

        /* For order Items */
        $order_items = $orders_data['line_items'];
        $order_product_data = array();
        foreach ($order_items as $product_dtails_data) {
            $product_id = $product_dtails_data['product_id'];
            $title = $product_dtails_data['name'];
            $sku = $product_dtails_data['sku'];
            $price = $product_dtails_data['total'];
            $quantity = $product_dtails_data['quantity'];
            $product_weight = isset($product_dtails_data['weight']) ? $product_dtails_data['weight'] : 0;
            $product_qauntity += $quantity;
            $order_product_data[] = [
                'user_id' => $user_id,
                'connection_product_id' => $product_id,
                'name' => $title,
                'order_product_sku' => $sku,
                'price' => $price,
                'qty' => $quantity,
                'weight' => $product_weight,
            ];

        }

        $customer_data = [
            'connection_customerId' => $customer_id,
            'user_id' => $user_id,
            'first_name' => $bill_fname,
            'last_name' => $bill_lname,
            'email' => $customer_email,
            'phone' => $bill_phone,
            'user_connection_id' => $user_connection_id,
            'customer_created' => $order_date,
            'updated_at' => $order_date,
            'addresses' => [
                [
                    'first_name' => $bill_fname,
                    'last_name' => $bill_lname,
                    'company' => isset($orders_data['billing']['company'])?$orders_data['billing']['company']:'',
                    'country' => $bill_country,
                    'country_iso' => $bill_country_iso,
                    'street_1' => $bill_street_1,
                    'street_2' => $bill_street_2,
                    'state' => $bill_state,
                    'city' => $bill_city,
                    'zip' => $bill_zip,
                    'phone' => $bill_phone,
                    'address_type' => 'billing',
                ],
            ]
        ];


        $order_data = [
            'user_id' => $user_id,
            'user_connection_id' => $user_connection_id,
            'connection_order_id' => $woocommerce_order_id,
            'status' => $order_status,
            'product_quantity' => $product_qauntity,
            'total_amount' => $total_amount,
            'customer_email' => $customer_email,
            'ship_street_1' => $ship_street_1,
            'ship_street_2' => $ship_street_2,
            'ship_state' => $ship_state,
            'ship_city' => $ship_city,
            'ship_country' => $ship_country,
            'ship_country_iso' => $ship_country_iso,
            'ship_zip' => $ship_zip,
            'bill_street_1' => $bill_street_1,
            'bill_street_2' => $bill_street_2,
            'bill_state' => $bill_state,
            'bill_city' => $bill_city,
            'bill_country' => $bill_country,
            'bill_country_iso' => $bill_country_iso,
            'bill_zip' => $bill_zip,

            'fee' => [
                'refunded_amount' => $refunded_amount,
                'discount_amount' => $discount_amount,
                'payment_method' => $payment_method,
                'shipping_cost_tax' => $shipping_cost_tax,
                'base_shipping_cost' => $base_shipping_cost,
                'payment_status' => $order_status,
                'payment_provider' => $payment_provider_id,
            ],

            'order_date' => $order_date,
            'created_at' => $order_date,
            'updated_at' => $order_last_modified_date,

            'currency_code' => $currency,
            'country_code' => $country_code,
            'conversion_rate' => $conversion_rate,
            'order_products' => $order_product_data,
            'order_customer' => $customer_data
        ];

        if ( !empty($order_data) ){
            Order::orderImportingCommon($order_data);
        }



    }

    public static function wcOrderImport($user_connection_id, $conversion_rate) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $woocommerce = self::createWoocommerceClient($user_connection_id);

        $store_woocommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $user_id = $store_woocommerce->user_id;
        $currency = $store_woocommerce->userConnectionDetails->currency;
        $country_code = $store_woocommerce->userConnectionDetails->country_code;


        $i = 1;

        while (true) {
            //if data is empty then loop break else data save
            $data = ['page' => $i, 'per_page' => 100];
            $wooOrders = $woocommerce->get('orders', $data);

            $wooOrders = @json_decode(@json_encode($wooOrders), true);
            if (empty($wooOrders)) {
                break;
            } else {

                foreach ($wooOrders as $orders_data) {

                    $orders_data['user_connection_id'] = $user_connection_id;
                    $orders_data['currency'] = $currency;
                    $orders_data['user_id'] = $user_id;
                    $orders_data['conversion_rate'] = $conversion_rate;
                    $orders_data['country_code'] = $country_code;

                    self::wcUpsertOrder($orders_data);
                }
            }

            $i ++;
        }
    }


}