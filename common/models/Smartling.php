<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%smartling}}".
 *
 * @property string $id
 * @property int $user_id
 * @property int $user_connection_id
 * @property string $project_name
 * @property string $token
 * @property string $sm_user_id
 * @property string $secret_key
 * @property string $project_id
 * @property int $connected
 * @property string $translation_type
 * @property string $account_name
 * @property string $account_id
 * @property string $project_type
 * @property string $project_type_value
 * @property string $target_locale
 * @property string $job_translationJobUid
 * @property string $job_jobName
 * @property string $job_targetLocaleIds
 * @property string $job_referenceNumber
 * @property string $job_callbackUrl
 * @property string $job_callbackMethod
 * @property string $job_createdByUserUid
 * @property string $job_jobStatus
 * @property int $translated_data_process
 * @property string $job_download_status
 * @property string $created_at
 * @property string $updated_at
 */
class Smartling extends \yii\db\ActiveRecord
{

    const CONNECTED_YES = 1;
    const CONNECTED_NO = 0;

    const TRANSLATED_STATUS_PENDING = 0;
    const TRANSLATED_STATUS_PROCESS = 1;
    const TRANSLATED_STATUS_COMPLETE = 2;

    const SMARTLING_STATUS_ACTIVE = 1;
    const SMARTLING_STATUS_IN_ACTIVE = 0;


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%smartling}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_connection_id', 'connected', 'translated_data_process'], 'integer'],
            [['user_connection_id'], 'required'],
            [['target_locale'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['project_name', 'token', 'project_id', 'translation_type', 'account_name', 'account_id', 'project_type', 'project_type_value', 'job_translationJobUid', 'job_jobName', 'job_targetLocaleIds', 'job_referenceNumber', 'job_callbackUrl', 'job_callbackMethod', 'job_createdByUserUid', 'job_jobStatus', 'job_download_status'], 'string', 'max' => 255],
            [['sm_user_id', 'secret_key'], 'string', 'max' => 512],
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
            'project_name' => Yii::t('common', 'Project Name'),
            'token' => Yii::t('common', 'Token'),
            'sm_user_id' => Yii::t('common', 'Sm User ID'),
            'secret_key' => Yii::t('common', 'Secret Key'),
            'project_id' => Yii::t('common', 'Project ID'),
            'connected' => Yii::t('common', 'Connected'),
            'translation_type' => Yii::t('common', 'Translation Type'),
            'account_name' => Yii::t('common', 'Account Name'),
            'account_id' => Yii::t('common', 'Account ID'),
            'project_type' => Yii::t('common', 'Project Type'),
            'project_type_value' => Yii::t('common', 'Project Type Value'),
            'target_locale' => Yii::t('common', 'Target Locale'),
            'job_translationJobUid' => Yii::t('common', 'Job Translation Job Uid'),
            'job_jobName' => Yii::t('common', 'Job Job Name'),
            'job_targetLocaleIds' => Yii::t('common', 'Job Target Locale Ids'),
            'job_referenceNumber' => Yii::t('common', 'Job Reference Number'),
            'job_callbackUrl' => Yii::t('common', 'Job Callback Url'),
            'job_callbackMethod' => Yii::t('common', 'Job Callback Method'),
            'job_createdByUserUid' => Yii::t('common', 'Job Created By User Uid'),
            'job_jobStatus' => Yii::t('common', 'Job Job Status'),
            'translated_data_process' => Yii::t('common', 'Translated Data Process'),
            'job_download_status' => Yii::t('common', 'Job Download Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }
    public function afterFind()
    {
        $this->translation_type = @json_decode($this->translation_type, true);
        parent::afterFind();
    }
    /**
     * @inheritdoc
     * @return \common\models\query\SmartlingQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\SmartlingQuery(get_called_class());
    }
}
