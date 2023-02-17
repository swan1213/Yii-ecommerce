<?php
namespace frontend\components;

use common\models\Category;
use common\models\Country;
use common\models\Customer;
use common\models\Order;
use common\models\Product;
use common\models\ProductCategory;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use frontend\components\ChannelFlipkartClient;
use common\components\order\OrderStatus;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Exception as SheetException;
use yii\base\Component;

class ChannelFlipkartComponent extends Component
{
    const timeOutLimit = 1;
    const errorTimeOutLimit = 20;
    const channelName = "flipkart";


    public static function createChannelFlipkartClient($user_connection_id){

        $connection_jet = UserConnection::findOne(['id' => $user_connection_id]);

        $flipkartClientConfig = $connection_jet->connection_info;

        $flipkartClient = new ChannelFlipkartClient($flipkartClientConfig);

        return $flipkartClient;
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

        $channel_details->store_name = 'Flipkart';
        $channel_details->store_url = '';
        $channel_details->country = '';
        $channel_details->country_code = 'In';
        $channel_details->currency = 'INR';
        $channel_details->currency_symbol = 'INR';
        $channel_details->others = '';

        $userConnectionSettings = $channel_details->settings;

        if(empty($userConnectionSettings['currency']) || !isset($userConnectionSettings['currency'])) {
            $userConnectionSettings['currency'] = $channel_details->currency;
        }

        $channel_details->settings = @json_encode($userConnectionSettings, JSON_UNESCAPED_UNICODE);


        $channel_details->save(false);

   }


    public static function ImportProductsFromXls($user_connection_id, $xls_file_path, $conversion_rate) {

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $flipkartConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $user_id = $flipkartConnection->user_id;
        $flipkart_connection_details = $flipkartConnection->userConnectionDetails;

        $store_currency_code = $flipkart_connection_details->currency;
        $store_country_code = $flipkart_connection_details->country_code;


        $inputfilename = $xls_file_path;


        try {
            $inputfiletype = IOFactory::identify($inputfilename);
            $objReader = IOFactory::createReader($inputfiletype);
            $objPHPExcel = $objReader->load($inputfilename);
        } catch (ReaderException $e) {
            echo ('Error loading file "' . pathinfo($inputfilename, PATHINFO_BASENAME) . '": ' . $e->getMessage());
            return false;
        }
//  Get worksheet dimensions

        try {
            $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);

            if (isset($allDataInSheet) and ! empty($allDataInSheet)) {

                foreach ($allDataInSheet as $key => $single) {
                    if (in_array($key, [1, 2]))
                        continue;

                    if ( !empty($single) ){

                        $product_sku = @$single['F'];
                        $product_weight = @$single['X'];//weight
                        $product_length = @$single['U'];//lenght
                        $product_width = @$single['V'];//Breadth
                        $product_height = @$single['W'];//height

                        $product_title = @$single['D'];
                        $product_description = $product_title;

                        $system_product_quantity = @$single['P'];
                        $product_quantity = $system_product_quantity;

                        $seller_product_quantity = @$single['Q'];

                        if ( isset($seller_product_quantity) && !empty($seller_product_quantity) ){
                            $product_quantity = $seller_product_quantity;
                        }
                        $stock_status = ($product_quantity > 0)?1:0;
                        $product_price = @$single['G'];
                        $product_sales_price = @$single['H'];
                        $product_id = @$single['B'];//Flipkart Listing Id
                        $updated_date = $created_date = date('Y-m-d H:i:s', time());

                        $listing_status = (strtoupper(@$single['S'])==="ACTIVE")?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE;
                        $published = isset($published_at)?Product::PRODUCT_PUBLISHED_YES:Product::PRODUCT_PUBLISHED_NO;

                        $product_category_name = @$single['C'];

                        $product_url = '';
                        $product_type = '';
                        $product_image_data = array();

                        $product_data = [
                            'user_id' => $user_id, // Elliot user id,
                            'name' => $product_title, // Product name,
                            'sku' => $product_sku, // Product SKU,
                            'url' => $product_url, // Product url if null give blank value,
                            'upc' => '', // Product upc if any,
                            'ean' => '',
                            'jan' => '', // Product jan if any,
                            'isbn' => '', // Product isban if any,
                            'mpn' => '', // Product mpn if any,
                            'description' => $product_description, // Product Description,
                            'adult' => null,
                            'age_group' => null,
                            'brand' => null,
                            'condition' => null,
                            'gender' => null,
                            'weight' => $product_weight, // Product weight if null give blank value,
                            'package_length' => $product_length,
                            'package_height' => $product_height,
                            'package_width' => $product_width,
                            'package_box' => null,
                            'stock_quantity' => $product_quantity, //Product quantity,
                            'allocate_inventory' => null,
                            'currency' => $store_currency_code,
                            'country_code' => $store_country_code,
                            'stock_level' => ($stock_status>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                            'stock_status' => ($stock_status>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                            'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                            'price' => $product_price, // Porduct price,
                            'sales_price' => $product_sales_price, // Product sale price if null give Product price value,
                            'schedule_sales_date' => null,
                            'status' => $listing_status,
                            'published' => $published,
                            'permanent_hidden' => Product::STATUS_NO,
                            'user_connection_id' => $user_connection_id,
                            'connection_product_id' => $product_id, // Stores/Channel product ID,
                            'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s'),
                            'updated_at' => $updated_date, // Product updated at date format date('Y-m-d H:i:s'),
                            'type' => $product_type, // Product Type
                            'images' => $product_image_data, // Product images data
                            'variations' => array(),
                            'options_set' => array(),
                            'websites' => array(), //This is for only magento give only and blank array
                            'conversion_rate' => $conversion_rate,
                            'categories' => array(), // Product categroy array. If null give a blank array
                            'flipkart_category_name' => $product_category_name
                        ];

                        self::upsertProduct($product_data);

                    }
                }
            }

        } catch (SheetException $ex){
            echo ('Error Read data "' . pathinfo($inputfilename, PATHINFO_BASENAME) . '": ' . $ex->getMessage());
            return false;
        }

        return true;
    }

