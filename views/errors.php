<?php
/*
INFO:
Compares the prices in the Listings database with a downloaded
list of prices (tab separated). Eg. Amazon

┌───────┬────────────┬───────┬──────────┐
│ sku   │ asin       │ price │ quantity │
├───────┼────────────┼───────┼──────────┤
│ 00000 │ B00IRC79CA │ 23.99 │ 995      │
│ 00001 │ B00IRC7AI8 │ 39.99 │ 1000     │
│ 00002 │ B00IRC7B3M │ 9.99  │ 996      │
│ etc...

When a category is selected a table of comparative prices is displayed:

┌─────────────────────────────┬────────────┬────────┬──────────────┬────────┐
│ Product Name                │ Variation  │ Price  │ Amazon Price │        │
├─────────────────────────────┼────────────┼────────┼──────────────┼────────┤
│ Brown Rock Salt x 15kg tub  │ 15         │ 14.25  │ 14.99        │ [view] │
│ Brown Rock Salt x 15kg tub  │ 15         │ 14.25  │ 14.99        │ [view] │
│ Brown Rock Salt x 20kg tub  │ 20         │ 16.49  │ 18.99        │ [view] │
│ etc...

The 'view' button opens the relevant Listings category
and highlights the individual listing with a red border.


TODO:

Array
(
    [view] => Errors
    [user] => 1

*/
$files_used[] = 'views/errors.php'; //DEBUG

$file = 'amazonInv'; // default
// $sku = '﻿sku';
$sku = '﻿seller-sku';
$price = 'price';
if( isset($_POST['platform']) ){
	if( 'a' == $_POST['platform'] ){
		$file = 'amazonInv';
		// $sku = '﻿sku';
		$sku = '﻿seller-sku';
		$price = 'price';
	}
	elseif( 'e' == $_POST['platform'] ){
		$file = 'ebayInv';
		// $sku = 'CustomLabel';
		// $price = 'StartPrice';
		// Modified 21 Oct 2021 due to eBay changing header names
		$sku = 'Custom label (SKU)';
		$price = 'Start price';
	}
	elseif( 'p' == $_POST['platform'] ){ $file = 'primeInv'; }
}

$target_file = "check_inventory/$file.txt";

$timestamp = filemtime($target_file);
$mod_date = date('Y-m-d H:i', $timestamp);

if( isset($_POST['upload_file']) ){
	// $target_file = "check_inventory/$file.txt";
	move_uploaded_file( $_FILES["fileToUpload"]["tmp_name"], $target_file);
}

