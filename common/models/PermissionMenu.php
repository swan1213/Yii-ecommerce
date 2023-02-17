<?php
namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%attribution}}".
 *
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $created_at

 */
class PermissionMenu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%permission_menu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
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
            'description' => Yii::t('common', 'Description'),
            'created_at' => Yii::t('common', 'Created At'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\PermissionMenuQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\PermissionMenuQuery(get_called_class());
    }
}