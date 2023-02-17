<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_attribution}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $attribution_id
 * @property string $product_id
 * @property string $item_name
 * @property string $item_value
 * @property string $connection_attribute_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Attribution $attribution
 * @property Product $product
 * @property User $user
 */
class ProductAttribution extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_attribution}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'attribution_id', 'product_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['item_name', 'item_value'], 'string', 'max' => 255],
            [['connection_attribute_id'], 'string', 'max' => 512],
            [['attribution_id'], 'exist', 'skipOnError' => true, 'targetClass' => Attribution::className(), 'targetAttribute' => ['attribution_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'attribution_id' => Yii::t('common', 'Attribution ID'),
            'product_id' => Yii::t('common', 'Product ID'),
            'item_name' => Yii::t('common', 'Item Name'),
            'item_value' => Yii::t('common', 'Item Value'),
            'connection_attribute_id' => Yii::t('common', 'Connection Attribute ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttribution()
    {
        return $this->hasOne(Attribution::className(), ['id' => 'attribution_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
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
     * @return \common\models\query\ProductAttributionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ProductAttributionQuery(get_called_class());
    }
}
