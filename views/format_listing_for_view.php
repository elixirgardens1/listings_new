<?php
$files_used[] = 'views/format_listing_for_view.php'; //DEBUG

// The links to Elixir products on Amazon, eBay etc. are stored in
// sku_am_eb@stock_control.db3. These are used for the URLs' drop-downs.
$where_in = implode("','", array_keys($id_skus) );

$sql = "SELECT * FROM `sku_am_eb` WHERE `sku` IN ('$where_in')";
$results = $db_stock->query($sql);
$results = $results->fetchAll(PDO::FETCH_ASSOC);

$dropdowns = [];
foreach( $results as $rec ){
	$dropdowns[ $rec['sku'] ][ $rec['platform'] . '_id' ] = $rec['id'];
}
	
$keys = [];
$product_names = [];
$cpus = [];
$lvws = [];
$group_prev = '';
$product_key_prev = '';
$inc = 0;
$forms_data = [];
$price_matrix = [];

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['listings']); echo '</pre>';

//=========================================================================
// Need to create a complete array with required elements for either
// 'Listing' view or 'Edit' view.
//=========================================================================
foreach( $session['listings'] as $id_lkup => $recs ){
	if( 'p' == $platform_post && 'PRIME' != substr($recs['courier'], 0,5) ){ continue; }
	
	if( isset($product_key) && '' == $product_key ){ $product_key = $recs['key']; }

	$keys[ $recs['group_'] ]                                       = $recs['key'];
	$product_names[ $recs['group_'] ][ $recs['product_name'] ]     = $recs['product_name'];
	$cpus[ $recs['group_'] ]["{$recs['cost_per_unit']}"]           = $recs['cost_per_unit'];
	$lvws[ $recs['group_'] ]["{$recs['lowest_variation_weight']}"] = $recs['lowest_variation_weight'];

	$args = [
		'pp1_perc'                => $session['pp1_perc'],
		'pp2_listings'            => $session['pp2_lkup'][$id_lkup],
		
		'id'                      => $id_lkup,
		'cost_per_unit'           => $recs['cost_per_unit'],
		'variation'               => $recs['variation'],
		'courier'                 => $recs['courier'],
		'packaging_band'          => $recs['packaging_band'],
		'lowest_variation_weight' => $recs['lowest_variation_weight'],
		'prev_price'              => $recs['prev_price'],
		'new_price'               => $recs['new_price'],
		'perc_advertising'        => $recs['perc_advertising'],
		'fees_val'                => $fees_val,
		'pricing_suggestion_val'  => $pricing_suggestion_val,
		'pricing_suggestion_vals' => $pricing_suggestion_vals,
		'platform'                => $platform_post,
		'lookup_couriers'         => $session['lookup_couriers'],
		'lookup_prime_couriers'   => $session['lookup_prime_couriers'],
		'lookup_postage_bands'    => $session['lookup_postage_bands'],
	];

	foreach( $lookup_platform as $key => $platform ){
		if( 'web' == $platform || 'prime' == $platform ){ continue; }
		
		$args['prev_price_'.$key] = $recs['prev_price_'.$key];
		$args['new_price_'.$key] = $recs['new_price_'.$key];
	}

	if( 'w' != $platform_post ){
		$args['comp1'] = $recs['comp1'];
		$args['comp2'] = $recs['comp2'];
		$args['comp3'] = $recs['comp3'];
		
		foreach( $lookup_platform as $key => $platform ){
			if( 'web' == $platform || 'prime' == $platform ){ continue; }
			
			$args['comp1_'.$key] = $recs['comp1_'.$key];
			$args['comp2_'.$key] = $recs['comp2_'.$key];
			$args['comp3_'.$key] = $recs['comp3_'.$key];
		}
	}

	$calc_vals = calculate_flds_fnc([
		'args'     => $args,
		'id_skus'  => $id_skus,
		'vat_rate' => $vat_rate,
	]);

	$session['listings'][$id_lkup]['total_weight']                 = $calc_vals['total_weight'];
	$session['listings'][$id_lkup]['new_price']                    = $calc_vals['new_price'];
	$session['listings'][$id_lkup]['new_price_e']                  = $calc_vals['new_price_e'];
	$session['listings'][$id_lkup]['new_price_a']                  = $calc_vals['new_price_a'];
	$session['listings'][$id_lkup]['new_price_f']                  = $calc_vals['new_price_f'];
	$session['listings'][$id_lkup]['new_price_s']                  = $calc_vals['new_price_s'];
	$session['listings'][$id_lkup]['total_product_cost']           = $calc_vals['total_product_cost'];
	$session['listings'][$id_lkup]['postage']                      = $calc_vals['postage'];
	$session['listings'][$id_lkup]['pricing_suggestion_20perc']    = $calc_vals['pricing_suggestion_20perc'];
	$session['listings'][$id_lkup]['cpu_to_cust']                  = $calc_vals['cpu_to_cust'];
	$session['listings'][$id_lkup]['vat']                          = $calc_vals['vat'];
	$session['listings'][$id_lkup]['fees']                         = $calc_vals['fees'];
	
	$session['listings'][$id_lkup]['pp1']                          = $calc_vals['pp1'];
	$session['listings'][$id_lkup]['pp2']                          = $calc_vals['pp2']; // profit +pp2
	
	$session['listings'][$id_lkup]['profit']                       = $calc_vals['profit'];
	$session['listings'][$id_lkup]['profit_perc']                  = $calc_vals['profit_perc'];
	$session['listings'][$id_lkup]['profit_10off']                 = $calc_vals['profit_10off'];
	$session['listings'][$id_lkup]['profit_10off_perc']            = $calc_vals['profit_10off_perc'];
	$session['listings'][$id_lkup]['cls_colour_profit']            = $calc_vals['cls_colour_profit'];
	$session['listings'][$id_lkup]['cls_colour_profit_perc']       = $calc_vals['cls_colour_profit_perc'];
	
	$session['listings'][$id_lkup]['cls_color_total_product_cost'] = $calc_vals['cls_color_total_product_cost'];
	$session['listings'][$id_lkup]['cls_color_postage']            = $calc_vals['cls_color_postage'];
	
	$session['listings'][$id_lkup]['cls_colour_profit_10off']      = $calc_vals['cls_colour_profit'];
	$session['listings'][$id_lkup]['cls_colour_profit_10off_perc'] = $calc_vals['cls_colour_profit'];

	if( preg_match('/(\d{1,2}m x \d{1,2}m)$/', $recs['product_name'], $match) ){
		$variants = $match[1];
	}

	$price_matrix[] = [
		'group'        => $recs['group_'],
		'product_name' => $recs['product_name'],
		'packing'      => $recs['packing'],
		'variation'    => $recs['variation'],
		'new_price'    => $session['listings'][$id_lkup]['new_price'],
		'id_lkup'      => $id_lkup,
	];
}

//DEBUG
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['listings']); echo '</pre>';