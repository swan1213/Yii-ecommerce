<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%attribution_type}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $label
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Attribution[] $attributions
 * @property User $user
 */
class AttributionType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%attribution_type}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'label'], 'string', 'max' => 255],
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
            'user_id' => Yii::t('common', 'User ID'),
            'name' => Yii::t('common', 'Name'),
            'label' => Yii::t('common', 'Label'),
            'description' => Yii::t('common', 'Description'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributions()
    {
        return $this->hasMany(Attribution::className(), ['attribution_type' => 'id']);
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
     * @return \common\models\query\AttributionTypeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\AttributionTypeQuery(get_called_class());
    }
}
