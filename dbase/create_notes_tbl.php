<?php
/*
http://localhost/listings_new/dbase/create_notes_tbl.php

http://192.168.0.24/FESP-REFACTOR/ 
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$db = new PDO('sqlite:listings_NEW.db3');

$sql = "DROP TABLE IF EXISTS `notes`";
$db->query($sql);

$db->exec(
	"CREATE TABLE IF NOT EXISTS `notes` (
		`cat_id` TEXT,
	    `group_` TEXT,
	    `note` TEXT,
	    `source` TEXT,
	    `timestamp` INT
	)"
);


$notes = [
    ['a8','0','Test note for Aggregates/Aggregates - Group 1 (ebay)','e','1708687414'],
    ['a8','1','Test note for Aggregates/Aggregates - Group 2 (ebay)','e','1708692726'],
    ['a8','0','Test note for Aggregates/Aggregates - Group 1 (amazon)','a','1708692765'],
];

$stmt = $db->prepare("INSERT INTO `notes` VALUES (?,?,?,?,?)");
$db->beginTransaction();
foreach ($notes as $i => $rec) {
	$stmt->execute([$rec[0], $rec[1], $rec[2], $rec[3], $rec[4]]);
}
$db->commit();
