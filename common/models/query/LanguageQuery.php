<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\Language]].
 *
 * @see \common\models\Language
 */
class LanguageQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\Language[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Language|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
