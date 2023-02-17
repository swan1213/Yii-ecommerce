<?php

namespace frontend\components;

use yii\base\Component;

use common\models\Category; 
use common\models\Connection;
use common\models\CurrencyConversion;
use common\models\Order;
use common\models\Product;
use common\models\ProductConnection;
use common\models\UserConnection;
use frontend\components\CustomFunction;

class ChannelWechatComponent extends Component{
    public static function deleteProduct($user_connection_id, $product_id) {
        $product_connection_row = ProductConnection::find()->where([
            'product_id' => $product_id,
            'user_connection_id' => $user_connection_id
        ])->one();
        
        if(empty($product_connection_row)) {
            return false;
        }

        $user_connection_row = UserConnection::find()->where([
            'id' => $user_connection_id
        ])->one();


        if(empty($user_connection_row)) {
            return false;
        }

        $user_credential = $user_connection_row->connection_info;
        $connection_product_id = $product_connection_row->connection_product_id;

        if($user_credential['type'] == 'wechat') {
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$user_credential['client_id'].'&secret='.$user_credential['client_secret'];  
            $response = CustomFunction::curlHttp($url);//here cannot use file_get_contents  
            $json_response = json_decode($response, true);
            
            if(!(isset($json_response['access_token']) and $json_response['access_token'])) {
                return false;
            }
            $token = $json_response['access_token'];

            $header = array(
                'Content-Type: application/json;charset=utf-8'
            );
            $url = 'https://api.weixin.qq.com/merchant/del?access_token=' . $token;
            $data = array(
                'product_id' => $connection_product_id
            );

            $json = CustomFunction::curlHttp($url, $data, 'POST', $header, 1);
            $response_data = json_decode($json, true);

            if($response_data['errcode'] != 0) {
                return false;
            }

            return true;
        }
    }

    public static function generateWechatFeedUpdate($user_connection_id, $product_instance, $connection_parent_id) {
        if(empty($product_instance)) {
            throw new \Exception('Empty product');
        }

        $imgs = [];

        $image_rows = $product_instance->productImages;

        if(!empty($image_rows) and count($image_rows) > 0) {
            foreach ($image_rows as $single_image) {
                $imgs[] = $single_image->link;
            }    
        }

        $sku_info = [];

        if(!empty($product_instance->sku)) {
            $vids = explode(':' ,$product_instance->sku);
            $sku_item = [
                'id' => $vids[0],
                'vid' => []
            ];

            for($i=1; $i<count($vids); $i++) {
                $sku_item['vid'][] = $vids[$i];
            }

            $sku_info[] = $sku_item;
        }
        


        $feed = [
            'product_id' => $connection_parent_id,
            'product_base' => [
                'name' => $product_instance->name,
                'sku_info' => $sku_info,
                'main_img' => $product_instance->url,
                'img' => $imgs
            ],
            'sku_list' => [
                '0' => [
                    'sku_id' => $product_instance->sku,
                    'ori_price' => $product_instance->price,
                    'icon_url' => $product_instance->url,
                    'price' => $product_instance->sales_price,
                    'quantity' => $product_instance->stock_quantity
                ]
            ]
        ];

        return $feed;
    }

    public static function generateWechatFeedCreation($user_connection_id, $product_instance) {
        if(empty($product_instance)) {
            throw new \Exception('Empty product');
        }

        $imgs = [];
        $image_rows = $product_instance->productImages;

        if(!empty($image_rows) and count($image_rows) > 0) {
            foreach ($image_rows as $single_image) {
                $imgs[] = $single_image->link;
            }    
        }

        $feed = [
            'product_base' => [
                'name' => $product_instance->name,
                'sku_info' => [],
                'main_img' => $product_instance->url,
                'img' => $imgs
            ],
            'sku_list' => [
                '0' => [
                    'sku_id' => '',
                    'ori_price' => $product_instance->price,
                    'icon_url' => $product_instance->url,
                    'price' => $product_instance->sales_price,
                    'quantity' => $product_instance->stock_quantity
                ]
            ]
        ];

        return $feed;
    }

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

