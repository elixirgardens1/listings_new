<?php
$files_used[] = 'views/retrieve_listings.php'; //DEBUG

$and = '';
if( 'Edit' == $view || 'Add Prime' == $view ){ $and = " AND `group_` = '{$_POST['group_edit']}'"; }

$where = "WHERE `cat_id` = '$cat_id'$and AND `remove` IS NULL ORDER BY `group_`,`variation`";

$sql = "SELECT * FROM `listings` $where";
$results = $db_listings->query($sql);
$listings = $results->fetchAll(PDO::FETCH_ASSOC);

/*
$zero_vat_ids = [];
foreach ($listings as $rec) {
	// If 'ignore_zero_vat' column ('listings' table) is NULL
	// and 'zero_vat' column ('lookup_prod_cats' table) is not NULL
	// push 'id_lkup' value ('listings' table) to $zero_vat_ids array.
	// This allows individual listings within a zero vat category to not have zero vat applied.
	if (!$rec['ignore_zero_vat'] && in_array($rec['cat_id'], $session['zero_vat_cat_ids'])) {
		$zero_vat_ids[] = $rec['id_lkup'];
	}
}
*/

$vat_rate = [];
foreach ($listings as $rec) {
	if (isset($session['vat_rate'][$rec['cat_id']])) {
		$vat_rate[$rec['id_lkup']] = $session['vat_rate'][$rec['cat_id']];
	}
}

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($vat_rate); echo "</pre><br>"; die();
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['zero_vat_cat_ids']); echo "</pre><br>";
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($zero_vat_ids); echo "</pre><br>";
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['vat_rate']); echo "</pre><br>";

//=========================================================================
// Retrieve listings platform data for the above listings
//=========================================================================
$id_lkups = [];
foreach ($listings as $vals) {
	$id_lkups[] = $vals['id_lkup'];
}
$id_lkups_str = implode("','", $id_lkups);

$tbl = 'listings_'.$lookup_platform[$platform_post];
$where_in = "WHERE `id` IN('$id_lkups_str')";

$sql = "SELECT * FROM `$tbl` $where_in";
$results = $db_listings->query($sql);
$listings_platform = $results->fetchAll(PDO::FETCH_ASSOC);

// Some platforms use eBay pricing. The folloing code does this by
// checking whever the platform's 'new_price' is empty. If so, the eBay
// price is used.
$sql = "SELECT `id`,`new_price` FROM `listings_ebay` $where_in";
$results = $db_listings->query($sql);
$ebay_new_prices = $results->fetchAll(PDO::FETCH_KEY_PAIR);

foreach( $listings_platform as $i => $rec ){
	if( '' == $rec['new_price'] ){
		$listings_platform[$i]['new_price'] = $ebay_new_prices[$rec['id']];
	}
}
// End use eBay price

// Get comps_ids data
$sql = "SELECT * FROM `comps_ids` $where_in";
$results = $db_listings->query($sql);
$results = $results->fetchAll(PDO::FETCH_ASSOC);

$comps_ids = [];
foreach( $results as $recs ){
	$comps_ids[ $recs['id'] ][ $recs['source'] ] = $recs;
}

// Get skus data
$sql = "SELECT * FROM `skus` $where_in";
$results = $db_listings->query($sql);
$results = $results->fetchAll(PDO::FETCH_ASSOC);

$skus = [];
foreach( $results as $recs ){
	$skus[ $recs['id'] ][ $recs['source'] ][] = $recs['sku'];
}

// Retrieve listings couriers
$sql = "SELECT id,courier FROM `listings_couriers`";
$results = $db_listings->query($sql);
$listings_couriers = $results->fetchAll(PDO::FETCH_KEY_PAIR);

// 13419 items:
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($listings_couriers); echo '</pre>'; die(); //DEBUG

// Retrieve prime listings couriers
$sql = "SELECT id,courier FROM `prime_couriers`";
$results = $db_listings->query($sql);
$listings_prime_couriers = $results->fetchAll(PDO::FETCH_KEY_PAIR);

