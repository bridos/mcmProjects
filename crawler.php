<?php
require_once "var.php";
require_once "../simple_html_dom.php";
require_once "mkm_connect/crawlSingleMKMCard.php";

new crawler();

class crawler{
	//private $mysqli;
	private $tasks = array();
	function __construct(){

		$this->getTasks();
		$this->crawl();
		$this->store();
	}
	function getTasks(){
		//$this->connect();
		$conn = new connectionManager("master");
		$query = "SELECT 
				    tracks.id id, url, sites.id sid, sites.selector
				FROM
				    tracks
				        INNER JOIN
				    sites ON tracks.site_id = sites.id";
				//WHERE
    			//	last_crawled NOT BETWEEN NOW() - INTERVAL 1 DAY AND NOW()";
		$response = $conn->mysqli->query($query);
		if(!$response){
			echo "Failed " . $conn->mysqli->error . "<br>";
		} else{
			while($result = $response->fetch_assoc()){
				$this->tasks[] = array(
					"id" => $result["id"],
					"url" => $result["url"],
					"sid" => $result["sid"],
					"selector" => explode(":::" , $result["selector"])
				); 
			}
			
		}
		$conn->disconnect();
	}

	function crawl(){
		$this->prettyPrint($this->tasks);
		//TODO 
		//add individual site crawling options!!!!
		foreach($this->tasks as &$job){
			$priceJob = new crawlSingleMKMCard($job["url"]);
			$job["price"] = $priceJob->price;
		}
		//foreach($result as $result)	
		$this->prettyPrint($this->tasks);	
	}

	function store(){
		
		$this->processPrices();
		//$this->connect();
		$conn = new connectionManager("master");
		foreach($this->tasks as $obj){
			if($obj["price"] > 0){
				$price = $obj["price"];
				$id = $obj["id"];
				$query = "UPDATE tracks set current_price = " . $price . " , last_crawled = NOW() WHERE tracks.id = " . $id;
				if(!$conn->mysqli->query($query)){
					echo "Problem for: ";
					$this->prettyPrint($obj);
					echo $conn->mysqli->error . "<br>";
				} else{
					echo "Save successfull!!!!<br>";
					$query = "INSERT INTO prices (track_id,price,date) VALUES (" . $id . "," . $price . ",NOW())";
					if(!$conn->mysqli->query($query)){
						echo "Prices was NOT updates<br>";
					} else{
						echo "Prices was UPDATED<br>";
					}
				}
			} else{
				echo "Nothing is stored something went wrong<br>";
			}
		}
		$conn->disconnect();
	}

	function get_html($addr , $selector, $sid){
		$ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.81 Safari/537.36';
		$ch = curl_init($addr);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch , CURLOPT_TIMEOUT , 60);
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		if(curl_exec($ch) === false){
			echo 'Curl error: ' . curl_error($ch);
			return;
		}else{
			echo "No error\n";
			$result=curl_exec($ch);
			curl_close($ch);
			return $this->processResult($result , $selector , $sid);
		}
		
	}
	function processResult($page , $selector , $sid){
		
		$pr = new crawlingFunctions($page , $selector , $sid);
		return $pr->price;
	}
	// function connect(){
	// 	$dbVars = new connectionVars();
	// 	$this->mysqli = new mysqli($dbVars->ip , $dbVars->userName , $dbVars->pass , $dbVars->dbName);
	// 	if(!$this->mysqli){
	// 		echo "Error <br>";
	// 	} else{
	// 		//echo "Connected<br>";
	// 	}
	// }
	// function disconnect(){
	// 	$this->mysqli->close();
	// }
	function prettyPrint($arr){
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}
	function processPrices(){
		//$this->connect();
		$conn = new connectionManager("master");
		foreach($this->tasks as &$obj){
			echo "Price is: " . $obj["price"] . "<br>";
			//if(is_numeric($obj["price"])){
					$obj["price"] = str_replace("," , "." , $obj["price"]);
					$conn->mysqli->real_escape_string($obj["price"]);
			//} else{
			//	echo "Problem with number unsetting...<br>";
			//	unset($obj["price"]);
			//}
		}
		$conn->disconnect();
	}
}