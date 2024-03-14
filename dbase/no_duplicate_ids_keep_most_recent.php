<?php
/*
http://localhost/ELIXIR/listings_new/dbase/no_duplicate_ids_keep_most_recent.php

http://192.168.0.24/FESP-REFACTOR/ 
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$db = new PDO('sqlite:listings_NEW.db3');

$sql = "SELECT * FROM `price_change` ORDER BY `rowid` DESC";
$res = $db->query($sql);
$price_change = $res->fetchAll(PDO::FETCH_ASSOC); // FETCH_ASSOC FETCH_COLUMN FETCH_KEY_PAIR FETCH_NUM

$tmp = [];
$price_change_data = [];
foreach ($price_change as $rec) {
	$id_platform = $rec['id'].$rec['platform'];
	if (!isset($tmp[$id_platform])) {
		// $price_change_data[] = [
		// 	'id'        => $rec['id'],
		// 	'platform'  => $rec['platform'],
		// 	'change'    => $rec['change'],
		// 	'user'      => $rec['user'],
		// 	'timestamp' => $rec['timestamp'],
		// ];
		$price_change_data[] = $rec;
		$tmp[$id_platform] = 1;
	}
}

function sortByTimestamp($a, $b) {
	return $a['timestamp'] - $b['timestamp'];
}
usort($price_change_data, 'sortByTimestamp');

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($price_change_data); echo '</pre>'; die();





$sql = "DELETE FROM `price_change`";
$db->query($sql);

$stmt = $db->prepare("INSERT INTO `price_change` (`id`,`platform`,`change`,`user`,`timestamp`) VALUES (?,?,?,?,?)");

$db->beginTransaction();
foreach ($price_change_data as $rec) {
    // echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($rec); echo '</pre>'; die();
    $stmt->execute([$rec['id'],$rec['platform'],$rec['change'],$rec['user'],$rec['timestamp']]);
}
$db->commit();



// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r(count($price_change)); echo '</pre>';
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r(count($price_change_data)); echo '</pre>';
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($price_change_data); echo '</pre>';

