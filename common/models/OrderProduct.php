<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_product}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $order_id
 * @property string $product_id
 * @property string $order_product_sku
 * @property double $price
 * @property string $qty
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Order $order
 * @property Product $product
 * @property User $user
 */
class OrderProduct extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id', 'product_id'], 'required'],
            [['user_id', 'order_id', 'product_id'], 'integer'],
            [['price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['order_product_sku', 'qty'], 'string', 'max' => 255],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::className(), 'targetAttribute' => ['order_id' => 'id']],
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
            'order_id' => Yii::t('common', 'Order ID'),
            'product_id' => Yii::t('common', 'Product ID'),
            'order_product_sku' => Yii::t('common', 'Order Product Sku'),
            'price' => Yii::t('common', 'Price'),
            'qty' => Yii::t('common', 'Qty'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
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
     * @return \common\models\query\OrderProductQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\OrderProductQuery(get_called_class());
    }
}
