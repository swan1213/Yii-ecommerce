<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_connection}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $user_connection_id
 * @property string $connection_product_id
 * @property string $product_id
 * @property string $extra_fields
 * @property string $json_data
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Product $product
 * @property UserConnection $userConnection
 * @property User $user
 */
class ProductConnection extends \yii\db\ActiveRecord
{
    const STATUS_YES = "Yes";
    const STATUS_NO = "No";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_connection}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_connection_id', 'product_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['connection_product_id'], 'string', 'max' => 512],
            [['status'], 'string', 'max' => 32],
            [['extra_fields', 'json_data'], 'string'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'connection_product_id' => Yii::t('common', 'Connection Product ID'),
            'product_id' => Yii::t('common', 'Product ID'),
            'extra_fields' => Yii::t('common', 'Extra Fields'),
            'json_data' => Yii::t('common', 'JSON Data'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
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
     */
    public function afterFind()
    {
        if ( !empty($this->extra_fields) ){
            $this->extra_fields = @json_decode($this->extra_fields, true);
        }
        
        if ( !empty($this->json_data) ){
            $this->json_data = @json_decode($this->json_data, true);
        }

        parent::afterFind();
    }

    /**
     * @inheritdoc
     * @return \common\models\query\ProductConnectionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ProductConnectionQuery(get_called_class());
    }
}
