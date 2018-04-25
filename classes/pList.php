<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */

class pList{
	// LOGGING CONFIG - ini file
	protected $_PROD_FILE;
	protected $_LOG_EOL;

	// FILE POINTER
	private $fp;

    protected $_Logger;

    // PRODUCTS LIST ARRAY
    private $aProductList;
    private $aStoreProductList;

    public function __construct() {
    	$this->initialize();
    	$this->readIniFile();
    }

    private function initialize() {
    	$this->fp = null;
    	$this->aProductList = array();
    	$this->aStoreProductList = array();
    	$this->_Logger = new Logger();
    }

    private function readIniFile() {
    	$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
    	$this->_PROD_FILE 	= $ini['products_file'];
    	$this->_LOG_EOL 	= $ini['log_endline'];
    }

    private function getProductIdBySKU($sku) {
    	$id = -1;

    	try {
    		$db = database::getInstance();
    		$mysqli = $db->getConnection();
    		$sql = "SELECT post_id FROM wp_postmeta WHERE meta_key = '_sku' AND meta_value = " . $sku;
    		$result = $mysqli->query($sql);

    		if ($result) {
    			if ($result->num_rows > 1) {
    				if ($this->_Logger->isDebugOn()) {
    					$this->_Logger->writeLogFile("[DEBUG] - [productList] getProductIdBySKU() - WARNING Multiple SKU " . $sku);
    				}
    			}

    			while ($row = mysqli_fetch_row($result)) {
    				$id = $row[0];
    			}
    		} else {
    			$id = -1;
    		}
    	} catch(Exception $e) {
    		// ToDo - Handle Exception & Log
    		echo "Connection failed: " . $e->getMessage();
    		$id = -1;
    	}

    	return $id;
    }

    public function getStoreProductList() {
    	return $this->aStoreProductList;
    }

