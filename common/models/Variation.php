<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%variation}}".
 *
 * @property string $id
 * @property string $name
 * @property string $items
 * @property string $description
 * @property string $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property ProductVariation[] $productVariations
 * @property User $user
 */
class Variation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%variation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['items', 'description'], 'string'],
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'items' => Yii::t('common', 'Items'),
            'description' => Yii::t('common', 'Description'),
            'user_id' => Yii::t('common', 'User ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductVariations()
    {
        return $this->hasMany(ProductVariation::className(), ['variation_id' => 'id']);
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
     * @return \common\models\query\VariationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\VariationQuery(get_called_class());
    }

    public function getVariationItemList(){
        return explode('-', $this->items);
    }
}
