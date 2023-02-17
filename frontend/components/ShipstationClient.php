<?php

namespace frontend\components;


use yii\httpclient\Client;
use Yii;

class ShipstationClient
{
    private $api_base_url = 'https://ssapi.shipstation.com/';
    public $apiClient = null;
    private $apiKey = null;
    private $apiSecret = null;

    public function __construct($config)
    {
        if ( $this->apiClient == null ) {
            $this->apiClient = new Client([
                'requestConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
                'responseConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
                'transport' => 'yii\httpclient\CurlTransport' // only cURL supports the options we need
            ]);
            $this->apiKey = $config['apiKey'];
            $this->apiSecret = $config['apiSecret'];
        }

    }


    /**
     * Returns the array.
     *
     * @param string $method
     * @param string $path
     * @param array $params
     * @return array
     */
    public function call($method, $path, $params=array())
    {

        $url = $this->api_base_url.trim($path);
        $method = strtolower($method);

        if ( in_array($method, array('get', 'delete')) ) {
            $request = $this->apiClient->createRequest()
                ->setMethod($method)
                ->setUrl($url)
                ->addHeaders(
                    [
                        'Authorization' => 'Basic ' . base64_encode($this->apiKey . ":". $this->apiSecret)
                    ]
                )
                ->setOptions([
                    CURLOPT_TIMEOUT => FALSE,
                ])
                ->send();

        } else {
            $request = $this->apiClient->createRequest()
                ->setMethod($method)
                ->setUrl($url)
                ->addHeaders(
                    [
                        'Authorization' => 'Basic ' . base64_encode($this->apiKey . ":". $this->apiSecret)
                    ]
                )
                ->setOptions([
                    CURLOPT_TIMEOUT => FALSE,
                ])
                ->setData($params)
                ->send();

        }

        return $request->content;

    }

}