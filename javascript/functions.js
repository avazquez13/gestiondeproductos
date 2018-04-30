/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
function GET_XML_HTTP() {
	var _fo;
	try {
		_fo=new XMLHttpRequest(); 
	} catch(e) {
		try {
 			_fo=new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				_fo=new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// "undefined"
			}
		}
	}
	return _fo;
}

function appRequest() {
	this.init();
}

appRequest.prototype.init = function() {
	this.handler = null;
	this.result = null;
}

appRequest.XMLHTTPOpen = function(handler) {
	var url = "../interface.php";
	handler.open("POST", url, true);
	return true;   
}

appRequest.prototype.handler = null;

appRequest.prototype.result = null;

appRequest.prototype.GetResult = function() {
	return this.result;
}

appRequest.prototype.Call = function(args) {
	disableStuff();
    
	this.handler = GET_XML_HTTP();
	appRequest.XMLHTTPOpen(this.handler);
	
	var caller = this;
	
	this.handler.onreadystatechange = function() {
		caller.Callback.call(caller);
	}
        
	this.handler.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
	this.handler.setRequestHeader('Access-Control-Allow-Origin', '*');

    try {
		var queryString = "";
		for (var key in args) {
		    if (args.hasOwnProperty(key)) {
		    	queryString =  queryString + key + "=" + encodeURIComponent(args[key]) + "&";
		    }
		}

		this.handler.send(queryString);
		caller.handler = this.handler;
    } catch(e) {
    	// Do something with this Exception
    	var ex_name 		= e.name;
     	var ex_message 		= e.message;
     	var http_error_text = handler.statusText;
     	var http_error 		= handler.status;
    }
}

appRequest.prototype.Callback=function() {
	if(this.handler.readyState == 4) {
		var txtResponse;
		
		if (this.handler.status == 200 && this.handler.responseText) {
			// Response can be Error Message - ToDo
			txtResponse = this.handler.responseText;
		} else {
			// What to do when http status is not 200
			var http_status = this.handler.status;
			
			if (this.handler.responseText != null || this.handler.responseText != "") {
				txtResponse = "Error: (" + http_status + ") " + this.handler.responseText;
			} else {
				txtResponse = "Error: (" + http_status + ") " + this.handler.statusText;
			}
		}
		
		this.result = txtResponse;
		this.handler.onreadystatechange = function() {};
		this.handler = null;
		
		enableStuff();
		
		if (this.result.indexOf("Error") == -1) {
			// Result OK
			var htmlResponse = processResponse(this.result);
			// ToDo: Review printResponse
			//printResponse(this.result);
			document.getElementById('responseContainer').innerHTML = htmlResponse;
		} else {
			document.getElementById('responseContainer').innerHTML = this.result;
			document.getElementById('responseContainer').className = "error_show";
		}
	}
}

var form = function() {
	this.name 			= "";   // Application Name
	this.apicall		= "";   // API Call to process
	this.model			= "";   // Product Model to Update (TODOS=ALL)
	this.margin			= "";   // Product Margin to apply to List Price (Default=15%)
	this.discount		= "";   // Product Discount for online store (Default=20%)
	this.updateStock	= "";   // If Product Stock quantity must be updated
	this.fields 		= null; // Product Fields (array)
}

form.prototype.init = new form();

form.prototype.setName = function(name) {
	this.name = name;
}

form.prototype.getName = function() {
	return this.name;
}

form.prototype.setApiCall = function(apicall) {
	this.apicall = apicall;
}

form.prototype.getApiCall = function() {
	return this.apicall;
}

form.prototype.setModel = function(model) {
	this.model = model;
}

form.prototype.getModel = function() {
	return this.model;
}

form.prototype.setMargin = function(margin) {
	this.margin = margin;
}

form.prototype.getMargin = function() {
	return this.margin;
}

form.prototype.setDiscount = function(discount) {
	this.discount = discount;
}

form.prototype.getDiscount = function() {
	return this.discount;
}

form.prototype.setUpdateStock = function(updateStock) {
	if (updateStock)
		this.updateStock = 0;
	else
		this.updateStock = -1;
}

form.prototype.getUpdateStock = function() {
	return this.updateStock;
}

form.prototype.setFields = function(fields) {
	this.fields = fields;
}

form.prototype.getFields = function() {
	return this.fields;
}

form.prototype.setFieldData = function() {
	var f = new productFields();
	f.id			= null;
	f.model			= null;
	f.title 		= null;
	f.status	 	= null;
	f.sku			= null;
	f.list_price 	= null;
	f.regular_price	= null;
	f.sale_price 	= null;
	f.stock 		= null;
	f.lastUpdate	= null;
	
	this.setFields(f);
}

