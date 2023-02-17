<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\VariationValue]].
 *
 * @see \common\models\VariationValue
 */
class VariationValueQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\VariationValue[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\VariationValue|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
