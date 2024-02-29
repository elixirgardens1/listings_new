<?php
/*
http://localhost/listings_new/dbase/make_price_change_tbl.php

http://192.168.0.24/FESP-REFACTOR/ 
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
// set_time_limit(30);
// ini_set("memory_limit", "-1");

$db = new PDO('sqlite:listings_NEW.db3');
$db_archive = new PDO('sqlite:archive/listings_NEW.db3');


$sql = "DROP TABLE `price_change`";
$db->query($sql);



$db->exec(
	"CREATE TABLE IF NOT EXISTS `price_change` (
		'id' INT,
		'platform' TEXT,
		'change' TEXT,
		`user` INT,
		`timestamp` INT
	)"
);

$price_change_hc = [
    ['11470','e','13.99>14.99','4','1661844099'],
    ['11471','e','17.99>21.49','4','1661844099'],
    ['11472','e','21.99>25.99','4','1661844099'],
    ['11473','e','24.99>29.99','4','1661844099'],
    ['11483','e','15.99>17.69','4','1661844140'],
    /* etc */
];

$stmt = $db->prepare("INSERT INTO `price_change` VALUES (?,?,?,?,?)");
$db->beginTransaction();
foreach ($price_change_hc as $i => $rec) {
	$stmt->execute([$rec[0], $rec[1], $rec[2], $rec[3], $rec[4]]);
}
$db->commit();
die();












/*
$sql = "SELECT * FROM `price_change`";
$res = $db->query($sql);
$price_change = $res->fetchAll(PDO::FETCH_NUM);

$arr = [];
$arr[] = "\$price_change_hc = [";
foreach ($price_change as $rec) {
	$arr[] = "    ['{$rec[0]}','{$rec[1]}','{$rec[2]}','{$rec[3]}','{$rec[4]}'],";
}
$arr[] = "];";

echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r(implode("\n", $arr)); echo '</pre>'; die();

die();
*/





$fresh = false;

if (!$fresh) {
	$sql = "SELECT * FROM `price_change`";
	$res = $db->query($sql);
	$price_change = $res->fetchAll(PDO::FETCH_NUM); // 22323
	// $first_ts = $price_change[0]['timestamp'];
	$first_ts = $price_change[0][4];
}

if ($fresh) {
	$sql = "SELECT * FROM `changes` WHERE `changes` LIKE '%{np}%'";
	$res = $db->query($sql);
}
else {
	$ts_cutoff = strtotime(date('Y-m-d'). ' - 18 months');
	$sql = "SELECT * FROM `changes` WHERE `timestamp` < $first_ts AND `timestamp` > $ts_cutoff AND `changes` LIKE '%{np}%'";
	$res = $db_archive->query($sql);
}

$changes = $res->fetchAll(PDO::FETCH_ASSOC); // 57973

$price_change_data = [];
foreach ($changes as $rec) {
	$id = $rec['id'];
	
	if( preg_match('/([a-z])\{np\}/', $rec['changes'], $m) ){
		$platform = $m[1];
	}
	
	list(, $price) = explode('{np}', $rec['changes']);
	list($price,) = explode('{', $price);
	
	$user = $rec['user'];
	$ts = $rec['timestamp'];
	$price_change_data[] = [$id, $platform, $price, $user, $ts];
}

if (!$fresh) {
	$price_change_data = array_merge($price_change_data, $price_change);
	// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($price_change_data); echo '</pre>'; die();
}


$db->query("DELETE FROM `price_change`");

$stmt = $db->prepare("INSERT INTO `price_change` VALUES (?,?,?,?,?)");
$db->beginTransaction();
foreach ($price_change_data as $i => $rec) {
	$stmt->execute([$rec[0], $rec[1], $rec[2], $rec[3], $rec[4]]);
}
$db->commit();

// 1704702287

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($price_change_data); echo '</pre>'; die();
