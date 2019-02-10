<?php
require_once 'staticConnectionFunctions.php';


class crawlMultipleMKMCards{
	private $urls = array();
	function __construct(&$arr){
		if(!is_array($arr)){
			die("No array For Multi Hit Provided<b>");
		}
		print_r($arr);
		$this->urls = array_column($arr , "url");
		$handler = new mkmApiConnectionInfo();
   		$handler->getUserCreds();
   		$handler->multiPrepare($this->urls);
   		$handler->multiExecute();
   		$values = $handler->responseArray;
   		$this->saveResults($arr , $values);
	}
	function saveResults(&$arr , $values){
		$i = 0;
		foreach($arr as &$task){
			$task["trend"] = $values[$i]->product->priceGuide->TREND;
			$task["real_url"] = "https://www.cardmarket.com" . $values[$i]->product->website;
			$i++;
		}
		//print_r($arr);die("i died");
	}
}