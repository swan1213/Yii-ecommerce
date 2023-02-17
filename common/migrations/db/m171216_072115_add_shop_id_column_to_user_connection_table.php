<?php

use yii\db\Migration;

/**
 * Handles adding shop_id to table `user_connection`.
 */
class m171216_072115_add_shop_id_column_to_user_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%user_connection}}', 'shop_id', $this->string(255)->after('connection_id'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%user_connection}}', 'shop_id');
    }
}
