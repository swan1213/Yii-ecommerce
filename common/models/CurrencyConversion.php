<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%currency_conversion}}".
 *
 * @property integer $id
 * @property string $from_currency
 * @property double $rate
 * @property string $created_at
 * @property string $updated_at
 */
class CurrencyConversion extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%currency_conversion}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['rate'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['from_currency'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'from_currency' => Yii::t('common', 'From Currency'),
            'rate' => Yii::t('common', 'Rate'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    public static function getCurrencyConversionRate($from_currency = 'USD', $to_currency = 'USD') {

        $from_currency = strtoupper($from_currency);
        $to_currency = strtoupper($to_currency);

        $conversion_rate = 1;

        if ( $from_currency != 'USD' && $to_currency == 'USD' ) {
            $rateModel = self::findOne(['from_currency' => $from_currency]);
            if ( !empty($rateModel) ) {
                $conversion_rate = (float) (1 / $rateModel->rate);
            }
        } else if ($from_currency != 'USD' && $to_currency != 'USD') {
            $fromRateModel = self::findOne(['from_currency' => $from_currency]);
            $toRateModel = self::findOne(['from_currency' => $to_currency]);
            if ( !empty($fromRateModel) && !empty($toRateModel) ) {
                $fromRate = $fromRateModel->rate;
                $toRate = $toRateModel->rate;
                $conversion_rate = (float) ($toRate / $fromRate);
            }

        } else if ( $from_currency == 'USD' && $to_currency != 'USD' ) {
            $rateModel = self::findOne(['from_currency' => $to_currency]);
            if ( !empty($rateModel) ) {
                $conversion_rate = $rateModel->rate;
            }

        }

        $conversion_rate = number_format((float) $conversion_rate, 5, '.', '');
        return $conversion_rate;
    }

    public static function getCurrencySymbol($name) {
        $symbol_data = self::find()->Where(['name' => $name])->one();
        if (!empty($symbol_data)) {
            $symbol = $symbol_data->symbol;
        } else {
            $symbol = '';
        }
        return $symbol;
    }

    public static function getDbConversionRate($currency) {
        $conversion_rate = self::getCurrencyConversionRate('USD', $currency);
        return $conversion_rate;
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CurrencyConversionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CurrencyConversionQuery(get_called_class());
    }
}
