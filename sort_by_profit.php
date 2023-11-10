<?php
/*
http://192.168.0.24/LISTINGS/sort_by_profit.php This returns eBay
http://192.168.0.24/LISTINGS/sort_by_profit.php?amazon

http://localhost/elixir/listings_new/sort_by_profit.php
*/

set_time_limit(3);

// require_once 'C:/inetpub/wwwroot/database_paths.php';
$listings_db_path = 'dbase/listings_NEW.db3';
$stock_control_db_path = 'dbase/stock_control.db3';
// require_once 'database_paths.php';


$db_listings = new PDO("sqlite:$listings_db_path");
$db_stock    = new PDO("sqlite:$stock_control_db_path");


require_once 'incs/lookups.php';
require_once 'incs/php_functions.php';
$lookup_platform_flip = array_flip($lookup_platform);

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($config_fees); echo '</pre>'; die();

// $db_listings = new PDO("sqlite:$listings_db_path");
// $db_stock    = new PDO("sqlite:$stock_control_db_path");


$platform = 'e';
if( isset($_GET['source']) ){
	$platform = $lookup_platform_flip[$_GET['source']];
}

$lp = $lookup_platform[$platform];

$lookup_skus = lookup_skus_fnc([
	'db' => $db_listings,
	'lookup_platform' => $lookup_platform,
	// 'source' => 'w',
]);
$lookup_comps_ids = lookup_comps_ids_fnc([
	'db' => $db_listings,
	'lookup_platform' => $lookup_platform,
]);


// The following arrays:
// 1. '$sort_by_profit_pricing_suggestion'
// 2. '$sort_by_profit_fees'
// 3. '$sort_by_profit_urls'
// are created in incs/lookups.php

$pricing_suggestion_vals['e'] = $pricing_suggestion_vals['w'] = $config_fees['projection_20perc'][1];
$pricing_suggestion_vals['a'] = $pricing_suggestion_vals['p'] = $config_fees['projection_20perc'][2];

// $pricing_suggestion_vals['e'] = $pricing_suggestion_vals['w'] = $sort_by_profit_pricing_suggestion['low'];
// $pricing_suggestion_vals['a'] = $pricing_suggestion_vals['p'] = $sort_by_profit_pricing_suggestion['high'];

// $root_path = $_SERVER['DOCUMENT_ROOT'];
// $xDrivePath = 'C:/xampp/htdocs';
// $listings_db_path = "$root_path/LISTINGS/listings.db3";
// $stock_control_db_path = "$xDrivePath/stocksystem/PHPAPI/stock_control.db3";

$db_listings = new PDO('sqlite:'.$listings_db_path);
$db_stock = new PDO('sqlite:'.$stock_control_db_path);

$sql = "SELECT * FROM `lookup_couriers_plus_fuel`";
// $sql = "SELECT * FROM `lookup_couriers`";
$tmp = results_fnc($db_listings,$sql);
$lookup_couriers = [];
foreach( $tmp as $rec ){
	$lookup_couriers[ $rec['name'] ] = [
		'courier' => $rec['courier'],
		'cost'    => $rec['cost'],
		'weight'  => $rec['weight'],
	];
}

$sql = "SELECT * FROM `lookup_postage_bands`";
$tmp = results_fnc($db_listings,$sql);
$lookup_postage_bands = [];
foreach ($tmp as $rec) {
	$lookup_postage_bands[ $rec['band'] ] = [
		'cost'              => $rec['cost'],
		'max_weight'        => $rec['max_weight'],
		'example_packaging' => $rec['example_packaging'],
	];
}


$sql = "SELECT key,product_cost FROM `products`";
$keys_costs = results_fnc($db_stock,$sql,'KEY_PAIR');
/*
[acc0]  => 5.78
[acc1]  => 3.5
[acc10] => 15
...
*/

// $sql = "SELECT id,courier,prev_price,new_price FROM `listings_ebay`";
$sql = "SELECT id,prev_price,new_price FROM `listings_ebay`";
$tmp = results_fnc($db_listings,$sql);

$sql = "SELECT id,courier FROM `listings_couriers`";
$couriers = $db_listings->query($sql);
$couriers_lkup = $couriers->fetchAll(PDO::FETCH_KEY_PAIR);

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($lookup_skus); echo '</pre>'; die();

