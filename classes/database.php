<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class database {
	
	protected $_Logger;
	
	private static $_instance;
	
	// Database Connection
	private $_DBCONN;
	private $_DBHOST;
	private $_DBNAME;
	private $_DBUSER;
	private $_DBPASS;
	
	public static function getInstance() {
		if(!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	public function __construct() {
		$this->readIniFile();
		$this->initialize();
    }
    
    private function initialize() {
    	$this->_Logger = new Logger();
    	$this->_DBCONN = new mysqli($this->_DBHOST, $this->_DBUSER,	$this->_DBPASS, $this->_DBNAME);

		if(mysqli_connect_error()) {
			echo utf8_encode(Messages::DB_ERROR_CONNECT);
			
			if ($this->_Logger->isDebugOn()) {
				$this->_Logger->writeLogFile("[DEBUG] - [database] init - ERROR: " . utf8_encode(Messages::DB_ERROR_CONNECT) );
			}
		}
    }
    
    private function readIniFile() {
    	$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
    	$this->_DBHOST 		= $ini['db_host'];
    	$this->_DBNAME 		= $ini['db_name'];
    	$this->_DBUSER 		= $ini['db_user'];
    	$this->_DBPASS 		= $ini['db_pass'];
    }
    
    private function __clone() { }
    
    public function getConnection() {
    	return $this->_DBCONN;
    }
}
?> 