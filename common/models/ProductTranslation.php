<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_translation}}".
 *
 * @property int $id
 * @property int $smartling_id
 * @property int $product_id
 * @property string $name
 * @property string $description
 * @property string $brand
 * @property string $override
 */
class ProductTranslation extends \yii\db\ActiveRecord
{

    const TRANSLATE_OVERRIDE_YES = "Yes";
    const TRANSLATE_OVERRIDE_NO = "No";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_translation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['smartling_id', 'product_id'], 'integer'],
            [['description'], 'string'],
            [['name', 'brand'], 'string', 'max' => 255],
            [['override'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'smartling_id' => Yii::t('common', 'Smartling ID'),
            'product_id' => Yii::t('common', 'Product ID'),
            'name' => Yii::t('common', 'Name'),
            'description' => Yii::t('common', 'Description'),
            'brand' => Yii::t('common', 'Brand'),
            'override' => Yii::t('common', 'Override'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\ProductTranslationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ProductTranslationQuery(get_called_class());
    }
}