    // Get All Products from STORE
    public function setStoreProductList() {
    	$id = null;
    	$name = null;
    	$title = null;
    	$status = null;
    	$sku = null;
    	$price = null;
    	$stock = null;
    	$sale = null;
    	$regular = null;
    	$timestamp = null;

    	$count = 0;

    	try {
    		$sqlProduct = "SELECT wp_posts.ID, wp_posts.post_title, wp_posts.post_status FROM wp_posts WHERE wp_posts.post_type = 'product'";
    		$db = database::getInstance();
    		$mysqli = $db->getConnection();
    		$result = $mysqli->query($sqlProduct);

    		if ($result) {
    			while ($row = mysqli_fetch_row($result)) {
    				// Product ID
    				$id = $row[0];

    				// Product Name (Model)
    				$name = null;

    				// Product Title
    				$title = $row[1];

    				// Product Status
    				$status = $row[2];

    				// Product SKU
    				$sqlSKU = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = '_sku' AND wp_postmeta.post_id = " . $id;
    				$r = $mysqli->query($sqlSKU);
    				$sku = mysqli_fetch_assoc($r)['meta_value'];

    				// Product Price
    				$price = null;

    				// Product Stock
    				$sqlStock = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = '_stock_quantity' AND wp_postmeta.post_id = " . $id;
    				$r = $mysqli->query($sqlStock);
    				$stock = mysqli_fetch_assoc($r)['meta_value'];

    				// Product Sale Price
    				$sqlSale = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = '_sale_price' AND wp_postmeta.post_id = " . $id;
    				$r = $mysqli->query($sqlSale);
    				$sale = mysqli_fetch_assoc($r)['meta_value'];

    				// Product Regular Price
    				$sqlRegular = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = '_regular_price' AND wp_postmeta.post_id = " . $id;
    				$r = $mysqli->query($sqlRegular);
    				$regular = mysqli_fetch_assoc($r)['meta_value'];

    				// Product Last Update Timestamp
    				$sqlTimestamp = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = 'last_update_api' AND wp_postmeta.post_id = " . $id;
    				$r = $mysqli->query($sqlTimestamp);
    				$timestamp = mysqli_fetch_assoc($r)['meta_value'];

    				$p = new Product();

    				$p->setProduct(
    							$id,
    							$name,
    							$title,
    							$status,
    							$sku,
    							$price,
    							$regular,
    							$sale,
    							$stock,
    							-1,
    							-1,
    							$timestamp);

    				$this->aStoreProductList[] = $p;
    				$count += 1;

    				$id = null;
    				$name = null;
    				$title = null;
    				$status = null;
    				$sku = null;
    				$price = null;
    				$stock = null;
    				$sale = null;
    				$regular = null;
    				$timestamp = null;
    			}
    		} else {
    			throw new CustomException(Messages::RESULT_ERROR);
    		}
    	} catch(Exception $e) {
    		if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [productList] setStoreProductList() - ERROR: " . $e->getMessage());
			}
    	} catch (CustomException $ce) {
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [productList] setStoreProductList() - ERROR: " . $ce->getMessage());
			}
		}

    	if ($this->_Logger->isDebugOn()) {
    		$this->_Logger->writeLogFile("[DEBUG] - [productList] setStoreProductList() - Se han leido " . $count . " productos de la Tienda Online" );
    	}
    }

    public function getProductList() {
    	return $this->aProductList;
    }

    // Get All Products from PRODUCT LIST provided
    public function setProductList($updateStock) {
    	if (file_exists($this->_PROD_FILE)) {
    		$this->openProductsFile();
    	} else {
    		return false;
    	}

    	$count = 0;

    	while (($data = fgetcsv($this->fp, 1000, ",")) !== FALSE) {
    		if (empty($data[5])) {
    			continue;
    		}

   			$p = new Product();

   			// Model - Column 0
   			$name = $data[0];

   			// Title - Column 4
   			$title = $data[4];

   			// Status - ToDo: search status from online store (published | private)
   			$status = 0;

   			// SKU - Columna 5
   			$sku = $data[5];

   			// Sale Price - Columna 7
   			$sale = preg_replace('/[^0-9-.]+/', '', $data[7]);

   			// Regular Price - 25% + Sale Price
   			$saleToInt = (int)$sale;
   			$regular = $saleToInt * 1.25;

   			// Price - Price = Sale Price / if no Sale Price then Price = Regular Price
   			$price = $sale;

   			// TIMESTAMP - NULL
   			$timestamp = 0;

   			// UPDATE STOCK -
   			// ToDo: Agregar Stock a Lista de Productos
   			// ToDo: Enable $updateStock Condition
   			$stock = null;
   			$update = -1;

			/*
   			if ($updateStock)
   				$update = 1;
   			else
   				$update = -1;
   			*/

   			$id = $this->getProductIdBySKU($sku);

   			$p->setProduct(
   					$id,
   					$name,
   					$title,
   					$status,
   					$sku,
   					$price,
   					$regular,
   					$sale,
   					$stock,
   					$update,
   					-1,
   					$timestamp);

   			$this->aProductList[] = $p;
   			$count += 1;
    	}

    	$this->closeProductsFile();

    	if ($this->_Logger->isDebugOn()) {
    		$this->_Logger->writeLogFile("[DEBUG] - [productList] setProductList() - Se han leido " . $count . " productos" );
    		$this->_Logger->writeLogFile("[DEBUG] - [productList] setProductList() - Fichero " . $this->_PROD_FILE);
    	}
    }

    private function openProductsFile() {
    	if (!is_resource($this->fp))
    		$this->fp = fopen ($this->_PROD_FILE , 'r');
    }

    private function closeProductsFile() {
    	if (is_resource($this->fp)) {
    		fclose($this->fp);
    		$this->fp = NULL;
    	}
    }

    public function __destruct() {
     	$this->closeProductsFile();
     	$this->file = NULL;
     	$this->fp = NULL;
     }
}
?>