$listings_ebay = [];
foreach ($tmp as $vals) {
	$listings_ebay[ $vals['id'] ] = [
		'sku'        => isset($lookup_skus['e'][ $vals['id'] ]) ? $lookup_skus['e'][ $vals['id'] ] : '',
		'courier'    => $couriers_lkup[ $vals['id'] ],
		// 'courier'    => $vals['courier'],
		'prev_price' => $vals['prev_price'],
		'new_price'  => $vals['new_price'],
		'comp1'      => isset($lookup_comps_ids['e'][ $vals['id'] ]) ? $lookup_comps_ids['e'][ $vals['id'] ]['comp1'] : '',
		'comp2'      => isset($lookup_comps_ids['e'][ $vals['id'] ]) ? $lookup_comps_ids['e'][ $vals['id'] ]['comp2'] : '',
		'comp3'      => isset($lookup_comps_ids['e'][ $vals['id'] ]) ? $lookup_comps_ids['e'][ $vals['id'] ]['comp3'] : '',
	];
}

// $sql = "SELECT id,courier,prev_price,new_price FROM `listings_amazon`";
$sql = "SELECT id,prev_price,new_price FROM `listings_amazon`";
$tmp = results_fnc($db_listings,$sql);
$listings_amazon = [];
foreach ($tmp as $vals) {
	$listings_amazon[ $vals['id'] ] = [
		'sku'        => isset($lookup_skus['a'][ $vals['id'] ]) ? $lookup_skus['a'][ $vals['id'] ] : '',
		// 'sku'        => $lookup_skus['a'][ $vals['id'] ],
		'courier'    => $couriers_lkup[ $vals['id'] ],
		// 'courier'    => $vals['courier'],
		'prev_price' => $vals['prev_price'],
		'new_price'  => $vals['new_price'],
		'comp1'      => isset($lookup_comps_ids['a'][ $vals['id'] ]) ? $lookup_comps_ids['a'][ $vals['id'] ]['comp1'] : '',
		'comp2'      => isset($lookup_comps_ids['a'][ $vals['id'] ]) ? $lookup_comps_ids['a'][ $vals['id'] ]['comp2'] : '',
		'comp3'      => isset($lookup_comps_ids['a'][ $vals['id'] ]) ? $lookup_comps_ids['a'][ $vals['id'] ]['comp3'] : '',
	];
}


$tmp = ['']; // set first element to empty
foreach( $lookup_couriers as $name => $v ){
	$tmp[] = $name;
}
$lookup_courier_names = $tmp;
/*
[0] => 
[1] => Post 0-100g
[2] => Post 100-250g
[3] => Post 250-500g
...
*/

$sql = "SELECT id_lkup,product_name FROM `listings`";
$product_name_lookup = results_fnc($db_listings,$sql,'KEY_PAIR');
/*
...
[12596] => 5mx100m woven + 10 staples
[12597] => 5mx100m woven + 20 staples
[12598] => 5mx100m woven + 50 staples
...
*/

$sql = "SELECT * FROM `listings` WHERE `remove` IS NULL";
$listings = results_fnc($db_listings,$sql);


// $sql = "SELECT id,courier,prev_price,new_price,perc_advertising FROM `listings_$lp`";
$sql = "SELECT id,prev_price,new_price,perc_advertising FROM `listings_$lp`";
$tmp = results_fnc($db_listings,$sql);

$listings_platform = [];
foreach ($tmp as $vals) {
	$id = $vals['id'];
	unset($vals['id_lkup']);
	unset($vals['id']);
	$listings_platform[$id] = $vals;
	
	$listings_platform[$id]['courier'] = $couriers_lkup[$id];
	$listings_platform[$id]['sku'] = isset($lookup_skus[ $lookup_platform_flip[$lp] ][$id]) ? $lookup_skus[ $lookup_platform_flip[$lp] ][$id] : '';
	// $listings_platform[$id]['sku'] = $lookup_skus[ $lookup_platform_flip[$lp] ][$id];
	$listings_platform[$id]['comp1'] = isset($lookup_comps_ids['a'][$id]) ? $lookup_comps_ids['a'][$id]['comp1'] : '';
	$listings_platform[$id]['comp2'] = isset($lookup_comps_ids['a'][$id]) ? $lookup_comps_ids['a'][$id]['comp2'] : '';
	$listings_platform[$id]['comp3'] = isset($lookup_comps_ids['a'][$id]) ? $lookup_comps_ids['a'][$id]['comp3'] : '';
}
/*
[
    [sku] => 02057
    [courier] => 5
    [comp1] => 7.57
    [comp2] => 9.99
    [comp3] => 5.99
    [prev_price] => 5.49
    [new_price] => 5.69
    [perc_advertising] => 0
    [IDs] => 174089704973,114210785701,271139131112
    [notes] => Without pegs
    [timestamp] => 1631021253
],[
    [sku] => 02049,FF-VL1_01.5_x_010+PG0,00476,00476
    [courier] => 5
    [comp1] => 9.49
    [comp2] => 7.95
    [comp3] => 
    [prev_price] => 6.69
    [new_price] => 6.99
    [perc_advertising] => 0
    [IDs] => 114067721574,170394899100,174089704973
    [notes] => Without pegs
    [timestamp] => 1631625094
]
...
*/



