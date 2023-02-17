<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%connection_attribution}}".
 *
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string $description
 * @property string $connection_id
 * @property string $created_at
 * @property string $updated_at
 */
class ConnectionAttribution extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%connection_attribution}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['connection_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['label'], 'string', 'max' => 512],
            [['description'], 'string', 'max' => 1024],
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
            'label' => Yii::t('common', 'Label'),
            'description' => Yii::t('common', 'Description'),
            'connection_id' => Yii::t('common', 'Connection ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\ConnectionAttributionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ConnectionAttributionQuery(get_called_class());
    }
}
