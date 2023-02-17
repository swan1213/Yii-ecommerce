<?php

use yii\db\Migration;

/**
 * Handles the creation of table `trial_period`.
 */
class m171208_053555_create_trial_period_table extends Migration
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

        $this->createTable('trial_period', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'trial_days' => $this->integer(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp'
        ], $tableOptions);


    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('trial_period');
    }
}
