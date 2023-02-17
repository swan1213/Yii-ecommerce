<?php
/**
 * Created by PhpStorm.
 * User: whitedove
 * Date: 1/15/2018
 * Time: 4:08 PM
 */

namespace frontend\components;

use common\models\Country;
use common\models\Order;
use common\models\Product;
use yii\base\Component;

class ChannelShipheroComponent extends Component
{
    public static function importCustomers(
        $api_key,
        $user_id,
        $user_connection_id
    ) {
        $addresses_api = 'https://api-gateway.shiphero.com/v1.2/general-api/list-vendors/?token='.$api_key;
        $header = array(
            'X-Api-Key: '.$api_key
        );

        $response = CustomFunction::curlHttp($addresses_api, null, 'GET', $header);
        $json_response = json_decode($response, true);

        if(isset($json_response['error'])) {
            throw new \Exception($json_response['error']);
        }
    }

    public static function importProducts(
        $api_key,
        $user_id,
        $user_connection_id
    ) {
        $addresses_api = 'https://api-gateway.shiphero.com/v1.2/general-api/get-product/?token='.$api_key;
        $header = array(
            'X-Api-Key: '.$api_key
        );

        $response = CustomFunction::curlHttp($addresses_api, null, 'GET', $header);
        $json_response = json_decode($response, true);

        if(isset($json_response['error'])) {
            throw new \Exception($json_response['error']);
        }
        if (isset($json_response['status']) && !empty($json_response['status'])) {
            if(!empty($json_response['products']) and count($json_response['products'])) {
                $products = $json_response['products'];
                foreach ($products as $single_product) {
                    if(empty($single_product)) {
                        continue;
                    }

                    // image data
                    $product_image_data = array();
                    if( !empty($single_product['images']) and count($single_product['images'])) {
                        $i = 0;
                        foreach ($single_product['images'] as $_image) {
                            if (empty($_image))
                                continue;
                            $base_img = '';
                            if ($i == 0)
                                $base_img = $_image['url'];
                            $product_image_data[] = array(
                                'image_url' => $_image['url'],
                                'connection_image_id' => '',
                                'label' => '',
                                'position' => '',
                                'base_img' => $base_img
                            );
                            $i ++;
                        }
                    }

                    $product_quantity = 0;
                    $stock_status = 0;
                    // variations
                    $variants_data = [];
                    date_default_timezone_set("UTC");

                    $created_date = date('Y-m-d H:i:s', !empty($single_product['created_at']) ? strtotime($single_product['created_at']) : time());
                    $updated_date = date("Y-m-d H:i:s");

                    if ($created_date == '1970-01-01 00:00:00')  {
                        $created_date = date('Y-m-d H:i:s', time());
                    }

                    $condition = null;

                    $product_model_data = [
                        'user_id' => $user_id, // Elliot user id,
                        'name' => $single_product['name'], // Product name,
                        'sku' => $single_product['sku'], // Product SKU,
                        'url' => '', // Product url if null give blank value,
                        'upc' => $single_product['barcode'], // Product upc if any,
                        'ean' => '',
                        'jan' => '', // Product jan if any,
                        'isbn' => '', // Product isban if any,
                        'mpn' => '', // Product mpn if any,
                        'description' => $single_product['customs_description'], // Product Description,
                        'adult' => null,
                        'age_group' => null,
                        'brand' => $single_product['brand'],
                        'condition' => $condition,
                        'gender' => null,
                        'weight' => isset($single_product['weight']) ? $single_product['weight'] : 0, // Product weight if null give blank value,
                        'package_length' => isset($single_product['height']) ? $single_product['height'] : 0,
                        'package_height' => isset($single_product['length']) ? $single_product['length'] : 0,
                        'package_width' => isset($single_product['width']) ? $single_product['width'] : 0,
                        'package_box' => '',
                        'stock_quantity' => $product_quantity, //Product quantity,
                        'allocate_inventory' => null,
                        'currency' => $single_product['value_currency'],
                        'country_code' => $single_product['country_of_manufacture'],
                        'stock_level' => ($stock_status>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                        'stock_status' => ($stock_status>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                        'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                        'price' => (float)$single_product['price'], // Porduct price,
                        'sales_price' => (float)$single_product['value'], // Product sale price if null give Product price value,
                        'schedule_sales_date' => null,
                        'status' => isset($stock_status)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
                        'permanent_hidden' => Product::STATUS_NO,
                        'user_connection_id' => $user_connection_id,
                        'connection_product_id' => $single_product['id'], // Stores/Channel product ID,
                        'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s'),
                        'updated_at' => $updated_date, // Product updated at date format date('Y-m-d H:i:s'),
                        'type' => '', // Product Type
                        'images' => $product_image_data, // Product images data
                        'variations' => $variants_data,
                        'options_set' => null,
                        'websites' => array(), //This is for only magento give only and blank array
                        'conversion_rate' => 1,
                        'categories' => array(), // Product categroy array. If null give a blank array
                    ];
                    Product::productImportingCommon($product_model_data);
                }
            }
        }
    }

    public static function importOrders($api_key, $user_id, $user_connection_id) {

        $addresses_api = 'https://api-gateway.shiphero.com/v1.2/general-api/get-orders/?token='.$api_key.'&from=1970-01-01&to=2050-12-31&all_orders=1';
        $header = array(
            'X-Api-Key: '.$api_key
        );

        $response = CustomFunction::curlHttp($addresses_api, null, 'GET', $header);
        $json_response = json_decode($response, true);

        if(isset($json_response['error'])) {
            throw new \Exception($json_response['error']);
        }
        if (isset($json_response['status']) && !empty($json_response['status']) && $json_response['status'] == 'success') {
            if(!empty($json_response['results']) and count($json_response['results'])) {
                $results = $json_response['results'];
                foreach ($results as $single_order) {
                    $order_status = null;

                    if(!empty($single_order['line_items']) and count($single_order['line_items'])) {
                        $line_items = $single_order['line_items'];
                        switch ($line_items[0]['fulfillment_status']) {
                            case "new":
                                $order_status = 'New';
                                break;
                            case "partially_shipped":
                                $order_status = "Pending";
                                break;
                            case "pending":
                                $order_status = "Pending";
                                break;
                            case "fulfilled":
                                $order_status = "Completed";
                                break;
                            case "cancelled":
                                $order_status = "Cancel";
                                break;
                            default:
                                $order_status = "Pending";
                        }
                    }

                    if(!empty($single_order['shipping_address'])) {

                        $customer_data = array(
                            'connection_customerId' => '',
                            'user_id' => $user_id,
                            'first_name' => $single_order['shipping_address']['first_name'],
                            'last_name' => $single_order['shipping_address']['last_name'],
                            'email' => $single_order['shipping_address']['email'],
                            'phone' => $single_order['shipping_address']['phone'],
                            'user_connection_id' => $user_connection_id,
                            'customer_created' =>  date('Y-m-d h:i:s', time()),
                            'updated_at' =>  date('Y-m-d h:i:s', time()),
                            'addresses' => [
                                [
                                    'first_name' => $single_order['shipping_address']['first_name'],
                                    'last_name' => $single_order['shipping_address']['last_name'],
                                    'company' => $single_order['shipping_address']['company'],
                                    'country' => (!empty($single_order['shipping_address']) and !empty($single_order['shipping_address']['country'])) ? Country::countryInfoFromCode($single_order['shipping_address']['country'])->name : 'US',
                                    'country_iso' => $single_order['shipping_address']['country'],
                                    'street_1' => $single_order['shipping_address']['address1'],
                                    'street_2' => $single_order['shipping_address']['address2'],
                                    'state' => $single_order['shipping_address']['province'],
                                    'city' => $single_order['shipping_address']['city'],
                                    'zip' => $single_order['shipping_address']['zip'],
                                    'phone' => $single_order['shipping_address']['phone'],
                                    'address_type' => 'Default',
                                ],
                            ]
                        );
                    } else {
                        $customer_data = array();
                    }

                    if(empty($customer_data)) {
                        continue;
                    }

                    $total_product_count = 0;
                    $order_product_data = array();
                    if(!empty($single_order['line_items']) and count($single_order['line_items'])) {
                        foreach ($single_order['line_items'] as $_items) {
                            $total_product_count += (int)$_items['quantity'];
                            $order_product_data[] = array(
                                'user_id' => $user_id,
                                'connection_product_id' => '',
                                'connection_variation_id' => '',
                                'name' => $_items['name'],
                                'order_product_sku' => $_items['sku'],
                                'price' => $_items['price'],
                                'qty' => (int)$_items['quantity']
                            );
                        }
                    }

                    $order_data = array(
                        'user_id' => $user_id,
                        'user_connection_id' => $user_connection_id,
                        'connection_order_id' => $single_order['id'],
                        'status' => $order_status,
                        'product_quantity' => $total_product_count,
                        'ship_fname' => $single_order['shipping_address']['first_name'],
                        'ship_lname' => $single_order['shipping_address']['last_name'],
                        'ship_phone' => $single_order['shipping_address']['phone'],
                        'ship_company' => $single_order['shipping_address']['company'],
                        'ship_street_1' => $single_order['shipping_address']['address1'],
                        'ship_street_2' => $single_order['shipping_address']['address2'],
                        'ship_city' => $single_order['shipping_address']['city'],
                        'ship_state' => $single_order['shipping_address']['province'],
                        'ship_zip' => $single_order['shipping_address']['zip'],
                        'ship_country' => (!empty($single_order['shipping_address']) and !empty($single_order['shipping_address']['country'])) ? Country::countryInfoFromCode($single_order['shipping_address']['country'])->name : 'US',
                        'ship_country_iso' => $single_order['shipping_address']['country'],
                        'bill_fname' => $single_order['billing_address']['first_name'],
                        'bill_lname' => $single_order['billing_address']['last_name'],
                        'bill_phone' => $single_order['billing_address']['phone'],
                        'bill_company' => $single_order['billing_address']['company'],
                        'bill_street_1' => $single_order['billing_address']['address1'],
                        'bill_street_2' => $single_order['billing_address']['address2'],
                        'bill_city' => $single_order['billing_address']['city'],
                        'bill_state' => $single_order['billing_address']['province'],
                        'bill_country' => (!empty($single_order['billing_address']) and !empty($single_order['origin_address']['country'])) ? Country::countryInfoFromCode($single_order['origin_address']['country'])->name : '',
                        'bill_zip' => $single_order['billing_address']['zip'],
                        'bill_country_iso' => $single_order['billing_address']['country'],
                        'fee' => [
                            'base_shippping_cost' => $single_order['shipping_price'],
                            'shipping_cost_tax' => $single_order['total_tax'],
                            'refunded_amount' => 0,
                            'discount_amount' => $single_order['discount'],
                            'payment_method' => $single_order['payment_method'],
                        ],
                        'total_amount' => $single_order['total_price'],
                        'order_date' => date('Y-m-d h:i:s', strtotime($single_order['order_date'])),
                        'created_at' => date('Y-m-d h:i:s', strtotime($single_order['created_at'])),
                        'updated_at' => date('Y-m-d h:i:s', strtotime($single_order['created_at'])),
                        'customer_email' => (!empty($single_order['shipping_address'])) ? $single_order['shipping_address']['email'] : '',
                        'currency_code' => 'USD',
                        'country_code' => (!empty($single_order['shipping_address'])) ? $single_order['shipping_address']['country'] : 'US',
                        'conversion_rate' => 1,
                        'order_products' => $order_product_data,
                        'order_customer' => $customer_data,
                    );

                    Order::orderImportingCommon($order_data);
                }
            }
        }
    }
}