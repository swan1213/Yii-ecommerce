<?php

use yii\db\Migration;
use common\models\Smartling;

/**
 * Handles the creation of table `smartling`.
 */
class m171208_080959_create_smartling_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('smartling', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'user_id' => $this->bigInteger(),
            'project_name' => $this->string(255),
            'token' => $this->string(255),
            'sm_user_id' => $this->string(512),
            'secret_key' => $this->string(512),
            'project_id' => $this->string(255),
            'connected' => $this->integer(1)->defaultValue(Smartling::CONNECTED_NO),
            'translation_type' => $this->string(255),
            'account_name' => $this->string(255),
            'account_id' => $this->string(255),
            'project_type' => $this->string(255),
            'project_type_value' => $this->string(255),
            'target_locale' => $this->text(),
            'job_translationJobUid' => $this->string(255),
            'job_jobName' => $this->string(255),
            'job_targetLocaleIds'=> $this->string(255),
            'job_referenceNumber' => $this->string(255),
            'job_callbackUrl' => $this->string(255),
            'job_callbackMethod' => $this->string(255),
            'job_createdByUserUid' => $this->string(255),
            'job_jobStatus' => $this->string(255),
            'translated_data_process' => $this->integer(1)->defaultValue(Smartling::TRANSLATED_STATUS_PENDING),
            'job_download_status' => $this->string(255),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp'

        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('smartling');
    }
}
