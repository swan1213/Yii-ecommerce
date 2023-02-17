<?php

namespace frontend\modules\api\v1\resources;

use common\models\UserProfile;
use yii\helpers\Url;

class Product extends \common\models\Product
{

    public function rules()
    {
        return [

            ['user_id', 'required'],
            ['name', 'required'],
            ['name', 'string', 'max' => 255],
            ['sku', 'required'],
            ['sku', 'string', 'max' => 255],
            ['upc', 'required'],
            ['upc', 'string', 'max' => 255],
            ['ean', 'required'],
            ['ean', 'string', 'max' => 255],
            ['jan', 'required'],
            ['jan', 'string', 'max' => 255],
            ['isbn', 'required'],
            ['isbn', 'string', 'max' => 255],
            ['mpn', 'required'],
            ['mpn', 'string', 'max' => 255],
            ['description', 'string'],
            ['adult', 'string'],
            ['adult', 'default', 'value' => self::ADULT_NO],
            ['adult', 'in', 'range' => self::adults()],
            ['age_group', 'string'],
            ['age_group', 'in', 'range' => array_keys(self::agegroups())],
            ['condition', 'string'],
            ['condition', 'in', 'range' => array_keys(self::conditions())],
            ['gender', 'default', 'value' => UserProfile::GENDER_UNISEX],
            ['gender', 'in', 'range' => array_keys(self::genders())],
            ['weight', 'required'],
            ['weight', 'number', 'min' => 0],
            ['stock_quantity', 'required'],
            ['stock_quantity', 'integer', 'min' => 0],
            ['currency', 'required'],
            ['country_code', 'required'],
            ['stock_level', 'string'],
            ['stock_level', 'default', 'value' => self::STOCK_LEVEL_IN_STOCK],
            ['stock_status', 'string'],
            ['stock_status', 'default', 'value' => self::STOCK_STATUS_VISIBLE],
            ['low_stock_notification', 'default', 'value' => self::LOW_STOCK_NOTIFICATION],
            ['price', 'required'],
            ['price', 'number', 'min' => 0],
            ['sales_price', 'required'],
            ['sales_price', 'number', 'min' => 0],
            ['schedule_sales_date', 'required'],
            ['schedule_sales_date', 'safe'],
            ['status', 'required'],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'name',
            'sku',
            'url',
            'upc',
            'ean',
            'jan',
            'isbn',
            'mpn',
            'description',
            'adult',
            'age_group',
            'condition',
            'gender',
            'weight',
            'stock_quantity',
            'allocate_inventory',
            'currency',
            'country_code',
            'stock_level',
            'stock_status',
            'low_stock_notification',
            'price',
            'sales_price',
            'schedule_sales_date',
            'status',
            'permanent_hidden',
            'created_at',
            'updated_at'
            ];
    }



}