<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%state}}".
 *
 * @property string $id
 * @property string $name
 * @property integer $country_id
 */
class State extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%state}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'country_id' => Yii::t('common', 'Country ID'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\StateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\StateQuery(get_called_class());
    }
}
