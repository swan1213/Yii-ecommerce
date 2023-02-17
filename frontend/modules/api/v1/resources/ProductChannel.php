<?php

namespace frontend\modules\api\v1\resources;

use frontend\modules\api\v1\resources\UserChannel;
use yii\helpers\Url;

class ProductChannel extends \common\models\ProductConnection
{

    public function fields()
    {
        return [
            'userChannels',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserChannels()
    {
        return $this->hasOne(UserChannel::className(), ['id' => 'user_connection_id']);
    }


}