var productFields = function() {
	this.id				= ""; // Product ID
    this.model			= ""; // Product Model
	this.title	 		= ""; // Product Title
	this.status			= ""; // Product Status (Publish / Draft / Private)
	this.sku		 	= ""; // Product SKU
	this.list_price 	= ""; // Product List Price
	this.regular_price	= ""; // Product Regular Price (Sale Price * 1.25 IF Store Discount is 20%)
	this.sale_price 	= ""; // Product Sale Price (List Price + Margin)
	this.stock 			= ""; // Product Quantity
	this.lastUpdate		= ""; // Timestamp of last Product update
}

form.prototype.formHandler = function() {
	var ajaxReq = new appRequest();
	ajaxReq.Call(this);
}

enableStuff = function() {
	var api	= document.getElementById("apicall").value;
	
	document.getElementById('responseContainer').className = "error_show";
	
	document.getElementById('menuLink').href = "/gestiondeproductos/index.html";
	
	document.getElementById('updateStock').readOnly = false;
	document.getElementById('updateStock').disabled = false;
	document.getElementById('updateStock').style.backgroundColor = '#FFFFFF';
	
	document.getElementById('submit_it').disabled = false;
	document.getElementById("submit_it").style.background = '#36434B';
	document.getElementById("submit_it").color = "#FFFFFF";
	
	/*
	document.getElementById("submit_it").onmouseover = function() {
		document.getElementById("submit_it").style.background = "#A7318F";
		document.getElementById("submit_it").color = "#FFFFFF";
	};
	*/

	switch (api) {
		case "updateProductsApi":
			document.getElementById("submit_it").value = "ACTUALIZAR PRODUCTOS";
			break;
		case "getProductsNotInStoreApi":
			document.getElementById("submit_it").value = "IDENTIFICAR PRODUCTOS QUE NO EXISTEN EN LA TIENDA";
			break;
		case "getProductsNotInFileApi":
			document.getElementById("submit_it").value = "IDENTIFICAR PRODUCTOS QUE NO EXISTEN EN LA LISTA";
			break;
	}
}

disableStuff = function() {
	document.getElementById('responseContainer').innerHTML = "";
	document.getElementById('responseContainer').className = "error_hide";
	
	document.getElementById('menuLink').href = "javascript:void(0)";
	
	document.getElementById('updateStock').readOnly = true;
	document.getElementById('updateStock').disabled = true;
	document.getElementById('updateStock').style.backgroundColor = '#7e7e7e';

	document.getElementById("submit_it").value = "Prcesando Lista de Productos...";
	document.getElementById('submit_it').disabled = true;
	document.getElementById("submit_it").color = "#00FFFF";
	document.getElementById("submit_it").style.background = '#555555';
	
	/*
	document.getElementById("submit_it").onmouseover = function() {
		document.getElementById("submit_it").style.background = "#D2CDE6";
		document.getElementById("submit_it").color = "#00FFFF";
	};
	*/

}

function processResponse(response) {
	var html = "";
	var responseContent = JSON.parse(response);
	
	switch (document.getElementById("apicall").value) {
		case "updateProductsApi":
			html = "<div id='responseMessage'>" +
				"<p class='response'>La Lista de Productos fue actualizada con exito!</p>" +
					"</div>" + 
						"<div id='tableContainer'>" +
							"<table class='tableContent'>" +
								"<tr><td><p class='responseContentText'>Modelo Seleccionado</p></td>" + 
									"<td><p class='responseContentValue'>" + responseContent.Model + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Productos Procesados</p></td>" + 
									"<td><p class='responseContentValue'>" + responseContent.Total + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Productos Actualizados</p></td>" +
									"<td><p class='responseContentValue'>" + responseContent.Succeeded + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Actualizaciones Fallidas</p></td>" +
									"<td><p class='responseContentValue'>" + responseContent.Failed + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Productos que no existen en la Tienda Online</p></td>" +
									"<td><p class='responseContentValue'>" + responseContent.Discarded + "</p></td></tr>" +
							"</table>" + 
						"</div>";
			break;
		case "getProductsNotInStoreApi":
			html = "<div id='responseMessage'>" +
				"<p class='response'>Se han identificado Productos que no existen en la Tienda!</p>" +
					"</div>" + 
						"<div id='tableContainer'>" +
							"<table class='tableContent'>" +
								"<tr><td><p class='responseContentText'>Modelo Seleccionado</p></td>" + 
									"<td><p class='responseContentValue'>" + responseContent.Model + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Productos que NO existen en TIENDA y figuran en LISTA</p></td>" +
									"<td><p class='responseContentValue'>" + responseContent.TotalList + "</p></td></tr>" +
							"</table>" + 
						"</div>" + 
				"<p class='response'>" + responseContent.Email + "</p>";;
			break;
		case "getProductsNotInFileApi":
			html = "<div id='responseMessage'>" +
				"<p class='response'>Se han identificado Productos en la Tienda que no existen en la Lista!</p>" +
					"</div>" + 
						"<div id='tableContainer'>" +
							"<table class='tableContent'>" +
								"<tr><td><p class='responseContentText'>Modelo Seleccionado</p></td>" + 
									"<td><p class='responseContentValue'>" + responseContent.Model + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Cantidad de Productos de la Lista</p></td>" +
									"<td><p class='responseContentValue'>" + responseContent.TotalFile + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Cantidad de Productos de la Tienda</p></td>" +
									"<td><p class='responseContentValue'>" + responseContent.TotalStore + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Productos que existen en TIENDA y no figuran en LISTA (TOTAL)</p></td>" +
									"<td><p class='responseContentValue'>" + responseContent.TotalNotUpdated + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Productos que existen en TIENDA y alguna vez fueron actualizados</p></td>" +
									"<td><p class='responseContentValue'>" + responseContent.TotalUpdated + "</p></td></tr>" +
								"<tr><td><p class='responseContentText'>Productos que existen en TIENDA y nunca fueron actualizados</p></td>" +
									"<td><p class='responseContentValue'>" + responseContent.TotalNeverUpdated + "</p></td></tr>" +
							"</table>" + 
						"</div>" + 
				"<p class='response'>" + responseContent.Email + "</p>";
			break;
		case "":
			// Apicall Undefined - Throw Error
			return "";
	}
	return html;
}


