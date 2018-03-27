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
require_once("classes/database.php");
require_once("classes/messages.php");
require_once("classes/customexception.php");
require_once("classes/product.php");
require_once("classes/productList.php");
require_once("classes/reports.php");
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
										`last_update` INT NOT NULL)
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
		$sqlInsertTimestamp = "INSERT INTO " . $TABLE_TIMESTAMP . "(`last_update`) VALUES (" . $this->_TIMESTAMP . ")";

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

	protected function updateProductApiCall() {
		if (!$this->checkConfig()) {
			echo utf8_encode(Messages::BAD_CONFIG);
			return FALSE;
		}

		$updateStock = ($this->_PARAMS['updateStock'] == 'true');
		$pList = new productList();
		$pList->setProductList($updateStock);
		$pl = $pList->getProductList();

		$this->_RESPONSE = $this->process($pl);

		$this->printCounters();

		echo $this->_RESPONSE;
		return;
	}

	protected function diffProductApiCall() {
		$generateNoList = ($this->_PARAMS['updateStock'] == 'true');

		$pList = new productList();
		$pList->setProductList(false);
		$pl = $pList->getProductList();

		$reports = new reports();
		$result1 = $reports->generateNoStoreReport($pl);

		if ($generateNoList) {
			// Identificar Productos que existen en la Tienda Online y no figuran en la Lista de Productos!
			$pList1 = new productList();
			$pList1->setStoreProductList();
			$storeList = $pList1->getStoreProductList();
			$result2 = $reports->generateNoListReport($storeList);
			echo $this->_RESPONSE = json_encode(array_merge($result1, $result2));
		} else {
			echo $this->_RESPONSE = json_encode($result1);
		}
		return;
	}

	protected function process($pList) {
		for ($i = 0; $i < count($pList); $i++) {
			$product = $pList[$i]->getProduct();

			if ($product[0]['value'] > 0) {

				// Producto con ID - Existe en la tienda y va a ser actualizado.
				$result = $this->processProduct($product);

				if ($this->_Logger->isDebugOn()) {
					$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] process() - count: " . $i);
				}

				if ($result) {
					// Producto Actualizado OK
					$product[10]['value'] = Product::_OP_SUCCESS;
					$this->countSUCCESS += 1;
				} else {
					// Producto No Actualizado - FAILURE
					$product[10]['value'] = Product::_OP_FAILED;
					$this->countFAIL += 1;

					if ($this->_Logger->isDebugOn()) {
						$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] process() - ERROR - Product Update Failed for Product SKU " . $product[4]['value']);
					}
				}
			} else {
				// Producto sin ID - No existe en la tienda.
				$product[10]['value'] = Product::_OP_DISCARDED;
				$this->countDISCARD += 1;
			}
			$this->countTOTAL += 1;
			sleep(3);
		}

		$response = array(
				'Succeeded' => $this->countSUCCESS,
				'Failed' => $this->countFAIL,
				'Discarded' => $this->countDISCARD,
				'Total' => $this->countTOTAL
		);

		return json_encode($response);
	}

	protected function processProduct($product) {
		// Updates [sale_price, regular_price, stock_quantity, last_update_api]

		$bSuccess = FALSE;

		$id				= $product[0]['value'];
		$sku			= $product[4]['value'];
		$regular_price 	= str_replace('.', ',', $product[6]['value']);
		$sale_price 	= str_replace('.', ',', $product[7]['value']);
		$stock			= $product[8]['value'];
		$update_stock 	= $product[9]['value'];
		$timestamp	 	= $this->_TIMESTAMP;

		// Setting Meta Data - last update api
		$last_update = new stdClass();
		$last_update->key = 'last_update_api';
		$last_update->value = $timestamp;

		$metaData = array();
		$metaData[] = $last_update;

		if ($update_stock > 0) {
			// Update Stock
			$data = [
					'regular_price' => $regular_price,
					'sale_price' => $sale_price,
					'stock_quantity' => $stock,
					'meta_data' => $metaData
			];

			$data = $data . ['stock_quantity' => $stock];
		} else {
			// No Update Stock
			$data = [
					'regular_price' => $regular_price,
					'sale_price' => $sale_price,
					'meta_data' => $metaData
			];
		}

		// WooCommerce Endpoint
		$endpoint = 'products/' . $id;

		try {
			// WOOCMMERCE - REST API!!!!
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] processProduct() - Procesando Producto: " . $product[4]['value']);
			}
			$woocommerce = new Client($this->_STORE_URL, $this->_CLIENTKEY, $this->_SECRETKEY, ['wp_api' => true, 'version' => 'wc/v2',  'query_string_auth' => true]);
			$response = $woocommerce->put($endpoint, $data);

			if ($response->id == $id) {
				$bSuccess = TRUE;
			} else {
				throw new CustomException(Messages::RESULT_ERROR);
			}
		} catch (Automattic\WooCommerce\HttpClient\HttpClientException $e) {
			// Producto No Actualizado - FAILURE
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] processProduct() - ERROR - WooCommerce REST API: " . $e);
			}
			$bSuccess = FALSE;
		} catch (Exception $ex) {
			// Producto No Actualizado - FAILURE
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] processProduct() - EXCEPTION: " . $e);
			}
			$bSuccess = FALSE;
		} catch (CustomException $ce) {
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] processProduct() - ERROR El producto no se ha actualizado: " . $ce->getMessage());
			}
			$bSuccess = FALSE;
		} finally {
			unset($woocommerce);	
		}
		
		return $bSuccess;
	}

	protected function printCounters() {
		if ($this->_Logger->isDebugOn()) {
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - Se han procesado " . $this->countTOTAL . " productos" );
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - Se han modificado con exito " . $this->countSUCCESS . " productos" );
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - " . $this->countFAIL . " productos han fallado al intentar actualizar" );
			$this->_Logger->writeLogFile("[DEBUG] - [wiseClient] printCounters() - " . $this->countDISCARD . " productos no existen en la tienda online" );
		}
	}
}
?>