<?php

namespace common\models\query;
use common\models\Crontask;

/**
 * This is the ActiveQuery class for [[\common\models\Crontask]].
 *
 * @see \common\models\Crontask
 */
class CrontaskQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\Crontask[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Crontask|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function completed(){
        $this->andWhere(['completed' => Crontask::COMPLETED_YES]);
        return $this;
    }

    public function enabled(){
        $this->andWhere(['enabled' => Crontask::ENABLED_YES]);
        return $this;
    }

}
