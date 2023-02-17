<?php
namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\PermissionSubmenu]].
 *
 * @see \common\models\PermissionMenu
 */
class PermissionSubmenuQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return \common\models\PermissionSubmenu[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\PermissionSubmenu|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}