$sql = "SELECT sku,id FROM `sku_am_eb` WHERE `platform` = '$platform'";

$tmp = results_fnc($db_stock,$sql);

$lookup_urls = [];
foreach ($tmp as $vals) {
	$lookup_urls[ $vals['sku'] ] = $vals['id'];
}
/*
[TYING-TUBE-X-01] => 400976393060
[TYING-TUBE-X-02] => 400976393060
[TYING-TUBE-X-03] => 400976393060
*/


if( !isset($lookup_skus[ $lookup_platform_flip[$lp] ]) ){
	//DEBUG
	echo '<pre style="background:#111; color:#b5ce28; font-size:60px;">'; print_r('NO SKUs EXIST FOR THIS PLATFORM!'); echo '</pre>'; die();
}

$lookup_ids = [];
foreach ($lookup_skus[ $lookup_platform_flip[$lp] ] as $id => $skus) {
	$link_ids = [];
	foreach (explode(',', $skus) as $sku) {
		$link_ids[] = isset($lookup_urls[$sku]) ? $lookup_urls[$sku] : [];
	}
	$lookup_ids[$id] = $link_ids;
}

/*
// Retrieve lookup list of Himalayan Pink Salt & Dead Sea (food grade) SKUs from StockControl DB
$sql = "SELECT sku FROM `sku_atts` WHERE `atts` LIKE 'sal2%' OR `atts` LIKE 'sal7%' OR `atts` LIKE 'sal8%'";
$lookup_no_vat_skus1 = results_fnc($db_stock,$sql,'COLUMN');

$sql = "SELECT * FROM `zero_vat_skus`";
$lookup_no_vat_skus2 = results_fnc($db_stock,$sql,'COLUMN');

$lookup_no_vat_skus = array_flip( array_merge($lookup_no_vat_skus1, $lookup_no_vat_skus2) );
*/

// Get zero vat ids.
// If 'ignore_zero_vat' column ('listings' table) is NULL
// and 'zero_vat' column ('lookup_prod_cats' table) is not NULL
// return array of 'id_lkup' values ('listings' table).
$sql = "SELECT id_lkup FROM `listings`
INNER JOIN `lookup_prod_cats`
ON listings.cat_id = lookup_prod_cats.cat_id
WHERE lookup_prod_cats.zero_vat = 1
AND listings.ignore_zero_vat IS null";

$zero_vat_ids = results_fnc($db_listings,$sql,'COLUMN');

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($zero_vat_ids); echo '</pre>'; die();

$multi_cpu = get_multi_cpu_fnc($db_listings);


if( 'e' == $platform || 'w' == $platform ){ $pricing_suggestion_val = $config_fees['projection_20perc'][1]; }
elseif( 'a' == $platform || 'p' == $platform ){ $pricing_suggestion_val = $config_fees['projection_20perc'][2]; }
// if( 'e' == $platform || 'w' == $platform ){ $pricing_suggestion_val = $sort_by_profit_pricing_suggestion['low']; }
// elseif( 'a' == $platform || 'p' == $platform ){ $pricing_suggestion_val = $sort_by_profit_pricing_suggestion['high']; }

$fees_val = 'e' == $platform ? $config_fees['platform_fees'][1] : $config_fees['platform_fees'][2];
// $fees_val = 'e' == $platform ? $sort_by_profit_fees['low'] : $sort_by_profit_fees['high'];


// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($lookup_courier_names); echo '</pre>'; die();


