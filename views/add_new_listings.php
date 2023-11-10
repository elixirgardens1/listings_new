<?php
$files_used[] = 'views/add_new_listings.php'; //DEBUG

$sql = "SELECT `key`,`product` FROM `products`";
$results = $db_stock->query($sql);
$keys_products = $results->fetchAll(PDO::FETCH_KEY_PAIR);

// Sort associative array by values (product) —— case-insensitive
uasort($keys_products,'strnatcasecmp');

$txt_filter_select = isset($_POST['hidden_filter_select']) ? $_POST['hidden_filter_select'] : '';
?>

<form method="post" class="fl-l mr20">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	<input type="submit" name="view" value="Dashboard" class="btn">
</form>

<span class="fl-l error-txt"></span>

<div class="h60"></div>

<form method="post">
	<div>
		<fieldset style="display: inline-block; border-radius: 8px; border: 1px solid #ccc;">
			<legend><h2 style="color: #000; font-size: 20px;">Add Name &amp; Variations &mdash; Listings</h2></legend>

			<table>
				<tr>
					<td class="txt3 pr10 va_m">Category:</td>
					<td>
						<select name="cat" class="large_select" onchange="this.form.submit()" required>
							<option value="">--SELECT--</option>
						<?php foreach( $session['lookup_prod_cats_all'] as $key => $val ){ ?>
							<?= sel_opt_fnc($key, $session['lookup_cats'][$key], isset($_POST['cat']) ? $_POST['cat'] : '') ?>
						<?php } ?>
						</select>

						<div style="float:right">
							<span class="txt3 pr10 va_m">Packing:</span>
							<select name="packing" class="large_select">
								<option value="">NONE</option>
								<option value="b">BAG</option>
								<option value="t">TUB</option>
							</select>
						</div>
					</td>
				</tr>
				
				<?php if( isset($_POST['cat']) && '' != $_POST['cat'] ){ ?>
				<tr>
					<td class="txt3 pr10 va_m">Listings:</td>
					<td>
						<select name="cat_id" class="large_select" onchange="this.form.submit()" required>
						<?php foreach( $session['lookup_prod_cats_all'][ $cat ] as $key => $val ){ ?>
							<?= sel_opt_fnc($key, $val, isset($_POST['cat_id']) ? $_POST['cat_id'] : '') ?>
						<?php } ?>
						</select>
					</td>
				</tr>
				<?php } ?>

				<tr>
					<td class="txt3 pr10 va_m">Product Name:</td>
					<td class="pr10 va_m">
						<input id="product_name_listings" class="bsTxtInput w700 h36 mb10" type="text" name="product_name_listings" value="<?= isset($data['product']) ? $data['product'] : ''; ?>" placeholder="Product Name" autocomplete="off" data-lpignore="true" required>
					</td>
				</tr>
				
				<tr>
					<td class="txt3 pr10 va_m">Variation:</td>
					<td class="pr10 va_m">
						<input id="variation" class="bsTxtInput h36 w700 mb10" type="text" name="variation" value="<?= isset($data['variation']) ? $data['variation'] : ''; ?>" placeholder="v1 v2 v3 etc" autocomplete="off" data-lpignore="true" required>
					</td>
				</tr>
				
				<tr>
					<td class="txt3 pr10 va_m">L V Weight:</td>
					<td class="pr10 va_m">
						<input id="variation" class="bsTxtInput h36 w300" type="text" name="lvw" value="<?= isset($data['lvw']) ? $data['lvw'] : ''; ?>" autocomplete="off" data-lpignore="true" required>
					</td>
				</tr>
			</table>
		</fieldset>
	</div>
	
	<div style="margin-top: 10px;">
		<div style="float: left;">
			<fieldset style="display: inline-block; border-radius: 8px; border: 1px solid #ccc; float: left;">
				<legend><h2 style="color: #000; font-size: 20px;">Select key</h2></legend>
				
				<input type="text" value="<?= $txt_filter_select ?>" class="bsTxtInput h36" id="txt_filter_select" placeholder="Enter Search Characters" style="width: 400px;" autocomplete="off" onkeyup="filter()">
				
				<br>

				<input type="hidden" name="hidden_filter_select">

				<?php
				//=========================================================================
				// Prepend new key and value to array
				//=========================================================================
				// $keys_products = array_reverse($keys_products, true);
				// $keys_products['ADD_MULTIPLE_KEYS'] = 'ADD MULTIPLE KEYS';
				// $keys_products = array_reverse($keys_products, true);
				?>

				<select id="select_key" name="key" size="16" style="width: 560px;" required>
				<?php foreach( $keys_products as $key => $name ){ ?>
					<?= sel_opt_fnc($key, $name, isset($_POST['key']) ? $_POST['key'] : '') ?>
				<?php } ?>
				</select>
			</fieldset>
		</div>
		
		<div id="jquery_tbl" class="fl-l mt28 ml14"></div>
	</div>
	
	<div class="cl-l"></div>
	
	<div class="mt10">
		<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
		<input type="hidden" name="view" value="Add New Listings">
		<input type="submit" name="add_new_listings" class="btn w140" value="Add">
	</div>
	
	<input type="hidden" name="add_new_listings_to_db">
