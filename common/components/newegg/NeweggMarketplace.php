<?php

namespace common\components\newegg;

use common\components\newegg\XMLNewegg;

class NeweggMarketplace {
	private $sellerId;
	private $baseUrl;
	private $header_array;
	private $api_key;
	private $secret_key;

	public function __construct($type, $sellerId, $api_key, $secret_key) {
		$this->sellerId = $sellerId;
		$this->api_key = $api_key;
		$this->secret_key = $secret_key;

		switch ($type) {
			case 'United States':
				$this->baseUrl = 'https://api.newegg.com/marketplace/';
				break;

			case 'Business':
				$this->baseUrl = 'https://api.newegg.com/marketplace/b2b/';
				break;

			case 'Canada':
				$this->baseUrl = 'https://api.newegg.com/marketplace/can/';
				break;
			
			default:
				throw new \Exception('Invalid the api url.');
				break;
		}

		$this->generateHeader($api_key, $secret_key);
	}

	// report
	public function submitReportRequest($payload) {
		$url = $this->baseUrl . 'reportmgmt/report/submitrequest?sellerid=' . $this->sellerId;
		return $this->curlHttp($url, $payload, 'POST', $this->header_array);
	}

	public function getReportStatus($payload) {
		$url = $this->baseUrl . 'reportmgmt/report/status?sellerid=' . $this->sellerId;
		return $this->curlHttp($url, $payload, 'PUT', $this->header_array);	
	}

	public function getReportResult($payload) {
		$url = $this->baseUrl . 'reportmgmt/report/result?sellerid=' . $this->sellerId;
		return $this->curlHttp($url, $payload, 'PUT', $this->header_array);	
	}

	// datafeed
	public function submitDatafeedRequest($payload, $requesttype) {
		$url = $this->baseUrl.'datafeedmgmt/feeds/submitfeed?sellerid='.$this->sellerId.'&requesttype='.$requesttype;
		$response = $this->curlHttp($url, $payload, 'POST', $this->header_array);
        $json_response = json_decode($response, true);

        if($json_response['IsSuccess'] == true and count($json_response['ResponseBody']['ResponseList']) > 0) {
        	return $json_response['ResponseBody']['ResponseList'][0]['RequestId'];
        } else {
        	throw new \Exception('Fail to update the product.');
        }
	}

	public function getDatafeedStatus($requestId) {
		$url = $this->baseUrl.'datafeedmgmt/feeds/status?sellerid='.$this->sellerId;
		$payload = [
			'OperationType' => 'GetFeedStatusRequest',
			'RequestBody' => [
				'GetRequestStatus' => [
					'RequestIDList' => [
						'RequestID' => $requestId
					],
					'MaxCount' => '30',
					'RequestStatus' => 'ALL' 
				]
			]
		];

		$response = $this->curlHttp($url, $payload, 'PUT', $this->header_array);
		$json_response = json_decode($response, true);

        if(isset($json_response['IsSuccess']) and $json_response['IsSuccess'] == true and count($json_response['ResponseBody']['ResponseList']) > 0) {
        	return $json_response['ResponseBody']['ResponseList'][0]['RequestStatus'];
        } else {
        	throw new \Exception('Fail to get datafeed status.');
        }
	}

	public function getDatafeedResult($requestId) {
		$url = $this->baseUrl.'datafeedmgmt/feeds/result/'.$requestId.'?sellerid='.$this->sellerId;
		$response = $this->curlHttp($url, null, 'GET', $this->header_array);
		$json_response = json_decode($response, true);

		if(isset($json_response['NeweggEnvelope']['Message']['ProcessingReport']['Result'][0]['ErrorList'])) {
			throw new \Exception('Invalid datafeed.');	
		}

		return $json_response['NeweggEnvelope']['Message']['ProcessingReport'];
	}

	public function getSubcategoryProperties($payload) {
		$url = $this->baseUrl . 'sellermgmt/seller/subcategoryproperty?sellerid=' . $this->sellerId;
		return $this->curlHttp($url, $payload, 'PUT', $this->header_array);
	}

