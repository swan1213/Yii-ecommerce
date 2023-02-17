<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%connection_category_list}}".
 *
 * @property int $id
 * @property int $parent_id
 * @property string $connection_id
 * @property string $connection_parent_id
 * @property string $category_connection_id
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Connection $connection
 */
class ConnectionCategoryList extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%connection_category_list}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'connection_id', 'connection_parent_id'], 'integer'],
            [['connection_id', 'name'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['category_connection_id'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 512],
            [['connection_id'], 'exist', 'skipOnError' => true, 'targetClass' => Connection::className(), 'targetAttribute' => ['connection_id' => 'id']],
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
            'connection_id' => Yii::t('common', 'Connection ID'),
            'connection_parent_id' => Yii::t('common', 'Connection Parent ID'),
            'category_connection_id' => Yii::t('common', 'Category Connection ID'),
            'name' => Yii::t('common', 'Name'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConnection()
    {
        return $this->hasOne(Connection::className(), ['id' => 'connection_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\ConnectionCategoryListQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ConnectionCategoryListQuery(get_called_class());
    }
}
