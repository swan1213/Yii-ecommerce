<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_connection_details}}".
 *
 * @property string $user_connection_id
 * @property string $store_name
 * @property string $store_url
 * @property string $country
 * @property string $country_code
 * @property string $currency
 * @property string $currency_symbol
 * @property string $settings
 * @property string $others
 * @property string $created_at
 * @property string $updated_at
 *
 * @property UserConnection $userConnection
 */
class UserConnectionDetails extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_connection_details}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['others'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['store_name', 'country', 'country_code'], 'string', 'max' => 255],
            [['store_url', 'settings'], 'string', 'max' => 512],
            [['currency', 'currency_symbol'], 'string', 'max' => 64],
            [['user_connection_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserConnection::className(), 'targetAttribute' => ['user_connection_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_connection_id' => Yii::t('common', 'User Connection ID'),
            'store_name' => Yii::t('common', 'Store Name'),
            'store_url' => Yii::t('common', 'Store Url'),
            'country' => Yii::t('common', 'Country'),
            'country_code' => Yii::t('common', 'Country Code'),
            'currency' => Yii::t('common', 'Currency'),
            'currency_symbol' => Yii::t('common', 'Currency Symbol'),
            'settings' => Yii::t('common', 'Settings'),
            'others' => Yii::t('common', 'Others'),
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
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->settings = @json_decode($this->settings, true);
        $this->others = @json_decode($this->others, true);

        parent::afterFind();
    }

    /**
     * @inheritdoc
     * @return \common\models\query\UserConnectionDetailsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\UserConnectionDetailsQuery(get_called_class());
    }
}
