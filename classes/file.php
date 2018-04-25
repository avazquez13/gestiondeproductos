<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class File {
	// LOGGING CONFIG - ini file
	private $file;
	private $eol;
	
	// FILE POINTER
	private $fp;
	
	protected $_Logger;
	
	public function __construct() {
		$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
		$this->eol 	= $ini['log_endline'];
		$this->setFilePointer(null);
		$this->_Logger = new Logger();
	}
	
	public function getFile() {
		return $this->file;
	}
	
	public function setFile($file) {
		$this->file = $file;
	}
	
	public function getFilePointer() {
		return $this->fp;
	}
	
	public function setFilePointer($fp) {
		$this->fp = $fp;
	}
	
	public function openFileForRead() {
		if (file_exists($this->file)) {
			if (!is_resource($this->fp)) {
				$this->setFilePointer(fopen ($this->file , 'r'));
			}
		} else {
			return false;
		}
	}
	
	public function openFileForReadWrite() {
		if (!is_resource($this->fp)) {
			$this->setFilePointer(fopen ($this->file , 'w'));
		} else {
			return false;
		}
	}
	
	public function closeFile() {
		if (is_resource($this->fp)) {
			fclose($this->fp);
			$this->setFilePointer(null);
		}
	}
	
	public function __destruct() {
		$this->closeFile();
		$this->setFile(null);
	}
}


?>