<?php
$files_used[] = 'views/listings_add.php'; //DEBUG
$files_used[] = 'js/js_functions.js';
?>
<tr data-id_lkup='1'>
	<td>
		<input type="text"
		class="product_name txtfld def"
		name="product_name"
		placeholder="* 3 characters or more"
		data-lpignore="true"
		autocomplete="off">
	</td>
	
	<td>
		<select class="packaging_band sel-sheet" name="packaging_band">
		<?php foreach( $session['lookup_postage_bands'] as $key => $v ){ ?>
			<option value="<?= $key ?>"><?= $key ?></option>
		<?php } ?>
		</select>
	</td>
	
	<td>
		<select class="courier sel-sheet" name="courier">
		<?php foreach( $session['lookup_couriers'] as $key => $v ){
			// Remove PRIME options from courier's drop-down
			if( 'p' != $platform_post && false !== stripos($key, 'prime') ){ continue; } ?>
			<option value="<?= $key ?>"><?= add_pound_sign_fnc($key) ?></option>
		<?php } ?>
		</select>
	</td>
	
	<td>
		<input type="text"
		class="cost_per_unit txtfld def"
		name="cost_per_unit"
		value="<?= $_POST['cpu'] ?>"
		data-lpignore="true"
		autocomplete="off">
	</td>
	
	<td>
		<input type="text"
		class="lowest_variation_weight txtfld def"
		name="lowest_variation_weight"
		placeholder="*"
		value="<?= $_POST['lvw'] ?>"
		data-lpignore="true"
		autocomplete="off">
	</td>
	
	<td>
		<input type="text"
		class="variation txtfld def"
		name="variation"
		placeholder="*"
		data-lpignore="true"
		autocomplete="off">
	</td>
</tr>
</table>
	
	<input type="hidden" name="vars" value="<?= $_POST['vars'] ?>">
	<input type="hidden" name="posY" value="<?= $_POST['posY'] ?>">
	<input type="hidden" name="group_edit" value="<?= $_POST['group_edit'] ?>">
	<input type="hidden" name="view" value="<?= $_POST['view'] ?>">
	<input type="hidden" name="key" value="<?= $_POST['key'] ?>">
	
	<input type="hidden" name="cat" value="<?= $_POST['cat'] ?>">
	<input type="hidden" name="cat_id" value="<?= $_POST['cat_id'] ?>">
	<input type="hidden" name="platform" value="<?= $_POST['platform'] ?>">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	
	<div>
		<div class="fl-l mr10">
			<input type="submit" id="btn_sbmt" name="submit" value="Add" class="btn w200 mb10 btn-disabled" disabled>
		</div>
		
		<div class="fl-l">
			<div class="mb10 fs16">* required fields</div>
			<div class="err_msg_display h12"></div>
		</div>
	</div>
	
	<div class="cl-l"></div>
	
	<input type="hidden" name="add_listing_to_db">

</form>
<?php require_once 'back_to_cat.php'; ?>

<script src="js/js_functions.js"></script>
<script>
	//=========================================================================
	// OPERATION:
	// JS is used for client side validation.
	// The 'Add' button is disabled on page load. The following fields are
	// all required for it to become enabled:
	// 1) 'Product Name' 2) 'Lowest Variation Weight' 3) 'Variation'
	// If the 'Product Name' is less than 3 characters or contains an underscore,
	// error messages are displayed:
	// "ERROR: Product Name must be 3 or more characters long!"
	// "ERROR: Product Name cannot contain underscores!"
	// If the 'Lowest Variation Weight' field wasn't added automatically
	// on page load (this happens if all values are equal within the group)
	// a value must be entered. If the value gets removed the following
	// error message gets displayed:
	// "ERROR: A 'Lowest Variation Weight' value is required!"
	// If a 'Variation' field value is entered that already exists within
	// the group the following error message gets displayed:
	// "ERROR: Variation already exists!"
	// If an entered value gets removed, the following error message gets displayed:
	// "ERROR: A 'Variation' value is required!"
	
	// NOTE: enable_disable_fnc() location js/js_functions.js
	//=========================================================================
	
	$('.product_name').focus();
	let product_name_flag = false;
	let lvw_flag          = '' != $('[name="lowest_variation_weight"]').val() ? true : false;
	let var_flag          = false;
	let true_false        = false;
	let error_msg = {};
	let error_msg_str = '';
	
	$('.product_name').on('keyup', function(){
		product_name_flag = false;
		true_false = product_name_flag & lvw_flag & var_flag;
		enable_disable_fnc(true_false);
		
		error_msg['product_name'] = '';
		
		if( $(this).val().length > 2 ){
			if( -1 != $(this).val().indexOf('_') ){
				product_name_flag = false;
				true_false = product_name_flag & lvw_flag & var_flag;
				enable_disable_fnc(true_false);
				error_msg['product_name'] = 'ERROR: Product Name cannot contain underscores!';
			}
			else{
				product_name_flag = true;
				true_false = product_name_flag & lvw_flag & var_flag;
				enable_disable_fnc(true_false);				
				error_msg['product_name'] = '';
			}
			
			error_msg_str = '';
			for( let i in error_msg ){
				error_msg_str += error_msg[i] + ", ";
			}
			error_msg_str = error_msg_str.substring(0, error_msg_str.length-2);
		}
		else{
			product_name_flag = false;
			true_false = product_name_flag & lvw_flag & var_flag;
			enable_disable_fnc(true_false);
			error_msg['product_name'] = 'ERROR: Product Name must be 3 or more characters long!';
		}
		
		error_msg_fnc();
	});
	
	
	// This is stored in a hidden field: value="1|5|10|20|40|60|80|100|120|140|160|180|200|400|600|800"
	let vars = $('input[name="vars"]').val();
	let vars_arr = vars.split("|");
	
	$('.variation').on('keyup', function(){
		var_flag = '' != $(this).val()
			// check that variation doesn't already exist
			&& !vars_arr.includes( $(this).val() );
		true_false = product_name_flag & lvw_flag & var_flag;
		
		enable_disable_fnc(true_false);
		
		error_msg['variation'] = '';
		if( vars_arr.includes( $(this).val() ) ){
			error_msg['variation'] = 'ERROR: Variation already exists!';
		}
		else if( '' == $(this).val() ){
			error_msg['variation'] = 'ERROR: Variation is requird!';
		}
		
		error_msg_fnc();
	});
	
	$('.lowest_variation_weight').on('keyup', function(){
		lvw_flag = '' != $(this).val();
		true_false = product_name_flag & lvw_flag & var_flag;
		
		enable_disable_fnc(true_false);
		
		error_msg['lowest_variation_weight'] = '';
		if( !lvw_flag ){
			error_msg['lowest_variation_weight'] = 'ERROR: Lowest Variation Weight is requird!';
		}
		error_msg_fnc();
	});


	function error_msg_fnc()
	{
		let error_msg_str = JSON.stringify(error_msg);
		
		error_msg_str = error_msg_str.replace('{"', '');
		error_msg_str = error_msg_str.replace('"}', '');
		error_msg_str = error_msg_str.replace('product_name":"', '');
		error_msg_str = error_msg_str.replace('variation":"', '');
		error_msg_str = error_msg_str.replace('lowest_variation_weight":"', '');
		
		console.log(error_msg_str);
		
		error_msg_str = error_msg_str.replaceAll('","', '<br>');
		
		// Remove line break if no error message precedes it
		if( /^<br>/.test(error_msg_str) ){
			error_msg_str = error_msg_str.replace('<br>', '');
		}
		
		$('.err_msg_display').html(error_msg_str);
	}
</script>