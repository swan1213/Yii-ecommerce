<?php

namespace frontend\components;


use yii\httpclient\Client;
use \Exception as Exception;

class VtexClient
{
    public $vtexRequestClient = null;
    private $vtex_account = null;
    private $vtex_env = null;
    private $app_key = null;
    private $app_token = null;
    private $base_url = null;

    public function __construct( $config, $api_base = null )
    {

        if ( $this->vtexRequestClient == null ) {
            $this->vtexRequestClient = new Client([
                'requestConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
                'responseConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
                'transport' => 'yii\httpclient\CurlTransport' // only cURL supports the options we need
            ]);
            $this->vtex_account = $config['account'];
            $this->vtex_env = $config['enviroment'];
            $this->app_key = $config['app_key'];
            $this->app_token = $config['app_token'];

            if ( $api_base == null ) {
                $this->base_url = "http://{$this->vtex_account}.{$this->vtex_env}.com.br/api/";
            } else {

                $this->base_url = $api_base;
            }
        }
    }

    public function call($method, $path, $params=array())
    {

        $url = $this->base_url.ltrim($path, '/');
        $method = strtolower($method);

        if ( in_array($method, array('get', 'delete')) ) {
            $request = $this->vtexRequestClient->createRequest()
                ->setMethod($method)
                ->setUrl($url)
                ->addHeaders(
                    [
                        'x-vtex-api-appkey' => $this->app_key,
                        'x-vtex-api-apptoken' => $this->app_token,
                    ]
                )
                ->setOptions([
                    CURLOPT_TIMEOUT => FALSE,
                ])
                ->send();
        } else {
            $request = $this->vtexRequestClient->createRequest()
                ->setMethod($method)
                ->setUrl($url)
                ->addHeaders(
                    [
                        'x-vtex-api-appkey' => $this->app_key,
                        'x-vtex-api-apptoken' => $this->app_token,
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
