<?php
$files_used[] = 'views/listings_edit.php'; //DEBUG

require_once 'format_listing_for_view.php';

//DEBUG
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['listings']); echo '</pre>'; die();

foreach( $session['listings'] as $id_lkup => $rec ){ ?>
	<tr class="edit_listing" data-id_lkup='<?= $id_lkup ?>' data-pkging='<?= $rec['packing'] ?>'>
		<td>
			<input type="hidden" name="listings_data[<?= $id_lkup ?>][packing]" value="<?= $rec['packing'] ?>">
			<input type="text"
				class="product_name txtfld def"
				name="listings_data[<?= $id_lkup ?>][product_name]"
				value="<?= $rec['product_name'] ?>"
				data-lpignore="true"
				autocomplete="off"
				required>
		</td>
		<!-- DROPDOWN - Packaging Band -->
		<td class="<?= isset($new_listings_added) ? 'red-bg' : '' ?>">
			<select class="packaging_band sel-sheet" name="listings_data[<?= $id_lkup ?>][packaging_band]">
			<?php foreach( $session['lookup_postage_bands'] as $key => $v ){ ?>
				<?= sel_opt_fnc($key, $key, isset($rec['packaging_band']) ? $rec['packaging_band'] : '') ?>
			<?php } ?>
			</select>
		</td>
		<!-- DROPDOWN - Courier -->
		<td class="<?= isset($new_listings_added) ? 'red-bg' : '' ?>">
			<select class="courier sel-sheet" name="listings_data[<?= $id_lkup ?>][courier]">
			<?php foreach( $session['lookup_couriers'] as $key => $v ){
				// Remove PRIME options from courier's drop-down
				if( 'p' != $platform_post && false !== stripos($key, 'prime') ){ continue; } ?>
				<?= sel_opt_fnc($key, add_pound_sign_fnc($key), isset($rec['courier']) ? $rec['courier'] : '') ?>
			<?php } ?>
			</select>
		</td>
		<td>
			<input type="text"
				class="cost_per_unit txtfld def"
				name="listings_data[<?= $id_lkup ?>][cost_per_unit]"
				value="<?= $rec['cost_per_unit'] ?>"
				data-lpignore="true"
				autocomplete="off">
		</td>
		<td>
			<input type="text"
				class="lowest_variation_weight txtfld def"
				name="listings_data[<?= $id_lkup ?>][lowest_variation_weight]"
				value="<?= $rec['lowest_variation_weight'] ?>"
				data-lpignore="true"
				autocomplete="off">
		</td>
		<td>
			<input type="text"
				class="variation txtfld def"
				name="listings_data[<?= $id_lkup ?>][variation]"
				value="<?= $rec['variation'] ?>"
				data-lpignore="true"
				autocomplete="off">
		</td>
		<td class="total_weight"><?= $rec['total_weight'] ?></td><!-- CALCULATION -->
		<?php
		// COMPS & IDs
		if( 'w' != $platform_post ){
			$not_empty_colour = '' == $rec['comp1'] && '' == $rec['comp2'] && '' == $rec['comp3'] ? '' : '#ff0';
			$exist = '' == $not_empty_colour ? 0 : 1;
			$data_id = $id_lkup; ?>
		<td>
			<input type="hidden" id="<?= $id_lkup ?>_comp1" name="listings_data[<?= $id_lkup ?>][comp1]" value="<?= $rec['comp1'] ?>">
			<input type="hidden" id="<?= $id_lkup ?>_comp2" name="listings_data[<?= $id_lkup ?>][comp2]" value="<?= $rec['comp2'] ?>">
			<input type="hidden" id="<?= $id_lkup ?>_comp3" name="listings_data[<?= $id_lkup ?>][comp3]" value="<?= $rec['comp3'] ?>">
			
			<input type="hidden" id="<?= $id_lkup ?>_id1" name="listings_data[<?= $id_lkup ?>][id1]" value="<?= $rec['id1'] ?>">
			<input type="hidden" id="<?= $id_lkup ?>_id2" name="listings_data[<?= $id_lkup ?>][id2]" value="<?= $rec['id2'] ?>">
			<input type="hidden" id="<?= $id_lkup ?>_id3" name="listings_data[<?= $id_lkup ?>][id3]" value="<?= $rec['id3'] ?>">
			
			<input type="hidden" id="<?= $id_lkup ?>_type1" name="listings_data[<?= $id_lkup ?>][type1]" value="<?= $rec['type1'] ?>">
			<input type="hidden" id="<?= $id_lkup ?>_type2" name="listings_data[<?= $id_lkup ?>][type2]" value="<?= $rec['type2'] ?>">
			<input type="hidden" id="<?= $id_lkup ?>_type3" name="listings_data[<?= $id_lkup ?>][type3]" value="<?= $rec['type3'] ?>">
			
			<input type="hidden" id="<?= $id_lkup ?>_exist" name="listings_data[<?= $id_lkup ?>][exist]" value="<?= $exist ?>">
			
			<input type="button"
				class="btn btn_thin triggerModalEdit"
				style="width: 110px;color: <?= $not_empty_colour ?>;"
				name="edit_comps_ids"
				data-id="<?= $data_id ?>"
				data-vars="<?= $rec['variation'] ?>"
				value="edit comps & ids"
				onclick="return false;">
		</td>
		<?php } ?>
		
		<!-- OURS -->
		<td>&nbsp;</td>
		<td>
			<input type="text"
				class="prev_price txtfld def"
				name="listings_data[<?= $id_lkup ?>][prev_price]"
				value="<?= $rec['prev_price'] ?>"
				data-lpignore="true"
				autocomplete="off">
		</td>
		<td class="pricing_suggestion_20perc"><?= $rec['pricing_suggestion_20perc'] ?></td><!-- CALCULATION -->
		
		<?php if( 'a' == $platform_post ){ ?>
			<td class="ebay_price"><?=  $rec['new_price_e'] ?></td>
		<?php } elseif( 'p' == $platform_post ){ ?>
			<td class="amazon_price"><?=  $rec['new_price_a'] ?></td>
		<?php } ?>
		
		<?php if( 'w' != $platform_post ){  ?>
			<td class="new_price_suggestion"><?= $rec['new_price'] ?></td><!-- CALCULATION -->
		<?php } ?>
		
		<td class="new_price_hl">
			<input type="text"
			class="new_price txtfld def"
			name="listings_data[<?= $id_lkup ?>][new_price]"
			style="width: 50px;" value="<?= $rec['new_price'] ?>"
			data-lpignore="true"
			autocomplete="off">
		</td>
		<td class="cpu_to_cust"><?= $rec['cpu_to_cust'] ?></td><!-- CALCULATION -->
		
		<td>
			<input type="text"
			class="perc_advertising txtfld def"
			name="listings_data[<?= $id_lkup ?>][perc_advertising]"
			value="<?= $recs['perc_advertising'] ?>"
			data-lpignore="true"
			autocomplete="off">
		</td>
		
		<td class="profit <?= $rec['cls_colour_profit'] ?>"><?= $rec['profit'] ?></td><!-- CALCULATION -->
		<td class="profit_perc <?= $rec['cls_colour_profit_perc'] ?>"><?= $rec['profit_perc'] ?></td><!-- CALCULATION -->
		
		<?php if( 'w' == $platform_post ){  ?>
			<td class="profit_10off <?= $rec['cls_colour_profit_10off'] ?>"><?= $rec['profit_10off'] ?></td><!-- CALCULATION: -->
			<td class="profit_10off_perc <?= $rec['cls_colour_profit_10off_perc'] ?>"><?= $rec['profit_10off_perc'] ?></td><!-- CALCULATION: -->
		<?php } ?>
		
		<td class="total_product_cost <?= $rec['cls_color_total_product_cost'] ?>"><?= $rec['total_product_cost'] ?></td><!-- CALCULATION -->
		<td class="postage <?= $rec['cls_color_postage'] ?>"><?= $rec['postage'] ?></td><!-- CALCULATION -->
		
		<td class="vat"><?= $rec['vat'] ?></td><!-- CALCULATION -->
		
		<?php if( 'w' != $platform_post ){  ?>
			<td class="fees"><?= $rec['fees'] ?></td><!-- CALCULATION -->
		<?php } ?>
		
		<td class="pp1"><?= $rec['pp1'] ?></td><!-- CALCULATION -->
		<?php $rec['pp2'] = 0; ?>
		<td class="pp2"><?= $rec['pp2'] ?></td><!-- CALCULATION -->
	</tr>
<?php } ?>
</table>

