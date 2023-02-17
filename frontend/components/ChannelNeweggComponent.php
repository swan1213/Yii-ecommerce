<?php

namespace frontend\components;

use yii\base\Component;

use common\models\Category; 
use common\models\Connection;
use common\models\CurrencyConversion;
use common\models\Order;
use common\models\Product;
use common\models\ProductConnection;
use common\models\Notification;
use common\models\UserConnection;
use common\components\newegg\NeweggMarketplace;
use frontend\components\CustomFunction;

class ChannelNeweggComponent extends Component{
    public static function feedProduct($user_connection_id, $product_id, $is_update) {
        try {
            $connection_product_id = 0;
            $product_row = Product::find()->where([
                'id' => $product_id
            ])->one();

            if(empty($product_row)) {
                throw new \Exception('Invalid product id');
            }

            $user_connection_row = UserConnection::find()->where([
                'id' => $user_connection_id
            ])->one();

            if(empty($user_connection_row)) {
                throw new \Exception('Invalid user connection id');
            }

            $product_connection_row = ProductConnection::find()->where([
                'product_id' => $product_id,
                'user_connection_id' => $user_connection_id
            ])->one();
            
            if(empty($product_connection_row)) {
                throw new \Exception('Empty product connection data');
            }

            $product_connection_id = $product_connection_row->id;
            $connection_product_id = $product_connection_row->connection_product_id;
            $user_credential = $user_connection_row->connection_info;

            $new_egg = new NeweggMarketplace(
                $user_credential['type'],
                $user_credential['market_id'],
                $user_credential['api_key'],
                $user_credential['secret_key']
            );

            if($is_update) {
                $new_egg->updateProduct($product_row);
                $new_egg->updateProductPrice($product_row);
            } else {
                $new_egg->createProduct($product_row);
            }

            return json_encode(array(
                'success' => true,
                'product_id' => $product_id,
                'connection_product_id' => $connection_product_id,
                'user_connection_id' => $user_connection_id
            ));
        } catch (\Exception $e) {
            return json_encode(array(
                'success' => false,
                'product_id' => $product_id,
                'user_connection_id' => $user_connection_id,
                'connection_product_id' => $connection_product_id,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
    * get and import product  from api again to db
    * @$products: product list
    * @$user_id: user id
    * @$user_connection_id: id in UserConnection table
    * @$user_credential: user credential
    */
    public static function importNeweggProducts(
        $products,
        $user_id,
        $user_connection_id,
        $user_credential
    ) {
        if (isset($products) and ! empty($products)) {
            $new_egg = new NeweggMarketplace(
                $user_credential['type'],
                $user_credential['market_id'],
                $user_credential['api_key'],
                $user_credential['secret_key']
            );

            foreach($products as $single_product) {
                date_default_timezone_set("UTC");
                $age_group = ($single_product->{'Age 18+ Verification'} == 'No')?Product::AGE_GROUP_Kids:Product::AGE_GROUP_Adult;
                $product_image_data = array();
                if( !empty($single_product->{'Item Images'}) ) {
                    foreach ($single_product->{'Item Images'} as $single_image) {
                        if (empty($_image))
                            continue;
                        $product_image_data[] = array(
                            'image_url' => $single_image->Image->ImageUrl,
                            'connection_image_id' => '',
                            'label' => '',
                            'position' => '',
                            'base_img' => ''
                        );
                    }
                }
                $variants_data = [];
                $options_set_data = [];

                $conversion_rate = self::getConversionRate($single_product->{'Currency'});

                // submit subcategory request
                $submit_payload = array(
                    "OperationType" => "GetSellerSubcategoryPropertyRequest",
                    "RequestBody" => array(
                        "SubcategoryID" => $single_product->{'SubCategoryID'}
                    )
                );
                $subcategory_response = $new_egg->getSubcategoryProperties($submit_payload);
                $response_json = json_decode($subcategory_response, true);

                if(isset($response_json['IsSuccess'])) {
                    if($response_json['IsSuccess'] == true and count($response_json['ResponseBody']['SubcategoryPropertyList']) > 0) {
                        
                        $subcategory_first_item = $response_json['ResponseBody']['SubcategoryPropertyList'][0];
                        $category_data = [
                            'name' => $subcategory_first_item['SubcategoryName'], // Give category name
                            'description' => '', // Give category body html
                            'parent_id' => 0,
                            'user_id' => $user_id, // Give Elliot user id,
                            'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                            'connection_category_id' => $subcategory_first_item['SubcategoryID'], // Give category id of Store/channels
                            'connection_parent_id' => '0', // Give Category parent id of Elliot if null then give 0
                            'created_at' => date('Y-m-d H:i:s', time()), // Give Created at date if null then give current date format date('Y-m-d H:i:s')
                            'updated_at' => date('Y-m-d H:i:s', time()), // Give Updated at date if null then give current date format date('Y-m-d H:i:s')
                        ];

                        Category::categoryImportingCommon($category_data);    // Then call store modal function and give $category data

                        $variants_options = [];

                        foreach ($response_json['ResponseBody']['SubcategoryPropertyList'] as $single_variant) {
                            if($single_variant['IsRequired'] == 1) {
                                $value_data = [
                                    'label' => '',
                                    'value' => $single_product->{$single_variant['PropertyName']}
                                ];
                                $oneVariantOption = [
                                    'name' => $single_variant['PropertyName'],
                                    'value' => $value_data
                                ];
                                $variants_options[] = $oneVariantOption;
                            }
                        }

                        $oneVariantData = [
                            'connection_variation_id' => $single_product->{'NE Item #'},
                            'sku_key' => 'sku',
                            'sku_value' => $single_product->{'Seller Part #'},
                            'inventory_key' => 'inventory_quantity',
                            'inventory_value' => (int)$single_product->{'Inventory'},
                            'price_key' => 'price',
                            'price_value' => (float)$single_product->{'Selling Price'},
                            'weight_key' => 'weight',
                            'weight_value' => (float)$single_product->{'Item Weight'},
                            'barcode' => '',
                            'options' => $variants_options,
                            'created_at' => date('Y-m-d h:i:s', time()), // Variant created at date format date('Y-m-d H:i:s')
                            'updated_at' => date('Y-m-d h:i:s', time()), // Variant updated at date format date('Y-m-d H:i:s')
                        ];
                        $variants_data[] = $oneVariantData;
                    }
                }

                $condition = null;
                switch ($single_product->{'Item Condition'}) {
                    case 'New':
                        $condition = Product::PRODUCT_CONDITION_NEW;
                        break;
                    case 'Refurbished':
                        $condition = Product::PRODUCT_CONDITION_REFURBISHED;
                        break;
                    case 'UsedLikeNew':
                        $condition = Product::PRODUCT_CONDITION_USED;
                        break;
                    case 'UsedVeryGood':
                        $condition = Product::PRODUCT_CONDITION_USED;
                        break;
                    case 'UsedGood':
                        $condition = Product::PRODUCT_CONDITION_USED;
                        break;
                    case 'UsedAcceptable':
                        $condition = Product::PRODUCT_CONDITION_USED;
                        break;
                }

                $product_data = [
                    'user_id' => $user_id, // Elliot user id,
                    'name' => $single_product->{'Website Short Title'}, // Product name,
                    'sku' => $single_product->{'Seller Part #'}, // Product SKU,
                    'url' => (count($product_image_data)>0)?$product_image_data[0]['image_url']:'', // Product url if null give blank value,
                    'upc' => $single_product->{'UPC / ISBN'}, // Product upc if any,
                    'ean' => '',
                    'jan' => '', // Product jan if any,
                    'isbn' => $single_product->{'UPC / ISBN'}, // Product isban if any,
                    'mpn' => '', // Product mpn if any,
                    'description' => $single_product->{'Product Description'}, // Product Description,
                    'adult' => null,
                    'age_group' => $age_group,
                    'brand' => $single_product->{'Manufacturer'},
                    'condition' => $condition,
                    'gender' => null,
                    'weight' => (float)$single_product->{'Item Weight'}, // Product weight if null give blank value,
                    'package_length' => (float)$single_product->{'Item Length'},
                    'package_height' => (float)$single_product->{'Item Height'},
                    'package_width' => (float)$single_product->{'Item Width'},
                    'package_box' => null,
                    'stock_quantity' => (int)$single_product->{'Inventory'}, //Product quantity,
                    'allocate_inventory' => null,
                    'currency' => $single_product->{'Currency'},
                    'country_code' => $single_product->{'Country Of Origin'},
                    'stock_level' => ((int)$single_product->{'Inventory'}>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                    'stock_status' => ((int)$single_product->{'Inventory'}>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                    'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                    'price' => (float)$single_product->{'Selling Price'}, // Porduct price,
                    'sales_price' => (float)$single_product->{'Selling Price'}, // Product sale price if null give Product price value,
                    'schedule_sales_date' => date('Y-m-d H:i:s', time()),
                    'status' => isset($published_at)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
                    'published' => isset($published_at)?Product::PRODUCT_PUBLISHED_YES:Product::PRODUCT_PUBLISHED_NO,
                    'permanent_hidden' => Product::STATUS_NO,
                    'user_connection_id' => $user_connection_id,
                    'connection_product_id' => $single_product->{'NE Item #'}, // Stores/Channel product ID,
                    'created_at' => date('Y-m-d H:i:s', time()), // Product created at date format date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s', time()), // Product updated at date format date('Y-m-d H:i:s'),
                    'type' => '', // Product Type
                    'images' => $product_image_data, // Product images data
                    'variations' => $variants_data,
                    'options_set' => $options_set_data,
                    'websites' => array(), //This is for only magento give only and blank array
                    'conversion_rate' => $conversion_rate,
                    'categories' => array($single_product->{'SubCategoryID'}), // Product categroy array. If null give a blank array
                ];

                if ( !empty($product_data) ) {
                    Product::productImportingCommon($product_data);
                }
            }
        }
    }

    /**
    * import orders
    * @orders: products
    * @$user_id: user id
    * @$user_connection_id: id in UserConnection table
    * @$conversion_rate: conversion rate
    */
    public static function importNeweggOrders(
        $orders,
        $user_id,
        $user_connection_id,
        $conversion_rate
    ) {
        if (isset($orders) and ! empty($orders)) {
            foreach ($orders as $single_order) {
                $name_array = explode(' ', $single_order['CustomerName']);
                $first_name = reset($name_array);
                $last_name = end($name_array);

                $customer_data = array(
                    'connection_customerId' => $single_order['CustomerEmailAddress'],
                    'user_id' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $single_order['CustomerEmailAddress'],
                    'phone' => $single_order['CustomerPhoneNumber'],
                    'user_connection_id' => $user_connection_id,
                    'customer_created' => date('Y-m-d H:i:s', time()),
                    'updated_at' => date('Y-m-d H:i:s', time()),
                    'addresses' => [
                        [
                            'first_name' => $single_order['ShipToFirstName'],
                            'last_name' => $single_order['ShipToLastName'],
                            'company' => $single_order['ShipToCompany'],
                            'country' => $single_order['ShipToCountryCode'],
                            'country_iso' => '',
                            'street_1' => $single_order['ShipToAddress1'],
                            'street_2' => $single_order['ShipToAddress2'],
                            'state' => $single_order['ShipToStateCode'],
                            'city' => $single_order['ShipToCityName'],
                            'zip' => $single_order['ShipToZipCode'],
                            'phone' => $single_order['CustomerPhoneNumber'],
                            'address_type' => 'Default',
                        ],
                    ]
                );

                $order_product_data = [];
                if(empty($single_order['ItemInfoList'])) {
                    foreach ($single_order['ItemInfoList'] as $_items) {
                        $order_product_data[] = array(
                            'user_id' => $user_id,
                            'connection_product_id' => $_items['ItemInfo']['NeweggItemNumber'],
                            'connection_variation_id' =>'',
                            'name' => '',
                            'order_product_sku' => $_items['ItemInfo']['MfrPartNumber'],
                            'price' => $_items['ItemInfo']['UnitPrice'],
                            'qty' => $_items['ItemInfo']['OrderedQty'],
                            'weight' => null,
                        );
                    }
                }

                $status = null;
                switch ($single_order['OrderStatus']) {
                    case '0':
                        $status = 'Unshipped';
                        break;
                    case '1':
                        $status = 'Partially Shipped';
                        break;
                    case '2':
                        $status = 'Shipped';
                        break;
                    case '3':
                        $status = 'Invoiced';
                        break;
                    case '4':
                        $status = 'Voided';
                        break;
                }

                $order_data = array(
                    'user_id' => $user_id,
                    'user_connection_id' => $user_connection_id,
                    'connection_order_id' => $single_order['OrderNumber'],
                    'status' => $status,
                    'product_quantity' => $single_order['OrderQty'],
                    'ship_fname' => '',
                    'ship_lname' => '',
                    'ship_phone' => '',
                    'ship_company' => '',
                    'ship_street_1' => '',
                    'ship_street_2' => '',
                    'ship_city' => '',
                    'ship_state' => '',
                    'ship_zip' => '',
                    'ship_country' => '',
                    'ship_country_iso' => '',
                    'bill_fname' => $single_order['ShipToFirstName'],
                    'bill_lname' => $single_order['ShipToLastName'],
                    'bill_phone' => '',
                    'bill_company' => $single_order['ShipToCompany'],
                    'bill_street_1' => $single_order['ShipToAddress1'],
                    'bill_street_2' => $single_order['ShipToAddress2'],
                    'bill_city' => $single_order['ShipToCityName'],
                    'bill_state' => $single_order['ShipToStateCode'],
                    'bill_country' => $single_order['ShipToCountryCode'],
                    'bill_zip' => $single_order['ShipToZipCode'],
                    'bill_country_iso' => '',
                    'fee' => [
                        'base_shippping_cost' => $single_order['ShippingAmount'],
                        'shipping_cost_tax' => $single_order['SalesTax'],
                        'refunded_amount' => $single_order['RefundAmount'],
                        'discount_amount' => $single_order['DiscountAmount'],
                        'payment_method' => '',
                    ],
                    'total_amount' => $single_order['OrderTotalAmount'],
                    'order_date' => date('Y-m-d H:i:s', $single_order['OrderDate']?strtotime($single_order['OrderDate']):time()),
                    'created_at' => date('Y-m-d H:i:s', $single_order['OrderDate']?strtotime($single_order['OrderDate']):time()),
                    'updated_at' => date('Y-m-d H:i:s', time()),
                    'customer_email' => $single_order['CustomerEmailAddress'],
                    'currency_code' => '',
                    'country_code' => '',
                    'conversion_rate' => $conversion_rate,
                    'order_products' => $order_product_data,
                    'order_customer' => $customer_data,
                );

                Order::orderImportingCommon($order_data);
            }
        }
    }

    /**
    * get converstion rate
    * @$currency_code: country code
    * return conversation rate
    */
    public static function getConversionRate($currency_code) {
        // update the currency conversation table
        $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency_code, 'USD');

        if(empty($conversion_rate)) {
            $conversion_rate = 1;
        }

        $currency_check_row = CurrencyConversion::find()->Where(['from_currency' => $currency_code])->one();

        if (empty($currency_check_row)) {
            $store_currency_conversion = new CurrencyConversion();
            $store_currency_conversion->from_currency = $currency_code;
            $store_currency_conversion->rate = $conversion_rate;
            $store_currency_conversion->created_at = date('Y-m-d H:i:s', time());
            $store_currency_conversion->save(false);
        } else {
            $currency_check_row->from_currency = $currency_code;
            $currency_check_row->rate = $conversion_rate;
            $currency_check_row->updated_at = date('Y-m-d H:i:s', time());
            $currency_check_row->save(false);
        }

        return $conversion_rate;
    }

    /**
    * analyze the item report
    * @file_data: data from file
    */
    public static function analyze_item_report($file_data) {
        $info_array = array_shift($file_data);
        $subcategory_id = str_replace('SubCategoryID=', '', $info_array[1]);
        $header_array = array_shift($file_data);
        $data_array = array();

        foreach ($file_data as $row) {
            if(is_array($row)) {
                $item = new \stdClass();
                $item->SubCategoryID = $subcategory_id;
                foreach ($header_array as $key => $header) {
                    $item->{$header} = $row[$key];
                }
                array_push($data_array, $item );
            }
        }

        return $data_array;
    }

    /**
    * filter products with report and zip report
    * @file: file data from front end
    */
    public static function filterNeweggProducts(
        $zip_item_array,
        $report_array
    ) {
        $filter_product_array = array();
        $batch_seller_parts = array_column($zip_item_array, 'Seller Part #');
        $report_seller_parts = array_column($report_array, '﻿Seller Part #');
        sort($batch_seller_parts);
        sort($report_seller_parts);
        $is_update = false;

        if($batch_seller_parts != $report_seller_parts) {
            $is_update = true;
        }

        foreach ($zip_item_array as $item) {
            foreach ($report_array as $single_report) {
                if($item->{'Seller Part #'} == $single_report['﻿Seller Part #']) {
                    $item->{'NE Item #'} = $single_report['NE Item #'];

                    if(($item->{'Selling Price'} != $single_report['Selling Price']) or
                        ($item->{'Inventory'} != $single_report['Inventory'])
                    ) {// there are differences between the zip file and channel data
                        $item->{'Selling Price'} = $single_report['Selling Price'];
                        $item->{'Inventory'} = $single_report['Inventory'];
                        $is_update = true;
                    }
                    array_push($filter_product_array, $item);
                    break;
                }
            }
        }

        return array(
            'is_update' => $is_update,
            'products' => $filter_product_array
        );
    }

    /**
    * analyze the csv file
    * @file_data: data from file
    */
    public static function analyze_products($file_data) {
        $header_array = array_shift($file_data);
        $data_array = array();

        foreach ($file_data as $row) {
            if(is_array($row)) {
                $item = array();
                foreach ($header_array as $key => $header) {
                    $item[$header] = $row[$key];
                }
                array_push($data_array, $item );
            }
        }

        return $data_array;
    }

    /**
    * read zip
    * @directory_path: file path
    */
    public static function read_zip_file($file_path) {
        $zip = new \ZipArchive();
        $res = $zip->open($file_path);

        $re_arr = array();
        if ($res === true) {
            $path_parts = pathinfo($file_path);
            $directory_path = $path_parts['dirname'];
            for( $i = 0; $i < $zip->numFiles; $i++ ){
                $file_parts = pathinfo($zip->getNameIndex($i));
                if($file_parts['extension'] != 'csv') {
                    throw new \Exception('Invaild file type. We only accept the csv file at a moment.');
                }
                $re_arr[] = $zip->getNameIndex($i);
            }

            $csv_path = $directory_path . '/csvs/';
            $zip->extractTo($csv_path);
            $item_array = array();

            for( $j = 0; $j < count($re_arr); $j++ ){
                $item_file_name = $csv_path . $re_arr[$j];
                $csv_result = self::read_csv_file($item_file_name);
                $analyze_result = self::analyze_item_report($csv_result);
                $item_array = array_merge($item_array, $analyze_result);
            }

            $zip->close();
            $remvoe_path = CustomFunction::delete_directory($csv_path);

            return $item_array;
        } else {
            $prefix_error_msg = 'When open the zip file you provided, ';
            $error_msg = "Can't open zip file.";
            switch ($res) {
                case \ZipArchive::ER_EXISTS:
                    $error_msg = 'File already exists.';
                    break;

                case \ZipArchive::ER_INCONS:
                    $error_msg = 'Zip archive inconsistent.';
                    break;

                case \ZipArchive::ER_INVAL:
                    $error_msg = 'Invalid argument.';
                    break;

                case \ZipArchive::ER_MEMORY:
                    $error_msg = 'Malloc failure.';
                    break;

                case \ZipArchive::ER_NOENT:
                    $error_msg = 'No such file.';
                    break;

                case \ZipArchive::ER_NOZIP:
                    $error_msg = 'Not a zip archive.';
                    break;

                case \ZipArchive::ER_OPEN:
                    $error_msg = "Can't open file.";
                    break;

                case \ZipArchive::ER_READ:
                    $error_msg = 'Read error.';
                    break;

                case \ZipArchive::ER_SEEK:
                    $error_msg = 'Seek error.';
                    break;
            }

            throw new \Exception($prefix_error_msg.$error_msg);
        }
    }

    /**
    * read csv file
    * @$path param: csv file path
    */
    public static function read_csv_file($path) {
        if (file_exists($path)) {
            $file_handle = fopen($path, 'r');
            while (!feof($file_handle) ) {
                $line_of_text[] = fgetcsv($file_handle, 4096);
            }
            fclose($file_handle);
            return $line_of_text;
        } else {
            throw new \Exception('Invaild file path to parse csv.');
        }
        
    }

    /**
    * get newegg orders through api
    * @$user_credential: user credential
    * return order list
    */
    public static function getNeweggOrders($user_credential) {
        $new_egg = new NeweggMarketplace(
            $user_credential['type'],
            $user_credential['market_id'],
            $user_credential['api_key'],
            $user_credential['secret_key']
        );
        // submitReportRequest
        $submit_payload = array(
            "OperationType" => "OrderListReportRequest",
            "RequestBody" => array(
                "OrderReportCriteria" => array(
                    "RequestType" => "ORDER_LIST_REPORT",
                    "KeywordsType" => "0",
                    "Status" => "4"
                )
            )
        );
        $report_request_response = $new_egg->submitReportRequest($submit_payload);
        $response_json = json_decode($report_request_response, true);

        if($response_json['IsSuccess'] == true and count($response_json['ResponseBody']['ResponseList']) > 0) {
            $loop_counter = 0;
            $delay_time = 30;
            $request_id = $response_json['ResponseBody']['ResponseList'][0]['RequestId'];
            //Get Report Status service
            $request_payload = array(
                "OperationType" => "GetReportStatusRequest",
                "RequestBody" => array(
                    "GetRequestStatus" => array(
                        "RequestIDList" => array(
                            "RequestID" => $request_id 
                        )
                    )  
                )
            );

            while ( 1 ) {
                if($loop_counter > 15) {
                    throw new \Exception('Timeout.');
                    break;
                }

                $report_status_response = $new_egg->getReportStatus($request_payload);
                $response_json = json_decode($report_status_response, true);
                $loop_counter ++;

                if($response_json['IsSuccess'] == true and count($response_json['ResponseBody']['ResponseList']) > 0) {
                    if($response_json['ResponseBody']['ResponseList'][0]['RequestStatus'] == 'FINISHED') {
                        //  Get Report Result service
                        $result_payload = array(
                            "OperationType" => "OrderListReportRequest",
                            "RequestBody" => array(
                                "RequestID" => $request_id,
                                "PageInfo" => array(
                                    "PageSize" => 20,
                                    "PageIndex" => 1
                                )  
                            )
                        );
                        $result_status_response = $new_egg->getReportResult($result_payload);
                        $result_response_json = json_decode($result_status_response, true);

                        if($result_response_json['IsSuccess'] == true and !empty($result_response_json['ResponseBody']['OrderInfoList'])) {
                            $order_response = self::read_csv_file($result_response_json['ResponseBody']['OrderInfoList']);
                            return $order_response;
                        } else {
                            return null;
                        }
                        
                        break;
                    } else {
                        sleep($delay_time + $loop_counter * 10);
                        continue;
                    }

                } else { // fail curl
                    throw new \Exception('Fail to get report status service.');
                    break;
                }
            }
        } else {
            throw new \Exception('Fail to submit report request.');
        }
    }

    /**
    * get newegg products through api
    * @$user_credential: user credential
    * return product list
    */
    public static function getNeweggProducts($user_credential) {
        $new_egg = new NeweggMarketplace(
            $user_credential['type'],
            $user_credential['market_id'],
            $user_credential['api_key'],
            $user_credential['secret_key']
        );
                
        // submitReportRequest
        $submit_payload = array(
            "OperationType" => "DailyInventoryReportRequest",
            "RequestBody" => array(
                "DailyInventoryReportCriteria" => array(
                    "RequestType" => "DAILY_INVENTORY_REPORT",
                    "FileType" => "CSV",
                    "FulfillType" => "0"
                )
            )
        );
        $report_request_response = $new_egg->submitReportRequest($submit_payload);

        $response_json = json_decode($report_request_response, true);

        if($response_json['IsSuccess'] == true and count($response_json['ResponseBody']['ResponseList']) > 0) {
            $loop_counter = 0;
            $delay_time = 30;
            $request_id = $response_json['ResponseBody']['ResponseList'][0]['RequestId'];
            //Get Report Status service
            $request_payload = array(
                "OperationType" => "GetReportStatusRequest",
                "RequestBody" => array(
                    "GetRequestStatus" => array(
                        "RequestIDList" => array(
                            "RequestID" => $request_id 
                        )
                    )  
                )
            );

            while ( 1 ) {
                if($loop_counter > 15) {
                    throw new \Exception('Timeout.');
                    break;
                }

                $report_status_response = $new_egg->getReportStatus($request_payload);
                $response_json = json_decode($report_status_response, true);
                $loop_counter ++;

                if($response_json['IsSuccess'] == true and count($response_json['ResponseBody']['ResponseList']) > 0) {
                    if($response_json['ResponseBody']['ResponseList'][0]['RequestStatus'] == 'FINISHED') {
                        //  Get Report Result service
                        var_dump($request_id);
                        $result_payload = array(
                            "OperationType" => "DailyInventoryReportRequest",
                            "RequestBody" => array(
                                "RequestID" => $request_id,
                                "PageInfo" => array(
                                    "PageSize" => 20,
                                    "PageIndex" => 1
                                )  
                            )
                        );
                        $result_status_response = $new_egg->getReportResult($result_payload);
                        $result_response_json = json_decode($result_status_response, true);

                        if($result_response_json['NeweggAPIResponse']['IsSuccess'] == true and !empty($result_response_json['NeweggAPIResponse']['ResponseBody']['ReportFileURL'])) {
                            $product_response = self::read_csv_file($result_response_json['NeweggAPIResponse']['ResponseBody']['ReportFileURL']);
                            return $product_response;
                        } else {
                            throw new \Exception('Fail to get report result.');
                        }
                        
                        break;
                    } else {
                        sleep($delay_time + $loop_counter * 10);
                        continue;
                    }

                } else { // fail curl
                    throw new \Exception('Fail to get report status service.');
                    break;
                }
            }
        } else {
            throw new \Exception('Fail to submit report request.');
        }
    }
}