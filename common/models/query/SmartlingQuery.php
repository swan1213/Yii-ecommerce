<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\Smartling]].
 *
 * @see \common\models\Smartling
 */
class SmartlingQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\Smartling[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Smartling|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
