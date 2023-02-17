<?php

namespace frontend\components;

use Yii;
use yii\base\Component;

use common\models\Category; 
use common\models\Country; 
use common\models\Connection;
use common\models\CurrencyConversion;
use common\models\Order;
use common\models\Product;
use common\models\ProductConnection;
use common\models\UserConnection;

class ChannelAmazonComponent extends Component{
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];  
        }
        return $randomString;
    }

    public static function deleteProduct($user_connection_id, $product_id) {
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

        $user_credential = $user_connection_row->connection_info;
        $marketplace_id = $user_credential['marketplace_id'];
        $merchant_id = $user_credential['market_id'];
        $access_key = $user_credential['access_key'];
        $secret_key = $user_credential['secret_key'];

        $domtree = new \DOMDocument('1.0', 'UTF-8');

        $amazonEnvelopeElement = $domtree->createElement('AmazonEnvelope');
        $xsiDomAttribute = $domtree->createAttribute('xsi:noNamespaceSchemaLocation');
        $xsiDomAttribute->value = 'amzn-envelope.xsd';
        $amazonEnvelopeElement->appendChild($xsiDomAttribute);
        $xsiDomAttribute = $domtree->createAttribute('xmlns:xsi');
        $xsiDomAttribute->value = 'http://www.w3.org/2001/XMLSchema-instance';
        $amazonEnvelopeElement->appendChild($xsiDomAttribute);
        $amazonEnvelopeElement = $domtree->appendChild($amazonEnvelopeElement);

        $headerTag = $domtree->createElement('Header');
        $headerTag = $amazonEnvelopeElement->appendChild($headerTag);

        $headerTag->appendChild($domtree->createElement('DocumentVersion', 1.01));
        $headerTag->appendChild($domtree->createElement('MerchantIdentifier', $merchant_id));

        $amazonEnvelopeElement->appendChild($domtree->createElement('MessageType', 'Product'));

        $messageTag = $domtree->createElement('Message');
        $messageTag = $amazonEnvelopeElement->appendChild($messageTag);

        $messageTag->appendChild($domtree->createElement('MessageID', 1));
        $messageTag->appendChild($domtree->createElement('OperationType', 'Delete'));

        $productTag = $domtree->createElement('Product');
        $productTag = $messageTag->appendChild($productTag);

        $productTag->appendChild($domtree->createElement('SKU', $product_row->sku));
        

        $feedContent = $domtree->saveXML();

        $client = new \MCS\MWSClient([
            'Marketplace_Id' => $marketplace_id,
            'Seller_Id' => $merchant_id,
            'Access_Key_ID' => $access_key,
            'Secret_Access_Key' => $secret_key
        ]);

        $result = $client->SubmitFeed('_POST_PRODUCT_DATA_', $feedContent, $debug = false);
        $feedSubmissionId = $result['FeedSubmissionId'];
        
        $count = 0;
        $delete_result = false;
        while ( $count <= 10) {
            try {
                $submitResult = $client->GetFeedSubmissionResult($feedSubmissionId);
                $status = $submitResult['StatusCode'];
                if($status == 'Complete') {
                    $delete_result = true;
                    break;
                }
            } catch (\Exception $e) {
                $count ++;
                sleep(10 * $count);
                continue;
            }
        }

        return $delete_result;
    }

    public static function feedProduct($user_connection_id, $product_id, $is_update) {
        try {
            $sku = '';
            $asin = '';
            $connection_product_id = 0;

            $product_row = Product::find()->where([
                'id' => $product_id
            ])->one();

            if(empty($product_row)) {
                throw new \Exception('Invalid product id');
            }

            if(empty($product_row->productCategories)) {
                throw new \Exception('Empty category');   
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

            if($is_update) {
                $sku = $product_row->sku;
                $asin = $product_connection_row->connection_product_id;
            } else {
                $count = 0;

                if(empty($sku)) {
                    $sku = $product_row->sku;
                } else {
                    while (1) {
                        $sku = self::generateRandomString(6);

                        $check_product_row = Product::find()->where([
                            'sku' => $sku
                        ])->one();

                        if(empty($check_product_row)) {
                            break;
                        }

                        if($count > 10) {
                            throw new \Exception('Too many time to generate the sku id, please try again later.');
                        }

                        $count ++;
                    }
                }

                $count = 0;

                while (1) {
                    $asin = self::generateRandomString(12);

                    $check_product_row = ProductConnection::find()->where([
                        'connection_product_id' => $asin
                    ])->one();

                    if(empty($check_product_row)) {
                        break;
                    }

                    if($count > 10) {
                        throw new \Exception('Too many time to generate the asin id, please try again later.');
                    }

                    $count ++;
                }
            }

            $user_credential = $user_connection_row->connection_info;

            $marketplace_id = $user_credential['marketplace_id'];
            $merchant_id = $user_credential['market_id'];
            $access_key = $user_credential['access_key'];
            $secret_key = $user_credential['secret_key'];

            switch ($marketplace_id) {
                case 'A1VC38T7YXB528':
                    // Japan
                    $content_type = 'Shift_JIS';
                    break;
                case 'AAHKV2X7AFYLW':
                    // Chinese
                    $content_type = 'UTF-8';
                    break;
                
                default:
                    $content_type = 'iso-8859-1';
                    break;
            }

            // var_dump($product_row->productCategories[0]->category->name);die;

            $domtree = new \DOMDocument('1.0', $content_type);

            $amazonEnvelopeElement = $domtree->createElement('AmazonEnvelope');
            $xsiDomAttribute = $domtree->createAttribute('xsi:noNamespaceSchemaLocation');
            $xsiDomAttribute->value = 'amzn-envelope.xsd';
            $amazonEnvelopeElement->appendChild($xsiDomAttribute);
            $xsiDomAttribute = $domtree->createAttribute('xmlns:xsi');
            $xsiDomAttribute->value = 'http://www.w3.org/2001/XMLSchema-instance';
            $amazonEnvelopeElement->appendChild($xsiDomAttribute);
            $amazonEnvelopeElement = $domtree->appendChild($amazonEnvelopeElement);

            $headerTag = $domtree->createElement('Header');
            $headerTag = $amazonEnvelopeElement->appendChild($headerTag);

            $headerTag->appendChild($domtree->createElement('DocumentVersion', 1.01));
            $headerTag->appendChild($domtree->createElement('MerchantIdentifier', $merchant_id));

            $amazonEnvelopeElement->appendChild($domtree->createElement('MessageType', 'Product'));
            $amazonEnvelopeElement->appendChild($domtree->createElement('PurgeAndReplace', 'false'));

            $messageTag = $domtree->createElement('Message');
            $messageTag = $amazonEnvelopeElement->appendChild($messageTag);

            $messageTag->appendChild($domtree->createElement('MessageID', 1));
            $messageTag->appendChild($domtree->createElement('OperationType', 'Update'));

            $productTag = $domtree->createElement('Product');
            $productTag = $messageTag->appendChild($productTag);

            $productTag->appendChild($domtree->createElement('SKU', $sku));

            $standardProductIdTag = $domtree->createElement('StandardProductID');
            $standardProductIdTag = $productTag->appendChild($standardProductIdTag);

            $standardProductIdTag->appendChild($domtree->createElement('Type', 'ASIN'));
            $standardProductIdTag->appendChild($domtree->createElement('Value', strtoupper($asin)));

            $conditionTag = $domtree->createElement('Condition');
            $conditionTag = $productTag->appendChild($conditionTag);

            // $condition = ($product_row->condition == Product::PRODUCT_CONDITION_USED)?'UsedGood':$product_row->condition;
            $conditionTag->appendChild($domtree->createElement('ConditionType', 'New'));

            $descriptionDataTag = $domtree->createElement('DescriptionData');
            $descriptionDataTag = $productTag->appendChild($descriptionDataTag);

            $descriptionDataTag->appendChild($domtree->createElement('Title', $product_row->name));
            if(!empty($product_row->description)) {
                $descriptionDataTag->appendChild($domtree->createElement('Description', $product_row->description));
            }

            // $msrpElement = $domtree->createElement('MSRP', $product_row->price);
            // $msrpDomAttribute = $domtree->createAttribute('currency');
            // $msrpDomAttribute->value = 'USD';
            // $msrpElement->appendChild($msrpDomAttribute);
            // $descriptionDataTag->appendChild($msrpElement);

            // $descriptionDataTag->appendChild($domtree->createElement('PackageWeight', number_format($product_row->weight, 2, '.' ,'')));

            $productDataTag = $domtree->createElement('ProductData');
            $productDataTag = $productTag->appendChild($productDataTag);

            $healthTag = $domtree->createElement('Health');
            $healthTag = $productDataTag->appendChild($healthTag);

            $productTypeTag = $domtree->createElement('ProductType');
            $productTypeTag = $healthTag->appendChild($productTypeTag);

            $healthMiscTag = $domtree->createElement('HealthMisc');
            $healthMiscTag = $productTypeTag->appendChild($healthMiscTag);

            $healthMiscTag->appendChild($domtree->createElement('Ingredients', 'Elliot'));
            $healthMiscTag->appendChild($domtree->createElement('Directions', 'Elliot'));
            
            $feedContent = $domtree->saveXML();
            // var_dump($feedContent);die;

            $client = new \MCS\MWSClient([
                'Marketplace_Id' => $marketplace_id,
                'Seller_Id' => $merchant_id,
                'Access_Key_ID' => $access_key,
                'Secret_Access_Key' => $secret_key
            ]);

            $result = $client->SubmitFeed('_POST_PRODUCT_DATA_', $feedContent, $debug = false);

            $feedSubmissionId = $result['FeedSubmissionId'];
            
            
        } catch (\Exception $e) {
            return json_encode(array(
                'success' => false,
                'product_id' => $product_id,
                'connection_product_id' => $connection_product_id,
                'user_connection_id' => $user_connection_id,
                'message' => $e->getMessage()
            ));
        }

        $count = 0;
        $submitResult;
        while ( $count <= 10) {
            try {
                $submitResult = $client->GetFeedSubmissionResult($feedSubmissionId);
                $status = $submitResult['StatusCode'];
                if($status == 'Complete') {
                    break;
                }
            } catch (\Exception $e) {
                $count ++;
                sleep(10 * $count);
                continue;
            }
        }

        return json_encode(array(
            'success' => true,
            'product_id' => $product_id,
            'connection_product_id' => $connection_product_id,
            'user_connection_id' => $user_connection_id
        ));

        // var_dump($submitResult);
    }

    /**
    * get and import product  from api again to db
    * @$user_credential: user credential to connect
    * @$user_id: user id
    * @$user_connection_id: id in UserConnection table
    * @$country_code: country code
    * @$currency_code: currency code
    */
    public static function importProducts(
        $user_credential,
        $user_id,
        $user_connection_id,
        $country_code,
        $currency_code
    ) {
        try {
            $marketplace_id = $user_credential['marketplace_id'];
            $merchant_id = $user_credential['market_id'];
            $access_key = $user_credential['access_key'];
            $secret_key = $user_credential['secret_key'];

            $client = new \MCS\MWSClient([
                'Marketplace_Id' => $marketplace_id,
                'Seller_Id' => $merchant_id,
                'Access_Key_ID' => $access_key,
                'Secret_Access_Key' => $secret_key
            ]);

            $reportId = $client->RequestReport('_GET_MERCHANT_LISTINGS_ALL_DATA_');
            // Wait a couple of minutes and get it's content
            $report_content = null;
            $is_get = false;

            for($i = 0; $i < 20; $i++){
                $report_content = $client->GetReport($reportId);
                if($report_content) {
                    $is_get = true;
                    break;
                }
                sleep(5);
            }

            if($is_get == false) {
                throw new \Exception('Timeout to wait for report response. Please try again later.');
            }

            if(!empty($report_content) and count($report_content)) {
                $asin_array = array_column($report_content, 'asin1');
                $productsForId = $client->GetMatchingProductForId($asin_array, $type = 'ASIN');
                
                foreach($report_content as $single_product) {
                    // import category
                    $category_list = $client->GetProductCategoriesForASIN($single_product['asin1']);
                    $product_categories = [];
                    $categories = [];

                    if(isset($category_list['ProductCategoryName'])) {
                        $categories[] = $category_list;
                    } else {
                        $categories = $category_list;                        
                    }

                    if(!empty($categories)) {
                        foreach ($categories as $single_category) {
                            $product_categories[] = $single_category['ProductCategoryId'];
                            $product_parent_category_id = 0;

                            if(isset($single_category['Parent']) and !empty($single_category['Parent'])) {
                                $product_parent_category_id = $single_category['Parent']['ProductCategoryId'];
                            }

                            $category_data = [
                                'name' => $single_category['ProductCategoryName'], // Give category name
                                'description' => '', // Give category body html
                                'parent_id' => 0,
                                'user_id' => $user_id, // Give Elliot user id,
                                'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                                'connection_category_id' => $single_category['ProductCategoryId'], // Give category id of Store/channels
                                'connection_parent_id' => $product_parent_category_id, // Give Category parent id of Elliot if null then give 0
                                'created_at' => date('Y-m-d H:i:s', time()), // Give Created at date if null then give current date format date('Y-m-d H:i:s')
                                'updated_at' => date('Y-m-d H:i:s', time()), // Give Updated at date if null then give current date format date('Y-m-d H:i:s')
                            ];

                            Category::categoryImportingCommon($category_data);
                        }
                    }

                    // import product
                    $single_product_for_id = $productsForId['found'][$single_product['asin1']];
                    $single_product_items = [];
                    $product_image_data = [];
                    $variants_data = [];

                    if (isset($single_product_for_id['ASIN'])) {
                        $single_product_items[] = $single_product_for_id;                        
                    } else {
                        $single_product_items = $single_product_for_id;
                    }

                    $brand = '';
                    $width = '';
                    $length = '';
                    $height = '';
                    $weight = '';

                    foreach ($single_product_items as $product_item) {
                        if(empty($brand) and isset($product_item['Brand'])) {
                            $brand = $product_item['Brand'];
                        }

                        if(empty($width) and isset($product_item['PackageDimensions'])) {
                            $width = $product_item['PackageDimensions']['Width'];
                        }

                        if(empty($length) and isset($product_item['PackageDimensions'])) {
                            $length = $product_item['PackageDimensions']['Length'];
                        }

                        if(empty($height) and isset($product_item['PackageDimensions'])) {
                            $height = $product_item['PackageDimensions']['Height'];
                        }

                        if(empty($weight) and isset($product_item['PackageDimensions'])) {
                            $weight = $product_item['PackageDimensions']['Weight'];
                        }

                        // import product images
                        if (isset($product_item['medium_image']) and !empty($product_item['medium_image'])) {
                            $product_image_data[] = array(
                                'image_url' => $product_item['medium_image'],
                                'connection_image_id' => 0,
                                'label' => '',
                                'position' => '',
                                'base_img' => ''
                            );
                        }

                        if (isset($product_item['small_image']) and !empty($product_item['small_image'])) {
                            $product_image_data[] = array(
                                'image_url' => $product_item['small_image'],
                                'connection_image_id' => 0,
                                'label' => '',
                                'position' => '',
                                'base_img' => ''
                            );
                        }

                        if (isset($product_item['large_image']) and !empty($product_item['large_image'])) {
                            $product_image_data[] = array(
                                'image_url' => $product_item['large_image'],
                                'connection_image_id' => 0,
                                'label' => '',
                                'position' => '',
                                'base_img' => ''
                            );
                        }

                        $variants_options = [];
                        if ( isset($product_item['PackageDimensions']) && !empty($product_item['PackageDimensions']) ){
                            foreach ($product_item['PackageDimensions'] as $key => $value) {
                                $value_data = [
                                    'label' => '',
                                    'value' => $value
                                ];
                                $oneVariantOption = [
                                    'name' => 'PackageDimensions-'.$key,
                                    'value' => $value_data
                                ];
                                $variants_options[] = $oneVariantOption;
                            }
                        }

                        if ( isset($product_item['ItemDimensions']) && !empty($product_item['ItemDimensions']) ){
                            foreach ($product_item['ItemDimensions'] as $key => $value) {
                                $value_data = [
                                    'label' => '',
                                    'value' => $value
                                ];
                                $oneVariantOption = [
                                    'name' => 'ItemDimensions-'.$key,
                                    'value' => $value_data
                                ];
                                $variants_options[] = $oneVariantOption;
                            }
                        }

                        $oneVariantData = [
                            'connection_variation_id' => $product_item['ASIN'],
                            'sku_key' => 'ASIN',
                            'sku_value' => $product_item['ASIN'],
                            'inventory_key' => 'inventory_quantity',
                            'inventory_value' =>  isset($product_item['PackageQuantity'])?$product_item['PackageQuantity']:0 ,
                            'price_key' => 'price',
                            'price_value' => isset($product_item['ListPrice'])?$product_item['ListPrice']['Amount']:$single_product['price'],
                            'weight_key' => 'weight',
                            'weight_value' => isset($product_item['PackageDimensions'])?(float)$product_item['PackageDimensions']['Weight']:0,
                            'barcode' => '',
                            'options' => $variants_options,
                            'created_at' => '', // Variant created at date format date('Y-m-d H:i:s')
                            'updated_at' => '', // Variant updated at date format date('Y-m-d H:i:s')
                        ];
                        $variants_data[] = $oneVariantData;
                    }

                    $adult = null;
                    $condition = null;

                    switch ((int)$single_product['item-condition']) {
                        case 0:
                            $condition = Product::PRODUCT_CONDITION_NEW;
                            break;

                        case 11:
                            $condition = Product::PRODUCT_CONDITION_REFURBISHED;
                            break;
                        
                        default:
                            $condition = Product::PRODUCT_CONDITION_USED;
                            break;
                    }

                    $created_date = date('Y-m-d H:i:s', $single_product['open-date']?strtotime($single_product['open-date']):time());

                    if ($created_date == '1970-01-01 00:00:00')  {
                        $created_date = date('Y-m-d H:i:s', time());
                    }

                    $product_data = [
                        'user_id' => $user_id, // Elliot user id,
                        'name' => $single_product['item-name'], // Product name,
                        'sku' => $single_product['seller-sku'], // Product SKU,
                        'url' => $single_product['image-url'], // Product url if null give blank value,
                        'upc' => '', // Product upc if any,
                        'ean' => '',
                        'jan' => '', // Product jan if any,
                        'isbn' => '', // Product isban if any,
                        'mpn' => '', // Product mpn if any,
                        'description' => $single_product['item-description'], // Product Description,
                        'adult' => $adult?Product::ADULT_YES:Product::ADULT_NO,
                        'age_group' => null,
                        'brand' => $brand,
                        'condition' => $condition,
                        'gender' => null,
                        'weight' => $weight, // Product weight if null give blank value,
                        'package_length' => $length,
                        'package_height' => $height,
                        'package_width' => $width,
                        'package_box' => null,
                        'stock_quantity' => $single_product['quantity'], //Product quantity,
                        'allocate_inventory' => null,
                        'currency' => $currency_code,
                        'country_code' => $country_code,
                        'stock_level' => ((int)$single_product['quantity']>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                        'stock_status' => ((int)$single_product['quantity']>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                        'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                        'price' => $single_product['price'], // Porduct price,
                        'sales_price' => $single_product['price'], // Product sale price if null give Product price value,
                        'schedule_sales_date' => null,
                        'status' => (strtolower($single_product['status']) == Product::STATUS_ACTIVE)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
                        'permanent_hidden' => Product::STATUS_NO,
                        'user_connection_id' => $user_connection_id,
                        'connection_product_id' => $single_product['product-id'], // Stores/Channel product ID,
                        'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s'),
                        'updated_at' => '', // Product updated at date format date('Y-m-d H:i:s'),
                        'type' => $single_product['product-id-type'], // Product Type
                        'images' => $product_image_data, // Product images data
                        'variations' => $variants_data,
                        'options_set' => null,
                        'websites' => array(), //This is for only magento give only and blank array
                        'conversion_rate' => 1,
                        'categories' => $product_categories, // Product categroy array. If null give a blank array
                    ];

                    if ( !empty($product_data) ) {
                        Product::productImportingCommon($product_data);
                    }
                }
            }
        } catch(MarketplaceWebServiceProducts_Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    /**
    * save orders in database
    * @$user_credential: order list
    * @$user_id: user id
    * @$user_connection_id: user connection id in UserConnection table
    * @$currency_code: currency code
    */
    public static function importOrders(
        $user_credential,
        $user_id,
        $user_connection_id,
        $currency_code
    ) {
        $marketplace_id = $user_credential['marketplace_id'];
        $merchant_id = $user_credential['market_id'];
        $access_key = $user_credential['access_key'];
        $secret_key = $user_credential['secret_key'];

        $client = new \MCS\MWSClient([
            'Marketplace_Id' => $marketplace_id,
            'Seller_Id' => $merchant_id,
            'Access_Key_ID' => $access_key,
            'Secret_Access_Key' => $secret_key
        ]);

        $fromDate = new \DateTime('1990-01-01');
        $orders = $client->ListOrders($fromDate);

        if (isset($orders) and ! empty($orders)) {
            foreach ($orders as $single_order) {
                $order_items = $client->ListOrderItems($single_order['AmazonOrderId']);

                date_default_timezone_set("UTC");
                $customer_id = $single_order['BuyerEmail'].'-'.$single_order['ShippingAddress']['PostalCode'];
                $order_created_at = date('Y-m-d H:i:s', isset($single_order['PurchaseDate']) ? strtotime($single_order['PurchaseDate']) : time());
                $order_updated_at = date('Y-m-d H:i:s', isset($single_order['LastUpdateDate']) ? strtotime($single_order['LastUpdateDate']) : time());

                $customer_names = array();
                $ship_names = array();

                if (isset($single_order['ShippingAddress']['Name'])) {
                   $ship_names = explode(' ', $single_order['ShippingAddress']['Name']);
                }

                if (isset($single_order['BuyerName'])) {
                   $customer_names = explode(' ', $single_order['BuyerName']);
                }

                $customer_data = array(
                    'connection_customerId' => $customer_id,
                    'user_id' => $user_id,
                    'first_name' => (count($customer_names) > 0) ? $customer_names[0] : '',
                    'last_name' => (count($customer_names) > 1) ? $customer_names[1] : '',
                    'email' => isset($single_order['BuyerEmail']) ? $single_order['BuyerEmail'] : "",
                    'phone' => '',
                    'user_connection_id' => $user_connection_id,
                    'customer_created' => $order_created_at,
                    'updated_at' => $order_updated_at,
                    'addresses' => [
                        [
                            'first_name' => explode(" ",$single_order['ShippingAddress']['Name'])[0] ? explode(" ",$single_order['ShippingAddress']['Name'])[0] : "",
                            'last_name' => explode(" ",$single_order['ShippingAddress']['Name'])[1] ? explode(" ",$single_order['ShippingAddress']['Name'])[1] : "",
                            'company' => '',
                            'country' => '',
                            'country_iso' => $single_order['ShippingAddress']['CountryCode'] ? $single_order['ShippingAddress']['CountryCode'] : "",
                            'street_1' => $single_order['ShippingAddress']['AddressLine1'] ? $single_order['ShippingAddress']['AddressLine1'] : "",
                            'street_2' => '',
                            'state' => '',
                            'city' => $single_order['ShippingAddress']['City'] ? $single_order['ShippingAddress']['City'] : "",
                            'zip' => $single_order['ShippingAddress']['PostalCode'] ? $single_order['ShippingAddress']['PostalCode'] : "",
                            'phone' => '',
                            'address_type' => 'Default'
                        ],
                    ]
                );

                $quantity = 0;
                $shipping_price = 0;
                $currency_code = '';
                $conversion_rate = 1;
                $order_product_data = array();

                foreach ($order_items as $single_order_item) {
                    $item_qty = 0;
                    $item_price = 0;

                    if(isset($single_order_item['QuantityOrdered'])) {
                        $item_qty += (int)$single_order_item['QuantityOrdered'];  
                        $quantity += $item_qty;
                    }

                    if(isset($single_order_item['ItemPrice'])) {
                        $item_price = $single_order_item['ItemPrice']['Amount'];
                        $currency_code = $single_order_item['ItemPrice']['CurrencyCode'];
                        $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency_code, 'USD');
                    }

                    $order_product_data[] = array(
                        'user_id' => $user_id,
                        'connection_product_id' => $single_order_item['ASIN'],
                        'connection_variation_id' => $single_order_item['OrderItemId'],
                        'name' => $single_order_item['Title'],
                        'order_product_sku' => $single_order_item['SellerSKU'],
                        'price' => $item_price,
                        'qty' => $item_qty,
                        'weight' => null,
                    );
                }

                foreach ($order_items as $single_order_item) {
                    if(isset($single_order_item['QuantityOrdered'])) {
                        $quantity += (int)$single_order_item['QuantityOrdered'];  
                    }
                    
                    if(isset($single_order_item['ShippingPrice'])) {
                        $shipping_price += (int)$single_order_item['ShippingPrice']['Amount'];
                    }

                    if(isset($single_order_item['ItemPrice'])) {
                        $currency_code = $single_order_item['ItemPrice']['CurrencyCode'];
                        $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency_code, 'USD');
                    }
                }

                $order_data = array(
                    'user_id' => $user_id,
                    'user_connection_id' => $user_connection_id,
                    'connection_order_id' => $single_order['AmazonOrderId'],
                    'status' => $single_order['OrderStatus'],
                    'product_quantity' => $quantity,
                    'ship_fname' => (count($ship_names) > 0) ? $ship_names[0] : '',
                    'ship_lname' => (count($ship_names) > 1) ? $ship_names[1] : '',
                    'ship_phone' => '',
                    'ship_company' => '',
                    'ship_street_1' =>$single_order['ShippingAddress']['AddressLine1'] ? $single_order['ShippingAddress']['AddressLine1'] : '',
                    'ship_street_2' => '',
                    'ship_city' => $single_order['ShippingAddress']['City'] ? $single_order['ShippingAddress']['City'] : '',
                    'ship_state' => '',
                    'ship_zip' => $single_order['ShippingAddress']['PostalCode'] ? $single_order['ShippingAddress']['PostalCode'] : '',
                    'ship_country' => '',
                    'ship_country_iso' => $single_order['ShippingAddress']['CountryCode'] ? $single_order['ShippingAddress']['CountryCode'] : '',
                    'bill_fname' => '',
                    'bill_lname' => '',
                    'bill_phone' => '',
                    'bill_company' => '',
                    'bill_street_1' => '',
                    'bill_street_2' => '',
                    'bill_city' => '',
                    'bill_state' => '',
                    'bill_country' => '',
                    'bill_zip' => '',
                    'bill_country_iso' => '',
                    'fee' => [
                        'base_shippping_cost' => $shipping_price,
                        'shipping_cost_tax' => 0,
                        'refunded_amount' => 0,
                        'discount_amount' => 0,
                        'payment_method' => isset($single_order['PaymentMethodDetails']['PaymentMethodDetail']) ? $single_order['PaymentMethodDetails']['PaymentMethodDetail'] : '',
                    ],
                    'total_amount' => isset($single_order['OrderTotal']) ? (int)$single_order['OrderTotal'] : 0,
                    'order_date' => $order_created_at,
                    'created_at' => $order_created_at,
                    'updated_at' => $order_updated_at,
                    'customer_email' => isset($single_order['BuyerEmail']) ? $single_order['BuyerEmail'] : '',
                    'currency_code' => isset($single_order['OrderTotal']['CurrencyCode']) ? $single_order['OrderTotal']['CurrencyCode'] : $currency_code,
                    'conversion_rate' => $conversion_rate,
                    'order_products' => $order_product_data,
                    'order_customer' => $customer_data,
                );

                Order::orderImportingCommon($order_data);   
            }
        }
    }
}