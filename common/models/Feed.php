<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%feed}}".
 *
 * @property int $id
 * @property string $name
 * @property string $link
 *
 * @property UserFeed[] $userFeeds
 */
class Feed extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%feed}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 255],
            [['link'], 'string', 'max' => 512],
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
            'link' => Yii::t('common', 'Link'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserFeeds()
    {
        return $this->hasMany(UserFeed::className(), ['feed_id' => 'id']);
    }

    /**
     * @inheritdoc
     * @return \common\models\query\FeedQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\FeedQuery(get_called_class());
    }
}
