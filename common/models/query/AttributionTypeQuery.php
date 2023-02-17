<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\AttributionType]].
 *
 * @see \common\models\AttributionType
 */
class AttributionTypeQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\AttributionType[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\AttributionType|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
