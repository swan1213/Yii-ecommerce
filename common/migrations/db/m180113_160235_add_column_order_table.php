<?php

use yii\db\Migration;

/**
 * Class m180113_160235_add_column_order_table
 */
class m180113_160235_add_column_order_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'ship_fname', $this->string(128)->after('product_quantity'));
        $this->addColumn('{{%order}}', 'ship_lname', $this->string(128)->after('ship_fname'));
        $this->addColumn('{{%order}}', 'ship_phone', $this->string(128)->after('ship_lname'));
        $this->addColumn('{{%order}}', 'ship_company', $this->string(128)->after('ship_phone'));

        $this->addColumn('{{%order}}', 'bill_fname', $this->string(128)->after('ship_country_iso'));
        $this->addColumn('{{%order}}', 'bill_lname', $this->string(128)->after('bill_fname'));
        $this->addColumn('{{%order}}', 'bill_phone', $this->string(128)->after('bill_lname'));
        $this->addColumn('{{%order}}', 'bill_company', $this->string(128)->after('bill_phone'));

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180113_160235_add_column_order_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m180113_160235_add_column_order_table cannot be reverted.\n";

        return false;
    }
    */
}
