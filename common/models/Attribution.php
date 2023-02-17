<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%attribution}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $label
 * @property string $description
 * @property string $attribution_type
 * @property string $created_at
 * @property string $updated_at
 *
 * @property AttributionType $attributionType
 * @property User $user
 * @property ProductAttribution[] $productAttributions
 */
class Attribution extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%attribution}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'attribution_type'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'label'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1024],
            [['attribution_type'], 'exist', 'skipOnError' => true, 'targetClass' => AttributionType::className(), 'targetAttribute' => ['attribution_type' => 'id']],
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
            'user_id' => Yii::t('common', 'User ID'),
            'name' => Yii::t('common', 'Name'),
            'label' => Yii::t('common', 'Label'),
            'description' => Yii::t('common', 'Description'),
            'attribution_type' => Yii::t('common', 'Attribution Type'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributionType()
    {
        return $this->hasOne(AttributionType::className(), ['id' => 'attribution_type']);
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
    public function getProductAttributions()
    {
        return $this->hasMany(ProductAttribution::className(), ['attribution_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\AttributionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\AttributionQuery(get_called_class());
    }
}
