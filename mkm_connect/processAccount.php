<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes

//require_once 'staticConnectionFunctions.php';
require_once '../var.php';
require_once 'crawlSingleMKMCard.php';
require_once 'crawlMultipleMKMCards.php';
new processAccountItems();
exit("Account Up to Date");
class processAccountItems{

	private $csvArticlesFile = "myarticles.csv";
	private $articleUrl = "https://api.cardmarket.com/ws/v2.0/output.json/products/";
	private $taskThreshold = 100;
	private $articles = array();
	private $tasks = array();
	private $results = array();
	private $accountItems = array();
	private $userId = 1;
	private $conditions = array();
	function __construct(){
		$this->getConditions();
		$this->load();
		$this->getPreviousItems();
		$this->diff();
		$this->getCurrentArticleInfo();
	}
	function getPreviousItems(){
		$select = "SELECT * FROM mkm_accounts WHERE user_id = $this->userId";
		$conn = new connectionManager("master");
		$response = $conn->mysqli->query($select);
		if(!$response){
			echo "Failed " . $conn->mysqli->error . "<br>";
		} else{
			while($result = $response->fetch_assoc()){
				$this->accountItems[$result["article_id"]] = array(
					"product_id" => $result["product_id"],
					"selling_price" => $result["selling_price"],
					"trend" => $result["price_trend"]
				); 
			}
			
		}
		$conn->disconnect();
	}
	function getConditions(){
		$select = "SELECT id , abbreviation FROM conditions";
		$conn = new connectionManager("master");
		$response = $conn->mysqli->query($select);
		if(!$response){
			echo "Failed " . $conn->mysqli->error . "<br>";
		} else{
			while($result = $response->fetch_object()){
				$this->conditions[$result->abbreviation] = $result->id;
			}
		}
	}
	function diff(){
		$new = array_diff_key($this->articles , $this->accountItems);
		$toRemove = array_diff_key($this->accountItems , $this->articles);
		// "Articles(from file)<br>";
		// echo "<pre>";
		// print_r($this->articles);
		// echo "</pre>";
		// "AccountItems(from db)<br>";
		// echo "<pre>";
		// print_r($this->accountItems);
		// echo "</pre>";
		echo "I have new: " . count($new) . "<br>";
		echo "<pre>";
		print_r($new);
		echo "</pre>";
		echo "I must remove: " . count($toRemove);
		//die();
		$conn = new connectionManager("master");
		foreach($toRemove as $removeItem){
			$query = "DELETE FROM mkm_accounts WHERE article_id = " . $removeItem["article_id"] . " AND user_id = $this->userId";
			
			if(!$conn->mysqli->query($query)){
				echo "Couldn't delete Record because: ".  $conn->mysqli->error . "<br>";die();
			}
			unset($this->articles[$removeItem["article_id"]]);	
		}
		$conn->disconnect();
		//echo count($this->articles);
		//print_r($toRemove);
	}

	function load(){
		$file = fopen($this->csvArticlesFile, 'r');
		while (($line = fgetcsv($file,0,';')) !== FALSE) {
		  //print_r($line);
		  
		  if($line[0] == "idArticle"){
		  	continue;
		  }
		  $this->articles[$line[0]] = array(
		  	"articleId" => $line[0],
		  	"productId" => $line[1],
		  	"price" => $line[6],
		  	"foil" => $line[9] == "X" ? 1 : 0,
		  	"amount" => $line[14],
		  	"condition" => $this->conditions[$line[8]],
		  	"url" => $this->articleUrl . $line[1]
		  );
		}
		fclose($file);
	}

	function getCurrentArticleInfo(){
		echo "I have to crawl: " . count($this->articles) . "<br>";
		$this->determineTasks();
		echo "<pre>";
		//print_r($this->tasks);
		echo "</pre>";
		//die;
		$i = 0;
		foreach($this->tasks as &$task){
			
			$priceJob = new crawlMultipleMKMCards($task);
			//foreach($task as &$article){
				//$priceJob = new crawlSingleMKMCard($this->articleUrl . $article["id"]);
				//$article["trend"] = $priceJob->price;
				//delete($priceJob);
			//}
			$i++;
			echo "Task " . $i . " completed!<br>"; 
			echo "<pre>";
			//print_r($task);
			echo "</pre>";
			$this->storeInDB($task);
		}
		echo "<pre>";
		//print_r($this->articles);
		echo "</pre>";
	}
	function determineTasks(){
		$i = 0;
		foreach($this->articles as $article){
			$this->tasks[$i % $this->taskThreshold][] = $article;
			$i++;
		}
	}
	function storeInDB($arr){
		$conn = new connectionManager("master");
		foreach($arr as $entry){
			$real_url = $conn->mysqli->real_escape_string($entry["real_url"]);
			$foil = $entry["foil"];
			$amount = $entry["amount"];
			$productId = $entry["productId"];
			$articleId = $entry["articleId"];
			$price =$entry["price"];
			$trend = $entry["trend"];
			$condition = $entry["condition"];
			$update = "INSERT INTO mkm_accounts (user_id , article_id , product_id , selling_price , price_trend , amount , foil , real_url , last_crawled , cardcondition) 
			VALUES($this->userId , $articleId , $productId , $price , $trend , $amount , $foil , '$real_url' , NOW() ,$condition) 
			ON DUPLICATE KEY UPDATE selling_price = $price , price_trend = $trend , amount = $amount , last_crawled = NOW() , real_url = '$real_url' , cardcondition = $condition";
			//echo "$update<br><br>";
			 if(!$conn->mysqli->query($update)){
			 	echo "Failed because: " . $conn->mysqli->error . "<br>";die();
			 }
		}
		$conn->disconnect();
	}
}