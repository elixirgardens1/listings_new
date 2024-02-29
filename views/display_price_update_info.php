<?php
/*
http://localhost/listings_new/views/display_price_update_info.php

http://192.168.0.24/FESP-REFACTOR/ 
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$db_listings = new PDO("sqlite:../dbase/listings_NEW.db3");
$db_stock = new PDO("sqlite:../dbase/stock_control.db3");


$sql = "SELECT * FROM `changes` WHERE `changes` LIKE '%{np}%'";
$res = $db_listings->query($sql);
$changes = $res->fetchAll(PDO::FETCH_ASSOC); // FETCH_ASSOC FETCH_COLUMN FETCH_KEY_PAIR FETCH_NUM

$changes_ids_str = implode("','", array_column($changes, 'id'));

echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($changes); echo '</pre>'; die();
/*
Array
(
    [0] => Array
        (
            [id] => 11140
            [changes] => a{np}10.89>10.99
            [user] => 2
            [timestamp] => 1704702287
        )

    [1] => Array
        (
            [id] => 11150
            [changes] => a{np}77.49>78.99
            [user] => 2
            [timestamp] => 1704702310
        )
    etc.
*/

/*

*/

$sql = "SELECT id_lkup,key,cat_id,product_name FROM `listings` WHERE `id_lkup` IN ('$changes_ids_str')";
$res = $db_listings->query($sql);
$listings_info = $res->fetchAll(PDO::FETCH_ASSOC);

/*
echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($listings_info); echo '</pre>'; die();
Array
(
    [0] => Array
        (
            [id_lkup] => 11140
            [key] => han19
            [cat_id] => a223
            [product_name] => 12" Mounting Brackets x 2
        )

    [1] => Array
        (
            [id_lkup] => 11150
            [key] => han19
            [cat_id] => a223
            [product_name] => 12" Mounting Brackets x 30
        )
*/

// Category lookup
$sql = "SELECT cat,name FROM `cats`";
$res = $db_stock->query($sql);
$cats_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);

// Sub category lookup
$sql = "SELECT cat_id,product_cat FROM `lookup_prod_cats`";
$res = $db_listings->query($sql);
$sub_cats_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);

// User lookup
$sql = "SELECT * FROM `user`";
$res = $db_listings->query($sql);
$user_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);

echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($user_lkup); echo '</pre>'; die();
