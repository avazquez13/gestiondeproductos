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
	const UPDATE_PRODUCT_API	= "updateProductApi";
	const DIFF_PRODUCT_API		= "diffProductApi";
	
	// CLIENT PARAMETERS
	protected $_PARAMS;
	protected $_APICALL;
	protected $_TIMESTAMP;
	
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
		$this->_TIMESTAMP = $date->getTimestamp();
		
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
	}
	
	public function getApiCall() {
		return $this->_APICALL;
	}
	
	public function setApiCall($Api) {
		$this->_APICALL = $Api;
	}
	
	abstract protected function updateProductApiCall();
	abstract protected function diffProductApiCall();
	
	public function exec() {
		$api = $this->_PARAMS['apicall'];
		
		if ($this->_Logger->isDebugOn()) {
			$this->_Logger->writeLogFile("[DEBUG] - [baseClient] exec() - APICALL: " . $api);
		}
		
		switch ($api) {
			case self::UPDATE_PRODUCT_API:
				$this->setApiCall(self::UPDATE_PRODUCT_API);
				$this->updateProductApiCall();
				break;
			case self::DIFF_PRODUCT_API:
				$this->setApiCall(self::DIFF_PRODUCT_API);
				$this->diffProductApiCall();
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