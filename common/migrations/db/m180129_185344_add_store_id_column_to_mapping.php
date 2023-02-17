<?php

use yii\db\Migration;

/**
 * Class m180129_185344_add_store_id_column_to_mapping
 */
class m180129_185344_add_store_id_column_to_mapping extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%mapping}}', 'store_id', $this->bigInteger()->unsigned()->after('elliot_id'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%mapping}}', 'store_id');
    }
}
