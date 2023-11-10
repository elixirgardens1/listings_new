<?php
/*
http://192.168.0.24/listings_new/incs/query_listings_db.php
*/

$db_listings = new PDO('sqlite:listings.db3');

// $sql = "DELETE FROM `multi_cpu` WHERE rowid = 497";
// $db_listings->query($sql);

// $sql = "DELETE FROM `listings` WHERE rowid > 12570";
// $db_listings->query($sql);

$sql = "SELECT rowid,* FROM `changes` ORDER BY `rowid` DESC LIMIT 3";
// $sql = "SELECT * FROM `changes` WHERE `changes` LIKE('%{pn}%') AND `changes` LIKE('%{v}%')  AND `changes` NOT LIKE('%{pb}%')";
$results = $db_listings->query($sql);
$results_changes = $results->fetchAll(PDO::FETCH_ASSOC);

//DEBUG
echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($results_changes); echo '</pre>'; die();



$sql = "SELECT rowid,* FROM `multi_cpu` ORDER BY `rowid` DESC LIMIT 3";
$results = $db_listings->query($sql);
$results_multi_cpu = $results->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT rowid,* FROM `listings` ORDER BY `rowid` DESC LIMIT 10";
$results = $db_listings->query($sql);
$results_listings = $results->fetchAll(PDO::FETCH_ASSOC);


//DEBUG
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($results_multi_cpu); echo '</pre>';
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($results_listings); echo '</pre>';


$platforms = [
	'ebay',	
	'amazon',
	'web',
	'floorworld',
	'prosalt',
	'onbuy',
];

// foreach( $platforms as $platform ){
// 	if( 'prime' == $platform ){ continue; }
	
// 	$sql = "DELETE FROM `listings_$platform` WHERE `id` > 13079";
// 	$db_listings->query($sql);
// }



foreach( $platforms as $platform ){
	$sql = "SELECT rowid,* FROM `listings_$platform` ORDER BY `rowid` DESC LIMIT 6";
	$results = $db_listings->query($sql);
	$results = $results->fetchAll(PDO::FETCH_ASSOC);
	
	echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r("listings_$platform:\n"); print_r($results); echo '</pre>';
}
