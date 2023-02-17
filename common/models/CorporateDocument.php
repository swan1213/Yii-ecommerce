<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%corporate_document}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $business_license
 * @property string $business_paper
 * @property string $tax_id
 * @property string $connection
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $user
 */
class CorporateDocument extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%corporate_document}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['business_license', 'business_paper'], 'string', 'max' => 512],
            [['tax_id', 'connection'], 'string', 'max' => 255],
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
            'business_license' => Yii::t('common', 'Business License'),
            'business_paper' => Yii::t('common', 'Business Paper'),
            'tax_id' => Yii::t('common', 'Tax ID'),
            'connection' => Yii::t('common', 'Connection'),
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
     * @return \common\models\query\CorporateDocumentQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new \common\models\query\CorporateDocumentQuery(get_called_class());
    }
}
