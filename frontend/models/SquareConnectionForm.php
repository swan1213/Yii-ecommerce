<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class SquareConnectionForm extends Model
{
    public $app_id;
    public $access_token;

    public function __construct() {
        parent::__construct();
    }

    public function rules()
    {
        return [
            [['app_id', 'access_token'], 'required']
        ];
    }
}