</form>

<script>
	// DEBUG: pre-populate form fields
	// $('[name="cat"]').val('ele');
	// $('[name="packing"]').val('b');
	// $('[name="product_name_listings"]').val('TEST');
	// $('[name="variation"]').val('1 2 3 4 5');
	// $('[name="lvw"]').val('3');
	
	
	let existingProducts = {};
	<?php
	if( isset($_POST['cat']) && '' != $_POST['cat'] ){
		$cat_ids = array_values(array_filter(array_keys($session['lookup_prod_cats_all'][ $cat ]) ) );
		$cat_ids_str = implode("','", $cat_ids);
		$sql = "SELECT `product_name` FROM `listings` WHERE `cat_id` IN ('$cat_ids_str')";
		$results = $db_listings->query($sql);
		$product_names = $results->fetchAll(PDO::FETCH_COLUMN);
		$tmp = [];
		// See 'add_new_product.php' for description of this section of code
		foreach( $product_names as $product_name ){
			$product_name = str_replace('"', '&quot;', $product_name);
			$tmp[] = "\"$product_name\": 1,";
		} ?>
	existingProducts = {<?php echo implode("", $tmp) ?>};
	<?php } ?>


	var id_inc = 0;
	var key_items = [];

	$(function(){
		$('[name="product_name_listings"]').focus();
		
		//=========================================================================
		// The following detects when the dynamically created buttons (class = del) are clicked.
		// The normal code "$('.del').keyup(function(){" only works on hardcoded buttons.
		//=========================================================================
		$(document).on("click",".del", function (){
			var pos = $(this).attr("data-id");
			// console.log( $(this).attr("data-id") );

			key_items.splice(pos, 1);
			id_inc--;

			// delete key_items[pos];

			make_keys_tbl_fnc(0);
		});

		$('#select_key').change(function(){
			make_keys_tbl_fnc(1);
		});
	});
	
	$('[name="product_name_listings"]').on('keyup', function(){
		let input_txt = $(this).val();
		input_txt = input_txt.replace('"', '&quot;');
		
		// Check if Product Name contain underscores
		if( input_txt.includes('_') ){
			$('.error-txt').css({'display': 'inline-block'});
			$('.error-txt').html( 'Product Name cannot contain underscores!' );
		}
		// Check if Product Name already exists
		else if( existingProducts[ input_txt ] ){
			$('.error-txt').css({'display': 'inline-block'});
			$('.error-txt').html( input_txt + ' already exists!' );
		}
		else{
			$('.error-txt').css({'display': 'none'});
		}
	});

	function make_keys_tbl_fnc(append_){
		if( append_ ){
			var key_id = $('#select_key').val();
			var key_name = $('#select_key').find(":selected").text();
			var button = '<button data-id="'+ id_inc++ +'" class="btn del" type="button" style="width: 26px; height: 26px; padding: 0;">&mdash;</button> ';
			// The data-id value gets added later when the table gets created. 
			var button = '<button data-id="" class="btn del" type="button" style="width: 26px; height: 26px; padding: 0;">&mdash;</button> ';

			key_items.push(button + key_name + '<input type="hidden" name="key_name[]" value="'+ key_id +'">');
		}

		$('#jquery_tbl').empty();

		var table = $('<table/>').addClass('style-tbl');

		var array_size = key_items.length;
		for( i=0; i<array_size; i++ ){
			// The replace function is used
			if( array_size > 1 ){ table.append('<tr><td>'+ key_items[i].replace('data-id="', 'data-id="' + i) +'</td><td><input type="text" name="key_amount[]" style="width: 30px;" autocomplete="off" required></td></tr>'); }
			else{ table.append('<tr><td>'+ key_items[i] +'</td></tr>'); }
		}
		$('#jquery_tbl').append(table);
	}
	
	function filter() {
		var count_dropdown_options = 0;
		var keyword = document.getElementById("txt_filter_select").value;
		var fleet = document.getElementById("select_key");
		for (var i = 0; i < fleet.length; i++) {
			var txt = fleet.options[i].text;
			if( txt.toLowerCase().indexOf( keyword.toLowerCase() ) < 0 && keyword.trim() !== '' ){ fleet.options[i].style.display = 'none'; }
			else{
				fleet.options[i].style.display = 'list-item';
				count_dropdown_options++;
			}
		}
		$('#count_dropdown_options').html('Total Products: ' + count_dropdown_options);
		$('input[name="hidden_filter_select"]').val(keyword);
	}

	// Keep filtered select option (drop-down) when page reloads after submitted data
	<?php if( isset($_POST['key']) ){ ?>
		document.getElementById("txt_filter_select").value = '<?= $txt_filter_select ?>';
		filter();
	<?php } ?>
</script>