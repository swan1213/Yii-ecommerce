<?php

namespace frontend\modules\api\v1\resources;

use yii\helpers\Url;

class UserChannel extends \common\models\UserConnection
{

    public function fields()
    {
        return [
            'id',
            'publicName',
        ];
    }


}