foreach( $listings_platform as $i => $rec ){
	$id = $rec['id'];
	$listings_platform[$i]['comp1'] = isset($comps_ids[$id][$platform_post]['comp1']) ? $comps_ids[$id][$platform_post]['comp1'] : '';
	$listings_platform[$i]['comp2'] = isset($comps_ids[$id][$platform_post]['comp2']) ? $comps_ids[$id][$platform_post]['comp2'] : '';
	$listings_platform[$i]['comp3'] = isset($comps_ids[$id][$platform_post]['comp3']) ? $comps_ids[$id][$platform_post]['comp3'] : '';
	
	$listings_platform[$i]['id1'] = isset($comps_ids[$id][$platform_post]['id1']) ? $comps_ids[$id][$platform_post]['id1'] : '';
	$listings_platform[$i]['id2'] = isset($comps_ids[$id][$platform_post]['id2']) ? $comps_ids[$id][$platform_post]['id2'] : '';
	$listings_platform[$i]['id3'] = isset($comps_ids[$id][$platform_post]['id3']) ? $comps_ids[$id][$platform_post]['id3'] : '';
	
	$listings_platform[$i]['type1'] = isset($comps_ids[$id][$platform_post]['type1']) ? $comps_ids[$id][$platform_post]['type1'] : '';
	$listings_platform[$i]['type2'] = isset($comps_ids[$id][$platform_post]['type2']) ? $comps_ids[$id][$platform_post]['type2'] : '';
	$listings_platform[$i]['type3'] = isset($comps_ids[$id][$platform_post]['type3']) ? $comps_ids[$id][$platform_post]['type3'] : '';
	
	$listings_platform[$i]['sku'] = isset($skus[$id][$platform_post]) ? $skus[$id][$platform_post] : '';
	
	$listings_platform[$i]['courier'] = 'p' != $platform_post ? $listings_couriers[$id] : $listings_prime_couriers[$id];
}

$tmp = [];
foreach ($listings_platform as $vals) {
	$id = $vals['id'];
	unset($vals['id_lkup']);
	unset($vals['id']);
	$tmp[$id] = $vals;
}
$listings_platform = $tmp;

$listings_ = [];
foreach( $lookup_platform as $pf => $platform ){
	// if( 'web' == $platform ){ continue; }
	
	$table = "listings_$platform";
	
	$sql = "SELECT * FROM `$table` $where_in";
	$results = $db_listings->query($sql);
	$results = $results->fetchAll(PDO::FETCH_ASSOC);
	
	foreach( $results as $vals ){
		$id = $vals['id'];
		
		if( 'web' != $platform ){
			$listings_[$platform][$id] = [
				'courier'    => 'prime' != $platform ? $listings_couriers[$id] : $listings_prime_couriers[$id],
				// 'courier'    => $vals['courier'],
				'prev_price' => $vals['prev_price'],
				'new_price'  => $vals['new_price'],
				
				'comp1' => isset($comps_ids[$id][$pf]['comp1']) ? $comps_ids[$id][$pf]['comp1'] : '',
				'comp2' => isset($comps_ids[$id][$pf]['comp2']) ? $comps_ids[$id][$pf]['comp2'] : '',
				'comp3' => isset($comps_ids[$id][$pf]['comp3']) ? $comps_ids[$id][$pf]['comp3'] : '',
				
				'id1'   => isset($comps_ids[$id][$pf]['id1']) ? $comps_ids[$id][$pf]['id1'] : '',
				'id2'   => isset($comps_ids[$id][$pf]['id2']) ? $comps_ids[$id][$pf]['id2'] : '',
				'id3'   => isset($comps_ids[$id][$pf]['id3']) ? $comps_ids[$id][$pf]['id3'] : '',
				
				'type1' => isset($comps_ids[$id][$pf]['type1']) ? $comps_ids[$id][$pf]['type1'] : '',
				'type2' => isset($comps_ids[$id][$pf]['type2']) ? $comps_ids[$id][$pf]['type2'] : '',
				'type3' => isset($comps_ids[$id][$pf]['type3']) ? $comps_ids[$id][$pf]['type3'] : '',
			];
		}
		
		$listings_[$platform][$id]['sku'] = isset($skus[$id][$pf]) ? $skus[$id][$pf] : '';
	}
}