    public static function upsertProduct($product_data){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        if ( !empty($product_data) ) {

            $gen_ProductId = Product::productImportingCommon($product_data);

            $product_category_name = $product_data['flipkart_category_name'];
            $user_connection_id = $product_data['user_connection_id'];
            $user_id = $product_data['user_id'];
            $created_date = $product_data['created_at'];
            $updated_date = $product_data['updated_at'];

            $product_category_id = null;
            if ( !empty($product_category_name) ){

                $checkCategory = Category::findOne(
                    [
                        'name' => $product_category_name,
                        'user_connection_id' => $user_connection_id
                    ]);

                if ( empty($checkCategory) ){
                    $checkCategory = new Category();

                    $categoryData = [
                        'Category' => [
                            'name' => $product_category_name, // Give category name
                            'description' => $product_category_name, // Give category name
                            'parent_id' => 0,
                            'user_id' => $user_id, // Give Elliot user id,
                            'user_connection_id' => $user_connection_id, // Give Channel/Store connection id
                            'connection_category_id' => 0, // Give 0
                            'connection_parent_id' => '0', // Give Category parent unique id of Store if null then give 0
                            'created_at' => $created_date, // Give Created at date if null then give current date format date('Y-m-d H:i:s')
                            'updated_at' => $updated_date, // Give Updated at date if null then give current date format date('Y-m-d H:i:s')
                        ]
                    ];

                    $checkCategory->load($categoryData);
                    $checkCategory->save(false);
                }

                $product_category_id = $checkCategory->id;


            }

            if ( !empty($gen_ProductId) && !empty($product_category_id) ){

                $productCategory = ProductCategory::findOne([
                    'product_id' => $gen_ProductId,
                    'category_id' => $product_category_id
                ]);

                if (empty($productCategory)) {
                    $productCategory = new ProductCategory();

                    $productCategory->user_id = $user_id;
                    $productCategory->category_id = $product_category_id;
                    $productCategory->product_id = $gen_ProductId;
                    $productCategory->created_at = $created_date;
                    $productCategory->save(false);
                }


            }

        }

    }

    public static function ImportOrdersFromCsv($user_connection_id, $csv_file_path, $conversion_rate)
    {

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        $flipkartConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $user_id = $flipkartConnection->user_id;
        $flipkart_connection_details = $flipkartConnection->userConnectionDetails;

        $store_currency_code = $flipkart_connection_details->currency;
        $store_country_code = $flipkart_connection_details->country_code;


        $results = self::ImportCSV2Array($csv_file_path);

        if ( !empty($results) ){

            return true;

        }


        return false;
    }

