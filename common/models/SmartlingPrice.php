<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%smartling_price}}".
 *
 * @property string $id
 * @property string $target_language
 * @property string $locale_id
 * @property string $editing
 * @property string $rate1
 * @property string $rate2
 * @property string $post_edit
 * @property string $created_at
 * @property string $updated_at
 */
class SmartlingPrice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%smartling_price}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['target_language', 'locale_id', 'editing', 'rate1', 'rate2', 'post_edit'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'target_language' => Yii::t('common', 'Target Language'),
            'locale_id' => Yii::t('common', 'Locale ID'),
            'editing' => Yii::t('common', 'Editing'),
            'rate1' => Yii::t('common', 'Rate1'),
            'rate2' => Yii::t('common', 'Rate2'),
            'post_edit' => Yii::t('common', 'Post Edit'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\SmartlingPriceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\SmartlingPriceQuery(get_called_class());
    }
}
