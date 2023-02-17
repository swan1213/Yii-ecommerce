<?php

namespace frontend\modules\api\v1\resources;

use yii\helpers\Url;
use frontend\modules\api\v1\resources\OrderProduct;

class OrderStatus extends \common\models\Order
{

    public function rules()
    {
        return [

            ['id', 'required'],
            ['status', 'string', 'max' => 255],
            ['status', 'required'],
            ['status', 'in', 'range' => array_keys(self::statuses())],
            ['visible', 'string'],
            ['visible', 'in', 'range' => array_keys(self::visibles())],
            ['visible', 'default', 'value' => self::ORDER_VISIBLE_ACTIVE],
        ];
    }


    public function fields()
    {
        return [
            'id',
            'status',
            //'trackingCompany',
            'tracking_link',
            'trackingNumber',
            'visible',
            'order_date',
            'updated_at'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTrackingNumber(){
        return $this->connection_order_id;
    }

    public function getTrackingCompany(){
        return null;
    }

}