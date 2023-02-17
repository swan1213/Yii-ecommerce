<?php

namespace common\models;

use frontend\components\BigcommerceComponent;
use frontend\components\ShopifyComponent;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%user_connection}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $connection_id
 * @property string $market_id
 * @property string $connection_info
 * @property int $import_status
 * @property int $connected
 * @property int $smartling_status
 * @property int $mapping_status
 * @property int $fulfillment_list_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property BillingInvoice[] $billingInvoices
 * @property Category[] $categories
 * @property Customer[] $customers
 * @property Order[] $orders
 * @property ProductConnection[] $productConnections
 * @property Connection $connection
 * @property User $user
 * @property UserConnectionDetails $userConnectionDetails
 */
class UserConnection extends \yii\db\ActiveRecord
{
    const IMPORT_STATUS_PROCESSING = 0;
    const IMPORT_STATUS_FAIL = 1;
    const IMPORT_STATUS_COMPLETED = 2;
    const IMPORT_STATUS_COMPLETED_READ = 3;

    const SMARTLING_DISABLED = 0;
    const SMARTLING_ENABLED = 1;

    const CONNECTED_YES = 1;
    const CONNECTED_NO = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_connection}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'connection_id', 'connection_info'], 'required'],
            [['user_id', 'connection_id', 'import_status', 'connected', 'smartling_status', 'mapping_status', 'fulfillment_list_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['market_id'], 'string', 'max' => 255],
            [['connection_info'], 'string', 'max' => 1024],
            [['connection_id'], 'exist', 'skipOnError' => true, 'targetClass' => Connection::className(), 'targetAttribute' => ['connection_id' => 'id']],
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
            'connection_id' => Yii::t('common', 'Connection ID'),
            'market_id' => Yii::t('common', 'Market ID'),
            'connection_info' => Yii::t('common', 'Connection Info'),
            'import_status' => Yii::t('common', 'Import Status'),
            'connected' => Yii::t('common', 'Connected'),
            'smartling_status' => Yii::t('common', 'Smartling Status'),
            'mapping_status' => Yii::t('common', 'Mapping Status'),
            'fulfillment_list_id' => Yii::t('common', 'Fulfillment List ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillingInvoices()
    {
        return $this->hasMany(BillingInvoice::className(), ['user_connection_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['user_connection_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['user_connection_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['user_connection_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductConnections()
    {
        return $this->hasMany(ProductConnection::className(), ['user_connection_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConnection()
    {
        return $this->hasOne(Connection::className(), ['id' => 'connection_id']);
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
    public function getUserConnectionDetails()
    {
        return $this->hasOne(UserConnectionDetails::className(), ['user_connection_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\UserConnectionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\UserConnectionQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->connection_info = @json_decode($this->connection_info, true);
        parent::afterFind();
    }

    public function getConnectionType(){
        return $this->connection->type_id;
    }

    public function getPublicName(){
        //return $this->connection->connectionName."-".$this->userConnectionDetails->store_name."-".$this->userConnectionDetails->country;
        if ( !empty($this->userConnectionDetails) ){

            return $this->connection->connectionName." ".$this->userConnectionDetails->country;
        }

        return $this->connection->connectionName;
    }

    public function getConnectionName(){
        return $this->connection->connectionName;
    }

    public static function findIdsByUserIdandConnectionIds($userId, $connectionIds) {
        $allUserConnectionIds = UserConnection::find()
            ->where(['user_id' => $userId, 'connection_id' => $connectionIds])
            ->select(['id'])
            ->asArray()->all();
        return ArrayHelper::getColumn($allUserConnectionIds, 'id');

    }

    /**
     * set the fail status to UserConnection table with user connection id
     * $user_connection_id: id in UserConnection tabl
     */
    public static function setFailStatus($user_connection_id) {
        if(!empty($user_connection_id)) {
            $user_connection_row = self::find()->where(['id' => $user_connection_id])->one();

            if(!empty($user_connection_row)) {
                date_default_timezone_set("UTC");
                $user_connection_row->import_status = self::IMPORT_STATUS_FAIL;
                $user_connection_row->connected = self::CONNECTED_NO;
                $user_connection_row->connection_info = json_encode($user_connection_row->connection_info);;
                $user_connection_row->updated_at = date('Y-m-d h:i:s', time());
                $user_connection_row->save(false);
            }
        }
    }

    public static function disableUserConnction($user_connection_id){

        ini_set("memory_limit", "-1");
        set_time_limit(0);

        ShopifyComponent::removeShopifyHooks($user_connection_id);
        BigcommerceComponent::removeBigcommerceHooks($user_connection_id);

        $cronTaskfilterName = '%-'.$user_connection_id;
        Crontask::deleteAll(['like', 'name', $cronTaskfilterName]);


        Order::deleteAll(['user_connection_id' => $user_connection_id]);
        Customer::deleteAll(['user_connection_id' => $user_connection_id]);
        //Product::deleteAll(['user_connection_id' => $user_connection_id]);

        $pConnections = ProductConnection::findAll(['user_connection_id' => $user_connection_id]);
        foreach ( $pConnections as $pConnection) {
            $productId = $pConnection->product_id;
            $productConnectionsCount = ProductConnection::find()->where(['product_id' => $productId])->count();

            if ( $productConnectionsCount == 1 ){
                Product::deleteAll(['id' => $pConnection->product_id]);
            }
        }
        ProductConnection::deleteAll(['user_connection_id' => $user_connection_id]);

        Category::deleteAll(['user_connection_id' => $user_connection_id]);

        if (UserConnection::deleteAll(['id' => $user_connection_id])) {

            return true;
        }

        return false;

    }
}
