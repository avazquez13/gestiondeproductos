<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class reports{
	private $fileName;
	private $eol;
	
    protected $_Logger;
     
    public function __construct() { 
    	$this->_Logger = new Logger();
    }
    
    public function getLastUpdateData() {
    	try {
    		$db = database::getInstance();
    		$mysqli = $db->getConnection();
    		$sql = "SELECT * FROM wise_products_timestamp ORDER BY id DESC LIMIT 1";
    		$result = $mysqli->query($sql);
    		$timestamp = mysqli_fetch_assoc($result)['last_update'];
    		$model = mysqli_fetch_assoc($result)['model'];
    	} catch(Exception $e) {
    		if ($this->_Logger->isDebugOn()) {
    			$this->_Logger->writeLogFile("[DEBUG] - [reports] getLastStoreTimestamp() - ERROR:  " . utf8_encode(Messages::RESULT_ERROR));
    		}
    	}
    	
    	$data = array(
    			'last_update' => $timestamp,
    			'model' => $model
    	);
    	
    	return $data;
    }
    
    // Lista de Productos que no figuran en la Tienda
    // Create NO STORE product file | PARAM Products Array(sku, model, title)
    public function generateNoStoreReport($products) {
    	$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
    	$eol = $ini['log_endline'];
    	$fileName = $ini['nostore_file'];
    	
    	$file = new File();
    	$file->setFile($fileName);
		$file->openFileForReadWrite();
		$filePointer = $file->getFilePointer();
		
		$countTotal = 0;
   		
		foreach($products as $key => $p) {
			$sku   = $p['sku'];
			$model = $p['model'];
			$title = $p['title'];
			
    		$message = $sku . "," . $model . "," . $title . ",";
    		fwrite($filePointer, "$message" . $eol);    		
    		$countTotal++;
		}
		
		$file->closeFile();
    	 
    	if ($this->_Logger->isDebugOn()) {
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoStoreReport() - Productos Que No Existen en Tienda: " . $countTotal);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoStoreReport() - Fichero Creado " . $file->getFile());
    	}
    	
    	$response = array(
    			'Model' => '',
    			'TotalList' => $countTotal
    	);
		
		return $response;
    }
    
    // Lista de Productos que no fueron actualizados porque no figuran en la lista de Productos
    // Create NO LIST product file | PARAM Products Array(sku, model, title)
    public function generateNoFileReport($pFile, $pStore) {
    	$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
    	$eol = $ini['log_endline'];
    	$fileName = $ini['nolist_file'];
    	 
    	$file = new File();
    	$file->setFile($fileName);
    	$file->openFileForReadWrite();
    	$filePointer = $file->getFilePointer();
    	
    	$lastUpdateData = $this->getLastUpdateData();
    	
    	// Products from File
    	$productsFromFile = $pFile['products'];
    	
    	// Products from Store
    	$productsFromStore = $pStore['products'];
    	 
    	// Total Count of Products from File
    	$countTotalFile = $pFile['count'];
    	    	
    	// Total Count of Products from Store
    	$countTotalStore = $pStore['count'];
    	
    	// Cantidad Total De Productos NUNCA actualizados
    	$countTotalNeverUpdated = 0;
    	
    	// Cantidad Total De Productos ACTUALIZADOS
    	$countTotalUpdated = 0;
    	
    	// Texto adicional para productos no actualizados
    	$text = '';
    	
    	$results = array();
    	
    	foreach($productsFromStore as $key => $storeRow) {
    		$id				= $storeRow[0]['value'];
    		$model			= $storeRow[1]['value'];
    		$title			= $storeRow[2]['value'];
    		$status			= $storeRow[3]['value'];
    		$sku			= $storeRow[4]['value'];
    		$listPrice		= $storeRow[5]['value'];
    		$regularPrice 	= $storeRow[6]['value'];
    		$salePrice 		= $storeRow[7]['value'];
    		$stock			= $storeRow[8]['value'];
    		$timestamp	 	= $storeRow[9]['value'];
    		
    		$this->_Logger->writeLogFile("[DEBUG] - Store ID: " . $id);
    		$this->_Logger->writeLogFile("[DEBUG] - Store ID: " . $model);
    		$this->_Logger->writeLogFile("[DEBUG] - Store TS: " . $timestamp);
    		
    		$found = false;
    		
    		// Loop through Products From File to check if Product From Store is OK
    		foreach($productsFromFile as $key => $fileRow) {
    			print_r($fileRow[0]['value']);
    			if ($id == $fileRow[0]['value']) {
    				// Product in Store PRESENT in File
    				$found = true;
    				break;
    			}
    		}
    		
    		if (!$found) {
    			// Product in Store NOT PRESENT in File
    			if ($timestamp == "" OR $timestamp == 0 OR $timestamp == null) {
    				// Product NEVER updated | Probably bad SKU
    				$countTotalNeverUpdated++;
    				$text = "Nunca Actualizado | Bad SKU";
    			} else {
    				// Product UPDATED at least once | Probably removed from catalogue
    				$countTotalUpdated++;
    				$text = "Actualizado | No Figura en Lista";
    			}
    			
    			// Print Product to NoList file
    			$message = 	$id . "," . $model . "," . $title . "," . $status . "," . $sku . "," . $text;    			 
    			fwrite($filePointer, "$message" . $eol);
    		}
    	}
    	
    	$countTotalNotUpdated = $countTotalUpdated + $countTotalNeverUpdated;
    	
    	$file->closeFile();
    	
    	if ($this->_Logger->isDebugOn()) {
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos en Tienda: " . $countTotalStore);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos en List: " . $countTotalFile);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos que existen en TIENDA y no figuran en LISTA (TOTAL): " . $countTotalNotUpdated);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos que existen en TIENDA y alguna vez fueron actualizados: " . $countTotalUpdated);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos que existen en TIENDA y nunca fueron actualizados: " . $countTotalNeverUpdated);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Fichero Creado " . $file->getFile());
    	}
    	
    	$response = array(
				'TotalStore' => $countTotalStore, 
				'TotalFile' => $countTotalFile,
    			'TotalNotUpdated' => $countTotalNotUpdated,
    			'TotalUpdated' => $countTotalUpdated,
    			'TotalNeverUpdated' => $countTotalNeverUpdated
		);
		
		return $response;
    }    
}
?> 