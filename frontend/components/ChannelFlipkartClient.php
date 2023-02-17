<?php

namespace frontend\components;

use yii\httpclient\Client;
use Yii;

class ChannelFlipkartClient
{
    private $api_base_url = 'https://api.flipkart.net/';
    public $apiClient = null;
    private $appId = null;
    private $appSecret = null;
    private $accessToken = null;

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
            $this->appId = $config['appId'];
            $this->appSecret = $config['appSecret'];

            $this->setAccessToken();
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
    private function setAccessToken()
    {
        $method = 'get';
        $url = $this->api_base_url.'oauth-service/oauth/token?grant_type=client_credentials&scope=Seller_Api';

        $request = $this->apiClient->createRequest()
            ->setMethod($method)
            ->setUrl($url)
            ->addHeaders(
                [
                    'Authorization' => 'Basic ' . base64_encode($this->appId . ":". $this->appSecret)
                ]
            )
            ->setOptions([
                CURLOPT_TIMEOUT => FALSE,
            ])
            ->send();

        $response = json_decode($request->content, true);

        if ( isset($response['access_token']) ) {
            $this->accessToken = $response['access_token'];
        } else {
            $this->accessToken = null;
        }
    }


    public function checkValidate(){

        if ( !empty($this->accessToken) )
            return true;

        return false;

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
                        'Authorization' => 'Bearer ' . $this->accessToken
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
                        'Authorization' => 'Bearer ' . $this->accessToken,
                    ]
                )
                ->setOptions([
                    CURLOPT_TIMEOUT => FALSE,
                ])
                ->setData($params)
                ->send();

        }

        return json_decode($request->content, true);

    }


}