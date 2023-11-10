<?php

// http://192.168.0.24/listings_new/convert_db_format/query_tbls.php

$db_sqlite = new PDO('sqlite:listings_NEW.db3');

$ids_in = "'14288','14289','14290','14291','14292','14293','14294'";

foreach ([
    'listings_amazon',
    'listings_ebay',
    'listings_web',
] as $tbl) {
    $sql = "SELECT * FROM $tbl WHERE `id` IN ($ids_in)";
    $results = $db_sqlite->query($sql);
    $results = $results->fetchAll(PDO::FETCH_ASSOC);

    echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($results); echo '</pre>';
}


$sql = "SELECT * FROM `listings_couriers` WHERE `courier` = '28'";
$results = $db_sqlite->query($sql);
$results = $results->fetchAll(PDO::FETCH_ASSOC);

echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($results); echo '</pre>';