uasort($listings, function($a, $b){
	// Sort by 1st field ('group_')
	$return_val = strcmp($a['group_'], $b['group_']);
	// Sort by 2nd field ('variation')
	if ($return_val == 0) { $return_val = strnatcmp($a['variation'], $b['variation']); }

	return $return_val;
});
$listings = array_values($listings);

$sql = "SELECT `key`,`product_cost` FROM `products`";
$results = $db_stock->query($sql);
$keys_costs = $results->fetchAll(PDO::FETCH_KEY_PAIR);

$multi_cpu = get_multi_cpu_fnc($db_listings);

$fees_val = $lookup_dash_view_profit[$platform_post]['platform_fees'];

$pricing_suggestion_vals[$platform_post] = $lookup_dash_view_profit[$platform_post]['projection_20perc'];
$pricing_suggestion_val = $pricing_suggestion_vals[$platform_post];

// Retrieve notes
$sql = "SELECT `group_`, `note` FROM `notes` WHERE `cat_id` = '$cat_id' AND `source` = '$platform_post'";
$results = $db_listings->query($sql);
$session['notes'] = $results->fetchAll(PDO::FETCH_KEY_PAIR);

$session['listings'] = [];
foreach( $listings as $rec ){
	// Skip if undefined
	if( !isset($listings_platform[$rec['id_lkup']]['prev_price']) ){ continue; }
	elseif( !isset($listings_platform[$rec['id_lkup']]['new_price']) ){ continue; }
	
	if( !isset($session['lookup_courier_names'][ $listings_platform[ $rec['id_lkup'] ]['courier'] ]) ){
		$courier = 'N/A';
	}
	else{
		$courier = $session['lookup_courier_names'][ $listings_platform[ $rec['id_lkup'] ]['courier'] ];
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
			// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($rec['key']); echo '</pre>';
			// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r('IF'); echo '</pre>';
			$cpu_ = $keys_costs[$rec['key']];
		}
		else{
			// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($rec['key']); echo '</pre>';
			// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r('ELSE'); echo '</pre>';
			// $multi_cpu = get_multi_cpu_fnc($db_listings);
			$multi_cpu = get_multi_cpu_fnc($crud_listings);

			$keys_ = explode(' ', $multi_cpu[$rec['key']]['keys']);
			$percs_ = explode(' ', $multi_cpu[$rec['key']]['percs']);

			$cpu_ = 0;
			for ($i=0; $i < count($keys_); $i++) { 
				$cpu_ += $keys_costs[$keys_[$i] ] * $percs_[$i];
			}
		}
	}
	
	$session['listings'][$rec['id_lkup']] = [
	   'pp2'                     => $rec['pp2'],
	   'key'                     => $rec['key'],
	   'group_'                  => $rec['group_'],
	   'packing'                 => $rec['packing'],
	   'product_name'            => $rec['product_name'],
	   'packaging_band'          => $rec['packaging_band'],
	   'courier'                 => $courier,
	   'cost_per_unit'           => $cpu_,
	   'lowest_variation_weight' => $rec['lowest_variation_weight'],
	   'variation'               => $rec['variation'],
	   'prev_price'              => $listings_platform[$rec['id_lkup']]['prev_price'],
	   'new_price'               => $listings_platform[$rec['id_lkup']]['new_price'],
	   'perc_advertising'        => $listings_platform[$rec['id_lkup']]['perc_advertising'],
	   'notes'                   => $listings_platform[$rec['id_lkup']]['notes'],
	];
	
	foreach( $lookup_platform as $key => $platform ){
		if( 'web' == $platform ){ continue; }
		
		$session['listings'][$rec['id_lkup']]['prev_price_'.$key] = isset($listings_[$platform][$rec['id_lkup']]['prev_price']) ? $listings_[$platform][$rec['id_lkup']]['prev_price'] : '';
		$session['listings'][$rec['id_lkup']]['new_price_'.$key]  = isset($listings_[$platform][$rec['id_lkup']]['new_price']) ? $listings_[$platform][$rec['id_lkup']]['new_price'] : '';
	}
	
	if( 'w' != $platform_post ){
		$session['listings'][$rec['id_lkup']]['comp1'] = $listings_platform[$rec['id_lkup']]['comp1'];
		$session['listings'][$rec['id_lkup']]['comp2'] = $listings_platform[$rec['id_lkup']]['comp2'];
		$session['listings'][$rec['id_lkup']]['comp3'] = $listings_platform[$rec['id_lkup']]['comp3'];

		$session['listings'][$rec['id_lkup']]['id1']   = $listings_platform[$rec['id_lkup']]['id1'];
		$session['listings'][$rec['id_lkup']]['id2']   = $listings_platform[$rec['id_lkup']]['id2'];
		$session['listings'][$rec['id_lkup']]['id3']   = $listings_platform[$rec['id_lkup']]['id3'];
		
		$session['listings'][$rec['id_lkup']]['type1']   = $listings_platform[$rec['id_lkup']]['type1'];
		$session['listings'][$rec['id_lkup']]['type2']   = $listings_platform[$rec['id_lkup']]['type2'];
		$session['listings'][$rec['id_lkup']]['type3']   = $listings_platform[$rec['id_lkup']]['type3'];
		
		foreach( $lookup_platform as $key => $platform ){
			if( 'web' == $platform ){ continue; }
			
			$session['listings'][$rec['id_lkup']]['comp1_'.$key] = isset($listings_[$platform][$rec['id_lkup']]['comp1']) ? $listings_[$platform][$rec['id_lkup']]['comp1'] : '';
			$session['listings'][$rec['id_lkup']]['comp2_'.$key] = isset($listings_[$platform][$rec['id_lkup']]['comp2']) ? $listings_[$platform][$rec['id_lkup']]['comp2'] : '';
			$session['listings'][$rec['id_lkup']]['comp3_'.$key] = isset($listings_[$platform][$rec['id_lkup']]['comp3']) ? $listings_[$platform][$rec['id_lkup']]['comp3'] : '';
		}
	}
	
	foreach( $lookup_platform as $key => $platform ){
		$session['listings'][$rec['id_lkup']]['sku_'.$key]   = isset($listings_[$platform][$rec['id_lkup']]['sku']) ? $listings_[$platform][$rec['id_lkup']]['sku'] : '';
	}
}

