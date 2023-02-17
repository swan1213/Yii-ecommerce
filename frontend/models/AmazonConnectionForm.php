<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class AmazonConnectionForm extends Model
{
    public $access_key;
    public $secret_key;
    public $merchant_id;

    public function rules()
    {
        return [
            [['access_key', 'secret_key', 'merchant_id'], 'required']
        ];
    }
}