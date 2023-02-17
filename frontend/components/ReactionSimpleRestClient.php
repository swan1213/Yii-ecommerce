<?php

namespace frontend\components;

use yii\httpclient\Client;
use \Exception as Exception;

class ReactionSimpleRestClient
{
    public $reactionRequestClient = null;
    private $reaction_account_id = null;
    private $reaction_url = null;
    private $reaction_account = null;
    private $reaction_pwd = null;
    private $base_url = null;
    private $user_token = null;

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
            $this->reaction_url = $config['reaction_url'];
            $this->reaction_account = $config['reaction_user_email'];
            $this->reaction_pwd = $config['reaction_user_pwd'];

            if ( substr($this->reaction_url, -1) === "/" ) {
                $this->base_url = $this->reaction_url;
            } else {
                $this->base_url = $this->reaction_url."/";
            }

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
        $method = 'post';
        $url = $this->base_url.'users/login';

        $sendData = [
            "email" => $this->reaction_account,
            "password" => $this->reaction_pwd,
        ];

        $request = $this->reactionRequestClient->createRequest()
            ->setMethod($method)
            ->setUrl($url)
            ->setOptions([
                CURLOPT_TIMEOUT => FALSE,
            ])
            ->setData($sendData)
            ->send();

        $response = json_decode($request->content, true);

        if ( isset($response['token']) ) {
            $this->user_token = $response['token'];
        } else {
            $this->user_token = null;
        }

        if ( isset($response['id']) ) {
            $this->reaction_account_id = $response['id'];
        } else {
            $this->reaction_account_id = null;
        }
    }

    public function checkValidate(){

        if ( !empty($this->user_token) )
            return true;

        return false;

    }

    public function getReactionAcctounId(){
        return $this->reaction_account_id;
    }

    public function getStoreUrl(){
        return $this->base_url;
    }


    public function call($method, $path, $params=array())
    {

        $url = $this->base_url.ltrim($path, '/');
        $method = strtolower($method);

        if ( in_array($method, array('get', 'delete')) ) {
            $request = $this->reactionRequestClient->createRequest()
                ->setMethod($method)
                ->setUrl($url)
                ->setOptions([
                    CURLOPT_TIMEOUT => FALSE,
                ])
                ->send();

        } else {
            $request = $this->reactionRequestClient->createRequest()
                ->setMethod($method)
                ->setUrl($url)
                ->setOptions([
                    CURLOPT_TIMEOUT => FALSE,
                ])
                ->setData($params)
                ->send();

        }

        return json_decode($request->content, true);

    }


}
