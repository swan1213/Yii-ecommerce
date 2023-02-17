<?php

namespace common\models;

use Yii;
use common\models\VariationValue;
use common\models\VariationItem;
/**
 * This is the model class for table "{{%variation_set}}".
 *
 * @property string $id
 * @property string $name
 * @property string $items
 * @property string $description
 * @property integer $item_count
 * @property string $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 */
class VariationSet extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%variation_set}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['items'], 'string'],
            [['item_count', 'user_id'], 'integer'],
            [['user_id'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 512],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'name' => Yii::t('common', 'Name'),
            'items' => Yii::t('common', 'Items'),
            'description' => Yii::t('common', 'Description'),
            'item_count' => Yii::t('common', 'Item Count'),
            'user_id' => Yii::t('common', 'User ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getItemNames(){
        $itemIds = $this->items;
        $itemsArray = explode('-', $itemIds);
        $items = VariationValue::find()
            ->select(['variation_item_id'])
            ->where(['in', 'id', $itemsArray])
            ->groupBy('variation_item_id')->all();
        $formattedStr = "";
        foreach ( $items as $item ){

            $formattedStr .= VariationValue::findItem($item->variation_item_id)->name;
            $itemId = $item->variation_item_id;
            $itemValues = VariationValue::find()
                ->select(['label', 'value'])->where(['in', 'id', $itemsArray])
                ->andWhere(['variation_item_id' => $itemId])
                ->all();

            foreach ( $itemValues as $itemValue ) {
                $tempItemStr = isset($itemValue->label)?$itemValue->label.'('.$itemValue->value.')':$itemValue->value;
                $formattedStr .= " | ". $tempItemStr;
            }
            $formattedStr .= '<br>';
        }
        return $formattedStr;
    }

    /**
     * @inheritdoc
     * @return \common\models\query\VariationSetQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\VariationSetQuery(get_called_class());
    }
}
