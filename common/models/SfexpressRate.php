<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%sfexpress_rate}}".
 *
 * @property integer $id
 * @property integer $weight
 * @property double $hongkong
 * @property double $macau
 * @property double $taiwan
 * @property double $mainlandchina
 * @property double $singapore
 * @property double $malaysia
 * @property double $japan
 * @property double $southkorea
 */
class SfexpressRate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%sfexpress_rate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['weight'], 'integer'],
            [['hongkong', 'macau', 'taiwan', 'mainlandchina', 'singapore', 'malaysia', 'japan', 'southkorea'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'weight' => Yii::t('common', 'Weight'),
            'hongkong' => Yii::t('common', 'Hongkong'),
            'macau' => Yii::t('common', 'Macau'),
            'taiwan' => Yii::t('common', 'Taiwan'),
            'mainlandchina' => Yii::t('common', 'Mainlandchina'),
            'singapore' => Yii::t('common', 'Singapore'),
            'malaysia' => Yii::t('common', 'Malaysia'),
            'japan' => Yii::t('common', 'Japan'),
            'southkorea' => Yii::t('common', 'Southkorea'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\SfexpressRateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\SfexpressRateQuery(get_called_class());
    }
}
