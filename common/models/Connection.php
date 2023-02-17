<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%connection}}".
 *
 * @property string $id
 * @property string $type_id
 * @property string $parent_id
 * @property string $name
 * @property string $url
 * @property double $amount
 * @property string $image_url
 * @property string $enabled
 * @property string $created_at
 * @property string $updated_at
 * @property string $is_subscription
 *
 * @property ConnectionCategoryList[] $connectionCategoryLists
 * @property UserConnection[] $userConnections
 * @property string $connectionName
 */
class Connection extends \yii\db\ActiveRecord
{
    const CONNECTION_TYPE_ELLIOT = 0;
    const CONNECTION_TYPE_STORE = 1;
    const CONNECTION_TYPE_CHANNEL = 2;
    const CONNECTION_TYPE_ERP = 3;
    const CONNECTION_TYPE_POS = 4;

    const CONNECTED_ENABLED_YES = 'Yes';
    const CONNECTED_ENABLED_NO = 'No';
    const SUBSCRIPTION_YES = 'Yes';
    const SUBSCRIPTION_NO = 'No';

    const CONNECT_STATUS_GET = "Get Connected";
    const CONNECT_STATUS_CONNECTED = "Connected";
    const CONNECT_STATUS_COMINGSOON= "Comming Soon";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%connection}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id', 'parent_id'], 'integer'],
            [['name'], 'required'],
            [['amount'], 'number'],
            [['enabled', 'is_subscription'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['url', 'image_url'], 'string', 'max' => 512],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'type_id' => Yii::t('common', 'Type ID'),
            'parent_id' => Yii::t('common', 'Parent ID'),
            'name' => Yii::t('common', 'Name'),
            'url' => Yii::t('common', 'Url'),
            'amount' => Yii::t('common', 'Amount'),
            'image_url' => Yii::t('common', 'Image Url'),
            'enabled' => Yii::t('common', 'Enabled'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'is_subscription' => Yii::t('common', 'Subscription'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConnectionCategoryLists()
    {
        return $this->hasMany(ConnectionCategoryList::className(), ['connection_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserConnections()
    {
        return $this->hasMany(UserConnection::className(), ['connection_id' => 'id']);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        if ($this->parent_id > 0) {
            $connection = ConnectionParent::findOne(['id' => $this->parent_id]);
            return $connection;
        }
        return null;
    }

    public function getConnectionName(){
        $parent = $this->getParent();
        if(isset($parent) && !empty($parent)){
            $parentName = $parent->name;
            if ( $parentName !== $this->name ) {
                return $parentName.' '.$this->name;
            }
            return $this->name;
        }
        return $this->name;
    }

    public function getConnectionImage() {

        if ( !isset($this->image_url) && empty($this->image_url) ){
            $parent = $this->getParent();
            return $parent->image_url;
        }
        return $this->image_url;

    }

    public function generateConnectionFormLink() {
        $parent = $this->getParent();
        $parentName = $parent->name;
        if ( $parentName !== $this->name ) {
            return '/'.str_replace(' ', '', strtolower($parentName)).'?id='.$this->id;
        }
        return '/'.str_replace(' ', '', strtolower($parentName)).'?id='.$parent->id;
    }

    public static function findChildConnections($parentId){
        $childConnectionIds = Connection::find()->where(['parent_id' => $parentId])->select(['id'])->asArray()->all();

        return ArrayHelper::getColumn($childConnectionIds, 'id');
    }
    /**
     * @inheritdoc
     * @return \common\models\query\ConnectionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ConnectionQuery(get_called_class());
    }
}
