<?php

namespace common\models;

use common\components\order\OrderStatus;
use Yii;
use yii\helpers\Json;

/**
 * This is the model class for table "{{%order}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $user_connection_id
 * @property string $customer_id
 * @property string $connection_order_id
 * @property string $status
 * @property string $product_quantity
 * @property string $ship_fname
 * @property string $ship_lname
 * @property string $ship_phone
 * @property string $ship_company
 * @property string $ship_street_1
 * @property string $ship_street_2
 * @property string $ship_city
 * @property string $ship_state
 * @property string $ship_zip
 * @property string $ship_country
 * @property string $ship_country_iso
 * @property string $bill_fname
 * @property string $bill_lname
 * @property string $bill_phone
 * @property string $bill_company
 * @property string $bill_street_1
 * @property string $bill_street_2
 * @property string $bill_city
 * @property string $bill_state
 * @property string $bill_zip
 * @property string $bill_country
 * @property string $bill_country_iso
 * @property string $brand_logo_image
 * @property string $tracking_link
 * @property string $fee_id
 * @property double $total_amount
 * @property string $order_date
 * @property string $sf_data
 * @property string $fulfill_status
 * @property string $visible
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Customer $customer
 * @property OrderFee $fee
 * @property UserConnection $userConnection
 * @property User $user
 * @property OrderProduct[] $orderProducts
 */
class Order extends \yii\db\ActiveRecord
{

    const ORDER_VISIBLE_ACTIVE = 'active';
    const ORDER_VISIBLE_INACTIVE = 'in_active';

