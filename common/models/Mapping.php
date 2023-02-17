<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%mapping}}".
 *
 * @property int $id
 * @property string $user_id
 * @property string $elliot_id
 * @property string $store_id
 * @property string $connection_id
 * @property string $created_at
 * @property string $updated_at
 */
class Mapping extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%mapping}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'elliot_id', 'store_id', 'connection_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
            'elliot_id' => Yii::t('common', 'Elliot ID'),
            'store_id' => Yii::t('common', 'Channel ID'),
            'connection_id' => Yii::t('common', 'Connection ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\MappingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\MappingQuery(get_called_class());
    }
}
