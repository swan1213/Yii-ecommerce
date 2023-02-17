<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

use common\models\Country;
use common\models\Connection;
use common\models\ConnectionParent;
use common\models\UserConnection;
use common\models\UserConnectionDetails;
use frontend\models\ShiphawkConnectionForm;
use frontend\components\ConsoleRunner;

/**
 * NeweggController implements for Channels model.
 */
class ShiphawkController extends Controller {
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

    
}