	public function getSellerStatus() {
		$url = $this->baseUrl . 'sellermgmt/seller/accountstatus?sellerid=' . $this->sellerId . '&version=307';
		return $this->curlHttp($url, null, 'GET', $this->header_array);	
	}

	public function getServiceStatus() {
		$url = $this->baseUrl . 'reportmgmt/servicestatus?sellerid=' . $this->sellerId;
		return $this->curlHttp($url, null, 'GET', $this->header_array);
	}

	protected function generateHeader($api_key, $secret_key) {
		$this->header_array = array(
			'Authorization:'.$api_key, 
            'Secretkey:'.$secret_key,
            'Content-Type: application/json',
            'Accept: application/json'
        ); 
	}

	public function createProduct($product)
	{
		$payload = XMLNewegg::createProduct($product);
		$requestId = $this->submitDatafeedRequest($payload, 'ITEM_DATA');
        $is_finish = false;
        $count = 0;

        while ($count <= 15) {
            $status = $this->getDatafeedStatus($requestId);
            if($status == 'FINISHED') {
                $is_finish = true;
                break;
            }

            sleep(30 + $count * 10);
            $count ++;
        }

        if(!$is_finish) {
            throw new \Exception('Timeout to wait the product creation, please try again later.');
        }

        $result = $this->getDatafeedResult($requestId);
	}

	public function updateProduct($product)
	{
		$payload = XMLNewegg::updateProduct($product);
		$requestId = $this->submitDatafeedRequest($payload, 'ITEM_DATA');
        $is_finish = false;
        $count = 0;

        while ($count <= 15) {
            $status = $this->getDatafeedStatus($requestId);
            if($status == 'FINISHED') {
                $is_finish = true;
                break;
            }

            sleep(30 + $count * 10);
            $count ++;
        }

        if(!$is_finish) {
            throw new \Exception('Timeout to wait the product update, please try again later.');
        }

        $this->getDatafeedResult($requestId);
	}

	public function updateProductPrice($product) {
        $payload = XMLNewegg::updateProductPrice($product);
		$requestId = $this->submitDatafeedRequest($payload, 'INVENTORY_AND_PRICE_DATA');
        $is_finish = false;
        $count = 0;

        while ($count <= 15) {
            $status = $this->getDatafeedStatus($requestId);
            if($status == 'FINISHED') {
                $is_finish = true;
                break;
            }

            sleep(30 + $count * 10);
            $count ++;
        }

        if(!$is_finish) {
            throw new \Exception('Timeout to wait the product price data update, please try again later.');
        }

        $this->getDatafeedResult($requestId);
	}

	protected function curlHttp($url, $params=null, $method = 'GET', $header = []) {
		$opts[CURLOPT_HTTPHEADER]     = $header;
    	$opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_URL]            = $url;
        $opts[CURLOPT_SSL_VERIFYHOST] = 0;
        $opts[CURLOPT_SSL_VERIFYPEER] = 0;

        switch (strtoupper($method)) {
            case 'GET':
                if(empty($params)) {
                    $opts[CURLOPT_URL] = $url;
                } else {
                    $opts[CURLOPT_URL] = $url . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
                }
                break;
            case 'POST':
                $opts[CURLOPT_POST]           = 1;
                $opts[CURLOPT_POSTFIELDS]     = json_encode($params);
                break;
            case 'PUT':
                $opts[CURLOPT_CUSTOMREQUEST]  = 'PUT';
                $opts[CURLOPT_POSTFIELDS]     = json_encode($params);
                break;
            default:
                throw new \Exception('Unsupported request method!');
        }

        try {
            set_time_limit(0);
	        $ch = curl_init();

	        curl_setopt_array($ch, $opts);
	        $response  = curl_exec($ch);

	        $error = curl_error($ch);
	        curl_close($ch);
	        if ($error) {
	            throw new \Exception('Request error occurred:' . $error);
	        }

	        $response = str_replace("\xEF\xBB\xBF",'',$response); 
	        return $response;
        } catch(\InvalidArgumentException $e) {
            curl_close($ch);
            throw $e;
        } catch (\Exception $e) {
            curl_close($ch);
            throw $e;
        }
	}
}
