<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%document_info}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $bank_account_no
 * @property string $bank_roting_no
 * @property string $bank_code
 * @property string $bank_name
 * @property string $bank_address
 * @property string $bank_swift
 * @property string $bank_account_type
 * @property string $business_tax_id
 * @property string $alipay_payment_account_no
 * @property string $alipay_payment_account_id
 * @property string $alipay_payment_account_email
 * @property string $alipay_payment_address_1
 * @property string $alipay_payment_address_2
 * @property string $alipay_payment_account_city
 * @property string $alipay_payment_account_state
 * @property string $alipay_payment_account_country
 * @property string $alipay_payment_account_zip_code
 * @property string $dinpay_payment_account_no
 * @property string $dinpay_payment_account_id
 * @property string $dinpay_payment_account_email
 * @property string $dinpay_payment_address_1
 * @property string $dinpay_payment_address_2
 * @property string $dinpay_payment_account_city
 * @property string $dinpay_payment_account_state
 * @property string $dinpay_payment_account_country
 * @property string $dinpay_payment_account_zip_code
 * @property string $payoneer_payment_account_no
 * @property string $payoneer_payment_account_id
 * @property string $payoneer_payment_account_email
 * @property string $payoneer_payment_address_1
 * @property string $payoneer_payment_address_2
 * @property string $payoneer_payment_account_city
 * @property string $payoneer_payment_account_state
 * @property string $payoneer_payment_account_country
 * @property string $payoneer_payment_account_zip_code
 * @property string $worldfirst_payment_account_no
 * @property string $worldfirst_payment_account_id
 * @property string $worldfirst_payment_account_email
 * @property string $worldfirst_payment_address_1
 * @property string $worldfirst_payment_address_2
 * @property string $worldfirst_payment_account_city
 * @property string $worldfirst_payment_account_state
 * @property string $worldfirst_payment_account_country
 * @property string $worldfirst_payment_account_zip_code
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 */
class DocumentInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['bank_address'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['bank_account_no', 'bank_roting_no', 'bank_code', 'bank_name', 'bank_swift', 'bank_account_type', 'business_tax_id', 'alipay_payment_account_no', 'alipay_payment_account_id', 'alipay_payment_account_email', 'alipay_payment_address_1', 'alipay_payment_address_2', 'alipay_payment_account_city', 'alipay_payment_account_state', 'alipay_payment_account_country', 'alipay_payment_account_zip_code', 'dinpay_payment_account_no', 'dinpay_payment_account_id', 'dinpay_payment_account_email', 'dinpay_payment_address_1', 'dinpay_payment_address_2', 'dinpay_payment_account_city', 'dinpay_payment_account_state', 'dinpay_payment_account_country', 'dinpay_payment_account_zip_code', 'payoneer_payment_account_no', 'payoneer_payment_account_id', 'payoneer_payment_account_email', 'payoneer_payment_address_1', 'payoneer_payment_address_2', 'payoneer_payment_account_city', 'payoneer_payment_account_state', 'payoneer_payment_account_country', 'payoneer_payment_account_zip_code', 'worldfirst_payment_account_no', 'worldfirst_payment_account_id', 'worldfirst_payment_account_email', 'worldfirst_payment_address_1', 'worldfirst_payment_address_2', 'worldfirst_payment_account_city', 'worldfirst_payment_account_state', 'worldfirst_payment_account_country', 'worldfirst_payment_account_zip_code'], 'string', 'max' => 255],
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
            'bank_account_no' => Yii::t('common', 'Bank Account No'),
            'bank_roting_no' => Yii::t('common', 'Bank Roting No'),
            'bank_code' => Yii::t('common', 'Bank Code'),
            'bank_name' => Yii::t('common', 'Bank Name'),
            'bank_address' => Yii::t('common', 'Bank Address'),
            'bank_swift' => Yii::t('common', 'Bank Swift'),
            'bank_account_type' => Yii::t('common', 'Bank Account Type'),
            'business_tax_id' => Yii::t('common', 'Business Tax ID'),
            'alipay_payment_account_no' => Yii::t('common', 'Alipay Payment Account No'),
            'alipay_payment_account_id' => Yii::t('common', 'Alipay Payment Account ID'),
            'alipay_payment_account_email' => Yii::t('common', 'Alipay Payment Account Email'),
            'alipay_payment_address_1' => Yii::t('common', 'Alipay Payment Address 1'),
            'alipay_payment_address_2' => Yii::t('common', 'Alipay Payment Address 2'),
            'alipay_payment_account_city' => Yii::t('common', 'Alipay Payment Account City'),
            'alipay_payment_account_state' => Yii::t('common', 'Alipay Payment Account State'),
            'alipay_payment_account_country' => Yii::t('common', 'Alipay Payment Account Country'),
            'alipay_payment_account_zip_code' => Yii::t('common', 'Alipay Payment Account Zip Code'),
            'dinpay_payment_account_no' => Yii::t('common', 'Dinpay Payment Account No'),
            'dinpay_payment_account_id' => Yii::t('common', 'Dinpay Payment Account ID'),
            'dinpay_payment_account_email' => Yii::t('common', 'Dinpay Payment Account Email'),
            'dinpay_payment_address_1' => Yii::t('common', 'Dinpay Payment Address 1'),
            'dinpay_payment_address_2' => Yii::t('common', 'Dinpay Payment Address 2'),
            'dinpay_payment_account_city' => Yii::t('common', 'Dinpay Payment Account City'),
            'dinpay_payment_account_state' => Yii::t('common', 'Dinpay Payment Account State'),
            'dinpay_payment_account_country' => Yii::t('common', 'Dinpay Payment Account Country'),
            'dinpay_payment_account_zip_code' => Yii::t('common', 'Dinpay Payment Account Zip Code'),
            'payoneer_payment_account_no' => Yii::t('common', 'Payoneer Payment Account No'),
            'payoneer_payment_account_id' => Yii::t('common', 'Payoneer Payment Account ID'),
            'payoneer_payment_account_email' => Yii::t('common', 'Payoneer Payment Account Email'),
            'payoneer_payment_address_1' => Yii::t('common', 'Payoneer Payment Address 1'),
            'payoneer_payment_address_2' => Yii::t('common', 'Payoneer Payment Address 2'),
            'payoneer_payment_account_city' => Yii::t('common', 'Payoneer Payment Account City'),
            'payoneer_payment_account_state' => Yii::t('common', 'Payoneer Payment Account State'),
            'payoneer_payment_account_country' => Yii::t('common', 'Payoneer Payment Account Country'),
            'payoneer_payment_account_zip_code' => Yii::t('common', 'Payoneer Payment Account Zip Code'),
            'worldfirst_payment_account_no' => Yii::t('common', 'Worldfirst Payment Account No'),
            'worldfirst_payment_account_id' => Yii::t('common', 'Worldfirst Payment Account ID'),
            'worldfirst_payment_account_email' => Yii::t('common', 'Worldfirst Payment Account Email'),
            'worldfirst_payment_address_1' => Yii::t('common', 'Worldfirst Payment Address 1'),
            'worldfirst_payment_address_2' => Yii::t('common', 'Worldfirst Payment Address 2'),
            'worldfirst_payment_account_city' => Yii::t('common', 'Worldfirst Payment Account City'),
            'worldfirst_payment_account_state' => Yii::t('common', 'Worldfirst Payment Account State'),
            'worldfirst_payment_account_country' => Yii::t('common', 'Worldfirst Payment Account Country'),
            'worldfirst_payment_account_zip_code' => Yii::t('common', 'Worldfirst Payment Account Zip Code'),
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
     * @inheritdoc
     * @return \common\models\query\DocumentInfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\DocumentInfoQuery(get_called_class());
    }
}
