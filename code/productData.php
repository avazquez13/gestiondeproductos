<?php

/*
 * Copyright � 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class ProductData {
	private $_Logger;
	
	public function __construct() {
		$this->_Logger = new Logger();
	}
	
	// Get Products from File provided based on Search Criteria (Model)
	public function getProductsFromFile($searchCriteria) {
		$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
		$fileName = $ini['products_file'];
		
		$file = new File();
		$file->setFile($fileName);
		$file->openFileForRead();
		$filePointer = $file->getFilePointer();
	
		$productList = new productList();
	
		$count = 0;
	
		while (($data = fgetcsv($filePointer, 1000, ",")) !== FALSE) {
			if (empty($data[5])) {
				continue;
			}
				
			// Column 0 - Product Model
			$model = $data[0];
	
			if (!$searchCriteria == "" && !empty($searchCriteria) && $searchCriteria != "TODOS") {
				if ($searchCriteria != $model){
					continue;
				}
			}
				
			// Column 1 - Product Width
			// Column 2 - Product Length
			// Column 3 - Product Heigth
			// Column 4 - Product Title
			$title = $data[4];
			// Column 5 - Product SKU
			$sku = $data[5];
			// Column 6 - Product List Price
			$listPrice = preg_replace('/[^0-9-.]+/', '', $data[6]);
			// Column 7 - Product Online Price (SALE)
			// Column 8 - STOCK - ToDo: Agregar Stock a Lista de Productos
				
			$p = new Product();
	
			$p->setProduct(
					null,
					$model,
					$title,
					null,
					$sku,
					$listPrice,
					null,
					null,
					null,
					null);
	
			$productList->addProduct($p->getProduct());
			$count += 1;
		}
	
		$file->closeFile();
	
		if ($this->_Logger->isDebugOn()) {
			$this->_Logger->writeLogFile("[DEBUG] - [productData] getProductsFromFile() - Se han leido " . $count . " productos" );
			$this->_Logger->writeLogFile("[DEBUG] - [productData] getProductsFromFile() - Fichero: " . $file->getFile());
			$this->_Logger->writeLogFile("[DEBUG] - [productData] getProductsFromFile() - Modelo: " . $searchCriteria);
		}
		
		$retVal = array (
				'products' => $productList->getProductList(),
				'count' => $productList->getNumObjects()
		);
	
		return $retVal;
	}
	
	// Get Products from Database based on Search Criteria (Model)
	public function getProductsFromStore($searchCriteria) {
		$productList = new productList();
		$count = 0;
		
		//$sqlProduct = "SELECT wp_posts.ID, wp_posts.post_title, wp_posts.post_status FROM wp_posts WHERE wp_posts.post_type = 'product'";
		
		$sqlProduct = "SELECT DISTINCT 
							p.id, 
							p.post_title, 
							p.post_status, 
							t.name, 
							tt.taxonomy
					FROM wp_posts AS p
					INNER JOIN wp_term_relationships AS tr ON p.id = tr.object_id
					INNER JOIN wp_term_taxonomy AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
					INNER JOIN wp_terms AS t ON t.term_id = tt.term_id
					WHERE 
						p.post_type =  'product' AND 
						tt.taxonomy = 'pa_model'";
		
		if (!$searchCriteria == "" && !empty($searchCriteria) && $searchCriteria != "TODOS") {
			$sql = $sqlProduct . " AND t.name = " . $searchCriteria;
		} else {
			$sql = $sqlProduct;
		}
	
		try {
			$db = database::getInstance();
			$mysqli = $db->getConnection();
			$result = $mysqli->query($sqlProduct);
	
			if ($result) {
				while ($row = mysqli_fetch_row($result)) {
					// Product ID
					$id = $row[0];
	
					// Product Title
					$title = $row[1];
	
					// Product Status
					$status = $row[2];
					
					// Product Model
					$model = $row[3];
	
					// Product SKU
					$sqlSKU = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = '_sku' AND wp_postmeta.post_id = " . $id;
					$r = $mysqli->query($sqlSKU);
					$sku = mysqli_fetch_assoc($r)['meta_value'];
	
					// Product Stock
					$sqlStock = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = '_stock_quantity' AND wp_postmeta.post_id = " . $id;
					$r = $mysqli->query($sqlStock);
					$stock = mysqli_fetch_assoc($r)['meta_value'];
	
					// Product Sale Price
					$sqlSale = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = '_sale_price' AND wp_postmeta.post_id = " . $id;
					$r = $mysqli->query($sqlSale);
					$salePrice = mysqli_fetch_assoc($r)['meta_value'];
	
					// Product Regular Price
					$sqlRegular = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = '_regular_price' AND wp_postmeta.post_id = " . $id;
					$r = $mysqli->query($sqlRegular);
					$regularPrice = mysqli_fetch_assoc($r)['meta_value'];
	
					// Product Last Update Timestamp
					$sqlTimestamp = "SELECT meta_value FROM wp_postmeta WHERE wp_postmeta.meta_key = 'last_update_api' AND wp_postmeta.post_id = " . $id;
					$r = $mysqli->query($sqlTimestamp);
					$timestamp = mysqli_fetch_assoc($r)['meta_value'];
	
					$p = new Product();
	
					$p->setProduct(
							$id,
							$model,
							$title,
							$status,
							$sku,
							null,
							$regularPrice,
							$salePrice,
							$stock,
							$timestamp);
	
					$productList->addProduct($p->getProduct());
					$count += 1;
				}
			} else {
				throw new CustomException(Messages::RESULT_ERROR);
			}
		} catch(Exception $e) {
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [productData] getProductsFromDB() - ERROR: " . $e->getMessage());
			}
		} catch (CustomException $ce) {
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [productData] getProductsFromDB() - ERROR: " . $ce->getMessage());
			}
		}
	
		if ($this->_Logger->isDebugOn()) {
			$this->_Logger->writeLogFile("[DEBUG] - [productData] getProductsFromDB() - Se han leido " . $count . " productos de la Tienda Online" );
		}
			
		$retVal = array (
				'products' => $productList->getProductList(),
				'count' => $productList->getNumObjects()
		);
	
		return $retVal;
	}
	
	public function findProductsNotInStore($pl) {
		$aNoStore = array();
		
		foreach($pl as $key => $product) {
			// Read Product Data
			$id				= $product[0]['value'];
			$model			= $product[1]['value'];
			$title			= $product[2]['value'];
			$status			= $product[3]['value'];
			$sku			= $product[4]['value'];
			$listPrice		= $product[5]['value'];
			$regularPrice 	= $product[6]['value'];
			$salePrice 		= $product[7]['value'];
			$stock			= $product[8]['value'];
			$timestamp	 	= $product[9]['value'];
			
			// Check if Product exists in Store
			$pd = new ProductData();
			$prodId = $pd->getProductIdBySKU($sku);
				
			if ($prodId == -1) {
				// Producto sin ID - No existe en la tienda.
				$pData = array (
						'sku' => $sku,
						'model' => $model,
						'title' => $title
				);
				
				array_push($aNoStore, $pData);
			}
			
		}
		return $aNoStore;
	}
	
	public function getProductIdBySKU($sku) {
		$id = -1;
	
		try {
			$db = database::getInstance();
			$mysqli = $db->getConnection();
			$sql = "SELECT post_id FROM wp_postmeta WHERE meta_key = '_sku' AND meta_value = " . $sku;
			$result = $mysqli->query($sql);
			
			if ($result) {
				if ($result->num_rows > 1) {
					if ($this->_Logger->isDebugOn()) {
						$this->_Logger->writeLogFile("[DEBUG] - [productData] getProductIdBySKU() - WARNING Multiple SKU " . $sku);
					}
				}
	
				while ($row = mysqli_fetch_row($result)) {
					$id = $row[0];
				}
			} else {
				$id = -1;
			}				
		} catch(Exception $e) {
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [productData] getProductIdBySKU() - EXCEPTION: " . $e);
			}
		}	
		return $id;
	}
}
?>