<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%document_file}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $type
 * @property string $file_base
 * @property string $file_path
 * @property string $created_at
 * @property string $updated_at
 *
 * @property DocumentDirector[] $documentDirectors
 * @property User $user
 */
class DocumentFile extends \yii\db\ActiveRecord
{
    const DOCUMENT_TYPE_BANKING ="banking";
    const DOCUMENT_TYPE_BUSINESS ="business";
    const DOCUMENT_TYPE_DIRECTORS ="directors";
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_file}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['type'], 'required'],
            [['type'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['file_base', 'file_path'], 'string', 'max' => 512],
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
            'type' => Yii::t('common', 'Type'),
            'file_base' => Yii::t('common', 'File Base'),
            'file_path' => Yii::t('common', 'File Path'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentDirectors()
    {
        return $this->hasMany(DocumentDirector::className(), ['document_file_id' => 'id']);
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
     * @return \common\models\query\DocumentFileQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\DocumentFileQuery(get_called_class());
    }
}
