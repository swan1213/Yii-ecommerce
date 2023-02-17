<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%connection_parent}}".
 *
 * @property string $id
 * @property string $name
 * @property string $url
 * @property double $amount
 * @property string $image_url
 * @property string $created_at
 * @property string $updated_at
 */
class ConnectionParent extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%connection_parent}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['amount'], 'number'],
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
            'name' => Yii::t('common', 'Name'),
            'url' => Yii::t('common', 'Url'),
            'amount' => Yii::t('common', 'Amount'),
            'image_url' => Yii::t('common', 'Image Url'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildConnections()
    {
        $connections = Connection::findAll(['parent_id' => $this->id]);
        return $connections;
    }


    /**
     * @inheritdoc
     * @return \common\models\query\ConnectionParentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ConnectionParentQuery(get_called_class());
    }
}
