<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class Logger{
    protected $file;
    protected $size;
    protected $content;
    protected $writeFlag;
    protected $endRow;
    protected $isDebug;
    protected $fp;
     
    public function __construct() { 
    	$ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/gestiondeproductos/config/app.ini');
        $this->file 		= $ini['log_file'];
        $this->size 		= $ini['log_size'];
        $this->writeFlag 	= $ini['log_append'];
        $this->endRow 		= $ini['log_endline'];
        $this->isDebug		= $ini['log_debug'];
    }
    
    public function isDebugOn() {
    	return $this->isDebug;
    }
    
    public function writeLogFile($message){
    	if (!file_exists($this->file))
            $this->flushLogFile(null, true);
    	else
    		$this->flushLogFile($message, false);
    }
    
    public function flushLogFile($message, $isNew) {
    	$this->openLogFile();
    	 
    	if ($isNew) {
    		fwrite($this->fp, "WISE Solutions INTEGRATION APPLICATION Logging Started..." . $this->endRow);
    	} else {
    		$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
    		$now = new DateTime();
    		$tz = new DateTimeZone("America/Argentina/Buenos_Aires");
    		date_timezone_set($now, $tz);
    		$time = $now->format('[d-M-Y:H:i:s]');
    		//ad level
    		fwrite($this->fp, "$time [$script_name] $message" . $this->endRow);
    	}
    	$this->closeLogFile();
    }
    
    public function openLogFile() {
    	if (!is_resource($this->fp))
    		$this->fp = fopen ($this->file , 'a+');
    }
    
    public function closeLogFile() {
    	if (is_resource($this->fp)) {
    		fclose($this->fp);
    		$this->fp = NULL;
    	}
    }
        
    public function __destruct() {
     	$this->closeLogFile();
     	$this->file = NULL;
     	$this->fp = NULL;
     }
    
}
?> 