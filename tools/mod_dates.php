<?php
/*
http://localhost/listings_new/tools/mod_dates.php

http://192.168.0.24/FESP-REFACTOR/ 
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// set_time_limit(30);
// ini_set("memory_limit", "-1");

$db_stock = new PDO('sqlite:../dbase/stock_control.db3');
$db_listings = new PDO('sqlite:../dbase/listings_NEW.db3');

$sql = "SELECT cat,name FROM `cats`";
$res = $db_stock->query($sql);
$stock = $res->fetchAll(PDO::FETCH_KEY_PAIR);

$sql = "SELECT cat,cat_id,product_cat FROM `lookup_prod_cats`";
$res = $db_listings->query($sql);
$product_cat = $res->fetchAll(PDO::FETCH_ASSOC);

$cat_subcat_lkup = [];
foreach ($product_cat as $rec) {
	$cat_subcat_lkup[$rec['cat_id']] = "{$stock[$rec['cat']]}/{$rec['product_cat']}";
}

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($cat_subcat_lkup); echo '</pre>'; die();
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($stock); echo '</pre>';
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($product_cat); echo '</pre>';

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r('_LISTINGS_'); echo '</pre>';


$listings = [];

$sql = "SELECT id_lkup,cat_id,product_name,timestamp FROM `listings`";
$res = $db_listings->query($sql);
$listings['base'] = $res->fetchAll(PDO::FETCH_ASSOC); // FETCH_ASSOC FETCH_COLUMN FETCH_KEY_PAIR FETCH_NUM

foreach (['amazon','ebay','web','prime'] as $platform) {
	$sql = "SELECT id,timestamp FROM `listings_$platform`";
	$res = $db_listings->query($sql);
	$listings[$platform] = $res->fetchAll(PDO::FETCH_KEY_PAIR);
}


/*
2023-11-20 | Aggregates/Aggregates | Blue Slate x 20kg | prim
2024-02-01 | Aggregates/Aggregates | Blue Slate x 20kg | ebay amaz webs
*/

$full_listings = [];
foreach ($listings['base'] as $i => $rec) {
	$full_listings[$rec['id_lkup']]['cat_id'] = $cat_subcat_lkup[$rec['cat_id']];
	$full_listings[$rec['id_lkup']]['product_name'] = $rec['product_name'];
	$full_listings[$rec['id_lkup']]['date'] = date("Y-m-d", $rec['timestamp']);
	$full_listings[$rec['id_lkup']][date("Y-m-d", $listings['amazon'][$rec['id_lkup']])][] = 'amazon';
	$full_listings[$rec['id_lkup']][date("Y-m-d", $listings['ebay'][$rec['id_lkup']])][] = 'ebay';
	$full_listings[$rec['id_lkup']][date("Y-m-d", $listings['web'][$rec['id_lkup']])][] = 'web';
	
	if (isset($listings['prime'][$rec['id_lkup']])) {
		$full_listings[$rec['id_lkup']][date("Y-m-d", $listings['prime'][$rec['id_lkup']])][] = 'prime';
	}
	unset($listings['base'][$i]['timestamp']);
}

foreach ($full_listings as $i => $recs) {
	foreach ($recs as $key => $rec) {
		if ('cat_id' != $key && 'product_name' != $key && 'date' != $key) {
			$full_listings[$i][$key] = implode('|', $rec);
		}
	}
}

$tmp = [];
foreach ($full_listings as $rec) {
	$tmp_ = " | {$rec['cat_id']} | {$rec['product_name']} | ";
	foreach ($rec as $key => $val) {
		if ('cat_id' != $key && 'product_name' != $key && 'date' != $key) {
			$date = $key;
			$plats = $val;
			
			$tmp[] = $date.$tmp_.$plats;
		}
	}
}
$full_listings = $tmp;
sort($full_listings);

echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($full_listings); echo '</pre>'; die();



/*
2023-11-20 | Aggregates/Aggregates | Blue Slate x 20kg | prim
2024-02-01 | Aggregates/Aggregates | Blue Slate x 20kg | ebay amaz webs
*/
