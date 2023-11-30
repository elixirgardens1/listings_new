<?php
/*
http://localhost/listings_new/dbase/update_db.php


http://192.168.0.24/listings_new/dbase/update_db.php
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


$db_sqlite = new PDO('sqlite:listings_NEW.db3');

$sql = "UPDATE listings_web SET new_price = (SELECT new_price FROM listings_ebay WHERE listings_web.id = listings_ebay.id)";
$db_sqlite->beginTransaction();
$db_sqlite->query($sql);
$db_sqlite->commit();
