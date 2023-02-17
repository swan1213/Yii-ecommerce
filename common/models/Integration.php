<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%integration}}".
 *
 * @property int $id
 * @property int $type_id
 * @property string $name
 * @property string $url
 * @property string $image_url
 * @property string $enabled
 * @property string $created_at
 * @property string $updated_at
 *
 * @property UserIntegration[] $userIntegrations
 */
class Integration extends \yii\db\ActiveRecord
{

    const INTEGRATION_TYPE_ERP = 1;
    const INTEGRATION_TYPE_POS = 2;
    const INTEGRATION_TYPE_TRANSLATE = 3;

    const INTEGRATION_ENABLED_YES = "Yes";
    const INTEGRATION_ENABLED_NO = "No";
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%integration}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type_id'], 'integer'],
            [['name'], 'required'],
            [['enabled'], 'string'],
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
            'name' => Yii::t('common', 'Name'),
            'url' => Yii::t('common', 'Url'),
            'image_url' => Yii::t('common', 'Image Url'),
            'enabled' => Yii::t('common', 'Enabled'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserIntegrations()
    {
        return $this->hasMany(UserIntegration::className(), ['integration_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\IntegrationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\IntegrationQuery(get_called_class());
    }
}
