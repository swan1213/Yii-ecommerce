<?php

namespace frontend\modules\user\controllers;

use common\base\MultiModel;
use common\models\User;
use common\models\UserProfile;
use frontend\modules\user\models\AccountForm;
use Intervention\Image\ImageManagerStatic;
use trntv\filekit\actions\DeleteAction;
use trntv\filekit\actions\UploadAction;
use Yii;
use yii\filters\AccessControl;
use frontend\components\BaseController;
use frontend\components\CustomFunction;
use frontend\modules\user\models\PasswordForm;

class ProfileController extends BaseController
{
    /**
     * @return array
     */
    public function actions()
    {
        return [
            'photo-upload' => [
                'class' => UploadAction::className(),
                'deleteRoute' => 'photo-delete',
                'on afterSave' => function ($event) {
                    /* @var $file \League\Flysystem\File */
                    $file = $event->file;
                    $img = ImageManagerStatic::make($file->read())->fit(215, 215);
                    $file->put($img->encode());
                }
            ],
            'photo-delete' => [
                'class' => DeleteAction::className()
            ]
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@']
                    ]
                ]
            ]
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionSetting()
    {
        $accountForm = new AccountForm();
        $accountForm->setUser(Yii::$app->user->identity);

        $model = new MultiModel([
            'models' => [
                'account' => $accountForm,
                'profile' => Yii::$app->user->identity->userProfile
            ]
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $locale = $model->getModel('profile')->locale;
            Yii::$app->session->setFlash('forceUpdateLocale');
            Yii::$app->session->setFlash('alert', [
                'options' => ['class' => 'alert-success'],
                'body' => Yii::t('frontend', 'Your account has been successfully saved', [], $locale)
            ]);
            return $this->refresh();
        }
        return $this->render('index', ['model' => $model]);
    }

    /**
     * Displays Profile.
     *
     * @return string
     */
    public function actionIndex() {
        return $this->render('index');
    }

    /* save profile iamge action */

    public function actionSaveProfileImage() {
        $baseDomain = Yii::$app->params['globalDomain'];
        $userDomain = Yii::$app->user->identity->domain;
        $baseURL = CustomFunction::getBaseUrl(Yii::$app->request, $userDomain, $baseDomain);
        $basedir = Yii::getAlias('@base');
        $ds = DIRECTORY_SEPARATOR;
        $storeFolder = $basedir . '/frontend/web/img/profile_images';
        $tempFile = $_FILES['file']['tmp_name'];
        $imageName = $_FILES['file']['name'];
        $RandomImageId = uniqid();
        //Generate Unique Name for each Image Uploaded
        $new_imageName = $RandomImageId . '_' . $imageName;
        //Path Setting
        $targetPath = $storeFolder . $ds;
        $targetFile = $targetPath . $new_imageName;
        //Save the Uploaded File
        $imageFile = move_uploaded_file($tempFile, $targetFile);

        //Image Thumbnail
        //Image::thumbnail(Yii::getAlias('@profile_images/' . $new_imageName), 71, 71)->save(Yii::getAlias('@profile_images/thumbnails/thumb_' . $new_imageName), ['quality' => 100]);

        //Temp entry into Product_images Table
        $userid = Yii::$app->user->identity->id;
        $userdata = UserProfile::find()->Where(['user_id' => $userid])->one();
        $userdata->photo_path = 'img/profile_images/'.$new_imageName;
        $userdata->photo_base_url = $baseURL;
        if ($userdata->save(false)) {
            $response = ['status' => 'success', 'data' => 'Profile image has been changed successfully'];
            Yii::$app->session->setFlash('success', 'Success! Profile image has been updated.');
        } else {
            $response = ['status' => 'success', 'data' => 'Failed to update Profile Image'];
            Yii::$app->session->setFlash('danger', 'Error! Failed to update Profile Image.');
        }
        echo json_encode($response);
        exit;
    }

    /**
     * Save account details through ajax.
     *
     * @return string
     */
    public function actionSaveprofile() {

        $request = Yii::$app->request;
        $userid = Yii::$app->user->identity->id;
        $emp_param = UserProfile::STR_EMPTY_VALUE;
        if (Yii::$app->request->post()) {

            $user = User::find()->Where(['id' => $userid])->one();
            $user->email = (Yii::$app->request->post('profile_email_add') == $emp_param) ? '' : Yii::$app->request->post('profile_email_add');
            if ($user->save(false)) {}

            $userdata = UserProfile::find()->Where(['user_id' => $userid])->one();
            $userdata->firstname = (Yii::$app->request->post('profile_first_name') == $emp_param) ? '' : Yii::$app->request->post('profile_first_name');
            $userdata->lastname = (Yii::$app->request->post('profile_last_name') == $emp_param) ? '' : Yii::$app->request->post('profile_last_name');
            $userdata->dob = (Yii::$app->request->post('profile_dob') == $emp_param) ? '' : Yii::$app->request->post('profile_dob');
            $userdata->gender = Yii::$app->request->post('gender');
            (Yii::$app->request->post('profile_email_add') == $emp_param) ? '' : Yii::$app->request->post('profile_email_add');
            $userdata->phoneno = (Yii::$app->request->post('profile_Phone_no') == $emp_param) ? '' : Yii::$app->request->post('profile_Phone_no');
            $userdata->timezone = (Yii::$app->request->post('profile_timezone') == $emp_param) ? '' : Yii::$app->request->post('profile_timezone');
            $userdata->corporate_addr_street1 = (Yii::$app->request->post('corporate_street1') == $emp_param) ? '' : Yii::$app->request->post('corporate_street1');
            $userdata->corporate_addr_street2 = (Yii::$app->request->post('corporate_street2') == $emp_param) ? '' : Yii::$app->request->post('corporate_street2');
            $userdata->corporate_addr_city = (Yii::$app->request->post('corporate_city') == $emp_param) ? '' : Yii::$app->request->post('corporate_city');
            $userdata->corporate_addr_state = (Yii::$app->request->post('corporate_state') == $emp_param) ? '' : Yii::$app->request->post('corporate_state');
            $userdata->corporate_addr_zipcode = Yii::$app->request->post('corporate_zip');
            (Yii::$app->request->post('profile_email_add') == $emp_param) ? '' : Yii::$app->request->post('profile_email_add');
            $userdata->corporate_addr_country = (Yii::$app->request->post('corporate_country') == $emp_param) ? '' : Yii::$app->request->post('corporate_country');
            $userdata->billing_addr_street1 = (Yii::$app->request->post('ship_street1') == $emp_param) ? '' : Yii::$app->request->post('ship_street1');
            $userdata->billing_addr_street2 = (Yii::$app->request->post('ship_street2') == $emp_param) ? '' : Yii::$app->request->post('ship_street2');
            $userdata->billing_addr_city = (Yii::$app->request->post('ship_city') == $emp_param) ? '' : Yii::$app->request->post('ship_city');
            $userdata->billing_addr_state = (Yii::$app->request->post('ship_state') == $emp_param) ? '' : Yii::$app->request->post('ship_state');
            $userdata->billing_addr_zipcode = (Yii::$app->request->post('ship_zip') == $emp_param) ? '' : Yii::$app->request->post('ship_zip');
            $userdata->billing_addr_country = (Yii::$app->request->post('ship_country') == $emp_param) ? '' : Yii::$app->request->post('ship_country');


            if ($userdata->save(false)) {
                $response = ['status' => 'success', 'data' => 'profile info saved'];
                Yii::$app->session->setFlash('success', 'Success! Profile info has been updated.');
            } else {

                $response = ['status' => 'error', 'data' => 'profile info not saved'];
                Yii::$app->session->setFlash('danger', 'Error! Profile info has not been updated.');
            }
        }
        echo json_encode($response);
        exit;
    }

    /**
     * For Change Password
     */
    public function actionChangePassword() {

        $model = new PasswordForm;
        $modeluser = User::find()->where(['id' => Yii::$app->user->identity->id])->one();

        if ($model->load(Yii::$app->request->post())) {

            if ($model->validate()) {

                $passHash = $model->setPassword($model->newpass);
                $authkey = $model->generateAuthKey();

                $modeluser->password_hash = $passHash;
                $modeluser->auth_key = $authkey;

                if ($modeluser->save(false)) {
                    Yii::$app->session->setFlash('success', 'Password Change SuccessFully.');
                    return $this->render('changePassword', [
                        'model' => $model
                    ]);
                }
            } else {
                return $this->render('changePassword', [
                    'model' => $model
                ]);
            }
        } else {
            return $this->render('changePassword', [
                'model' => $model
            ]);
        }
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset() {

        $this->layout = '@frontend/views/layouts/base';
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            if ($model->sendEmail()) {

                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token) {

        $this->layout = '@frontend/views/layouts/base';
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }


    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findProfileModel($id)->delete();
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    protected function findProfileModel($id) {
        if (($model = UserProfile::findOne(['user_id' => $id])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