    public static function ImportCSV2Array($filename)
    {
        $row = 0;
        $col = 0;

        $handle = @fopen($filename, "r");
        if ($handle)
        {
            while (($row = fgetcsv($handle, 4096)) !== false)
            {
                if (empty($fields))
                {
                    $fields = $row;
                    continue;
                }

                foreach ($row as $k=>$value)
                {
                    $results[$col][$fields[$k]] = $value;
                }
                $col++;
                unset($row);
            }
            if (!feof($handle))
            {
                echo "Error: unexpected fgets() fail";
            }
            fclose($handle);
        }

        return $results;
    }

    public static function OrderImport($user_connection_id, $conversion_rate = 1){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $flipkartConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $user_id = $flipkartConnection->user_id;
        $flipkart_connection_details = $flipkartConnection->userConnectionDetails;

        $store_currency_code = $flipkart_connection_details->currency;
        $store_country_code = $flipkart_connection_details->country_code;

        $flipkartClient = self::createChannelFlipkartClient($user_connection_id);

        $api_base_url = "sellers/v2";
        $api_path = "/orders/search";

        $post_data = [
            'pagination' => [
                'pageSize' => 20
            ],
            'sort' => [
                'field' => 'orderDate',
                'order' => 'asc'
            ]
        ];

        $api_full_path = $api_base_url.$api_path;

        if ( $flipkartClient->checkValidate() ){

            $results = $flipkartClient->call('post', $api_full_path, $post_data);

            if ( !empty($results) && isset($results['orderItems']) ){

                while (true){

                    $order_list = isset($results['orderItems'])?$results['orderItems']:array();
                    if ( empty($order_list) ){
                        break;
                    }
                    $hasMore = isset($results['hasMore'])?$results['hasMore']:false;
                    $nextPageUrl = isset($results['nextPageUrl'])?$results['nextPageUrl']:'';

                    foreach ($order_list as $order){

                        $order_id = $order['orderItemId'];
                        $order_status = $order['status'];

                        //  APPROVED, PACKING_IN_PROGRESS, PACKED, READY_TO_DISPATCH, PICKUP_COMPLETE,
                        //  CANCELLED, SHIPPED, DELIVERED, RETURN_REQUESTED, RETURNED

                        switch (strtoupper($order_status)) {
                            case "APPROVED":
                                $order_status_2 = OrderStatus::COMPLETED;
                                break;
                            case "DELIVERED":
                                $order_status_2 = OrderStatus::COMPLETED;
                                break;
                            case "SHIPPED":
                                $order_status_2 = OrderStatus::IN_TRANSIT;
                                break;
                            case "PACKED":
                                $order_status_2 = OrderStatus::IN_TRANSIT;
                                break;
                            case "PICKUP_COMPLETE":
                                $order_status_2 = OrderStatus::PENDING;
                                break;
                            case "PACKING_IN_PROGRESS":
                                $order_status_2 = OrderStatus::PENDING;
                                break;
                            case "READY_TO_DISPATCH":
                                $order_status_2 = OrderStatus::PENDING;
                                break;
                            case "CANCELLED":
                                $order_status_2 = OrderStatus::CANCEL;
                                break;
                            case "RETURN_REQUESTED":
                                $order_status_2 = OrderStatus::REFUNDED;
                                break;
                            case "RETURNED":
                                $order_status_2 = OrderStatus::REFUNDED;
                                break;
                            default:
                                $order_status_2 = OrderStatus::PENDING;

                        }

                        $order_created_at = isset($order['orderDate'])?date('Y-m-d H:i:s', strtotime($order['orderDate'])):time();
                        $order_updated_at = isset($order['updatedAt'])?date('Y-m-d H:i:s', strtotime($order['updatedAt'])):time();
                        $total_product_count = 0;
                        $main_product_count = $order['quantity'];
                        $total_product_count += $main_product_count;
                        $order_total = 0;
                        $total_discount = 0;
                        $base_shipping_cost = 0;
                        $main_product_price = 0;
                        if ( isset($order['priceComponents']) && isset($order['priceComponents']['totalPrice'])){
                            $order_total = isset($order['priceComponents']['totalPrice'])?$order['priceComponents']['totalPrice']:0;
                            //$payment_gateway = isset($order['priceComponents']['totalPrice'])?$order['priceComponents']['totalPrice']:'';
                            $total_discount = isset($order['priceComponents']['flipkartDiscount'])?$order['priceComponents']['flipkartDiscount']:0;
                            $base_shipping_cost = isset($order['priceComponents']['shippingCharge'])?$order['priceComponents']['shippingCharge']:0;
                            $main_product_price = isset($order['priceComponents']['sellingPrice'])?$order['priceComponents']['sellingPrice']:0;
                        }


                        $order_shipments = self::getShipmentsDetail($user_connection_id, $order_id, $order_created_at);
                        $customer_data = $order_shipments['customer_data'];

                        $order_product_data = array();

                        $main_product_id = isset($order['listingId'])?$order['listingId']:0;
                        $main_product_title = isset($order['title'])?$order['title']:'';
                        $main_product_sku = isset($order['sku'])?$order['sku']:'';
                        $payment_method = isset($order['paymentType'])?$order['paymentType']:'';

                        $order_product_data[] = array(
                            'user_id' => $user_id,
                            'connection_product_id' => $main_product_id,
                            'name' => $main_product_title,
                            'order_product_sku' => $main_product_sku,
                            'price' => $main_product_price,
                            'qty' => $main_product_count,
                            'weight' => '',
                        );



                        $order_items = $order['subItems'];
                        foreach ($order_items as $_items) {

                            $item_order_price = 0;
                            $item_price = 0;
                            $item_title = isset($_items['title'])? $_items['title'] : 0;
                            $item_quantity = isset($_items['quantity'])? $_items['quantity'] : 0;
                            $item_sku = isset($_items['sku'])? $_items['sku'] : '';
                            $item_product_id = isset($_items['listingId'])? $_items['listingId'] : 0;
                            //$variant_id = ($_items['variant_id'] == '') ? 0 : $_items['variant_id'];
                            if ( isset($_items['priceComponents']) && isset($_items['priceComponents']['totalPrice'])){
                                $item_order_price = isset($_items['priceComponents']['totalPrice'])?$_items['priceComponents']['totalPrice']:0;
                                $item_price = isset($_items['priceComponents']['sellingPrice'])?$_items['priceComponents']['sellingPrice']:0;
                            }

                            $item_weight = '';
                            $order_product_data[] = array(
                                'user_id' => $user_id,
                                'connection_product_id' => $item_product_id,
                                'name' => $item_title,
                                'order_product_sku' => $item_sku,
                                'price' => $item_price,
                                'qty' => $item_quantity,
                                'weight' => $item_weight,
                            );

                            //$total_product_count += $item_quantity;
                            $order_total += $item_order_price;
                        }

                        self::precheckProducts($user_connection_id, $order_product_data, $conversion_rate);

                        $order_data = array(
                            'user_id' => $user_id,
                            'user_connection_id' => $user_connection_id,
                            'connection_order_id' => $order_id,
                            'status' => $order_status_2,
                            'product_quantity' => $total_product_count,
                            'ship_fname' => $order_shipments['ship_fname'],
                            'ship_lname' => $order_shipments['ship_lname'],
                            'ship_phone' => $order_shipments['ship_phone'],
                            'ship_company' => $order_shipments['ship_company'],
                            'ship_street_1' => $order_shipments['ship_street_1'],
                            'ship_street_2' => $order_shipments['ship_street_2'],
                            'ship_city' => $order_shipments['ship_city'],
                            'ship_state' => $order_shipments['ship_state'],
                            'ship_zip' => $order_shipments['ship_zip'],
                            'ship_country' => $order_shipments['ship_country'],
                            'ship_country_iso' => $order_shipments['ship_country_iso'],
                            'bill_fname' => $order_shipments['bill_fname'],
                            'bill_lname' => $order_shipments['bill_fname'],
                            'bill_phone' => $order_shipments['bill_phone'],
                            'bill_company' => $order_shipments['bill_company'],
                            'bill_street_1' => $order_shipments['bill_street_1'],
                            'bill_street_2' => $order_shipments['bill_street_2'],
                            'bill_city' => $order_shipments['bill_city'],
                            'bill_state' => $order_shipments['bill_state'],
                            'bill_country' => $order_shipments['bill_country'],
                            'bill_zip' => $order_shipments['bill_zip'],
                            'bill_country_iso' => $order_shipments['bill_country_iso'],
                            'fee' => [
                                'base_shippping_cost' => $base_shipping_cost,
                                'shipping_cost_tax' => 0,
                                'refunded_amount' => 0,
                                'discount_amount' => $total_discount,
                                'payment_method' => $payment_method,
                            ],
                            'total_amount' => $order_total,
                            'order_date' => $order_created_at,
                            'created_at' => $order_created_at,
                            'updated_at' => $order_updated_at,
                            'customer_email' => $order_shipments['customer_data']['email'],
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

                    if ( $hasMore ){

                        $api_base_url = "sellers/v2";
                        $api_path = $nextPageUrl;

                        $api_full_path = $api_base_url.$api_path;

                        $results = $flipkartClient->call('get', $api_full_path);
                    } else {

                        $results = [];
                    }

                }

                return true;

            }

        }

        return false;



    }

    public static function precheckProducts($user_connection_id, $products_data, $conversion_rate){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $flipkartConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $flipkart_connection_details = $flipkartConnection->userConnectionDetails;

        $store_currency_code = $flipkart_connection_details->currency;
        $store_country_code = $flipkart_connection_details->country_code;

        $user_id = $flipkartConnection->user_id;

        $flipkartClient = self::createChannelFlipkartClient($user_connection_id);



        if ( !empty($products_data) ){

            foreach ($products_data as $each_product){

                $connection_product_id = $each_product['connection_product_id'];
                $connection_product_sku = $each_product['order_product_sku'];
                $connection_product_title = $each_product['name'];

                $product = Product::findOne([
                    'user_connection_id' => $user_connection_id,
                    'connection_product_id' => $connection_product_id
                ]);

                if ( empty($product) ){
                    $product = Product::findOne([
                        'user_connection_id' => $user_connection_id,
                        'sku' => $connection_product_sku
                    ]);

                }

                if ( empty($product) ){

                    if ( $flipkartClient->checkValidate() ){

                        $api_base_url = "sellers";
                        $api_path = '';
                        if ( !empty($connection_product_id) ){

                            $api_path = "/skus/listings/".$connection_product_id;
                        }
                        if ( !empty($connection_product_sku) ){
                            $api_path = "/skus/".$connection_product_sku."/listings";
                        }

                        if ( !empty($api_path) ){
                            $api_full_path = $api_base_url.$api_path;

                            $results = $flipkartClient->call('get', $api_full_path);

                            if ( !empty($results) && !isset($results['errors']) ){

                                $product_url = '';

                                $product_weight = '';
                                $product_length = '';
                                $product_height = '';
                                $product_width = '';
                                $product_quantity = 0;
                                $product_price = 0;
                                $product_sales_price = 0;
                                $stock_status = 0;
                                $listing_status = Product::STATUS_INACTIVE;
                                $published = Product::PRODUCT_PUBLISHED_NO;

                                $published_at = date('Y-m-d H:i:s', time());
                                $updated_date = $created_date = $published_at;
                                $product_image_data = array();
                                $product_type = '';

                                if ( !empty($results['attributeValues']) ){
                                    $productAttributeList = $results['attributeValues'];

                                    $product_weight = isset($productAttributeList['package_weight'])?$productAttributeList['package_weight']:'';
                                    $product_length = isset($productAttributeList['package_length'])?$productAttributeList['package_length']:'';
                                    $product_height = isset($productAttributeList['package_height'])?$productAttributeList['package_height']:'';
                                    $product_width = isset($productAttributeList['package_breadth'])?$productAttributeList['package_breadth']:'';
                                    $system_product_quantity = isset($productAttributeList['stock_count'])?$productAttributeList['stock_count']:0;
                                    $product_quantity = isset($productAttributeList['inventory_count'])?$productAttributeList['inventory_count']:$system_product_quantity;

                                    if ( isset($productAttributeList['actual_stock_count']) ){
                                        $product_quantity = $productAttributeList['actual_stock_count'];
                                    }

                                    $product_price = isset($productAttributeList['mrp'])?$productAttributeList['mrp']:0;
                                    $product_sales_price = isset($productAttributeList['selling_price'])?$productAttributeList['selling_price']:0;
                                    $stock_status = ($product_quantity>0)?1:0;
                                    $listing_status = (isset($productAttributeList['listing_status']) && $productAttributeList['listing_status']==="ACTIVE" )?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE;

                                    $published_at = (isset($productAttributeList['fk_release_date'])&&!empty($productAttributeList['fk_release_date']))?$productAttributeList['fk_release_date']:null;
                                    $published = !empty($published_at)?Product::PRODUCT_PUBLISHED_YES:Product::PRODUCT_PUBLISHED_NO;
                                    if ( !empty($published_at) ){
                                        $updated_date = $created_date = $published_at;
                                    }

                                }

                                $product_data = [
                                    'user_id' => $user_id, // Elliot user id,
                                    'name' => $connection_product_title, // Product name,
                                    'sku' => $connection_product_sku, // Product SKU,
                                    'url' => $product_url, // Product url if null give blank value,
                                    'upc' => '', // Product upc if any,
                                    'ean' => '',
                                    'jan' => '', // Product jan if any,
                                    'isbn' => '', // Product isban if any,
                                    'mpn' => '', // Product mpn if any,
                                    'description' => $connection_product_title, // Product Description,
                                    'adult' => null,
                                    'age_group' => null,
                                    'brand' => null,
                                    'condition' => null,
                                    'gender' => null,
                                    'weight' => $product_weight, // Product weight if null give blank value,
                                    'package_length' => $product_length,
                                    'package_height' => $product_height,
                                    'package_width' => $product_width,
                                    'package_box' => null,
                                    'stock_quantity' => $product_quantity, //Product quantity,
                                    'allocate_inventory' => null,
                                    'currency' => $store_currency_code,
                                    'country_code' => $store_country_code,
                                    'stock_level' => ($stock_status>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                                    'stock_status' => ($stock_status>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                                    'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                                    'price' => $product_price, // Porduct price,
                                    'sales_price' => $product_sales_price, // Product sale price if null give Product price value,
                                    'schedule_sales_date' => null,
                                    'status' => $listing_status,
                                    'published' => $published,
                                    'permanent_hidden' => Product::STATUS_NO,
                                    'user_connection_id' => $user_connection_id,
                                    'connection_product_id' => $connection_product_id, // Stores/Channel product ID,
                                    'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s'),
                                    'updated_at' => $updated_date, // Product updated at date format date('Y-m-d H:i:s'),
                                    'type' => $product_type, // Product Type
                                    'images' => $product_image_data, // Product images data
                                    'variations' => array(),
                                    'options_set' => array(),
                                    'websites' => array(), //This is for only magento give only and blank array
                                    'conversion_rate' => $conversion_rate,
                                    'categories' => array(), // Product categroy array. If null give a blank array
                                    'flipkart_category_name' => ''
                                ];

                                self::upsertProduct($product_data);


                            }
                        }

                    }


                }
            }


        }

    }

    public static function getShipmentsDetail($user_connection_id, $orderItemId, $order_date){

        ini_set("memory_limit", "-1");
        set_time_limit(0);


        $customer_f_name = '';
        $customer_l_name = '';
        $customer_email = '';
        $cus_phone = '';
        $customer_create_date = $order_date;
        $customer_update_date = $order_date;

        $addr_fname = '';
        $addr_lname = '';
        $bill_company = '';
        $country = '';
        $country_code = '';
        $add_1 = '';
        $add_2 = '';
        $state = '';
        $city = '';
        $zip = '';
        $add_phone = '';

        $shipping_f_name = '';
        $shipping_l_name = '';
        $shipping_phone = '';
        $ship_company = '';
        $shipping_street_1 = '';
        $shipping_street_2 = '';
        $shipping_city = '';
        $shipping_state = '';
        $shipping_zip = '';
        $shipping_country = '';
        $shipping_country_code = '';


        $flipkartConnection = UserConnection::findOne(['id' => $user_connection_id]);

        $user_id = $flipkartConnection->user_id;

        $flipkartClient = self::createChannelFlipkartClient($user_connection_id);

        if ( $flipkartClient->checkValidate() ){

            $api_base_url = "sellers/v2";
            $api_path = "/orders/shipments?orderItemIds=" . $orderItemId;

            $api_full_path = $api_base_url.$api_path;

            $results = $flipkartClient->call('get', $api_full_path);


            if ( !empty($results) && isset($results['shipments']) ){

                $shipmentDetails = $results['shipments'][0];

                if ( isset($shipmentDetails['buyerDetails']) ){

                    $customer_f_name = isset($shipmentDetails['buyerDetails']['firstName'])?$shipmentDetails['buyerDetails']['firstName']:'';
                    $customer_l_name = isset($shipmentDetails['buyerDetails']['lastName'])?$shipmentDetails['buyerDetails']['lastName']:'';
                    $customer_email = isset($shipmentDetails['buyerDetails']['primaryEmail'])?$shipmentDetails['buyerDetails']['primaryEmail']:'';
                    $cus_phone = isset($shipmentDetails['buyerDetails']['contactNumber'])?$shipmentDetails['buyerDetails']['contactNumber']:'';


                }

                if ( isset($shipmentDetails['billingAddress']) ){

                    $addr_fname = isset($shipmentDetails['billingAddress']['firstName'])?$shipmentDetails['billingAddress']['firstName']:'';
                    $addr_lname = isset($shipmentDetails['billingAddress']['lastName'])?$shipmentDetails['billingAddress']['lastName']:'';
                    $bill_company = '';
                    $country = 'India';
                    $country_code = 'IN';
                    $add_1 = isset($shipmentDetails['billingAddress']['addressLine1'])?$shipmentDetails['billingAddress']['addressLine1']:'';
                    $add_2 = isset($shipmentDetails['billingAddress']['addressLine2'])?$shipmentDetails['billingAddress']['addressLine2']:'';
                    $state = isset($shipmentDetails['billingAddress']['state'])?$shipmentDetails['billingAddress']['state']:'';
                    $city = isset($shipmentDetails['billingAddress']['city'])?$shipmentDetails['billingAddress']['city']:'';
                    $zip = isset($shipmentDetails['billingAddress']['pincode'])?$shipmentDetails['billingAddress']['pincode']:'';
                    $add_phone = isset($shipmentDetails['billingAddress']['contactNumber'])?$shipmentDetails['billingAddress']['contactNumber']:'';

                }

                if ( isset($shipmentDetails['deliveryAddress']) ){

                    $shipping_f_name = isset($shipmentDetails['deliveryAddress']['firstName'])?$shipmentDetails['deliveryAddress']['firstName']:'';
                    $shipping_l_name = isset($shipmentDetails['deliveryAddress']['lastName'])?$shipmentDetails['deliveryAddress']['lastName']:'';
                    $shipping_phone = isset($shipmentDetails['deliveryAddress']['contactNumber'])?$shipmentDetails['deliveryAddress']['contactNumber']:'';
                    $shipping_street_1 = isset($shipmentDetails['deliveryAddress']['addressLine1'])?$shipmentDetails['deliveryAddress']['addressLine1']:'';
                    $shipping_street_2 = isset($shipmentDetails['deliveryAddress']['addressLine2'])?$shipmentDetails['deliveryAddress']['addressLine2']:'';
                    $shipping_city = isset($shipmentDetails['deliveryAddress']['city'])?$shipmentDetails['deliveryAddress']['city']:'';
                    $shipping_state = isset($shipmentDetails['deliveryAddress']['state'])?$shipmentDetails['deliveryAddress']['state']:'';
                    $shipping_zip = isset($shipmentDetails['deliveryAddress']['pincode'])?$shipmentDetails['deliveryAddress']['pincode']:'';
                    $shipping_country = 'India';
                    $shipping_country_code = 'IN';

                }

            }

        }


        $customer_data = array(
            'connection_customerId' => 0,
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
                    'company' => $bill_company,
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

        $shipmentDetails = [
            'customer_data' => $customer_data,
            'ship_fname' => $shipping_f_name,
            'ship_lname' => $shipping_l_name,
            'ship_phone' => $shipping_phone,
            'ship_company' => $ship_company,
            'ship_street_1' => $shipping_street_1,
            'ship_street_2' => $shipping_street_2,
            'ship_city' => $shipping_city,
            'ship_state' => $shipping_state,
            'ship_zip' => $shipping_zip,
            'ship_country' => $shipping_country,
            'ship_country_iso' => $shipping_country_code,
            'bill_fname' => $addr_fname,
            'bill_lname' => $addr_lname,
            'bill_phone' => $add_phone,
            'bill_company' => $bill_company,
            'bill_street_1' => $add_1,
            'bill_street_2' => $add_2,
            'bill_city' => $city,
            'bill_state' => $state,
            'bill_country' => $country,
            'bill_zip' => $zip,
            'bill_country_iso' => $country_code,

        ];

        return $shipmentDetails;
    }
}