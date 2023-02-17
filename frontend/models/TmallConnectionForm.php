<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class TmallConnectionForm extends Model
{
    public $app_key;
    public $app_secret;

    public function rules()
    {
        return [
            [['app_key', 'app_secret'], 'required']
        ];
    }
}