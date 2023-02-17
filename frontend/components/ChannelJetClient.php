<?php

namespace frontend\components;


use yii\httpclient\Client;
use Yii;

class ChannelJetClient
{
    private $api_base_url = 'https://merchant-api.jet.com/api/';
    public $apiClient = null;
    private $apiUser = null;
    private $apiPass = null;
    private $apiToken = null;

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
            $this->apiUser = $config['apiUser'];
            $this->apiPass = $config['apiPass'];

            $this->apiToken = $this->getToken();
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
    private function getToken()
    {
        $method = 'post';
        $url = $this->api_base_url.'token';

        $params = [
            'user' => $this->apiUser,
            'pass' => $this->apiPass
        ];

        $request = $this->apiClient->createRequest()
            ->setMethod($method)
            ->setUrl($url)
            ->setOptions([
                CURLOPT_TIMEOUT => FALSE,
            ])
            ->setData($params)
            ->send();

        $response = json_decode($request->content, true);

        if ( isset($response['id_token']) ) {
            return $response['id_token'];
        }

        return null;
    }


    public function checkValidate(){

        if ( !empty($this->apiToken) )
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
                        'Authorization' => 'Bearer ' . $this->apiToken
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
                        'Authorization' => 'Bearer ' . $this->apiToken
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