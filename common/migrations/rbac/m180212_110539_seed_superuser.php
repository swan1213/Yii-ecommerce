<?php

use common\rbac\Migration;
use common\models\User;

class m180212_110539_seed_superuser extends Migration
{
    public function up()
    {


        $manager = $this->auth->getRole(User::ROLE_MANAGER);
        $administrator = $this->auth->getRole(User::ROLE_ADMINISTRATOR);
        $superuser = $this->auth->createRole(User::ROLE_SUPERUSER);
        $this->auth->add($superuser);
        $this->auth->addChild($superuser, $manager);

        $this->auth->removeChild($administrator, $manager);
        $this->auth->addChild($administrator, $superuser);


    }

    public function down()
    {
        echo "m180212_110539_seed_superuser cannot be reverted.\n";

        return false;
    }
}
