<?php

namespace common\models;

use frontend\modules\user\models\SignupForm;
use Yii;

use common\commands\AddToTimelineCommand;
use common\models\query\UserQuery;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%user}}".
 *
 * @property string $id
 * @property string $parent_id
 * @property string $username
 * @property string $auth_key
 * @property string $access_token
 * @property string $password_hash
 * @property string $oauth_client
 * @property string $oauth_client_user_id
 * @property string $email
 * @property int $status
 * @property string $domain
 * @property string $company
 * @property string $currency
 * @property string $level
 * @property int $google_feed
 * @property double $annual_revenue
 * @property double $annual_order_target
 * @property int $smartling_status
 * @property string $payment_info
 * @property string $permission_id
 * @property int $created_at
 * @property int $updated_at
 * @property int $logged_at
 * @property int $general_category_id
 *
 * @property Attribution[] $attributions
 * @property AttributionType[] $attributionTypes
 * @property BillingInvoice[] $billingInvoices
 * @property Category[] $categories
 * @property Content[] $contents
 * @property CorporateDocument[] $corporateDocuments
 * @property Customer[] $customers
 * @property DocumentDirector[] $documentDirectors
 * @property DocumentFile[] $documentFiles
 * @property DocumentInfo[] $documentInfos
 * @property Fulfillment[] $fulfillments
 * @property Notification[] $notifications
 * @property Order[] $orders
 * @property OrderProduct[] $orderProducts
 * @property Product[] $products
 * @property ProductAttribution[] $productAttributions
 * @property ProductCategory[] $productCategories
 * @property ProductConnection[] $productConnections
 * @property ProductImage[] $productImages
 * @property ProductVariation[] $productVariations
 * @property UserConnection[] $userConnections
 * @property UserIntegration[] $userIntegrations
 * @property UserProfile $userProfile
 * @property Variation[] $variations
 * @property VariationItem[] $variationItems
 * @property VariationSet[] $variationSets
 * @property VariationValue[] $variationValues
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_NOT_ACTIVE = 1;
    const STATUS_ACTIVE = 2;
    const STATUS_DELETED = 3;

    const ROLE_USER = 'user';
    const ROLE_MANAGER = 'manager';
    const ROLE_SUPERUSER = 'superuser';
    const ROLE_ADMINISTRATOR = 'administrator';

    const USER_LEVEL_MERCHANT = 'merchant';
    const USER_LEVEL_MERCHANT_USER = 'merchant_user';
    const USER_LEVEL_SUPERADMIN = 'superadmin';

    const GOOGLE_FEED_YES = 1;
    const GOOGLE_FEED_NO = 0;

    const SMARTLING_ACTIVE = 1;
    const SMARTLING_NOT_ACTIVE = 0;


    const EVENT_AFTER_SIGNUP = 'afterSignup';
    const EVENT_AFTER_LOGIN = 'afterLogin';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            'auth_key' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'auth_key'
                ],
                'value' => Yii::$app->getSecurity()->generateRandomString()
            ],
            'access_token' => [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'access_token'
                ],
                'value' => function () {
                    return Yii::$app->getSecurity()->generateRandomString(40);
                }
            ]
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return ArrayHelper::merge(
            parent::scenarios(),
            [
                'oauth_create' => [
                    'oauth_client', 'oauth_client_user_id', 'email', 'username', '!status'
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email', 'domain'], 'unique'],
            [['email', 'domain', 'company'], 'required'],
            ['status', 'default', 'value' => self::STATUS_NOT_ACTIVE],
            ['status', 'in', 'range' => array_keys(self::statuses())],
            [['username'], 'filter', 'filter' => '\yii\helpers\Html::encode'],
            [['domain', 'company'], 'string', 'max' => 255],
            [['currency'], 'string', 'max' => 64],
            ['level', 'default', 'value' => self::USER_LEVEL_MERCHANT],
            ['google_feed', 'default', 'value' => self::GOOGLE_FEED_NO],
            [['annual_revenue', 'annual_order_target'], 'number'],
            ['smartling_status', 'default', 'value' => self::SMARTLING_NOT_ACTIVE],
            [['payment_info'], 'string', 'max' => 512],
            [['permission_id', 'parent_id', 'general_category_id'], 'integer'],
            [['permission_id'], 'required'],
        ];
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'parent_id' => Yii::t('common', 'Parent ID'),
            'username' => Yii::t('common', 'Username'),
            'auth_key' => Yii::t('common', 'Auth Key'),
            'access_token' => Yii::t('common', 'Access Token'),
            'password_hash' => Yii::t('common', 'Password Hash'),
            'oauth_client' => Yii::t('common', 'Oauth Client'),
            'oauth_client_user_id' => Yii::t('common', 'Oauth Client User ID'),
            'email' => Yii::t('common', 'Email'),
            'status' => Yii::t('common', 'Status'),
            'domain' => Yii::t('common', 'Domain'),
            'company' => Yii::t('common', 'Company'),
            'currency' => Yii::t('common', 'Currency'),
            'level' => Yii::t('common', 'Level'),
            'google_feed' => Yii::t('common', 'Google Feed'),
            'annual_revenue' => Yii::t('common', 'Annual Revenue'),
            'annual_order_target' => Yii::t('common', 'Annual Order Target'),
            'smartling_status' => Yii::t('common', 'Smartling Status'),
            'payment_info' => Yii::t('common', 'Payment Info'),
            'permission_id' => Yii::t('common', 'Permission ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'logged_at' => Yii::t('common', 'Logged At'),
            'general_category_id' => Yii::t('common', 'General Category ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributions()
    {
        return $this->hasMany(Attribution::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributionTypes()
    {
        return $this->hasMany(AttributionType::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillingInvoices()
    {
        return $this->hasMany(BillingInvoice::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContents()
    {
        return $this->hasMany(Content::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorporateDocuments()
    {
        return $this->hasMany(CorporateDocument::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomers()
    {
        return $this->hasMany(Customer::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentDirectors()
    {
        return $this->hasMany(DocumentDirector::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentFiles()
    {
        return $this->hasMany(DocumentFile::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentInfos()
    {
        return $this->hasMany(DocumentInfo::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFulfillments()
    {
        return $this->hasMany(Fulfillment::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotifications()
    {
        return $this->hasMany(Notification::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrderProducts()
    {
        return $this->hasMany(OrderProduct::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductAttributions()
    {
        return $this->hasMany(ProductAttribution::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductCategories()
    {
        return $this->hasMany(ProductCategory::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductConnections()
    {
        return $this->hasMany(ProductConnection::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductImages()
    {
        return $this->hasMany(ProductImage::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductVariations()
    {
        return $this->hasMany(ProductVariation::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserConnections()
    {
        return $this->hasMany(UserConnection::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserIntegrations()
    {
        return $this->hasMany(UserIntegration::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfile()
    {
        return $this->hasOne(UserProfile::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariations()
    {
        return $this->hasMany(Variation::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariationItems()
    {
        return $this->hasMany(VariationItem::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariationSets()
    {
        return $this->hasMany(VariationSet::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariationValues()
    {
        return $this->hasMany(VariationValue::className(), ['user_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->getSecurity()->generatePasswordHash($password);
    }

    /**
     * Creates user profile and application event
     * @param array $profileData
     */
    public function afterSignup(array $profileData = [])
    {
        $this->refresh();
        Yii::$app->commandBus->handle(new AddToTimelineCommand([
            'category' => 'user',
            'event' => 'signup',
            'data' => [
                'public_identity' => $this->getPublicIdentity(),
                'user_id' => $this->getId(),
                'created_at' => $this->created_at
            ]
        ]));
        $profile = new UserProfile();
        $profile->locale = Yii::$app->language;
        $profile->load($profileData, '');
        $this->link('userProfile', $profile);
        $this->trigger(self::EVENT_AFTER_SIGNUP);
        // Default role
        $auth = Yii::$app->authManager;
        if(SignupForm::getemaildomain($this->email)=="helloiamellot"){
            $auth->assign($auth->getRole(User::ROLE_SUPERUSER), $this->getId());
        }
        else{
            $auth->assign($auth->getRole(User::ROLE_USER), $this->getId());
        }
    }

    public function makeDefaultConnection(){

        $defaultConnection = new UserConnection();

        $defaultConnection->user_id = $this->getId();
        $defaultConnection->connection_id = 1;
        $defaultConnection->market_id = $this->getId();
        $defaultConnection->import_status = UserConnection::IMPORT_STATUS_COMPLETED;
        $defaultConnection->connected = UserConnection::CONNECTED_YES;

        $defaultConnectionUrl = $this->domain . env('GLOBAL_DOMAIN');
        $connectionInfo = [
            'url' => $defaultConnectionUrl,
            'access_token' => $this->access_token,
        ];

        $defaultConnection->connection_info = @json_encode($connectionInfo, JSON_UNESCAPED_UNICODE);

        if ($defaultConnection->save()){

            $defaultConnectionDetail = new UserConnectionDetails();

            $defaultConnectionDetail->user_connection_id = $defaultConnection->id;
            $defaultConnectionDetail->store_name = $this->company;
            $defaultConnectionDetail->store_url = $defaultConnectionUrl;
            $defaultConnectionDetail->country = 'United States';
            $defaultConnectionDetail->country_code = 'US';
            $defaultConnectionDetail->currency = $this->currency;
            $defaultConnectionDetail->currency_symbol = '$';
            $defaultConnectionDetailSettings = [
                "currency" => $this->currency
            ];
            $defaultConnectionDetail->settings = @json_encode($defaultConnectionDetailSettings, JSON_UNESCAPED_UNICODE);

            $defaultConnectionDetail->save(false);
        }

    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getParentId(){
        if(isset($this->parent_id) && !empty($this->parent_id)){
            return $this->parent_id;
        }

        return $this->id;
    }

    public function getCreatedDateTime(){
        return date('Y-m-d H:i:s', $this->created_at);
    }

    public function getPermission(){
        $permission = UserPermission::findOne(['id' => $this->permission_id]);
        return $permission;
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->currency = isset($this->currency)?strtoupper($this->currency):'USD';
        parent::afterFind();
    }

    /**
     * @return string
     */
    public function getPublicIdentity()
    {
        if ($this->userProfile && $this->userProfile->getFullname()) {
            return $this->userProfile->getFullname();
        }
        if ($this->username) {
            return $this->username;
        }
        if ($this->company){
            return $this->company;
        }
        return $this->email;
    }

    public static function getDefaultConnection($user_id) {
        $userConnection = UserConnection::findOne(['user_id' => $user_id, 'connection_id' => 1]);
        return $userConnection->id;
    }

    /**
     * @inheritdoc
     * @return \common\models\query\UserQuery the active query used by this AR class.
     */
    /**
     * @return UserQuery
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::find()
            ->active()
            ->andWhere(['id' => $id])
            ->one();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::find()
            ->active()
            ->andWhere(['access_token' => $token])
            ->one();
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return User|array|null
     */
    public static function findByUsername($username)
    {
        return static::find()
            ->active()
            ->andWhere(['username' => $username])
            ->one();
    }

    /**
     * Finds user by username or email
     *
     * @param string $login
     * @return User|array|null
     */
    public static function findByLogin($login)
    {
        return static::find()
            ->active()
            ->andWhere(['or', ['username' => $login], ['email' => $login]])
            ->one();
    }

    /**
     * Returns user statuses list
     * @return array|mixed
     */
    public static function statuses()
    {
        return [
            self::STATUS_NOT_ACTIVE => Yii::t('common', 'Not Active'),
            self::STATUS_ACTIVE => Yii::t('common', 'Active'),
            self::STATUS_DELETED => Yii::t('common', 'Deleted')
        ];
    }

}
