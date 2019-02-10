<?php

class crawlingFunctions{
	public $price;
	function __construct($str , $selector , $sid){
		$mode = $selector[0];
		$action = $selector[1];
		switch($mode){
			case "function":
				$this->$action($str);
			break;
			case "custom":
			break;
			default:
			break;
		}
	}
	function mkmfunction($result){
		$ptrend="Price Trend";
		$s_pos=strpos($result,$ptrend);
		$rest= substr($result,$s_pos);

		$s_pos=strpos($rest,'">');
		$rest=substr($rest,$s_pos);

		$e_pos=strpos($rest,'&');
		$rest=substr($rest,0,$e_pos);
		$rest=substr($rest,2);
		//echo $rest;
		//die("restis: ".$rest);
		$rest = str_replace("<span>","",$rest);
		//file_put_contents($file,$rest);

		$this->price = $rest;
	}
}