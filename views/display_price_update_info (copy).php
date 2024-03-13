<?php
/*
http://localhost/listings_new/views/display_price_update_info.php
http://localhost/ELIXIR/listings_new/views/display_price_update_info.php

http://192.168.0.24/listings_new/views/display_price_update_info.php 
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ini_set("memory_limit", "-1");


require_once '../incs/db_connections.php';
$db_listings = new PDO("sqlite:$listings_db_path");
$db_stock    = new PDO("sqlite:$stock_control_db_path");

// Get most recent timestamp
$sql = "SELECT `timestamp` FROM `price_change` ORDER BY `rowid` DESC LIMIT 1";
$res = $db_listings->query($sql);
$last_ts = $res->fetch(PDO::FETCH_COLUMN);

// Get all price updates since last time records were added to 'price_change' table.
$sql = "SELECT * FROM `changes` WHERE `changes` LIKE '%{np}%' AND `timestamp` > $last_ts";
$res = $db_listings->query($sql);
$changes = $res->fetchAll(PDO::FETCH_ASSOC);

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($changes); echo '</pre>'; die();

// Delete `price_change` records older than 18 months
$ts_minus_18months = strtotime(date('Y-m-d'). ' - 18 months');
$sql = "DELETE FROM `price_change` WHERE `timestamp` < $ts_minus_18months";
$db_listings->query($sql);









// Add new records to `price_change` table
// +++++++++++++++++++++++++++++++++++++++
/*
$price_change_data = [];
foreach ($changes as $rec) {
    $id = $rec['id'];
    
    $platform = '';
    if( preg_match('/([a-z])\{np\}/', $rec['changes'], $m) ){
        $platform = $m[1];
    }
    
    if ('' == $platform) {
        $platform = substr($rec['changes'], 0,1);
        if ('l' == $platform) {
            list(, $tmp) = explode('_', $rec['changes']);
            $platform = substr($tmp, 0,1);
        }
    }
    
    list(, $price) = explode('{np}', $rec['changes']);
    list($price,) = explode('{', $price);
    
    $user = $rec['user'];
    $ts = $rec['timestamp'];
    $price_change_data[] = [$id, $platform, $price, $user, $ts];
}

$stmt = $db_listings->prepare("INSERT INTO `price_change` (`id`,`platform`,`change`,`user`,`timestamp`) VALUES (?,?,?,?,?)");

$db_listings->beginTransaction();
foreach ($price_change_data as $rec) {
    $stmt->execute([$rec[0],$rec[1],$rec[2],$rec[3],$rec[4]]);
}
$db_listings->commit();
*/



$sql = "SELECT * FROM `price_change`";
// $sql = "SELECT * FROM `price_change` LIMIT 40000";
// $sql = "SELECT * FROM `changes` WHERE `changes` LIKE '%{np}%'";
$res = $db_listings->query($sql);
$changes = $res->fetchAll(PDO::FETCH_ASSOC); // FETCH_ASSOC FETCH_COLUMN FETCH_KEY_PAIR FETCH_NUM
/*
[
    [
        [id] => 11140
        [platform] => e
        [change] => 10.89>10.99
        [user] => 2
        [timestamp] => 1704702287
    ][
        [id] => 11150
        [platform] => e
        [change] => 77.49>78.99
        [user] => 2
        [timestamp] => 1704702310
    ]
    etc.
*/

$changes_ids_str = implode("','", array_column($changes, 'id'));


$sql = "SELECT id_lkup,key,cat_id,product_name FROM `listings` WHERE `id_lkup` IN ('$changes_ids_str')";
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
/*
[
    [11140] => [
        [key] => han19
        [cat_id] => a223
        [product_name] => 12" Mounting Brackets x 2
    ]

    [11150] => [
        [key] => han19
        [cat_id] => a223
        [product_name] => 12" Mounting Brackets x 30
    ]
    etc.
*/

// Category lookup
$sql = "SELECT cat,name FROM `cats`";
$res = $db_stock->query($sql);
$cats_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);
/*
[
    [agg] => Aggregates
    [bam] => Bamboo
    [bir] => Birds / Wildlife
*/

