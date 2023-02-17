<?php

use yii\db\Migration;

/**
 * Handles adding settings to table `user_connection_details`.
 */
class m171218_021850_add_settings_column_to_user_connection_details_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%user_connection_details}}', 'settings', $this->string(512)->after('currency_symbol'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%user_connection_details}}', 'settings');
    }
}
