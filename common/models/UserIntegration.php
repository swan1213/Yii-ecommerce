<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_integration}}".
 *
 * @property int $id
 * @property string $user_id
 * @property int $integration_id
 * @property int $connected
 * @property string $connection_info
 * @property string $extra_info
 * @property string $created_at
 * @property string $updated_at
 * @property int $import_status
 *
 * @property Integration $integration
 * @property User $user
 */
class UserIntegration extends \yii\db\ActiveRecord
{
    const IMPORT_STATUS_PROCESSING = 0;
    const IMPORT_STATUS_FAIL = 1;
    const IMPORT_STATUS_COMPLETED = 2;
    const IMPORT_STATUS_COMPLETED_READ = 3;

    const INTEGRATION_CONNECTED_YES = 1;
    const INTEGRATION_CONNECTED_NO = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_integration}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'integration_id', 'connection_info'], 'required'],
            [['user_id', 'integration_id', 'connected', 'import_status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['connection_info', 'extra_info'], 'string', 'max' => 1024],
            [['integration_id'], 'exist', 'skipOnError' => true, 'targetClass' => Integration::className(), 'targetAttribute' => ['integration_id' => 'id']],
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
            'integration_id' => Yii::t('common', 'Integration ID'),
            'connected' => Yii::t('common', 'Connected'),
            'connection_info' => Yii::t('common', 'Connection Info'),
            'extra_info' => Yii::t('common', 'Extra Info'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'import_status' => Yii::t('common', 'Import Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIntegration()
    {
        return $this->hasOne(Integration::className(), ['id' => 'integration_id']);
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
        $this->connection_info = @json_decode($this->connection_info, true);
        parent::afterFind();
    }

    /**
     * @inheritdoc
     * @return \common\models\query\UserIntegrationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\UserIntegrationQuery(get_called_class());
    }
}
