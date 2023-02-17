<?php

use yii\db\Migration;

/**
 * Handles adding parent_id to table `category`.
 */
class m171217_133955_add_parent_id_column_to_category_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%category}}', 'parent_id', $this->bigInteger()->unsigned()->defaultValue(0)->after('description'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {

    }
}
