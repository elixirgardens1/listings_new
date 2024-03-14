<?php

// All this need storing in the likes of db_lkup table in a database.
$doc_root = $_SERVER["DOCUMENT_ROOT"];
$listings_path = "$doc_root/listings_new/";
// $listings_path = "$doc_root/ELIXIR/listings_new/";

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
