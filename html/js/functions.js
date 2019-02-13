var functionsFilePath = 'http://localhost/xampp/personalProjects/price_trend_getter_jquery/new_functions/mkm_connect/checkAccountPrices.php';
//var https = require("https");

function getCards(){
	//var str = "Blah";

	//const https = require('https');
	$.ajax({
		type: "GET",
		url: functionsFilePath+"?userId=1",
		success: function (json){
			//var str ="<table><tr><th>mySell</th><th>Trend</th><th>Details</th><th>Action</th><th></th><th>Url</th></tr>";
			var str ="<table><tr><th>mySell</th><th>Trend</th><th>Foil</th><th>Condition</th><th>Language</th><th>Amount</th>";
			str+= "<th>Suggestions</th><th></th><th></th><th>Name</th></tr>";
	     	//var json = JSON.parse(data);
	     	$.each(json , function(i , item){
	     		str += "<tr id="+json[i].articleId+">";
	     		//str += "<td>" + json[i].id + "</td>";
	     		str += "<td class = 'price'>" + json[i].mySell + "</td>";
	     		str += "<td>" + json[i].trend + "</td>";
	     		//str += "<td>" + getDetails(json[i].foil , json[i].condition , json[i].language) + "</td>";
	     		str += "<td>" + json[i].foil + "</td>";
	     		str += "<td code = "+json[i].condid+">" + json[i].condition + "</td>";
	     		str += "<td code = "+json[i].langid+">" + json[i].language + "</td>";
	     		//str += "<td>" + json[i].foil + "</td>";
	     		str += "<td>" + json[i].amount + "</td>";
	     		str += "<td><select id ="+json[i].articleId+"sel>" + getSelectFromJson(json[i]) + "</select></td>";
	     		// str += "<td><select id = '" + json[i].articleId + "'<option selected='true'>Ad DB ignore rule</option><option>" +
	     		//  Math.ceil(json[i].mySell*10)/10 + "</option><option>" + Math.floor(json[i].mySell*10)/10 + "</option>" +
	     		//  "<option>" + Math.ceil(json[i].trend*10)/10 + "</option><option>" + Math.floor(json[i].trend*10)/10 +
	     		//   "</option></select>";
	     		condvar = '"'+json[i].condition+'"';
	     		str += "<td><button onclick='updatePrice("+json[i].articleId+","+condvar+","+json[i].langid+","+json[i].foil+")'>Update</button></td>";
	     		str += "<td><a href="+json[i].url+" target='_blank'>Visit</td>";
	     		str += "<td>"+json[i].cardname+"</td>";
	     		str += "</tr>";
	     	})
	     	str += "</table>"
     	//	console.log(str);
			$("#cardInfo").html(str);
     	},
		dataType: "json"
	})
}
function getDetails(foil , cond , lang){
	var str = "";
	str += "foil:"+foil+" condition:"+cond+" language:"+lang;
	return str;
}
function getSelectFromJson(json){
	ret = "";
	prices = [Math.ceil(json.mySell*10)/10 ,
	 	Math.floor(json.mySell*10)/10 ,
	  	Math.ceil(json.trend*10)/10,
	    Math.floor(json.trend*10)/10,
	    json.trend];

	var unique = prices.filter( onlyUnique );
	
	$.each(unique , function(i , item){
		if(item == json.mySell){
			return;
		}
		ret += "<option>" + item + "</option>";
	});
	return ret;
}
function onlyUnique(value, index, self) { 
    return self.indexOf(value) === index;
}


function updatePrice(articleId , condition , language, foil){
	price = $('#' + articleId+"sel").find(":selected").text();
	//console.log("cond: "+condition +" lang: "+language);
	//return;
	//console.log("price: "+price);
	if(foil == 1){
		foil = true;
	} else{
		foil = false;
	}
	$.ajax({
		type: "GET",
		url: "http://localhost/xampp/personalProjects/price_trend_getter_jquery/new_functions/putRequests/updatePrice.php?"+
		"articleId="+articleId+"&price="+price+"&condition="+condition+"&language="+language+"&foil="+foil,
		success: function(json){
			console.log("in succ");
			console.log(json);
			//var obj = $.parseJSON(json);
			if(json.status == 1){
				//console.log("in status");
				//console.log($("#262695518 .price").html());
				$("#"+articleId+ " .price").html(price);
				console.log("done updating");
			}
		},
		dataType: "json"
	})
}