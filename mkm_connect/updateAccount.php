<?php
require_once 'staticConnectionFunctions.php';
require_once '../var.php';
new updateAccount();

class updateAccount{
	
	private $url = "https://api.cardmarket.com/ws/v2.0/stock";
	private $articles2Update = array();
	private $header;

	function __construct($articles = FALSE){
		$this->articles = $articles;
		$handler = new mkmApiConnectionInfo();
   		$handler->getUserCreds();
   		$this->buildPutRequest();
   		$handler->prepareRequest($this->url , "PUT");
   		$handler->execute($this->header);
   		//$handler->prepare($articles , "PUT");
	}

	function buildPutRequest(){
		$this->header = '<?xml version="1.0" encoding="UTF-8" ?> 
		<request>
			<article>
				<idArticle>1096</idArticle>
				<idLanguage>1</idLanguage>
				<comments>Don\'t embarass me now</comments>
				<count>1</count>
        		<price>0.5</price>
				<condition>GD</condition>
			</article>
		</request>';
		// header("Content-type: text/xml");
		// $dom = new DOMDocument();
		// $dom->encoding = "utf-8";
		// $dom->xmlVersion = '1.0';
		// $dom->formatOutput = true;
		// $root = $dom->createElement('request');
		// $articleNode = $dom->createElement('article');
		// //$idAttr = new DOMAttr("articleId" , '12345');
		// //$articleNode = setAttributeNode($idAttr);
		
		// $childNodeId = $dom->createElement("articleId" , '12345');
		// $childNodeCount = $dom->createElement("amount" , '3');
		// $childNodePrice = $dom->createElement("price" , "0.5");
		
		// $articleNode->appendChild($childNodeId);
		// $articleNode->appendChild($childNodeCount);
		// $articleNode->appendChild($childNodePrice);

		// $root->appendChild($articleNode);

		// $dom->appendChild($root);

		// //echo "<pre>";
		// //print_r($dom);
		// //echo "</pre>";

		// //echo "<pre>";
		// //print_r($root);
		// //echo "</pre>";
		// echo $dom->saveXML();
		//var_dump($root);

	}
}


Hello,

I am trying to execute a PUT request with PHP CURL.
I have completed the following steps:

1) i got your example for a simple GET request.
2) I changed the method to PUT
3) I used the following xml
    '<?xml version="1.0" encoding="UTF-8" ?> 
		<request>
			<article>
				<idArticle>1096</idArticle>
				<idLanguage>1</idLanguage>
				<comments>Edited through the API</comments>
				<count>1</count>
        		<price>0.5</price>
				<condition>GD</condition>
        		<isFoil>false</isFoil>
       		 	<isSigned>false</isSigned>
        		<isPlayset>false</isPlayset>
			</article>
		</request>'
4) I added the curl setup options:
       curl_setopt($curlHandle, CURLOPT_CUSTOMREQUEST, "PUT");
       curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $data);

When executing the request, I get a 200 response but the message is: "Du bist nicht autorisiert um diese Aktion auszuführen". Which means I am not authorized.

I do not know what I am doing wrong, is there a put example in the api documentation I am missing?
   