<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class WechatConnectionForm extends Model
{
    public $client_id;
    public $client_secret;

    public function rules()
    {
        return [
            [['client_id', 'client_secret'], 'required'],
        ];
    }
}