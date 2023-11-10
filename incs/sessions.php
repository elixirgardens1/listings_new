<?php
$files_used[] = 'incs/sessions.php'; //DEBUG

$session = [];

$sql = "SELECT cat,name FROM `cats`";
$results = $db_stock->query($sql);
$session['lookup_cats'] = $results->fetchAll(PDO::FETCH_KEY_PAIR);

//- - - - - - - - - - - - - - - - - - -

$sql = "SELECT cat,cat_id,product_cat,vat_rate FROM `lookup_prod_cats`";
$results = $db_listings->query($sql);
$results = $results->fetchAll(PDO::FETCH_ASSOC);

/*
$session['zero_vat_cat_ids'] = [];
foreach ($results as $rec) {
	if (0 == $rec['vat_rate']) {
		$session['zero_vat_cat_ids'][] = $rec['cat_id'];
	}
}
*/

$session['vat_rate'] = [];
foreach ($results as $rec) {
	$session['vat_rate'][$rec['cat_id']] = $rec['vat_rate'];
}
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['vat_rate']); echo '</pre>';


$session['lookup_prod_cats'] = [];
$prev_cat = '';
foreach( $results as $rec ){
	if( $prev_cat != $rec['cat'] ){
		$session['lookup_prod_cats'][ $rec['cat'] ][''] = '- - SELECT - -';
		$session['lookup_prod_cats'][ $rec['cat'] ][ $rec['cat_id'] ] = $rec['product_cat'];
	}
	else{ $session['lookup_prod_cats'][ $rec['cat'] ][ $rec['cat_id'] ] = $rec['product_cat']; }
	$prev_cat = $rec['cat'];
}
$session['lookup_prod_cats_all'] = $session['lookup_prod_cats'];


// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r( $session['lookup_prod_cats']); echo '</pre>'; die();

//- - - - - - - - - - - - - - - - - - -

$sql = "SELECT * FROM `lookup_postage_bands`";
$results = $db_listings->query($sql);
$results = $results->fetchAll(PDO::FETCH_ASSOC);

$session['lookup_postage_bands'] = [];
foreach( $results as $rec ){
	$session['lookup_postage_bands'][ $rec['band'] ] = [
		'cost'              => $rec['cost'],
		'max_weight'        => $rec['max_weight'],
		'example_packaging' => $rec['example_packaging'],
	];
}

//- - - - - - - - - - - - - - - - - - -

$sql = "SELECT name,rowid FROM `lookup_couriers_plus_fuel`";
$results = $db_listings->query($sql);
$lookup_couriers_name_id = $results->fetchAll(PDO::FETCH_KEY_PAIR);

$sql = "SELECT * FROM `lookup_couriers_plus_fuel`";
// $sql = "SELECT * FROM `lookup_couriers`";
$results = $db_listings->query($sql);
$results = $results->fetchAll(PDO::FETCH_ASSOC);

$session['lookup_couriers'] = [];
foreach( $results as $rec ){
	$operation = substr($rec['fuel'], 0,1);
	$fuel_amount = substr($rec['fuel'], 1);
	
	$cost = $rec['cost'];
	if( '%' == $operation ){
		$cost = $cost * $fuel_amount/100 + $cost;
	}
	
	$session['lookup_couriers'][ $rec['name'] ] = [
		'courier' => $rec['courier'],
		'cost'    => $cost,
		'weight'  => $rec['weight'],
	];
}
/*
	0.89  | 0.9256
	1.15  | 1.196
	1.32  | 1.3728
	1.65  | 1.716
	1.84  | 1.9136
	2.07  | 2.1528
	2.07  | 2.1528
	2.14  | 2.2256
	2.9   | 3.016
	3.52  | 3.6608
	7.23  | 7.5192
	17.19 | 17.8776
	20.99 | 
	49    | 
	51    | 
	54    | 
	3.07  | 
	3.07  | 
	4.17  | 
	5.35  | 
	7.05  | 
	15    | 
	3.21  | 
	30    | 
	3.79  | 
	0.72  | 
*/

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['lookup_couriers']); echo '</pre>'; die(); //DEBUG

$tmp = ['']; // set first element to empty
foreach( $session['lookup_couriers'] as $name => $v ){
	$tmp[] = $name;
}
$session['lookup_courier_names'] = $tmp;
// $lookup_courier_names_flip = array_flip($lookup_courier_names);

$session['lookup_prime_couriers'] = [];
$session['lookup_prime_couriers'][''] = '';
foreach ($session['lookup_couriers'] as $key => $vals) {
	// echo "<pre style='background:#111; color:#b5ce28; font-size:11px;'>\n\n\n"; print_r($vals['courier']); echo '</pre>';
	
	if( 'Prime' == $vals['courier'] ){
		$session['lookup_prime_couriers'][$key] = $vals;
	}
}

