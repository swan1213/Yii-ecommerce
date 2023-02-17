<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class NeweggConnectionForm extends Model
{
	public $seller_id;
    public $api_key;
    public $secret_key;
    public $item_file;

    public function rules()
    {
        return [
            [['seller_id', 'api_key', 'secret_key', 'item_file'], 'required'],
            [['item_file'], 'file', 'extensions' => 'zip']
        ];
    }
}