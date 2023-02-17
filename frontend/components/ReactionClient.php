<?php

namespace frontend\components;


use yii\httpclient\Client;
use \Exception as Exception;

class ReactionClient
{
    public $reactionRequestClient = null;
    private $reaction_account = null;
    private $reaction_env = null;
    private $app_key = null;
    private $app_token = null;
    private $base_url = null;

    public function __construct( $config, $api_base = null )
    {

        if ( $this->reactionRequestClient == null ) {
            $this->reactionRequestClient = new Client([
                'requestConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
                'responseConfig' => [
                    'format' => Client::FORMAT_JSON
                ],
                'transport' => 'yii\httpclient\CurlTransport' // only cURL supports the options we need
            ]);
            $this->reaction_account = $config['account'];
            $this->reaction_env = $config['enviroment'];
            $this->app_key = $config['app_key'];
            $this->app_token = $config['app_token'];

            if ( $api_base == null ) {
                $this->base_url = "https://{$this->reaction_account}.{$this->reaction_env}.com.br/api/";
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
            $request = $this->reactionRequestClient->createRequest()
                ->setMethod($method)
                ->setUrl($url)
                ->addHeaders(
                    [
                        'x-reaction-api-appkey' => $this->app_key,
                        'x-reaction-api-apptoken' => $this->app_token,
                    ]
                )
                ->send();

        } else {
            $request = $this->reactionRequestClient->createRequest()
                ->setMethod($method)
                ->setUrl($url)
                ->addHeaders(
                    [
                        'x-reaction-api-appkey' => $this->app_key,
                        'x-reaction-api-apptoken' => $this->app_token,
                    ]
                )
                ->setData($params)
                ->send();

        }

        return json_decode($request->content, true);

    }


}
