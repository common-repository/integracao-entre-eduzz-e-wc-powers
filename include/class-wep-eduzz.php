<?php
class Wooeduzzpowers_Eduzz {
	private $email;
	private $publickey;
	private $apikey;
	private $token;
 	private $urlBase;

 	public function __construct(){
		$this->urlBase = 'https://api2.eduzz.com/';
 		$this->setAccess();
 	}


 	public function saveAccess($access){

 		$resp = $this->sendCurl('credential/generate_token',$access,'POST');
 		if ($resp===FALSE) {
 			return false;
 		} 

 		$access['token'] = array($resp->token,date("YmdHis", strtotime($resp->token_valid_until)));
 		$access = json_encode($access);
		$up = update_option('wep_access_code', $access,FALSE);
		if (!$up) {
			$up = add_option('wep_access_code', $access);
		}

		$this->setAccess();
		return true;
 	}

 	public function setAccess(){
 		$access = get_option( 'wep_access_code', '' );

 		if (!empty($access)) {
 			$access = json_decode($access);
 			$this->email	=	$access->email;
 			$this->publickey	=	$access->publickey;
 			$this->apikey	=	$access->apikey;
 			$this->token	=	$access->token;
 		}
 	}
 	public function getAccess(){
 		return array(
 			'email' => $this->email,
 			'publickey' => $this->publickey,
 			'apikey' => $this->apikey,
 			'token' => $this->token,
 		);
 	}

	public function renovaToken(){
		$pars = array(
			'email' => $this->email,
			'publickey' => $this->publickey, 
			'apikey' => $this->apikey,  
		);

 		$resp = $this->sendCurl('credential/generate_token',$pars,'POST');
 		if ($resp!==FALSE) {
 			$this->$token = array($resp->token,date("YmdHis", strtotime($resp->token_valid_until)));
 		}
 		return false;
 	} 	

 	public function testaConexao(){
 		$resp = $this->renovaToken();
 		if ($resp === FALSE) {
 			return false;
 		}
 		return true;
 	}

 	public function getSaleList($args){
 		$defaultArgs = array(
 			'start_date' => date("Y-m-d", strtotime("-1 day ")),
 			'end_date' => date("Y-m-d"),
 			'page' => '1',
 			'date_type' => 'payment'
 		);

 		$args = array_merge($defaultArgs, $args);
 		$args = http_build_query($args);

 		$resp = $this->sendCurl('sale/get_sale_list?'.$args,array(),'GET', false);

 		if ($resp === FALSE) {
 			return false;
 		}

 		return $resp;
 	}

 	public function getSaleByID($saleId){
 		if (empty($saleId)) {
 			return false;
 		}
 		$resp = $this->sendCurl('/sale/get_sale/'.$saleId,array(),'GET');
 		
 		if ($resp === FALSE) {
 			return false;
 		}
		return $resp[0];
 	}


 	public function refundSale($saleId){
 		if (empty($saleId)) { return false; }
 		$sale = $this->getSaleByID($saleId);
 		if ($sale !== false) {
 			$resp = $this->sendCurl('/sale/refund/'.$saleId,array(),'POST');
 			if ($resp!==false) {
 				return true;
 			}
 		}
 		return false;
 	}

 	


 	private function sendCurl($modo = '', $pars = '', $method = 'GET', $debug = false){
 		$url = $this->urlBase.$modo;
 		$args = array(
            'timeout' => 30,
            'body'    => $pars
        );
        if ($modo != 'credential/generate_token') {
 			$args['headers'] = array(
 				'token' => $this->token[0],
				'Publickey' => $this->publickey,
				'APIapikey' => $this->apikey
 			);
 		}
 		switch ($method) {
 			case 'POST':
 				$response = wp_remote_post($url,$args);
 				break;
 			case 'PUT':
 				$args['method'] = 'PUT';
 				$response = wp_remote_post($url,$args);
 				break;
 			case 'GET':
 				$response = wp_remote_get($url, $args);
 				break;
 		}
 		$output = json_decode($response['body']);
 		if ($debug) {
 			var_dump($url);
 			var_dump($args);
 			var_dump($output);
 			exit();
 		}
 		if ($output->success === FALSE) {
 		 	return false;
 		 } 
        return $output->data;

 	}
}
?>