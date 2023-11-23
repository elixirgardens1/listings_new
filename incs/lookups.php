<?php
$lookup_units = [
	'q' => 'Qty',
	'l' => 'Length',
	'w' => 'Weight',
	'v' => 'Volume'
];
$sort_by_profit_urls = [
	'e' => 'https://www.ebay.co.uk/itm/',
	'a' => 'https://www.amazon.co.uk/dp/',
	'p' => 'https://www.amazon.co.uk/dp/',
	'w' => 'https://elixirgardensupplies.co.uk/product/',
];

$link_colour = [
	'1' => '',
	'2' => 'link_grn',
	'3' => 'link_orange',
	'4' => 'link_blu',
];

$link_type = [
	'1' => 'Like 4 Like',
	'2' => 'Cheapest',
	'3' => 'Most Popular',
	'4' => 'Out of Stock',
];

$sql = "SELECT * FROM `config_fees`";
$results = $db_listings->query($sql);

$config_fees = $results->fetchAll(PDO::FETCH_ASSOC);

$tmp = [];
foreach( $config_fees as $rec ){
	if ('pp1_perc' != $rec['type']) {
		$tmp[$rec['type'] ][$rec['id'] ] = $rec['value'];
	}
	// else {
	// 	$pp1_perc = $rec['value'];
	// }
}
$config_fees = $tmp;

$sql = "SELECT * FROM `platforms`";
$results = $db_listings->query($sql);
$platforms = $results->fetchAll(PDO::FETCH_ASSOC);

// NOTE: The $lookup_platform array (below) gets created from this
$lookup_dash_view_profit = [];
foreach( $platforms as $rec ){
	$lookup_dash_view_profit[$rec['id'] ] = [
		'txt'               => $rec['txt'],
		'source'            => strtolower($rec['txt']),
		'platform_fees'     => $config_fees['platform_fees'][$rec['platform_fees'] ],
		'projection_20perc' => $config_fees['projection_20perc'][$rec['projection_20perc'] ],
		'no_price_matrix'   => $rec['no_price_matrix'],
	];
}
$lookup_platform = [];
foreach( $lookup_dash_view_profit as $key => $rec ){
	$lookup_platform[$key] = $rec['source'];
}

$sql = "SELECT * FROM `header_colours`";
$results = $db_listings->query($sql);
$header_colours_results = $results->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT `platform`,`id` FROM `header_colour_selection`";
$results = $db_listings->query($sql);
$header_colour_selection = $results->fetchAll(PDO::FETCH_KEY_PAIR);


$tmp = [];
foreach( $header_colours_results as $rec ){
	$tmp[$rec['id']] = [
		'bg-color' => $rec['bg-color'],
		'fg-color' => $rec['fg-color'],
	];
}
$header_colours_results = $tmp;

$tmp = [];
foreach( $header_colour_selection as $platform => $rec ){
	// Ternary operator
	$foreground =
	'' == $header_colours_results[$header_colour_selection[$platform]]['fg-color'] ?
	'' :
	' color:'. $header_colours_results[$header_colour_selection[$platform]]['fg-color'] .' !important;';
	$tmp[] = '.bg-'.$platform.'{background:'. $header_colours_results[$header_colour_selection[$platform]]['bg-color'] .';' . $foreground . '}';
}
$header_colours = implode('', $tmp);

//DEBUG
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($header_colours); echo '</pre>'; die();

// *** This data is currently duplicated in the StockControl database (rooms_lookup), minus the keys.
$sql = "SELECT `id`,`room` FROM `rooms_lookup`";
$results = $db_listings->query($sql);
$lookup_rooms = $results->fetchAll(PDO::FETCH_KEY_PAIR);
