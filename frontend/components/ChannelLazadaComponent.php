<?php

namespace frontend\components;

use yii\base\Component;

use common\models\Category; 
use common\models\Country; 
use common\models\Connection;
use common\models\CurrencyConversion;
use common\models\Order;
use common\models\Product;
use common\models\ProductConnection;
use common\models\ProductAttribution;
use common\models\UserConnection;
use common\components\lazada\LazadaSellerCenter;

class ChannelLazadaComponent extends Component{
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

        $lazada = new LazadaSellerCenter(
            $user_credential['api_url'],
            $user_credential['market_id'],
            $user_credential['api_key']
        );

        $domtree = new \DOMDocument('1.0', 'UTF-8');
        $requestTag = $domtree->createElement("Request");
        $requestTag = $domtree->appendChild($requestTag);

        $productTag = $domtree->createElement("Product");
        $productTag = $requestTag->appendChild($productTag);

        $skusTag = $domtree->createElement("Skus");
        $skusTag = $productTag->appendChild($skusTag);

        $skuTag = $domtree->createElement("Sku");
        $skuTag = $skusTag->appendChild($skuTag);

        $skuTag->appendChild($domtree->createElement('SellerSku', $product_row->sku));
        
        try {
            $lazada->Product()->RemoveProduct($domtree->saveXML());
            return true;
        } catch (\Exception $e) {
            // var_dump($e->getMessage());
            return false;
        }
    }

    public static function GetCategoryAttributes($lazada, $category_id) {
        $parameters = array(
            'PrimaryCategory' => $category_id
        );
        $attributes = $lazada->Product()->GetCategoryAttributes($parameters);
        $normal_required_attributes = [];
        $sku_required_attributes = [];
        $variations = [];

        if(!empty($attributes)) {
            foreach ($attributes as $single_attribute) {
                if($single_attribute->isMandatory == 1) {
                    if($single_attribute->attributeType == 'normal') {
                        $normal_required_attributes[] = $single_attribute;  
                    } else if($single_attribute->attributeType == 'sku') {
                        $sku_required_attributes[] = $single_attribute;
                    }
                }

                if($single_attribute->attributeType == 'sku') {
                    $pos = strpos($single_attribute->name, 'size');

                    if ($pos !== false) {
                        $variations[] = $single_attribute;
                    }
                    if($single_attribute->name == 'color_family') {
                        $variations[] = $single_attribute;   
                    }
                }
            }
        }

        return array(
            'normal' => $normal_required_attributes,
            'sku' => $sku_required_attributes,
            'variations' => $variations
        );
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

            $category_id = '';
            $latest_date = null;

            foreach ($product_row->productCategories as $single_product_category) {
                if(
                    $user_connection_row->user_id == $single_product_category->user_id and
                    $single_product_category->category->user_connection_id == $user_connection_id
                ) {
                    $date = empty($single_product_category->category->updated_at)?$single_product_category->category->created_at:$single_product_category->category->created_at;

                    if(empty($latest_date) or $date > $latest_date) {
                        $latest_date = $date;
                        $category_id = $single_product_category->category->connection_category_id;
                    }
                }
            }

            $user_credential = $user_connection_row->connection_info;

            $lazada = new LazadaSellerCenter(
                $user_credential['api_url'],
                $user_credential['market_id'],
                $user_credential['api_key']
            );

            /* create a dom document with encoding utf8 */
            $domtree = new \DOMDocument('1.0', 'UTF-8');

            if($is_update) {
                $requestTag = $domtree->createElement("Request");
                $requestTag = $domtree->appendChild($requestTag);

                $productTag = $domtree->createElement("Product");
                $productTag = $requestTag->appendChild($productTag);

                $productTag->appendChild($domtree->createElement('PrimaryCategory', $category_id));

                $attributesTag = $domtree->createElement("Attributes");
                $attributesTag = $productTag->appendChild($attributesTag);
                $name = preg_replace("/&#?[a-z0-9]{2,8};/i", "", strip_tags($product_row->name));
                $attributesTag->appendChild($domtree->createElement('name', $name));
                $description = preg_replace("/&#?[a-z0-9]{2,8};/i", "", strip_tags($product_row->description));
                $attributesTag->appendChild($domtree->createElement('short_description', $description));

                $skusTag = $domtree->createElement("Skus");
                $skusTag = $productTag->appendChild($skusTag);

                $skuTag = $domtree->createElement("Sku");
                $skuTag = $skusTag->appendChild($skuTag);

                $skuTag->appendChild($domtree->createElement('SellerSku', $product_row->sku));
                $skuTag->appendChild($domtree->createElement('quantity', $product_row->stock_quantity));
                $skuTag->appendChild($domtree->createElement('price', $product_row->price));
                $skuTag->appendChild($domtree->createElement('special_price', $product_row->sales_price));
                $skuTag->appendChild($domtree->createElement('package_length', $product_row->package_length));
                $skuTag->appendChild($domtree->createElement('package_height', $product_row->package_height));
                $skuTag->appendChild($domtree->createElement('package_weight', $product_row->weight));
                $skuTag->appendChild($domtree->createElement('package_width', $product_row->package_width));
                
                $image_rows = $product_row->productImages;

                if(!empty($image_rows) and count($image_rows) > 0) {
                    $imagesTag = $domtree->createElement("Images");
                    $imagesTag = $skuTag->appendChild($imagesTag);

                    foreach ($image_rows as $single_image) {
                        $imagesTag->appendChild($domtree->createElement('Image', $single_image->link));
                    }    
                }
            } else {
                $attribute_response = self::getCategoryAttributes($lazada, $category_id);
                $normal_required_attributes = $attribute_response['normal'];
                $sku_required_attributes = $attribute_response['sku'];

                $requestTag = $domtree->createElement("Request");
                $requestTag = $domtree->appendChild($requestTag);

                $productTag = $domtree->createElement("Product");
                $productTag = $requestTag->appendChild($productTag);

                $productTag->appendChild($domtree->createElement('PrimaryCategory', $category_id));

                $attributesTag = $domtree->createElement("Attributes");
                $attributesTag = $productTag->appendChild($attributesTag);

                $extra_fields = $product_connection_row->extra_fields;
                $json_data = $product_connection_row->json_data;

                foreach ($normal_required_attributes as $normal_attribute) {
                    if($normal_attribute->inputType == 'richText') {
                        $defaultValue = 'Elliot';

                        switch ($normal_attribute->name) {
                            case 'short_description':
                                $defaultValue = self::stripTags($product_row->description);
                                break;
                            
                            default:
                                $defaultValue = 'Elliot';
                                break;
                        }

                        $defaultValue = empty($defaultValue)?'Elliot':$defaultValue;
                        $attributesTag->appendChild($domtree->createElement($normal_attribute->name, $defaultValue));
                    } else {
                        $defaultValue = 'Elliot';

                        switch ($normal_attribute->name) {
                            case 'name':
                                $defaultValue = $product_row->name;
                                break;
                            case 'brand':
                                $defaultValue = $product_row->brand;
                                break;
                            case 'name_ms':
                                $defaultValue = $product_row->name;
                                break;
                            
                            default:
                                if(isset($product_row->{$normal_attribute->name})) {
                                    $defaultValue = $product_row->{$normal_attribute->name};
                                }

                                switch ($normal_attribute->inputType) {
                                    case 'multiSelect':
                                        if(!in_array($defaultValue, $normal_attribute->options)) {
                                            if(empty($normal_attribute->options) or count($normal_attribute->options) == 0) {
                                                $defaultValue = 'Elliot';    
                                            } else {
                                                $defaultValue = $normal_attribute->options[0]->name;
                                            }
                                        }
                                        break;
                                    case 'singleSelect':
                                        if(!in_array($defaultValue, $normal_attribute->options)) {
                                            if(empty($normal_attribute->options) or count($normal_attribute->options) == 0) {
                                                $defaultValue = 'Elliot';    
                                            } else {
                                                $defaultValue = $normal_attribute->options[0]->name;
                                            }
                                        }
                                        break;
                                    case 'numeric':
                                        $defaultValue = empty($defaultValue)?0:(float)$defaultValue;
                                        break;

                                    default:
                                        $defaultValue = empty($defaultValue)?'Elliot':$defaultValue;
                                        break;
                                }
                                
                                break;
                        }

                        $defaultValue = (empty($defaultValue) and $defaultValue != 0)?'Elliot':$defaultValue;
                        $attributesTag->appendChild($domtree->createElement($normal_attribute->name, $defaultValue));
                    }
                }

                $skusTag = $domtree->createElement("Skus");
                $skusTag = $productTag->appendChild($skusTag);

                $skuTag = $domtree->createElement("Sku");
                $skuTag = $skusTag->appendChild($skuTag);

                foreach ($sku_required_attributes as $sku_attribute) {
                    if($sku_attribute->inputType == 'richText') {
                        $skuTag->appendChild($domtree->createElement($sku_attribute->name, 'Elliot'));
                    } else {
                        $defaultValue = 'Elliot';

                        switch ($sku_attribute->name) {
                            case 'SellerSku':
                                $defaultValue = $product_row->sku;
                                break;
                            case 'package_weight':
                                $defaultValue = $product_row->weight;
                                break;
                            case 'package_weight':
                                $defaultValue = $product_row->weight;
                                break;
                            
                            default:
                                if(isset($product_row->{$sku_attribute->name})) {
                                    $defaultValue = $product_row->{$sku_attribute->name};
                                }

                                switch ($sku_attribute->inputType) {
                                    case 'multiSelect':
                                        if(!in_array($defaultValue, $sku_attribute->options)) {
                                            if(empty($sku_attribute->options) or count($sku_attribute->options) == 0) {
                                                $defaultValue = 'Elliot';    
                                            } else {
                                                $defaultValue = $sku_attribute->options[0]->name;
                                            }
                                        }
                                        break;
                                    case 'singleSelect':
                                        if(!in_array($defaultValue, $sku_attribute->options)) {
                                            if(empty($sku_attribute->options) or count($sku_attribute->options) == 0) {
                                                $defaultValue = 'Elliot';    
                                            } else {
                                                $defaultValue = $sku_attribute->options[0]->name;
                                            }
                                        }
                                        break;
                                    case 'numeric':
                                        $defaultValue = empty($defaultValue)?0:(float)$defaultValue;
                                        break;

                                    default:
                                        $defaultValue = empty($defaultValue)?'Elliot':$defaultValue;
                                        break;
                                }

                                break;  
                        }

                        $defaultValue = (empty($defaultValue) and $defaultValue != 0)?'Elliot':$defaultValue;
                        $skuTag->appendChild($domtree->createElement($sku_attribute->name, $defaultValue));
                    }
                }

                $skuTag->appendChild($domtree->createElement('quantity', $product_row->stock_quantity));
                
                $image_rows = $product_row->productImages;
                // $imagesTag = $domtree->createElement("Images");
                // $imagesTag = $skuTag->appendChild($imagesTag);

                // if(!empty($image_rows) and count($image_rows) > 0) {
                //     foreach ($image_rows as $single_image) {
                //         $imagesTag->appendChild($domtree->createElement('Image', $single_image->link));
                //     }    
                // }
            }

            // /* get the xml printed */
            // Header('Content-type: text/xml');
            // echo $domtree->saveXML();
            // die;

            try {
                if($is_update) {// Elliot product
                    $lazada->Product()->UpdateProduct($domtree->saveXML());
                } else {
                    $lazada->Product()->CreateProduct($domtree->saveXML());
                }
            } catch (\Exception $e) {
                $message = '';
                if(isset($e->errors->ErrorDetail[0])) {
                    $message = 'Field: '.$e->errors->ErrorDetail[0]->Field;
                    $message .= ' , Content: ';
                    $message .=  $e->errors->ErrorDetail[0]->Message;
                } else {
                    $message = $e->getMessage();
                }

                throw new \Exception($message);
            }

            return json_encode(array(
                'success' => true,
                'product_id' => $product_id,
                'user_connection_id' => $user_connection_id,
                'connection_product_id' => $connection_product_id
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

    public static function stripTags($data) {
        return preg_replace("/&#?[a-z0-9]{2,8};/i", "", strip_tags($data));
    }

	public static function flatten($elements) {
        $flatArray = array();

        foreach ($elements as $item) {
            if (array_key_exists('children', $item)) {
                $flatArray = array_merge($flatArray, self::flatten($item->children));
                unset($item->children);
                $flatArray[] = $item;
            } else {
                $flatArray[] = $item;
            }
        }
        return $flatArray;
    }

    /**
    * save orders in database
    * @$user_id: id in User table
    * @$user_connection_id: id in UserConnection table
    * @$currency_code: currency code of the product
    * @$user_credential: user credential
    */
    public static function importOrders(
        $user_id,
        $user_connection_id,
        $currency_code,
        $user_credential
    ) {
        $lazada = new LazadaSellerCenter(
            $user_credential['api_url'],
            $user_credential['market_id'],
            $user_credential['api_key']
        );
        $orders = $lazada->Order()->GetOrders();

        if (!empty($orders) and count($orders) > 0) {
            foreach ($orders as $single_order) {
                date_default_timezone_set("UTC");
                if(empty($single_order)) {
                    continue;
                }

                $customer_id = $single_order->AddressShipping->Phone.'-'.$single_order->AddressShipping->PostCode;
                $order_created_at = date('Y-m-d H:i:s', isset($single_order->CreatedAt) ? strtotime($single_order->CreatedAt) : time());
                $order_updated_at = date('Y-m-d H:i:s', isset($single_order->UpdatedAt) ? strtotime($single_order->UpdatedAt) : time());

                // get items with orderid
                $order_items = $lazada->Order()->GetOrders(array('OrderId' => $single_order->OrderId));
                
                $conversion_rate = 1;
                $order_currency_code = $currency_code;
                $bill_country_code = '';
                $ship_country_code = '';

                if(isset($single_order->AddressBilling->Country)) {
                    $row = Country::countryInfoFromName($single_order->AddressBilling->Country);

                    if(!empty($row)) {
                        $bill_country_code = $row->sortname;
                    }
                }

                if(isset($single_order->AddressShipping->Country)) {
                    $row = Country::countryInfoFromName($single_order->AddressShipping->Country);

                    if(!empty($row)) {
                        $ship_country_code = $row->sortname;
                    }
                }

                if(!empty($order_items) and count($order_items) > 0) {
                    $order_product_data = array();
                    foreach ($order_items as $order_single_item) {
                        if(isset($order_single_item->Currency)) {
                            $order_currency_code = $order_single_item->Currency;
                            $conversion_rate = CurrencyConversion::getCurrencyConversionRate($order_currency_code, 'USD');
                        }

                        $order_product_data[] = array(
                            'user_id' => $user_id,
                            'connection_product_id' => isset($order_single_item->Sku) ? $order_single_item->Sku : '',
                            'connection_variation_id' => isset($order_single_item->OrderItemId) ? $order_single_item->OrderItemId : '',
                            'name' => isset($order_single_item->Name) ? $order_single_item->Name : '',
                            'order_product_sku' => isset($order_single_item->Sku) ? $order_single_item->Sku : '',
                            'price' => isset($order_single_item->ItemPrice) ? (float)$order_single_item->ItemPrice : 0,
                            'qty' => null,
                            'weight' => null,
                        );
                    }
                }

                $customer_data = array(
                    'connection_customerId' => $customer_id,
                    'user_id' => $user_id,
                    'first_name' => isset($single_order->CustomerFistName) ? $single_order->CustomerFistName : "",
                    'last_name' => isset($single_order->CustomerLastName) ? $single_order->CustomerLastName : "",
                    'email' => isset($single_order->AddressBilling->CustomerEmail) ? $single_order->AddressBilling->CustomerEmail : "",
                    'phone' => isset($single_order->AddressBilling->Phone) ? $single_order->AddressBilling->Phone : "",
                    'user_connection_id' => $user_connection_id,
                    'customer_created' => $order_created_at,
                    'updated_at' => $order_updated_at,
                    'addresses' => [
                        [
                            'first_name' => isset($single_order->AddressBilling->FirstName) ? $single_order->AddressBilling->FirstName : '',
                            'last_name' => isset($single_order->AddressBilling->LastName) ? $single_order->AddressBilling->LastName : '',
                            'company' => '',
                            'country' => isset($single_order->AddressBilling->Country) ? $single_order->AddressBilling->Country : '',
                            'country_iso' => $bill_country_code,
                            'street_1' => isset($single_order->AddressBilling->Address1) ? $single_order->AddressBilling->Address1 : '',
                            'street_2' => isset($single_order->AddressBilling->Address2) ? $single_order->AddressBilling->Address2 : '',
                            'state' => '',
                            'city' => isset($single_order->AddressBilling->City) ? $single_order->AddressBilling->City : '',
                            'zip' => isset($single_order->AddressBilling->PostCode) ? $single_order->AddressBilling->PostCode : '',
                            'phone' => isset($single_order->AddressBilling->Phone) ? $single_order->AddressBilling->Phone : '',
                            'address_type' => 'Default'
                        ],
                    ]
                );

                $order_data = array(
                    'user_id' => $user_id,
                    'user_connection_id' => $user_connection_id,
                    'connection_order_id' => $single_order->OrderId,
                    'status' => isset($single_order->Statuses) ? $single_order->Statuses[0] : '',
                    'product_quantity' => isset($single_order->ItemsCount) ? (int)$single_order->ItemsCount : 0,
                    'ship_fname' => isset($single_order->AddressShipping->FirstName) ? $single_order->AddressShipping->FirstName : '',
                    'ship_lname' => isset($single_order->AddressShipping->LastName) ? $single_order->AddressShipping->LastName : '',
                    'ship_phone' => isset($single_order->AddressShipping->Phone) ? $single_order->AddressShipping->Phone : '',
                    'ship_company' => '',
                    'ship_street_1' => isset($single_order->AddressShipping->Address1) ? $single_order->AddressShipping->Address1 : '',
                    'ship_street_2' => isset($single_order->AddressShipping->Address2) ? $single_order->AddressShipping->Address2 : '',
                    'ship_city' => isset($single_order->AddressShipping->City) ? $single_order->AddressShipping->City : '',
                    'ship_state' => '',
                    'ship_zip' => isset($single_order->AddressShipping->PostCode) ? $single_order->AddressShipping->PostCode : '',
                    'ship_country' => isset($single_order->AddressShipping->Country) ? $single_order->AddressShipping->Country : '',
                    'ship_country_iso' => $ship_country_code,
                    'bill_fname' => isset($single_order->AddressBilling->FirstName) ? $single_order->AddressBilling->FirstName : '',
                    'bill_lname' => isset($single_order->AddressBilling->LastName) ? $single_order->AddressBilling->LastName : '',
                    'bill_phone' => isset($single_order->AddressBilling->Phone) ? $single_order->AddressBilling->Phone : '',
                    'bill_company' => '',
                    'bill_street_1' => isset($single_order->AddressBilling->Address1) ? $single_order->AddressBilling->Address1 : '',
                    'bill_street_2' => isset($single_order->AddressBilling->Address2) ? $single_order->AddressBilling->Address2 : '',
                    'bill_city' => isset($single_order->AddressBilling->City) ? $single_order->AddressBilling->City : '',
                    'bill_state' => '',
                    'bill_country' => isset($single_order->AddressBilling->Country) ? $single_order->AddressBilling->Country : '',
                    'bill_zip' => isset($single_order->AddressBilling->PostCode) ? $single_order->AddressBilling->PostCode : '',
                    'bill_country_iso' => $bill_country_code,
                    'fee' => [
                        'base_shippping_cost' => isset($single_order->ShippingFee) ? (float)$single_order->ShippingFee : 0,
                        'shipping_cost_tax' => 0,
                        'refunded_amount' => 0,
                        'discount_amount' => 0,
                        'payment_method' => isset($single_order->PaymentMethod) ? $single_order->PaymentMethod : '',
                    ],
                    'total_amount' => isset($single_order->Price) ? (float)$single_order->Price : 0,
                    'order_date' => $order_created_at,
                    'created_at' => $order_created_at,
                    'updated_at' => $order_updated_at,
                    'customer_email' => isset($single_order->AddressShipping->CustomerEmail) ? $single_order->AddressShipping->CustomerEmail : '',
                    'currency_code' => $order_currency_code,
                    'conversion_rate' => $conversion_rate,
                    'country_code' => $bill_country_code,
                    'order_products' => $order_product_data,
                    'order_customer' => $customer_data,
                );

                Order::orderImportingCommon($order_data);           
            }
        }
    }

    /**
    * import products
    * @$user_id: id in User table
    * @$user_connection_id: id in UserConnection table
    * @$country_code: country code
    * @$currency_code: currency code
    * @$user_credential: user credential
    */
    public static function importProducts(
        $user_id,
        $user_connection_id,
        $country_code,
        $currency_code,
        $user_credential
    ) {
        $conversion_rate = CurrencyConversion::getCurrencyConversionRate($currency_code, 'USD');
        $lazada = new LazadaSellerCenter(
            $user_credential['api_url'],
            $user_credential['market_id'],
            $user_credential['api_key']
        );
        $category_tree_response = $lazada->Product()->GetCategoryTree();
        $category_list = self::flatten($category_tree_response);

    	$products = $lazada->Product()->GetProducts();

        if (isset($products) and ! empty($products)) {
            foreach ($products as $single_product) {
                if(empty($single_product)) {
                    continue;
                }

                $categories = array();
                if(isset($single_product->PrimaryCategory) and !empty($single_product->PrimaryCategory)) {
                    $categories[] = $single_product->PrimaryCategory;
                    $category_name = '';

                    foreach ($category_list as $single_category) {
                        if(isset($single_category->categoryId) and ($single_category->categoryId == $single_product->PrimaryCategory)) {
                            $category_name = isset($single_category->name) ? $single_category->name : '';
                            break;
                        }
                    }

                    $category_data = [
                        'name' => $category_name, // Give category name
                        'description' => '', // Give category body html
                        'parent_id' => 0,
                        'user_id' => $user_id, // Give Elliot user id,
                        'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                        'connection_category_id' => $single_product->PrimaryCategory, // Give category id of Store/channels
                        'connection_parent_id' => '0', // Give Category parent id of Elliot if null then give 0
                        'created_at' => date('Y-m-d H:i:s', time()),
                        'updated_at' => date('Y-m-d H:i:s', time())
                    ];

                    Category::categoryImportingCommon($category_data);
                }

                if(!empty($single_product->Skus) and count($single_product->Skus) > 0) {
                    $first_sku_product = $single_product->Skus[0];

                    $product_image_data = array();
                    if( !empty($first_sku_product->Images) and isset($first_sku_product->Images) ) {
                        foreach ($first_sku_product->Images as $_image) {
                            if (empty($_image))
                                continue;
                            $product_image_data[] = array(
                                'image_url' => $_image,
                                'connection_image_id' => '',
                                'label' => '',
                                'position' => '',
                                'base_img' => isset($first_sku_product->Url) ? $first_sku_product->Url : ''
                            );
                        }
                    }

                    $attribute_response = self::getCategoryAttributes($lazada, $single_product->PrimaryCategory);
                    $normal_required_attributes = $attribute_response['normal'];
                    $sku_required_attributes = $attribute_response['sku'];
                    $product_variations = $attribute_response['variations'];
                    $extra_fields = [];
                    $json_data = [];

                    // foreach ($normal_required_attributes as $normal_attribute) {
                    //     if(isset($single_product->Attributes->{$normal_attribute->name})) {
                    //         switch ($normal_attribute->name) {
                    //             case 'name':
                    //                 break;
                    //             case 'brand':
                    //                 break;
                    //             case 'name_ms':
                    //                 break;
                    //             case 'short_description':
                    //                     break;
                    //             case 'warranty_type':
                    //                 break;
                    //             default:
                    //                 $extra_fields[] = $normal_attribute->name;
                    //                 $json_data[$normal_attribute->name] = $single_product->Attributes->{$normal_attribute->name};
                    //                 break;
                    //         }
                    //     }
                    // }
                    
                    $product_quantity = 0;
                    $stock_status = 0;

                    // variations
                    $variants_data = [];
                    foreach ($single_product->Skus as $sku_variant) {
                        $variants_id = $sku_variant->SellerSku;
                        $variants_price = $sku_variant->price?$sku_variant->price:0;

                        $variant_sku = $sku_variant->SellerSku;
                        $variants_barcode = isset($sku_variant->tax_class)?$sku_variant->tax_class:'';

                        $variants_qty = $sku_variant->Available;
                        $product_quantity += intval($sku_variant->Available);
                        if ($product_quantity > 0) {
                            $stock_status = 1;
                        } else {
                            $stock_status = 0;
                        }
                        $variants_weight = $sku_variant->package_weight;
                        $variants_created_at = date('Y-m-d H:i:s', time());
                        $variants_updated_at = date('Y-m-d H:i:s', time());
                        $variants_title_value = '';

                        $variants_options = [];

                        foreach ($product_variations as $single_variation) {
                            if(isset($sku_variant->{$single_variation->name})) {
                                $value_data = [
                                    'label' => '',
                                    'value' => $sku_variant->{$single_variation->name}
                                ];
                                $oneVariantOption = [
                                    'name' => $single_variation->label,
                                    'value' => $value_data
                                ];
                                $variants_options[] = $oneVariantOption;
                            }
                        }

                        $oneVariantData = [
                            'connection_variation_id' => $variants_id,
                            'sku_key' => 'sku',
                            'sku_value' => $variant_sku,
                            'inventory_key' => 'inventory_quantity',
                            'inventory_value' => $variants_qty,
                            'price_key' => 'price',
                            'price_value' => $variants_price,
                            'weight_key' => 'weight',
                            'weight_value' => $variants_weight,
                            'barcode' => $variants_barcode,
                            'options' => $variants_options,
                            'created_at' => $variants_created_at, // Variant created at date format date('Y-m-d H:i:s')
                            'updated_at' => $variants_updated_at, // Variant updated at date format date('Y-m-d H:i:s')
                        ];
                        $variants_data[] = $oneVariantData;
                    }

                    date_default_timezone_set("UTC");

                    $created_date = date('Y-m-d H:i:s', isset($first_sku_product->special_from_date) ? strtotime($first_sku_product->special_from_date) : time());
                    $updated_date = date('Y-m-d H:i:s', isset($first_sku_product->special_from_date) ? strtotime($first_sku_product->special_from_date) : time());

                    if ($created_date == '1970-01-01 00:00:00')  {
                        $created_date = date('Y-m-d H:i:s', time());
                    }

                    if ($updated_date == '1970-01-01 00:00:00')  {
                        $updated_date = date('Y-m-d H:i:s', time());
                    }
                    
                    $condition = null;
                    if(isset($single_product->Attributes->condition)) {
                        switch (ucfirst($single_product->Attributes->condition)) {
                            case Product::PRODUCT_CONDITION_NEW:
                                $condition = Product::PRODUCT_CONDITION_NEW;
                                break;
                            case Product::PRODUCT_CONDITION_USED:
                                $condition = Product::PRODUCT_CONDITION_USED;
                                break;
                            case Product::PRODUCT_CONDITION_REFURBISHED:
                                $condition = Product::PRODUCT_CONDITION_REFURBISHED;
                                break;
                        }
                    }

                    $product_data = [
                        'user_id' => $user_id, // Elliot user id,
                        'name' => $single_product->Attributes->name, // Product name,
                        'sku' => $first_sku_product->SellerSku, // Product SKU,
                        'url' => isset($first_sku_product->Url) ? $first_sku_product->Url : '', // Product url if null give blank value,
                        'upc' => '', // Product upc if any,
                        'ean' => '',
                        'jan' => '', // Product jan if any,
                        'isbn' => '', // Product isban if any,
                        'mpn' => '', // Product mpn if any,
                        'description' =>  $single_product->Attributes->short_description, // Product Description,
                        'adult' => null,
                        'age_group' => null,
                        'brand' => isset($single_product->Attributes->brand) ? $single_product->Attributes->brand : '',
                        'condition' => $condition,
                        'gender' => null,
                        'weight' => isset($first_sku_product->package_weight) ? $first_sku_product->package_weight : 0, // Product weight if null give blank value,
                        'package_length' => isset($first_sku_product->package_length) ? $first_sku_product->package_length : 0,
                        'package_height' => isset($first_sku_product->package_height) ? $first_sku_product->package_height : 0,
                        'package_width' => isset($first_sku_product->package_width) ? $first_sku_product->package_width : 0,
                        'package_box' => isset($first_sku_product->package_content) ? $first_sku_product->package_content : '',
                        'stock_quantity' => $product_quantity, //Product quantity,
                        'allocate_inventory' => null,
                        'currency' => $currency_code,
                        'country_code' => $country_code,
                        'stock_level' => ($stock_status>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
                        'stock_status' => ($stock_status>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
                        'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
                        'price' => (float)$first_sku_product->price, // Porduct price,
                        'sales_price' => (float)$first_sku_product->special_price, // Product sale price if null give Product price value,
                        'schedule_sales_date' => null,
                        'status' => isset($stock_status)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
                        'permanent_hidden' => Product::STATUS_NO,
                        'user_connection_id' => $user_connection_id,
                        'connection_product_id' => $first_sku_product->SellerSku, // Stores/Channel product ID,
                        'created_at' => $created_date, // Product created at date format date('Y-m-d H:i:s'),
                        'updated_at' => $updated_date, // Product updated at date format date('Y-m-d H:i:s'),
                        'type' => isset($single_product->Attributes->warranty_type) ? $single_product->Attributes->warranty_type : '', // Product Type
                        'images' => $product_image_data, // Product images data
                        'variations' => $variants_data,
                        'options_set' => null,
                        'websites' => array(), //This is for only magento give only and blank array
                        'conversion_rate' => 1,
                        'categories' => $categories // Product categroy array. If null give a blank array
                    ];

                    if ( !empty($product_data) ) {
                        Product::productImportingCommon($product_data);
                    }
                }
            }
        }
    }
}