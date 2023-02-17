<?php

use common\models\User;
use common\models\UserProfile;
use yii\db\Migration;

class m140703_123000_user extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->bigPrimaryKey()->unsigned(),
            'parent_id' => $this->bigInteger()->unsigned(),
            'username' => $this->string(32),
            'auth_key' => $this->string(32)->notNull(),
            'access_token' => $this->string(40)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'oauth_client' => $this->string(),
            'oauth_client_user_id' => $this->string(),
            'email' => $this->string()->notNull(),
            'status' => $this->smallInteger()->notNull()->defaultValue(User::STATUS_ACTIVE),
            'domain' => $this->string(255)->notNull(),
            'company' => $this->string(255)->notNull(),
            'currency' => $this->string(64)->defaultValue('USD'),
            'level'=> $this->string(32)->defaultValue(User::USER_LEVEL_MERCHANT),
            'google_feed' => $this->integer(1)->defaultValue(User::GOOGLE_FEED_NO),
            'annual_revenue' => $this->double(4)->defaultValue(0),
            'annual_order_target' => $this->double(4)->defaultValue(0),
            'smartling_status' => $this->integer(1)->defaultValue(User::SMARTLING_NOT_ACTIVE),
            'payment_info' => $this->string(512),
            'permission_id' => $this->bigInteger()->unsigned()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'logged_at' => $this->integer()
        ], $tableOptions);

        $this->createTable('{{%user_profile}}', [
            'user_id' => $this->bigPrimaryKey()->unsigned(),
            'firstname' => $this->string(),
            'middlename' => $this->string(),
            'lastname' => $this->string(),
            'photo_path' => $this->string(),
            'photo_base_url' => $this->string(),
            'cover_path' => $this->string(),
            'cover_base_url' => $this->string(),
            'locale' => $this->string(32)->notNull(),
            'gender' => $this->string(32)->defaultValue(UserProfile::GENDER_UNISEX),
            'dob' => $this->date(),
            'phoneno' => $this->string(32),
            'tax_rate' => $this->double(),
            'language' => $this->string(32)->defaultValue('english'),
            'weight_preference' => $this->string(32)->defaultValue('Ounces'),
            'timezone' => $this->string(64),
            'trial_period_status' => $this->integer(1)->defaultValue(UserProfile::TRIAL_STATUS_ACTIVE),
            'subscription_plan' => $this->integer()->null(),
            'subscription_plan_status' => $this->integer()->defaultValue(UserProfile::SUBSCRIPTION_PLAN_DEACTIVE),
            'account_confirm_status' => $this->integer(1)->defaultValue(UserProfile::ACCOUNT_CONFIRM_PENDING),
            'corporate_addr_street1' => $this->string(255)->null(),
            'corporate_addr_street2' => $this->string(255)->null(),
            'corporate_addr_city' => $this->string(255)->null(),
            'corporate_addr_state' => $this->string(255)->null(),
            'corporate_addr_zipcode' => $this->string(255)->null(),
            'corporate_addr_country' => $this->string(255)->null(),
            'corporate_phone_number' => $this->string(255)->null(),
            'billing_addr_street1' => $this->string(255)->null(),
            'billing_addr_street2' => $this->string(255)->null(),
            'billing_addr_city' => $this->string(255)->null(),
            'billing_addr_state' => $this->string(255)->null(),
            'billing_addr_zipcode' => $this->string(255)->null(),
            'billing_addr_country' => $this->string(255)->null(),
        ], $tableOptions);

        $this->addForeignKey('fk_user', '{{%user_profile}}', 'user_id', '{{%user}}', 'id', 'cascade', 'cascade');

    }

    public function down()
    {
        $this->dropForeignKey('fk_user', '{{%user_profile}}');
        $this->dropTable('{{%user_profile}}');
        $this->dropTable('{{%user}}');

    }
}
