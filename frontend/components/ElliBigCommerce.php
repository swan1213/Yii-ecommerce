<?php

namespace frontend\components;

use Bigcommerce\Api\Client;
use Bigcommerce\Api\Filter;

class ElliBigCommerce extends Client
{

    public static function getSkusByProductId($productId, $filter = array())
    {
        $filter = Filter::create($filter);
        self::getConnection()->setTimeout(0);
        return self::getCollection('/products/'.$productId.'/skus'. $filter->toQuery(), 'Sku');
    }


    public static function getOptionsByOptionsSetId($optionsSetId){

        return self::getCollection('/optionsets/' . $optionsSetId . '/options', 'Option');

    }

    public static function getProductSkusCount($productId){
        return self::getCount('/products/' . $productId . '/skus/count');
    }

    public static function updateProductSkuById($productId, $skuId, $fields){

        $updatePath = '/products/' . $productId . '/skus/' . $skuId;
        return self::updateResource($updatePath, $fields);
    }

    public static function getOptionValuesById($optionId)
    {
        return self::getCollection('/options/' . $optionId . '/values', 'OptionValue');
    }

}