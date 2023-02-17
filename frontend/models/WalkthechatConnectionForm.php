<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class WalkthechatConnectionForm extends Model
{
    public $username;
    public $password;

    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
        ];
    }
}