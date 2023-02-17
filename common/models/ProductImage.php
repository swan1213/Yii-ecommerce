<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%product_image}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $product_id
 * @property string $label
 * @property string $link
 * @property string $alternative_image
 * @property string $html_video_link
 * @property string $degree_360_video_link
 * @property string $tag_status
 * @property string $tag
 * @property string $priority
 * @property string $default_image
 * @property integer $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $connection_image_id
 *
 * @property Product $product
 * @property User $user
 */
class ProductImage extends \yii\db\ActiveRecord
{
    const DEFAULT_IMAGE_YES = "Yes";
    const DEFAULT_IMAGE_NO = "No";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%product_image}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'product_id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['label', 'tag_status', 'tag', 'priority', 'connection_image_id'], 'string', 'max' => 255],
            [['link', 'alternative_image', 'html_video_link', 'degree_360_video_link'], 'string', 'max' => 512],
            [['default_image'], 'string', 'max' => 32],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
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
            'user_id' => Yii::t('common', 'User ID'),
            'product_id' => Yii::t('common', 'Product ID'),
            'label' => Yii::t('common', 'Label'),
            'link' => Yii::t('common', 'Link'),
            'alternative_image' => Yii::t('common', 'Alternative Image'),
            'html_video_link' => Yii::t('common', 'Html Video Link'),
            'degree_360_video_link' => Yii::t('common', 'Degree 360 Video Link'),
            'tag_status' => Yii::t('common', 'Tag Status'),
            'tag' => Yii::t('common', 'Tag'),
            'priority' => Yii::t('common', 'Priority'),
            'default_image' => Yii::t('common', 'Default Image'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'connection_image_id' => Yii::t('common', 'Connection Image ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\ProductImageQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\ProductImageQuery(get_called_class());
    }
}
