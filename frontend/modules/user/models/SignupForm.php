<?php

namespace frontend\modules\user\models;

use cheatsheet\Time;
use Yii;
use yii\base\Model;
use common\models\User;
use common\commands\SendEmailCommand;
use common\models\UserToken;
use frontend\modules\user\Module;
use yii\base\Exception;
use yii\helpers\Url;

/**
 * Signup form
 */
class SignupForm extends Model {

    public $username;
    public $company;
    public $email;
    public $password;
    public $confirm_password;
    public $acceptTerms;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                ['username', 'filter', 'filter' => 'trim'],
                ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => Yii::t('frontend', 'This username has already been taken.')],
                ['username', 'string', 'min' => 2, 'max' => 255],

                ['company', 'filter', 'filter' => 'trim'],
                ['company', 'required'],
                ['company', 'unique', 'targetClass' => '\common\models\User', 'message' => Yii::t('frontend', 'This Company has already been taken.')],
                ['company', 'string', 'min' => 2, 'max' => 255],

                ['email', 'filter', 'filter' => 'trim'],
                ['email', 'required'],
                ['email', 'email'],
                ['email', 'string', 'max' => 255],
                ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => Yii::t('frontend', 'This email address has already been taken.')],
            
                ['password', 'required'],
                ['password', 'string', 'min' => 6],
                ['confirm_password', 'required'],
                ['confirm_password', 'string', 'min' => 6],
                ['confirm_password', 'compare', 'compareAttribute' => 'password', 'message' => Yii::t('frontend', 'Passwords don\'t match')],
            	['acceptTerms', 'compare', 'compareValue' => 1, 'message' => Yii::t('frontend', 'You should accept term to use our service')],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup() {

        if ($this->validate()) {
            $shouldBeActivated = $this->shouldBeActivated();
            $user = new User();
            $user->company = $this->company;
//            $user->username = $this->username;
            $user->email = $this->email;
            //$domain = $this->getemaildomain($this->email);
            $domain = $this->getcompanydomain($this->company);
            $user->domain = $domain;
            $user->status = $shouldBeActivated ? User::STATUS_NOT_ACTIVE : User::STATUS_ACTIVE;
            $user->permission_id = 0;
            $user->setPassword($this->password);

            if (!$user->save()) {
                throw new Exception("User couldn't be  saved");
            };
            $user->afterSignup();

            $user->makeDefaultConnection();

            $mailChimpSyncData = [
                'email' => $this->email,
                'status' => 'subscribed'
            ];
            $this->syncMailChimp($mailChimpSyncData);

            if ($shouldBeActivated) {
                $token = UserToken::create(
                    $user->id,
                    UserToken::TYPE_ACTIVATION,
                    Time::SECONDS_IN_A_DAY
                );
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('frontend', 'Activation email'),
                    'view' => 'activation',
                    'to' => $this->email,
                    'params' => [
                        'url' => Url::to(['/user/sign-in/activation', 'token' => $token->token], true)
                    ]
                ]));
            } else {
                Yii::$app->commandBus->handle(new SendEmailCommand([
                    'subject' => Yii::t('frontend', 'Welcome Mail'),
                    'view' => '@common/mail/template',
                    'to' => $this->email,
                    'params' => [
                        'title' => 'Welcome Mail',
                        'content' => 'Hi '. $this->email . '<br>Thanks for Signing up',
                        'server' => env('SEVER_URL')
                    ]
                ]));

            }
            return $user;
        }

        return null;

    }

    public function checkEmail($attribute, $params) {
        
        $new_user_email = $this->email;
        /* explode a user new email */
        $explode_new_user_email = explode("@", $new_user_email);
        $new_user_email = $explode_new_user_email[1];
        $match_new_user_email = '@' . $new_user_email;
        $users_data = User::find()->where('email LIKE :query')->addParams([':query' => '%' . $match_new_user_email])->andWhere(['role' => 'merchant'])->all();
        if (!empty($users_data)) {
            // no real check at the moment to be sure that the error is triggered
            $users_check = User::find()->where('email LIKE :query')->addParams([':query' => '%' . $match_new_user_email])->andWhere(['role' => 'merchant'])->one();
            $contact_email=$users_check->email;
            
            $this->addError('email', 'Your company has already registered an account with Elliot, please contact '.$contact_email.' for access"');
            // $this->addError($attribute, Yii::t('user', 'You entered an invalid date format.'));
        }
    }

    public static function getemaildomain($new_user_email) {

        $explode_email = substr(strrchr($new_user_email, "@"), 1);
        $explode_domain = explode('.', $explode_email);
        $new_domain = $explode_domain[0];

        return $new_domain;
    }

    public function getcompanydomain($new_user_company) {

        $new_domain = strtolower($new_user_company);

        return $new_domain;
    }

    /**
     * @return bool
     */
    public function shouldBeActivated()
    {
        /** @var Module $userModule */
        $userModule = Yii::$app->getModule('user');
        if (!$userModule) {
            return false;
        } elseif ($userModule->shouldBeActivated) {
            return true;
        } else {
            return false;
        }
    }

    public function syncMailChimp($data) {
        $apiKey = env('MAILCHIMP_API_KEY');
        $listId = env('MAILCHIMP_LIST_ID');

        $memberId = md5(strtolower($data['email']));
        $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

        $json = json_encode([
            'email_address' => $data['email'],
            'status'        => $data['status'] // "subscribed","unsubscribed","cleaned","pending"
        ]);

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        // var_dump($result);
        // die('here');
        return $httpCode;

    }
}
