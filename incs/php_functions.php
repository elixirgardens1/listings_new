<?php
$files_used[] = 'incs/php_functions.php'; //DEBUG

function calculate_flds_fnc($params)
{
	// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($params['args']); echo '</pre>';
	
	$args     = $params['args'];
	$id_skus  = $params['id_skus'];
	$vat_rate = $params['vat_rate'];
	// $lookup_platform  = $params['lookup_platform'];
	
	// $args['pp1_perc']
	
	$total_weight = $args['variation'] * $args['lowest_variation_weight'] + $args['lookup_postage_bands'][$args['packaging_band']]['max_weight'];
	
	$lookup_couriers = 'p' != $args['platform'] ? 'lookup_couriers' : 'lookup_prime_couriers';
	
	// echo '<pre style="background:#111; color:#b5ce28; font-size:11px; width:600px;">'; print_r($args[$lookup_couriers]); echo '</pre>'; die();
	
	$courier_cost = $args[$lookup_couriers][ $args['courier'] ]['cost'];
	
	//DEBUG
	$allowed = [
		'pp2',
		'id',
		'cost_per_unit',
		'variation',
		'courier',
		'packaging_band',
		'lowest_variation_weight',
		'prev_price',  // empty
		'new_price',   // empty
		'perc_advertising',
		'fees_val',
		'pricing_suggestion_val',
	];
	$allowed = array_flip($allowed);
	$args_copy = $args;
	foreach( $args_copy as $key => $arg ){
		if( !isset($allowed[$key]) ){ unset($args_copy[$key]); }
	}

	// Calculate fields
	$total_product_cost = $args['cost_per_unit'] * $args['variation'];
	
	if( '20-30kg DX Over 8ft' != $args['courier'] ){
		$postage = ($courier_cost + $args['lookup_postage_bands'][ $args['packaging_band'] ]['cost']) * ceil($args['lowest_variation_weight'] * $args['variation'] / 29.5 );
	}
	else{
		$postage = (13.59+( ($total_weight - 20) *0.3 )) *1.095 *1.1;
	}
	
	
	// If courier name contains 'pallet', 'drop' or '20-30kg dx'
	if(
		false !== stripos($args['courier'], 'pallet') ||
		false !== stripos($args['courier'], 'drop')
	){
		$postage = $args['lookup_couriers'][$args['courier']]['cost'];
	}

	$ebay_val = 0; // Remove 30p ebay fee

	$pricing_suggestion_20perc = ($total_product_cost + $postage + $ebay_val) * $args['pricing_suggestion_val'];
	
	if( 'w' != $args['platform'] ){
		if( '' == $args['comp1'] && '' == $args['comp2'] && '' == $args['comp3'] ){
			$new_price = $args['prev_price'];
		}
		else{
			$tmp = [];
			foreach ([$args['comp1'],$args['comp2'],$args['comp3'] ] as $val) { if( '' != $val ){ $tmp[] = $val; } }//2Mar2021
			$new_price = min($tmp);
		}
		if( '' == $new_price ){
			$new_price = ceil($pricing_suggestion_20perc) - 0.01;
		}
	}
	// Price for web
	else{
		$new_price = $args['new_price'];
	}

	if( !isset($args['ignore_new_price']) && '' != $args['new_price'] ){
		$new_price = $args['new_price'];
	}
	
	$new_price_calc = '' != $args['new_price'] ? $args['new_price'] : $new_price;
	
	// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($new_price_calc); echo '</pre>';

	$new_price_e = 0;
	$new_price_a = 0;
	
	$new_price_f = 0;
	$new_price_s = 0;
	
	// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($args['pricing_suggestion_vals']); echo '</pre>';

	// Calculate eBay prices to display on Amazon listing
	if( 'a' == $args['platform'] ){
		$new_price_e = $args['new_price_e'];
		if( '' == $args['new_price_e'] ){
			if( '' == $args['comp1_e'] && '' == $args['comp2_e'] && '' == $args['comp3_e'] ){
				$new_price_e = $args['prev_price_e'];
			}//2Mar2021
			else{
				$tmp = [];
				foreach ([$args['comp1_e'],$args['comp2_e'],$args['comp3_e'] ] as $val) { if( '' != $val ){ $tmp[] = $val; } }//2Mar2021
				$new_price_e = min($tmp);
			}
			if( '' == $new_price_e ){
				$pricing_suggestion_20perc_e = ($total_product_cost + $postage) * $args['pricing_suggestion_vals']['a'];
				// $pricing_suggestion_20perc_e = ($total_product_cost + $postage) * $args['pricing_suggestion_vals']['e'];
				$new_price_e = ceil($pricing_suggestion_20perc_e) - 0.01;
			}
		}
		$new_price_e = number_format((float)$new_price_e, 2, '.', '');
	}
	// Calculate Amazon prices to display on Prime listing
	elseif( 'p' == $args['platform'] ){
		$new_price_a = $args['new_price_a'];
		if( '' == $args['new_price_a'] ){
			if( '' == $args['comp1_a'] && '' == $args['comp2_a'] && '' == $args['comp3_a'] ){ $new_price_a = $args['prev_price_a']; }//2Mar2021
			else{
				$tmp = [];
				foreach ([$args['comp1_a'],$args['comp2_a'],$args['comp3_a'] ] as $val) { if( '' != $val ){ $tmp[] = $val; } }//2Mar2021
				$new_price_a = min($tmp);
			}
			if( '' == $new_price_a ){
				$pricing_suggestion_20perc_a = ($total_product_cost + $postage) * $args['pricing_suggestion_vals']['a'];
				$new_price_a = ceil($pricing_suggestion_20perc_a) - 0.01;
			}
		}
		$new_price_a = number_format((float)$new_price_a, 2, '.', '');
	}
	
	// gettype() floatval()
	
	$new_price_calc = (float)$new_price_calc;
	
	// Suppress warnings
	@($cpu_to_cust = (float)$new_price_calc / $args['variation']);
	
	if (0 != $vat_rate[$args['id']]) {
		@($vat = $new_price_calc - $new_price_calc/(1 + $vat_rate[$args['id']]/100));
	}
	else {$vat = 0;}
	
	$id_skus_flip = array_flip($id_skus);
	
	$pp1 = $new_price_calc * $args['pp1_perc']/100;
	
	$fees = 'w' != $args['platform'] ? $new_price_calc * $args['fees_val'] + $new_price_calc * ($args['perc_advertising']/100) : 0; //MOD
	@($profit = $new_price_calc - $total_product_cost - $postage - $vat - $fees - $pp1);

	$pp2 = $args['pp2_listings'] + $profit;
	// $pp2 = $args['pp2'];
	
	// if (!$new_price_calc) {$new_price_calc = 999;}
	//ISSUE: Some records on the "Aggregates/Aggregates/Web" page end up with $new_price_calc having a zero value, causing 'DivisionByZeroError'
	@($profit_perc = $profit / $new_price_calc * 100);

	$cls_colour_profit = $profit < 1 ? ' red-bg' : ' grn';

	switch (true) {
        case $profit_perc < 4:
            $cls_colour_profit_perc = ' red-bg';
            break;
        case $profit_perc < 7:
            $cls_colour_profit_perc = ' orange';
            break;
        case $profit_perc < 10:
            $cls_colour_profit_perc = ' yellow';
            break;
        case $profit_perc < 15:
            $cls_colour_profit_perc = ' grn';
            break;
        default:
        	$cls_colour_profit_perc = ' blue';
        
        /*
        case $profit_perc < 15:
            $cls_colour_profit_perc = ' red-bg';
            break;

        case $profit_perc < 20:
            $cls_colour_profit_perc = ' orange';
            break;

        case $profit_perc < 30:
            $cls_colour_profit_perc = ' grn';
            break;

        case $profit_perc > 29:
            $cls_colour_profit_perc = ' blue';
            break;
        */
    }
    $cls_color_total_product_cost = '';
    $cls_color_postage = '';
    
    /*
    switch (true) {
    	case $total_product_cost / $new_price_calc < 0.15:
    		$cls_color_total_product_cost = ' blue';
    		break;
    	case $total_product_cost / $new_price_calc < 0.20:
    		$cls_color_total_product_cost = ' grn';
    		break;
    	case $total_product_cost / $new_price_calc < 0.30:
    		$cls_color_total_product_cost = ' orange';
    		break;
    	case $total_product_cost / $new_price_calc > 0.29:
    		$cls_color_total_product_cost = ' red-bg';
    		break;
    }
    
    switch (true) {
    	case $postage / $new_price_calc < 0.15:
    		$cls_color_postage = ' blue';
    		break;
    	case $postage / $new_price_calc < 0.20:
    		$cls_color_postage = ' grn';
    		break;
    	case $postage / $new_price_calc < 0.30:
    		$cls_color_postage = ' orange';
    		break;
    	case $postage / $new_price_calc > 0.29:
    		$cls_color_postage = ' red-bg';
    		break;
    }
    */
    

	$cls_colour_profit_10off = '';
	$cls_colour_profit_10off_perc = '';

	if( 'w' == $args['platform'] ){
		// Calculate fields
		@($profit_10off = $new_price_calc * 0.9 - $new_price_calc * 0.9 / 6 - $total_product_cost - $postage);
		@($profit_10off_perc = $profit_10off / ($new_price_calc * 0.9) * 100);
		$cls_colour_profit_10off = $profit_10off < 1 ? ' red-bg' : ' grn';
		// $cls_colour_profit_10off = $profit_10off < 1.25 ? ' red-bg' : ' grn';
		$cls_colour_profit_10off_perc = $profit_10off_perc < 20 ? ' red-bg' : ' grn';

		// Round calculations
		$profit_10off = number_format((float)$profit_10off, 2, '.', '');
		$profit_10off_perc = number_format((float)$profit_10off_perc, 2, '.', '');
	}

	// Round calculations
	$total_product_cost = number_format((float)$total_product_cost, 2, '.', '');
	$postage = number_format((float)$postage, 2, '.', '');
	$pricing_suggestion_20perc = number_format((float)$pricing_suggestion_20perc, 2, '.', '');
	$new_price = number_format((float)$new_price, 2, '.', '');
	$cpu_to_cust = number_format((float)$cpu_to_cust, 4, '.', '');
	$vat = number_format((float)$vat, 2, '.', '');
	$fees = number_format((float)$fees, 2, '.', '');
	$profit = number_format((float)$profit, 2, '.', '');

	$profit_perc = number_format((float)$profit_perc, 2, '.', '');
	
	$pp1 = number_format($pp1, 2, '.', '');
	$pp2 = number_format($pp2, 2, '.', '');
	
	$return_array = [
		'total_weight'                 => $total_weight,
		'new_price'                    => $new_price,
		'new_price_e'                  => $new_price_e,
		'new_price_a'                  => $new_price_a,
		
		'new_price_f'                  => $new_price_f,
		'new_price_s'                  => $new_price_s,
		
		'total_product_cost'           => $total_product_cost,
		'postage'                      => $postage,
		'pricing_suggestion_20perc'    => $pricing_suggestion_20perc,
		'cpu_to_cust'                  => $cpu_to_cust,
		'vat'                          => $vat,
		'fees'                         => $fees,
		'profit'                       => $profit,
		'profit_perc'                  => $profit_perc,
		'profit_10off'                 => '',
		'profit_10off_perc'            => '',
		
		'pp1'                          => $pp1,
		'pp2'                          => $pp2,

		'cls_colour_profit'            => $cls_colour_profit,
		'cls_colour_profit_perc'       => $cls_colour_profit_perc,
		
		'cls_color_total_product_cost' => $cls_color_total_product_cost,
		'cls_color_postage'            => $cls_color_postage,
		
		'cls_colour_profit_10off'      => '',
		'cls_colour_profit_10off_perc' => '',
	];

	if( 'w' == $args['platform'] ){
		$return_array['profit_10off']      = $profit_10off;
		$return_array['profit_10off_perc'] = $profit_10off_perc;
		$return_array['cls_colour_profit_10off'] = $cls_colour_profit_10off;
        $return_array['cls_colour_profit_10off_perc'] = $cls_colour_profit_10off_perc;
	}
	else{
		$return_array['cls_colour_profit_10off']      = $cls_colour_profit_10off;
		$return_array['cls_colour_profit_10off_perc'] = $cls_colour_profit_10off_perc;
	}

	return $return_array;
}

