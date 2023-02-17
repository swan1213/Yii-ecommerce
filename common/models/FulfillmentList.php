<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%fulfillment_list}}".
 *
 * @property string $id
 * @property string $name
 * @property string $link
 * @property string $image_link
 * @property string $type
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Fulfillment[] $fulfillments
 */
class FulfillmentList extends \yii\db\ActiveRecord
{
    const FULFILLMENT_TYPE_SOFTWARE = "Software";
    const FULFILLMENT_TYPE_CARRIERS = "Carriers";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%fulfillment_list}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'link', 'image_link'], 'string', 'max' => 512],
            [['type'], 'string', 'max' => 255],
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
            'link' => Yii::t('common', 'Link'),
            'image_link' => Yii::t('common', 'Image Link'),
            'type' => Yii::t('common', 'Type'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFulfillments()
    {
        return $this->hasMany(Fulfillment::className(), ['fulfillment_list_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\FulfillmentListQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\FulfillmentListQuery(get_called_class());
    }
}
