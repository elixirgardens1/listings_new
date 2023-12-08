<?php

if ($live = false) {
    require_once 'C:/inetpub/wwwroot/database_paths.php';
    $listings_db_path = 'dbase/listings_NEW.db3';
}
else {
    $listings_db_path = 'dbase/listings_NEW.db3';
    $stock_control_db_path = 'dbase/stock_control.db3';
}