function sanitize_post_data_fnc($post_array, $operations)
{
	// Trim
	function my_trim(&$item, $key) {
		$item = trim($item);
	}
	// Remove multiple whitespace
	function remove_multiple_whitespace(&$item, $key){
		$item = preg_replace('/\s+/', ' ',$item);
	}
	// Convert & " ' < > to &amp; &quot; &#039; &lt; &gt;
	function my_htmlspecialchars(&$item, $key) {
		$item = htmlspecialchars( $item, ENT_QUOTES );
	}
	// Replace MS style single/double quotes (“ ” ‘ ’) with standard quotes (" ')
	function ascii_translit(&$item, $key) {
		// B0CM7T7YWF
		if (!in_array($key, ['id1','id2','id3'])) {
			$item = iconv('UTF-8', 'ASCII//TRANSLIT', $item);
		}
	}

	foreach ($operations as $operation) {
		array_walk_recursive($post_array, $operation);
	}

	return $post_array;
}

function sel_opt_fnc($key, $val, $post)
{
	$sel = $key == $post ? ' selected' : '';
	return "<option value=\"$key\"$sel>$val</option>";
}

function add_pound_sign_fnc($str)
{
	$str = str_replace('under 10', 'under &pound;10', $str);
	$str = str_replace('over 10', 'over &pound;10', $str);

	return $str;
}

