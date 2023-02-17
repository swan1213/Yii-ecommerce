<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%notification}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $title
 * @property string $message
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 */
class Notification extends \yii\db\ActiveRecord
{
    const NOTIFICATION_STATUS_READ = "Read";
    const NOTIFICATION_STATUS_UNREAD = "UnRead";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%notification}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'status'], 'string', 'max' => 255],
            [['message'], 'string', 'max' => 512],
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
            'title' => Yii::t('common', 'Title'),
            'message' => Yii::t('common', 'Message'),
            'status' => Yii::t('common', 'Status'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
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
     * @return \common\models\query\NotificationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\NotificationQuery(get_called_class());
    }

    public static function saveMessage($user_id, $notif_type, $msg) {
        $notification_model = new Notification();
        $notification_model->user_id = $user_id;
        $notification_model->title = $notif_type;
        $notification_model->message = $msg;
        $notification_model->save(false);
    }
}
