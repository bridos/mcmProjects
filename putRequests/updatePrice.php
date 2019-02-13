<?php
require_once '../mkm_connect/staticConnectionFunctions.php';
require_once '../var.php';

if(!isset($_GET["articleId"])){
	die("No articleId given, dying..");
}
if(!isset($_GET["price"])){
	die("Can't update a price without a price, dying..");
}
$params = array();
// if(isset($_GET["language"])){
// 	$params[] = array("language" => )
//}
$params["language"] = isset($_GET["language"]) ? $_GET["language"] : 1; 
$params["foil"] =  isset($_GET["foil"]) ? $_GET["foil"] : false; 
$params["condition"] =  isset($_GET["condition"]) ? $_GET["condition"] : 6;
$params["amount"] = isset($_GET["amount"]) ? $_GET["amount"] : 1;
//print_r($params);
//die();
new priceUpdater($_GET["articleId"] , $_GET["price"] , $params);

class priceUpdater{
	private $articleId;
	private $price;
	private $url = "https://api.cardmarket.com/ws/v2.0/stock";
	/*
	private $xml = '<?xml version="1.0" encoding="UTF-8" ?> 
		<request>
			<article>
				<idArticle>::ARTICLEID::</idArticle>
				<idLanguage>::LANGUAGE::</idLanguage>
				<comments>Don\'t fail me now</comments>
				<count>::AMOUNT::</count>
        		<price>::PRICE::</price>
        		<isFoil>::FOIL::</isFoil>
				<condition>::CONDITION::</condition>
			</article>
		</request>';
		*/
	private $xml = "";
	function __construct($articleId , $price , $params){
		$this->articleId = $articleId;
		$this->price = $price;
		$this->params = $params;
		$this->prepareXml();
		$handler = new mkmApiConnectionInfo();
		$handler->getUserCreds();
   		$handler->prepareRequest($this->url , "PUT");
   		//die($this->xml);
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
		// $this->xml = str_replace(
		// 	array("::ARTICLEID::" , "::PRICE::" , "::CONDITION::" , "::LANGUAGE::" , "::AMOUNT::" , "::FOIL::") ,
		// 	array($this->articleId , $this->price , $this->params["condition"] , $this->params["language"] , $this->params["amount"] , $this->params["foil"]),
		// 	$this->xml
		// );
		
		$this->xml = '<?xml version="1.0" encoding="UTF-8" ?> 
		<request>
			<article>
				<idArticle>'.$this->articleId.'</idArticle>
				<price>'.$this->price.'</price>';

		if(isset($this->params['condition'])){
			$this->xml .= "<condition>".$this->params['condition']."</condition>";
		}
		if(isset($this->params['language'])){
			$this->xml .= "<idLanguage>".$this->params['language']."</idLanguage>";
		}
		if(isset($this->params['amount'])){
			$this->xml .= "<count>".$this->params['amount']."</count>";
		}
		if(isset($this->params['foil'])){
			if($this->params['foil'] === true){
				echo "in!<br>";
				$this->xml .= "<isFoil>".$this->params['foil']."</isFoil>";
			} else{
				//echo "foil param false<br>";
			}
		}
		$this->xml .= 
			"</article>
		</request>";
	}
}