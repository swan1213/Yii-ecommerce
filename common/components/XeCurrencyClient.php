<?php

namespace common\components;


use yii\httpclient\Client;
use Yii;

class XeCurrencyClient
{
    private $xe_base_url = "https://xecdapi.xe.com/v1/";
    public $xeClient = null;
    private $xeAccountId = null;
    private $xeAccountApiKey = null;

    public function __construct()
    {
        if ( $this->xeClient == null ) {
            $this->xeClient = new Client();
            $this->xeAccountId = env('XE_ACCOUNT_ID');
            $this->xeAccountApiKey = env('XE_ACCOUNT_API_KEY');
        }

    }


    /**
     * Returns the array.
     *
     * @param array|string $from
     * @param string $to
     * @return array
     */
    public function getCurrencyRate($from, $to='USD', $amount = 1){

        if ( is_array($from) ) {
            $fromCurrency = implode(',', $from);
        } else {
            $fromCurrency = $from;
        }

        $getUrl = $this->xe_base_url.'convert_to.json/';

        $response = $this->xeClient->createRequest()
            ->setMethod('get')
            ->setUrl($getUrl)
            ->addHeaders(
                [
                    'Authorization' => 'Basic ' . base64_encode($this->xeAccountId . ":". $this->xeAccountApiKey)
                ]
            )
            ->setData([
                'to' => $to,
                'from' => $fromCurrency,
                'amount' => $amount
            ])
            ->send();

        return $response->content;
    }

    public function getAllCurrencyRate($to='USD', $amount = 1){

        $fromCurrency = "*";

        $getUrl = $this->xe_base_url.'convert_to.json/';

        $response = $this->xeClient->createRequest()
            ->setMethod('get')
            ->setUrl($getUrl)
            ->addHeaders(
                [
                    'Authorization' => 'Basic ' . base64_encode($this->xeAccountId . ":". $this->xeAccountApiKey)
                ]
            )
            ->setData([
                'to' => $to,
                'from' => $fromCurrency,
                'amount' => $amount
            ])
            ->send();

        return $response->content;
    }
}