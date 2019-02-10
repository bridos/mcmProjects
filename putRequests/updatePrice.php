<?php
require_once '../mkm_connect/staticConnectionFunctions.php';
require_once '../var.php';

if(!isset($_GET["articleId"])){
	die("No articleId given, dying..");
}

new priceUpdater($_GET["articleId"] , $_GET["price"]);

class priceUpdater{
	private $articleId;
	private $price;
	private $url = "https://api.cardmarket.com/ws/v2.0/stock";
	private $xml = '<?xml version="1.0" encoding="UTF-8" ?> 
		<request>
			<article>
				<idArticle>::articleId::</idArticle>
				<idLanguage>1</idLanguage>
				<comments>Edited through the API</comments>
				<count>1</count>
        		<price>::price::</price>
				<condition>GD</condition>
			</article>
		</request>';
	function __construct($articleId , $price){
		$this->articleId = $articleId;
		$this->price = $price;
		$this->prepareXml();
		$handler = new mkmApiConnectionInfo();
		$handler->getUserCreds();
   		$handler->prepareRequest($this->url , "PUT");
   		$handler->execute($this->xml);
   		$this->updateInDB();
   		//$this->sendReply();

	}
	function updateInDb(){
		$update = "UPDATE mpm_master_db.mkm_accounts SET selling_price = $this->price WHERE article_id = $this->articleId";
		$conn = new connectionManager("master");
		if(!$conn->mysqli->query($update)){
			echo "Died because: " . $conn->mysqli->error;die();
		} else{
			echo json_encode(array("status"=>1));
		}
	}
	function prepareXml(){
		$this->xml = str_replace(
			array("::articleId::" , "::price::") ,
			array($this->articleId , $this->price),
			$this->xml
		);
	}
}