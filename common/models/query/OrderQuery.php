<?php

namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\Order]].
 *
 * @see \common\models\Order
 */
class OrderQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\Order[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Order|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function CreatedAtMinWhere($date){
        if (!empty($date)){
            $this->andFilterWhere(['>=', 'order_date', date('Y-m-d H:i:s', strtotime($date))]);
        }
        return $this;
    }

    public function CreatedAtMaxWhere($date){
        if (!empty($date)){
            $this->andFilterWhere(['<=', 'order_date', date('Y-m-d H:i:s', strtotime($date))]);
        }
        return $this;
    }

    public function UpdatedAtMinWhere($date){
        if (!empty($date)){
            $this->andFilterWhere(['>=', 'updated_at', date('Y-m-d H:i:s', strtotime($date))]);
        }
        return $this;
    }

    public function UpdatedAtMaxWhere($date){
        if (!empty($date)){
            $this->andFilterWhere(['<=', 'updated_at', date('Y-m-d H:i:s', strtotime($date))]);
        }
        return $this;
    }

}
