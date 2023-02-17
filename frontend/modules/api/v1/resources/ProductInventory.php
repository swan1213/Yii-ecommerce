<?php

namespace frontend\modules\api\v1\resources;

use yii\helpers\Url;
use frontend\modules\api\v1\resources\ProductChannel;

class ProductInventory extends \common\models\Product
{

    public function rules()
    {
        return [

            ['user_id', 'required'],
            ['id', 'required'],
            ['name', 'string', 'max' => 255],
            ['sku', 'required'],
            ['sku', 'string', 'max' => 255],
            ['stock_quantity', 'required'],
            ['stock_quantity', 'integer', 'min' => 0],
            ['stock_level', 'string'],
            ['stock_level', 'default', 'value' => self::STOCK_LEVEL_IN_STOCK],
            ['stock_status', 'string'],
            ['stock_status', 'default', 'value' => self::STOCK_STATUS_VISIBLE],
        ];
    }


    public function fields()
    {
        return [
            'uniqueId',
            'sku',
            'stock_quantity',
            //'allocate_inventory',
            'stock_level',
            'stock_status',
            'channels',
            //'permanent_hidden',
            'created_at',
            'updated_at'
        ];
    }

    public function getChannels(){
        return $this->hasMany(ProductChannel::className(), ['product_id' => 'id']);
    }

}