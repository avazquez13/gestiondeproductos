<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class Product{
	
	// Labels
	const _lbid 			= 'Id';
	const _lbname 			= 'Name';
	const _lbtitle			= 'Title';
	const _lbstatus			= 'Status';
	const _lbsku			= 'SKU';
	const _lbprice			= 'Price';
	const _lbregular_price	= 'Regular Price';
	const _lbsale_price		= 'Sale Price';
	const _lbstock			= 'Stock';
	const _lbupdate_stock	= 'Update Stock?';
	const _lbop_status		= 'Operation Status';
	const _lbop_timestamp	= 'Operation Timestamp';
	
	// Operation STATUS
	const _OP_SUCCESS 	= 0;
	const _OP_FAILED 	= 1;
	const _OP_DISCARDED = 2;
	
	// Product List Array
    private $aProduct;
     
    public function __construct() {
    	$this->aProduct = array();
    }
    
    public function getProduct() {
    	return $this->aProduct;
    }
    
    public function setProduct(
    	$id,
    	$name,
    	$title,
    	$status,
    	$sku,
    	$price,
    	$regular_price,
    	$sale_price,
    	$stock,
    	$update_stock,
    	$op_status,
    	$op_timestamp) {
    		
    	$this->aProduct = array(
    		array('name' => self::_lbid,		 	'value'	=> $id),
    		array('name' => self::_lbname, 			'value'	=> $name),
    		array('name' => self::_lbtitle, 		'value'	=> $title),
    		array('name' => self::_lbstatus, 		'value'	=> $status),
    		array('name' => self::_lbsku,	 		'value'	=> $sku),
    		array('name' => self::_lbprice, 		'value'	=> $price),
    		array('name' => self::_lbregular_price, 'value'	=> $regular_price),
    		array('name' => self::_lbsale_price, 	'value'	=> $sale_price),
    		array('name' => self::_lbstock, 		'value'	=> $stock),
    		array('name' => self::_lbupdate_stock,	'value'	=> $update_stock),
    		array('name' => self::_lbop_status, 	'value'	=> $op_status),
    		array('name' => self::_lbop_timestamp, 	'value'	=> $op_timestamp),
    	);
    }
    
    public function productToString() {
    	return json_encode($this->aProduct);
    }
}
?> 