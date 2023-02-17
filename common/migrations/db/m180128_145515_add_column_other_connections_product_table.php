<?php

use yii\db\Migration;

/**
 * Class m180128_145515_add_column_other_connections_product_table
 */
class m180128_145515_add_column_other_connections_product_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%product}}', 'other_connections', $this->string(255)->after('user_connection_id'));
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180128_145515_add_column_other_connections_product_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180128_145515_add_column_other_connections_product_table cannot be reverted.\n";

        return false;
    }
    */
}
