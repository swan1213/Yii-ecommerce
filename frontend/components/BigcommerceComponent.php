<?php

namespace frontend\components;


use common\models\Category;
use common\models\CurrencyConversion;
use common\models\CurrencySymbol;
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
use frontend\components\ElliBigCommerce as Bigcommerce;
use common\components\order\OrderStatus;


class BigcommerceComponent extends Component
{
    const timeOutLimit = 1;
    const errorTimeOutLimit = 20;
    const storeName = "bigcommerce";

    public static function importShop($user_connection_id, $shopDatails){


        if (!empty($shopDatails)) {

            $user_Bgc_Connection = UserConnectionDetails::findOne(
                [
                    'user_connection_id' => $user_connection_id
                ]
            );

            if (empty($user_Bgc_Connection)) {
                $user_Bgc_Connection = new UserConnectionDetails();

                $user_Bgc_Connection->user_connection_id = $user_connection_id;
            }

            $symbol = CurrencySymbol::getCurrencySymbol($shopDatails->currency);

            $others_details = [
                "first_name" => isset($shopDatails->first_name)?$shopDatails->first_name:'',
                "last_name" => isset($shopDatails->last_name)?$shopDatails->last_name:'',
                "admin_email" => isset($shopDatails->admin_email)?$shopDatails->admin_email:'',
                "order_email" => isset($shopDatails->order_email)?$shopDatails->order_email:'',
                "address" => isset($shopDatails->address)?$shopDatails->address:'',
                "phone" => isset($shopDatails->phone)?$shopDatails->phone:'',
            ];
            $others_details = json_encode($others_details);

            $user_Bgc_Connection->store_name = isset($shopDatails->name)?$shopDatails->name:'';
            $user_Bgc_Connection->store_url = isset($shopDatails->domain)?$shopDatails->domain:'';
            $user_Bgc_Connection->country = isset($shopDatails->country)?$shopDatails->country:'';
            $user_Bgc_Connection->country_code = isset($shopDatails->country_code)?$shopDatails->country_code:'';
            $user_Bgc_Connection->currency = isset($shopDatails->currency)?$shopDatails->currency:'';
            $user_Bgc_Connection->currency_symbol = $symbol;
            $user_Bgc_Connection->others= $others_details;

            $userConnectionSettings = $user_Bgc_Connection->settings;

            if(empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
                $userConnectionSettings['currency'] = $user_Bgc_Connection->currency;
            }

            $user_Bgc_Connection->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);


            $user_Bgc_Connection->save(false);

            return true;
        }

