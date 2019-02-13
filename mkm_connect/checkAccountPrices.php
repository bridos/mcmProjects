<?php
require_once '../var.php';
if(isset($_GET['userId'])){
	$userId = $_GET['userId'];
} else{
	die("Not a proper userId<br>");
}
//echo "Called!\n";
new checkAccountPrices($userId);

class checkAccountPrices{
	private $cards = array();
	private $card2Check = array();
	private $userId;
	private $thresholds = array( 
		'1' => '50' ,
		'10' => '20' ,
		'50' => '10');
	// private $thresholds = array( 
	// 	'1' => '0' ,
	// 	'10' => '0' ,
	// 	'50' => '0');

	function __construct($userId){
		isset($userId) ? $this->userId = $userId : 1;
		$this->getAccountItems($this->userId);
		$this->checkPriceDiff();
		$this->returnUrgentItems();
	}

	function getAccountItems($id = 1){
		$conn = new connectionManager("master");
		$query = "SELECT 
				    product_id,
				    article_id,
				    selling_price,
				    price_trend,
				    real_url,
				    foil,
				    amount,
				    conditions.abbreviation cardcondition,
				    conditions.id condId,
				    languages.abbreviation language,
				    languages.id langId,
				    replace(substring_index(real_url,'/',-1),'-',' ') cardname
				FROM
				    mkm_accounts
				        INNER JOIN
				    languages ON languages.id = mkm_accounts.language
				        INNER JOIN
				    conditions ON conditions.id = mkm_accounts.cardcondition
				WHERE
				    user_id = 1";
		//die($query);
		$response = $conn->mysqli->query($query);
		if(!$response){
			echo "No select: " . $conn->mysqli->error . "<br>";
		} else{
			while($result = $response->fetch_object()){
				$this->cards[] = (object)[
					"articleId" => "$result->article_id",
					"productId" => "$result->product_id",
					"mySell" => "$result->selling_price",
					"trend" => "$result->price_trend",
					"url" => "$result->real_url",
					"foil" => "$result->foil",
					"amount" => "$result->amount",
					"language" => "$result->language",
					"condition" => "$result->cardcondition",
					"cardname" => "$result->cardname",
					"langid" => "$result->langId",
					"condid" => "$result->condId"];
			}
			//print_r($this->cards);
		}
	}

	function checkPriceDiff(){
		foreach($this->cards as $card){
			$margin = $this->getMargin($card->mySell);
			if($card->mySell > ($card->trend + ($card->trend * $margin / 100) ) || $card->mySell < ($card->trend - ($card->trend * $margin / 100) )){
				//echo "Price is $card->mySell so Margin is: $margin<br>";
				//echo "Check: $card->url you sell at: $card->mySell and trend is: $card->trend<br>";
				$this->cards2Check[] = $card;
			} else{
				//echo "Card: $card->url within limits sell: $card->mySell , and trend: $card->trend<br>";
			}
		}
	}

	function returnUrgentItems(){
		$json = json_encode($this->cards2Check);
		echo $json;
	}

	function getMargin($var){
		reset($this->thresholds);
		$target = key($this->thresholds);

		foreach($this->thresholds as $key => $percent){
			if($var > $key){
				//echo "$var bigger than $key looking for the next<br>";
				continue;
			} else{
				//echo "$var under key $key  so returning last key!<br>";
				$target = $key;
				break;
			}
		}
		//echo "price is $var so the threshhold is: " . $this->thresholds[$target] . "%<br>";
		return $this->thresholds[$target];
	}
}
