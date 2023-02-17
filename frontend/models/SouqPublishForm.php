<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class SouqPublishForm extends Model
{
    public $connections;

    public function rules()
    {
        return [
            [['connections'], 'required'],
        ];
    }
}