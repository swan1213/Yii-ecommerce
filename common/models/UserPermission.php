<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_permission}}".
 *
 * @property string $id
 * @property string $title
 * @property string $menu_permission
 * @property string $channel_permission
 * @property string $other_permission
 * @property string $user_id
 */
class UserPermission extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_permission}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['menu_permission', 'channel_permission', 'other_permission'], 'string'],
            [['user_id'], 'integer'],
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'title' => Yii::t('common', 'Title'),
            'menu_permission' => Yii::t('common', 'Menu Permission'),
            'channel_permission' => Yii::t('common', 'Channel Permission'),
            'other_permission' => Yii::t('common', 'Other Permission'),
            'user_id' => Yii::t('common', 'User ID'),
        ];
    }

    /**
     * @inheritdoc
     * @return \common\models\query\UserPermissionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\UserPermissionQuery(get_called_class());
    }
}