// Only display Prime Courier options if platform = 'p'
// If platform != 'p' only display non Prime Courier options
if( isset($_POST['platform']) ){
	$tmp = [];
	if( 'p' == $_POST['platform'] ){
		foreach ($session['lookup_couriers'] as $key => $vals) {
			if( 'Prime' == $vals['courier'] ){ $tmp[$key] = $vals; }
		}
	}
	else{
		foreach ($session['lookup_couriers'] as $key => $vals) {
			if( 'Prime' != $vals['courier'] ){ $tmp[$key] = $vals; }
		}
	}
	$session['lookup_couriers'] = $tmp;
}

//DEBUG
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($_POST['platform']."\n"); print_r($session['lookup_couriers']); echo '</pre>';

//- - - - - - - - - - - - - - - - - - -

/*
// Retrieve lookup list of Himalayan Pink Salt & Dead Sea (food grade) SKUs from StockControl DB
$sql = "SELECT * FROM `sku_atts` WHERE `atts` LIKE 'sal%' OR `atts` LIKE 'fer%' OR `atts` LIKE 'bir%'";
// $sql = "SELECT sku FROM `sku_atts` WHERE `atts` LIKE 'sal2%' OR `atts` LIKE 'sal7%' OR `atts` LIKE 'sal8%'";
$results = $db_stock->query($sql);
$results = $results->fetchAll(PDO::FETCH_ASSOC);


$filtered_results = [];
foreach ($results as $rec) {
	if (
		'sal2' == substr($rec['atts'], 0,4)
		|| 'sal7' == substr($rec['atts'], 0,4)
		|| 'sal8' == substr($rec['atts'], 0,4)
		|| 'fer60' == substr($rec['atts'], 0,5)
		|| 'bir2' == substr($rec['atts'], 0,4)
	){
		$filtered_results[] = $rec['sku'];
	}
}

$session['lookup_no_vat_skus'] = array_flip($filtered_results);
*/

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($filtered_results); echo '</pre>'; die();
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($results); echo '</pre>'; die();

// $sql = "SELECT * FROM `zero_vat_skus`";	
// $results = $db_stock->query($sql);
// $results2 = $results->fetchAll(PDO::FETCH_COLUMN);

// $session['lookup_no_vat_skus'] = array_flip( array_merge($results1, $results2) );

//- - - - - - - - - - - - - - - - - - -

//=========================================================================
// Remove menu items if not currently in the 'listings' table
//=========================================================================
$sql = "SELECT * FROM `listings`";
$results = $db_listings->query($sql);
$results = $results->fetchAll(PDO::FETCH_ASSOC);

$current_products = [];
foreach ($results as $vals) {
	$current_products[ $vals['cat_id'] ] = 1;
}

foreach ($session['lookup_prod_cats'] as $cat => $vals) {
	foreach ($vals as $cat_id => $v) {
		if( !isset($current_products[$cat_id]) ){
			if( '' != $cat_id ){
				unset($session['lookup_prod_cats'][$cat][$cat_id]);
			}
		}
	}
}

// Remove all cats with no products
foreach ($session['lookup_prod_cats'] as $key => $vals) {
	if( 1 == count($session['lookup_prod_cats'][$key]) ){
		unset($session['lookup_prod_cats'][$key]);
	}
}

// Get first cat
foreach ($session['lookup_prod_cats'] as $key => $v) {
	$cat = $key;
	break;
}
foreach ($session['lookup_cats'] as $key => $v) {
	if( isset($session['lookup_prod_cats'][$key]) ){
		$cat = $key;
		break;
	}
}

if( isset($_POST['cat']) ){
	$_POST['cat'] = html_entity_decode($_POST['cat']);
	$cat = $_POST['cat'];
}
$cat_id = '';
if( isset($_POST['cat_id']) ){
	$cat_id = $_POST['cat_id'];
	if( !isset($session['lookup_prod_cats'][$_POST['cat'] ][$_POST['cat_id'] ]) ){ $cat_id = ''; }
}

// $session['lookup_prod_cats'] array needs to be sorted so that categories drop-down appears in alphabetical order.
$tmp = [];
foreach ($session['lookup_cats'] as $key => $val) {
	if( isset($session['lookup_prod_cats'][$key]) ){
		$tmp[$key] = $session['lookup_prod_cats'][$key];
	}
}
$session['lookup_prod_cats'] = $tmp;
