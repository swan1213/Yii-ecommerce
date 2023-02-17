<?php

namespace common\models;

use Yii;
use common\models\UserProfile;

/**
 * This is the model class for table "{{%customer}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $connection_customerId
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $photo_image
 * @property string $cover_image
 * @property string $dob
 * @property string $gender
 * @property string $phone
 * @property string $visible
 * @property string $user_connection_id
 * @property string $customer_created
 * @property string $updated_at
 *
 * @property UserConnection $userConnection
 * @property User $user
 * @property CustomerAddress $customerAddress
 * @property CustomerAddress[] $customerAddresses
 * @property Order[] $orders
 */
class Customer extends \yii\db\ActiveRecord
{
    const VISIBLE_YES = 'Yes';
    const VISIBLE_NO = 'No';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_connection_id'], 'required'],
            [['user_id', 'user_connection_id'], 'integer'],
            [['dob', 'customer_created', 'updated_at'], 'safe'],
            [['visible'], 'string'],
            [['connection_customerId', 'first_name', 'last_name', 'email'], 'string', 'max' => 255],
            [['photo_image', 'cover_image'], 'string', 'max' => 512],
            [['gender'], 'string', 'max' => 32],
            [['gender'], 'in', 'range' => [UserProfile::GENDER_UNISEX, UserProfile::GENDER_MALE, UserProfile::GENDER_FEMALE]],
            [['phone'], 'string', 'max' => 64],
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
            'connection_customerId' => Yii::t('common', 'Connection Customer ID'),
            'first_name' => Yii::t('common', 'First Name'),
            'last_name' => Yii::t('common', 'Last Name'),
            'email' => Yii::t('common', 'Email'),
            'photo_image' => Yii::t('common', 'Photo Image'),
            'cover_image' => Yii::t('common', 'Cover Image'),
            'dob' => Yii::t('common', 'Dob'),
            'gender' => Yii::t('common', 'Gender'),
            'phone' => Yii::t('common', 'Phone'),
            'visible' => Yii::t('common', 'Visible'),
            'user_connection_id' => Yii::t('common', 'User Connection ID'),
            'customer_created' => Yii::t('common', 'Customer Created'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerAddresses()
    {
        return $this->hasMany(CustomerAddress::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerAddress()
    {
        return $this->hasOne(CustomerAddress::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }

    public function getCustomerFullName(){

        if ( !empty($this->last_name) ) {
            return $this->first_name . " " . $this->last_name;
        }
        return $this->first_name;
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CustomerQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CustomerQuery(get_called_class());
    }

    /**
     * Common function for Customer Importing for all channels/stores
     */
    public static function customerImportingCommon($customer_data) {

        if ( $customer_data['connection_customerId'] > 0 ) {
            $customer = Customer::findOne([
                'user_connection_id' => $customer_data['user_connection_id'],
                'connection_customerId' => $customer_data['connection_customerId']
            ]);
        } else {

            if ( !empty($customer_data['email']) ) {
                $customer = Customer::findOne([
                    'user_connection_id' => $customer_data['user_connection_id'],
                    'email' => $customer_data['email']
                ]);
            }

            if ( empty($customer) && !empty($customer_data['phone']) ){
                $customer = Customer::findOne([
                    'user_connection_id' => $customer_data['user_connection_id'],
                    'phone' => $customer_data['phone']
                ]);

            }

//            if ( empty($customer) && !empty($customer_data['email']) ){
//                $customer = Customer::findOne([
//                    'user_id' => $customer_data['user_id'],
//                    'email' => $customer_data['email']
//                ]);
//
//            }
//            if ( empty($customer) && !empty($customer_data['phone']) ){
//                $customer = Customer::findOne([
//                    'user_id' => $customer_data['user_id'],
//                    'phone' => $customer_data['phone']
//                ]);
//
//            }

        }


        if ( empty($customer) ) {
            $customer = new Customer();
        }


        if ( isset($customer_data['customer_created']) && !empty($customer_data['customer_created']) ){
            $customer_data['customer_created'] = date('Y-m-d h:i:s', strtotime($customer_data['customer_created']));
        } else {
            $customer_data['customer_created'] = date('Y-m-d h:i:s', time());
        }

        if ( isset($customer_data['updated_at']) && !empty($customer_data['updated_at']) ){
            $customer_data['updated_at'] = date('Y-m-d h:i:s', strtotime($customer_data['updated_at']));
        } else {
            $customer_data['updated_at'] = date('Y-m-d h:i:s', time());
        }

        $customerData = [
            'Customer' => $customer_data
        ];

        if ( $customer->load($customerData) && $customer->save(false) ){

            $customer_id = $customer->id;
            $customerAddrs = $customer_data['addresses'];
            foreach($customerAddrs as $eachAddr) {

                $eachAddr['customer_id'] = $customer_id;

                $customer_address = CustomerAddress::findOne($eachAddr);
                if ( empty($customer_address) ) {
                    $customer_address = new CustomerAddress();

                    $customer_address_data = [
                        'CustomerAddress' => $eachAddr
                    ];
                    $customer_address->load($customer_address_data);
                    $customer_address->save(false);
                }

            }

            return $customer_id;
        }

        return null;
    }


    public static function customerInsertCommon($customer_data) {

        $customer = new Customer();

        if ( isset($customer_data['customer_created']) && !empty($customer_data['customer_created']) ){
            $customer_data['customer_created'] = date('Y-m-d h:i:s', strtotime($customer_data['customer_created']));
        } else {
            $customer_data['customer_created'] = date('Y-m-d h:i:s', time());
        }

        if ( isset($customer_data['updated_at']) && !empty($customer_data['updated_at']) ){
            $customer_data['updated_at'] = date('Y-m-d h:i:s', strtotime($customer_data['updated_at']));
        } else {
            $customer_data['updated_at'] = date('Y-m-d h:i:s', time());
        }

        $customerData = [
            'Customer' => $customer_data
        ];

        if ( $customer->load($customerData) && $customer->save(false) ){

            $customer_id = $customer->id;
            $customerAddrs = $customer_data['addresses'];
            foreach($customerAddrs as $eachAddr) {

                $eachAddr['customer_id'] = $customer_id;

                $customer_address = new CustomerAddress();

                $customer_address_data = [
                    'CustomerAddress' => $eachAddr
                ];
                $customer_address->load($customer_address_data);
                $customer_address->save(false);

            }

            return $customer_id;
        }

        return null;
    }

}
