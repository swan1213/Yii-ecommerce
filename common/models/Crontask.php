<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%crontask}}".
 *
 * @property int $id
 * @property string $name
 * @property string $action
 * @property string $params
 * @property string $completed
 * @property string $enabled
 * @property string $created_at
 * @property string $updated_at
 */
class Crontask extends \yii\db\ActiveRecord
{

    const COMPLETED_YES = "Yes";
    const COMPLETED_NO = "No";

    const ENABLED_YES = "Yes";
    const ENABLED_NO = "No";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%crontask}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'action'], 'required'],
            [['completed', 'enabled'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'action'], 'string', 'max' => 255],
            [['params'], 'string', 'max' => 1024],
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
            'action' => Yii::t('common', 'Action'),
            'params' => Yii::t('common', 'Params'),
            'completed' => Yii::t('common', 'Completed'),
            'enabled' => Yii::t('common', 'Enabled'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterFind()
    {
        $this->params = @json_decode($this->params, true);
        parent::afterFind();
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CrontaskQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CrontaskQuery(get_called_class());
    }
}
