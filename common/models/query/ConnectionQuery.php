<?php

namespace common\models\query;

use common\models\Connection;
use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[\common\models\Connection]].
 *
 * @see \common\models\Connection
 */
class ConnectionQuery extends ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\Connection[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Connection|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function channel()
    {
        $this->andWhere(['type_id' => Connection::CONNECTION_TYPE_CHANNEL]);
        return $this;
    }

    public function store()
    {
        $this->andWhere(['type_id' => Connection::CONNECTION_TYPE_STORE]);
        return $this;
    }

    public function erp()
    {
        $this->andWhere(['type_id' => Connection::CONNECTION_TYPE_ERP]);
        return $this;
    }

    public function pos()
    {
        $this->andWhere(['type_id' => Connection::CONNECTION_TYPE_POS]);
        return $this;
    }

    public function enabled(){
        $this->andWhere(['enabled' => Connection::CONNECTED_ENABLED_YES]);
        return $this;
    }

}
