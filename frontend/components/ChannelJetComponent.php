<?php
namespace frontend\components;

use common\models\Category;
use common\models\Country;
use common\models\Customer;
use common\models\Order;
use common\models\Product;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use frontend\components\ChannelJetClient;
use common\components\order\OrderStatus;

use yii\base\Component;

class ChannelJetComponent extends Component
{
    const timeOutLimit = 1;
    const errorTimeOutLimit = 20;
    const channelName = "jet";
    const pageLimit = 300;


    public static function createChannelJetClient($user_connection_id){

        $connection_jet = UserConnection::findOne(['id' => $user_connection_id]);

        if ( !empty($connection_jet) ){
            $jetClientConfig = $connection_jet->connection_info;

            $jetClient = new ChannelJetClient($jetClientConfig);

            if ( $jetClient->checkValidate() ){
                return $jetClient;
            }

        }

        return null;

    }

    /** Jet Channel Imporiting **/
    public static function channelInfoDetail($user_connection_id) {


        $channel_details = UserConnectionDetails::findOne(
            [
                'user_connection_id' => $user_connection_id
            ]
        );

        if (empty($channel_details)) {
            $channel_details = new UserConnectionDetails();
            $channel_details->user_connection_id = $user_connection_id;
        }

        $channel_details->store_name = 'Jet';
        $channel_details->store_url = '';
        $channel_details->country = '';
        $channel_details->country_code = 'US';
        $channel_details->currency = 'USD';
        $channel_details->currency_symbol = '$';
        $channel_details->others = '';

        $userConnectionSettings = $channel_details->settings;

        if(empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
            $userConnectionSettings['currency'] = $channel_details->currency;
        }

        $channel_details->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);


