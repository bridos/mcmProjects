var functionsFilePath = 'http://localhost/xampp/personalProjects/price_trend_getter_jquery/new_functions/mkm_connect/checkAccountPrices.php';
//var https = require("https");

function getCards(){
	//var str = "Blah";

	//const https = require('https');
	$.ajax({
		type: "GET",
		url: functionsFilePath+"?userId=1",
		success: function (json){
			var str ="<table><tr><th>mySell</th><th>Trend</th><th>Url</th><th>Foil</th><th>Amount</th><th>Action</th><th></th></tr>";
	     	//var json = JSON.parse(data);
	     	$.each(json , function(i , item){
	     		str += "<tr id="+json[i].articleId+">";
	     		//str += "<td>" + json[i].id + "</td>";
	     		str += "<td class = 'price'>" + json[i].mySell + "</td>";
	     		str += "<td>" + json[i].trend + "</td>";
	     		str += "<td>" + json[i].url + "</td>";
	     		str += "<td>" + json[i].foil + "</td>";
	     		str += "<td>" + json[i].amount + "</td>";
	     		str += "<td><select id ="+json[i].articleId+"sel>" + getSelectFromJson(json[i]) + "</select></td>";
	     		// str += "<td><select id = '" + json[i].articleId + "'<option selected='true'>Ad DB ignore rule</option><option>" +
	     		//  Math.ceil(json[i].mySell*10)/10 + "</option><option>" + Math.floor(json[i].mySell*10)/10 + "</option>" +
	     		//  "<option>" + Math.ceil(json[i].trend*10)/10 + "</option><option>" + Math.floor(json[i].trend*10)/10 +
	     		//   "</option></select>";
	     		str += "<td><button onclick='updatePrice("+json[i].articleId+")'>Go</button></td>";
	     		str += "</tr>";
	     	})
	     	str += "</table>"
     	//	console.log(str);
			$("#cardInfo").html(str);
     	},
		dataType: "json"
	})
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


function updatePrice(articleId){
	price = $('#' + articleId+"sel").find(":selected").text();
	console.log("price: "+price);
	$.ajax({
		type: "GET",
		url: "http://localhost/xampp/personalProjects/price_trend_getter_jquery/new_functions/putRequests/updatePrice.php?articleId="+articleId+"&price="+price,
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