function calc_tub_price_fnc($variation, $price)
{
	if( $variation > 15 ){ $tub_cost = 4; }
	elseif( $variation > 10 ){ $tub_cost = 2.5; }
	elseif( $variation > 5 ){ $tub_cost = 2; }
	elseif( $variation > 1 ){ $tub_cost = 1.5; }
	else{ $tub_cost = 1; }

	return $price + $tub_cost;
}

function get_multi_cpu_fnc($db_listings)
{
	$sql = "SELECT * FROM `multi_cpu`";
	$multi_cpu = results_fnc($db_listings,$sql);

	$tmp = [];
	foreach ($multi_cpu as $vals) {
		$tmp[$vals['key'] ] = [
			'keys' => $vals['keys'],
			'percs' => $vals['percs'],
		];
	}
	return $tmp;
}

function spacer_and_buttons_fnc($args)
{
	$return = [];
	$recs          = $args['recs'];
	// $keys          = $args['keys'];
	$product_names = $args['product_names'];
	$group_prev    = $args['group_prev'];
	$cpus          = $args['cpus'];
	$lvws          = $args['lvws'];
	$cat           = $args['cat'];
	$cat_id        = $args['cat_id'];
	$platform_post = $args['platform_post'];
	
	$product_name = '';
	if( 1 == count($product_names[$group_prev]) ){
		foreach ($product_names[$group_prev] as $val) {
			$product_name = $val;
		}
	}

	$cpu = '';
	if( 1 == count($cpus[$group_prev]) ){
		foreach ($cpus[$group_prev] as $val) {
			$cpu = $val;
		}
	}

	$lvw = '';
	if( 1 == count($lvws[$group_prev]) ){
		foreach ($lvws[$group_prev] as $val) {
			$lvw = $val;
		}
	}
	
	// Used for hidden forms parameters at the end of the main form.
	$return['forms_data'] = [
		// 'key'        => $recs['key'],
		'key'        => $args['key'],
		'group_edit' => $group_prev,
		'cat'        => $cat,
		'cat_id'     => $cat_id,
		'platform'   => $platform_post,

		'product_name' => $product_name,
		'cpu'          => $cpu,
		'lvw'          => $lvw,
	];
	
	static $inc = 0;
	
	$tmp = [];
	$tmp[] = '<tr><td colspan="25">';
	
	$tmp[] = '<input type="button" name="edit" data-id="'.$inc.'" value="Edit" class="btn bt_spacer">';
	$tmp[] = '<input type="button" name="add" data-id="'.$inc.'" value="Add" class="btn bt_spacer">';
	
	// Surprisingly, the 'Add Prime' button does not appear on the Listings 'Prime' view.
	// The number of items in a 'Prime view' group are either very few or non existant.
	// Full groups need to exist in order to add the required items to Prime listings.
	if( 'p' != $platform_post ){
		$tmp[] = '<input type="button" name="add_prime" data-id="'.$inc.'" value="Add Prime" class="btn bt_spacer">';
	}
	
	$inc++;
	
	$tmp[] = '</td></tr>';
	
	$return['html'] = implode('', $tmp);
	
	return $return;
}