    const ORDER_FULFILL_STATUS_YES = 'Yes';
    const ORDER_FULFILL_STATUS_NO = 'No';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%order}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_connection_id', 'customer_id', 'fee_id'], 'required'],
            [['user_id', 'user_connection_id', 'customer_id', 'fee_id'], 'integer'],
            [['total_amount'], 'number'],
            [['order_date', 'created_at', 'updated_at'], 'safe'],
            [['sf_data', 'visible'], 'string'],
            [['connection_order_id', 'status', 'product_quantity', 'ship_street_1', 'ship_street_2', 'ship_city', 'ship_state', 'bill_street_1', 'bill_street_2', 'bill_city', 'bill_state'], 'string', 'max' => 255],
            [['ship_fname', 'ship_lname', 'ship_phone', 'ship_company', 'ship_country', 'bill_fname', 'bill_lname', 'bill_phone', 'bill_company', 'bill_country'], 'string', 'max' => 128],
            [['ship_zip', 'ship_country_iso', 'bill_zip', 'bill_country_iso'], 'string', 'max' => 64],
            [['brand_logo_image', 'tracking_link'], 'string', 'max' => 512],
            [['fulfill_status'], 'string', 'max' => 16],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Customer::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['fee_id'], 'exist', 'skipOnError' => true, 'targetClass' => OrderFee::className(), 'targetAttribute' => ['fee_id' => 'id']],
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
            'user_connection_id' => Yii::t('common', 'User Connection ID'),
            'customer_id' => Yii::t('common', 'Customer ID'),
            'connection_order_id' => Yii::t('common', 'Connection Order ID'),
            'status' => Yii::t('common', 'Status'),
            'product_quantity' => Yii::t('common', 'Product Quantity'),
            'ship_fname' => Yii::t('common', 'Ship First Name'),
            'ship_lname' => Yii::t('common', 'Ship Last Name'),
            'ship_phone' => Yii::t('common', 'Ship Phone'),
            'ship_company' => Yii::t('common', 'Ship Company'),
            'ship_street_1' => Yii::t('common', 'Ship Street 1'),
            'ship_street_2' => Yii::t('common', 'Ship Street 2'),
            'ship_city' => Yii::t('common', 'Ship City'),
            'ship_state' => Yii::t('common', 'Ship State'),
            'ship_zip' => Yii::t('common', 'Ship Zip'),
            'ship_country' => Yii::t('common', 'Ship Country'),
            'ship_country_iso' => Yii::t('common', 'Ship Country Iso'),
            'bill_fname' => Yii::t('common', 'Bill First Name'),
            'bill_lname' => Yii::t('common', 'Bill Last Name'),
            'bill_phone' => Yii::t('common', 'Bill Phone'),
            'bill_company' => Yii::t('common', 'Bill Company'),
            'bill_street_1' => Yii::t('common', 'Bill Street 1'),
            'bill_street_2' => Yii::t('common', 'Bill Street 2'),
            'bill_city' => Yii::t('common', 'Bill City'),
            'bill_state' => Yii::t('common', 'Bill State'),
            'bill_zip' => Yii::t('common', 'Bill Zip'),
            'bill_country' => Yii::t('common', 'Bill Country'),
            'bill_country_iso' => Yii::t('common', 'Bill Country Iso'),
            'brand_logo_image' => Yii::t('common', 'Brand Logo Image'),
            'tracking_link' => Yii::t('common', 'Tracking Link'),
            'fee_id' => Yii::t('common', 'Fee ID'),
            'total_amount' => Yii::t('common', 'Total Amount'),
            'order_date' => Yii::t('common', 'Order Date'),
            'sf_data' => Yii::t('common', 'Sf Data'),
            'fulfill_status' => Yii::t('common', 'Fulfill Status'),
            'visible' => Yii::t('common', 'Visible'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
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
     * @return \yii\db\ActiveQuery
     */
    public function getFee()
    {
        return $this->hasOne(OrderFee::className(), ['id' => 'fee_id']);
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

    public function getBillName(){
        if ( !empty($this->bill_lname) )
            return $this->bill_fname." ". $this->bill_lname;

        return $this->bill_fname;
    }

    public function getShipName(){
        if ( !empty($this->ship_lname) )
            return $this->ship_fname." ". $this->ship_lname;

        return $this->ship_fname;
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProduct::className(), ['order_id' => 'id']);
    }

    public function getSfData(){
        return Json::decode($this->sf_data);
    }


    /**
     * @inheritdoc
     * @return \common\models\query\OrderQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\OrderQuery(get_called_class());
    }

    /**
     * Common function for Order Importing for all channels/stores
     */

    public static function orderImportingCommon($order_data){

        if ( empty($order_data['connection_order_id'])
            || !isset($order_data['connection_order_id'])
            || $order_data['connection_order_id'] == '0' ) {

            return;
        }

        $order_data['order_date'] = date('Y-m-d h:i:s', strtotime($order_data['order_date']));
        $order_data['created_at'] = date('Y-m-d h:i:s', strtotime($order_data['created_at']));
        $order_data['updated_at'] = date('Y-m-d h:i:s', strtotime($order_data['updated_at']));

        $conversion_rate = $order_data['conversion_rate'];

        $order_data['fee']['market_place_fee'] = number_format((float)$conversion_rate * isset($order_data['fee']['market_place_fee'])?$order_data['fee']['market_place_fee']:0, 2, '.', '');
        $order_data['fee']['credit_card_fee'] = number_format((float)$conversion_rate * isset($order_data['fee']['credit_card_fee'])?$order_data['fee']['credit_card_fee']:0, 2, '.', '');
        $order_data['fee']['processing_fee'] = number_format((float)$conversion_rate * isset($order_data['fee']['processing_fee'])?$order_data['fee']['processing_fee']:0, 2, '.', '');
        $order_data['fee']['base_shippping_cost'] = number_format((float)$conversion_rate * isset($order_data['fee']['base_shippping_cost'])?$order_data['fee']['base_shippping_cost']:0, 2, '.', '');
        $order_data['fee']['shipping_cost_tax'] = number_format((float)$conversion_rate * isset($order_data['fee']['shipping_cost_tax'])?$order_data['fee']['shipping_cost_tax']:0, 2, '.', '');
        $order_data['fee']['base_handling_cost'] = number_format((float)$conversion_rate * isset($order_data['fee']['base_handling_cost'])?$order_data['fee']['base_handling_cost']:0, 2, '.', '');
        $order_data['fee']['handling_cost_tax'] = number_format((float)$conversion_rate * isset($order_data['fee']['handling_cost_tax'])?$order_data['fee']['handling_cost_tax']:0, 2, '.', '');
        $order_data['fee']['base_wrapping_cost'] = number_format((float)$conversion_rate * isset($order_data['fee']['base_wrapping_cost'])?$order_data['fee']['base_wrapping_cost']:0, 2, '.', '');
        $order_data['fee']['wrapping_cost_tax'] = number_format((float)$conversion_rate * isset($order_data['fee']['wrapping_cost_tax'])?$order_data['fee']['wrapping_cost_tax']:0, 2, '.', '');

        $order_data['fee']['refunded_amount'] = number_format((float)$conversion_rate * isset($order_data['fee']['refunded_amount'])?$order_data['fee']['refunded_amount']:0, 2, '.', '');
        $order_data['fee']['discount_amount'] = number_format((float)$conversion_rate * isset($order_data['fee']['discount_amount'])?$order_data['fee']['discount_amount']:0, 2, '.', '');
        $order_data['fee']['coupon_discount'] = number_format((float)$conversion_rate * isset($order_data['fee']['coupon_discount'])?$order_data['fee']['coupon_discount']:0, 2, '.', '');


        $order_data['total_amount'] = number_format((float)$conversion_rate * isset($order_data['total_amount'])?$order_data['total_amount']:0, 2, '.', '');


        $check_order = Order::findOne([
            'connection_order_id' => $order_data['connection_order_id'],
            'user_connection_id' => $order_data['user_connection_id']
        ]);

        $customer = NULL;

        if ( $order_data['order_customer']['connection_customerId'] > 0 ){
            $customer = Customer::findOne([
                'user_connection_id' => $order_data['user_connection_id'],
                'connection_customerId' => $order_data['order_customer']['connection_customerId']
            ]);
        }
        if ( empty($customer) && !empty($order_data['order_customer']['email']) ){

            $customer = Customer::findOne([
                'user_connection_id' => $order_data['user_connection_id'],
                'email' => $order_data['order_customer']['email']
            ]);
        }

        if ( empty($customer) && !empty($order_data['order_customer']['phone']) ){

            $customer = Customer::findOne([
                'user_connection_id' => $order_data['user_connection_id'],
                'phone' => $order_data['order_customer']['phone']
            ]);
        }


        if ( empty($customer) ) {
            $customer_data = $order_data['order_customer'];
            $customer_data['user_connection_id'] = $order_data['user_connection_id'];

            $customer_data['created_at'] = date('Y-m-d h:i:s', strtotime(isset($customer_data['created_at'])?$customer_data['created_at']:time()));;
            $customer_data['updated_at'] = date('Y-m-d h:i:s', strtotime(isset($customer_data['updated_at'])?$customer_data['updated_at']:time()));;
            $customer_id = Customer::customerImportingCommon($customer_data);

        } else {
            $customer_id = $customer->id;
        }

        if ( empty($customer_id) ) {
            return;
        }

        if ( !isset($order_data['fee']['payment_status']) || empty($order_data['fee']['payment_status'])) {
            $order_data['fee']['payment_status'] = $order_data['status'];
        }

        $orderFeeData = [
            'OrderFee' => $order_data['fee']
        ];

        if ( empty($check_order) ) {
            $check_order = new Order();


            $orderFeeModel = new OrderFee();

            $orderFeeModel->load($orderFeeData);
            $orderFeeModel->save(false);

            $orderFeeId = $orderFeeModel->id;

        } else {
            $orderFeeModel = OrderFee::findOne(['id' => $check_order->fee_id]);

            $orderFeeModel->load($orderFeeData);
            $orderFeeModel->save(false);

            $orderFeeId = $orderFeeModel->id;
        }

        $order_data['customer_id'] = $customer_id;
        $order_data['fee_id'] = $orderFeeId;
        $orderData = [
            'Order' => $order_data
        ];

        if ( $check_order->load($orderData) && $check_order->save(false) ){

            $order_id = $check_order->id;
            OrderProduct::deleteAll(['order_id' => $order_id]);

            $order_product_data = $order_data['order_products'];
            foreach ($order_product_data as $each_order_product){

                $orderedProduct = NULL;

                if ( $each_order_product['connection_product_id'] > 0 ) {
//                    $orderedProduct = Product::findOne([
//                        'user_connection_id' => $order_data['user_connection_id'],
//                        'connection_product_id' => $each_order_product['connection_product_id'],
//                    ]);
                    $orderedProduct = ProductConnection::findOne([
                        'user_connection_id' => $order_data['user_connection_id'],
                        'connection_product_id' => $each_order_product['connection_product_id'],
                    ]);
                }
                if ( empty($orderedProduct) && !empty($each_order_product['order_product_sku'])) {

                    $checkProduct = Product::findOne([
                        'user_id' => $order_data['user_id'],
                        'sku' => $each_order_product['order_product_sku'],
                    ]);

                    if ( !empty($checkProduct) ){

                        $orderedProduct = ProductConnection::findOne([
                            'user_connection_id' => $order_data['user_connection_id'],
                            'product_id' => $checkProduct->id,
                        ]);
                    }

                }
                if ( empty($orderedProduct) && !empty($each_order_product['name']) ){

                    $checkProduct = Product::findOne([
                        'user_id' => $order_data['user_id'],
                        'name' => $each_order_product['name'],
                    ]);

                    if ( !empty($checkProduct) ){

                        $orderedProduct = ProductConnection::findOne([
                            'user_connection_id' => $order_data['user_connection_id'],
                            'product_id' => $checkProduct->id,
                        ]);
                    }

                }

                if ( empty($orderedProduct)
                    && (isset($each_order_product['connection_variation_id'])
                        && !empty($each_order_product['connection_variation_id'])) )
                {
                    $connection_variation_id = $each_order_product['connection_variation_id'];

                    $getOrderedProduct = Product::find()
                        ->joinWith(['productVariations'])
                        ->joinWith(['productConnections'])
                        ->where(['product_connection.user_connection_id' => $order_data['user_connection_id']])
                        ->andWhere(['product_variation.connection_variation_id' => $connection_variation_id])
                        ->one();

                    if ( !empty($getOrderedProduct) ){
                        $orderedProduct = ProductConnection::findOne([
                            'user_connection_id' => $order_data['user_connection_id'],
                            'product_id' => $getOrderedProduct->id,
                        ]);
                    }
                }

                if ( empty($orderedProduct) ) {

                    $newProduct = new Product();

                    $newProduct->name = $each_order_product['name'];
                    $newProduct->sku = $each_order_product['order_product_sku'];
                    $newProduct->adult = Product::ADULT_NO;
                    $newProduct->gender = UserProfile::GENDER_UNISEX;
                    $newProduct->stock_quantity = 0;
                    $newProduct->stock_level = Product::STOCK_LEVEL_OUT_STOCK;
                    $newProduct->stock_status = Product::STOCK_STATUS_HIDDEN;

                    $orderedProduct_price = $each_order_product['price'];
                    if ($conversion_rate > 0 ){
                        $orderedProduct_price = number_format((float)$conversion_rate * $orderedProduct_price, 2, '.', '');
                    }

                    $newProduct->price = $orderedProduct_price;
                    $newProduct->sales_price = $orderedProduct_price;
                    $newProduct->user_id = $order_data['user_id'];
                    $newProduct->status = Product::STATUS_INACTIVE;
                    $newProduct->permanent_hidden = Product::STATUS_YES;
                    $newProduct->currency = $order_data['currency_code'];
                    $newProduct->country_code = $order_data['country_code'];
                    $newProduct->published = Product::PRODUCT_PUBLISHED_NO;

                    $newProduct->save(false);

                    $orderedProduct = new ProductConnection();

                    $orderedProduct->user_id = $order_data['user_id'];
                    $orderedProduct->product_id = $newProduct->id;
                    $orderedProduct->user_connection_id = $order_data['user_connection_id'];
                    $orderedProduct->connection_product_id = '0';
                    $orderedProduct->status = ProductConnection::STATUS_NO;
                    $orderedProduct->save(false);
                }

                if ( !empty($orderedProduct) ) {

                    $order_product = new OrderProduct();
                    $each_order_product['order_id'] = $order_id;
                    $each_order_product['product_id'] = $orderedProduct->product_id;
                    $each_order_product['price'] = number_format((float)$conversion_rate * isset($each_order_product['price'])?$each_order_product['price']:0, 2, '.', '');
                    $orderProductData = [
                        'OrderProduct' => $each_order_product
                    ];
                    $order_product->load($orderProductData);
                    $order_product->save(false);
                }
            }
        }
    }

    public static function statuses()
    {
        return [
            OrderStatus::CANCEL,
            OrderStatus::PENDING,
            OrderStatus::REFUNDED,
            OrderStatus::COMPLETED,
            OrderStatus::IN_TRANSIT,
            OrderStatus::ON_HOLD,
        ];
    }

    public static function visibles()
    {
        return [
            self::ORDER_VISIBLE_ACTIVE,
            self::ORDER_VISIBLE_INACTIVE
        ];
    }

}
