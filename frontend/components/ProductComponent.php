<?php

namespace frontend\components;

use common\models\Product;
use common\models\Connection;
use common\models\UserConnection;
use common\models\User;
use yii\base\Component;
use frontend\components\ElliShopifyClient as Shopify;
use frontend\components\ElliBigCommerce as BigCommerce;

class ProductComponent extends Component
{


    public static function importProductFromOrder($productId, $connectionName, $connectionId) {
        if (!empty($productId)){
            switch ($connectionName){
                case 'Shopify':
                    return self::importShopifyProduct($productId, $connectionName, $connectionId);
                    break;
                case 'BigCommerce':
                    return self::importBigCommerceProduct($productId, $connectionId);
                    break;
            }

        }

        return 0;

    }

    private static function importShopifyProduct($productId, $connectionName, $connectionId){

        $shopifyProduct = Products::findOne(['connection_product_id' => $productId, 'connection_id' => $connectionId]);

        if (empty($shopifyProduct)){

            $shopifyConnInfo = StoresConnection::findOne(['stores_connection_id' => $connectionId]);


            $shopify_shop = $shopifyConnInfo->url;
            $shopify_pass = $shopifyConnInfo->key_password;
            $shopify_api = $shopifyConnInfo->api_key;
            $shopify_access_token = $shopifyConnInfo->shared_secret;
            $user_id = $shopifyConnInfo->user_id;

            $elliShopify = new Shopify($shopify_shop, $shopify_pass, $shopify_api, $shopify_access_token);

            $store_details = $elliShopify->call('GET', '/admin/shop.json');
            $call_left = $elliShopify->callsLeft();
            if ($call_left <= 8) {
                sleep(10);
            }

            $shopify_country_name = $store_details['country_name'];
            $store_country_code = $store_details['country_code'];
            $store_currency_code = $store_details['currency'];

            if ($store_currency_code != '') {

                $conversion_rate = Stores::getCurrencyConversionRate($store_currency_code, 'USD');
            }


            $_product = $elliShopify->call('GET', '/admin/products/' . $productId . '.json');

            if (!empty($_product)) {

                $product_id = $_product['id'];
                $title = $_product['title'];
                $description = $_product['body_html'];
                $product_handle = $_product['handle'];
                $product_type = $_product['product_type'];
                $product_url = 'https://' . $shopify_shop . '/products/' . $product_handle;
                $created_date = date('Y-m-d h:i:s', strtotime($_product['created_at']));
                $updated_date = date('Y-m-d h:i:s', strtotime($_product['updated_at']));
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
                        'image_url' => $image_src,
                        'label' => '',
                        'position' => $image_position,
                        'base_img' => $base_image,
                    );
                }

                $count = 1;
                $product_quantity = 0;

                $elliotProductId = 0;
                $storeId = 0;
                $product_data = [];
                foreach ($product_variants as $_variants) {
                    $variants_id = $_variants['id'];
                    $variants_price = $_variants['price'];

                    $variant_sku = ($_variants['sku'] == '') ? $variant_sku = '' : $_variants['sku'];
                    $variants_barcode = $_variants['barcode'];
                    if ($variants_barcode == '') {
                        $variants_barcode = '';
                    }
                    //$variants_qty = $_variants['inventory_quantity'];
                    $product_quantity += intval($_variants['inventory_quantity']);
                    if ($product_quantity > 0) {
                        $stock_status = 1;
                    } else {
                        $stock_status = 0;
                    }
                    $variants_weight = $_variants['weight'];
                    $variants_created_at = $_variants['created_at'];
                    $variants_updated_at = $_variants['updated_at'];
                    $variants_title_value = $_variants['title'];



                    if ($count == 1) {
                        $store_id = Stores::find()->select('store_id')->where(['store_name' => $connectionName])->one();
                        $storeId = $store_id->store_id;
                        $product_data = [
                            'product_id' => $product_id, // Stores/Channel product ID
                            'name' => $title, // Product name
                            'sku' => $variant_sku, // Product SKU
                            'type' => $product_type, // Product Type
                            'description' => $description, // Product Description
                            'product_url_path' => $product_url, // Product url if null give blank value
                            'weight' => $variants_weight, // Product weight if null give blank value
                            'status' => 1, // Product status (visible or hidden). Give in 0 or 1 form (0 = hidden, 1 = visible).
                            'price' => $variants_price, // Porduct price
                            'sale_price' => $variants_price, // Product sale price if null give Product price value
                            'qty' => $product_quantity, //Product quantity
                            'stock_status' => $stock_status, // Product stock status ("in stock" or "out of stock").
                            // Give in 0 or 1 form (0 = out of stock, 1 = in stock)
                            'websites' => array(), //This is for only magento give only and blank array
                            'brand' => '', // Product brand if any
                            'low_stock_notification' => 5, // Porduct low stock notification if any otherwise give default 5 value
                            'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s')
                            'updated_at' => $updated_date, // Product updated at date format date('Y-m-d H:i:s')
                            'channel_store_name' => $connectionName, // Channel or Store name
                            'channel_store_prefix' => 'SP', // Channel or store prefix id
                            'mul_store_id' => $connectionId, //Give multiple store id
                            'mul_channel_id' => '', // Give multiple channel id
                            'elliot_user_id' => $user_id, // Elliot user id
                            'store_id' => $storeId, // if your are importing store give store id
                            'channel_id' => '', // if your are importing channel give channel id
                            'country_code' => $store_country_code,
                            'currency_code' => $store_currency_code,
                            'conversion_rate' => $conversion_rate,
                            'upc' => $variants_barcode, // Product upc if any
                            'jan' => '', // Product jan if any
                            'isban' => '', // Product isban if any
                            'mpn' => '', // Product mpn if any
                            'categories' => array(), // Product categroy array. If null give a blank array
                            'images' => $product_image_data, // Product images data
                        ];
                    }

                    $count++;
                }
                if ( !empty($product_data) ) {
                    $product_options = $_product['options'];
                    $product_data['qty'] = $product_quantity;
                    $elliotProductId = Stores::productImportingCommon($product_data, $connectionId);
                    if ( $elliotProductId > 0 ) {
                        //if variation check
                        Stores::shopifyProductVariation($elliotProductId, $product_variants, $user_id, $connectionName, $product_options, $connectionId, $product_type);
                    }
                    return $elliotProductId;
                }

            }

            return 0;

        }


        return $shopifyProduct->id;
    }

    private static function importBigCommerceProduct($productId, $connectionId){
        $bigProduct = Products::findOne(['connection_product_id' => $productId, 'connection_id' => $connectionId]);

        if (empty($bigProduct)){

            $get_big_id = Stores::find()->select('store_id')->where(['store_name' => 'BigCommerce'])->one();
            $bigcommerce_store_id = $get_big_id->store_id;

            $bigCommerceConnInfo = StoresConnection::findOne(['stores_connection_id' => $connectionId]);

            $bigCommerceSetting = [
                'client_id' => $bigCommerceConnInfo->big_client_id,
                'auth_token' => $bigCommerceConnInfo->big_access_token,
                'store_hash' => $bigCommerceConnInfo->big_store_hash
            ];

            $userId = $bigCommerceConnInfo->user_id;
            $userInfo = User::findOne(['id' => $userId]);
            $user_company = $userInfo->company_name;

            BigCommerce::configure($bigCommerceSetting);

            $Bgc_store_details = Bigcommerce::getStore();
            $country_code = $Bgc_store_details->country_code;
            $currency_code = $Bgc_store_details->currency;

            if (!empty($Bgc_store_details)) {
                $bgcdoamin = $Bgc_store_details->domain;
            }
            if ($currency_code != '') {

                $conversion_rate = Stores::getCurrencyConversionRate($currency_code, 'USD');
            }


            $product = BigCommerce::getProduct($productId);
            if (!empty($product)) {
                $p_id = $product->id;
                $p_name = $product->name;
                $p_type = $product->type;
                //Give prefix big Commerce Product id
                $prefix_product_id = 'BGC' . $p_id;
                $p_sku = $product->sku;
                $p_upc = $product->upc;
                $p_des = $product->description;
                $p_avail = $product->availability;
                if ($p_avail == 'available' || $p_avail == 'preorder') {
                    $product_status = 1;
                } else {
                    $product_status = 0;
                }

                $p_brand = ucfirst($user_company);
                $product_url = $bgcdoamin . $product->custom_url;
                //$product->brand_id;
                //$product->brand; //object
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
                        $p_image_link = $_image->standard_url;
                        $p_image_label = $_image->description;
                        $p_image_priority = $_image->sort_order;
                        $p_image_created_date = date('Y-m-d H:i:s', strtotime($_image->date_created));
                        $p_image_updated_date = date('Y-m-d H:i:s', strtotime($_image->date_created));

                        $product_image_data[] = array(
                            'image_url' => $p_image_link,
                            'label' => $p_image_label,
                            'position' => $p_image_priority,
                            'base_img' => $p_image_link,
                        );
                    }
                }

                $product_data = [
                    'product_id' => $p_id, // Stores/Channel product ID
                    'name' => $p_name, // Product name
                    'sku' => $p_sku, // Product SKU
                    'type' => $p_type, // Product type
                    'description' => $p_des, // Product Description
                    'product_url_path' => $product_url, // Product url if null give blank value
                    'weight' => $p_weight, // Product weight if null give blank value
                    'status' => $product_status, // Product status (visible or hidden). Give in 0 or 1 form (0 = hidden, 1 = visible).
                    'price' => $p_price, // Porduct price
                    'sale_price' => $p_saleprice, // Product sale price if null give Product price value
                    'qty' => $p_stk_lvl, //Product quantity
                    'stock_status' => $p_visibility, // Product stock status ("in stock" or "out of stock").
                    // Give in 0 or 1 form (0 = out of stock, 1 = in stock)
                    'websites' => array(), //This is for only magento give only and blank array
                    'brand' => $p_brand, // Product brand if any
                    'low_stock_notification' => $p_stk_warning_lvl, // Porduct low stock notification if any otherwise give default 5 value
                    'created_at' => $p_created_date, // Product created at date format date('Y-m-d H:i:s')
                    'updated_at' => $p_updated_date, // Product updated at date format date('Y-m-d H:i:s')
                    'mul_store_id' => $connectionId, //Give multiple store id
                    'mul_channel_id' => '', // Give multiple channel id
                    'channel_store_name' => 'BigCommerce', // Channel or Store name
                    'channel_store_prefix' => 'BGC', // Channel or store prefix id
                    'elliot_user_id' => $userId, // Elliot user id
                    'store_id' => $bigcommerce_store_id, // if your are importing store give store id
                    'channel_id' => '', // if your are importing channel give channel id
                    'country_code' => $country_code, // store or channel country code
                    'currency_code' => $currency_code,
                    'conversion_rate' => $conversion_rate,
                    'upc' => $p_upc, // Product barcode if any
                    'ean' => '', // Product ean if any
                    'jan' => '', // Product jan if any
                    'isban' => '', // Product isban if any
                    'mpn' => '', // Product mpn if any
                    'categories' => $product_categories_ids, // Product categroy array. If null give a blank array
                    'images' => $product_image_data, // Product images data
                ];

                $productOptionsSetId = $product->option_set_id;
                $elliotProductId = Stores::productImportingCommon($product_data, $connectionId);
                if ($elliotProductId > 0) {
                    Stores::bigCommerceProductVariation($bigCommerceSetting, $p_id, $productOptionsSetId, $elliotProductId, $userId, 'BigCommerce', $connectionId, $product_data);
                    Stores::bgcUpdateProductTotalIQ($p_id, $elliotProductId, $userId);

                    return $elliotProductId;
                }
            }

            return 0;

        }


        return $bigProduct->id;
    }
}