$file_plus_ext = "$file.txt";
$file_arr = file('check_inventory/' . $file_plus_ext, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

if( 'amazonInv' == $file ){
	$sku = '﻿seller-sku';
	// $sku = '﻿sku';
	// $sku = '﻿sku';
	$price = 'price';
}
elseif( 'ebayInv' == $file ){
	// Added 21 Oct 2021 due to eBay moving the header names to 2nd row.
	unset($file_arr[0]);
	$file_arr = array_values($file_arr);
	
	// $sku = 'CustomLabel';
	// $price = 'StartPrice';
	
	// Modified 21 Oct 2021 due to eBay changing header names
	$sku = 'Custom label (SKU)';
	$price = 'Start price';
}

$lookup_sku_price = [];
foreach( $file_arr as $i => $row ){
	if( 'amazonInv' == $file ){
		$flds = explode("\t", $row);
	}
	elseif( 'ebayInv' == $file ){
		$flds = explode(',', $row);
		$flds = array_reverse($flds);
	}

	// Get field names and their positions
	if( 0 == $i ){
		$fld_names = [];
		foreach ($flds as $index => $fld) {
			$fld_names[$fld] = $index;
		}

		$missing_fld_names = 'ERROR: Column name(s) missing from downloaded file:';
		if( !isset($fld_names[$sku]) ){
			$missing_fld_names .= ' sku';
		}
		if( !isset($fld_names[$price]) ){
			$missing_fld_names .= ' price';
		}
	}
	elseif( 1 == $i && 'ERROR: Column name(s) missing from downloaded file:' != $missing_fld_names ){
		echo '<pre style="background:#002; color:#fff;">'; print_r($missing_fld_names); echo '</pre>';
	}
	elseif( $i > 0 && 'ERROR: Column name(s) missing from downloaded file:' == $missing_fld_names ){
		if( 'GBP' != $flds[ $fld_names[$price] ] ){
			$lookup_sku_price[ $flds[ $fld_names[$sku] ] ] = $flds[ $fld_names[$price] ];
		}
	}
}

$db_listings = new PDO("sqlite:$listings_db_path");

$post_platform = isset($_POST['platform']) ? $_POST['platform'] : 'a';

// SELECT id, sku, new_price FROM `listings_platform`
$sql = "SELECT id, new_price FROM `listings_{$lookup_platform[$post_platform]}`";
$results = $db_listings->query($sql);
$listings_platform = $results->fetchAll(PDO::FETCH_ASSOC);


$lookup_skus = lookup_skus_fnc([
	'db' => $db_listings,
	'lookup_platform' => $lookup_platform,
	'source' => $post_platform,
]);

foreach( $listings_platform as $i => $rec ){
	$listings_platform[$i]['sku'] = isset($lookup_skus[$post_platform][$i]) ? $lookup_skus[$post_platform][$i] : '';
}

// Get rowid, key, cat_id from listings
$sql = "SELECT id_lkup, key, cat_id, product_name, variation FROM `listings`";
$results = $db_listings->query($sql);
$listings = $results->fetchAll(PDO::FETCH_ASSOC);



$listings_key_rowid = [];
foreach ($listings as $vals) {
	$listings_key_rowid[ $vals['id_lkup'] ] = [
		'key'          => $vals['key'],
		'cat_id'       => $vals['cat_id'],
		'product_name' => $vals['product_name'],
		'variation'    => $vals['variation'],
	];
}

// Get rowid of removed listings
$sql = "SELECT rowid,rowid FROM `listings` WHERE `remove` = 1";
$results = $db_listings->query($sql);
$removed_listings = $results->fetchAll(PDO::FETCH_KEY_PAIR);

// Get keys that makes up a group
$sql = "SELECT key,keys FROM `multi_cpu`";
$results = $db_listings->query($sql);
$lookup_multi_cpu = $results->fetchAll(PDO::FETCH_KEY_PAIR);

$cats = [];
$price_errors = [];
foreach ($listings_platform as $vals) {
	foreach (explode(',', $vals['sku']) as $sku) {
		if( isset($lookup_sku_price[$sku]) ){
			// Ignore removed listings
			if( !isset($removed_listings[$vals['id']]) && $lookup_sku_price[$sku] != $vals['new_price'] ){
				$cat_full = $listings_key_rowid[$vals['id']]['key'];

				$cat = substr($cat_full, 0,3);

				// If group get the keys that it's comprised of
				if( preg_match('/^g[0-9]/', $cat) ){
					list($cat,) = explode(' ', $lookup_multi_cpu[$cat_full]);
					$cat = substr($cat, 0,3);
				}

				if( isset($_POST['cat']) && $cat == $_POST['cat'] ){
					$cat_id = $listings_key_rowid[$vals['id']]['cat_id'];
					$price_errors[$sku] = [
						'price_listings' => $vals['new_price'],
						'price_amazon'   => $lookup_sku_price[$sku],
						'product_name'   => $listings_key_rowid[$vals['id']]['product_name'],
						'variation'      => $listings_key_rowid[$vals['id']]['variation'],
						'cat'            => $cat,
						'cat_orig'       => $cat_full,
						'cat_id'         => $cat_id,
						'id'             => $vals['id'],
						'sku'            => $sku,
					];
				}
				$cats[$cat] = isset($cats[$cat]) ? $cats[$cat] +1 : $cats[$cat] = 1;
			}
		}
	}
}

//DEBUG
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['lookup_prod_cats']); echo '</pre>'; die();
?>

<form method="post" class="fl-l mr20">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	<input type="submit" name="view" value="Dashboard" class="btn">
</form>

<div style="float: left; margin-right: 10px;">
	<!-- Open Modal -->
	<button id="triggerModal" class="btn">Amazon / eBay URLs</button>
</div>

<!-- ///////////////////////////////////////////////////////////////// -->

<div style="float: left;">
	<form method="post">
		<input type="hidden" name="view" value="Errors">
		<input type="hidden" name="user" value="<?= $_POST['user'] ?>">

		<select name="fix_errors_view_cat" class="large_select" onchange="this.form.submit()">
			<option value="">- - SELECT - -</option>
		<?php foreach( $session['lookup_prod_cats'] as $key => $val ){ ?>
			<?php if( '' != $cats[$key] ){ ?>
			<?= sel_opt_fnc($key, "($cats[$key]) " . $session['lookup_cats'][$key], isset($_POST['cat']) ? $_POST['cat'] : '') ?>
			<?php } ?>
		<?php } ?>
		</select>

		<select name="fix_errors_view_platform" class="large_select" onchange="this.form.submit()">
		<?php foreach( [
				'a' => 'amazon',
				'e' => 'ebay'
			] as $key => $val ){ ?>
			<?= sel_opt_fnc($key, ucfirst($val), isset($_POST['platform']) ? $_POST['platform'] : '') ?>
		<?php } ?>
		</select>
	</form>
</div>

