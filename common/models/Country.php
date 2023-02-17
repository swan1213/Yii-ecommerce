<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%country}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $sortname
 * @property string $country_capital
 * @property string $phonecode
 * @property string $currency_code
 * @property string $currency_symbol
 * @property string $country_flag
 */
class Country extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%country}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'country_capital'], 'string', 'max' => 255],
            [['sortname', 'phonecode'], 'string', 'max' => 32],
            [['currency_code', 'currency_symbol'], 'string', 'max' => 64],
            [['country_flag'], 'string', 'max' => 512],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'name' => Yii::t('common', 'Name'),
            'sortname' => Yii::t('common', 'Sortname'),
            'country_capital' => Yii::t('common', 'Country Capital'),
            'phonecode' => Yii::t('common', 'Phonecode'),
            'currency_code' => Yii::t('common', 'Currency Code'),
            'currency_symbol' => Yii::t('common', 'Currency Symbol'),
            'country_flag' => Yii::t('common', 'Country Flag'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CountryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CountryQuery(get_called_class());
    }


    public static function countryInfoFromCode($countryCode) {
        $countryInfo = Country::findOne(['sortname' => strtoupper($countryCode)]);
        return $countryInfo;
    }

    public static function countryInfoFromName($countryName) {
        $countryInfo = Country::findOne(['name' => $countryName]);
        return $countryInfo;
    }
}