function modal_box_fnc($args)
{
	list($w,$h) = explode('x', $args['wxh']);
	$title = $args['title'];
	$exit_btn_left_pos = $w-20;
	
	$return = <<<HEREDOC
	<div id="modalBgEdit" class="modalBg">
		<div id="modalBox" class="modalBox" style="width:{$w}px; height:{$h}px;">
		
			<input type="button" name="close_modal" class="btn modalClose" style="margin-left:{$exit_btn_left_pos}px; margin-top:-8px; height:28px;" value="X">
			
			<h2 style="font-size: 18px;">$title</h2>
			
		</div>
	</div>
HEREDOC;
return $return;
}

function assoc_multi_array_fnc($arr, $key1, $key2=NULL)
{
	$tmp = [];
	foreach( $arr as $val ){
		$tmp[ $key2 ? $val[$key1] .'_'. $val[$key2] : $val[$key1] ] = $val;
	}
	return $tmp;
}

function array_to_table_fnc($args, $users=NULL){
	$html = [];
	$html[] = '<table class="'.$args['tbl_class'].'"><thead><tr>';
	foreach ($args['header'] as $h_cell) {
		$style = 'Changes' == $h_cell ? " style='color: #000; text-align: left;'" : " style='color: #000;'";
		$html[] = "<th$style>$h_cell</th>";
	}
	$html[] = '</tr></thead>';

	$html[] = '<tbody>';

	foreach( $args['body'] as $row ){
		$html[] = '<tr>';
		foreach( $row as $cell ){
			$html[] = "<td>$cell</td>";
		}
		if( $users ){
			$html[] = '<td width="100%"><code>' . implode("<br>", $users[$row[0] ][$row[1] ][$row[2] ] ) . '</code></td>';
		}
		$html[] = '</tr>';
	}

	$html[] = '</tbody>';
	$html[] = '</table>';

	return implode("\n", $html);
}

