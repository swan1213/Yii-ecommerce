<?php

use yii\db\Migration;

/**
 * Class m180130_080445_add_default_attribution_table
 */
class m180130_080445_add_default_attribution_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->addColumn('{{%attribution}}', 'default', $this->bigInteger()->defaultValue(0)->after('attribution_type'));
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropColumn('{{%attribution}}', 'default');
    }


}
