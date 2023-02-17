<?php

namespace common\models\query;

use common\models\Product;

/**
 * This is the ActiveQuery class for [[\common\models\Product]].
 *
 * @see \common\models\Product
 */
class ProductQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \common\models\Product[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\Product|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function Activated(){
        $this->andWhere(['not like', 'status', Product::STATUS_INACTIVE]);
        return $this;
    }

    public function Published(){
        $this->andWhere(['published' => Product::PRODUCT_PUBLISHED_YES]);
        return $this;
    }

    public function CreatedAtMinWhere($date){
        if (!empty($date)){

            $this->andFilterWhere(['>=', 'created_at', date('Y-m-d H:i:s', strtotime($date))]);
        }
        return $this;
    }

    public function CreatedAtMaxWhere($date){
        if (!empty($date)){
            $this->andFilterWhere(['<=', 'created_at', date('Y-m-d H:i:s', strtotime($date))]);
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