function printResponse(result) {
	// If iframe exists...remove it.
	var iframe;
	var responseContainerDiv
	
	iframe = document.getElementById('iResults');
	responseContainerDiv = document.getElementById("responseContainer");
	
	if (iframe) {
		responseContainerDiv.removeChild(iframe);
	}

	var entries = JSON.parse(result);
	var count = entries["result_count"];
	var total = entries["total_count"];
	var entryList = entries["entry_list"];
	
	var htmlTable;
	var header1 = entryList[0]["name_value_list"]["id"]["name"];
	var header2 = entryList[0]["name_value_list"]["name"]["name"];
	var header3 = entryList[0]["name_value_list"]["status"]["name"];
	var header4 = entryList[0]["name_value_list"]["campaign_type"]["name"];
	
	htmlTable = '<table width="100%" border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">' 
		+ '<tbody>'
		+ '<tr bgcolor="lightgrey" style="text-transform:uppercase; letter-spacing:0.5px; font:12px arial, sans-serif; border-bottom:1px solid lightgrey;">'
		+ '<th height="30px" vertical-align="center" align="left">'
		+ header1
		+ '</th>'
		+ '<th height="30px" vertical-align="center" align="left">'
		+ header2
		+ '</th>'
		+ '<th height="30px" vertical-align="center" align="left">'
		+ header3
		+ '</th>'
		+ '<th height="30px" vertical-align="center" align="left">'
		+ header4
		+ '</th>'
		+ '</tr>';


	for(var i in entryList ) {
	    if (entryList.hasOwnProperty(i)){
	       var entry = entryList[i]["name_value_list"];
	       
	       var cell1 = entry["id"]["value"];
	       var cell2 = entry["name"]["value"];
	       var cell3 = entry["status"]["value"];
	       var cell4 = entry["campaign_type"]["value"];
	       
	       htmlTable += '<tr style="letter-spacing:0.5px; font:12px arial, sans-serif; border-bottom:1px solid lightgrey;">'
	    	   + '<td vertical-align="center">' 
	    	   + cell1 
	    	   + '</td>'
	    	   + '<td vertical-align="center">' 
	    	   + cell2 
	    	   + '</td>'
		       + '<td vertical-align="center">' 
	    	   + cell3 
	    	   + '</td>'
	    	   + '<td vertical-align="center">' 
	    	   + cell4 
	    	   + '</td></tr>';	
	    }
	}
	
	htmlTable += '</tbody></table>'
	
	var content = '<!DOCTYPE html>'
		 + '<head>'
		 + '<title></title>'
		 + '</head>'
		 + '<body>'
		 + htmlTable
		 + '</body>'
		 + '</html>';
	
	// Create iframe
	iframe = document.createElement('iframe');
	iframe.id = "iResults";
	iframe.setAttribute("src", "data:text/html;charset=utf-8," + content);
	responseContainerDiv.appendChild(iframe)	
	
	var countDiv = document.getElementById('count');
	countDiv.innerHTML = "Total de Registros Devueltos: " + count + " de " + total;
	
	return true;
}

function sendData() {
	var name 		= "gestiondeproductos"
	var apicall		= document.getElementById("apicall").value;
	var model 		= document.getElementById("model").value;
	var margin 		= document.getElementById("margin").value;
	var discount 	= document.getElementById("discount").value;
	var updateStock = document.getElementById("updateStock").checked;
	
	if (!apicall) {
		document.getElementById('responseContainer').innerHTML = "No se puede determinar la Operación. Por favor inténtelo nuevamente";
		document.getElementById('responseContainer').className = "error-show";
		return false;
	}

	var f = new form();
	f.setName(name);
	f.setApiCall(apicall);
	f.setModel(model);
	f.setMargin(margin);
	f.setDiscount(discount);
	f.setUpdateStock(updateStock);
	f.setFieldData();
	f.formHandler();
	
	return true;
}
