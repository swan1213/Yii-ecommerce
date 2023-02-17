<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%fulfillment}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $fulfillment_list_id
 * @property int $connected
 * @property string $fulfillment_link
 * @property string $created_at
 * @property string $updated_at
 * @property string $connection_info
 *
 * @property FulfillmentList $fulfillmentList
 * @property User $user
 */
class Fulfillment extends \yii\db\ActiveRecord
{

    const CONNECTED_YES = 1;
    const CONNECTED_NO = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fulfillment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'fulfillment_list_id', 'connected'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['fulfillment_link'], 'string', 'max' => 512],
            [['connection_info'], 'string', 'max' => 1024],
            [['fulfillment_list_id'], 'exist', 'skipOnError' => true, 'targetClass' => FulfillmentList::className(), 'targetAttribute' => ['fulfillment_list_id' => 'id']],
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
            'name' => Yii::t('common', 'Name'),
            'fulfillment_list_id' => Yii::t('common', 'Fulfillment List ID'),
            'connected' => Yii::t('common', 'Connected'),
            'fulfillment_link' => Yii::t('common', 'Fulfillment Link'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'connection_info' => Yii::t('common', 'Connection Info'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFulfillmentList()
    {
        return $this->hasOne(FulfillmentList::className(), ['id' => 'fulfillment_list_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }


    public function afterFind()
    {
        $this->connection_info = @json_decode($this->connection_info, true);
        parent::afterFind();
    }
    /**
     * @inheritdoc
     * @return \common\models\query\FulfillmentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\FulfillmentQuery(get_called_class());
    }
}
