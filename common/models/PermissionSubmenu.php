<?php
namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%attribution}}".
 *
 * @property string $id
 * @property string $name
 * @property string $parent_id
 * @property string $description
 * @property string $created_at
 */
class PermissionSubmenu extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%permission_submenu}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id'], 'integer'],
            [['created_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 1024],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => PermissionMenu::className(), 'targetAttribute' => ['parent_id' => 'id']],
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
            'parent_id' => Yii::t('common', 'Parent ID'),
            'created_at' => Yii::t('common', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */

    public function getParent()
    {
        return $this->hasOne(PermissionMenu::className(), ['id' => 'parent_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\AttributionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\PermissionSubmenuQuery(get_called_class());
    }
}