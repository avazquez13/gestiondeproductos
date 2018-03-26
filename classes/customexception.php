<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */

class CustomException extends Exception {
	protected $message;
	protected $code;
	
	protected $_Logger;
	
	public function __construct($error) {
		$this->message = $error['message'];
		$this->code = $error['code'];
		
		parent::__construct($this->message, $this->code, NULL);
		
		$this->logException();		
	}
	
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
	
	public function logException() {
		$this->_Logger = new Logger();
        $this->_Logger->writeLogFile("[ERROR] - [CUSTOM EXCEPTION] logException -  " . $this->__toString());
	}
	
}
?>