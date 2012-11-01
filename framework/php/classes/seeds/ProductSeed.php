<?php
/**
 * Product information and manipulation
 *
 * @package platform.org.cashmusic
 * @author CASH Music
 * @link http://cashmusic.org/
 *
 * Copyright (c) 2012, CASH Music
 * Licensed under the Affero General Public License version 3.
 * See http://www.gnu.org/licenses/agpl-3.0.html
 *
 **/
class ProductSeed {
	protected $dbseed,$product_sku;

	public function __construct($dbseed,$product_sku) {
		$this->dbseed = $dbseed;
		$this->product_sku = $product_sku;
	}
	
	public function getInfo() {
		$query = "SELECT * FROM commerce_products WHERE sku = '{$this->product_sku}'";
		return $this->dbseed->doQueryForAssoc($query);
	}

	public function getQtySold() {
		$query = "SELECT id FROM commerce_transactions WHERE product_sku = '{$this->product_sku}'";
		return $this->dbseed->doQueryForCount($query);
	}

	public function getAvailability() {
		$qty_sold = $this->getQtySold();
		$query = "SELECT qty_total FROM commerce_products WHERE sku = '{$this->product_sku}'";
		$result = $this->dbseed->doQueryForAssoc($query);
		if ($result['qty_total'] == -1) {
			return true;
		} else {
			if ($result['qty_total'] > $qty_sold) {
				return true;
			} else {
				return false;
			}
		}
	}
} // END class 
?>