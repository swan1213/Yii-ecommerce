<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%billing_invoice}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $stripe_id
 * @property string $total_word_count
 * @property string $amount
 * @property string $refund_amount
 * @property string $customer_email
 * @property string $invoice_name
 * @property string $status
 * @property string $user_connection_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property UserConnection $userConnection
 * @property User $user
 */
class BillingInvoice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%billing_invoice}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_connection_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['stripe_id', 'total_word_count', 'amount', 'refund_amount', 'customer_email', 'invoice_name', 'status'], 'string', 'max' => 255],
            [['user_connection_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserConnection::className(), 'targetAttribute' => ['user_connection_id' => 'id']],
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
            'stripe_id' => Yii::t('common', 'Stripe ID'),
            'total_word_count' => Yii::t('common', 'Total Word Count'),
            'amount' => Yii::t('common', 'Amount'),
            'refund_amount' => Yii::t('common', 'Refund Amount'),
            'customer_email' => Yii::t('common', 'Customer Email'),
            'invoice_name' => Yii::t('common', 'Invoice Name'),
            'status' => Yii::t('common', 'Status'),
            'user_connection_id' => Yii::t('common', 'User Connection ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserConnection()
    {
        return $this->hasOne(UserConnection::className(), ['id' => 'user_connection_id']);
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
     * @return \common\models\query\BillingInvoiceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\BillingInvoiceQuery(get_called_class());
    }
}
