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

// $time = new stdClass( id = 38218 [key] => last_update_api [value] => 1518560711 ) 
$time = new stdClass();
$time->key = 'last_update_api';
$time->value = '333333333';

$t = array();
$t[] = $time;


$time2 = array	();
$time2[0] = 38218;
$time2[1] = 'last_update_api';
$time2[2] = '1111111111';

$endpoint = 'products';
		
$woocommerce = new Client(
		'https://www.yokohamapalermo.com.ar/', 
		'ck_dba953dc5bc6af8d7cea0824dd181bc3de660369', 
		'cs_9e42a93a406857ba843739cb8a86361cf188c41e', 
		['wp_api' => true, 'version' => 'wc/v2',  'query_string_auth' => true]);

$data = [
		'regular_price' => '9999',
		'sale_price' => '6666',
		'meta_data' => $t
];

/*
$response = $woocommerce->get('products',array( 'filter[limit]' => 100 ));
print_r($response);
$count = 0 ;
foreach ($response as $storeProduct) {
	$count += 1;
	echo ($storeProduct->id . "\r");
}
echo ("   count   " . $count);
*/

$mysqli = new mysqli('localhost', 'yokohama',	'yoko', 'yokohama');
$sql = "SELECT * FROM wise_products_timestamp ORDER BY id DESC LIMIT 1";
$r = $mysqli->query($sql);

$t = mysqli_fetch_assoc($r)['last_update'];


print_r($t);




?>