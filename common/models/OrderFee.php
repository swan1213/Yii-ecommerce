<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%order_fee}}".
 *
 * @property string $id
 * @property double $market_place_fee
 * @property double $credit_card_fee
 * @property double $processing_fee
 * @property double $base_shippping_cost
 * @property double $shipping_cost_tax
 * @property double $base_handling_cost
 * @property double $handling_cost_tax
 * @property double $base_wrapping_cost
 * @property double $wrapping_cost_tax
 * @property string $payment_method
 * @property string $payment_provider
 * @property string $payment_status
 * @property double $refunded_amount
 * @property double $discount_amount
 * @property double $coupon_discount
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Order[] $orders
 */
class OrderFee extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order_fee}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['market_place_fee', 'credit_card_fee', 'processing_fee', 'base_shippping_cost', 'shipping_cost_tax', 'base_handling_cost', 'handling_cost_tax', 'base_wrapping_cost', 'wrapping_cost_tax', 'refunded_amount', 'discount_amount', 'coupon_discount'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['payment_method', 'payment_provider'], 'string', 'max' => 255],
            [['payment_status'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'market_place_fee' => Yii::t('common', 'Market Place Fee'),
            'credit_card_fee' => Yii::t('common', 'Credit Card Fee'),
            'processing_fee' => Yii::t('common', 'Processing Fee'),
            'base_shippping_cost' => Yii::t('common', 'Base Shippping Cost'),
            'shipping_cost_tax' => Yii::t('common', 'Shipping Cost Tax'),
            'base_handling_cost' => Yii::t('common', 'Base Handling Cost'),
            'handling_cost_tax' => Yii::t('common', 'Handling Cost Tax'),
            'base_wrapping_cost' => Yii::t('common', 'Base Wrapping Cost'),
            'wrapping_cost_tax' => Yii::t('common', 'Wrapping Cost Tax'),
            'payment_method' => Yii::t('common', 'Payment Method'),
            'payment_provider' => Yii::t('common', 'Payment Provider'),
            'payment_status' => Yii::t('common', 'Payment Status'),
            'refunded_amount' => Yii::t('common', 'Refunded Amount'),
            'discount_amount' => Yii::t('common', 'Discount Amount'),
            'coupon_discount' => Yii::t('common', 'Coupon Discount'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['fee_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\OrderFeeQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\OrderFeeQuery(get_called_class());
    }
}
