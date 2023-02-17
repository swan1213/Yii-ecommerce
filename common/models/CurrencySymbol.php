<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%currency_symbol}}".
 *
 * @property string $id
 * @property string $name
 * @property string $symbol
 */
class CurrencySymbol extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%currency_symbol}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'symbol'], 'string', 'max' => 255],
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
            'symbol' => Yii::t('common', 'Symbol'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CurrencySymbolQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CurrencySymbolQuery(get_called_class());
    }

    public static function getCurrencySymbol($name) {

        $symbol = '';
        $symbol_data = CurrencySymbol::findOne(['name' => $name]);
        if (!empty($symbol_data)) {
            $symbol = $symbol_data->symbol;
        }
        return $symbol;
    }

}
