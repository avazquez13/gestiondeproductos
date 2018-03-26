<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class reports{
	// LOGGING CONFIG - ini file
	protected $_PROD_FILE;
	protected $_NOSTORE_FILE;
	protected $_NOLIST_FILE;
	protected $_LOG_FILE;
	protected $_LOG_PATH;
	protected $_LOG_SIZE;
	protected $_LOG_APPEND;
	protected $_LOG_EOL;
	protected $_LOG_DEBUG;
	
    // FILE POINTERS
    private $fp1;
    private $fp2;
    
    protected $_Logger;
     
    public function __construct() { 
    	$this->initialize();
    	$this->readIniFile();
    }
    
    private function initialize() {
    	$this->fp1 = null;
    	$this->fp2 = null;
    	
    	$this->_Logger = new Logger();
    }
    
    private function readIniFile() {
    	$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
    
    	// EXTERNAL FILES - ini file
    	$this->_PROD_FILE 		= $ini['products_file'];
    	$this->_NOSTORE_FILE 	= $ini['nostore_file'];
    	$this->_NOLIST_FILE 	= $ini['nolist_file'];
    	
    	// LOGGING CONFIG - ini file
    	$this->_LOG_FILE		= $ini['log_file'];
    	$this->_LOG_PATH 		= $ini['log_path'];
    	$this->_LOG_SIZE 		= $ini['log_size'];
    	$this->_LOG_APPEND 		= $ini['log_append'];
    	$this->_LOG_EOL 		= $ini['log_endline'];
    	$this->_LOG_DEBUG 		= $ini['log_debug'];
    }
    
    public function getLastStoreTimestamp() {
    	$t = null;
    	
    	try {
    		$db = database::getInstance();
    		$mysqli = $db->getConnection();
    		$sql = "SELECT * FROM wise_products_timestamp ORDER BY id DESC LIMIT 1";
    		$result = $mysqli->query($sql);
    		$t = mysqli_fetch_assoc($result)['last_update'];
    	} catch(Exception $e) {
    		if ($this->_Logger->isDebugOn()) {
    			$this->_Logger->writeLogFile("[DEBUG] - [reports] getLastStoreTimestamp() - ERROR:  " . utf8_encode(Messages::RESULT_ERROR));
    		}
    	}
    	return $t;
    }
    
    // Lista de Productos que no existen en la Tienda y figuran en la lista de Productos
    public function generateNoStoreReport($storeList) {
    	$this->openNoStoreFile();
		
		$countTotal = 0;
		$countUpdated = 0;
   		$countNoStore = 0;
   		
    	for ($i = 0; $i < count($storeList); $i++) {
    		$product = $storeList[$i]->getProduct();

    		if ($product[0]['value'] == -1) {
    			$id				= $product[0]['value'];
    			$title			= $product[2]['value'];
    			$sku			= $product[4]['value'];
    			$regular_price 	= $product[6]['value'];
    			$sale_price 	= $product[7]['value'];
    			
    			$message = $sku . "," . $title . "," . $sale_price . ",";
    			
    			fwrite($this->fp1, "$message" . $this->_LOG_EOL);
    			$countNoStore += 1;
    		} else {
    			$countUpdated += 1;
    		}
    		$countTotal += 1;
		}
		
		$this->closeNoStoreFile();
    	 
    	if ($this->_Logger->isDebugOn()) {
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoStoreReport() - Productos Que No Existen en Tienda: " . $countNoStore);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoStoreReport() - Fichero Creado " . $this->_NOSTORE_FILE);
    	}
    	
    	$response = array(
				'NoStore' => $countNoStore, 
    			'Updated' => $countUpdated,
				'TotalList' => $countTotal
		);
		
		return $response;
    }
    
    // Lista de Productos que no fueron actualizados porque no figuran en la lista de Productos
    public function generateNoListReport($store) {
    	$this->openNoListFile();
    	
    	// Cantidad Total De Productos en la Tienda
    	$countTotalStore = 0;
    	
    	// Cantidad Total De Productos NUNCA actualizados
    	$countTotalNeverList = 0;
    	
    	// Cantidad Total De Productos NO actualizados en la ultima lista
    	$countTotalNoList = 0;
    	
    	// Cantidad Total De Productos NO actualizados
    	$countTotalNotUpdated = 0;
    	
    	// Cantidad Total De Productos ACTUALIZADOS
    	$countTotalUpdated = 0;
    	
    	// Texto adicional para productos no actualizados
    	$text = '';
    	
    	$results = array();
    	
    	$lastStoreTimestamp = $this->getLastStoreTimestamp();
   		
    	for ($i = 0; $i < count($store); $i++) {
    		$productFound = 0;
    		
    		// Get Timestamp
    		$productTimestamp = $store[$i]->getProduct()[11]['value'];
    		
    		if ($productTimestamp != $lastStoreTimestamp) {
    			// Producto de Tienda Online no fue actualizado en la ultima operacion
    			
    			if (isset($productTimestamp)) {
    				// Producto de Tienda Online NO actualizado en la ultima operacion
    				$countTotalNoList += 1;
    				$text = 'No figura en lista';
    			} else {
    				// Producto de Tienda Online NUNCA actualizado
    				$countTotalNeverList += 1;
    				$text = 'Nunca actualizado';
    			}
    			
    			$results[] = $store[$i]->getProduct();
    			
    			// Print Product to NoList file
    			$message = 	$store[$i]->getProduct()[0]['value'] . "," . 
    						$store[$i]->getProduct()[2]['value'] . "," .
    						$store[$i]->getProduct()[4]['value'] . "," . $text;
    			
    			fwrite($this->fp2, "$message" . $this->_LOG_EOL);
    		} else {
    			// Producto de Tienda Online ACTUALIZADO
    			$countTotalUpdated +=1;
    		}
    		$countTotalStore += 1;
    	}
    	
    	$countTotalNotUpdated = $countTotalNoList + $countTotalNeverList;
    	
    	$this->closeNoListFile();
 	
    	if ($this->_Logger->isDebugOn()) {
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos en Tienda: " . $countTotalStore);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos Actualizados: " . $countTotalUpdated);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos NO Actualizados (TOTAL): " . $countTotalNotUpdated);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos NO Actualizados (NO FIGURAN EN LISTA): " . $countTotalNoList);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Productos NUNCA Actualizados: " . $countTotalNeverList);
    		$this->_Logger->writeLogFile("[DEBUG] - [reports] generateNoListReport() - Fichero Creado " . $this->_NOLIST_FILE);
    	}
    	
    	$response = array(
				'TotalStore' => $countTotalStore, 
				'TotalUpdated' => $countTotalUpdated,
    			'TotalNotUpdated' => $countTotalNotUpdated,
    			'NotUpdatedCurrent' => $countTotalNoList,
    			'NotUpdatedNever' => $countTotalNeverList
		);
		
		return $response;
    }
    
    private function openNoStoreFile() {
    	if (!is_resource($this->fp1))
    		$this->fp1 = fopen ($this->_NOSTORE_FILE , 'w');
    }
    
    private function closeNoStoreFile() {
    	if (is_resource($this->fp1)) {
    		fclose($this->fp1);
    		$this->fp1 = NULL;
    	}
    }
    
    private function openNoListFile() {
    	if (!is_resource($this->fp2))
    		$this->fp2 = fopen ($this->_NOLIST_FILE , 'w');
    }
    
    private function closeNoListFile() {
    	if (is_resource($this->fp2)) {
    		fclose($this->fp2);
    		$this->fp2 = NULL;
    	}
    }
    
    public function __destruct() {
     	$this->_NOLIST_FILE = NULL;
     	$this->_NOSTORE_FILE = NULL;
     	$this->fp1 = NULL;
     	$this->fp2 = NULL;
    }
    
}
?> 