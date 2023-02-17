<?php

namespace frontend\modules\api\v1\resources;


class OrderProduct extends \common\models\OrderProduct
{
    public function fields()
    {
        return [
            'product_id',
            'order_product_sku',
            'price',
            'qty',
            'productInfo',
        ];
    }

    public function getProductInfo(){
        return $this->hasMany(Product::className(), ['id' => 'product_id']);
    }

}