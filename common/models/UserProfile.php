<?php

namespace common\models;

use trntv\filekit\behaviors\UploadBehavior;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_profile".
 *
 * @property string $user_id
 * @property integer $locale
 * @property string $firstname
 * @property string $middlename
 * @property string $lastname
 * @property string $photo
 * @property string $photo_path
 * @property string $photo_base_url
 * @property string $coverPicture
 * @property string $cover_path
 * @property string $cover_base_url
 * @property string $gender
 * @property string $dob
 * @property string $phoneno
 * @property integer $tax_rate
 * @property string $language
 * @property string $weight_preference
 * @property string $timezone
 * @property string $trial_period_status
 * @property integer $subscription_plan
 * @property integer $subscription_plan_status
 * @property integer $account_confirm_status
 * @property string $corporate_addr_street1
 * @property string $corporate_addr_street2
 * @property string $corporate_addr_city
 * @property string $corporate_addr_state
 * @property string $corporate_addr_zipcode
 * @property string $corporate_addr_country
 * @property string $corporate_phone_number
 * @property string $billing_addr_street1
 * @property string $billing_addr_street2
 * @property string $billing_addr_city
 * @property string $billing_addr_state
 * @property string $billing_addr_zipcode
 * @property string $billing_addr_country
 *
 * @property User $user
 */
class UserProfile extends ActiveRecord
{
    const GENDER_UNISEX = "Unisex";
    const GENDER_MALE = "Male";
    const GENDER_FEMALE = "Female";

    const TRIAL_STATUS_ACTIVE = 1;
    const TRIAL_STATUS_DEACTIVE = 0;

    const SUBSCRIPTION_PLAN_ACTIVE = 1;
    const SUBSCRIPTION_PLAN_DEACTIVE = 0;

    const ACCOUNT_CONFIRM_PENDING = 0;
    const ACCOUNT_CONFIRM_APPROVED = 1;

    const STR_EMPTY_VALUE = "Empty";
    /**
     * @var
     */
    public $photo;
    public $coverPicture;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_profile}}';
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'photo' => [
                'class' => UploadBehavior::className(),
                'attribute' => 'photo',
                'pathAttribute' => 'photo_path',
                'baseUrlAttribute' => 'photo_base_url'
            ],
            'coverPicture' => [
                'class' => UploadBehavior::className(),
                'attribute' => 'coverPicture',
                'pathAttribute' => 'cover_path',
                'baseUrlAttribute' => 'cover_base_url'
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['firstname', 'lastname'], 'required'],
            [
                [
                    'user_id', 'trial_period_status', 'subscription_plan',
                    'account_confirm_status', 'subscription_plan_status'
                ], 'integer'],
            [['gender'], 'in', 'range' => [self::GENDER_UNISEX, self::GENDER_MALE, self::GENDER_FEMALE]],
            [
                [
                    'firstname', 'middlename', 'lastname', 'photo_path', 'photo_base_url',
                    'cover_path', 'cover_base_url',
                    'corporate_addr_street1', 'corporate_addr_street2', 'corporate_addr_city',
                    'corporate_addr_state', 'corporate_addr_zipcode', 'corporate_addr_country', 'corporate_phone_number',
                    'billing_addr_street1', 'billing_addr_street2', 'billing_addr_city',
                    'billing_addr_state', 'billing_addr_zipcode', 'billing_addr_country'
                ], 'string', 'max' => 255],
            [['timezone'], 'string', 'max' => 64],
            [['phoneno', 'gender',  'language', 'weight_preference'], 'string', 'max' => 32],
            [['tax_rate'], 'number'],
            ['locale', 'default', 'value' => Yii::$app->language],
            ['locale', 'in', 'range' => array_keys(Yii::$app->params['availableLocales'])],
            ['photo', 'safe'],
            ['coverPicture', 'safe'],
            ['dob', 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => Yii::t('common', 'User ID'),
            'firstname' => Yii::t('common', 'Firstname'),
            'middlename' => Yii::t('common', 'Middlename'),
            'lastname' => Yii::t('common', 'Lastname'),
            'locale' => Yii::t('common', 'Locale'),
            'photo' => Yii::t('common', 'User Photo'),
            'coverPicture' => Yii::t('common', 'User Background Picture'),
            'gender' => Yii::t('common', 'Gender'),
            'dob' => Yii::t('common', 'Birth Of Day'),
            'phoneno' => Yii::t('common', 'Phone Number'),
            'tax_rate' => Yii::t('common', 'Tax Rate'),
            'language' => Yii::t('common', 'Language'),
            'weight_preference' => Yii::t('common', 'Weight Preference'),
            'timezone' => Yii::t('common', 'Time Zone'),
            'trial_period_status' => Yii::t('common', 'Trial Period Status'),
            'subscription_plan' => Yii::t('common', 'Subscription Plan'),
            'subscription_plan_status' => Yii::t('common', 'Subscription Status'),
            'account_confirm_status' => Yii::t('common', 'Subscription Plan'),
            'corporate_addr_street1' => Yii::t('common', 'Corporate Address Street1'),
            'corporate_addr_street2' => Yii::t('common', 'Corporate Address Street2'),
            'corporate_addr_city' => Yii::t('common', 'Corporate Address City'),
            'corporate_addr_state' => Yii::t('common', 'Corporate Address State'),
            'corporate_addr_zipcode' => Yii::t('common', 'Corporate Address ZipCode'),
            'corporate_addr_country' => Yii::t('common', 'Corporate Address Country'),
            'corporate_phone_number' => Yii::t('common', 'Corporate Phone Number'),
            'billing_addr_street1' => Yii::t('common', 'Billing Address Street1'),
            'billing_addr_street2' => Yii::t('common', 'Billing Address Street2'),
            'billing_addr_city' => Yii::t('common', 'Billing Address City'),
            'billing_addr_state' => Yii::t('common', 'Billing Address State'),
            'billing_addr_zipcode' => Yii::t('common', 'Billing Address ZipCode'),
            'billing_addr_country' => Yii::t('common', 'Billing Address Country'),
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
     * @return null|string
     */
    public function getFullName()
    {
        if ($this->firstname || $this->lastname) {
            return implode(' ', [$this->firstname, $this->lastname]);
        }
        return null;
    }

    /**
     * @param null $default
     * @return bool|null|string
     */
    public function getPhoto($default = null)
    {
        return $this->photo_path
            ? ($this->photo_base_url . '/' . $this->photo_path)
            : $default;
    }

    /**
     * @param null $default
     * @return bool|null|string
     */
    public function getCoverPicture($default = null)
    {
        return $this->cover_path
            ? ($this->cover_base_url . '/' . $this->cover_path)
            : $default;
    }
}
