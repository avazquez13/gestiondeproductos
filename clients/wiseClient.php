<?php
/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */


/*
// WOOCOMMERCE REST API LIBRARY
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/Automattic/WooCommerce/Client.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/Automattic/WooCommerce/HttpClient/BasicAuth.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/Automattic/WooCommerce/HttpClient/HttpClient.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/Automattic/WooCommerce/HttpClient/HttpClientException.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/Automattic/WooCommerce/HttpClient/OAuth.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/Automattic/WooCommerce/HttpClient/Options.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/Automattic/WooCommerce/HttpClient/Request.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/Automattic/WooCommerce/HttpClient/Response.php");

use Automattic\WooCommerce\Client;

require_once("/home/yokohamapalermo/public_html/gestiondeproductos/clients/baseClient.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/classes/database.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/classes/messages.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/classes/customexception.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/classes/product.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/classes/productList.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/classes/reports.php");
require_once("/home/yokohamapalermo/public_html/gestiondeproductos/log/logger.php");
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

require_once("clients/baseClient.php");

require_once("classes/product.php");
require_once("classes/productList.php");
require_once("classes/database.php");
require_once("classes/file.php");
require_once("classes/messages.php");
require_once("classes/customexception.php");

require_once("code/reports.php");
require_once("code/productData.php");
require_once("code/routines.php");

require_once("log/Logger.php");

class WiseClient extends BaseClient{

	public function  checkConfig() {
		$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
		$TABLE_TIMESTAMP 	= $ini['db_table_timestamp'];
		$TABLE_PROD_NOLIST 	= $ini['db_table_prod_nolist'];

		$retVal = false;

		// SQL CREATE TABLE - wise_products_timestamp
		$sqlCreateProductsTimestamp = "CREATE TABLE IF NOT EXISTS " . $TABLE_TIMESTAMP . "
										(`id` INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
										`last_update` INT NOT NULL,
										`model` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL)
										ENGINE=InnoDB";

		// SQL CREATE TABLE - wise_products_nolist
		$sqlCreateProductsNoList = "CREATE TABLE IF NOT EXISTS " . $TABLE_PROD_NOLIST . "
										(`id` bigint(20) NOT NULL,
										`title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
										`sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
										`regular_price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
										`sale_price` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
										`status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
										PRIMARY KEY (`id`))
										ENGINE=InnoDB";

		// SQL INSERT TIMESTAMP - wise_products_timestamp
		$sqlInsertTimestamp = "INSERT INTO " . $TABLE_TIMESTAMP . "(`last_update`, `model`) VALUES (" . $this->_TIMESTAMP . ", " . $this->getModel() . ")";

		// SQL QUERIES FOR CHECKUPS
		$sqlSelectProductsTimestamp = "SELECT * FROM " . $TABLE_TIMESTAMP;
		$sqlSelectProductsNoList = "SELECT count(*) FROM " . $TABLE_PROD_NOLIST;

		try {
			$db = database::getInstance();
    		$mysqli = $db->getConnection();
    		$result1 = $mysqli->query($sqlCreateProductsTimestamp);
    		$result2 = $mysqli->query($sqlCreateProductsNoList);

    		if ($result1 & $result2) {
    			if ($this->_Logger->isDebugOn()) {
    				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] checkConfig() - Configuración OK");
    			}
    		} else {
    			if ($this->_Logger->isDebugOn()) {
    				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] checkConfig() - WARNING Fallo en la Configuración");
    			}
    		}

			$result3 = $mysqli->query($sqlInsertTimestamp);

			if ($result3) {
				if ($this->_Logger->isDebugOn()) {
					$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] checkConfig() - Timestamp Configurado: " . $this->_TIMESTAMP);
				}
			}

			$retVal = true;
		} catch(Exception $e) {
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] checkConfig() - CONFIG ERROR:  " . utf8_encode(Messages::BAD_CONFIG));
			}
			echo "CONFIG ERROR: " . utf8_encode(Messages::BAD_CONFIG);;
			$retVal = false;
		}

		return $retVal;
	}

	protected function getProductDataApiCall() {
		$pd = new ProductData();
		$pl = $pd->getProductsFromFile($this->getModel());
		echo json_encode($pl['products']);
		return;
	}
	
	protected function updateProductsApiCall() {
		if (!$this->checkConfig()) {
			echo utf8_encode(Messages::BAD_CONFIG);
			return FALSE;
		}
		
		$pd = new ProductData();
		$pl = $pd->getProductsFromFile($this->getModel());
		$this->_RESPONSE = $this->process($pl['products']);
		echo json_encode($this->_RESPONSE);
		return;
	}

	protected function getProductsNotInStoreApiCall() {
		$pd = new ProductData();
		$pl = $pd->getProductsFromFile($this->getModel());
		$aNoStore = $pd->findProductsNotInStore($pl['products']);
		
		$reports = new reports();
		$this->_RESPONSE = $reports->generateNoStoreReport($aNoStore);
		$this->_RESPONSE['Model'] = $this->getModel();
		echo json_encode($this->_RESPONSE);
		return;
	}
	
	protected function getProductsNotInFileApiCall() {
		$pdFile = new ProductData();
		$prodcutsFromFile = $pdFile->getProductsFromFile($this->getModel());
		
		$pdDB = new ProductData();
		$prodcutsFromStore = $pdDB->getProductsFromStore($this->getModel());
		
		$reports = new reports();
		$this->_RESPONSE = $reports->generateNoFileReport($prodcutsFromFile, $prodcutsFromStore);
		$this->_RESPONSE['Model'] = ($this->getModel() != null ? $this->getModel() : 'TODOS');
		
		echo json_encode($this->_RESPONSE);
	}

	protected function process($pList) {
		foreach($pList as $key => $product) {
			$result = $this->processProduct($product);
			
			switch ($result) {
				case 0: // SUCCESS
					$this->countSUCCESS += 1;
					break;
				case 1: // FAIL
					$this->countFAIL += 1;
					
					if ($this->_Logger->isDebugOn()) {
						$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] process() - ERROR - Product Update Failed for Product SKU " . $product[4]['value']);
					}
					
					break;
				case 2: // DISCARD
					$this->countDISCARD += 1;
					break;
			}

			$this->countTOTAL += 1;
		}
		
		$this->printCounters();

		$response = array(
				'Model' => $this->getModel(),
				'Succeeded' => $this->countSUCCESS,
				'Failed' => $this->countFAIL,
				'Discarded' => $this->countDISCARD,
				'Total' => $this->countTOTAL
		);

		return $response;
	}

	protected function processProduct($product) {
		// Updates [sale_price, regular_price, stock_quantity, last_update_api]
		// Return Values: 0=success | 1=failure | 2=discarded
		$opResult = 0;

		// Read Product Data
		$id				= $product[0]['value'];
		$model			= $product[1]['value'];
		$title			= $product[2]['value'];
		$status			= $product[3]['value'];
		$sku			= $product[4]['value'];
		$list_price		= $product[5]['value'];
		$regular_price 	= $product[6]['value'];
		$sale_price 	= $product[7]['value'];
		$stock			= $product[8]['value'];
		$timestamp	 	= $product[9]['value'];
		
		// Check if Product exists in Store
		If ($id == null) {
			$pd = new ProductData();
			$prodId = $pd->getProductIdBySKU($sku);
			
			if ($prodId == -1) {
				// Producto sin ID - No existe en la tienda.
				$opResult = WiseClient::_OP_DISCARDED;
				return $opResult;
			}
		} else {
			$prodId = $id;
		}
				
		// Sale Price = List Price + Margin (default 15%)
		$sale_price = routines::calculateSalePrice($list_price, $this->getMargin());
			
		// Regular Price = Sale Price + Percentage Online (default 20%)
		$regular_price = routines::calculateRegularPrice($sale_price, $this->getDiscount());
		
		// Define Process TimeStamp
		$timestamp = $this->getProcessTimestamp();
		
		// Setting Meta Data - last_update_api
		$last_update = new stdClass();
		$last_update->key = 'last_update_api';
		$last_update->value = $timestamp;

		$metaData = array();
		$metaData[] = $last_update;

		if ($this->getUpdateStock() > 0) {
			// Update Stock
			$data = [
					'regular_price' => preg_replace('/[^0-9-.]+/', '', strval($regular_price)),
					'sale_price' => preg_replace('/[^0-9-.]+/', '', strval($sale_price)),
					'stock_quantity' => $stock,
					'meta_data' => $metaData
			];

			$data = $data . ['stock_quantity' => $stock];
		} else {
			// Do Not Update Stock
			$data = [
					'regular_price' => preg_replace('/[^0-9-.]+/', '', strval($regular_price)),
					'sale_price' => preg_replace('/[^0-9-.]+/', '', strval($sale_price)),
					'meta_data' => $metaData
			];
		}
		
				// WooCommerce Endpoint
		$endpoint = 'products/' . $prodId;

		try {
			// WOOCMMERCE - REST API!!!!
			$woocommerce = new Client($this->_STORE_URL, $this->_CLIENTKEY, $this->_SECRETKEY, ['wp_api' => true, 'version' => 'wc/v2',  'query_string_auth' => true]);
			
			$response = $woocommerce->put($endpoint, $data);
			
			if ($response->id == $prodId) {
				$opResult = WiseClient::_OP_SUCCEDED;
			} else {
				throw new CustomException(Messages::RESULT_ERROR);
			}
		} catch (Automattic\WooCommerce\HttpClient\HttpClientException $e) {
			// Producto No Actualizado - FAILURE
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] processProduct() - ERROR - WooCommerce REST API: " . $e);
			}
			$opResult = WiseClient::_OP_FAILED;
		} catch (Exception $ex) {
			// Producto No Actualizado - FAILURE
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] processProduct() - EXCEPTION: " . $ex);
			}
			$opResult = WiseClient::_OP_FAILED;
		} catch (CustomException $ce) {
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] processProduct() - ERROR - El producto no se ha actualizado: " . $ce->getMessage());
			}
			$opResult = WiseClient::_OP_FAILED;
		} finally {
			unset($woocommerce);
		}
		
		return $opResult;
	}	
	
	protected function printCounters() {
		if ($this->_Logger->isDebugOn()) {
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - Se han procesado " . $this->countTOTAL . " productos" );
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - Productos Procesados: " . $this->getModel());
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - Margen Aplicado: " . $this->getMargin() . "%");
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - Descuento Online Aplicado: " . $this->getDiscount() . "%");
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - Se han modificado con exito " . $this->countSUCCESS . " productos" );
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - " . $this->countFAIL . " productos han fallado al intentar actualizar" );
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - " . $this->countDISCARD . " productos no existen en la tienda online" );
		}
	}
}
?>