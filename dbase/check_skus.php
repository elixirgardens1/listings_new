<?php
/*
http://localhost/listings_new/dbase/check_skus.php

http://192.168.0.24/FESP-REFACTOR/ 
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// set_time_limit(30);
// ini_set("memory_limit", "-1");




$id_str = "'15398','15399','15400','15401','15402','15403','15404','15405','15406','15407','15408'";
$source = 'e';
query_tbl($id_str, $source);

$source = 'a';
query_tbl($id_str, $source);

$id_str = "'696','697','698','699','700','701','702','703','704','705'";
$source = 'a';
query_tbl($id_str, $source);


function query_tbl($id_str, $source)
{
	$db = new PDO('sqlite:listings_NEW.db3');
	$sql = "SELECT sku FROM `skus` WHERE `id` IN ($id_str) AND source = '$source'";
	$res = $db->query($sql);
	$steel_cane_2_ebay = $res->fetchAll(PDO::FETCH_COLUMN);
	
	$platform = [
		'e' => 'ebay',
		'a' => 'amazon',
	];
	
	echo "<pre style=\"background:#111; color:#b5ce28; font-size:11px;\">{$platform[$source]} - ";
	print_r($steel_cane_2_ebay);
	echo '</pre>';
}