// Sub category lookup
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
/*
[
    [a1] => [
        [cat] => acc
        [product_cat] => Doormats
    ]

    [a2] => [
        [cat] => acc
        [product_cat] => Pen
    ]

    [a3] => [
        [cat] => acc
        [product_cat] => Raised Bed
    ]
*/

// User lookup
$sql = "SELECT * FROM `user`";
$res = $db_listings->query($sql);
$user_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);

// Required due to removal of secondary user records
$user_lkup[11] = 'David 2';
$user_lkup[12] = 'Lewis 2';
$user_lkup[13] = 'Kevin 2';
$user_lkup[14] = 'Mark 2';
$user_lkup[15] = 'Rachel 2';
$user_lkup[16] = 'Robert 2';
$user_lkup[17] = 'Josh 2';
$user_lkup[18] = 'Vova 2';
$user_lkup[19] = 'Peter 2';


// Platform lookup
$sql = "SELECT id,txt FROM `platforms`";
$res = $db_listings->query($sql);
$platform_lkup = $res->fetchAll(PDO::FETCH_KEY_PAIR);


$undef = [];
$data = [];
foreach ($changes as $rec) {
    $cat = 'MISSING_CAT';
    $sub_cat = 'MISSING_SUB';
    $product_name = 'MISSING_NAME';
    
    if (isset($listings_info_lkup[$rec['id']]['cat_id'])) {
        $cat_id       = @$listings_info_lkup[$rec['id']]['cat_id']; // undef: 12451, 12461, 14306, 14307, 14448, 14449, 14456, 14457, 14458, 14459, 14460, 14461, 
        $cat          = $cats_lkup[$sub_cats_lkup[$cat_id]['cat']];
        $sub_cat      = $sub_cats_lkup[$cat_id]['product_cat'];
        $product_name = $listings_info_lkup[$rec['id']]['product_name'];
    }
    else {
        $undef[$rec['id']] = $rec['id'];
    }
    
    $id = $rec['id'];
    $platform = $platform_lkup[$rec['platform']];
    $change = $rec['change'];
    $user = $user_lkup[$rec['user']];
    $date = date("Y-m-d", $rec['timestamp']);
    
    $data[] = [$id,$cat,$sub_cat,$product_name,$change,$platform,$user,$date];
}

$undef_vals = array_values($undef);
sort($undef_vals);

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r("Not in 'listings' table: "); print_r($undef_vals); echo '</pre>';

/* QUERIES:
key (listings): g107, g108, g109 etc
Fertilisers (fer) > Liquid Seaweed and iron (a59)
*/


$args_array_to_table = [
    'tbl_class' => 'tbl-1',
    'header' => ['Listings ID','Cat','Sub Cat','Product Name','Price Change','Platform','User','Date'],
    'content' => $data,
];

/**
 * array_to_table_fnc() function that outputs a table from input params
 * 
 * @param  array $args - pass table style name, header titles and table content
 * @return string - Dynamically created HTML table
 */
function array_to_table_fnc($args){
    $html = [];
    $html[] = '<table class="'.$args['tbl_class'].'"><thead><tr>';
    foreach ($args['header'] as $h_cell) {
        $html[] = "<th>$h_cell</th>";
    }
    $html[] = '</tr></thead>';

    $html[] = '<tbody>';

    foreach( $args['content'] as $row ){
        $html[] = '<tr>';
        foreach( $row as $cell ){
            $html[] = "<td>$cell</td>";
        }
        $html[] = '</tr>';
    }

    $html[] = '</tbody>';
    $html[] = '</table>';

    return implode("\n", $html);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>All listings price changes</title>

<!-- TABLE STYLE -->
<style>
    table.tbl-1{
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .tbl-1 th,.tbl-1 td{
        font-size: 12px;
        font-family: monospace;
        border: 1px solid #000;
        padding: 5px;
        vertical-align: top;
    }
    .tbl-1 td{ text-align: left; }
    .tbl-1 tr:nth-child(2n+2){ background: rgb(228, 238, 250); } /* light blue */
    .tbl-1 thead tr{ background: rgb(238, 238, 238); } /* light grey */
</style>

</head>
<body>

<?= array_to_table_fnc($args_array_to_table); ?>

</body>
</html>
