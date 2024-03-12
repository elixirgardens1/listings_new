<?php

$doc_root = $_SERVER["DOCUMENT_ROOT"];
$listings_path = "$doc_root/listings_new/";

if ($live = false) {
    require_once 'C:/inetpub/wwwroot/database_paths.php';
    $listings_db_path = $listings_path.'dbase/listings_NEW.db3';
}
else {
    $listings_db_path = $listings_path.'dbase/listings_NEW.db3';
    $stock_control_db_path = $listings_path.'dbase/stock_control.db3';
}

/*
if ($live = false) {
    require_once 'C:/inetpub/wwwroot/database_paths.php';
    $listings_db_path = 'dbase/listings_NEW.db3';
}
else {
    $listings_db_path = 'dbase/listings_NEW.db3';
    $stock_control_db_path = 'dbase/stock_control.db3';
}
*/
