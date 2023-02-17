<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Product;

/**
 * ProductsSearch represents the model behind the search form about `app\models\Products`.
 */
class ProductSearch extends Product {

  /**
   * @inheritdoc
   */
  public function rules() {
    return [
      [['id', 'low_stock_notification'], 'integer'],
      [['name', 'sku', 'upc', 'ean', 'jan', 'isbn', 'mpn', 'description', 'adult', 'age_group', 'brand', 'condition', 'gender', 'weight', 'stock_quantity', 'stock_level', 'stock_status', 'price', 'sales_price', 'schedule_sales_date', 'created_at', 'updated_at'], 'safe'],
    ];
  }

  /**
   * @inheritdoc
   */
  public function scenarios() {
    // bypass scenarios() implementation in the parent class
    return Model::scenarios();
  }

  /**
   * Creates data provider instance with search query applied
   *
   * @param array $params
   *
   * @return ActiveDataProvider
   */
  public function searchInDB($params, $userId) {
    $query = Product::find(['user_id' => $userId]);

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      // uncomment the following line if you do not want to return any records when validation fails
      // $query->where('0=1');
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'low_stock_notification' => $this->low_stock_notification,
      'schedule_sales_date' => $this->schedule_sales_date,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    $query->andFilterWhere(['like', 'name', $this->name])
        ->andFilterWhere(['like', 'sku', $this->sku])
        ->andFilterWhere(['like', 'upc', $this->upc])
        ->andFilterWhere(['like', 'ean', $this->ean])
        ->andFilterWhere(['like', 'jan', $this->jan])
        ->andFilterWhere(['like', 'isbn', $this->isbn])
        ->andFilterWhere(['like', 'mpn', $this->mpn])
        ->andFilterWhere(['like', 'description', $this->description])
        ->andFilterWhere(['like', 'adult', $this->adult])
        ->andFilterWhere(['like', 'age_group', $this->age_group])
        ->andFilterWhere(['like', 'brand', $this->brand])
        ->andFilterWhere(['like', 'condition', $this->condition])
        ->andFilterWhere(['like', 'gender', $this->gender])
        ->andFilterWhere(['like', 'weight', $this->weight])
        ->andFilterWhere(['like', 'stock_quantity', $this->stock_quantity])
        ->andFilterWhere(['like', 'stock_level', $this->stock_level])
        ->andFilterWhere(['like', 'stock_status', $this->stock_status])
        ->andFilterWhere(['like', 'price', $this->price])
        ->andFilterWhere(['like', 'sales_price', $this->sales_price]);

    return $dataProvider;
  }

}
