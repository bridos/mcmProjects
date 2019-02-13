<?php

class mkmApiConnectionInfo{
	//private $method             = "GET";
	private $url;
    private $appToken;
    private $appSecret;
    private $accessToken;
    private $accessSecret;
    private $nonce              = "53eb1f44909d6";
    private $timestamp          = "1407917892";
    private $signatureMethod    = "HMAC-SHA1";
   	private $version            = "1.0";
   	private $baseString;
   	private $headers = array();
   	private $multiUrlArray = array();
   	private $curlArray = array();
   	public $responseArray = array();

	function getUserCreds(){
	   		$query = "SELECT * FROM users WHERE id = 1";
	   		$conn = new connectionManager("master");
	   		if($response = $conn->mysqli->query($query)){
				while($result = $response->fetch_assoc()){
					
					$this->appToken = $result["appToken"];
					$this->appSecret = $result["appSecret"];
					$this->accessToken = $result["accessToken"];
					$this->accessSecret = $result["accessSecret"];;
				}	
			} else{
				 echo "Failed " . $this->mysqli->error . "<br>";
				 die();
			}
			$conn->disconnect();	
		}

	function prepareRequest($url , $method = "GET"){
		$this->url = $url;
		$this->method = $method;
		$params             = array(
	       'realm'                     => $this->url,
	       'oauth_consumer_key'        => $this->appToken,
	       'oauth_token'               => $this->accessToken,
	       'oauth_nonce'               => $this->nonce,
	       'oauth_timestamp'           => $this->timestamp,
	       'oauth_signature_method'    => $this->signatureMethod,
	       'oauth_version'             => $this->version
        );

		$baseString         = strtoupper($this->method) . "&";
   		$baseString        .= rawurlencode($this->url) . "&";
    	$encodedParams      = array();
	   foreach ($params as $key => $value)
	   {
	       if ("realm" != $key)
	       {
	           $encodedParams[rawurlencode($key)] = rawurlencode($value);
	       }
	   }
	   ksort($encodedParams);
	   $values             = array();
	   foreach ($encodedParams as $key => $value)
	   {
	       $values[] = $key . "=" . $value;
	   }
	   $paramsString       = rawurlencode(implode("&", $values));
	   $baseString        .= $paramsString;

	    $signatureKey       = rawurlencode($this->appSecret) . "&" . rawurlencode($this->accessSecret);
	   
	   /**
	    * Create the OAuth signature
	    * Attention: Make sure to provide the binary data to the Base64 encoder
	    *
	    * @var $oAuthSignature string OAuth signature value
	    */
	   $rawSignature       = hash_hmac("sha1", $baseString, $signatureKey, true);
	   $oAuthSignature     = base64_encode($rawSignature);
	   
	   /*
	    * Include the OAuth signature parameter in the header parameters array
	    */
	   $params['oauth_signature'] = $oAuthSignature;
	   
	   /*
	    * Construct the header string
	    */
	   $header             = "Authorization: OAuth ";
	   
	   $headerParams       = array();
	   foreach ($params as $key => $value)
	   {
	       $headerParams[] = $key . "=\"" . $value . "\"";
	   }
	   $header            .= implode(", ", $headerParams);	
	   
	   //die($header);
	   $this->header = $header;
	   $this->baseString = $baseString;
	}
	

	function execute($data = FALSE){
		$curlHandle         = curl_init();
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curlHandle, CURLOPT_URL, $this->url);
	    curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array($this->header));
	    //print_r($this->header);
	    curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
	    if($this->method == "PUT"){
	   		//echo "in put<br>";
	   		curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "PUT");
	   		//curl_setopt($curlHandle, CURLOPT_POSTFIELDS, http_build_query($data));
	   		//curl_setopt($curlHandle, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
	   		curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);	
	    }
	    //print_r($this->header);
	    //print_r($data);
	   /**
	    * Execute the request, retrieve information about the request and response, and close the connection
	    *
	    * @var $content string Response to the request
	    * @var $info array Array with information about the last request on the $curlHandle
	    */
	   $content            = curl_exec($curlHandle);
	   
	   
	   
	   /*
	    * Convert the response string into an object
	    *
	    * If you have chosen XML as response format (which is standard) use simplexml_load_string
	    * If you have chosen JSON as response format use json_decode
	    *
	    * @var $decoded \SimpleXMLElement|\stdClass Converted Object (XML|JSON)
	    */
	   // $decoded            = json_decode($content);
	   // echo "<pre>";
	    //print_r($content);
	   // echo "</pre>";
	   $decoded            = json_decode($content);
	   $info               = curl_getinfo($curlHandle);
	   curl_close($curlHandle);
	   if($info["http_code"] == 200){
	  		$this->respJson = $decoded;
	  	// 	echo "status: <pre>";
	   // print_r($info);
	   // echo "</pre>";
	   // echo "resp: <pre>";
	   // print_r($decoded);
	   // echo "</pre>";
	   		return $this->respJson;
	   } else{
	   	die("error in resp<br>");
	   }
	   // echo "status: <pre>";
	   // print_r($info);
	   // echo "</pre>";
	   // echo "resp: <pre>";
	   // print_r($decoded);
	   // echo "</pre>";
	   
	   die();
	   
	}

	function multiPrepare($arr , $method = "GET"){
		//print_r($arr);
		$this->method = $method;
		foreach($arr as $url){
			$this->prepareRequest($url , $this->method);
			$this->multiUrlArray[] = array(
				"url" => $this->url , 
				"header"=>$this->header ,
				"baseString" => $this->baseString);
		}
	}
	function multiExecute(){
		$total = count($this->multiUrlArray);
		for($i = 0 ; $i < $total ; $i++){
			$this->curlArray[$i] = curl_init();
			curl_setopt($this->curlArray[$i], CURLOPT_RETURNTRANSFER, true);
	    	curl_setopt($this->curlArray[$i], CURLOPT_HTTPHEADER, array($this->multiUrlArray[$i]["header"]));
	    	curl_setopt($this->curlArray[$i], CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curlArray[$i] , CURLOPT_URL, $this->multiUrlArray[$i]["url"]);
		}
		$multiHandle = curl_multi_init();

		foreach($this->curlArray as $curlItem){
			curl_multi_add_handle($multiHandle , $curlItem);
		}

		$running = null;
		do {
    		curl_multi_exec($multiHandle , $running);
		} while($running > 0);
			
		foreach($this->curlArray as $id => $c) {
    		$this->responseArray[$id] = json_decode(curl_multi_getcontent($c));
    		curl_multi_remove_handle($multiHandle, $c);
  		}
  		curl_multi_close($multiHandle);
		//echo "<pre>";
		//print_r($result);
		//echo "</pre>";
		//die();
	}
}