<?php
namespace common\models\query;

/**
 * This is the ActiveQuery class for [[\common\models\PermissionOther]].
 *
 * @see \common\models\PermissionMenu
 */
class PermissionOtherQuery extends \yii\db\ActiveQuery
{
    /**
     * @inheritdoc
     * @return \common\models\PermissionOther[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \common\models\PermissionOther|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}