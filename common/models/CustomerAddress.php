<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%customer_address}}".
 *
 * @property string $id
 * @property string $customer_id
 * @property string $first_name
 * @property string $last_name
 * @property string $company
 * @property string $country
 * @property string $country_iso
 * @property string $street_1
 * @property string $street_2
 * @property string $state
 * @property string $city
 * @property string $zip
 * @property string $phone
 * @property string $address_type
 *
 * @property Customer $customer
 */
class CustomerAddress extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_address}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id'], 'integer'],
            [['first_name', 'last_name', 'company', 'country', 'country_iso', 'street_1', 'street_2', 'state', 'city', 'zip', 'phone', 'address_type'], 'string', 'max' => 255],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'customer_id' => Yii::t('common', 'Customer ID'),
            'first_name' => Yii::t('common', 'First Name'),
            'last_name' => Yii::t('common', 'Last Name'),
            'company' => Yii::t('common', 'Company'),
            'country' => Yii::t('common', 'Country'),
            'country_iso' => Yii::t('common', 'Country Iso'),
            'street_1' => Yii::t('common', 'Street 1'),
            'street_2' => Yii::t('common', 'Street 2'),
            'state' => Yii::t('common', 'State'),
            'city' => Yii::t('common', 'City'),
            'zip' => Yii::t('common', 'Zip'),
            'phone' => Yii::t('common', 'Phone'),
            'address_type' => Yii::t('common', 'Address Type'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CustomerAddressQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CustomerAddressQuery(get_called_class());
    }
}
