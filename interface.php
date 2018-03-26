<?php 

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
/*
 * PENDINGS
 * 
 * LOGGER Create Categories
 * LOGERR Exception for file errors
 * GLOBAL for Logger
 * Session()
 * Change PHPSESSID name
 * USER Throw exception in api
 * 
 */
 
	require_once("clients/wiseClient.php");
	
	if (!is_array($_POST) || empty($_POST)) {
		http_response_code(400);
		echo utf8_encode(Messages::BAD_PARAM_FORMAT);
		return FALSE;
	}
	
	$client = new WiseClient();
	$client->setParams($_POST);
	$client->exec();
	return TRUE;
?>