<?php

use yii\db\Migration;

/**
 * Class m171218_111641_insert_permission_other
 */
class m171218_111641_insert_permission_other extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->insert('permission_other', ['name' => 'other_product_edit', 'label' => 'Single Product Edit']);
        $this->insert('permission_other', ['name' => 'other_assign_channel', 'label' => 'Assign Product to Channel']);
        $this->insert('permission_other', ['name' => 'other_fulfillment', 'label' => 'Fulfillment']);
        $this->insert('permission_other', ['name' => 'other_translation', 'label' => 'Translation']);
        $this->insert('permission_other', ['name' => 'other_currency', 'label' => 'Currency']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171218_111641_insert_permission_other cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m171218_111641_insert_permission_other cannot be reverted.\n";

        return false;
    }
    */
}
