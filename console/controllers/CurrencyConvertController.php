<?php

namespace console\controllers;

use yii\console\Controller;
use common\components\XeCurrencyClient;
use yii\helpers\Json;
use common\models\CurrencyConversion;


class CurrencyConvertController extends Controller
{

    public function actionTest(){
        $xeClient = new XeCurrencyClient();

        $result = Json::decode($xeClient->getCurrencyRate("CNY"));

        print_r($result);
    }

    public function actionTestAll(){

        $xeClient = new XeCurrencyClient();
        $result = Json::decode($xeClient->getAllCurrencyRate());


        if ( isset($result['from']) ) {

            $rateObjects = $result['from'];

            foreach ($rateObjects as $eachRateObject){
                $quoteCurrency = $eachRateObject['quotecurrency'];
                $rateValue = $eachRateObject['mid'];

                $currency = CurrencyConversion::findOne([
                    'from_currency' => $quoteCurrency,
                ]);

                if ( empty($currency) ) {
                    $currency = new CurrencyConversion();
                }

                $currency->from_currency = $quoteCurrency;
                $currency->rate = $rateValue;

                $currency->save(false);

            }

        } else {
            echo "result = ";
            print_r($result);
        }


    }

    public function actionAllUsd(){
        $xeClient = new XeCurrencyClient();

        $result = Json::decode($xeClient->getAllCurrencyRate());

        if ( isset($result['from']) ) {

            $rateObjects = $result['from'];

            foreach ($rateObjects as $eachRateObject){
                $quoteCurrency = $eachRateObject['quotecurrency'];
                $rateValue = $eachRateObject['mid'];

                $currency = CurrencyConversion::findOne([
                    'from_currency' => $quoteCurrency,
                ]);

                if ( empty($currency) ) {
                    $currency = new CurrencyConversion();
                }

                $currency->from_currency = $quoteCurrency;
                $currency->rate = $rateValue;

                $currency->save(false);


            }

        } else {
            echo "result = ";
            print_r($result);
        }



    }


}