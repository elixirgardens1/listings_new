<?php
$files_used[] = 'views/listings.php'; //DEBUG

require_once 'format_listing_for_view.php';

//=========================================================================
// Build the table that displays on the listings view. 
// This also includes the 'Edit', 'Add' & 'Add Prime' buttons
//=========================================================================

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['listings']); echo '</pre>'; die();

$group_variations = [];
foreach( $session['listings'] as $id_lkup => $rec ){
	//DEBUG
	// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($rec); echo '</pre>';
	
	if( $group_prev != $rec['group_'] && '' != $group_prev ){
		// This code also appears at the end of script
		$return = spacer_and_buttons_fnc([
			'recs'          => $rec,
			'key'           => $keys[$group_prev],
			'product_names' => $product_names,
			'group_prev'    => $group_prev,
			'cpus'          => $cpus,
			'lvws'          => $lvws,
			'cat'           => $cat,
			'cat_id'        => $cat_id,
			'platform_post' => $platform_post,
		]);
		
		$forms_data[] = array_merge($return['forms_data'], ['vars' => $group_variations]);
		$group_variations = [];
		echo $return['html'];
	}
	
	$group_variations[] = $rec['variation'];
	
	$error_sty = isset($_POST['id']) && $_POST['id'] == $id_lkup ? ' style="border: 3px solid red;"' : ''; ?>
	<tr data-id_lkup='<?= $id_lkup ?>'<?= $error_sty ?>>
		<?php
		$notes_icon = '';
		if( '' != $rec['notes'] ){
			$notes_icon = '<div style="margin-top:0; margin-bottom:0; float: right;" class="tooltip"><img src="incs/notes_icon_s.png" style="margin-top:-5px; margin-bottom:-5px;" alt="note"><span class="tooltiptext">'.$rec['notes'].'</span></div>';
		}
		
		$courier_fmt = $rec['courier'];
		if( '10' == substr($courier_fmt, -2) ){
			$courier_fmt = str_replace(' 10', ' &pound;10', $rec['courier']);
		}
		
		// $existing_skus = '';
		$existing_skus = is_array($rec['sku_'.$platform_post]) ? implode('_-_',$rec['sku_'.$platform_post]) : '';
		?>
		<td class="product_name"><?= $rec['product_name'] ?><?= $notes_icon ?></td>
		<td class="packaging_band"><?= $rec['packaging_band'] ?></td>
		<td class="courier"><?= $courier_fmt ?></td>
		<td class="cost_per_unit"><?= $rec['cost_per_unit'] ?></td>
		<td class="lowest_variation_weight"><?= $rec['lowest_variation_weight'] ?></td>
		<td class="variation"><?= $rec['variation'] ?></td>
		<td class="total_weight"><?= $rec['total_weight'] ?></td><!-- CALCULATION -->
		<!-- Skus -->
		<td class="total_weight">
			<input type="button"
			class="btn btn_thin triggerSkuEdit"
			style="width: 60px;"
			name="edit_skus"
			data-id="<?= $id_lkup ?>"
			data-vars="<?= $rec['variation'] ?>"
			data-skus="<?= $existing_skus ?>"
			value="add/edit"
			onclick="return false;">
		</td>
		<!-- json_encode($rec['sku_'.$platform_post]) -->
		
		<?php
		// CREATE COMP COLUMNS
		$url = '';
		$type = '';
		// eBay, Floorworld or Prosalt
		if( 'e' == $platform_post || 'f' == $platform_post || 's' == $platform_post ){
			$url = 'https://www.ebay.co.uk/itm';
			$type = 'e_id';
		}
		elseif( 'a' == $platform_post || 'p' == $platform_post ){
			$url = 'https://www.amazon.co.uk/dp';
			$type = 'a_id';
		}
		elseif( 'w' == $platform_post ){
			$url = 'https://elixirgardensupplies.co.uk/product';
			$type = 'w_id';
		}
		
		$my_dropdown = '';
		if( 'p' != $platform_post && 'w' != $platform_post ){
			$id_lkup_skus = $session['listings'][$id_lkup]['sku_'.$platform_post];
			
			if( is_array($id_lkup_skus) ){ // NEWCODE
				$urls = [];
				foreach( $id_lkup_skus as $sku ){
					if( isset($dropdowns[$sku][$type]) ){
						$urls[] = "<a href=\"$url/{$dropdowns[$sku][$type]}\" target=\"_blank\">{$dropdowns[$sku][$type]}</a>";
					}
				}

				$make_dropdown = [];
				$make_dropdown[] = '<div class="dropdown">';
				if( count($urls) > 0 ){
					$make_dropdown[] = '<button class="dropbtn">URLs</button>';
					$make_dropdown[] = '<div class="dropdown-content">';
					$make_dropdown[] = implode(' ', $urls);
					$make_dropdown[] = '</div>';
				}
				$make_dropdown[] = '</div>';

				$my_dropdown = implode(' ', $make_dropdown);
			}
		}
		
		if( 'w' != $platform_post ){
			$comps = [ $rec['comp1'], $rec['comp2'], $rec['comp3'] ];
			$ids = [ $rec['id1'], $rec['id2'], $rec['id3'] ];
			$types = [ $rec['type1'], $rec['type2'], $rec['type3'] ];
			
			// $link_colour = [
			// 	'1' => '',
			// 	'2' => 'link_grn',
			// 	'3' => 'link_orange',
			// ];
			
			// $link_titles = [
			// 	'1' => 'Like 4 Like',
			// 	'2' => 'Cheapest',
			// 	'3' => 'Most Popular',
			// ];

			foreach( $comps as $i => $comp ){
				if( '' != $comp ){
					$comp = "<a class='{$link_colour[$types[$i] ]}' href='$url/{$ids[$i]}' title='{$link_titles[$types[$i] ]}' target='_blank'>$comp</a>";
				}
				echo "<td class='comp1'>$comp</td>";
			}
		}
		?>
		<!-- OURS -->
		<td class="comp3"><?= $my_dropdown ?></td>
		<td class="prev_price"><?= $rec['prev_price'] ?></td>
		<td class="pricing_suggestion_20perc"><?= $rec['pricing_suggestion_20perc'] ?></td><!-- CALCULATION -->

		<?php if( 'a' == $platform_post ): ?>
			<td class="ebay_price"><?=  $rec['new_price_e'] ?></td>
		<?php elseif( 'p' == $platform_post ): ?>
			<td class="amazon_price"><?=  $rec['new_price_a'] ?></td>
		<?php endif; ?>

		<td class="new_price new_price_hl"><?= $rec['new_price'] ?></td><!-- CALCULATION -->
		<td class="cpu_to_cust"><?= $rec['cpu_to_cust'] ?></td><!-- CALCULATION -->
		<td class="perc_advertising"><?= $rec['perc_advertising'] ?></td>
		
		<!-- Profit(£) -->
		<td class="<?= $rec['cls_colour_profit'] ?>"><?= $rec['profit'] ?></td><!-- CALCULATION -->
		<!-- Profit % -->
		<td class="<?= $rec['cls_colour_profit_perc'] ?>"><?= $rec['profit_perc'] ?></td><!-- CALCULATION -->
		
		<?php if( 'w' == $platform_post ): ?>
		<td class="<?= $rec['cls_colour_profit_10off'] ?>"><?= $rec['profit_10off'] ?></td><!-- CALCULATION -->
		<td class="<?= $rec['cls_colour_profit_10off_perc'] ?>"><?= $rec['profit_10off_perc'] ?></td><!-- CALCULATION -->
		<?php endif; ?>
		
		<td class="total_product_cost <?= $rec['cls_color_total_product_cost'] ?>"><?= $rec['total_product_cost'] ?></td><!-- CALCULATION -->
		<td class="postage <?= $rec['cls_color_postage'] ?>"><?= $rec['postage'] ?></td><!-- CALCULATION -->
		
		<td class="vat"><?= $rec['vat'] ?></td><!-- CALCULATION -->
		<?php if( 'w' != $platform_post ): ?>
		<td class="fees"><?= $rec['fees'] ?></td><!-- CALCULATION -->
		<?php endif; ?>
		
		<td class="pp1"><?= $rec['pp1'] ?></td><!-- CALCULATION -->
		<?php $rec['pp2'] = 0; ?>
		<td class="pp2"><?= $rec['pp2'] ?></td><!-- CALCULATION -->
		
		<!-- CHECKBOX -->
		<td class='tick fvis-status-generated'>
			<input id='<?= $id_lkup ?>' class="sudo_cbx" type='checkbox' name='export_remove[]' value='<?= $id_lkup ?>'>
			<label class="cbx" for='<?= $id_lkup ?>'></label>
		</td>		
	</tr>
<?php
	$group_prev = $rec['group_']; $product_key_prev = $rec['key'];
}
// NEW_PRICE_MATRIX
$all_vars = [];
foreach ($price_matrix as $i => $vals) {
	$all_vars["{$vals['variation']}"] = $vals['variation'];
}
ksort($all_vars);
$all_vars = array_values($all_vars);

