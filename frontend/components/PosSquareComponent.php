<?php

namespace frontend\components;

use yii\base\Component;

use common\models\Category; 
use common\models\Connection;
use common\models\Country;
use common\models\Customer;
use common\models\CurrencyConversion;
use common\models\Order;
use common\models\Product;
use common\models\ProductConnection;
use common\models\UserConnection;
use common\models\UserConnectionDetails;

class PosSquareComponent extends Component {
	public static function deleteProduct($user_connection_id, $product_id) {
        $product_connection_row = ProductConnection::find()->where([
            'product_id' => $product_id,
            'user_connection_id' => $user_connection_id
        ])->one();
        
        if(empty($product_connection_row)) {
            throw new \Exception('Invalid product or user connection id');
        }

        $user_connection_row = UserConnection::find()->where([
            'id' => $user_connection_id
        ])->one();


        if(empty($user_connection_row)) {
            throw new \Exception('Invalid user connection id');
        }

        $user_credential = $user_connection_row->connection_info;
        \SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($user_credential['access_token']);
        $location_id = $user_credential['location_id']; // string | The ID of the item's associated location.
        $api_instance = new \SquareConnect\Api\V1ItemsApi();
        $item_id = $product_connection_row->connection_product_id;

        try {
		    $result = $api_instance->deleteItem($location_id, $item_id);
		    return true;
		} catch (Exception $e) {
		    return false;
		}
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

	        $product_connection_row = ProductConnection::find()->where([
                'product_id' => $product_id,
                'user_connection_id' => $user_connection_id
            ])->one();
            
            if(empty($product_connection_row)) {
                throw new \Exception('There is no square connection for you.');
            }

            $connection_product_id = $product_connection_row->connection_product_id;

	        if($is_update) {
		        $item_id = $connection_product_id; // string | The ID of the item to modify.
	        }

	        if(empty($product_row->productVariations) or 
	        	count($product_row->productVariations) == 0
	    	) {
	        	throw new \Exception('There is no square product variation of this product.');	
	    	}

	    	$variation_id = $product_row->productVariations[0]->connection_variation_id;

	        $user_connection_row = UserConnection::find()->where([
	            'id' => $user_connection_id
	        ])->one();

	        if(empty($user_connection_row)) {
	            throw new \Exception('Invalid user connection id');
	        }

	        $user_credential = $user_connection_row->connection_info;

	        \SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($user_credential['access_token']);
	        $location_id = $user_credential['location_id']; // string | The ID of the item's associated location.
	        $api_instance = new \SquareConnect\Api\V1ItemsApi();

	        if($is_update) {
	        	// update the item
				$item_body = new \SquareConnect\Model\V1Item(); // \SquareConnect\Model\V1Item | An object containing the fields to POST for the request.  See the corresponding object definition for field details.
				$item_body = array(
					'id' => $item_id,
					'name' => $product_row->name,
					'description' => $product_row->description,
					'type' => $product_row->type,
					'category_id' => $product_category_id
				);

				try {
				    $result = $api_instance->updateItem($location_id, $item_id, $item_body);
				} catch (Exception $e) {
					throw new \Exception('Exception when calling V1ItemsApi->updateItem: '.$e->getMessage());
				}

				// update item variation
				$variation_id = $product_row->productVariations[0]->connection_variation_id; // string | The ID of the variation to modify.
				$variation_body = new \SquareConnect\Model\V1Variation(); // \SquareConnect\Model\V1Variation | An object containing the fields to POST for the request.  See the corresponding object definition for field details.
				$variation_body = array(
					'sku' => $product_row->sku,
					'price_money' => array(
						'currency_code' => $product_row->currency,
	                    'amount' => $product_row->price*100
	                )
				);

				try {
					$result = $api_instance->updateVariation($location_id, $item_id, $variation_id, $variation_body);
				} catch (Exception $e) {
					throw new \Exception('Exception when calling V1ItemsApi->updateVariation: '.$e->getMessage());
				}
				
				$inventories = self::retireveSquareProductInventory($location_id);
				$product_inventory = array_filter($inventories, function($arr) use ($variation_id) {
				    return $arr['variation_id'] == $variation_id;
				});

				if(empty($product_inventory)) {
					throw new \Exception('There is no product inventory');
				}

				$inventory_body = new \SquareConnect\Model\V1AdjustInventoryRequest();
				$inventory_body = array(
					'quantity_delta' => $product_row->stock_quantity - reset($product_inventory)->getQuantityOnHand(),
					'adjustment_type' => 'MANUAL_ADJUST'
				);
				try {
					$result = $api_instance->adjustInventory($location_id, $variation_id, $inventory_body);
				} catch (Exception $e) {
					throw new \Exception('Exception when calling V1ItemsApi->adjustInventory: '.$e->getMessage());
				}
	        } else {
	        	// create the item
				$item_body = new \SquareConnect\Model\V1Item(); // \SquareConnect\Model\V1Item | An object containing the fields to POST for the request.  See the corresponding object definition for field details.

				$variations = [];
				$conversion_rate = CurrencyConversion::getCurrencyConversionRate($product_row->currency, 'USD');
				$variation_data = [];

				foreach ($product_row->productVariations as $single_variation) {
					$qty = 0;
					if($single_variation->inventory_key == 'inventory_quantity') {
						$qty = (int)$single_variation->inventory_value;
					}

					$sku = uniqid();
					if(isset($single_variation->sku_key) and $single_variation->sku_key == 'sku') {
						$sku = 'elliot'.$user_connection_id.$single_variation->sku_value;
					}

					$id = 'elliot'.$single_variation->id;

					$variation = array(
						'id' => $id,
						'name' => 'Regular',
						'visibility'=>'PRIVATE',
						'pricing_type' => 'FIXED_PRICING',
						'price_money' => array(
							'currency_code' => 'USD',
							'amount' => (float)$single_variation->price_value * $conversion_rate * 100
						),
						'sku' => $sku,
						'track_inventory' => true
					);

					$variation_data[] = array(
						'id' => $id,
						'qty' => $qty
					);
					$variations[] = $variation;
				}

				$item_body = array(
					'name' => $product_row->name,
					'description' => $product_row->description,
					'type' => $product_row->type,
					'variations' => $variations
				);

				try {
				    $result = $api_instance->createItem($location_id, $item_body);
				    $product_variants = $result['variations'];
				} catch (Exception $e) {
					throw new \Exception('Exception when calling V1ItemsApi->createItem: '.$e->getMessage());
				}

				foreach ($product_variants as $single_variation) {
					$qty = 0;
					foreach ($variation_data as $single_variation_data) {
						$variation_id = $single_variation->getId();
						if($single_variation_data['id'] == $variation_id) {
							$qty = $single_variation_data['qty'];
						}	
					}

					$inventory_body = array(
						'quantity_delta' => $qty,
						'adjustment_type' => 'MANUAL_ADJUST'
					);

					try {
						$result = $api_instance->adjustInventory($location_id, $variation_id, $inventory_body);
					} catch (Exception $e) {
						throw new \Exception('Exception when calling V1ItemsApi->adjustInventory: '.$e->getMessage());
					}	
				}
	        }
		    return json_encode(array(
                'success' => true,
                'product_id' => $product_id,
                'connection_product_id' => $connection_product_id,
                'user_connection_id' => $user_connection_id
            ));
		} catch (Exception $e) {
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
     * Square get product inventory list
     */
    public static function retireveSquareProductInventory($location_id) {
        $api_instance = new \SquareConnect\Api\V1ItemsApi();
        $limit = 1000;
        $result = $api_instance->listInventory($location_id, $limit);
        return $result;
    }

	public static function importObjects(
		$user_id,
		$user_connection_id,
		$location_id,
		$country_code,
		$currency_code
	) {
		$api = new \SquareConnect\Api\CatalogApi();

        // List all Category and Tax objects in the catalog.
        // The results are returned a pimportProductsage at a time.
        $types = join(',', [
            'ITEM',
            'CATEGORY'
        ]);
        $cursor = null;

        // Store all results in an array.
        $objects = [];

		do {
            $apiResponse = $api->listCatalog($cursor, $types);
            // Use the response cursor as the cursor for the subsequent request.
            $cursor = $apiResponse['cursor'];

            if ($apiResponse['objects'] != null) {
                $objects = array_merge($objects, $apiResponse['objects']);
            }

        // When the response cursor is null, the results are complete.
        } while ($apiResponse['cursor']);

        // filter the categories from objects
        $category_list = array_filter($objects, function($arr){
		    return $arr['type'] == 'CATEGORY';
		});

		// filter the products from objects
        $product_list = array_filter($objects, function($arr){
		    return $arr['type'] == 'ITEM';
		});

        // import categories
		foreach ($category_list as $single_category) {
			$category_data = [
                'name' => $single_category['category_data']['name'], // Give category name
                'description' => '', // Give category body html
                'parent_id' => 0,
                'user_id' => $user_id, // Give Elliot user id,
                'user_connection_id' => $user_connection_id, // Give Channel/Store prefix id
                'connection_category_id' => $single_category['id'], // Give category id of Store/channels
                'connection_parent_id' => '0', // Give Category parent id of Elliot if null then give 0
                'created_at' => date('Y-m-d H:i:s', $single_category['updated_at']?strtotime($single_category['updated_at']):time()),
                'updated_at' => date('Y-m-d H:i:s', $single_category['updated_at']?strtotime($single_category['updated_at']):time())
            ];

            Category::categoryImportingCommon($category_data);    // Then call store modal function and give $category data
		}

		$inventory_list = self::retireveSquareProductInventory($location_id);

		foreach ($product_list as $single_product) {
			$product_variants = $single_product['item_data']['variations'];
			$store_currency_code = isset($product_variants[0]['item_variation_data']['price_money']['currency'])?$product_variants[0]['item_variation_data']['price_money']['currency']:'USD';
			$conversion_rate = CurrencyConversion::getCurrencyConversionRate($store_currency_code, $currency_code);
			
			$variants_data = [];
			$product_quantity = 0;
			$stock_status = 0;
			
			if(!empty($product_variants)) {
				foreach ($product_variants as $_variants) {
					$variation_id = $_variants['id'];
					
					$product_inventory = array_filter($inventory_list, function($arr) use ($variation_id) {
					    return $arr['variation_id'] == $variation_id;
					});

					$variants_qty = reset($product_inventory)['quantity_on_hand'];
            		$product_quantity += intval($variants_qty);

		            $variants_options = [];

		            $oneVariantData = [
		                'connection_variation_id' => $_variants['id'],
		                'sku_key' => 'sku',
		                'sku_value' => $_variants['item_variation_data']['sku'],
		                'inventory_key' => 'inventory_quantity',
		                'inventory_value' => $variants_qty,
		                'price_key' => 'price',
		                'price_value' => (float)$_variants['item_variation_data']['price_money']['amount']/100,
		                'weight_key' => 'weight',
		                'weight_value' => null,
		                'upc' => $_variants['item_variation_data']['upc'],
		                'options' => $variants_options,
		                'created_at' => date('Y-m-d H:i:s', $_variants['updated_at']?strtotime($_variants['updated_at']):time()),
		                'updated_at' => date('Y-m-d H:i:s', $_variants['updated_at']?strtotime($_variants['updated_at']):time())
		            ];
		            $variants_data[] = $oneVariantData;
		        }
			}

			if(count($variants_data) > 0) {
				$product_data = [
		            'user_id' => $user_id, // Elliot user id,
		            'name' => $single_product['item_data']['name'], // Product name,
		            'sku' => $variants_data[0]['sku_value'], // Product SKU,
		            'url' => $single_product['item_data']['image_url'], // Product url if null give blank value,
		            'upc' => $variants_data[0]['upc'], // Product upc if any,
		            'ean' => '',
		            'jan' => '', // Product jan if any,
		            'isbn' => '', // Product isban if any,
		            'mpn' => '', // Product mpn if any,
		            'description' => $single_product['item_data']['description'], // Product Description,
		            'adult' => null,
		            'age_group' => null,
		            'brand' => null,
		            'condition' => null,
		            'gender' => null,
		            'weight' => null, // Product weight if null give blank value,
		            'package_length' => null,
		            'package_height' => null,
		            'package_width' => null,
		            'package_box' => null,
		            'stock_quantity' => $product_quantity, //Product quantity,
		            'allocate_inventory' => null,
		            'currency' => $product_variants[0]['item_variation_data']['price_money']['currency'],
		            'country_code' => $country_code,
		            'stock_level' => ($stock_status>0)?Product::STOCK_LEVEL_IN_STOCK:Product::STOCK_LEVEL_OUT_STOCK, // Product stock status ("in stock" or "out of stock"),
		            'stock_status' => ($stock_status>0)?Product::STOCK_STATUS_VISIBLE:Product::STOCK_STATUS_HIDDEN, // Product stock status ("Visible" or "Hidden"),
		            'low_stock_notification' => Product::LOW_STOCK_NOTIFICATION, // Porduct low stock notification if any otherwise give default 5 value,
		            'price' => $variants_data[0]['price_value'], // Porduct price,
		            'sales_price' => $variants_data[0]['price_value'], // Product sale price if null give Product price value,
		            'schedule_sales_date' => null,
		            'status' => ($stock_status>0)?Product::STATUS_ACTIVE:Product::STATUS_INACTIVE,
		            'published' => ($stock_status>0)?Product::PRODUCT_PUBLISHED_YES:Product::PRODUCT_PUBLISHED_NO,
		            'permanent_hidden' => Product::STATUS_NO,
		            'user_connection_id' => $user_connection_id,
		            'connection_product_id' => $single_product['id'], // Stores/Channel product ID,
		            'created_at' => $variants_data[0]['created_at'], // Product created at date format date('Y-m-d H:i:s'),
		            'updated_at' => $variants_data[0]['updated_at'], // Product updated at date format date('Y-m-d H:i:s'),
		            'type' => $single_product['item_data']['product_type'], // Product Type
		            'images' => array(), // Product images data
		            'variations' => $variants_data,
		            'options_set' => array(),
		            'websites' => array(), //This is for only magento give only and blank array
		            'conversion_rate' => $conversion_rate,
		            'categories' => array(
		            	$single_product['item_data']['category_id']
		            ), // Product categroy array. If null give a blank array
		        ];

		        if ( !empty($product_data) ) {
		            Product::productImportingCommon($product_data);
		        }
			}
		}
	}

	/**
     * Square Category Importing
     */
    public static function importCustomers($user_id, $user_connection_id){
        $api = new \SquareConnect\Api\CustomersApi();
        $customer_result = $api->listCustomers();
        
        foreach($customer_result['customers'] as $single_customer){
        	$customer_data = array(
	            'connection_customerId' => $single_customer['id'],
	            'user_id' => $user_id,
	            'first_name' => $single_customer['given_name'],
	            'last_name' => $single_customer['family_name'],
	            'email' => $single_customer['email_address'],
	            'phone' => $single_customer['phone_number'],
	            'user_connection_id' => $user_connection_id,
	            'customer_created' => date('Y-m-d H:i:s', $single_customer['created_at']?strtotime($single_customer['created_at']):time()),
	            'updated_at' =>  date('Y-m-d H:i:s', $single_customer['updated_at']?strtotime($single_customer['updated_at']):time()),
	            'addresses' => [
	                [
	                    'first_name' => $single_customer['address']['first_name'],
	                    'last_name' => $single_customer['address']['last_name'],
	                    'company' => $single_customer['company_name'],
	                    'country' => '',
	                    'country_iso' => $single_customer['address']['country'],
	                    'street_1' => $single_customer['address']['address_line_1'],
	                    'street_2' => $single_customer['address']['address_line_2'],
	                    'state' => $single_customer['address']['administrative_district_level_1'],
	                    'city' => $single_customer['address']['locality'],
	                    'zip' => $single_customer['address']['postal_code'],
	                    'phone' => $single_customer['phone_number'],
	                    'address_type' => 'Default',
	                ],
	            ]
	        );

	        Customer::customerImportingCommon($customer_data);
        }
    }

    public static function importOrders(
    	$user_id,
    	$user_connection_id,
    	$location_id,
    	$country_code,
    	$currency_code
    ) {
    	$v1_api_instance = new \SquareConnect\Api\V1TransactionsApi();
        $order = "ASC";
        $limit = 100;
        $v1_order_list = $v1_api_instance->listOrders($location_id, $order, $limit);

        $v2_api_instance = new \SquareConnect\Api\OrdersApi();
        $body = new \SquareConnect\Model\BatchRetrieveOrdersRequest();
		$v2_order_response = $v2_api_instance->batchRetrieveOrders($location_id, $body);
		$v2_order_list = $v2_order_response['orders'];

        if(!empty($v1_order_list)) {
        	foreach ($v1_order_list as $single_order) {
        		$v2_single_order = array_filter($v2_order_list, function($arr) use ($order_id) {
				    return $arr['id'] == $single_order['id'];
				});

        		$order_stautus = '';

        		switch ($single_order['state']) {
	                case "PENDING":
	                    $order_stautus = OrderStatus::PENDING;
	                    break;
                    case "OPEN":
	                    $order_stautus = OrderStatus::PENDING;
	                    break;
	                case "REJECTED":
	                    $order_stautus = OrderStatus::CANCEL;
	                    break;
	                case "COMPLETED":
	                    $order_stautus = OrderStatus::COMPLETED;
	                    break;
	                case "REFUNDED":
	                    $order_stautus = OrderStatus::REFUNDED;
	                    break;
	                case "CANCELED":
	                    $order_stautus = OrderStatus::CANCEL;
	                    break;
	                default:
	                    $order_stautus = OrderStatus::PENDING;
	            }

	            $order_product_data = array();
        		$product_quantity = 0;
	            foreach ($v2_single_order['line_items'] as $_items) {
	                $quantity = (int)$_items['quantity'];
	                $product_quantity += $quantity;
	                $order_product_data[] = array(
	                    'user_id' => $user_id,
	                    'connection_product_id' => null,
	                    'connection_variation_id' => $_items['id'],
	                    'name' => $_items['name'],
	                    'order_product_sku' => '',
	                    'price' => $_items['total_money']['amount'],
	                    'qty' => $quantity,
	                    'weight' => 0,
	                );
	            }

	            $conversion_rate = CurrencyConversion::getCurrencyConversionRate($v2_single_order['total_money']['currency'], $currency_code);

	            $order_data = array(
	                'user_id' => $user_id,
	                'user_connection_id' => $user_connection_id,
	                'connection_order_id' => $single_order['id'],
	                'status' => $order_stautus,
	                'product_quantity' => $product_quantity,
	                'ship_fname' => '',
	                'ship_lname' => '',
	                'ship_phone' => '',
	                'ship_company' => '',
	                'ship_street_1' => $single_order['shipping_address']['address_line_1'],
	                'ship_street_2' => $single_order['shipping_address']['address_line_2'],
	                'ship_city' => $single_order['shipping_address']['locality'],
	                'ship_state' => $single_order['shipping_address']['administrative_district_level_1'],
	                'ship_zip' => $single_order['shipping_address']['postal_code'],
	                'ship_country' => Country::countryInfoFromCode($single_order['shipping_address']['country_code'])->name,
	                'ship_country_iso' => $single_order['shipping_address']['country_code'],
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
	                    'base_shippping_cost' => $single_order['total_shipping_money']['amount'],
	                    'shipping_cost_tax' => $single_order['total_tax_money']['amount'],
	                    'refunded_amount' => 0,
	                    'discount_amount' => $single_order['total_tax_money']['amount'],
	                    'payment_method' => '',
	                ],
	                'total_amount' => $v2_single_order['total_money']['amount'],
	                'order_date' => date('Y-m-d H:i:s', $single_order['created_at']?strtotime($single_order['created_at']):time()),
	                'created_at' => date('Y-m-d H:i:s', $single_order['created_at']?strtotime($single_order['created_at']):time()),
	                'updated_at' => date('Y-m-d H:i:s', $single_order['updated_at']?strtotime($single_order['updated_at']):time()),
	                'customer_email' => '',
	                'currency_code' => $v2_single_order['total_money']['currency'],
	                'country_code' => $single_order['shipping_address']['country_code'],
	                'conversion_rate' => $conversion_rate,
	                'order_products' => $order_product_data,
	                'order_customer' => array()
	            );

	            if ( !empty($order_data) ){
	                Order::orderImportingCommon($order_data);
	            }
        	}
        }
    }
}