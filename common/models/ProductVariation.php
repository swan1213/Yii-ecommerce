<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_variation}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $variation_id
 * @property string $product_id
 * @property string $sku_key
 * @property string $sku_value
 * @property string $inventory_key
 * @property string $inventory_value
 * @property string $price_key
 * @property string $price_value
 * @property string $weight_key
 * @property string $weight_value
 * @property int $allocate_inventory
 * @property double $allocate_percent
 * @property string $variation_set_id
 * @property string $connection_variation_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $user_connection_id
 *
 * @property Product $product
 * @property User $user
 * @property Variation $variation
 */
class ProductVariation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_variation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'variation_id'], 'required'],
            [['user_id', 'variation_id', 'product_id', 'allocate_inventory', 'variation_set_id', 'user_connection_id'], 'integer'],
            [['allocate_percent'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['sku_key', 'inventory_key', 'inventory_value', 'price_key', 'price_value', 'weight_key', 'weight_value', 'connection_variation_id'], 'string', 'max' => 255],
            [['sku_value'], 'string', 'max' => 512],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['variation_id'], 'exist', 'skipOnError' => true, 'targetClass' => Variation::className(), 'targetAttribute' => ['variation_id' => 'id']],
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
            'variation_id' => Yii::t('common', 'Variation ID'),
            'product_id' => Yii::t('common', 'Product ID'),
            'sku_key' => Yii::t('common', 'Sku Key'),
            'sku_value' => Yii::t('common', 'Sku Value'),
            'inventory_key' => Yii::t('common', 'Inventory Key'),
            'inventory_value' => Yii::t('common', 'Inventory Value'),
            'price_key' => Yii::t('common', 'Price Key'),
            'price_value' => Yii::t('common', 'Price Value'),
            'weight_key' => Yii::t('common', 'Weight Key'),
            'weight_value' => Yii::t('common', 'Weight Value'),
            'allocate_inventory' => Yii::t('common', 'Allocate Inventory'),
            'allocate_percent' => Yii::t('common', 'Allocate Percent'),
            'variation_set_id' => Yii::t('common', 'Variation Set ID'),
            'connection_variation_id' => Yii::t('common', 'Connection Variation ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'user_connection_id' => Yii::t('common', 'User Connection ID'),
        ];
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
     * @return \yii\db\ActiveQuery
     */
    public function getVariation()
    {
        return $this->hasOne(Variation::className(), ['id' => 'variation_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\ProductVariationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ProductVariationQuery(get_called_class());
    }

    public function getVariationItems($index)
    {
        $items = $this->variation->items;
        $itemValues = explode("-", $items);
        if ( isset($itemValues[$index]) ){
            $itemList = VariationValue::findOne(['id' => $itemValues[$index]]);
            if (!empty($itemList->label))
                return $itemList->label;
            if (!empty($itemList->value))
                return $itemList->value;
            return '-';
        }
        return "-";
    }

    public static function getVariationSetTypeNames($id)
    {
        $variationSet = VariationSet::findOne(['id' => $id]);
        if ( !empty($variationSet) ) {
            $typeSetNames = $variationSet->name;
            return explode("/", $typeSetNames);
        }

        return null;
    }
}
