<?php

use yii\db\Migration;

/**
 * Handles adding parent_id to table `connection`.
 */
class m171208_205548_add_parent_id_column_to_connection_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%connection}}', 'parent_id', $this->bigInteger()->unsigned()->after('type_id')->defaultValue(0));
        $this->createIndex('parent_id', '{{%connection}}', 'parent_id');

    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%connection}}', 'parent_id');
    }
}
