function productModelDataHandler() {
	var apicall	= "getProductDataApi";
	var f = new form();
	f.setApiCall(apicall);
	
	var queryString = "";
	
	for (var key in f) {
	    if (f.hasOwnProperty(key)) {
	    	queryString =  queryString + key + "=" + encodeURIComponent(f[key]) + "&";
	    }
	}

	$.ajax({
		url: '../interface.php',
		data: queryString,
	    type: 'post',
	    dataType: 'json',
	    
	    beforeSend: function(request) {
	    	request.setRequestHeader("Accept", "application/x-www-form-urlencoded; charset=UTF-8");
	        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
	        request.setRequestHeader('Access-Control-Allow-Origin', '*');
	    },
	    
	    success:  function (response) {
	    	modelList = getProductModelList(response);
	    	
	    	$.each(modelList, function(index, value) {
	    		$('#model').append($('<option>').text(value).val(value));
	    	});
	    },
	    
	    error: function(jqXHR, textStatus, errorThrown) {
	    	console.log(errorThrown);
	    	console.log(jqXHR.responseText);
	  	}
	});
}

function getProductModelList(data) {
	var ml = [];
	
	for (var i = 0; i < data.length; i++) {
		model = data[i][1]["value"];
		
		if (!isInArray(model, ml))
			ml.push(model);
	}
	ml.sort();
	ml.unshift("TODOS");
	return ml;
}

function isInArray(value, array) {
	return array.indexOf(value) > -1;
}

