<?php

use yii\db\Migration;

/**
 * Handles the creation of table `smartling_price`.
 */
class m171220_132809_create_smartling_price_table extends Migration
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


        $this->createTable('{{%smartling_price}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'target_language' => $this->string(),
            'locale_id' => $this->string(),
            'editing' => $this->string(),
            'rate1' => $this->string(),
            'rate2' => $this->string(),
            'post_edit' => $this->string(),
            'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'updated_at' => 'datetime on update current_timestamp',
        ], $tableOptions);

        $this->batchInsert('{{%smartling_price}}', ['id', 'target_language', 'locale_id', 'editing', 'rate1', 'rate2', 'post_edit'], [
            ['1','Arabic ','ar-EG','$0.21','$0.15','$0.06','$0.12'],
            ['2','Danish','da-DK','$0.25','$0.17','$0.08','$0.15'],
            ['3','Dutch','nl-NL','$0.20','$0.14','$0.06','$0.12'],
            ['4','English (Canada)','en-CA','$0.10','n/a','$0.10','$0.10'],
            ['5','English (UK)','en-GB','$0.10','n/a','$0.10','$0.10'],
            ['6','Finnish','fi-FI','$0.25','$0.17','$0.08','$0.15'],
            ['7','French (Canada)','fr-CA','$0.25','$0.17','$0.08','$0.15'],
            ['8','French (France)','fr-FR','$0.20','$0.14','$0.06','$0.12'],
            ['9','German','de-DE','$0.20','$0.14','$0.06','$0.12'],
            ['10','Hindi','hi-IN','$0.23','$0.16','$0.07','$0.14'],
            ['11','Indonesian','id-ID','$0.23','$0.16','$0.07','$0.14'],
            ['12','Italian','it-IT','$0.20','$0.14','$0.06','$0.12'],
            ['13','Japanese','ja-JP','$0.26','$0.18','$0.08','$0.16'],
            ['14','Korean','ko-KR','$0.26','$0.18','$0.08','$0.16'],
            ['15','Norwegian','nb-NO','$0.25','$0.17','$0.08','$0.15'],
            ['16','Polish','pl-PL','$0.17','$0.12','$0.05','$0.10'],
            ['17','Simplified Chinese','zh-CN','$0.15','$0.10','$0.05','$0.09'],
            ['18','Spanish (Latam)','es-LA','$0.15','$0.10','$0.05','$0.09'],
            ['19','Spanish (Spain)','es-ES','$0.20','$0.14','$0.06','$0.12'],
            ['20','Swedish','sv-SE','$0.25','$0.17','$0.08','$0.15'],
            ['21','Thai','th-TH','$0.23','$0.16','$0.07','$0.14'],
            ['22','Traditiona Hong Kong','zh-HK','$0.23','$0.16','$0.07','$0.14'],
            ['23','Traditional Taiwan','zh-TW','$0.23','$0.16','$0.07','$0.14'],
            ['24','Vietnamese','vi-VN','$0.23','$0.16','$0.07','$0.14'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('smartling_price');
    }
}
