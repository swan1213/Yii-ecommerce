<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%language}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $iso_639-1
 */
class Language extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%language}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'iso_639-1'], 'string', 'max' => 255],
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
            'iso_639-1' => Yii::t('common', 'Iso 639 1'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\LanguageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\LanguageQuery(get_called_class());
    }
}
