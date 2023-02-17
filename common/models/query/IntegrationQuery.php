<?php

namespace common\models\query;


use common\models\Integration;

/**
 * This is the ActiveQuery class for [[\common\models\Integration]].
 *
 * @see \common\models\Integration
 */
class IntegrationQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\Integration[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Integration|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function ErpChannel()
    {
        $this->andWhere(['type_id' => Integration::INTEGRATION_TYPE_ERP]);
        return $this;
    }

    public function PosChannel()
    {
        $this->andWhere(['type_id' => Integration::INTEGRATION_TYPE_POS]);
        return $this;
    }

    public function enabled()
    {
        $this->andWhere(['enabled' => Integration::INTEGRATION_ENABLED_YES]);
        return $this;
    }

}
