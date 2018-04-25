<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
abstract class BaseClient {
	
	// API CALLS
	const CREATE_ENTRY_API		= "createEntryApi";
	const GET_ENTRY_API			= "getEntryApi";
	const GET_ENTRY_LIST_API	= "getEntryListApi";
	const SET_ENTRY_API			= "setEntryListApi";
	
	// PRODUCT API CALLS
	const GET_PRODUCT_DATA_API			= "getProductDataApi";
	const UPDATE_PRODUCTS_API			= "updateProductsApi";
	const GET_PRODUCTS_NOT_STORE_API 	= "getProductsNotInStoreApi"; // Products that are in the LIST but do not exist in the STORE
	const GET_PRODUCTS_NOT_FILE_API 	= "getProductsNotInFileApi";  // Products that are in the STORE but do not exist in the LIST
	
	// Operation STATUS
	const _OP_SUCCEDED 	= 0;
	const _OP_FAILED 	= 1;
	const _OP_DISCARDED = 2;
	
	// APPLICATION CONFIG - ini file
	protected $_APPNAME;
	protected $_APPVERSION;
	protected $_APPEMAIL;
	protected $_USERNAME;
	protected $_PASSWORD;
	protected $_CLIENTKEY;
	protected $_SECRETKEY;
	protected $_STORE_URL;
	
	// LOGGING OBJECT
	protected $_Logger;
	
	// OPERATION COUNTERS
	protected $countTOTAL;
	protected $countSUCCESS;
	protected $countFAIL;
	protected $countDISCARD;
	
	// CLIENT REQUEST
	protected $_PARAMS;
	protected $_APICALL;			// API Call to process
	protected $_MODEL;				// Product Model to Update (TODOS=ALL)
	protected $_MARGIN;				// Product Margin to apply to List Price (Default=15%)
	protected $_DISCOUNT;			// Product Discount for online store (Default=20%)
	protected $_UPDATESTOCK;		// If Product Stock quantity must be updated (0=yes | -1=no)
	//protected $_PRODUCTFIELDS;	// Product Fields (array)
	protected $_TIMESTAMP;			// PROCESS TIMESTAMP
	
	// CLIENT RESPONSE
	protected $_RESPONSE;
	
	public function __construct() {
		$this->initialize();
		$this->readIniFile();
		
		if ($this->_Logger->isDebugOn()) {
			$this->_Logger->writeLogFile("[DEBUG] - [baseClient] _Construct - Client Started for User: " . $this->_USERNAME);
		}
	}
	
	protected function initialize() {
		$this->_RESPONSE = NULL;
		$this->_PARAMS = NULL;
		$date = new DateTime();
		$this->setProcessTimestamo($date->getTimestamp());
		
		$this->countTOTAL 	= 0;
		$this->countSUCCESS = 0;
		$this->countFAIL 	= 0;
		$this->countDISCARD = 0;
		
		$this->_Logger = new Logger();
	}
	
	protected function readIniFile() {
		$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
		
		// APPLICATION CONFIG
		$this->_APPNAME 	= $ini['app_name'];
		$this->_APPVERSION 	= $ini['app_version'];
		$this->_APPEMAIL 	= $ini['app_email'];
		$this->_USERNAME 	= $ini['app_user'];
		$this->_PASSWORD 	= $ini['app_pass'];
		$this->_CLIENTKEY 	= $ini['client_key'];
		$this->_SECRETKEY 	= $ini['secret_key'];
		$this->_STORE_URL 	= $ini['store_url'];
	}
	
	public function getParams() {
		return $this->_PARAMS;
	}
	
	public function setParams($params) {
		$this->_PARAMS = $params;
		
		$this->setApiCall($this->_PARAMS['apicall']);
		$this->setModel($this->_PARAMS['model']);
		$this->setMargin($this->_PARAMS['margin']);
		$this->setDiscount($this->_PARAMS['discount']);
		$this->setUpdateStock($this->_PARAMS['updateStock']);
	}
	
	public function getApiCall() {
		return $this->_APICALL;
	}
	
	public function setApiCall($Api) {
		$this->_APICALL = $Api;
	}
	
	public function getModel() {
		return $this->_MODEL;
	}
	
	public function setModel($model) {
		$this->_MODEL = $model;
	}
	
	public function getMargin() {
		return $this->_MARGIN;
	}
	
	public function setMargin($margin) {
		$this->_MARGIN = $margin;
	}
	
	public function getDiscount() {
		return $this->_DISCOUNT;
	}
	
	public function setDiscount($discount) {
		$this->_DISCOUNT = $discount;
	}
	
	public function getUpdateStock() {
		return $this->_UPDATESTOCK;
	}
	
	public function setUpdateStock($updateStock) {
		$this->_UPDATESTOCK = $updateStock;
	}
	
	public function isUpdateStock() {
		if ($this->_UPDATESTOCK > -1)
			return true;
		else
			return false;
	}
	
	public function getProcessTimestamp() {
		return $this->_TIMESTAMP;
	}
	
	public function setProcessTimestamo($time) {
		$this->_TIMESTAMP = $time;
	}
		
	abstract protected function updateProductsApiCall();
	abstract protected function getProductDataApiCall();
	abstract protected function getProductsNotInStoreApiCall();
	abstract protected function getProductsNotInFileApiCall();
	
	public function exec() {
		$api = $this->getApiCall();
		
		if ($this->_Logger->isDebugOn()) {
			$this->_Logger->writeLogFile("[DEBUG] - [baseClient] exec() - APICALL: " . $api);
		}
		
		switch ($api) {
			case self::UPDATE_PRODUCTS_API:
				$this->setApiCall(self::UPDATE_PRODUCTS_API);
				$this->updateProductsApiCall();
				break;
			case self::GET_PRODUCT_DATA_API:
				$this->setApiCall(self::GET_PRODUCT_DATA_API);
				$this->getProductDataApiCall();
				break;
			case self::GET_PRODUCTS_NOT_STORE_API:
				$this->setApiCall(self::GET_PRODUCTS_NOT_STORE_API);
				$this->getProductsNotInStoreApiCall();
				break;
			case self::GET_PRODUCTS_NOT_FILE_API:
				$this->setApiCall(self::GET_PRODUCTS_NOT_FILE_API);
				$this->getProductsNotInFileApiCall();
				break;
			case "":
				http_response_code(400);
				echo utf8_encode(Messages::MISSING_APICALL);
				return FALSE;
			default:
				http_response_code(400);
				echo utf8_encode(Messages::MISSING_APICALL);
				return FALSE;
		}
	}
	
}
?>