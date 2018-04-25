<?php
/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */

// WOOCOMMERCE REST API LIBRARY
require_once("Automattic\WooCommerce\Client.php");
require_once("Automattic\WooCommerce\HttpClient\BasicAuth.php");
require_once("Automattic\WooCommerce\HttpClient\HttpClient.php");
require_once("Automattic\WooCommerce\HttpClient\HttpClientException.php");
require_once("Automattic\WooCommerce\HttpClient\OAuth.php");
require_once("Automattic\WooCommerce\HttpClient\Options.php");
require_once("Automattic\WooCommerce\HttpClient\Request.php");
require_once("Automattic\WooCommerce\HttpClient\Response.php");

use Automattic\WooCommerce\Client;

/*
[75] => stdClass Object ( 
		[id] => 38218 
		[key] => last_update_api 
		[value] => 1518560711 
)
*/

/*
$data = [
		'regular_price' => '9999',
		'sale_price' => '6666',
		'meta_data' => $t
];
*/



// Updates [sale_price, regular_price, stock_quantity, last_update_api]
// Return Values: 0=success | 1=failure | 2=discarded
$opResult = 0;

$prodId = '18468';
$sale_price = 2500.35;
$regular_price = 5000.45;
$timestamp = 0;

// Setting Meta Data - last_update_api
$last_update = new stdClass();
$last_update->key = 'last_update_api';
$last_update->value = $timestamp;

$metaData = array();
$metaData[] = $last_update;

$data = [
		'regular_price' => preg_replace('/[^0-9-.]+/', '', strval($regular_price)),
		'sale_price' => preg_replace('/[^0-9-.]+/', '', strval($sale_price)),
		'meta_data' => $metaData
];

print_r($data);

// WooCommerce Endpoint
$endpoint = 'products/' . $prodId;

// WOOCMMERCE - REST API!!!!
//$woocommerce = new Client($this->_STORE_URL, $this->_CLIENTKEY, $this->_SECRETKEY, ['wp_api' => true, 'version' => 'wc/v2',  'query_string_auth' => true]);

$woocommerce = new Client(
		'https://www.yokohamapalermo.com.ar/',
		'ck_dba953dc5bc6af8d7cea0824dd181bc3de660369',
		'cs_9e42a93a406857ba843739cb8a86361cf188c41e',
		['wp_api' => true, 'version' => 'wc/v2',  'query_string_auth' => true]);


$response = $woocommerce->put($endpoint, $data);

print_r($response);

unset($woocommerce);



?>