<?php
class connectionManager{
	
	public $mysqli;

	function __construct($targetDB){
		switch($targetDB){
			case "master":
				$this->connectMaster();
			break;
			case "sets":
				$this->connectSets();
			break;
			case "thesis":
				$this->connectThesis();
			break;
			default:
				die("Not a correct DB<br>");
			break;
		}
		
	}

	function connectMaster(){
		$dbVars = new connectionMasterVars();
		$this->mysqli = new mysqli($dbVars->ip , $dbVars->userName , $dbVars->pass , $dbVars->dbName);
		if(!$this->mysqli){
			echo "Error <br>";
			die();
		} else{
			//echo "Connected<br>";
			$this->mysqli->query("SET NAMES 'utf8'");
			$this->mysqli->query("SET CHARACTER SET 'utf8'");
		}
	}
    
    function connectSets(){
		$dbVars = new connectionSetsVars();
		$this->mysqli = new mysqli($dbVars->ip , $dbVars->userName , $dbVars->pass , $dbVars->dbName);
		if(!$this->mysqli){
			echo "Error <br>";
			die();
		} else{
			$this->mysqli->query("SET NAMES 'utf8'");
			$this->mysqli->query("SET CHARACTER SET 'utf8'");
			//echo "Connected<br>";
		}
	}

    function connectThesis(){
    	$dbVars = new connectionThesisVars();
    	$this->mysqli = new mysqli($dbVars->ip , $dbVars->userName , $dbVars->pass , $dbVars->dbName);
    	if(!$this->mysqli){
    		echo "Error<br>";
    		die();
    	} else{
			$this->mysqli->query("SET NAMES 'utf8'");
			$this->mysqli->query("SET CHARACTER SET 'utf8'");
    	}
    }
	function disconnect(){
		$this->mysqli->close();
	}		
}

class connectionMasterVars{
	
	public $userName = "root";
	public $pass = "";
	public $ip = "localhost";
	public $dbName = "mpm_master_db";

	
	
}

class connectionSetsVars{
	public $userName = "root";
	public $pass = "";
	public $ip = "localhost";
	public $dbName = "magic_sets";
}

class connectionThesisVars{
	public $userName = "root";
	public $pass = "";
	public $ip = "localhost";
	public $dbName = "thesis_db";
}