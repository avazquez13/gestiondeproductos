<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class Product {
	
	// Labels
	const _lbid 			= 'Id';
	const _lbmodel 			= 'Model';
	const _lbtitle			= 'Title';
	const _lbstatus			= 'Status';
	const _lbsku			= 'SKU';
	const _lblist_price		= 'List Price';
	const _lbregular_price	= 'Regular Price';
	const _lbsale_price		= 'Sale Price';
	const _lbstock			= 'Stock';
	const _lbop_timestamp	= 'Operation Timestamp';
	const _lbparent_sku		= 'Parent SKU';
	
	// Product List Array
    protected $aProduct;
     
    public function __construct() {
    	$this->aProduct = array();
    }
    
    public function getProduct() {
    	return $this->aProduct;
    }
    
    public function setProduct(
    	$id,
    	$model,
    	$title,
    	$status,
    	$sku,
    	$list_price,
    	$regular_price,
    	$sale_price,
    	$stock,
    	$op_timestamp,
    	$parent_sku) {
    		
    	$this->aProduct = array(
    		array('name' => self::_lbid,		 	'value'	=> $id),
    		array('name' => self::_lbmodel, 		'value'	=> $model),
    		array('name' => self::_lbtitle, 		'value'	=> $title),
    		array('name' => self::_lbstatus, 		'value'	=> $status),
    		array('name' => self::_lbsku,	 		'value'	=> $sku),
    		array('name' => self::_lblist_price, 	'value'	=> $list_price),
    		array('name' => self::_lbregular_price, 'value'	=> $regular_price),
    		array('name' => self::_lbsale_price, 	'value'	=> $sale_price),
    		array('name' => self::_lbstock, 		'value'	=> $stock),
    		array('name' => self::_lbop_timestamp, 	'value'	=> $op_timestamp),
    		array('name' => self::_lbparent_sku, 	'value'	=> $parent_sku),
    	);
    }
    
    public function productToString() {
    	return json_encode($this->aProduct);
    }
}
?> 