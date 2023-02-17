<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%category}}".
 *
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $parent_id
 * @property string $user_id
 * @property string $user_connection_id
 * @property string $connection_category_id
 * @property string $connection_parent_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property UserConnection $userConnection
 * @property User $user
 * @property ProductCategory[] $productCategories
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['parent_id', 'user_id', 'user_connection_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'connection_category_id', 'connection_parent_id'], 'string', 'max' => 255],
            [['user_connection_id'], 'exist', 'skipOnError' => true, 'targetClass' => UserConnection::className(), 'targetAttribute' => ['user_connection_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
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
            'user_id' => Yii::t('common', 'User ID'),
            'user_connection_id' => Yii::t('common', 'User Connection ID'),
            'connection_category_id' => Yii::t('common', 'Connection Category ID'),
            'connection_parent_id' => Yii::t('common', 'Connection Parent ID'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserConnection()
    {
        return $this->hasOne(UserConnection::className(), ['id' => 'user_connection_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductCategories()
    {
        return $this->hasMany(ProductCategory::className(), ['category_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\CategoryQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CategoryQuery(get_called_class());
    }

    /**
     * Common function for Category Importing for all channels/stores
     */
    public static function categoryImportingCommon($category) {

        $checkCategory = Category::findOne(
            [
                'connection_category_id' => $category['connection_category_id'],
                'connection_parent_id' => $category['connection_parent_id'],
                'user_connection_id' => $category['user_connection_id']
            ]);

        if ( empty($checkCategory) ){
            $checkCategory = new Category();

            if ( isset($category['created_at']) && !empty($category['created_at']) ){
                $category['created_at'] = date('Y-m-d h:i:s', strtotime($category['created_at']));
            }
            if ( isset($category['updated_at']) && !empty($category['updated_at']) ){
                $category['updated_at'] = date('Y-m-d h:i:s', strtotime($category['updated_at']));
            }

            if ( $category['connection_parent_id'] > 0 ){
                $parentCategory = Category::findOne([
                    'user_connection_id' => $category['user_connection_id'],
                    'connection_category_id' => $category['connection_parent_id'],
                ]);
                if ( !empty($parentCategory) ){
                    $category['parent_id'] = $parentCategory->id;
                }
            }
            $categoryData = [
                'Category' => $category
            ];

            if ($checkCategory->load($categoryData) && $checkCategory->save(false)) {
                return $checkCategory->id;
            }

            return null;
        }


        return $checkCategory->id;

    }

    public static function categoryInsertCommon($category) {

        $checkCategory = new Category();

        if ( isset($category['created_at']) && !empty($category['created_at']) ){
            $category['created_at'] = date('Y-m-d h:i:s', strtotime($category['created_at']));
        }
        if ( isset($category['updated_at']) && !empty($category['updated_at']) ){
            $category['updated_at'] = date('Y-m-d h:i:s', strtotime($category['updated_at']));
        }

        if ( $category['connection_parent_id'] > 0 ){
            $parentCategory = Category::findOne([
                'user_connection_id' => $category['user_connection_id'],
                'connection_category_id' => $category['connection_parent_id'],
            ]);
            if ( !empty($parentCategory) ){
                $category['parent_id'] = $parentCategory->id;
            }
        }
        $categoryData = [
            'Category' => $category
        ];

        if ($checkCategory->load($categoryData) && $checkCategory->save(false)) {
            return $checkCategory->id;
        }

        return null;

    }

}
