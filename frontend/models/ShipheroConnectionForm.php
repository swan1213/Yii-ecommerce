<?php
/**
 * Created by PhpStorm.
 * User: whitedove
 * Date: 1/15/2018
 * Time: 3:41 PM
 */

namespace frontend\models;


use yii\base\Model;

class ShipheroConnectionForm extends Model
{
    public $api_key;
    public $api_secret;

    public function rules()
    {
        return [
            [['api_key', 'api_secret'], 'required'],
        ];
    }
}