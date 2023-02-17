<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_feed}}".
 *
 * @property string $id
 * @property string $name
 * @property string $user_id
 * @property int $feed_id
 * @property string $feed_connection_id
 * @property string $link
 * @property string $code
 * @property string $categories
 * @property string $country_codes
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Feed $feed
 */
class UserFeed extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_feed}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'feed_id'], 'required'],
            [['user_id', 'feed_id'], 'integer'],
            [['categories', 'country_codes'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'link'], 'string', 'max' => 512],
            [['feed_connection_id'], 'string', 'max' => 64],
            [['code'], 'string', 'max' => 1024],
            [['feed_id'], 'exist', 'skipOnError' => true, 'targetClass' => Feed::className(), 'targetAttribute' => ['feed_id' => 'id']],
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
            'user_id' => Yii::t('common', 'User ID'),
            'feed_id' => Yii::t('common', 'Feed ID'),
            'feed_connection_id' => Yii::t('common', 'Feed Connection ID'),
            'link' => Yii::t('common', 'Link'),
            'code' => Yii::t('common', 'Code'),
            'categories' => Yii::t('common', 'Categories'),
            'country_codes' => Yii::t('common', 'Country Codes'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeed()
    {
        return $this->hasOne(Feed::className(), ['id' => 'feed_id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\UserFeedQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\UserFeedQuery(get_called_class());
    }
}
