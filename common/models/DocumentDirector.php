<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%document_director}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $document_file_id
 * @property string $first_name
 * @property string $last_name
 * @property string $dob
 * @property string $address
 * @property string $last_4_social
 * @property string $created_at
 * @property string $updated_at
 *
 * @property DocumentFile $documentFile
 * @property User $user
 */
class DocumentDirector extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%document_director}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'document_file_id'], 'integer'],
            [['dob', 'created_at', 'updated_at'], 'safe'],
            [['first_name', 'last_name', 'address', 'last_4_social'], 'string', 'max' => 255],
            [['document_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => DocumentFile::className(), 'targetAttribute' => ['document_file_id' => 'id']],
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
            'document_file_id' => Yii::t('common', 'Document File ID'),
            'first_name' => Yii::t('common', 'First Name'),
            'last_name' => Yii::t('common', 'Last Name'),
            'dob' => Yii::t('common', 'Dob'),
            'address' => Yii::t('common', 'Address'),
            'last_4_social' => Yii::t('common', 'Last 4 Social'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDocumentFile()
    {
        return $this->hasOne(DocumentFile::className(), ['id' => 'document_file_id']);
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
     * @return \common\models\query\DocumentDirectorQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\DocumentDirectorQuery(get_called_class());
    }
}
