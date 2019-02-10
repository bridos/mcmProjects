<?php
require_once '../var.php';
require_once 'staticConnectionFunctions.php';
new set2Cards(1822); 

class set2Cards{
	private $targetSet;
   	private $respJson;

   	function __construct($setId){
   		if(!isset($setId)){
   			die("No legal set provided");
   		}
   		$this->targetSet = trim($setId);
   		$handler = new mkmApiConnectionInfo();
   		$handler->getUserCreds();
   		$handler->prepareRequest("https://api.cardmarket.com/ws/v2.0/output.json/expansions/". $this->targetSet ."/singles");
   		$this->respJson = $handler->execute();
   		$this->saveResults();

   	}

	function saveResults(){
		echo "<pre>";
		//print_r($this->respJson);
		echo"</pre>";


		foreach($this->respJson->single as $card){
			if($card->rarity== "Token"){
				continue;
			}
			$cards[$card->idProduct] = array(
				"name" => $card->enName,
				"url" => $card->website
			);
		}
		echo "<pre>";
		//print_r($cards);
		echo"</pre>";

		//CHECK IF THE TABLE ALREADY EXISTS
		$dbTableName = strtolower(str_replace(" " , "_" , $this->respJson->expansion->enName) . "_" . $this->targetSet);
		$conn = new connectionManager("sets");
		//echo $dbTableName . "<br>";
		$query = "SHOW TABLES LIKE '" . $dbTableName . "'";
		$existingCards = array();
		//echo $query . "<br>";
		//die($query);
		if ($result = $conn->mysqli->query($query)) {
		    if($result->num_rows == 1) {
		        echo "Table exists <br>";
		        //TODO GET CARDS ALREADY IN DB AND STORE THE NEW ONES
		        //
		        //
		        $select = "SELECT mkm_id FROM " . $dbTableName;
		        //echo $select;
		        $existingCards = array();
		        if($response2 = $conn->mysqli->query($select)){
					while($result2 = $response2->fetch_assoc()){
						$existingCards[$result2["mkm_id"]] = $result2["mkm_id"];
					}
				}
			} else {
			    echo "Table does not exist <br>";
			    $createQuery = "
			    CREATE TABLE `magic_sets`.`" . $conn->mysqli->real_escape_string($dbTableName) . "` (
				  `mkm_id` INT NOT NULL,
				  `mkm_url` VARCHAR(128) NULL,
				  `card_name` VARCHAR(45) NULL,
				  `api_url` VARCHAR(64) NULL,
				  PRIMARY KEY (`mkm_id`));";
				  echo $createQuery;
				if(!$conn->mysqli->query($createQuery)){
					echo "Problem Create<br>".$conn->mysqli->error."<br>";
				} else{
					echo "Table: " . $dbTableName . " was created!<br>";
				}
			}
		}
		else {
			echo "NO QUERY!<br>";
		}

		//INSERT ALL NEW CARDS TO THE TABLE
		$toInsert = array_diff_key($cards, $existingCards);
		echo "I have found: " . count($toInsert) . " new Cards<br>";
		if(count($toInsert) >0){
			$prepare = $conn->mysqli->prepare("INSERT INTO " . $dbTableName . "(mkm_id , mkm_url , card_name, api_url) VALUES(? , ? , ? , ?)");
			$prepare->bind_param("isss" , $id, $url , $name , $api_url);
			foreach($toInsert as $id => $values){
				$url = "https://www.cardmarket.com" . $values["url"];
				$name = $values["name"];
				$api_url = $conn->mysqli->real_escape_string("https://api.cardmarket.com/ws/v2.0/output.json/products/" . $id);
				$prepare->execute();
			}	
		}
		
		$conn->disconnect();
	}

}