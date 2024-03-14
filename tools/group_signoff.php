<?php
/*
http://localhost/listings_new/tools/group_signoff.php

http://192.168.0.24/FESP-REFACTOR/ 
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once '../incs/db_connections.php';
$db_listings = new PDO("sqlite:$listings_db_path");
$db_stock    = new PDO("sqlite:$stock_control_db_path");

// +++++++++++++++++++++++++++++++++++++++++++++++++++
$sql = "SELECT id_lkup,key,cat_id,product_name FROM `listings`";
$res = $db_listings->query($sql);
$listings_info_lkup = $res->fetchAll(PDO::FETCH_ASSOC);

$tmp = [];
foreach ($listings_info_lkup as $rec) {
    $tmp[$rec['id_lkup']] = [
        'key' => $rec['key'],
        'cat_id' => $rec['cat_id'],
        'product_name' => $rec['product_name'],
    ];
}
$listings_info_lkup = $tmp;


// Category lookup
// +++++++++++++++++++++++++++++++++++++++++++++++++++
$sql = "SELECT cat,name FROM `cats`";
$res = $db_stock->query($sql);
$cats_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);

// Sub category lookup
// +++++++++++++++++++++++++++++++++++++++++++++++++++
$sql = "SELECT cat,cat_id,product_cat FROM `lookup_prod_cats`";
$res = $db_listings->query($sql);
$sub_cats_lkup = $res->fetchAll(PDO::FETCH_ASSOC);

$tmp = [];
foreach ($sub_cats_lkup as $rec) {
    $tmp[$rec['cat_id']] = [
        'cat' => $rec['cat'],
        'product_cat' => $rec['product_cat'],
    ];
}
$sub_cats_lkup = $tmp;

// User lookup
// +++++++++++++++++++++++++++++++++++++++++++++++++++
$sql = "SELECT * FROM `user`";
$res = $db_listings->query($sql);
$user_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);

// Required due to removal of secondary user records
// +++++++++++++++++++++++++++++++++++++++++++++++++++
$user_lkup[11] = 'David 2';
$user_lkup[12] = 'Lewis 2';
$user_lkup[13] = 'Kevin 2';
$user_lkup[14] = 'Mark 2';
$user_lkup[15] = 'Rachel 2';
$user_lkup[16] = 'Robert 2';
$user_lkup[17] = 'Josh 2';
$user_lkup[18] = 'Vova 2';
$user_lkup[19] = 'Peter 2';

