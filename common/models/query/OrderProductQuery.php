<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\OrderProduct]].
 *
 * @see \common\models\OrderProduct
 */
class OrderProductQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\OrderProduct[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\OrderProduct|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
