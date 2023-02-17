<?php

namespace frontend\modules\user\models;

use Yii;
use yii\base\Model;
use common\models\User;

class PasswordForm extends Model{
public $oldpass;
public $newpass;
public $repeatnewpass;

    public function rules(){
        return [
            [['oldpass', 'newpass', 'repeatnewpass'], 'required'],
             ['oldpass', 'findPasswords'],
             ['repeatnewpass', 'compare', 'compareAttribute' => 'newpass'],
        ];
    }

    public function findPasswords($attribute, $params){
        $user = User::find()->where(['id' => Yii::$app->user->identity->id])->one();
        $password = $user->password_hash;
        $password1 = Yii::$app->security->validatePassword($this->oldpass, $password);
        if($password1 != 1) {
            $this->addError($attribute, 'Old password is incorrect');
        }
    }
    
    public function attributeLabels(){
        return [
        'oldpass' => 'Old Password',
         'newpass' => 'New Password',
         'repeatnewpass' => 'Repeat New Password',
        ];
    }
    
    public function setPassword($password)
    {
        return Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        return Yii::$app->security->generateRandomString();
    }
    
}

