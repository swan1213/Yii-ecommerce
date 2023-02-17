<?php
/**
 * Created by PhpStorm.
 * User: ForestPrincer
 * Date: 2017-12-07
 * Time: 19:12
 */

namespace frontend\components;

use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    //public $layout = 'common';
    public $userDomain = "";
    public static $userHomeUrl = "";

    public function beforeAction($action)
    {
        if (!Yii::$app->user->isGuest){
            $baseDomain = Yii::$app->params['globalDomain'];
            $userDomain = Yii::$app->user->identity->domain;
            $userFullDomain = $userDomain.$baseDomain;
            $requestHost = Yii::$app->request->serverName;
            if ( $requestHost !== $userFullDomain ) {
                $redirectUrl = $this->getUserHomeUrl(Yii::$app->request, $userDomain, $baseDomain);
                Yii::$app->response->redirect($redirectUrl, 301)->send();
            }

        }

        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
//        if (Yii::$app->user->isGuest){
//            return $this->goHome();
//        }

        return parent::afterAction($action, $result);
    }

    protected function getUserHomeUrl($request, $userDomain, $baseDomain){
        preg_match("/^(?<protocol>(http|https):\/\/)(((?<subdomain>[a-z]+)\.)*)((.*\.)*(?<domain>.+\.[a-z]+))$/", $request->hostInfo, $matches);

        return $matches['protocol'] . $userDomain . $baseDomain;

    }
}