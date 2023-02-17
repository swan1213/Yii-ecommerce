<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class ShiphawkConnectionForm extends Model
{
    public $product_key;

    public function rules()
    {
        return [
            [['product_key'], 'required'],
        ];
    }
}