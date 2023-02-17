<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\FulfillmentList]].
 *
 * @see \common\models\FulfillmentList
 */
class FulfillmentListQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\FulfillmentList[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\FulfillmentList|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
