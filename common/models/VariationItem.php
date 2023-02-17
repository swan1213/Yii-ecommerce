<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%variation_item}}".
 *
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 * @property VariationValue[] $variationValues
 */
class VariationItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%variation_item}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 512],
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
            'name' => Yii::t('common', 'Name'),
            'description' => Yii::t('common', 'Description'),
            'user_id' => Yii::t('common', 'User ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariationValues()
    {
        return $this->hasMany(VariationValue::className(), ['variation_item_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\VariationItemQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\VariationItemQuery(get_called_class());
    }
}
