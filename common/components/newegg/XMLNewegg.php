<?php
namespace common\components\newegg;

class XMLNewegg
{
	public static function createProduct($product)
	{
		$sku           = $product->sku;
		$upc           = $product->upc;
		$name          = $product->name;
		// $manufacture   = $product->getAttributeText('manufacturer');
		// $mnfPartNumber = $product->getData('part_number');
		// $shortDesc     = preg_replace("/&#?[a-z0-9]{2,8};/i","",strip_tags($product->getShort_description()));
		$productDesc   = preg_replace("/&#?[a-z0-9]{2,8};/i", "", strip_tags($product->description));
		$weight = number_format($product->weight,2);
		$length = number_format($product->package_length,2);
		$height = number_format($product->package_height,2);
		$width  = number_format($product->package_width,2);
		$itemCondition = $product->condition;
		// $msrp  = str_replace(',', '',number_format($product->getMsrp(),2));
		// $map   = str_replace(',', '',number_format($product->getMap(),2));
		$price = str_replace(',', '', number_format($product->sales_price,2));
		$stocklevel = (!empty($product->stock_quantity))?(int)$product->stock_quantity:0;
		$shipping = "Default";
        if(empty($product->productCategories)) {
            throw new Exception("Category is required.");
        }
		$category_id = $product->productCategories[0]->category->connection_category_id;

		$feed = [
        	'NeweggEnvelope' => [
        		'Header' => [
        			'DocumentVersion' => '1.0',
        		],
    			'MessageType' => 'BatchItemCreation',
    			'Overwrite' => 'No',
    			'Message' => [
    				'Itemfeed' => [
    					'SummaryInfo' => [
    						'SubCategoryID' => $category_id
    					],
    					'Item' => [
    						'0' => [
    							'Action' => 'Create Item',
        						'BasicInfo' => [
        							'SellerPartNumber' => $sku,
        							'Manufacturer' => $product->brand,
        							'ManufacturerPartNumberOrISBN' => $sku,
        							'UPC' => $upc,
        							'ItemPackage' => 'Retail',
        							'ShippingRestriction' => 'Yes',
        							'WebsiteShortTitle' => $name,
        							'ProductDescription' => $productDesc,
        							'ItemDimension' => [
        								'ItemLength' => $length,
        								'ItemWidth' => $width,
        								'ItemHeight' => $height
        							],
        							'ItemWeight' => $weight,
        							'PacksOrSets' => '1',
        							'ItemCondition' => $itemCondition,
        							'Currency' => $product->currency,
        							'SellingPrice' => $price,
        							'Shipping' => $shipping,
        							'Inventory' => $stocklevel,
        							'ActivationMark' => 'True',
        							'ItemImages' => [
        								'Image' => [
        									'ImageUrl' => $product->url,
        									'IsPrimary' => 'True'
        								]
        							]
        						]
    						]
    					]
    				]
    			]
        	]
        ];

        return $feed;
	}

	public static function updateProduct($product)
	{
		$sku           = $product->sku;
		$upc           = $product->upc;
		$name          = $product->name;
		$productDesc   = preg_replace("/&#?[a-z0-9]{2,8};/i","",strip_tags($product->description));
		$weight = number_format($product->weight,2);
		$length = number_format($product->package_length,2);
		$height = number_format($product->package_height,2);
		$width  = number_format($product->package_width,2);
		$itemCondition = $product->condition;

		$feed = [
        	'NeweggEnvelope' => [
        		'Header' => [
        			'DocumentVersion' => '3.0',
        		],
    			'MessageType' => 'BatchItemCreation',
    			'Message' => [
    				'Itemfeed' => [
    					'Item' => [
    						'0' => [
    							'Action' => 'Update Item',
        						'BasicInfo' => [
        							'SellerPartNumber' => $sku,
        							'WebsiteShortTitle' => $name,
        							'ProductDescription' => $productDesc,
        							'ItemDimension' => [
        								'ItemLength' => $length,
        								'ItemWidth' => $width,
        								'ItemHeight' => $height
        							],
        							'ItemWeight' => $weight,
        							'ActivationMark' => 'True',
        							'ItemImages' => [
        								'Image' => [
        									'ImageUrl' => $product->url,
        									'IsPrimary' => 'True'
        								]
        							]
        						]
    						]
    					]
    				]
    			]
        	]
        ];
		return $feed;
	}

	public static function updateProductPrice($product)
	{
		$stocklevel = (!empty($product->stock_quantity))?(string)$product->stock_quantity:'0';

		$feed = [
        	'NeweggEnvelope' => [
        		'Header' => [
        			'DocumentVersion' => '1.0'
        		],
    			'MessageType' => 'Inventory',
    			'Overwrite' => 'No',
    			'Message' => [
    				'Inventory' => [
    					'Item' => [
    						'0' => [
    							'SellerPartNumber' => $product->sku,
    							'SellingPrice' => str_replace(',', '', number_format($product->sales_price,2)),
    							'Inventory' => $stocklevel,
    							'Currency' => $product->currency
    						]
    					]
    				]
    			]
        	]
        ];
		return $feed;
	}
}