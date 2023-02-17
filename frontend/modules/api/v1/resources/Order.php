<?php

namespace frontend\modules\api\v1\resources;

use yii\helpers\Url;
use frontend\modules\api\v1\resources\OrderProduct;

class Order extends \common\models\Order
{

    public function fields()
    {
        return [
            'id',
            'customer_id',
            'status',
            'product_quantity',
            'ship_fname',
            'ship_lname',
            'ship_phone',
            'ship_company',
            'ship_street_1',
            'ship_street_2',
            'ship_city',
            'ship_state',
            'ship_zip',
            'ship_country',
            'ship_country_iso',
            'bill_fname',
            'bill_lname',
            'bill_phone',
            'bill_company',
            'bill_street_1',
            'bill_street_2',
            'bill_city',
            'bill_state',
            'bill_zip',
            'bill_country',
            'bill_country_iso',
            'tracking_link',
            'total_amount',
            'visible',
            'orderProduct',
            'sfData',
            'order_date',
            'updated_at'
            ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProduct(){
        return $this->hasMany(OrderProduct::className(), ['order_id' => 'id']);

    }

}