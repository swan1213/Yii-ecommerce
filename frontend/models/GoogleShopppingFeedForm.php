<?php

namespace frontend\models;

use Yii;
use yii\base\Model;

class GoogleShopppingFeedForm extends Model
{
    public $target_country;
    public $language;
    public $destinations;
    public $feed_name;
    public $fetch_frequency;
    public $fetch_date;
    public $fetch_weekday;
    public $fetch_time;
    public $timezone;
    public $category_ids;

    public function rules()
    {
        return [
            [['target_country', 'language', 'feed_name', 'timezone', 'category_ids'], 'required'],
        ];
    }
}