function zzz_create_array_fnc($params)
{
	$rec                     = $params['rec'];
	$recs                    = $params['recs'];
	$fees_val                = $params['fees_val'];
	$pricing_suggestion_val  = $params['pricing_suggestion_val'];
	$pricing_suggestion_vals = $params['pricing_suggestion_vals'];
	$platform_post           = $params['platform_post'];
	$lookup_couriers         = $params['lookup_couriers'];
	$lookup_postage_bands    = $params['lookup_postage_bands'];
	
	$args = [
		'id'                      => $rec['rowid'],
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
		'lookup_couriers'         => $lookup_couriers,
		'lookup_postage_bands'    => $lookup_postage_bands,
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
}

function calculate_profit_perc_fnc($args, $lookup_skus, $zero_vat_ids)
{
	if( isset($_GET['test']) && '12596' == $args['id'] ){
		//DEBUG
		echo '<pre style="background:#002; color:#fff;">'; print_r($args); echo '</pre>'; die();
	}
	
	$total_product_cost = $args['cost_per_unit'] * $args['variation'];
	$postage = ($args['lookup_couriers'][ $args['courier'] ]['cost'] +
				$args['lookup_postage_bands'][ $args['packaging_band'] ]['cost']) * ceil($args['lowest_variation_weight'] * $args['variation'] / 29.5 );

	if( false !== stripos($args['courier'], 'pallet') || false !== stripos($args['courier'], 'drop') ){
		$postage = $args['lookup_couriers'][$args['courier']]['cost'];
	}

	$ebay_val = 'e' == $args['platform'] ? 0.3 : 0;

	$pricing_suggestion_20perc = ($total_product_cost + $postage + $ebay_val) * $args['pricing_suggestion_val'];

	if( 'w' != $args['platform'] ){
		if( '' == $args['comp1'] && '' == $args['comp2'] && '' == $args['comp3'] ){ $new_price = $args['prev_price']; }//2Mar2021
		else{
			$tmp = [];
			foreach ([$args['comp1'],$args['comp2'],$args['comp3'] ] as $val) { if( '' != $val ){ $tmp[] = $val; } }//2Mar2021
			$new_price = min($tmp);
		}
		if( '' == $new_price ){ $new_price = ceil($pricing_suggestion_20perc) - 0.01; }
	}
	// Price for web
	else{ $new_price = $args['new_price']; }

	if( !isset($args['ignore_new_price']) && '' != $args['new_price'] ){
		$new_price = $args['new_price'];
	}

	$new_price_calc = '' != $args['new_price'] ? $args['new_price'] : $new_price;
	$new_price_calc = (float)$new_price_calc;

	$new_price_e = 0;
	$new_price_a = 0;

	// Suppress warnings
	@($cpu_to_cust = $new_price_calc / (float)$args['variation']);
	@($vat = $new_price_calc/6);

	$piv = '';
	
	if (in_array($args['id'], $zero_vat_ids)) {
		$vat = 0;
		$piv = 'profit includes vat';
	}

	$fees = 'w' != $args['platform'] ? $new_price_calc * $args['fees_val'] + $new_price_calc * ((float)$args['perc_advertising']/100) : 0; //MOD

	@($profit = $new_price_calc - (float)$total_product_cost - (float)$postage - (float)$vat - (float)$fees);

	// Deduct 30 pence from profit ebay
	$profit = 'e' == $args['platform'] ? $profit - 0.3 : $profit;
	
	$profit_perc = '0' != $new_price_calc ? $profit / $new_price_calc * 100 : 0;

	$profit = number_format((float)$profit, 2, '.', '');
	$profit_perc = number_format((float)$profit_perc, 2, '.', '');
	$new_price = number_format((float)$new_price, 2, '.', '');

	return [
		'id' => $args['id'],
		'profit_perc' => $profit_perc,
		'profit' => $profit,
		'price' => $new_price,
		'piv' => $piv,
	];
}

function results_fnc($db_listings,$sql,$arr_type=null)
{
	$results = $db_listings->query($sql);
	
	if( !$arr_type ){
		return $results->fetchAll(PDO::FETCH_ASSOC);
	}
	elseif( 'KEY_PAIR' == $arr_type ){
		return $results->fetchAll(PDO::FETCH_KEY_PAIR);
	}
	elseif( 'COLUMN' == $arr_type ){
		return $results->fetchAll(PDO::FETCH_COLUMN);
	}
	elseif( 'NUM' == $arr_type ){
		return $results->fetchAll(PDO::FETCH_NUM);
	}
}

function lookup_skus_fnc($args)
{
	$db              = $args['db'];
	$lookup_platform = $args['lookup_platform'];
	
	// If source not set, get all platforms
	$where = '';
	if( isset($args['source']) ){ $where = " WHERE `source` = '{$args['source']}'"; }
	
	
	// lookup_skus
	$sql = "SELECT id,sku,source FROM `skus`$where";
	$lookup_skus = results_fnc($db,$sql);
	$tmp = [];
	foreach( $lookup_skus as $rec ){
		$tmp[ $rec['source'] ][ $rec['id'] ][] = $rec['sku'];
	}
	$lookup_skus = [];
	foreach( array_keys($lookup_platform) as $platform_char ){
		if( isset($tmp[$platform_char]) ){
			foreach( $tmp[$platform_char] as $id => $skus ){
				$lookup_skus[$platform_char][$id] = implode(',', $skus);
			}
		}
	}
	
	return $lookup_skus;
}

function lookup_comps_ids_fnc($args)
{
	$db              = $args['db'];
	$lookup_platform = $args['lookup_platform'];
	
	// If source not set, get all platforms
	$where = '';
	if( isset($args['source']) ){ $where = " WHERE `source` = '{$args['source']}'"; }
	
	// lookup_comps_ids
	$sql = "SELECT id,comp1,comp2,comp3,source FROM `comps_ids`$where";
	$tmp = results_fnc($db,$sql);
	$lookup_comps_ids = [];
	foreach( $tmp as $rec ){
		$lookup_comps_ids[ $rec['source'] ][ $rec['id'] ] = [
			'comp1' => $rec['comp1'],
			'comp2' => $rec['comp2'],
			'comp3' => $rec['comp3'],
		];
	}
	
	return $lookup_comps_ids;
}

function check_changes_fnc($args)
{
	$changes = [];
	
	foreach( $args as $arg ){
		if( $arg[1] !== $arg[2] ){
			if( !is_null($arg[1]) ){
				$changes[] = $arg[0] . $arg[1] .'>'. $arg[2];
			}
			else{ $changes[] = $arg[0] . $arg[2]; }
		}
	}
	
	return count($changes) ? implode('', $changes) : NULL;
}

function record_changes_fnc($args)
{
	$db  = $args['db'];

	$stmt = $db->prepare("INSERT INTO `changes` VALUES (?,?,?,?)");

	if( !isset($args['no_trans']) ){ $db->beginTransaction(); }
	if( isset($args['recs']) ){
		foreach( $args['recs'] as $id => $changes ){
			$stmt->execute([$id, $changes, $args['user'], $args['timestamp'] ]);
		}
	}
	else{ $stmt->execute($args['rec']); }
	if( !isset($args['no_trans']) ){ $db->commit(); }
}

function record_changes_to_stock_fnc($args)
{
	$db  = $args['db'];

	$stmt = $db->prepare("INSERT INTO `changes_stock` VALUES (?,?,?)");
	
	$db->beginTransaction();
	$stmt->execute([$args['changes'], $args['user'], $args['timestamp'] ]);
	$db->commit();
}

function record_deletes_fnc($args)
{
	$db  = $args['db'];

	$stmt = $db->prepare("INSERT INTO `deletes` VALUES (?,?,?,?)");

	$db->beginTransaction();
	if( isset($args['recs']) ){
		foreach( $args['recs'] as $id => $deletes ){
			$stmt->execute([$id, $deletes, $args['user'], $args['timestamp'] ]);
		}
	}
	else{ $stmt->execute($args['rec']); }
	$db->commit();
}