array_unshift($all_vars, '&nbsp;');

$first_variation = $price_matrix[0]['variation'];

$new_price_matrix = [];
$new_price_matrix[] = $all_vars;

$var_price = [];
$product_names_ = [];
$prev_group = '';
foreach ($price_matrix as $vals) {
	if( $prev_group != $vals['group'] ){
		$prev_group = $vals['group'];

		$product_name = $vals['product_name'];
		$product_names_[] = $product_name;
	}
	$var_price[$product_name]["{$vals['variation']}"]['price'] = $vals['new_price'];
	$var_price[$product_name]["{$vals['variation']}"]['id'] = $vals['id_lkup'];
	$var_price[$product_name]["{$vals['variation']}"]['packing'] = $vals['packing'];
}

foreach ($var_price as $product_name => $v) {
	$tmp = [];
	foreach ($new_price_matrix[0] as $i => $val) {
		if( $i > 0 ){
			if( isset($var_price[$product_name]["$val"]) ){
				$tmp[] = $var_price[$product_name]["$val"];
			}
			else{ $tmp[] = 'N/A'; }
		}
		else{ $tmp[] = $product_name; }
	}
	$new_price_matrix[] = $tmp;
}

$return = spacer_and_buttons_fnc([
	'recs'          => $rec,
	'key'           => $keys[$group_prev],
	'product_names' => $product_names,
	'group_prev'    => $group_prev,
	'cpus'          => $cpus,
	'lvws'          => $lvws,
	'cat'           => $cat,
	'cat_id'        => $cat_id,
	'platform_post' => $platform_post,
]);

$forms_data[] = array_merge($return['forms_data'], ['vars' => $group_variations]);

echo $return['html'];
?>
</table>

<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
<input type="hidden" name="cat" value="<?= $_POST['cat'] ?>">
<input type="hidden" name="cat_id" value="<?= $_POST['cat_id'] ?>">
<input type="hidden" name="platform" value="<?= $_POST['platform'] ?>">
<input type="hidden" name="export_remove_option_hidden" value="e">
<input type="hidden" name="view" value="Listings">

</form>

<script>
	$(function() {
		$('#export_remove_option').on('change', function() {
			$('input[name="export_remove_option_hidden"]').val( $(this).val() );
		});
	});
</script>


<?php require_once 'views/price_matrix.php'; ?>

<?php require_once 'incs/modal_boxes.php'; ?>