<input type="submit" name="save_listing_to_db" value="Save" class="btn w200 mb10">

<input type="hidden" name="view" value="Listings">

<input type="hidden" name="posY" value="<?= $_POST['posY'] ?>">
<input type="hidden" name="cat" value="<?= isset($_POST['cat']) ? $_POST['cat'] : '' ?>">
<input type="hidden" name="cat_id" value="<?= isset($_POST['cat_id']) ? $_POST['cat_id'] : '' ?>">
<input type="hidden" name="platform" value="<?= $_POST['platform'] ?>">
<input type="hidden" name="user" value="<?= $_POST['user'] ?>">

</form>

<form method="post">
	<input type="submit" name="back_to_category" value="Back to category" class="btn w200">
	
	<input type="hidden" name="view" value="Listings">
	
	<input type="hidden" name="posY" value="<?= $_POST['posY'] ?>">
	<input type="hidden" name="cat" value="<?= isset($_POST['cat']) ? $_POST['cat'] : '' ?>">
	<input type="hidden" name="cat_id" value="<?= isset($_POST['cat_id']) ? $_POST['cat_id'] : '' ?>">
	<input type="hidden" name="platform" value="<?= $_POST['platform'] ?>">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
</form>

<?php require_once 'js/js_form_fld_calculations.php'; ?>

<?php require_once 'incs/modal_boxes.php'; ?>