        $channel_details->save(false);

   }


    /** Jet Product Importing **/
    public static function jetProductImporting($user_connection_id, $conversion_rate) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $jetClient = self::createChannelJetClient($user_connection_id);

        if ( !empty($jetClient) && $jetClient->checkValidate() ){

            $userChannelConnection = UserConnection::findOne(['id' => $user_connection_id]);

            $user_id = $userChannelConnection->user_id;


            $skus_list_url = 'merchant-skus';

            $skus_list = $jetClient->call('get', $skus_list_url);

            if(isset($skus_list['sku_urls']) && count($skus_list['sku_urls'])>0){

                $skus_urls = $skus_list['sku_urls'];

                foreach($skus_urls as $sku_url){

                    $product_data = self::getJetProductBySku($user_id, $user_connection_id, $sku_url, $conversion_rate);

                    if ( !empty($product_data) ) {

                        Product::productImportingCommon($product_data);
                    }

                }

            }

        }

    }


    public static function getJetProductBySku($user_id, $user_connection_id, $sku_url, $conversion_rate){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $product_data = [];


        $jetClient = self::createChannelJetClient($user_connection_id);

        if ( !empty($jetClient) && $jetClient->checkValidate() ){

            $product = $jetClient->call('get', $sku_url);

            if ( isset($product['status']) ){

                $product_id = isset($product['merchant_sku_id']) ? $product['merchant_sku_id'] : '0';
                $title = isset($product['product_title']) ? $product['product_title'] : '';
                $description = isset($product['product_description']) ? $product['product_description'] : '';

                $product_img = (isset($product['alternate_images']))?$product['alternate_images']:array();
                $main_image_url = isset($product['main_image_url']) ? $product['main_image_url'] : '';

                $brand = isset($product['brand']) ? $product['brand'] : '';
                $weight = isset($product['shipping_weight_pounds']) ? $product['shipping_weight_pounds'] : '';

                $package_length = isset($product['package_length_inches']) ? $product['package_length_inches'] : null;
                $package_height = isset($product['package_height_inches']) ? $product['package_height_inches'] : null;
                $package_width = isset($product['package_width_inches']) ? $product['package_width_inches'] : null;

                $product_type = isset($product['manufacturer']) ? $product['manufacturer'] : '';

                $created_date = isset($product['sku_created_date']) ? date('Y-m-d H:i:s', strtotime($product['sku_created_date'])) : date('Y-m-d H:i:s');
                $updated_date = isset($product['price_last_update']) ? date('Y-m-d H:i:s', strtotime($product['price_last_update'])) : date('Y-m-d H:i:s');

                $price = isset($product['price']) ? $product['price'] : 0;
                if ( $price == 0 || empty($price) ){
                    $sku_price_url = $sku_url."/price";
                    $priceJson = $jetClient->call('get', $sku_price_url);

                    $price = isset($priceJson['price']) ? $priceJson['price'] : 0;

                }
                $target_price = isset($product['target_price']) ? $product['target_price'] : '';

                $sku = isset($product['merchant_sku']) ? $product['merchant_sku'] : '';
                $status = isset($product['status']) ? $product['status'] : '';
                $product_qty = isset($product['inventory_by_fulfillment_node']) ? $product['inventory_by_fulfillment_node'][0]['quantity'] : 0;

                if ( $product_qty == 0 || empty($product_qty) ){
                    $sku_inventory_url = $sku_url."/inventory";
                    $inventoryJson = $jetClient->call('get', $sku_inventory_url);

                    $product_qty = isset($inventoryJson['inventory_by_fulfillment_node']) ? $inventoryJson['inventory_by_fulfillment_node'][0]['quantity'] : 0;

                }

                $jet_node_ids = array();
                $cat1 = isset($product['jet_browse_node_id']) ? $product['jet_browse_node_id'] : '';
                $cat2 = isset($product['jet_browse_node_id_mapped_level_0']) ? $product['jet_browse_node_id_mapped_level_0'] : '';
                $cat3 = isset($product['jet_browse_node_id_mapped_level_1']) ? $product['jet_browse_node_id_mapped_level_1'] : '';
                $cat4 = isset($product['jet_browse_node_id_mapped_level_2']) ? $product['jet_browse_node_id_mapped_level_2'] : '';
                if($cat1!=''){
                    $jet_node_ids[] = $cat1;
                }
                if($cat2!=''){
                    $jet_node_ids[] = $cat2;
                }
                if($cat3!=''){
                    $jet_node_ids[] = $cat3;
                }
                if($cat4!=''){
                    $jet_node_ids[] = $cat4;
                }

                $product_category_ids = [];
                if ( !empty($jet_node_ids) ) {
                    foreach($jet_node_ids as $node_id){
                        $cate_url = "taxonomy/nodes/".$node_id;
                        $cat_result = $jetClient->call('get', $cate_url);
                        if($cat_result=='' || isset($cat_result['Message']) && $cat_result['Message']=='The request is invalid.'){
                            continue;
                        }else{
                            $cat_name = $cat_result['jet_node_name'];
                            $cat_id = $cat_result['jet_node_id'];
                            $cat_parent_id = isset($cat_result['parent_id'])? $cat_result['parent_id'] : '0';
                            $product_category_ids[] = $cat_id;
                            $category_data = [
                                'name' => $cat_name, // Give category name
                                'description' => '', // Give empty string
                                'parent_id' => 0,
                                'user_id' => $user_id, // Give Elliot user id,
                                'user_connection_id' => $user_connection_id, // Give Channel/Store connection id
                                'connection_category_id' => $cat_id, // Give category id of Store/channels
                                'connection_parent_id' => $cat_parent_id, // Give Category parent unique id of Store if null then give 0
                            ];

                            Category::categoryImportingCommon($category_data);    // Then call store modal function and give $category data
                        }
                    }

                }


                $product_url = '';
                $jet_sku = isset($product['jet_sku']) ? $product['jet_sku'] : '';
                if ( !empty($jet_sku) ){
                    $product_url = 'https://jet.com/search?term='.$jet_sku;
                }

                $upc = $ean = $isbn = $mpn = $jan = '';

                if(isset($product['standard_product_codes'])){
                    $product_codes = $product['standard_product_codes'];
                    foreach($product_codes as $_product_code){
                        if($_product_code['standard_product_code_type']=='UPC'){
                            $upc = $_product_code['standard_product_code'];
                        }
                        if($_product_code['standard_product_code_type']=='EAN'){
                            $ean = $_product_code['standard_product_code'];
                        }
                        if($_product_code['standard_product_code_type']=='ISBN'){
                            $isbn = $_product_code['standard_product_code'];
                        }
                        if($_product_code['standard_product_code_type']=='MPN'){
                            $mpn = $_product_code['standard_product_code'];
                        }
                        if($_product_code['standard_product_code_type']=='JAN'){
                            $jan = $_product_code['standard_product_code'];
                        }
                    }
                }

                $product_status = Product::STATUS_INACTIVE;
                $product_published = Product::PRODUCT_PUBLISHED_NO;
                if ( $status == 'Available for Purchase' || $status == 'Under Jet Review' ) {
                    $product_status = Product::STATUS_ACTIVE;
                    $product_published = Product::PRODUCT_PUBLISHED_YES;
                }

                $stock_status = ($product_qty>0) ? 1 : 0;

                $product_image_data = array();
                if(count($product_img)>0){
                    $base_img = $main_image_url;
                    foreach($product_img as $_image){
                        $img_url = $_image['image_url'];
                        $position = $_image['image_slot_id'];
                        if($main_image_url == $img_url){
                            $base_img = $img_url;
                        }
                        $product_image_data[] = array(
                            'connection_image_id' => 0,
                            'image_url' => $img_url,
                            'label' => '',
                            'position' => $position,
                            'base_img' => $base_img,
                        );

                    }
                }


                $product_variation = [];

                if( isset($product['attributes_node_specific']) ){

                    $current_product_attributes = $product['attributes_node_specific'];
                    $current_product_variation = self::jetProductVariationOption($user_connection_id, $cat1, $current_product_attributes);

                    if ( !empty($current_product_variation) ){
                        $product_variation = $current_product_variation;
                    }
                }

                $variants_data = [];

                $variants_data_url = $sku_url . "/variation";
                $variants_list = $jetClient->call('get', $variants_data_url);
                if ( !empty($variants_list) ){

                    $children_skus = isset($variants_list['children_skus'])?$variants_list['children_skus']:null;

                    if ( !empty($children_skus) ){

                        foreach ($children_skus as $children_sku_data){

                            $children_sku = $children_sku_data['merchant_sku'];
                            $children_sku_id = $children_sku_data['merchant_sku_id'];

                            if( $children_sku == $sku ) {

                                if ( !empty($product_variation) ){

                                    $oneVariantData = [
                                        'connection_variation_id' => $product_id,
                                        'sku_key' => 'merchant_sku',
                                        'sku_value' => $sku,
                                        'inventory_key' => 'inventory_by_fulfillment_node',
                                        'inventory_value' => $product_qty,
                                        'price_key' => 'price',
                                        'price_value' => $price,
                                        'weight_key' => 'shipping_weight_pounds',
                                        'weight_value' => $weight,
                                        'options' => $product_variation,
                                        'created_at' => $created_date,
                                        'updated_at' => $updated_date,
                                    ];

                                    $variants_data[] = $oneVariantData;
                                }

                            } else {


                                $children_sku_url = "merchant-skus/".$children_sku;
                                $children_product = $jetClient->call('get', $children_sku_url);

                                if ( isset($children_product['status']) ){

                                    $child_product_weight = isset($children_product['shipping_weight_pounds']) ? $children_product['shipping_weight_pounds'] : '';

                                    $child_created_date = isset($children_product['sku_created_date']) ? date('Y-m-d H:i:s', strtotime($children_product['sku_created_date'])) : date('Y-m-d H:i:s');
                                    $child_updated_date = isset($children_product['price_last_update']) ? date('Y-m-d H:i:s', strtotime($children_product['price_last_update'])) : date('Y-m-d H:i:s');

                                    $child_product_price = isset($children_product['price']) ? $children_product['price'] : 0;
                                    if ( $child_product_price == 0 || empty($child_product_price) ){
                                        $child_price_url = $children_sku_url."/price";
                                        $child_priceJson = $jetClient->call('get', $child_price_url);

                                        $child_product_price = isset($child_priceJson['price']) ? $child_priceJson['price'] : 0;

                                    }

                                    $child_product_qty = isset($children_product['inventory_by_fulfillment_node']) ? $children_product['inventory_by_fulfillment_node'][0]['quantity'] : 0;

                                    if ( $child_product_qty == 0 || empty($child_product_qty) ){
                                        $child_inventory_url = $children_sku_url."/inventory";
                                        $child_inventoryJson = $jetClient->call('get', $child_inventory_url);

                                        $child_product_qty = isset($child_inventoryJson['inventory_by_fulfillment_node']) ? $child_inventoryJson['inventory_by_fulfillment_node'][0]['quantity'] : 0;

                                    }

                                    $child_jet_id = isset($children_product['jet_browse_node_id']) ? $children_product['jet_browse_node_id'] : '';


                                    $child_product_variation = [];

                                    if( isset($children_product['attributes_node_specific']) ){

                                        $current_child_product_attributes = $children_product['attributes_node_specific'];
                                        $current_child_product_variation = self::jetProductVariationOption($user_connection_id, $child_jet_id, $current_child_product_attributes);

                                        if ( !empty($current_child_product_variation) ){
                                            $child_product_variation = $current_child_product_variation;
                                        }
                                    }

                                    $oneVariantData = [
                                        'connection_variation_id' => $children_sku_id,
                                        'sku_key' => 'merchant_sku',
                                        'sku_value' => $children_sku,
                                        'inventory_key' => 'inventory_by_fulfillment_node',
                                        'inventory_value' => $child_product_qty,
                                        'price_key' => 'price',
                                        'price_value' => $child_product_price,
                                        'weight_key' => 'shipping_weight_pounds',
                                        'weight_value' => $child_product_weight,
                                        'options' => $child_product_variation,
                                        'created_at' => $child_created_date,
                                        'updated_at' => $child_updated_date,
                                    ];

                                    $variants_data[] = $oneVariantData;


                                }

                            }


                        }

                    }

                }


                $product_data = [
                    'user_id'=> $user_id,         // Elliot user id
                    'name'=> $title,  // Product name
                    'sku'=> $sku,    // Product SKU
                    'url'=> $product_url,   // Product url if null give blank value
                    'upc'=> $upc,  // Product barcode if any
                    'ean'=> $ean,  // Product ean if any
                    'jan'=> $jan,  // Product jan if any
                    'isbn'=> $isbn,    // Product isban if any
                    'mpn'=> $mpn,  // Product mpn if any
                    'description'=> $description,    // Product Description
                    'adult' => null,
                    'age_group' => null,
                    'brand'=> $brand,    // Product brand if any
                    'condition' => null,
                    'gender' => null,
                    'weight'=> $weight,  // Product weight if null give blank value
                    'package_length' => $package_length,
                    'package_height' => $package_height,
                    'package_width' => $package_width,
                    'package_box' => null,
                    'stock_quantity' => $product_qty, //Product quantity,
                    'allocate_inventory' => null,
                    'currency' => 'USD',
                    'country_code' => 'US',
                    'stock_level' => ($stock_status>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                    'stock_status' => ($stock_status>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                    'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                    'price' => $price, // Porduct price,
                    'sales_price' => $price,
                    'schedule_sales_date' => null,
                    'status' => $product_status,
                    'published' => $product_published,
                    'permanent_hidden' => Product::STATUS_NO,
                    'user_connection_id' => $user_connection_id,
                    'connection_product_id' => $product_id, // Stores/Channel product ID,
                    'type' => $product_type, // Product Manufacturer
                    'conversion_rate' => $conversion_rate,
                    'websites' => array(), //This is for only magento give only and blank array
                    'images' => $product_image_data, // Product images data
                    'categories'=> $product_category_ids, // Product categroy array. If null give a blank array
                    'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s'),
                    'updated_at' => $updated_date, // Product updated at date format date('Y-m-d H:i:s'),
                    'options_set' => array(),
                    'variations' => $variants_data,


                ];

            }

        }

        return $product_data;
    }

    public static function jetProductVariationOption($user_connection_id, $jet_id, $attribute_nodes){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $options = [];

        if( !empty($jet_id) && !empty($attribute_nodes) ){

            $attribute_nodes_count = count($attribute_nodes);

            $jetClient = self::createChannelJetClient($user_connection_id);

            if ( !empty($jetClient) && $jetClient->checkValidate() ){

                $attribute_url = "taxonomy/nodes/" . $jet_id . "/attributes";
                $jet_attributes_result = $jetClient->call('get', $attribute_url);

                if ( !empty($jet_attributes_result) && isset($jet_attributes_result['attributes']) ){

                    $attributes = $jet_attributes_result['attributes'];

                    foreach ($attributes as $attribute) {

                        foreach ($attribute_nodes as $attribute_node){

                            if ( $attribute['attribute_id'] == $attribute_node['attribute_id'] ){

                                $value_data = [
                                    'label' => '',
                                    'value' => $attribute_node['attribute_value']
                                ];

                                $oneVariationOption = [
                                    'name' => $attribute['attribute_description'],
                                    'value' => $value_data
                                ];

                                $options[] = $oneVariationOption;
                            }

                        }

                        if( count($options) == $attribute_nodes_count ){
                            break;
                        }
                    }

                }

            }

        }

        return $options;

    }

    /** Jet order importing **/
    public static function jetOrderImporting($user_connection_id, $get_order_url, $conversion_rate) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $jetConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $user_id = $jetConnection->user_id;
        $store_connection_details = $jetConnection->userConnectionDetails;
        $store_currency_code = $store_connection_details->currency;
        $store_country_code = $store_connection_details->country_code;


        $jetClient = self::createChannelJetClient($user_connection_id);

        if ( !empty($jetClient) && $jetClient->checkValidate() ){

            $order_list = $jetClient->call('get', $get_order_url);

            if(isset($order_list['order_urls']) && count($order_list['order_urls'])>0){
                $orders_urls = $order_list['order_urls'];
                foreach ($orders_urls as $order_url){
                    $order = $jetClient->call('get', $order_url);

                    if ( !empty($order) ){

                        $order_id = $order['merchant_order_id'];
                        $customer_email = $order['hash_email'];
                        $customer_id = $order['customer_reference_order_id'];
                        $order_created_at = isset($order['order_placed_date']) ? $order['order_placed_date'] : date('Y-m-d H:i:s');
                        $order_updated_at = isset($order['order_ready_date']) ? $order['order_ready_date'] : date('Y-m-d H:i:s');
                        $order_status = $order['status'];
                        //$total_product_qty  = count($order['order_items']);
                        $total_product_qty  = 0;

                        $base_price = isset($order['order_totals']['item_price']['base_price']) ? $order['order_totals']['item_price']['base_price'] : 0;
                        $item_tax = isset($order['order_totals']['item_price']['item_tax']) ? $order['order_totals']['item_price']['item_tax'] : 0;
                        $item_shipping_cost = isset($order['order_totals']['item_price']['item_shipping_cost']) ? $order['order_totals']['item_price']['item_shipping_cost'] : 0;
                        $item_shipping_tax = isset($order['order_totals']['item_price']['item_shipping_tax']) ? $order['order_totals']['item_price']['item_shipping_tax'] : 0;
                        $item_fulfillment_cost = isset($order['order_totals']['item_price']['item_fulfillment_cost']) ? $order['order_totals']['item_price']['item_fulfillment_cost'] : 0;

                        $order_total = $base_price+$item_tax+$item_shipping_cost+$item_shipping_tax+$item_fulfillment_cost;

                        $bill_addr_fname = $bill_addr_lname = $bill_company = $bill_add_1 = $bill_add_2 = $bill_city = $bill_state = $bill_country = $bill_zip = $bill_add_phone = $bill_country_code = '';

                        if ( isset($order['buyer']) && !empty($order['buyer']) ){

                            $buyer_name = isset($order['buyer']['name'])?$order['buyer']['name']:"";

                            $buyer_names = explode(" ", $buyer_name);

                            switch (count($buyer_names)){
                                case 2:
                                    $bill_addr_fname = $buyer_names[0];
                                    $bill_addr_lname = $buyer_names[1];
                                    break;
                                case 3:
                                    $bill_addr_fname = $buyer_names[0];
                                    $bill_addr_lname = $buyer_names[1] . " " . $buyer_names[2];
                                    break;

                                default:
                                    $bill_addr_fname = $buyer_name;
                                    break;

                            }

                            $bill_add_phone = isset($order['buyer']['phone_number'])?$order['buyer']['phone_number']:"";

                        }

                        $ship_f_name = "";
                        $ship_l_name = "";
                        $ship_name = isset($order['shipping_to']['recipient']['name']) ? $order['shipping_to']['recipient']['name'] : '';
                        if ( isset($ship_name) && !empty($ship_name) ){
                            $ship_names = explode(" ", $ship_name);

                            switch (count($ship_names)){
                                case 2:
                                    $ship_f_name = $ship_names[0];
                                    $ship_l_name = $ship_names[1];
                                    break;
                                case 3:
                                    $ship_f_name = $ship_names[0];
                                    $ship_l_name = $ship_names[1] . " " . $ship_names[2];
                                    break;

                                default:
                                    $ship_f_name = $ship_name;
                                    break;

                            }

                        }

                        $ship_phone = isset($order['shipping_to']['recipient']['phone_number']) ? $order['shipping_to']['recipient']['phone_number'] : '';
                        $ship_address_1 = isset($order['shipping_to']['address']['address1']) ? $order['shipping_to']['address']['address1'] : '';
                        $ship_address_2 = isset($order['shipping_to']['address']['address2']) ? $order['shipping_to']['address']['address2'] : '';
                        $ship_city = isset($order['shipping_to']['address']['city']) ? $order['shipping_to']['address']['city'] : '';
                        $ship_state = isset($order['shipping_to']['address']['state']) ? $order['shipping_to']['address']['state'] : '';
                        $ship_zip = isset($order['shipping_to']['address']['zip_code']) ? $order['shipping_to']['address']['zip_code'] : '';
                        $country_name = '';
                        $country_id = '';
                        if ($ship_zip != '') {
                            $country_data = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address='.$ship_zip.'&sensor=true');
                            $country_result = json_decode($country_data, true);
                            if ( !empty($country_result) && isset($country_result['results'][0]['address_components']) ) {

                                $add = $country_result['results'][0]['address_components'];
                                foreach ($add as $_add) {
                                    if ($_add['types'][0] == 'country') {
                                        $country_name = $_add['long_name'];
                                        $country_id = $_add['short_name'];
                                    }
                                }

                            }
                        }

                        switch (strtolower($order_status)) {
                            case "created":
                                $order_status_2 = OrderStatus::PENDING;
                                break;
                            case "ready":
                                $order_status_2 = OrderStatus::IN_TRANSIT;
                                break;
                            case "acknowledged":
                                $order_status_2 = OrderStatus::IN_TRANSIT;
                                break;
                            case "inprogress":
                                $order_status_2 = OrderStatus::IN_TRANSIT;
                                break;
                            case "complete":
                                $order_status_2 = OrderStatus::COMPLETED;
                                break;
                            default:
                                $order_status_2 = OrderStatus::PENDING;
                        }


                        $order_product_data = array();
                        $order_items = $order['order_items'];
                        foreach($order_items as $_items){
                            $product_sku = $_items['merchant_sku'];
                            $product_id = 0;

                            $product_data = $jetClient->call('get', 'merchant-skus/'.$product_sku);
                            if($product_data!=''){
                                $product_id = isset($product_data['merchant_sku_id']) ? $product_data['merchant_sku_id'] : 0;
                            }
                            $title = $_items['product_title'];
                            $price = $_items['item_price']['base_price'];
                            $quantity = $_items['request_order_quantity'];
                            $product_weight = 0;

                            $order_product_data[] = array(
                                'user_id' => $user_id,
                                'connection_product_id' => $product_id,
                                'name' => $title,
                                'order_product_sku' => $product_sku,
                                'price' => $price,
                                'qty' => $quantity,
                                'weight' => $product_weight,
                            );
                            $total_product_qty += $quantity;
                        }

                        $customer_data = array(
                            'connection_customerId' => $customer_id,
                            'user_id' => $user_id,
                            'first_name' => $bill_addr_fname,
                            'last_name' => $bill_addr_lname,
                            'email' => $customer_email,
                            'phone' => $bill_add_phone,
                            'user_connection_id' => $user_connection_id,
                            'customer_created' => $order_created_at,
                            'updated_at' => $order_updated_at,
                            'addresses' => [
                                [
                                    'first_name' => $bill_addr_fname,
                                    'last_name' => $bill_addr_lname,
                                    'company' => $bill_company,
                                    'country' => $bill_country,
                                    'country_iso' => $bill_country_code,
                                    'street_1' => $bill_add_1,
                                    'street_2' => $bill_add_2,
                                    'state' => $bill_state,
                                    'city' => $bill_state,
                                    'zip' => $bill_zip,
                                    'phone' => $bill_add_phone,
                                    'address_type' => 'Billing',
                                ],
                            ]
                        );


                        $order_data = array(
                            'user_id' => $user_id,
                            'user_connection_id' => $user_connection_id,
                            'connection_order_id' => $order_id,
                            'status' => $order_status_2,
                            'product_quantity' => $total_product_qty,
                            'ship_fname' => $ship_f_name,
                            'ship_lname' => $ship_l_name,
                            'ship_phone' => $ship_phone,
                            'ship_company' => '',
                            'ship_street_1' => $ship_address_1,
                            'ship_street_2' => $ship_address_2,
                            'ship_city' => $ship_city,
                            'ship_state' => $ship_state,
                            'ship_zip' => $ship_zip,
                            'ship_country' => $country_name,
                            'ship_country_iso' => $country_id,
                            'bill_fname' => $bill_addr_fname,
                            'bill_lname' => $bill_addr_lname,
                            'bill_phone' => $bill_add_phone,
                            'bill_company' => $bill_company,
                            'bill_street_1' => $bill_add_1,
                            'bill_street_2' => $bill_add_2,
                            'bill_city' => $bill_city,
                            'bill_state' => $bill_state,
                            'bill_country' => $bill_country,
                            'bill_zip' => $bill_zip,
                            'bill_country_iso' => $bill_country_code,
                            'fee' => [
                                'base_shippping_cost' => $item_shipping_cost,
                                'shipping_cost_tax' => $item_shipping_tax,
                                'payment_method' => 'Debit card',
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

                        if(!empty($order_data)){
                            Order::orderImportingCommon($order_data);
                        }


                    }

                }
            }

        }


    }
}