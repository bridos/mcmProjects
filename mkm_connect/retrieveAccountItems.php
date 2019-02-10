<?php
require_once 'staticConnectionFunctions.php';
require_once '../var.php';
new retrieveAccountItems("https://api.cardmarket.com/ws/v2.0/output.json/stock/file");

class retrieveAccountItems{
	private $url;
	private $gzArticlesFile = "myArticles.gz";
	private $csvArticlesFile = "myarticles.csv";
	private $respJson;
	private $articles = array();

	function __construct($url){
		if(!isset($url)){
   			die("No legal Url provided");
   		}
   		$this->url = $url;
   		$handler = new mkmApiConnectionInfo();
   		$handler->getUserCreds();
   		$handler->prepareRequest($this->url);
   		echo "Getting File<br>";
   		$this->respJson = $handler->execute();
   		echo "Unzipping And Storing<br>";
   		$this->handleResults();
   		echo "Finished<br>";
	}

	function handleResults(){
		$this->store();
		$this->decompress();
		//$this->load();
		// echo "Results: <pre>";
		// print_r( $this->articles);
		// echo "</pre>";
	}
	function store(){
		$data = base64_decode($this->respJson->stock);
		if(file_exists($this->gzArticlesFile)){
			unlink($this->gzArticlesFile);
		}
		if(file_exists($this->csvArticlesFile)){
			unlink($this->csvArticlesFile);
		}
		//$this->gzCompressFile($this->articlesFile, $data);
		file_put_contents($this->gzArticlesFile, $data);
	}

	function decompress(){
		//This input should be from somewhere else, hard-coded in this example
		$file_name = $this->gzArticlesFile;

		// Raising this value may increase performance
		$buffer_size = 4096; // read 4kb at a time
		$out_file_name = $this->csvArticlesFile;

		// Open our files (in binary mode)
		$file = gzopen($file_name, 'rb');
		$out_file = fopen($out_file_name, 'wb'); 

		// Keep repeating until the end of the input file
		while (!gzeof($file)) {
		    // Read buffer-size bytes
		    // Both fwrite and gzread and binary-safe
		    fwrite($out_file, gzread($file, $buffer_size));
		}

		// Files are done, close files
		fclose($out_file);
		gzclose($file);
	}

	// function load(){
	// 	$file = fopen($this->csvArticlesFile, 'r');
	// 	while (($line = fgetcsv($file,0,';')) !== FALSE) {
	// 	  //print_r($line);
		  
	// 	  if($line[0] == "idArticle"){
	// 	  	continue;
	// 	  }
	// 	  $this->articles[] = array(
	// 	  	"id" => $line[1],
	// 	  	"price" => $line[6],
	// 	  	//"foil" => $this->oneZeroFoil($line[9]),
	// 	  	"foil" => $line[9] == "X" ? 1 : 0,
	// 	  	"ammount" => $line[14]
	// 	  );
	// 	}
	// 	fclose($file);
	// }
	
}