function sort_dec($a, $b){
	return $b > $a ? 1 : -1;
}
uasort($session['listings'], function($a, $b){
	$return_val = strcmp($a['group_'], $b['group_']);
	if ($return_val == 0) { $return_val = sort_dec($b['variation'], $a['variation']); }
	return $return_val;
});

// Get skus data
$lookup_skus = [];
if( count($session['listings']) ){
	$in = implode("','", array_keys($session['listings']) );
	
	$pform = isset($_POST['platform']) ? $_POST['platform'] : 'e';
	
	$sql = "SELECT `sku`,`id` FROM `skus` WHERE `source` = '$pform' AND `id` IN('$in')";
	$results = $db_listings->query($sql);
	$id_skus = $results->fetchAll(PDO::FETCH_KEY_PAIR);
}

$prime_records = FALSE;
foreach( $session['listings'] as $id_lkup => $recs ){
	// if( 'p' == $platform_post ){
	// 	if( 'PRIME' != substr($recs['courier'], 0,5) ){ continue; }
	// 	else{
	// 		$prime_records = TRUE;
	// 		break;
	// 	}
	// }
	if( 'p' == $platform_post && 'PRIME' != substr($recs['courier'], 0,5) ){ continue; }
	else{
		$prime_records = TRUE;
		break;
	}
}
$prime_records = TRUE;