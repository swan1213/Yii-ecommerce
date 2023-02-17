<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%trial_period}}".
 *
 * @property string $id
 * @property integer $trial_days
 * @property string $created_at
 * @property string $updated_at
 */
class TrialPeriod extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%trial_period}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['trial_days'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'trial_days' => Yii::t('common', 'Trial Days'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\TrialPeriodQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\TrialPeriodQuery(get_called_class());
    }
}
