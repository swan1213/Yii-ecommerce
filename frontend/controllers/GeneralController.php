<?php

namespace frontend\controllers;

use common\models\CurrencyConversion;
use common\models\GeneralCategory;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\CurrencySymbol;
use yii;

use frontend\components\BaseController;

class GeneralController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'error'],
                'rules' => [
                    [
                        'actions' => ['index', 'error'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays General Page.
     *
     * @return string
     */
    public function actionIndex() {

        return $this->render('index');
    }

    public function actionSavecurrency() {
        if (isset($_POST) and ! empty($_POST) and isset($_POST['currency']) and ! empty($_POST['currency'])) {
            $user = Yii::$app->user->identity;
            $user->currency = $_POST['currency'];
            $user->save(false);
        }


        $selected_currency = CurrencySymbol::find()->where(['name' => strtolower($user->currency)])->select(['id', 'symbol'])->asArray()->one();
        if (isset($selected_currency) and ! empty($selected_currency)) {
            $currency_symbol = $selected_currency['symbol'];
        }

        $conversion_rate = 1;
        if (isset($user->currency) and $user->currency != 'USD') {
            $conversion_rate = CurrencyConversion::getCurrencyConversionRate('USD', $user->currency);
        }
        /* Save Currency conversion */
        if ($user->currency != '') {
            $conversion_rate = CurrencyConversion::getCurrencyConversionRate($user->currency, 'USD');
        }
        $array = ['annual_revenue' => $user->annual_revenue * $conversion_rate, 'currency_symbol' => $currency_symbol];
        echo json_encode($array);
        die;
    }

    public function actionSaverevenue() {
        if (isset($_POST) and ! empty($_POST) and isset($_POST['revenue'])) {
            $user = Yii::$app->user->identity;
            $value = $_POST['revenue'];
            if (strpos($value, ',') !== false) {
                $value = str_replace(',', '', $_POST['revenue']);
            } else {
                $value = $value;
            }
            $conversion_rate = 1;
            if (isset($user->currency) and $user->currency != 'USD') {
                $conversion_rate = CurrencyConversion::getCurrencyConversionRate($user->currency, 'USD');
                $value = $value * $conversion_rate;
            }
            $user->annual_revenue = $value;
            $user->save(false);
        }
        die;
    }

    public function actionCategories() {
        $categories = GeneralCategory::find()->asArray()->all();
        $final_array=[];
        if (isset($categories) and ! empty($categories)) {
            foreach ($categories as $category) {
                $single_array['value']= strtoupper($category['name']);
                $single_array['text']= strtoupper($category['name']);
                $final_array[]=$single_array;
            }
        }
        echo json_encode($final_array);
        die;
    }

    public function actionSaveCategory() {
        if (isset($_POST) and ! empty($_POST) and isset($_POST['category'])) {
            $user = Yii::$app->user->identity;
            $value = $_POST['category'];
            $selected_category = GeneralCategory::find()->where(['name' => $value])->one();
            if (!empty($selected_category)) {
                $user->general_category_id = $selected_category->id;
                $user->save(false);
            }
        }
        die;
    }
}