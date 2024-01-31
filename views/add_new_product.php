<?php
$files_used[] = 'views/add_new_product.php'; //DEBUG

// Retrieve suppliers from products@stock_control.db3
$sql = "SELECT DISTINCT primary_supplier,secondary_supplier FROM `products`";
$results = $db_stock->query($sql);
$results_suppliers = $results->fetchAll(PDO::FETCH_ASSOC);

$suppliers_dropdown = [];
foreach( $results_suppliers as $rec ){
	if( '' != $rec['primary_supplier'] ){ $suppliers_dropdown[ $rec['primary_supplier'] ] = 1; }
	if( '' != $rec['secondary_supplier'] ){ $suppliers_dropdown[ $rec['secondary_supplier'] ] = 1; }
}
$suppliers_dropdown = array_keys($suppliers_dropdown);
sort($suppliers_dropdown);
?>

<form method="post" style="float: left;">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	<input type="submit" name="view" value="Dashboard" class="btn">
</form>

<div class="h60"></div>

<form method="post">
	<fieldset style="display: inline-block; border-radius: 8px; border: 1px solid #ccc;">
		<legend><h2 style="color: #000; font-size: 20px;">Add New Product &mdash; Stock Control</h2></legend>
		
		<table>
			<tr>
				<td class="txt3 pr10 va_m">Product Name:</td>
				<td class="va_m"><input id="product" class="bsTxtInput w700 h36" type="text" name="product" value="<?= isset($data['product']) ? $data['product'] : ''; ?>" placeholder="Product Name" autocomplete="off" data-lpignore="true" required></td>
			</tr>
		</table>

		<table>
			<tr>
				<td class="txt3 pr10 va_m">Cat:</td>
				<td class="pr10 va_m">
					<select name="cat" id="cat" class="large_select" required>
						<option value="">- Select Cat -</option>
						<?php foreach( $session['lookup_cats'] as $key => $val ){ ?>
							<?= sel_opt_fnc($key, $val, isset($_POST['cat']) ? $_POST['cat'] : '') ?>
						<?php } ?>
					</select>
				</td>

				<td class="txt3 pr10 va_m">Cost:</td>
				<td class="va_m"><input id="cost" class="bsTxtInput w200 mr20 h36" type="text" name="cost" value="<?= isset($data['cost']) ? $data['cost'] : ''; ?>" placeholder="Cost (integer or decimal)" autocomplete="off" data-lpignore="true" pattern="^\d+(\.\d+)?$"></td>

				<td class="txt3 pr10 va_m">Units:</td>
				<td class="pr10 va_m">
					<select name="units" id="units" class="large_select" required>
						<option value="">- Select Units -</option>
						<?php foreach( $lookup_units as $key => $val ){ ?>
							<?= sel_opt_fnc($key, $val, isset($data['units']) ? $data['units'] : '') ?>
						<?php } ?>
					</select>
				</td>

				<td class="txt3 pr10 va_m">Room:</td>
				<td class="pr10 va_m">
					<select name="room" id="room" class="large_select" required>
						<option value="">- Select Room -</option>
						<?php
						// foreach( $lookup_rooms as $room_id => $room ){
						foreach( $lookup_rooms as $room ){
							$room_id = strtolower(substr($room, 0,1));
						?>
							<?= sel_opt_fnc($room_id, $room, isset($data['room']) ? $data['room'] : '') ?>
						<?php } ?>
					</select>
				</td>
			</tr>
		</table>
		
		<table>
			<tr>
				<td class="txt3 pr10 va_m">Pkg Qty:</td>
				<td class="va_m"><input id="pkg_qty" class="bsTxtInput w100 h36" type="text" name="pkg_qty" value="" autocomplete="off" data-lpignore="true" required></td>

				<td>&nbsp;</td>

				<td class="txt3 pr10 va_m">Pkg Multiples:</td>
				<td class="va_m"><input id="pkg_multiples" class="bsTxtInput w100 h36" type="text" name="pkg_multiples" value="" autocomplete="off" data-lpignore="true" required></td>

				<td>&nbsp;</td>

				<td class="txt3 pr10 va_m">Primary Supplier:</td>
				<td class="va_m">
					<input id="pri_sup" class="bsTxtInput w200 h36 fl-l mr10" type="text" name="pri_sup" value="" autocomplete="" data-lpignore="true" required>
					
					<select name="supplier" id="supplier" class="large_select">
						<option value="">- Existing Suppliers -</option>
						<?php foreach( $suppliers_dropdown as $supplier ){ ?>
							<?= sel_opt_fnc($supplier, $supplier, isset($data['supplier']) ? $data['supplier'] : '') ?>
						<?php } ?>
					</select>
				</td>
			</tr>
		</table>
	</fieldset>
	
	<div class="h20"></div>
	
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	<input type="hidden" name="view" value="<?= $_POST['view'] ?>">
	
	<div class="fl-l mr50">
		<input type="submit" name="add_new_product" class="btn w140" value="Add">
	</div>
	
	<span class="error-txt"></span>
	
	<input type="hidden" name="add_new_product_to_db">
</form>

<script>
	// DEBUG: pre-populate form fields
	// $('[name="product"]').val('Test Product');
	// $('[name="cat"]').val('agg');
	// $('[name="cost"]').val('9.99');
	// $('[name="units"]').val('q');
	// $('[name="room"]').val('1');
	// $('[name="room"]').val('m');
	// $('[name="pkg_qty"]').val('1');
	// $('[name="pkg_multiples"]').val('1');
	// $('[name="pri_sup"]').val('China');
	
	// $('[name="product"]').val('Soil Warming Cable 3"');
	// $('[name="cat"]').val('ele');
	// $('[name="cost"]').val('3.96');
	// $('[name="units"]').val('q');
	// $('[name="pkg_qty"]').val('1');
	// $('[name="pkg_multiples"]').val('1');
	// $('[name="pri_sup"]').val('China');
	
	
	<?php
	$sql = "SELECT `product` FROM `products`";
	$results = $db_stock->query($sql);
	$products = $results->fetchAll(PDO::FETCH_COLUMN);
	$tmp = [];
	foreach( $products as $product ){
		// Need to convert double quotes to &quot; to avoid errors
		// in JavaScript array. eg. ("Tree Guards Brown 39&quot; : 1,) NOT ("Tree Guards Brown 39"" : 1,)
		$product = str_replace('"', '&quot;', $product);
		$tmp[] = "\"$product\": 1,";
	}
	?>
	let existingProducts = {<?php echo implode("", $tmp) ?>};
	// let existingProducts = {
	// 	"prod1" : 1,
	// 	"prod2" : 1,
	// 	"prod3" : 1,
	// };
	
	// Tree Guards Brown 39"
	// Tree Guards Brown 39&quot;
	
	$('[name="product"]').focus();
	
	// Product name cannot contain existing names or underscores
	$('[name="product"]').on('keyup', function(){
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
	
	// Insert 'Existing Suppliers' drop-down selection into 'Primary Supplier' text input
	$(document).on('change', '#supplier', function(){
		$('[name="pri_sup"]').val( input_txt );
	});
</script>