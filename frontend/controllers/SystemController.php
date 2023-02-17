<?php
namespace frontend\controllers;

use yii\filters\AccessControl;

use frontend\components\BaseController;


class SystemController extends BaseController
{

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

    public function actionUpdates() {
        return $this->render('systemupdate');
    }

    public function actionStatus() {
        return $this->render('systemstatus');
    }


}