$profit_results = [];
foreach( $listings as $i => $rec ){
	// Skip if undefined
	if( !isset($listings_platform[$rec['id_lkup']]['prev_price']) ){ continue; }
	elseif( !isset($listings_platform[$rec['id_lkup']]['new_price']) ){ continue; }

	if( !isset($lookup_courier_names[ $listings_platform[ $rec['id_lkup'] ]['courier'] ]) ){
		$courier = 'N/A';
	}
	else{
		$courier = $lookup_courier_names[ $listings_platform[ $rec['id_lkup'] ]['courier'] ];
	}

	// Create cost per unit from the combination of multiple keys/percentages.
	if( isset($multi_cpu[$rec['key']]) ){
		$keys_ = explode(' ', $multi_cpu[$rec['key']]['keys']);
		$percs_ = explode(' ', $multi_cpu[$rec['key']]['percs']);

		$cpu_ = 0;
		for ($i=0; $i < count($keys_); $i++) { 
			$cpu_ += $keys_costs[$keys_[$i] ] * $percs_[$i];
		}
	}
	else{
		if( isset($keys_costs[$rec['key']]) ){
			$cpu_ = $keys_costs[$rec['key']];
		}
		else{
			$multi_cpu = get_multi_cpu_fnc($db_listings);

			$keys_ = explode(' ', $multi_cpu[$rec['key']]['keys']);
			$percs_ = explode(' ', $multi_cpu[$rec['key']]['percs']);

			$cpu_ = 0;
			for ($i=0; $i < count($keys_); $i++) { 
				$cpu_ += $keys_costs[$keys_[$i] ] * $percs_[$i];
			}
		}
	}

	$args = [
		'id'                      => $rec['id_lkup'],
		'cost_per_unit'           => $cpu_,
		'variation'               => $rec['variation'],
		'courier'                 => $courier,
		'packaging_band'          => $rec['packaging_band'],
		'lowest_variation_weight' => $rec['lowest_variation_weight'],
		'prev_price'              => $listings_platform[$rec['id_lkup']]['prev_price'],
		'new_price'               => $listings_platform[$rec['id_lkup']]['new_price'],
		'perc_advertising'        => $listings_platform[$rec['id_lkup']]['perc_advertising'],
		'prev_price_e'            => $listings_ebay[$rec['id_lkup']]['prev_price'],
		'new_price_e'             => $listings_ebay[$rec['id_lkup']]['new_price'],
		'prev_price_a'            => $listings_amazon[$rec['id_lkup']]['prev_price'], // Undefined array key 4
		'new_price_a'             => $listings_amazon[$rec['id_lkup']]['new_price'],  // Undefined array key 4
		'fees_val'                => $fees_val,
		'pricing_suggestion_val'  => $pricing_suggestion_val,
		'pricing_suggestion_vals' => $pricing_suggestion_vals,
		'platform'                => $platform,
		'lookup_couriers'         => $lookup_couriers,
		'lookup_postage_bands'    => $lookup_postage_bands,
	];

	if( 'w' != $platform ){
		$args['comp1'] = $listings_platform[$rec['id_lkup']]['comp1'];
		$args['comp2'] = $listings_platform[$rec['id_lkup']]['comp2'];
		$args['comp3'] = $listings_platform[$rec['id_lkup']]['comp3'];
		$args['comp1_e'] = $listings_ebay[$rec['id_lkup']]['comp1'];
		$args['comp2_e'] = $listings_ebay[$rec['id_lkup']]['comp2'];
		$args['comp3_e'] = $listings_ebay[$rec['id_lkup']]['comp3'];
		$args['comp1_a'] = $listings_amazon[$rec['id_lkup']]['comp1']; // Undefined array key 4
		$args['comp2_a'] = $listings_amazon[$rec['id_lkup']]['comp2']; // Undefined array key 4
		$args['comp3_a'] = $listings_amazon[$rec['id_lkup']]['comp3']; // Undefined array key 4
	}
	
	$result = calculate_profit_perc_fnc($args, $lookup_skus[ $lookup_platform_flip[$lp] ], $zero_vat_ids);

	if( isset($lookup_ids[ $result['id'] ]) ){
		$tmp = [];
		foreach( $lookup_ids[ $result['id'] ] as $id ){
			if( !is_array($id) ){
				if( !isset($_GET['csv']) ){
					$tmp[] = "<a href=\"{$sort_by_profit_urls[$platform]}$id\" target=\"_blank\">$id</a>";
				}
				else{
					$tmp[] = "=HYPERLINK( \"{$sort_by_profit_urls[$platform]}$id\", \"$id\" )";
					break;
				}
			}
		}
		
		$profit_results[] = [
			'product_name' => $product_name_lookup[ $result['id'] ],
			'skus' => $lookup_skus[ $lookup_platform_flip[$lp] ][ $result['id'] ],
			'ids' => implode(' ', $tmp),
			'price' => $result['price'],
			'profit' => $result['profit'],
			'profit_perc' => $result['profit_perc'],
			'piv' => $result['piv'],
		];
	}

}

usort( $profit_results, function ($a, $b) {
	if( $a['profit_perc'] == $b['profit_perc'] ){ return 0; }
	return $a['profit_perc'] < $b['profit_perc'] ? -1 : 1;
});


if( !isset($_GET['csv']) ){
	echo '<pre>'; print_r($profit_results); echo '</pre>'; die();
}

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=Records.csv');

$csvHandle = fopen('php://output', 'w');
fputcsv($csvHandle, [
    'Product',
    // 'Skus',
    'id(s)',
    'Price',
    'Profit',
    'Profit %',
    'VAT Info',
]);

foreach ($profit_results as $rec) {
    fputcsv($csvHandle, [
        $rec['product_name'],
        // $rec['product_name'] . " ({$rec['skus']})",
        // $rec['skus'],
        $rec['ids'],
        $rec['price'],
        $rec['profit'],
        $rec['profit_perc'],
        $rec['piv'],
    ]);
}
fclose($csvHandle);