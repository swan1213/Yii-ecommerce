<?php
namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%attribution}}".
 *
 * @property string $id
 * @property string $name
 * @property string $label
 * @property string $description
 * @property string $created_at

 */
class PermissionOther extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%permission_other}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['name', 'label'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1024],
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
            'label' => Yii::t('common', 'Label'),
            'description' => Yii::t('common', 'Description'),
            'created_at' => Yii::t('common', 'Created At'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\PermissionOtherQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\PermissionOtherQuery(get_called_class());
    }
}