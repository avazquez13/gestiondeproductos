<?php

/*
 * Copyright © 2016 Wise Solutions S.A.
 * All rights reserved.
 *
 * This software is the confidential property and proprietary information of
 * Wise Solutions S.A.
 */
 
class routines {
	public static function debug_to_console($data) {
		$output = $data;
		
		if (is_array( $output))
			$output = implode(',', $output);
	
		echo "<DEBUG>console.log('Debug Data: " . $output . "');</DEBUG>";
	}
	
	public static function calculateSalePrice($listPrice, $margin) {
		$price = floatval($listPrice);
		$gain = (int) $margin;
		$salePrice = $price + ($price * $gain / 100);
		return $salePrice;
	}
	
	public static function calculateRegularPrice($salePrice, $discount) {
		$off = (int) $discount;
		$sale = floatval($salePrice);
	
		switch ($off) {
			case 5:
				// Discount 5% - Add 5.3%
				$price = $sale * 1.053;
				break;
			case 10:
				// Discount 10% - Add 11%
				$price = $sale * 1.11;
				break;
			case 15:
				// Discount 15% - Add 18%
				$price = $sale * 1.18;
				break;
			case 20:
				// Discount 20% - Add 25%
				$price = $sale * 1.25;
				break;
			case 25:
				// Discount 25% - Add 34%
				$price = $sale * 1.34;
				break;
			case 30:
				// Discount 30% - Add 43%
				$price = $sale * 1.43;
				break;
			case 35:
				// Discount 35% - Add 54%
				$price = $sale * 1.54;
				break;
			case 40:
				// Discount 40% - Add 68%
				$price = $sale * 1.68;
				break;
			case 45:
				// Discount 45% - Add 82%
				$price = $sale * 1.82;
				break;
			case 50:
				// Discount 50% - Add 100%
				$price = $sale * 2;
				break;
			default:
				// Discount 20% - Add 25%
				$price = $sale * 1.25;
				break;
		}
		return $price;
	}
	
	public static function getBrandName($brand) {
		$brandName = '';
		
		switch ($brand) {
			case 1:
				// Hankook
				$brandName = 'Hankook';
				break;
			case 2:
				// Linglong
				$brandName = 'Linglong';
				break;
			case 3:
				// Yokohama
				$brandName = 'Yokohama';
				break;
		}
		
		return $brandName;
	}
}
?>