        return false;

    }





    public static function addBigcommerceHooks($user_connection_id) {

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $bigCommerceSetting = $userConnection->connection_info;

        Bigcommerce::configure($bigCommerceSetting);

        $hookBaseUrl = env('SEVER_URL');

        $bgcHookList = Bigcommerce::listWebhooks();

        $bgcHooks = [];
        if (!empty($bgcHookList)) {

            foreach ($bgcHookList as $eachHook) {

                if ( isset($eachHook->destination) && !empty($eachHook->destination) ){
                    $bgcHooks[] = $eachHook->destination;
                }

            }
        }

        $webHookUrl = $hookBaseUrl . "hooklistener/bigcommerce/product-create?id=" . $user_connection_id . "&action=".self::storeName;
        if (!in_array($webHookUrl, $bgcHooks)) {

            //products hooks
            $hookrs = Bigcommerce::createWebhook(array(
                'scope' => 'store/product/created',
                'destination' => $webHookUrl,
                'is_active' => true
            ));

        }



        //product inventory hooks
        $webHookUrl = $hookBaseUrl . 'hooklistener/bigcommerce/product-update?id=' . $user_connection_id . "&action=".self::storeName;

        if (!in_array($webHookUrl, $bgcHooks)) {

            //products hooks
            $hookrs = Bigcommerce::createWebhook(array(
                'scope' => 'store/product/updated',
                'destination' => $webHookUrl,
                'is_active' => true
            ));

        }


        $webHookUrl = $hookBaseUrl . 'hooklistener/bigcommerce/product-delete?id=' . $user_connection_id . "&action=".self::storeName;

        if (!in_array($webHookUrl, $bgcHooks)) {
            //products hooks
            $hookrs = Bigcommerce::createWebhook(array(
                'scope' => 'store/product/deleted',
                'destination' => $webHookUrl,
                'is_active' => true
            ));

        }




        //Orders create hooks
        $webHookUrl = $hookBaseUrl . 'hooklistener/bigcommerce/order-create?id=' . $user_connection_id . "&action=".self::storeName;

        if (!in_array($webHookUrl, $bgcHooks)) {
            //products hooks
            $hookrs = Bigcommerce::createWebhook(array(
                'scope' => 'store/order/created',
                'destination' => $webHookUrl,
                'is_active' => true
            ));

        }


        //Orders update hooks
        $webHookUrl = $hookBaseUrl . 'hooklistener/bigcommerce/order-update?id=' . $user_connection_id . "&action=".self::storeName;

        if (!in_array($webHookUrl, $bgcHooks)) {
            //products hooks
            $hookrs = Bigcommerce::createWebhook(array(
                'scope' => 'store/order/updated',
                'destination' => $webHookUrl,
                'is_active' => true
            ));

        }



        //Category hooks
        $webHookUrl = $hookBaseUrl . 'hooklistener/bigcommerce/category?id=' . $user_connection_id . "&action=".self::storeName;

        if (!in_array($webHookUrl, $bgcHooks)) {
            //products hooks
            $hookrs = Bigcommerce::createWebhook(array(
                'scope' => 'store/category/*',
                'destination' => $webHookUrl,
                'is_active' => true
            ));

        }




        //customer hooks
        $webHookUrl = $hookBaseUrl . 'hooklistener/bigcommerce/customer?id=' . $user_connection_id . "&action=".self::storeName;

        if (!in_array($webHookUrl, $bgcHooks)) {
            //products hooks
            $hookrs = Bigcommerce::createWebhook(array(
                'scope' => 'store/customer/*',
                'destination' => $webHookUrl,
                'is_active' => true
            ));

        }

        //SKU hooks
//        $hookrs = Bigcommerce::createWebhook(array(
//            'scope' => 'store/sku/*',
//            'destination' => $hookBaseUrl . 'hooklistener/bigcommerce/sku-update?id=' . $user_connection_id,
//            'is_active' => true
//        ));

    }


    public static function removeBigcommerceHooks($user_connection_id) {

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $bigCommerceSetting = $userConnection->connection_info;

        if ( isset($bigCommerceSetting['client_id']) && isset($bigCommerceSetting['auth_token']) && isset($bigCommerceSetting['store_hash']) ){

            Bigcommerce::configure($bigCommerceSetting);

            $hookBaseUrl = env('SEVER_URL');

            $bgcHookList = Bigcommerce::listWebhooks();

            if (!empty($bgcHookList)) {

                foreach ($bgcHookList as $eachHook) {

                    if ( isset($eachHook->destination) && !empty($eachHook->destination) ){
                        $bgchook_url = $eachHook->destination;

                        $webHookUrl_p_create = $hookBaseUrl . "hooklistener/bigcommerce/product-create?id=" . $user_connection_id . "&action=".self::storeName;

                        if ( $bgchook_url === $webHookUrl_p_create ){
                            if ( isset($eachHook->id) && !empty($eachHook->id) ){
                                $bgc_hook_id = $eachHook->id;
                                Bigcommerce::deleteWebhook($bgc_hook_id);
                            }
                        }

                        //product inventory hooks
                        $webHookUrl_p_update = $hookBaseUrl . 'hooklistener/bigcommerce/product-update?id=' . $user_connection_id . "&action=".self::storeName;

                        if ( $bgchook_url === $webHookUrl_p_update ){
                            if ( isset($eachHook->id) && !empty($eachHook->id) ){
                                $bgc_hook_id = $eachHook->id;
                                Bigcommerce::deleteWebhook($bgc_hook_id);
                            }
                        }

                        $webHookUrl_p_delete = $hookBaseUrl . 'hooklistener/bigcommerce/product-delete?id=' . $user_connection_id . "&action=".self::storeName;

                        if ( $bgchook_url === $webHookUrl_p_delete ){
                            if ( isset($eachHook->id) && !empty($eachHook->id) ){
                                $bgc_hook_id = $eachHook->id;
                                Bigcommerce::deleteWebhook($bgc_hook_id);
                            }
                        }

                        //Orders create hooks
                        $webHookUrl_o_create = $hookBaseUrl . 'hooklistener/bigcommerce/order-create?id=' . $user_connection_id . "&action=".self::storeName;

                        if ( $bgchook_url === $webHookUrl_o_create ){
                            if ( isset($eachHook->id) && !empty($eachHook->id) ){
                                $bgc_hook_id = $eachHook->id;
                                Bigcommerce::deleteWebhook($bgc_hook_id);
                            }
                        }


                        $webHookUrl_o_update = $hookBaseUrl . 'hooklistener/bigcommerce/order-update?id=' . $user_connection_id . "&action=".self::storeName;

                        if ( $bgchook_url === $webHookUrl_o_update ){
                            if ( isset($eachHook->id) && !empty($eachHook->id) ){
                                $bgc_hook_id = $eachHook->id;
                                Bigcommerce::deleteWebhook($bgc_hook_id);
                            }
                        }

                        //Category hooks
                        $webHookUrl_category = $hookBaseUrl . 'hooklistener/bigcommerce/category?id=' . $user_connection_id . "&action=".self::storeName;

                        if ( $bgchook_url === $webHookUrl_category ){
                            if ( isset($eachHook->id) && !empty($eachHook->id) ){
                                $bgc_hook_id = $eachHook->id;
                                Bigcommerce::deleteWebhook($bgc_hook_id);
                            }
                        }

                        //customer hooks
                        $webHookUrl_customer = $hookBaseUrl . 'hooklistener/bigcommerce/customer?id=' . $user_connection_id . "&action=".self::storeName;

                        if ( $bgchook_url === $webHookUrl_customer ){
                            if ( isset($eachHook->id) && !empty($eachHook->id) ){
                                $bgc_hook_id = $eachHook->id;
                                Bigcommerce::deleteWebhook($bgc_hook_id);
                            }
                        }


                        //SKU hooks
            //        $hookrs = Bigcommerce::createWebhook(array(
            //            'scope' => 'store/sku/*',
            //            'destination' => $hookBaseUrl . 'hooklistener/bigcommerce/sku-update?id=' . $user_connection_id,
            //            'is_active' => true
            //        ));


                    }

                }
            }


        }

    }

    public static function bgcCategoryImport($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $store_bigcommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $bigCommerceSetting = $store_bigcommerce->connection_info;

        $importUser = $store_bigcommerce->user;
        $user_id = $importUser->id;

        Bigcommerce::configure($bigCommerceSetting);

        /* Fetching All the Categories from BigCommerce Store */
        $categories_total_count = Bigcommerce::getCategoriesCount();

        $pageCount = ceil($categories_total_count / 250);
        //To list all the products
        for ($page = 1; $page <= $pageCount; $page ++) {
            sleep(self::timeOutLimit);
            $pageFilter = [
                "page" => $page,
                "limit" => 250
            ];

            $categories = Bigcommerce::getCategories($pageFilter);
            if (!empty($categories)) {
                foreach ($categories as $category) {

                    $cat_id = $category->id;
                    $cat_name = $category->name;
                    $cat_description = $category->description;
                    $cat_parent_id = $category->parent_id;

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

        }

    }

    public static function bgcUpsertProduct($product, $extraParam){

        $conversion_rate = $extraParam['conversion_rate'];
        $user_connection_id = $extraParam['user_connection_id'];
        $user_company = $extraParam['user_company'];
        $bgcdomain = $extraParam['bgcdomain'];
        $user_id = $extraParam['user_id'];
        $country_code = $extraParam['country_code'];
        $currency = $extraParam['currency'];
        $bigCommerceSetting = $extraParam['bigCommerceSetting'];

        Bigcommerce::configure($bigCommerceSetting);


        $p_id = $product->id;
        $p_name = $product->name;
        $p_type = $product->type;
        //Give prefix big Commerce Product id

        $p_sku = $product->sku;
        $p_upc = $product->upc;
        $p_des = $product->description;
        $p_avail = $product->availability;
        $p_published = Product::PRODUCT_PUBLISHED_NO;
        if ($p_avail == 'available') {
            $p_published = Product::PRODUCT_PUBLISHED_YES;
        }
        if ($p_avail == 'available') {
            $product_status = 1;
        } else {
            $product_status = 0;
        }

        //$product->brand_id;
        //$product->brand; //object
        $p_brand = ucfirst($user_company);
        if ( isset($product->brand_id) && $product->brand_id !== '0' ){

            //$bgc_product_brand = isset($product->brand)?$product->brand:null;
            $bgc_product_brand = Bigcommerce::getBrand($product->brand_id);
            if ( !empty($bgc_product_brand) ){
                $p_brand = ucfirst($bgc_product_brand->name);
            }

        }

        $product_url = $bgcdomain . $product->custom_url;
        $p_weight = $product->weight;
        $p_price = $product->price;
        $p_saleprice = $product->sale_price;
        /* For if sale price empty null or 0 then price value is  sale price value */
        if ($p_saleprice == '' || $p_saleprice == Null || $p_saleprice == 0) {
            $p_saleprice = $p_price;
        }
        $p_visibility = $product->is_visible;
        $p_stk_lvl = $product->inventory_level;
        $p_stk_warning_lvl = $product->inventory_warning_level;
        if ($p_stk_warning_lvl == '' || $p_stk_warning_lvl == 0 || $p_stk_warning_lvl == Null) {
            $p_stk_warning_lvl = 5;
        }
        $p_stk_track = $product->inventory_tracking;
        $p_sale = $product->total_sold;
        $p_condition = $product->condition;
        //Fields which are required but not avaialable @Bigcommerce
        $p_ean = '';
        $p_jan = '';
        $p_isbn = '';
        $p_mpn = '';
        $p_created_date = date('Y-m-d H:i:s', strtotime($product->date_created));
        $p_updated_date = date('Y-m-d H:i:s', strtotime($product->date_modified));

        $product_categories_ids = $product->categories;

        $product_image_data = array();
        if (!empty($product->images)) {
            foreach ($product->images as $_image) {
                $p_image_id = $_image->id;
                $p_image_link = $_image->standard_url;
                $p_image_label = $_image->description;
                $p_image_priority = $_image->sort_order;

                $product_image_data[] = array(
                    'connection_image_id' => $p_image_id,
                    'image_url' => $p_image_link,
                    'label' => $p_image_label,
                    'position' => $p_image_priority,
                    'base_img' => $p_image_link,
                );
            }
        }

        $product_quantity = 0;

        $options_set_data = [];
        $product_option_set = $product->option_set;

        if ( !empty($product_option_set) ) {

            $product_options = $product_option_set->options;
            foreach ($product_options as $eachOption){

                $eachOptionValues = [];
                foreach($eachOption->values as $eachOptionValue){
                    $eachOptionValues[] = [
                        'label' => $eachOptionValue->label,
                        'value' => $eachOptionValue->value
                    ];
                }
                $oneOptions_set_data = [
                    'name' => $eachOption->display_name,
                    'values' => $eachOptionValues
                ];
                $options_set_data[] = $oneOptions_set_data;
            }

        }


        $variants_data = [];

        $product_variants = $product->skus;

        if ( !empty($product_variants) ){
            foreach ($product_variants as $_variants) {
                $variants_id = $_variants->id;
                $variants_price = $_variants->price;

                $variant_sku = ($_variants->sku == '') ? '' : $_variants->sku;
                $variants_upc = !empty($_variants->upc)?$_variants->upc:'';

                $variants_qty = $_variants->inventory_level;
                $product_quantity += intval($_variants->inventory_level);
//                if ($product_quantity > 0) {
//                    $stock_status = 1;
//                } else {
//                    $stock_status = 0;
//                }
                $variants_weight = $_variants->weight;

                //$variants_created_at = $_variants['created_at'];
                //$variants_updated_at = $_variants['updated_at'];
                //$variants_title_value = $_variants['title'];

                $variants_options = [];

                $skuOptions = $_variants->options;
                foreach ($skuOptions as $eachSkuOption){
                    $skuOptionId = $eachSkuOption->product_option_id;
                    $skuOptionValueId = $eachSkuOption->option_value_id;

                    $skuOption = Bigcommerce::getProductOption($p_id, $skuOptionId);
                    $optionId = $skuOption->option_id;

                    $skuValue = Bigcommerce::getOptionValue($optionId, $skuOptionValueId);

                    $skuOptionName = $skuOption->display_name;

                    $value_data = [
                        'label' => $skuValue->label,
                        'value' => $skuValue->value
                    ];
                    $oneVariantOption = [
                        'name' => $skuOptionName,
                        'value' => $value_data
                    ];

                    $variants_options[] = $oneVariantOption;

                }

                $oneVariantData = [
                    'connection_variation_id' => $variants_id,
                    'sku_key' => 'sku',
                    'sku_value' => $variant_sku,
                    'inventory_key' => 'inventory_level',
                    'inventory_value' => $variants_qty,
                    'price_key' => 'price',
                    'price_value' => $variants_price,
                    'weight_key' => 'weight',
                    'weight_value' => $variants_weight,
                    'upc' => $variants_upc,
                    'options' => $variants_options,
                ];
                $variants_data[] = $oneVariantData;
            }
        }

        if ( $product_quantity > 0 ){
            $real_p_qty = $product_quantity;
        } else {
            $real_p_qty = $p_stk_lvl;
        }

        $product_data = [
            'user_id' => $user_id, // Elliot user id
            'name' => $p_name, // Product name
            'sku' => $p_sku, // Product SKU
            'url' => $product_url, // Product url if null give blank value
            'upc' => $p_upc, // Product barcode if any
            'ean' => '', // Product ean if any
            'jan' => '', // Product jan if any
            'isbn' => '', // Product isban if any
            'mpn' => '', // Product mpn if any
            'condition' => $p_condition, // Product mpn if any
            'description' => $p_des, // Product Description
            'weight' => $p_weight, // Product weight if null give blank value
            'stock_quantity' => $real_p_qty, //Product quantity
            'country_code' => $country_code, // store or channel country code
            'currency' => $currency,
            'stock_level' => ($real_p_qty>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK,
            'stock_status' => ($p_visibility)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN,
            'low_stock_notification' => $p_stk_warning_lvl, // Porduct low stock notification if any otherwise give default 5 value
            'price' => $p_price, // Porduct price
            'sales_price' => $p_saleprice, // Product sale price if null give Product price value
            'status' => ($product_status>0)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
            'published' => $p_published,
            'permanent_hidden' => Product::STATUS_NO,
            'user_connection_id' => $user_connection_id,
            'connection_product_id' => $p_id, // Stores/Channel product ID
            'created_at' => $p_created_date, // Product created at date format date('Y-m-d H:i:s')
            'updated_at' => $p_updated_date, // Product updated at date format date('Y-m-d H:i:s')
            'type' => $p_type, // Product type
            'images' => $product_image_data, // Product images data
            'websites' => array(), //This is for only magento give only and blank array
            'conversion_rate' => $conversion_rate,
            'brand' => $p_brand, // Product brand if any
            'categories' => $product_categories_ids, // Product categroy array. If null give a blank array
            'variations' => $variants_data,
            'options_set' => $options_set_data,
        ];

        if ( !empty($product_data) ) {
            Product::productImportingCommon($product_data);
        }

    }

    public static function bgcProductImport($user_connection_id, $conversion_rate = 1){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $store_bigcommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $bigCommerceSetting = $store_bigcommerce->connection_info;
        $bgcdomain = $store_bigcommerce->userConnectionDetails->store_url;
        $user_company = $store_bigcommerce->user->company;
        $user_id = $store_bigcommerce->user_id;
        $currency = $store_bigcommerce->userConnectionDetails->currency;
        $country_code = $store_bigcommerce->userConnectionDetails->country_code;

        Bigcommerce::configure($bigCommerceSetting);


        // To count all Products
        $totalCount = Bigcommerce::getProductsCount();
        $pageCount = ceil($totalCount / 250);
        //To list all the products
        for ($page = 1; $page <= $pageCount; $page ++) {
            sleep(self::timeOutLimit);
            $pageFilter = [
                "page" => $page,
                "limit" => 250
            ];
            $products = Bigcommerce::getProducts($pageFilter);
            if (!empty($products)) {

                foreach ($products as $product) {

                    $extraData = [
                        'conversion_rate' => $conversion_rate,
                        'user_connection_id' => $user_connection_id,
                        'user_company' => $user_company,
                        'bgcdomain' => $bgcdomain,
                        'user_id' => $user_id,
                        'country_code' => $country_code,
                        'currency' => $currency,
                        'bigCommerceSetting' => $bigCommerceSetting,
                    ];
                    BigcommerceComponent::bgcUpsertProduct($product, $extraData);

                }
            }
        }

    }

    public static function bgcUpsertCustomer($customers_data, $extra_data){

        $user_id = $extra_data['user_id'];
        $user_connection_id = $extra_data['user_connection_id'];

        $customer_id = $customers_data->id;

        $first_name = $customers_data->first_name;
        $last_name = $customers_data->last_name;
        $customer_email = $customers_data->email;
        $customer_phone = $customers_data->phone;
        $create_at = $customers_data->date_created;
        $update_at = $customers_data->date_modified;

        $customer_addresses = [];
        $customer_addrs = $customers_data->addresses;
        if ( !empty($customer_addrs) ) {

            $cus_addr_index = 0;
            foreach ($customer_addrs as $eachAddr){

                if ( $cus_addr_index > 0 ) {
                    break;
                }
                $addr_fname = isset($eachAddr->first_name) ? $eachAddr->first_name : "";
                $addr_lname = isset($eachAddr->last_name) ? $eachAddr->last_name : "";
                $addr_company = isset($eachAddr->company) ? $eachAddr->company : "";
                $addr_street1 = isset($eachAddr->street_1) ? $eachAddr->street_1 : "";
                $addr_street2 = isset($eachAddr->street_2) ? $eachAddr->street_2 : "";
                $addr_city = isset($eachAddr->city) ? $eachAddr->city : "";
                $addr_country = isset($eachAddr->country) ? $eachAddr->country : "";
                $addr_country_iso = isset($eachAddr->country_iso2) ? $eachAddr->country_iso2 : "";
                $addr_state = isset($eachAddr->state) ? $eachAddr->state : "";
                $addr_zip = isset($eachAddr->zip) ? $eachAddr->zip : "";
                $addr_phone = isset($eachAddr->phone) ? $eachAddr->phone : "";
                $addr_add_type = isset($eachAddr->address_type) ? $eachAddr->address_type : "";

                $oneCustomerAddr = [
                    'first_name' => $addr_fname,
                    'last_name' => $addr_lname,
                    'company' => $addr_company,
                    'country' => $addr_country,
                    'country_iso' => $addr_country_iso,
                    'street_1' => $addr_street1,
                    'street_2' => $addr_street2,
                    'state' => $addr_state,
                    'city' => $addr_city,
                    'zip' => $addr_zip,
                    'phone' => $addr_phone,
                    'address_type' => $addr_add_type,

                ];

                $customer_addresses[] = $oneCustomerAddr;

                $cus_addr_index ++;
            }

        }

        $customer_data = [
            'connection_customerId' => $customer_id,
            'user_id' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $customer_email,
            'phone' => $customer_phone,
            'user_connection_id' => $user_connection_id,
            'customer_created' => $create_at,
            'updated_at' => $update_at,
            'addresses' => $customer_addresses
        ];
        Customer::customerImportingCommon($customer_data);

    }

    public static function bgcCustomerImport($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $store_bigcommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $bigCommerceSetting = $store_bigcommerce->connection_info;
        $user_id = $store_bigcommerce->user_id;

        Bigcommerce::configure($bigCommerceSetting);


        /* Fetching All  Customers from BigCommerce Store */
        $customers_count = Bigcommerce::getCustomersCount();
        $pageCount = ceil($customers_count / 250);
        //To list all the customers
        for ($page = 1; $page <= $pageCount; $page ++) {
            sleep(self::timeOutLimit);
            $pageFilter = [
                "page" => $page,
                "limit" => 250
            ];

            $customers = Bigcommerce::getCustomers($pageFilter);


            if (!empty($customers)) {
                foreach ($customers as $customers_data) {
                    //get customer Adderss
                    $extra_data = [
                        'user_connection_id' => $user_connection_id,
                        'user_id' => $user_id
                    ];
                    self::bgcUpsertCustomer($customers_data, $extra_data);
                }
            }
        }


    }


    public static function bgcOrderImport($user_connection_id, $conversion_rate = 1){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $store_bigcommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $bigCommerceSetting = $store_bigcommerce->connection_info;

        $user_id = $store_bigcommerce->user_id;
        $currency = $store_bigcommerce->userConnectionDetails->currency;
        $country_code = $store_bigcommerce->userConnectionDetails->country_code;

        Bigcommerce::configure($bigCommerceSetting);



        $orders_total_count = Bigcommerce::getOrdersCount();

        $pageCount = ceil($orders_total_count / 250);
        //To list all the customers
        for ($page = 1; $page <= $pageCount; $page ++) {
            sleep(self::timeOutLimit);
            $pageFilter = [
                "page" => $page,
                "limit" => 250
            ];

            $orders = Bigcommerce::getOrders($pageFilter);
            /* For Orders */
            if (!empty($orders)) {
                foreach ($orders as $orders_data) {

                    $extraData = [
                        'conversion_rate' => $conversion_rate,
                        'user_connection_id' => $user_connection_id,
                        'user_id' => $user_id,
                        'currency_code' => $currency,
                        'country_code' => $country_code,
                    ];
                    BigcommerceComponent::bgcUpsertOrder($orders_data, $extraData);
                }
            }

        }

    }

    public static function bgcUpsertOrder($orders_data, $extraParam){

        $conversion_rate = $extraParam['conversion_rate'];
        $user_connection_id = $extraParam['user_connection_id'];
        $user_id = $extraParam['user_id'];
        $currency_code = $extraParam['currency_code'];
        $country_code = $extraParam['country_code'];

        //get BigCommerce Order id
        $bigcommerce_order_id = $orders_data->id;

        $order_status = $orders_data->status;

        switch (ucfirst($order_status)){
            case OrderStatus::REFUNDED :
                $order_status = OrderStatus::REFUNDED;
                break;
            case OrderStatus::IN_TRANSIT :
                $order_status = OrderStatus::IN_TRANSIT;
                break;
            case OrderStatus::PENDING :
                $order_status = OrderStatus::PENDING;
                break;
            case OrderStatus::COMPLETED :
                $order_status = OrderStatus::COMPLETED;
                break;
            case OrderStatus::CANCEL :
                $order_status = OrderStatus::CANCEL;
                break;
            case OrderStatus::ON_HOLD :
                $order_status = OrderStatus::ON_HOLD;
                break;
            default:
                break;
        }

        $product_qauntity = $orders_data->items_total;

        //billing Address
        $bill_street_1 = isset($orders_data->billing_address->street_1) ? $orders_data->billing_address->street_1 : '';
        $bill_street_2 = isset($orders_data->billing_address->street_2) ? $orders_data->billing_address->street_2 : '';
        $bill_city = isset($orders_data->billing_address->city) ? $orders_data->billing_address->city : '';
        $bill_state = isset($orders_data->billing_address->state) ? $orders_data->billing_address->state : '';
        $bill_zip = isset($orders_data->billing_address->zip) ? $orders_data->billing_address->zip : '';
        $bill_country = isset($orders_data->billing_address->country) ? $orders_data->billing_address->country : '';
        $bill_country_iso = isset($orders_data->billing_address->country_iso2) ? $orders_data->billing_address->country_iso2 : '';
        $get_email = isset($orders_data->billing_address->email) ? $orders_data->billing_address->email : "";
        $get_phone = isset($orders_data->billing_address->phone) ? $orders_data->billing_address->phone : "";
        $bill_first_name = isset($orders_data->billing_address->first_name) ? $orders_data->billing_address->first_name : "";
        $bill_last_name = isset($orders_data->billing_address->last_name) ? $orders_data->billing_address->last_name : "";
        $bill_company = isset($orders_data->billing_address->company) ? $orders_data->billing_address->company : "";

        //Fetch Ship details
        //$ship_details = Bigcommerce::getCollection('/orders/' . $orders_data->id . '/shipping_addresses', 'Order');
        $ship_details = $orders_data->shipping_addresses;
        //Shipping Address
        $ship_fname = isset($ship_details[0]->first_name) ? $ship_details[0]->first_name : $bill_first_name;
        $ship_lname = isset($ship_details[0]->last_name) ? $ship_details[0]->last_name : $bill_last_name;
        $ship_phone = isset($ship_details[0]->phone) ? $ship_details[0]->phone : $get_phone;
        $ship_company = isset($ship_details[0]->company) ? $ship_details[0]->company : $bill_company;
        $ship_street_1 = isset($ship_details[0]->street_1) ? $ship_details[0]->street_1 : $bill_street_1;
        $ship_street_2 = isset($ship_details[0]->street_2) ? $ship_details[0]->street_2 : $bill_street_2;
        $ship_city = isset($ship_details[0]->city) ? $ship_details[0]->city : $bill_city;
        $ship_state = isset($ship_details[0]->state) ? $ship_details[0]->state : $bill_state;
        $ship_zip = isset($ship_details[0]->zip) ? $ship_details[0]->zip : $bill_zip;
        $ship_country = isset($ship_details[0]->country) ? $ship_details[0]->country : $bill_country;
        $ship_country_iso = isset($ship_details[0]->country_iso2) ? $ship_details[0]->country_iso2 : $bill_country_iso;

        $total_amount = $orders_data->total_inc_tax;
        $base_shipping_cost = $orders_data->base_shipping_cost;
        $shipping_cost_tax = $orders_data->shipping_cost_tax;
        $base_handling_cost = $orders_data->base_handling_cost;
        $handling_cost_tax = $orders_data->handling_cost_tax;
        $base_wrapping_cost = $orders_data->base_wrapping_cost;
        $wrapping_cost_tax = $orders_data->wrapping_cost_tax;
        $payment_method = $orders_data->payment_method;
        $payment_provider_id = $orders_data->payment_provider_id;
        $payment_status = $orders_data->payment_status;
        $refunded_amount = $orders_data->refunded_amount;
        $discount_amount = $orders_data->discount_amount;
        $coupon_discount = $orders_data->coupon_discount;
        $order_date = $orders_data->date_created;
        $order_last_modified_date = $orders_data->date_modified;

        $order_product_data = array();
        //$product_dtails = Bigcommerce::getCollection('/orders/' . $orders_data->id . '/products', 'Order');
        $product_dtails = $orders_data->products;
        foreach ($product_dtails as $product_dtails_data) {
            $product_id = $product_dtails_data->product_id;
            $product_name = $product_dtails_data->name;
            $product_qty = $product_dtails_data->quantity;
            $order_product_sku = $product_dtails_data->sku;
            $price = $product_dtails_data->total_inc_tax;
            $product_weight = $product_dtails_data->weight;
            $order_product_data[] = [
                'user_id' => $user_id,
                'connection_product_id' => $product_id,
                'name' => $product_name,
                'order_product_sku' => $order_product_sku,
                'price' => $price,
                'qty' => $product_qty,
                'weight' => $product_weight,
            ];
        }

        $order_customer_id = $orders_data->customer_id;  //0

        $customer_data = [
            'connection_customerId' => $order_customer_id,
            'user_id' => $user_id,
            'first_name' => $bill_first_name,
            'last_name' => $bill_last_name,
            'email' => $get_email,
            'phone' => $get_phone,
            'user_connection_id' => $user_connection_id,
            'customer_created' => $order_date,
            'updated_at' => $order_date,
            'addresses' => [
                [
                    'first_name' => $bill_first_name,
                    'last_name' => $bill_last_name,
                    'company' => $bill_company,
                    'country' => $bill_country,
                    'country_iso' => $bill_country_iso,
                    'street_1' => $bill_street_1,
                    'street_2' => $bill_street_2,
                    'state' => $bill_state,
                    'city' => $bill_city,
                    'zip' => $bill_zip,
                    'phone' => $get_phone,
                    'address_type' => 'Temporary',
                ],
            ]
        ];




        $order_data = [
            'user_id' => $user_id,
            'user_connection_id' => $user_connection_id,
            'connection_order_id' => $bigcommerce_order_id,
            'status' => $order_status,
            'product_quantity' => $product_qauntity,
            'ship_fname' => $ship_fname,
            'ship_lname' => $ship_lname,
            'ship_phone' => $ship_phone,
            'ship_company' => $ship_company,
            'ship_street_1' => $ship_street_1,
            'ship_street_2' => $ship_street_2,
            'ship_state' => $ship_state,
            'ship_city' => $ship_city,
            'ship_country' => $ship_country,
            'ship_country_iso' => $ship_country_iso,
            'ship_zip' => $ship_zip,
            'bill_fname' => $bill_first_name,
            'bill_lname' => $bill_last_name,
            'bill_phone' => $get_phone,
            'bill_company' => $bill_company,
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
                'payment_status' => $payment_status,
                'payment_provider' => $payment_provider_id,
                'coupon_discount' => $coupon_discount,
                'base_handling_cost' => $base_handling_cost,
                'handling_cost_tax' => $handling_cost_tax,
                'base_wrapping_cost' => $base_wrapping_cost,
                'wrapping_cost_tax' => $wrapping_cost_tax,
            ],
            'total_amount' => $total_amount,
            'order_date' => $order_date,
            'created_at' => $order_date,
            'updated_at' => $order_last_modified_date,
            'customer_email' => $get_email,
            'currency_code' => $currency_code,
            'country_code' => $country_code,
            'conversion_rate' => $conversion_rate,
            'order_products' => $order_product_data,
            'order_customer' => $customer_data
        ];

        if ( !empty($order_data) ){
            Order::orderImportingCommon($order_data);
        }

    }


    public static function bgcUpstreamProduct($user_connection_id, $product_id, $is_update = true, $p_variant_id = 0){

        if ( $is_update ){
            return self::bgcUpdateProduct($user_connection_id, $product_id, $p_variant_id);
        }

        return self::bgcNewProduct($user_connection_id, $product_id);

    }

    public static function bgcUpdateProduct($user_connection_id, $product_id, $p_variant_id = 0){

        $store_bigcommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $response = [
            'success' => false,
            'product_id' => $product_id,
            'connection_product_id' => 0,
            'user_connection_id' => $user_connection_id,
            'message' => "Couldn't find a user connection."
        ];


        if ( !empty($store_bigcommerce) ) {

            $bigCommerceSetting = $store_bigcommerce->connection_info;
            $store_connection_details = $store_bigcommerce->userConnectionDetails;

            $importUser = $store_bigcommerce->user;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';

            $bgcdomain = $store_bigcommerce->userConnectionDetails->store_url;

            $store_currency_code = $store_connection_details->currency;

            $conversion_rate = 1;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($userCurrency, $store_currency_code);
            }


            Bigcommerce::configure($bigCommerceSetting);

            if ( $p_variant_id > 0 ){

                $productModel = Product::findOne(['id' => $product_id]);

                $product_price = 0;
                $product_sale_price = 0;
                if ( !empty($productModel) ){

                    $product_price = $productModel->price;
                    $product_sale_price = $productModel->sales_price;

                    if ( !empty($product_price) ){
                        $product_price = number_format((float)$conversion_rate * $product_price, 2, '.', '');
                    } else {
                        $product_price = 0;
                    }

                    if ( !empty($product_sale_price) ){
                        $product_sale_price = number_format((float)$conversion_rate * $product_sale_price, 2, '.', '');
                    } else {
                        $product_sale_price = 0;
                    }

                }

                $pVariantModel = ProductVariation::findOne(['id' => $p_variant_id]);

                if ( !empty($pVariantModel) ){

                    $sku_key = $pVariantModel->sku_key;
                    $sku_value = $pVariantModel->sku_value;

                    //$inventory_key = $pVariantModel->inventory_key;
                    $inventory_value = $pVariantModel->inventory_value;

                    //$price_key = $pVariantModel->price_key;
                    $price_value = $pVariantModel->price_value;

                    if ( !empty($price_value) ){
                        $price_value = number_format((float)$conversion_rate * $price_value, 2, '.', '');
                    } else {
                        $price_value = 0;
                    }


                    //$weight_key = $pVariantModel->weight_key;
                    $weight_value = $pVariantModel->weight_value;

                    if ( $price_value == 0 ){

                        if ( $product_price > 0 ){
                            $price_value = $product_price;
                        }

                    }

                    $updateParam = [
                        'inventory_level' => $inventory_value,
                        'inventory_warning_level' => Product::LOW_STOCK_NOTIFICATION,
                        'price' => $price_value,
                        'weight' => $weight_value,
                    ];

                    $bgcConnectionProduct = ProductConnection::findOne([
                        'user_connection_id' => $user_connection_id,
                        'product_id' => $product_id
                    ]);

                    if ( !empty($bgcConnectionProduct) ){
                        $bgcProductId = $bgcConnectionProduct->connection_product_id;
                        if ( $bgcProductId > 0 ){

                            $bgcVariantId = $pVariantModel->connection_variation_id;
                            Bigcommerce::updateProductSkuById($bgcProductId, $bgcVariantId, $updateParam);

                            $response['success'] = true;
                            $response['connection_product_id'] = $bgcProductId;
                            $response['message'] = "";

                        }


                    }

                }

            } else {

                $product = Product::findOne(['id' => $product_id]);

                if ( !empty($product) ){

                    $bgcConnectionProduct = ProductConnection::findOne([
                        'user_connection_id' => $user_connection_id,
                        'product_id' => $product_id
                    ]);

                    if ( !empty($bgcConnectionProduct) ){

                        $connection_product_id = $bgcConnectionProduct->connection_product_id;


                        $product_url = str_replace($bgcdomain, '', $product->url);

                        $product_price = $product->price;
                        if ( !empty($product_price) ){
                            $product_price = number_format((float)$conversion_rate * $product_price, 2, '.', '');
                        } else {
                            $product_price = 0;
                        }


                        $product_sale_price = $product->sales_price;
                        if ( !empty($product_sale_price) ){
                            $product_sale_price = number_format((float)$conversion_rate * $product_sale_price, 2, '.', '');
                        } else {
                            $product_sale_price = 0;
                        }


                        $updateParam = [
                            'name' => $product->name,
                            'sku' => $product->sku,
                            'description' => $product->description,
                            'custom_url' => $product_url,
                            'upc' => $product->upc,
                            'condition' => $product->condition,
                            'weight' => $product->weight,
                            'inventory_warning_level' => $product->low_stock_notification,
                            'price' => $product_price,
                            'sale_price' => $product_sale_price,
                            'inventory_level' => $product->stock_quantity,
                            'is_visible' => ($product->stock_status === Product::STOCK_STATUS_VISIBLE)?true:false,
                        ];

                        if ( ($product->status === Product::STATUS_ACTIVE) ){
                            $updateParam['availability'] = 'available';
                        }

                        Bigcommerce::updateProduct($connection_product_id, $updateParam);

                        $response['success'] = true;
                        $response['connection_product_id'] = $connection_product_id;
                        $response['message'] = "";

                    }

                }

            }

        }

        return json_encode($response, JSON_UNESCAPED_UNICODE);


    }

    public static function bgcNewProduct($user_connection_id, $product_id){

        $store_bigcommerce = UserConnection::findOne(['id' => $user_connection_id]);

        $response = [
            'success' => false,
            'product_id' => $product_id,
            'connection_product_id' => 0,
            'user_connection_id' => $user_connection_id,
            'message' => "Couldn't find a user connection."
        ];


        if ( !empty($store_bigcommerce) ) {

            $bigCommerceSetting = $store_bigcommerce->connection_info;
            $store_connection_details = $store_bigcommerce->userConnectionDetails;

            $importUser = $store_bigcommerce->user;
            $userCurrency = isset($importUser->currency)?$importUser->currency:'USD';

            $bgcdomain = $store_bigcommerce->userConnectionDetails->store_url;

            $store_currency_code = $store_connection_details->currency;

            $conversion_rate = 1;
            if ($store_currency_code != '') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($userCurrency, $store_currency_code);
            }

            Bigcommerce::configure($bigCommerceSetting);

            $product = Product::findOne(['id' => $product_id]);

            if ( !empty($product) ){

                $product_price = $product->price;
                if ( !empty($product_price) ){
                    $product_price = number_format((float)$conversion_rate * $product_price, 2, '.', '');
                } else {
                    $product_price = 0;
                }

                $stock_manage = $product->stock_manage;

                $product_sale_price = $product->sales_price;
                if ( !empty($product_sale_price) ){
                    $product_sale_price = number_format((float)$conversion_rate * $product_sale_price, 2, '.', '');
                } else {
                    $product_sale_price = 0;
                }

                $productCategories = ProductCategory::findAll([
                    'product_id' => $product_id
                ]);

                $category_connectionId_array = [];
                $p_category_result = [];
                if ( !empty($productCategories) ){

                    foreach ($productCategories as $productCategory){

                        $categoryName = $productCategory->category->name;
                        $category_connectionId = $productCategory->category->connection_category_id;

                        $p_one_cat = [
                            "category_name" => $categoryName,
                            "connection_category_id" => $category_connectionId,
                        ];
                        $p_category_result[] = $p_one_cat;
                    }

                }

                if (empty($p_category_result)) {

                    $response['message'] = "Unable to add to BigCommerce, please add a category in order to publish.";
                    return json_encode($response, JSON_UNESCAPED_UNICODE);

                } else {

                    foreach ($p_category_result as $p_category){

                        $bgcCategoryFilter = [
                            "name" => $p_category['category_name']
                        ];
                        $bgc_category_result = Bigcommerce::getCategories($bgcCategoryFilter);

                        if ( !empty($bgc_category_result) ){

                            foreach ($bgc_category_result as $bgc_category){
                                $cat_id = $bgc_category->id;
                                break;
                            }

                        } else {

                            $newCategoryObject = [
                                "name" => $categoryName
                            ];
                            $category_result = Bigcommerce::createCategory($newCategoryObject);

                            if ( !empty($category_result) && isset($category_result->id) ){
                                $cat_id = $category_result->id;
                            }

                        }

                        if ( isset($cat_id) ){
                            array_push($category_connectionId_array, $cat_id);
                        }

                    }


                }

                $p_inventory_level = $product->stock_quantity;
                if ( $stock_manage === Product::STOCK_MANAGE_NO ){
                    $p_inventory_level = 0;
                }
                $postParam = [
                    'name' => $product->name,
                    'price' => $product_price,
                    'sale_price' => $product_sale_price,
                    'weight' => $product->weight,
                    'description' => $product->description,
                    'upc' => $product->upc,
                    'sku' => $product->sku,
                    'inventory_warning_level' => $product->low_stock_notification,
                    'inventory_level' => $p_inventory_level,
                    'condition' => $product->condition,
                    'availability' => 'available',
                    'inventory_tracking' => 'simple',
                    'is_visible' => true,
                    'categories' => $category_connectionId_array,
                    'type' => 'physical',
                ];

                $createBgcResult = Bigcommerce::createProduct($postParam);
                if ( !empty( $createBgcResult ) && isset( $createBgcResult->id ) ){

                    $connection_product_id = $createBgcResult->id;
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

                    $p_var_count = 0;
                    $p_bgc_option_set_flag = false;
                    $bgc_options_set_id = 0;
                    foreach ($productVariants as $productVariant){

                        $sku_value = $productVariant->sku_value;
                        $inventory_value = $productVariant->inventory_value;

                        if ( $stock_manage === Product::STOCK_MANAGE_NO ){
                            $inventory_value = 0;
                        }

                        $price_value = $productVariant->price_value;
                        $weight_value = $productVariant->weight_value;

                        $p_variant_set_id = $productVariant->variation_set_id;

                        if ( !$p_bgc_option_set_flag && !empty($p_variant_set_id) ){

                            $bgc_options_set_id = self::bgcOptionSet($user_connection_id, $p_variant_set_id);

                            if ( !empty($bgc_options_set_id) ){

                                $updateProductObject = [
                                    "option_set_id" => $bgc_options_set_id
                                ];
                                Bigcommerce::updateProduct($connection_product_id, $updateProductObject);

                                $p_bgc_option_set_flag = true;
                            }

                        }

                        $p_variant_id = $productVariant->variation_id;

                        if ( !empty($price_value) ){
                            $price_value = number_format((float)$conversion_rate * $price_value, 2, '.', '');
                        } else {
                            $price_value = 0;
                        }

                        if ( $price_value == 0 ){
                            if ( $product_price > 0 ){
                                $price_value = $product_price;
                            }
                        }
                        $p_variant_Param = [
                            'sku' => $sku_value,
                            'inventory_level' => $inventory_value,
                            'inventory_warning_level' => Product::LOW_STOCK_NOTIFICATION,
                            'price' => $price_value,
                            'weight' => $weight_value,
                        ];

                        $p_options = self::bgcOption($user_connection_id, $p_variant_id, $bgc_options_set_id, $connection_product_id);

                        if ( !empty($p_options) ){
                            $p_variant_Param['options'] = $p_options;
                        }
                        $create_sku_result = Bigcommerce::createSku($connection_product_id, $p_variant_Param);
                        if ( !empty($create_sku_result) && isset($create_sku_result->id) ){

                            if ( $productVariant->connection_variation_id === '-1' ){
                                $productVariant->connection_variation_id = $create_sku_result->id;
                                $productVariant->user_connection_id = $user_connection_id;
                                $productVariant->save(false);
                            }
                        }

                        $p_var_count ++;
                    }

                    $productImages = ProductImage::findAll([
                        'product_id' => $product_id
                    ]);

                    if ( !empty($productImages) ){

                        foreach ($productImages as $productImage) {

                            $p_image = [
                                'image_file' => $productImage->link
                            ];
                            Bigcommerce::createProductImage($connection_product_id, $p_image);
                        }
                    }

                    $response['success'] = true;
                    $response['connection_product_id'] = $connection_product_id;
                    $response['message'] = "";
                }

            }

        }
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }


    public static function bgcDeleteProduct($user_connection_id, $connection_product_id){

        $store_bigcommerce = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($store_bigcommerce) ) {

            $bigCommerceSetting = $store_bigcommerce->connection_info;

            Bigcommerce::configure($bigCommerceSetting);

            Bigcommerce::deleteProduct($connection_product_id);

            return true;

        }


        return false;
    }

    public static function bgcOption($user_connection_id, $p_option_id, $bgc_option_set_id, $connection_product_id){

        $p_options = [];

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $bigCommerceSetting = $userConnection->connection_info;

        Bigcommerce::configure($bigCommerceSetting);


        $variation = Variation::findOne(['id' => $p_option_id]);
        if ( !empty($variation) ){

            $variation_items = $variation->items;
            $split_values = explode('-', $variation_items);
            foreach ($split_values as $split_value){

                $option_id = 0;
                $option_value_id = 0;

                $valueObject = VariationValue::findOne(['id' => $split_value]);

                if ( !empty($valueObject) ){
                    $value = $valueObject->value;
                    $value_label = !empty($valueObject->label)?$valueObject->label:$value;
                    $variation_item_id = $valueObject->variation_item_id;

                    $value_item = VariationItem::findOne(['id' => $variation_item_id]);
                    if ( !empty($value_item) ){

                        $value_item_name = ucfirst(strtolower(trim($value_item->name)));

                        $pageFilter = [
                            "display_name" => $value_item_name,
                            "limit" => 250,
                        ];

                        $options = Bigcommerce::getOptions($pageFilter);
                        if (!empty($options)) {
                            foreach ($options as $option) {

                                $opt_id = $option->id;
                                $opt_name = $option->name;
                                $opt_disp_name = $option->display_name;
                                $opt_type = $option->type;

                                if ( $opt_disp_name === $value_item_name ){
                                    $option_id = $opt_id;

                                    $bgc_option_values = Bigcommerce::getOptionValuesById($option_id);

                                    foreach ($bgc_option_values as $bgc_option_value){
                                        $bgc_opt_value_id = $bgc_option_value->id;
                                        $bgc_opt_value_name = $bgc_option_value->value;

                                        if ( $bgc_opt_value_name == $value ){
                                            $option_value_id = $bgc_opt_value_id;
                                            break;
                                        }
                                    }

                                    break;
                                }
                            }
                        }

                        if ( $option_id == 0 ){

                            $createOption = [
                                "name" => $value_item_name,
                                "display_name" => $value_item_name,
                                "type" => "CS"
                            ];

                            $bgc_option_response = Bigcommerce::createOption($createOption);

                            if ( !empty($bgc_option_response) && isset($bgc_option_response->id) ){
                                $option_id = $bgc_option_response->id;



                            }
                        }

                        if ( $option_value_id == 0 ){

                            $createOptionValue = [
                                "label" => $value_label,
                                "value" => $value,
                            ];

                            $bgc_option_value_response = Bigcommerce::createOptionValue($option_id, $createOptionValue);

                            if ( !empty($bgc_option_value_response) && isset($bgc_option_value_response->id) ){
                                $option_value_id = $bgc_option_value_response->id;
                            }

                        }

                        if ( $option_id > 0 && $option_value_id > 0 ){

                            $bgc_optionsSet_result = Bigcommerce::getOptionsByOptionsSetId($bgc_option_set_id);
                            $is_new_option = true;
                            if ( !empty($bgc_optionsSet_result) ){

                                foreach ($bgc_optionsSet_result as $bgc_optionsSet){

                                    $exist_option_id = $bgc_optionsSet->option_id;
                                    if ( $option_id == $exist_option_id ){
                                        $is_new_option = false;
                                        break;
                                    }

                                }

                            }
                            if ( $is_new_option ){

                                $bgc_optionsSet_created = Bigcommerce::createOptionSetOption(["option_id" => $option_id], $bgc_option_set_id);

                            }


                            $product_options = Bigcommerce::getProductOptions($connection_product_id);

                            if ( !empty($product_options) ){

                                foreach ($product_options as $product_option){

                                    $bgc_product_option_id = $product_option->option_id;
                                    if ( $option_id == $bgc_product_option_id ){
                                        $product_option_id = $product_option->id;
                                        break;
                                    }

                                }


                                $p_options[] = [
                                    "product_option_id" => $product_option_id,
                                    "option_value_id" => $option_value_id
                                ];

                            }


                        }

                    }

                }
            }

        }

        return $p_options;
    }

    public static function bgcOptionSet($user_connection_id, $p_option_set_id){

        $option_set_id = null;

        $userConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $bigCommerceSetting = $userConnection->connection_info;

        Bigcommerce::configure($bigCommerceSetting);


        $variationSet = VariationSet::findOne(['id' => $p_option_set_id]);

        if ( !empty($variationSet) ){

            $option_set_name = $variationSet->name;

            $optionSetObject = [
                "name" => $option_set_name
            ];

            $bgc_option_set_result = Bigcommerce::getOptionSets($optionSetObject);

            if ( !empty($bgc_option_set_result) ){

                foreach ( $bgc_option_set_result as $set_result ){

                    $option_set_id = $set_result->id;

                }

            } else {

                $bgc_create_set_result = Bigcommerce::createOptionSet($optionSetObject);

                if ( !empty($bgc_create_set_result) && isset($bgc_create_set_result->id) ){
                    $option_set_id = $bgc_create_set_result->id;
                }

            }

        }

        return $option_set_id;
    }

}