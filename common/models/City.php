<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%city}}".
 *
 * @property string $id
 * @property string $name
 * @property integer $state_id
 */
class City extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%city}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['state_id'], 'integer'],
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
            'state_id' => Yii::t('common', 'State ID'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CityQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CityQuery(get_called_class());
    }
}
