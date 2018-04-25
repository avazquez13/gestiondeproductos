<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class productList {
	protected $aProductList;
	protected $numObjects;
	protected $iterateNum;
	protected $resetFlag;
	
	public function __construct() {
		$this->resetIterator();
		$this->numObjects = 0;
		$this->aProductList = array();
	}
	
	public function addProduct($product) {
		$this->aProductList[] = $product;
		$this->numObjects++;
	}
	
	public function getProduct($key) {
		return $this->aProductList[$key];
	}
	
	public function getProductList() {
		return $this->aProductList;
	}
	
	public function setProductList($aProducts) {
		$this->aProductList = $aProducts;
	}
	
	public function getNumObjects() {
		return $this->numObjects;
	}
	
	public function getCurrent() {
		return $this->aProductList[$this->iterateNum];
	}	
	
	public function setCurrent($obj) {
		$this->aProductList[$this->iterateNum] = $obj;
	}
	
	public function isEmpty() {
		return ($this->numObjects == 0);
	}
	
	public function next() {
		$num = ($this->currentObjIsLast()) ? 0 : $this->iterateNum + 1;
		$this->iterateNum = $num;
	}
	
	public function getLast() {
		return $this->aProductList[$this->numObjects-1];
	}
	
	public function currentObjIsFirst() {
		return ($this->iterateNum == 0);
	}	
	
	public function currentObjIsLast() {
		return (($this->numObjects-1) == $this->iterateNum);
	}	
	
	public function getObjNum($num) {
		return (isset($this->aProductList[$num])) ? $this->aProductList[$num] : false;
	}
	
	public function iterate() {
		if ($this->iterateNum < 0) {
			$this->iterateNum = 0;
		}
		
		if ($this->resetFlag) {
			$this->resetFlag = false;
		} else {
			$this->iterateNum++;
		}
		
		if ($this->iterateNum == $this->numObjects || !isset($this->aProductList[$this->iterateNum])) {
			$this->resetIterator();
			return false;
		}
	
		return $this->getCurrent();
	}
	
	public function resetIterator() {
		$this->iterateNum = 0;
		$this->resetFlag = true;
	}	
	
	public function __toString() {
		$str = '';
		foreach ($this->objects as $obj) {
			$str .= '--------------------------<br />'.$obj.'<br />';
		}
		return $str;
	}
}


?>