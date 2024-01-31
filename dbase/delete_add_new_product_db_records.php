<?php
/*
http://localhost/listings_new/dbase/delete_add_new_product_db_records.php

http://deepthought:8080/stocksystem/PHPAPI/delete_add_new_product_db_records.php
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$db_sqlite = new PDO('sqlite:stock_control.db3');


$tbls = [
    'products'=>'1194',
    'product_rooms'=>'1181',
    'stock_qty'=>'1195',
    'stock'=>'1195'
];

foreach ($tbls as $tbl => $rowid) {
    if (isset($_GET['delete'])) {
        $sql = "DELETE FROM `$tbl` WHERE `rowid` >= '$rowid'";
        $db_sqlite->query($sql);
    }
    else {
        $sql = "SELECT * FROM `$tbl` WHERE `rowid` >= '$rowid'";
        $results = $db_sqlite->query($sql);
        $results = $results->fetchAll(PDO::FETCH_ASSOC);
        echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($results); echo '</pre>';
    }
    
}