<div style="float: left;">
	<form method="post" enctype="multipart/form-data">
		<?php
		$submit_btn_val = 'Upload Amazon File Only';
		$platform = 'a';
		if( isset($_POST['platform']) ){
			if( 'a' == $_POST['platform'] ){$submit_btn_val = 'Upload Amazon File Only'; }
			elseif( 'e' == $_POST['platform'] ){$submit_btn_val = 'Upload eBay File Only'; }
			elseif( 'p' == $_POST['platform'] ){$submit_btn_val = 'Upload Prime File Only'; }

			$platform = $_POST['platform'];
		}		
		?>
		<input type="hidden" name="view" value="Errors">
		<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
		<input type="hidden" name="platform" value="<?= $platform ?>">
		<input type="submit" value="<?= $submit_btn_val ?>" name="upload_file" class="btn">
		<input type="file" name="fileToUpload" id="fileToUpload" required>
		<!-- <span>https://sellercentral.amazon.co.uk/listing/reports?report_reference_id=361838018710</span> -->
	</form>
</div>

<h2><?= 'amazonInv' == $file ? 'Amazon file download time: ' : 'eBay file download time: ' ?> <?= $mod_date ?></h2>

<div class="h30"></div>

<?php
if( isset($_POST['cat']) && '' != $_POST['cat'] ){
	$data = [];
	foreach ($price_errors as $sku => $vals) {
		if( '' != $vals['sku'] ){
			$data[] = [
				$vals['product_name'],
				$vals['variation'],
				$vals['price_listings'],
				$vals['price_amazon'],
				"<form style=\"float:right;\" method=\"post\" target=\"_blank\"><input type=\"submit\" value=\"view\" class=\"btn\" style=\"height: 26px;\"><input type=\"hidden\" name=\"user\" value=\"{$_POST['user']}\"><input type=\"hidden\" name=\"cat_orig\" value=\"{$vals['cat_orig']}\"><input type=\"hidden\" name=\"cat\" value=\"{$_POST['cat']}\"><input type=\"hidden\" name=\"cat_id\" value=\"{$vals['cat_id']}\"><input type=\"hidden\" name=\"platform\" value=\"{$_POST['platform']}\"><input type=\"hidden\" name=\"id\" value=\"{$vals['id']}\"><input type=\"hidden\" name=\"sku\" value=\"{$vals['sku']}\"><input type=\"hidden\" name=\"from_errors\"><input type=\"hidden\" name=\"view\" value=\"Listings\"></form>"
			];
		}
	}

	if( 'amazonInv' == $file ){ $platform_header = 'Amazon Price'; }
	elseif( 'ebayInv' == $file ){ $platform_header = 'eBay Price'; }

	$args_array_to_table = [
		'tbl_class' => 'style-tbl',
		'header' => ['Product Name','Variation','Price',$platform_header,''],
		'body' => $data,
	];
}

if( isset($_POST['cat']) && '' != $_POST['cat'] && count($data) > 0 ){
	echo array_to_table_fnc($args_array_to_table);
}
?>

<!-- ///////////////////////////////////////////////////////////////// -->

<style>
	.modalBg {
		display: none;
		position: fixed;
		z-index: 1;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		overflow: auto;
		background: rgba(0,0,0,0.5);
	}
	.modalBg > div {
		border-radius: 8px;
		padding: 12px;
		background: #fefefe;
		border:1px solid white;
		box-shadow: 4px 4px 10px #222;
		margin: 15% auto;
		width: 300px;
		height: 100px;
		/* Add animation */
		position: relative;
		animation-name: animatetop;
		animation-duration: 0.4s
	}

	/* Add Animation */
	@keyframes animatetop {
		from {top: -300px; opacity: 0}
		to {top: 0; opacity: 1}
	}
</style>

<!-- Modal Page Gray -->
<div id="myModal" class="modalBg">
	<!-- Modal Box -->
	<div>
		<a href="https://sellercentral.amazon.co.uk/listing/reports?report_reference_id=361838018710" target="_blank">Amazon URL</a>
		<br><br>
		<a href="https://k2b-bulk.ebay.co.uk/ws/eBayISAPI.dll?SMDownloadPickup" target="_blank">eBay URL</a>
	</div>
</div>

<script>
	$(function() {
		$('#triggerModal').click(function(){
			$('#myModal').css({'display': 'block'});
		});

		// Close modalBox when escape key is pressed
		$(document).keyup(function(e){
			if (e.keyCode === 27) { // esc
				$('.modalBg').css({'display': 'none'});
			}
		});

		// Close modalBox when background is clicked
		$(document).click(function(e){
			// $('#myModal')[0] equals document.getElementById("myModal")
			if( $('#myModal')[0] == e.target ){
				$('#myModal').css({'display': 'none'});
			}
		});
	});
</script>