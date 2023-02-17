<?php

namespace frontend\controllers;

use yii\filters\AccessControl;
use yii\web\Controller;

class InstagramController extends Controller
{
    /**
     * action filter
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($id) {
        return $this->render('index');
    }
}