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
			var responseContent = JSON.parse(this.result);
			
			switch (document.getElementById("apicall").value) {
				case "updateProductApi":
					var htmlResponse = "<div id='responseMessage'>" +
						"<p class='response'>La Lista de Productos fue actualizada con exito!</p>" +
							"</div>" + 
								"<div id='tableContainer'>" +
									"<table class='tableContent'>" +
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
				case "diffProductApi":
					var htmlResponse = "<div id='responseMessage'>" +
						"<p class='response'>Se han identificado inconsistencias en la Lista de Productos!</p>" +
							"</div>" + 
								"<div id='tableContainer'>" +
									"<table class='tableContent'>" +
										"<tr><td><p class='responseContentText'>Lista de Productos</p></td>" + 
											"<td><p class='responseContentValue'>" + responseContent.TotalList + "</p></td></tr>" +
										"<tr><td><p class='responseContentText'>Productos Actualizados</p></td>" + 
											"<td><p class='responseContentValue'>" + responseContent.Updated + "</p></td></tr>" +
										"<tr><td><p class='responseContentText'>Productos que NO existen en la Tienda (figuran en la lista de productos)</p></td>" +
											"<td><p class='responseContentValue'>" + responseContent.NoStore + "</p></td></tr>" +
										"<tr><td><p class='responseContentText'>Productos en Tienda</p></td>" +
											"<td><p class='responseContentValue'>" + (responseContent.TotalStore ? responseContent.TotalStore : 'No Procesado') + "</p></td></tr>" +
										"<tr><td><p class='responseContentText'>Productos No Actualizados (TOTAL)</p></td>" +
											"<td><p class='responseContentValue'>" + (responseContent.TotalNotUpdated ? responseContent.TotalNotUpdated : 'No Procesado') + "</p></td></tr>" +
										"<tr><td><p class='responseContentText'>Productos NO Actualizados (NO FIGURAN EN LA LISTA ACTUAL)</p></td>" +
											"<td><p class='responseContentValue'>" + (responseContent.NotUpdatedCurrent ? responseContent.NotUpdatedCurrent : 'No Procesado') + "</p></td></tr>" +
										"<tr><td><p class='responseContentText'>Productos NUNCA Actualizados</p></td>" +
											"<td><p class='responseContentValue'>" + (responseContent.NotUpdatedNever ? responseContent.NotUpdatedNever : 'No Procesado') + "</p></td></tr>" +
									"</table>" + 
								"</div>";
					break;
				case "":
					// Apicall Undefined - Throw Error
					return false;
			}
			
			
			document.getElementById('responseContainer').innerHTML = htmlResponse;
		} else {
			document.getElementById('responseContainer').innerHTML = this.result;
			document.getElementById('responseContainer').className = "error_show";
		}
	}
}

var form = function() {
	this.name 	= "";
	this.api 	= "";
	this.fields = null;
}

form.prototype.init = new form();

form.prototype.setApi = function(api) {
	this.api = api;
}

form.prototype.getApi = function() {
	return this.api;
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
	f.name			= null;
	f.title 		= null;
	f.status	 	= null;
	f.sku			= null;
	f.price 		= null;
	f.regular_price	= null;
	f.sale_price 	= null;
	f.stock 		= null;
	// ToDo: Opcion update Stock y opcion Buscar Huerfanos
	f.updateStock	= document.getElementById("updateStock").checked;
	f.apicall 		= this.api;
	
	this.setFields(f);
}

form.prototype.formHandler = function() {
	var ajaxReq = new appRequest();
	ajaxReq.Call(this.fields);
}

var productFields = function() {
	this.id				= "";
    this.name			= "";
	this.title	 		= "";
	this.status			= "";
	this.sku		 	= "";
	this.price 			= "";
	this.regular_price	= "";
	this.sale_price 	= "";
	this.stock 			= "";
	this.updateStock	= "";
	this.apicall 		= "";
}

form.prototype.formHandler = function() {
	var ajaxReq = new appRequest();
	ajaxReq.Call(this.fields);
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

	if (api == "updateProductApi") {
		document.getElementById("submit_it").value = "ACTUALIZAR PRODUCTOS";
	} else {
		document.getElementById("submit_it").value = "IDENTIFICAR PRODUCTOS NO INGRESADOS";
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
	document.getElementById("submit_it").style.background = '#D2CDE6';
	
	/*
	document.getElementById("submit_it").onmouseover = function() {
		document.getElementById("submit_it").style.background = "#D2CDE6";
		document.getElementById("submit_it").color = "#00FFFF";
	};
	*/

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
	var api	= document.getElementById("apicall").value;
	var f = new form();
	
	if (!api) {
		document.getElementById('responseContainer').innerHTML = "No se puede determinar la Operación. Por favor inténtelo nuevamente";
		document.getElementById('responseContainer').className = "error-show";
		return false;
	}

	f.setApi(api);
	
	switch (api) {
		case "updateProductApi":
			f.setFieldData();
			break;
		case "diffProductApi":
			f.setFieldData();
			break;
		case "":
			// Apicall Undefined - Throw Error
			return false;
	}
	
	f.formHandler();
	return true;
}