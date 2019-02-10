<?php
//require_once '../var.php';
require_once 'staticConnectionFunctions.php';
//new crawlSingleMKMCard(364128);

class crawlSingleMKMCard{
	private $targetCard;
	private $respJson;
	private $task = array();
	public $price;

	function __construct($url){
   		if(!isset($url)){
   			die("No legal Card provided");
   		}
   		$this->targetCard = $url;
   		$handler = new mkmApiConnectionInfo();
   		$handler->getUserCreds();
   		$handler->prepareRequest($this->targetCard);
   		$this->respJson = $handler->execute();
   		$this->saveResults();

   	}
   	function saveResults(){
  //  		echo "<pre>";
		// print_r($this->respJson);
		// echo"</pre>";
		$this->price = $this->respJson->product->priceGuide->TREND;
      $this->url = "https://www.cardmarket.com" . $this->respJson->product->website;
		//echo $this->price;
		//die();
   	}
}