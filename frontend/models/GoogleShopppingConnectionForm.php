<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class GoogleShopppingConnectionForm extends Model
{
    public $merchant_id;

    public function rules()
    {
        return [
            [['merchant_id'], 'required'],
        ];
    }
}