            if($user_credential['type'] == 'wechat') {
                $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$user_credential['client_id'].'&secret='.$user_credential['client_secret'];  
                $response = CustomFunction::curlHttp($url);//here cannot use file_get_contents  
                $json_response = json_decode($response, true);

                if(!(isset($json_response['access_token']) and $json_response['access_token'])) {
                    throw new \Exception($json_response['errmsg']);
                }
                $token = $json_response['access_token'];

                $header = array(
                    'Content-Type: application/json;charset=utf-8'
                );

                if($is_update) {
                    $url = 'https://api.weixin.qq.com/merchant/update?access_token=' . $token;
                    $feed = self::generateWechatFeedUpdate($user_connection_id, $product_row, $product_connection_row->connection_product_id);
                } else {
                    $url = 'https://api.weixin.qq.com/merchant/create?access_token=' . $token;
                    $feed = self::generateWechatFeedCreation($user_connection_id, $product_row);
                }

                $json = CustomFunction::curlHttp($url, $feed, 'POST', $header, 1);
                $response_data = json_decode($json, true);

                if($response_data['errcode'] != 0) {
                    throw new \Exception($response_data['errmsg']);
                }

                return json_encode(array(
                    'success' => true,
                    'product_id' => $product_id,
                    'connection_product_id' => $connection_product_id,
                    'user_connection_id' => $user_connection_id
                ));
            }

