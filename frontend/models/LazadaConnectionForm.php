<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class LazadaConnectionForm extends Model
{
    public $email;
    public $api_key;

    public function __construct() {
        parent::__construct();
    }

    public function rules()
    {
        return [
            [['email', 'api_key'], 'required'],
            ['email', 'email']
        ];
    }
}