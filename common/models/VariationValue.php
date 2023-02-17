<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%variation_value}}".
 *
 * @property string $id
 * @property string $variation_item_id
 * @property string $label
 * @property string $value
 * @property string $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property VariationItem $variationItem
 * @property User $user
 */
class VariationValue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%variation_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['variation_item_id', 'user_id'], 'required'],
            [['variation_item_id', 'user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['label', 'value'], 'string', 'max' => 255],
            [['variation_item_id'], 'exist', 'skipOnError' => true, 'targetClass' => VariationItem::className(), 'targetAttribute' => ['variation_item_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'variation_item_id' => Yii::t('common', 'Variation Item ID'),
            'label' => Yii::t('common', 'Label'),
            'value' => Yii::t('common', 'Value'),
            'user_id' => Yii::t('common', 'User ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariationItem()
    {
        return $this->hasOne(VariationItem::className(), ['id' => 'variation_item_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    /**
     * @inheritdoc
     * @return \common\models\query\VariationValueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\VariationValueQuery(get_called_class());
    }

    public static function findItem($id){
        return VariationItem::findOne(['id' => $id]);
    }


}