            return json_encode(array(
                'success' => false,
                'product_id' => $product_id,
                'user_connection_id' => $user_connection_id,
                'connection_product_id' => $connection_product_id,
                'message' => 'We do not provide the export functionality using walkethechat api'
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

	public static function checkProjectAvalability(
        $header,
        $user_id,
        $user_connection_id,
        $user_connection_detail_row
    ) {
    	$conversion_rate = CurrencyConversion::getCurrencyConversionRate($user_connection_detail_row->country_code, 'USD');

        if(empty($conversion_rate)) {
            $conversion_rate = 1;
        }

        $url = 'https://cms-api.walkthechat.com/contacts';
        $contacts_data = CustomFunction::curlHttp($url, null, 'GET', $header);
        $json_contacts_data = json_decode($contacts_data, true);

        if(!isset($json_contacts_data['error'])) {
            self::importWalkthechatCategories(
                $header,
                $user_id,
                $user_connection_id
            );
            
            self::importWalkthechatProducts(
                $header,
                $user_id,
                $user_connection_id,
                $conversion_rate
            );

            self::importWalkthechatOrders(
                $header,
                $user_id,
                $user_connection_id,
                $conversion_rate
            );

        }
    }

    public static function importWalkthechatCategories(
        $header,
        $user_id,
        $user_connection_id
    ) {
        // get the category list
        $url = 'https://cms-api.walkthechat.com/categories/product?page=1&limit=999999&sort=-modifed';
        $categories_data = CustomFunction::curlHttp($url, null, 'GET', $header);
        $json_categories_data = json_decode($categories_data, true);

        if(!isset($json_categories_data['error']) and isset($json_categories_data['categoriesProduct'])) {
            foreach ($json_categories_data['categoriesProduct'] as $single_category) {
                $category_data = [
                    'name' => $single_category['name'], // Give category name
                    'description' => $single_category['description'], // Give category body html
                    'parent_id' => '0',
                    'user_id' => $user_id, // Give Elliot user id,
                    'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                    'connection_category_id' => $single_category['groupId'], // Give category id of Store/channels
                    'connection_parent_id' => '0', // Give Category parent id of Elliot if null then give 0
                    'created_at' => date('Y-m-d h:i:s', $single_category['created']), // Give Created at date if null then give current date format date('Y-m-d H:i:s')
                    'updated_at' => date('Y-m-d h:i:s', $single_category['modified']), // Give Updated at date if null then give current date format date('Y-m-d H:i:s')
                ];

                Category::categoryImportingCommon($category_data);    // Then call store modal function and give $category data
            }
        }
    }

    public static function importWalkthechatProducts(
        $header,
        $user_id,
        $user_connection_id,
        $conversion_rate
    ) {
        $url = 'https://cms-api.walkthechat.com/products/?page=1&limit=9999999&sort=-sort';
        $products_data = CustomFunction::curlHttp($url, null, 'GET', $header);
        $json_products_data = json_decode($products_data, true);

        if(!isset($json_products_data['error'])) {
            if(isset($json_products_data['products']) and count($json_products_data['products'])) {
                foreach ($json_products_data['products'] as $single_product) {
                    $group_id = $single_product['groupId'];
                    $url = "https://cms-api.walkthechat.com/products/$group_id/variants/";
                    $product_variants_data = CustomFunction::curlHttp($url, null, 'GET', $header);
                    $json_product_variants_data = json_decode($product_variants_data, true);
                    
                    $product_image_data = array();
                    $variants_data = array();
                    $options_set_data = array();
                    $product_quantity = 0;
                    $single_product_created_at;
                    $single_product_updated_at;

                    if(!isset($json_product_variants_data['error']) and isset($json_product_variants_data['variants'])) {
                        foreach ($json_product_variants_data['variants']['en']['variants'] as $single_variant) {
                            $variants_qty = $single_variant['unit'] ? (int)$single_variant['unit'] : 0;
                            $product_quantity += $variants_qty;
                            if ($product_quantity > 0) {
                                $stock_status = 1;
                            } else {
                                $stock_status = 0;
                            }
                            
                            $variants_weight = isset($single_variant['weight']) ?  $single_variant['weight'] : 0;
                            $variants_created_at = date('Y-m-d h:i:s', $single_variant['created']);
                            $variants_updated_at = date('Y-m-d h:i:s', $single_variant['modified']);
                            if(empty($single_product_created_at)) {
                                $single_product_created_at = $variants_created_at;
                            }

                            if(empty($single_product_created_at)) {
                                $single_product_updated_at = $variants_updated_at;
                            }

                            $variants_options = [];
                            if ( isset($single_variant['variant']) && !empty($single_variant['variant']) ){
                                foreach ($single_variant['variant'] as $varient_option_key => $varient_option_value) {
                                    $value_data = [
                                        'label' => '',
                                        'value' => $varient_option_value
                                    ];
                                    $oneVariantOption = [
                                        'name' => $varient_option_key,
                                        'value' => $value_data
                                    ];
                                    $variants_options[] = $oneVariantOption;
                                }
                            }

                            $oneVariantData = [
                                'connection_variation_id' => $single_variant['_id'],
                                'sku_key' => 'sku',
                                'sku_value' => $single_variant['sku'],
                                'inventory_key' => 'inventory_quantity',
                                'inventory_value' => $variants_qty,
                                'price_key' => 'price',
                                'price_value' => $single_variant['price'],
                                'weight_key' => 'weight',
                                'weight_value' => $variants_weight,
                                'upc' => $single_variant['barcode'],
                                'options' => $variants_options,
                                'created_at' => $variants_created_at, // Variant created at date format date('Y-m-d H:i:s')
                                'updated_at' => $variants_updated_at, // Variant updated at date format date('Y-m-d H:i:s')
                            ];
                            $variants_data[] = $oneVariantData;
                        }

                        if(isset($json_product_variants_data['variants']) and isset($json_product_variants_data['variants']['en']['product'])) {
                            $gallery_id = $json_product_variants_data['variants']['en']['product']['gallery'];
                            $url = "https://cms-api.walkthechat.com/galleries/$gallery_id";
                            $gallery_data = CustomFunction::curlHttp($url, null, 'GET', $header);
                            $json_gallery_data = json_decode($gallery_data, true);

                            if(!isset($json_gallery_data['error']) and isset($json_gallery_data['galleries'])) {
                                foreach ($json_gallery_data['galleries'] as $_image) {
                                    $product_image_data[] = array(
                                        'connection_image_id' => $_image['_id'],
                                        'image_url' => $_image['url'],
                                        'label' => '',
                                        'position' => '',
                                        'base_img' => $single_product['thumbnail'],
                                    );
                                }
                            }
                        }
                    }

                    $product_data = [
                        'user_id' => $user_id, // Elliot user id,
                        'name' => $single_product['title'], // Product name,
                        'sku' => $single_product['sku'], // Product SKU,
                        'url' => $single_product['thumbnail'], // Product url if null give blank value,
                        'upc' => '', // Product upc if any,
                        'ean' => '',
                        'jan' => '', // Product jan if any,
                        'isbn' => '', // Product isban if any,
                        'mpn' => '', // Product mpn if any,
                        'description' => $single_product['description'], // Product Description,
                        'adult' => null,
                        'age_group' => null,
                        'brand' => null,
                        'condition' => null,
                        'gender' => null,
                        'weight' => $single_product['weight'], // Product weight if null give blank value,
                        'package_length' => null,
                        'package_height' => null,
                        'package_width' => null,
                        'package_box' => null,
                        'stock_quantity' => $single_product['unit'], //Product quantity,
                        'allocate_inventory' => null,
                        'currency' => 'CNY',
                        'country_code' => 'CN',
                        'stock_level' => ($single_product['stock'])?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                        'stock_status' => ($single_product['stock'])?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                        'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                        'price' => $single_product['price'], // Porduct price,
                        'sales_price' => $single_product['salesPrice'], // Product sale price if null give Product price value,
                        'schedule_sales_date' => null,
                        'status' => ($single_product['stock'])?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
                        'published' => ($single_product['stock'])?Product::PRODUCT_PUBLISHED_YES:Product::PRODUCT_PUBLISHED_NO,
                        'permanent_hidden' => Product::STATUS_NO,
                        'user_connection_id' => $user_connection_id,
                        'connection_product_id' => $single_product['_id'], // Stores/Channel product ID,
                        'created_at' => empty($single_product_created_at)?date('Y-m-d h:i:s', time()):$single_product_created_at, // Product created at date format date('Y-m-d H:i:s'),
                        'updated_at' => empty($single_product_updated_at)?date('Y-m-d h:i:s', time()):$single_product_updated_at, // Product updated at date format date('Y-m-d H:i:s'),
                        'type' => '', // Product Type
                        'images' => $product_image_data, // Product images data
                        'variations' => $variants_data,
                        'options_set' => $options_set_data,
                        'websites' => array(), //This is for only magento give only and blank array
                        'conversion_rate' => $conversion_rate,
                        'categories' => $single_product['categoryGroupId'], // Product categroy array. If null give a blank array
                    ];

                    if ( !empty($product_data) ) {
                        Product::productImportingCommon($product_data);
                    }
                }
            }
        }
    }

    public static function importWalkthechatOrders(
        $header,
        $user_id,
        $user_connection_id,
        $conversion_rate
    ) {
        // get the order list
        $url = 'https://cms-api.walkthechat.com/orders?page=1&limit=10&sort=-created';
        $orders_data = CustomFunction::curlHttp($url, null, 'GET', $header);
        $json_orders_data = json_decode($orders_data, true);

        if(!isset($json_orders_data['error']) and isset($json_orders_data['orders'])) {
            foreach ($json_orders_data['orders'] as $single_order) {
                $status = '';
                switch ($single_order['status']) {
                    case "waiting-for-shipment":
                        $status = 'Pending';
                    break;
                    case "Awaiting Fulfillment":
                        $status = 'Pending';
                    break;
                    case "Awaiting Shipment":
                        $status = 'Pending';
                    break;
                    case "authorized":
                        $status = "In Transit";
                    break;
                    case "partially_paid":
                        $status = "Completed";
                    break;
                    case "paid":
                        $status = "Completed";
                    break;
                    case "completed":
                        $status = "Completed";
                    break;
                    case "partially_refunded":
                        $status = "Refunded";
                    break;
                    case "Partially Refunded":
                        $status = "Refunded";
                    break;
                    case "refunded":
                        $status = "Refunded";
                    break;
                    case "voided":
                        $status = "Cancel";
                    break;
                    default:
                        $status = "Pending";
                }

                $total_product_count = 0;
                $order_product_data = array();
                $product_order_data = $single_order['products'];

                if (isset($single_order['productsDetails'])) {
                    $productsDetails_order_data = $single_order['productsDetails'];
                }

                if (!empty($product_order_data)) {
                    $total_product_count = $product_order_data[0]->quantity;
                } elseif (!empty($productsDetails_order_data)) {
                    $total_product_count = 0;

                    foreach ($productsDetails_order_data->en as $p_details) {
                        $total_product_count += (float)$p_details->cartProductQuantity;
                        $order_product_data[] = array(
                            'user_id' => $user_id,
                            'connection_product_id' => $p_details['groupId'],
                            'connection_variation_id' => '',
                            'name' => $p_details['title'],
                            'order_product_sku' => $p_details['sku'],
                            'price' => $p_details['price'],
                            'qty' => $p_details['cartProductQuantity'],
                            'weight' => $p_details['weight']
                        );
                    }
                }

                $customer_data = array(
                    'connection_customerId' => $single_order['userId'],
                    'user_id' => $user_id,
                    'first_name' => $single_order['customerFields']['address']['name'],
                    'last_name' => $single_order['customerFields']['address']['surname'],
                    'email' => '',
                    'phone' => $single_order['customerFields']['address']['phone'],
                    'user_connection_id' => $user_connection_id,
                    'customer_created' => date('Y-m-d h:i:s', time()),
                    'updated_at' => date('Y-m-d h:i:s', time()),
                    'addresses' => [
                        [
                            'first_name' => $single_order['customerFields']['address']['name'],
                            'last_name' => $single_order['customerFields']['address']['surname'],
                            'company' => '',
                            'country' => $single_order['customerFields']['address']['country'],
                            'country_iso' => '',
                            'street_1' => $single_order['customerFields']['address']['address'],
                            'street_2' => '',
                            'state' => $single_order['customerFields']['address']['province'],
                            'city' => $single_order['customerFields']['address']['city'],
                            'zip' => $single_order['customerFields']['address']['zipcode'],
                            'phone' => $single_order['customerFields']['address']['phone'],
                            'address_type' => 'Default',
                        ],
                    ]
                );

                $order_data = array(
                    'user_id' => $user_id,
                    'user_connection_id' => $user_connection_id,
                    'connection_order_id' => $single_order['_id'],
                    'status' => $status,
                    'product_quantity' => $total_product_count,
                    'ship_fname' => '',
                    'ship_lname' => '',
                    'ship_phone' => '',
                    'ship_company' => '',
                    'ship_street_1' => '',
                    'ship_street_2' => '',
                    'ship_city' => '',
                    'ship_state' => '',
                    'ship_country' => '',
                    'ship_country_iso' => '',
                    'bill_fname' => '',
                    'bill_lname' => $single_order['customerFields']['address']['surname'],
                    'bill_phone' => $single_order['customerFields']['address']['phone'],
                    'bill_company' => $single_order['customerFields']['address']['name'],
                    'bill_street_1' => $single_order['customerFields']['address']['address'],
                    'bill_street_2' => '',
                    'bill_city' => $single_order['customerFields']['address']['city'],
                    'bill_state' => $single_order['customerFields']['address']['province'],
                    'bill_country' => $single_order['customerFields']['address']['country'],
                    'bill_zip' => $single_order['customerFields']['address']['zipcode'],
                    'bill_country_iso' => '',
                    'fee' => [
                        'base_shippping_cost' => null,
                        'shipping_cost_tax' => null,
                        'refunded_amount' => null,
                        'discount_amount' => null,
                        'payment_method' => $single_order['paymentMethod'],
                    ],
                    'total_amount' => $single_order['price'],
                    'order_date' => date('Y-m-d h:i:s', $single_category['created']),
                    'created_at' => date('Y-m-d h:i:s', $single_category['created']),
                    'updated_at' => date('Y-m-d h:i:s', $single_category['modified']),
                    'customer_email' => '',
                    'currency_code' => 'CNY',
                    'country_code' => 'CN',
                    'conversion_rate' => $conversion_rate,
                    'order_products' => $order_product_data,
                    'order_customer' => $customer_data,
                );
                Order::orderImportingCommon($order_data);
            }
        }
    }

    /**
    * import categories
    * @$token: access token
    * @$user_id: user id
    * @$user_connection_id: id in UserConnection table
    */
    public static function importWechatCategories(
        $token,
        $user_id,
        $user_connection_id
    ) {
        // get the category list
        $url = 'https://api.weixin.qq.com/merchant/group/getall?access_token=' . $token;
        $header = array(
            'Content-Type: application/json;charset=utf-8'
        );
        $json = CustomFunction::curlHttp($url, null, 'GET', $header);
        $response_data = json_decode($json, true);

        
        if(isset($response_data['errcode']) and $response_data['errcode'] != 0){
            throw new \Exception($response_data['errmsg']);
        }

        // import
        foreach($response_data['groups_detail'] as $single_category)
        {
            $category_data = [
                'name' => $single_category['group_name'], // Give category name
                'description' => '', // Give category body html
                'parent_id' => 0,
                'user_id' => $user_id, // Give Elliot user id,
                'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                'connection_category_id' => $single_category['group_id'], // Give category id of Store/channels
                'connection_parent_id' => '0', // Give Category parent id of Elliot if null then give 0
                'created_at' => date('Y-m-d H:i:s', time()), // Give Created at date if null then give current date format date('Y-m-d H:i:s')
                'updated_at' => date('Y-m-d H:i:s', time()), // Give Updated at date if null then give current date format date('Y-m-d H:i:s')
            ];

            Category::categoryImportingCommon($category_data);    // Then call store modal function and give $category data
        }
    }

    /**
    * import products
    * @$token: access token
    * @$user_id: user id
    * @$user_connection_id: id in UserConnection table
    * @$conversation_rate: conversation rate
    */
    public static function importWechatProducts(
        $token,
        $user_id,
        $user_connection_id,
        $conversion_rate
    ) {
        // get the product list
        $url = 'https://api.weixin.qq.com/merchant/getbystatus?access_token=' . $token;
        $post_data = array(
            'status' => 0
        );
        $header = array(
            'Content-Type: application/json;charset=utf-8'
        );

        $json = CustomFunction::curlHttp($url, $post_data, 'POST', $header);
        $response_data = json_decode($json, true);

        if(isset($response_data['errcode']) and $response_data['errcode'] != 0){
            throw new \Exception($response_data['errmsg']);
        }
        
        foreach ($response_data['products_info'] as $single_product) {

            $product_image_data = array();
            foreach ($single_product['product_base']['img'] as $_image) {
                if(empty($_image)) {
                    continue;
                }

                $product_image_data[] = array(
                    'connection_image_id' => '',
                    'image_url' => $_image,
                    'label' => '',
                    'position' => '',
                    'base_img' => $single_product['product_base']['main_img']
                );
            }

            $options_set_data = [];
            if(isset($single_product['product_base']['property']) and !empty($single_product['product_base']['property'])) {
                foreach ($single_product['product_base']['property'] as $eachOption){
                    $eachOptionValues = [];
                    $eachOptionValues[] = [
                        'label' => '',
                        'value' => $eachOption['vid']
                    ];
                    $oneOptions_set_data = [
                        'name' => $eachOption['id'],
                        'values' => $eachOptionValues
                    ];
                    $options_set_data[] = $oneOptions_set_data;
                }
            }

            $product_quantity = 0;

            $variants_data = [];
            foreach ($single_product['sku_list'] as $_variants) {
                $variants_qty = $_variants['quantity'];
                $product_quantity += intval($_variants['quantity']);

                $oneVariantData = [
                    'connection_variation_id' => $_variants['sku_id'],
                    'sku_key' => 'sku',
                    'sku_value' => $_variants['sku_id'],
                    'inventory_key' => 'inventory_quantity',
                    'inventory_value' => $variants_qty,
                    'price_key' => 'price',
                    'price_value' => $_variants['price'],
                    'weight_key' => 'weight',
                    'weight_value' => null,
                    'barcode' => $_variants['product_code'],
                    'options' => array(),
                    'created_at' => date('Y-m-d h:i:s', time()), // Variant created at date format date('Y-m-d H:i:s')
                    'updated_at' => date('Y-m-d h:i:s', time()), // Variant updated at date format date('Y-m-d H:i:s')
                ];
                $variants_data[] = $oneVariantData;
            }

            if(count($variants_data) > 0) {
                $product_data = [
                    'user_id' => $user_id, // Elliot user id,
                    'name' => $single_product['product_base']['name'], // Product name,
                    'sku' => $single_product['sku_list'][0]['sku_id'], // Product SKU,
                    'url' => $single_product['sku_list'][0]['icon_url'], // Product url if null give blank value,
                    'upc' => $variants_data[0]['barcode'], // Product upc if any,
                    'ean' => '',
                    'jan' => '', // Product jan if any,
                    'isbn' => '', // Product isban if any,
                    'mpn' => '', // Product mpn if any,
                    'description' => $single_product['product_base']['detail_html'], // Product Description,
                    'adult' => null,
                    'age_group' => null,
                    'brand' => null,
                    'condition' => null,
                    'gender' => null,
                    'weight' => $single_product['delivery_info']['weight'], // Product weight if null give blank value,
                    'package_length' => null,
                    'package_height' => null,
                    'package_width' => null,
                    'package_box' => null,
                    'stock_quantity' => $product_quantity, //Product quantity,
                    'allocate_inventory' => null,
                    'currency' => 'CNY',
                    'country_code' => 'CN',
                    'stock_level' => ($product_quantity>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                    'stock_status' => ($product_quantity>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                    'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                    'price' => $single_product['sku_list'][0]['ori_price'], // Porduct price,
                    'sales_price' => $single_product['sku_list'][0]['price'], // Product sale price if null give Product price value,
                    'schedule_sales_date' => null,
                    'status' => ($product_quantity>0)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
                    'permanent_hidden' => Product::PRODUCT_PERMANENT_NO,
                    'user_connection_id' => $user_connection_id,
                    'connection_product_id' => $single_product['product_id'], // Stores/Channel product ID,
                    'created_at' => date('Y-m-d h:i:s', time()), // Product created at date format date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d h:i:s', time()), // Product updated at date format date('Y-m-d H:i:s'),
                    'type' => null, // Product Type
                    'images' => $product_image_data, // Product images data
                    'variations' => $variants_data,
                    'options_set' => $options_set_data,
                    'websites' => array(), //This is for only magento give only and blank array
                    'conversion_rate' => $conversion_rate,
                    'categories' => $single_product['product_base']['category_id'], // Product categroy array. If null give a blank array
                ];

                if ( !empty($product_data) ) {
                    Product::productImportingCommon($product_data);
                }
            }
        }
    }

    /**
    * import orders
    * @token: access token
    * @user_id: user id
    * @user_connection_id: id in UserConnection
    * @conversion_rate: conversion rate
    */
    public static function importWechatOrders(
        $token,
        $user_id,
        $user_connection_id,
        $conversion_rate
    ) {
        // get the product list
        $url = 'https://api.weixin.qq.com/merchant/order/getbyfilter?access_token=' . $token;
        $post_data = array(
            'status' => 2,
            'begintime' => strtotime('2000-01-01'),
            'endtime' => strtotime(date('Y-m-d h:i:s', time()))
        );

        $json = CustomFunction::curlHttp($url, $post_data, 'POST');
        $response_data = json_decode($json, true);

        if(isset($response_data['errcode']) and $response_data['errcode'] != 0){
            throw new \Exception($response_data['errmsg']);
        }

        if (isset($response_data['order_list']) and ! empty($response_data['order_list'])) {
            foreach ($response_data['order_list'] as $single_order) {
                date_default_timezone_set("UTC");
                $customer_id = $single_order['order_id'];
                $order_created_at = date('Y-m-d H:i:s', isset($single_order['order_create_time']) ? strtotime($single_order['order_create_time']) : time());
                $order_updated_at = null;

                $order_product_data = array();
                // foreach ($orderitems_response as $_items) {
                //     if(isset($_items['Currency'])) {
                //         $currency_code = $_items['Currency'];
                //     }
                    
                //     $order_product_data[] = array(
                //         'user_id' => $user_id,
                //         'connection_product_id' => isset($_items['Sku']) ? $_items['Sku'] : '',
                //         'connection_variation_id' => isset($_items['Variation']) ? $_items['Variation'] : '',
                //         'name' => isset($_items['Name']) ? $_items['Name'] : '',
                //         'order_product_sku' => isset($_items['Sku']) ? $_items['Sku'] : '',
                //         'price' => isset($_items['ItemPrice']) ? (float)$_items['ItemPrice'] : 0,
                //         'qty' => null,
                //         'weight' => null,
                //     );
                // }

                $customer_data = array(
                    'connection_customerId' => $customer_id,
                    'user_id' => $user_id,
                    'first_name' => $single_order['receiver_name'],
                    'last_name' => '',
                    'email' => '',
                    'phone' => $single_order['receiver_mobile'],
                    'user_connection_id' => $user_connection_id,
                    'customer_created' => $order_created_at,
                    'updated_at' => date('Y-m-d h:i:s', time()),
                    'addresses' => [
                        [
                            'first_name' => $single_order['receiver_name'],
                            'last_name' => '',
                            'company' => '',
                            'country' => '',
                            'country_iso' => '',
                            'street_1' => $single_order['receiver_address'],
                            'street_2' => '',
                            'state' => '',
                            'city' => $single_order['receiver_city'],
                            'zip' => '',
                            'phone' => $single_order['receiver_mobile'],
                            'address_type' => 'Default'
                        ]
                    ]
                );

                $order_data = array(
                    'user_id' => $user_id,
                    'user_connection_id' => $user_connection_id,
                    'connection_order_id' => $single_order['order_id'],
                    'status' => $single_order['order_status'],
                    'product_quantity' => $single_order['product_count'],
                    'ship_fname' => '',
                    'ship_lname' => '',
                    'ship_phone' => '',
                    'ship_company' => '',
                    'ship_street_1' => '',
                    'ship_street_2' => '',
                    'ship_city' => '',
                    'ship_state' => '',
                    'ship_zip' => '',
                    'ship_country' => 'China',
                    'ship_country_iso' => '',
                    'bill_fname' => $single_order['receiver_name'],
                    'bill_lname' => '',
                    'bill_phone' => $single_order['receiver_phone'],
                    'bill_company' => '',
                    'bill_street_1' => $single_order['receiver_address'],
                    'bill_street_2' => '',
                    'bill_city' => $single_order['receiver_city'],
                    'bill_state' => $single_order['receiver_province'],
                    'bill_country' => 'China',
                    'bill_zip' => '',
                    'bill_country_iso' => 'CN',
                    'fee' => [
                        'base_shippping_cost' => $single_order['order_total_price'],
                        'shipping_cost_tax' => 0,
                        'refunded_amount' => 0,
                        'discount_amount' => 0,
                        'payment_method' => '',
                    ],
                    'total_amount' => $single_order['order_total_price'],
                    'order_date' => $order_created_at,
                    'created_at' => $order_created_at,
                    'updated_at' => $order_updated_at,
                    'customer_email' => '',
                    'currency_code' => 'CN',
                    'conversion_rate' => $conversion_rate,
                    'order_products' => $order_product_data,
                    'order_customer' => $customer_data,
                );

                Order::orderImportingCommon($order_data);           
            }
        }
    }
}