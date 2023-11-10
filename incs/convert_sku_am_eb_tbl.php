<?php
/*
http://192.168.0.24/LISTINGS_v3/convert_sku_am_eb_tbl.php

http://192.168.0.24:8080/stocksystem/PHPAPI/convert_sku_am_eb_tbl.php
*/

//DEBUG
echo '<pre style="background:#002; color:#fff;">'; print_r('TEST'); echo '</pre>'; die();

$db = new PDO('sqlite:stock_control.db3');

$sql = "SELECT * FROM `sku_am_eb`";
$results = $db->query($sql);
$sku_am_eb = $results->fetchAll(PDO::FETCH_ASSOC); // ::FETCH_NUM ::FETCH_COLUMN ::FETCH_KEY_PAIR


$db->query("DROP TABLE `sku_am_eb`");
$db->exec("CREATE TABLE IF NOT EXISTS sku_am_eb(sku TEXT, id TEXT, platform TEXT, PRIMARY KEY (sku, platform))");
$db->query("DELETE FROM `sku_am_eb`");

$stmt = $db->prepare("INSERT INTO `sku_am_eb` (`sku`,`id`,`platform`) VALUES (?,?,?)");

$db->beginTransaction();
foreach( $sku_am_eb as $rec ){
	if( '' != $rec['am_id'] ){
		$stmt->execute([ $rec['sku'], $rec['am_id'], 'a' ]);
	}
	if( '' != $rec['eb_id'] ){
		$stmt->execute([ $rec['sku'], $rec['eb_id'], 'e' ]);
	}
	if( '' != $rec['we_id'] ){
		$stmt->execute([ $rec['sku'], $rec['we_id'], 'w' ]);
	}
	if( '' != $rec['pr_id'] ){
		$stmt->execute([ $rec['sku'], $rec['pr_id'], 'p' ]);
	}
}
$db->commit();


// $db->query("DROP TABLE `sku_am_eb3`");
// $db->exec("CREATE TABLE IF NOT EXISTS sku_am_eb3(sku TEXT, id TEXT, platform TEXT, PRIMARY KEY (sku, platform))");

