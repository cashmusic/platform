<?php
$hostname = "";
$username = "";
$password = "";
$database = "";
// connect
$dblink = mysql_connect($hostname,$username,$password) or die("Unable to connect to database");
mysql_select_db($database, $dblink) or die("Unable to select database");

function getProductInfo($product_code) {
	global $dblink;
	$query = "SELECT * FROM product WHERE code = '$product_code'";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$productinfo = mysql_fetch_assoc($result);
		return $productinfo;
		mysql_free_result($result);
	} else {
		//echo "anything";
		return false;
	}
}

function getProductQtySold($product_code) {
	global $dblink;
	$query = "SELECT * FROM transaction WHERE product_code = '$product_code'";
	$result = mysql_query($query,$dblink);
	$rowcount = mysql_num_rows($result);		
	updateProductQtySold($product_code,$rowcount);
	return $rowcount;
}

function updateProductQtySold($product_code,$new_qty) {
	global $dblink;
	$query = "UPDATE product SET qty_sold = $new_qty WHERE code = '$product_code'";
	if (mysql_query($query,$dblink)) {
		return true;
	}
}

function getProductAvailability($product_code) {
	global $dblink;
	//getProductQtySold($product_code);
	$query = "SELECT qty_total,qty_sold FROM product WHERE code = '$product_code'";
	$result = mysql_query($query,$dblink);
	$row = mysql_fetch_assoc($result);
	if ($row['qty_total'] == -1) {
		return true;
	} else {
		if ($row['qty_total'] > $row['qty_sold']) {
			return true;
		} else {
			return false;
		}
	}
}

function getAllProductQtySold() {
	global $dblink;
	$query = "SELECT * FROM product ORDER BY beneficiary ASC, code ASC";
	$result = mysql_query($query,$dblink);
	if (mysql_num_rows($result)) { 
		$full_list = "<table>";
		$lastbenefactor = "";
		while ($row = mysql_fetch_assoc($result)) {
			if ($row['beneficiary'] != $lastbenefactor) {
				$lastbenefactor = $row['beneficiary'];
				if (!empty($lastbenefactor)) {
					"";
				}
				$full_list = $full_list .  "<tr><td colspan=\"3\"><br /><b>$lastbenefactor</b></td></tr>";
			}
			$qtySold = getProductQtySold($row['code']);
			$full_list = $full_list . "<tr><td class=\"rpad2em\"><small>{$row['code']}</small></td><td class=\"rpad2em\">{$row['title']}</td><td class=\"tar\">$qtySold</td></tr>";
			updateProductQtySold($row['code'],$qtySold);
		}
		$full_list = $full_list .  "</table>";
		echo $full_list;
	}
}
?>