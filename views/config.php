<?php

$sql = "SELECT * FROM `config_fees`";
$results = $db_listings->query($sql);
$config_fees = $results->fetchAll(PDO::FETCH_ASSOC);

$projection_20perc = [];
$orig_projection_20perc = [];
$platform_fees = [];
$orig_platform_fees = [];
foreach( $config_fees as $rec ){
	if( 'projection_20perc' == $rec['type'] ){
		$projection_20perc[] = [
			'id' => $rec['id'],
			'value' => $rec['value'],
		];
		$orig_projection_20perc[$rec['id'] ] = $rec['value'];
	}
	elseif( 'platform_fees' == $rec['type'] ){
		$platform_fees[] = [
			'id' => $rec['id'],
			'value' => $rec['value'],
		];
		$orig_platform_fees[$rec['id'] ] = $rec['value'];
	}
}


$sql = "SELECT rowid,* FROM `lookup_couriers_plus_fuel`";
// $sql = "SELECT rowid,* FROM `lookup_couriers`";
$results = $db_listings->query($sql);
$lookup_couriers = $results->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT rowid,* FROM `lookup_postage_bands`";
$results = $db_listings->query($sql);
$lookup_postage_bands = $results->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT rowid,* FROM `lookup_prod_cats`";
$results = $db_listings->query($sql);
$lookup_prod_cats = $results->fetchAll(PDO::FETCH_ASSOC);


//=========================================================================
// This will need changing to rooms_lookup@stock_control.db3 when Ryan
// has updated format.
//=========================================================================
$sql = "SELECT * FROM `rooms_lookup`";
$results = $db_listings->query($sql);
$rooms_lookup = $results->fetchAll(PDO::FETCH_KEY_PAIR);
$orig_rooms_lookup = $rooms_lookup;


$orig_vals_json = json_encode([
	'projection_20perc' => $orig_projection_20perc,
	'platform_fees' => $orig_platform_fees,
	'rooms_lookup' => $orig_rooms_lookup,
]);
$orig_vals_json = str_replace('"', 'QUOT', $orig_vals_json);
?>
<form method="post" class="fl-l mr30">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	<input type="submit" name="view" value="Dashboard" class="btn">
</form>

<div style="font-size: 30px;">Config</div>

<div class="cl-l mb20"></div>

<form method="post">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	<input type="hidden" name="update_config">
	<input type="hidden" name="modify_config_db">
	<input type="hidden" name="orig_vals" value="<?= $orig_vals_json ?>">
	
	<!-- Product Cats -->
	<div class="fl-l mr10">
		<span class="fs-tbl-ttl">Product Cats Lookup</span>
		<table class="style-tbl">
			<tr>
				<th class="w40">cat</th>
				<th class="w40">cat_id</th>
				<th class="w180">product_cat</th>
			</tr>
			<?php foreach( $lookup_prod_cats as $rec ){ ?>
			<tr>
				<td>
					<input type="text" class="product_name txtfld def" name="prod_cat[<?= $rec['rowid'] ?>]" value="<?= $rec['cat'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
				<td>
					<input type="text" class="product_name txtfld def" name="prod_cat[<?= $rec['rowid'] ?>]" value="<?= $rec['cat_id'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
				<td>
					<input type="text" class="product_name txtfld def" name="prod_cat[<?= $rec['rowid'] ?>]" value="<?= $rec['product_cat'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
			</tr>
			<?php } ?>
		</table>
	</div>
	
	<!-- Couriers -->
	<div class="fl-l mr10">
		<span class="fs-tbl-ttl">Courier Lookup</span>
		<table class="style-tbl">
			<tr>
				<th class="w180">name</th>
				<th class="w60">courier</th>
				<th class="w50">cost</th>
				<th class="w50">weight</th>
			</tr>
			<?php foreach( $lookup_couriers as $rec ){ ?>
			<tr>
				<td>
					<input type="text" class="product_name txtfld def" name="courier_name[<?= $rec['rowid'] ?>]" value="<?= $rec['name'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
				<td>
					<input type="text" class="product_name txtfld def" name="courier_courier[<?= $rec['rowid'] ?>]" value="<?= $rec['courier'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
				<td>
					<input type="text" class="product_name txtfld def" name="courier_cost[<?= $rec['rowid'] ?>]" value="<?= $rec['cost'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
				<td>
					<input type="text" class="product_name txtfld def" name="courier_weight[<?= $rec['rowid'] ?>]" value="<?= $rec['weight'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
			</tr>
			<?php } ?>
		</table>
	</div>
	
	<!-- Postage Bands -->
	<div class="fl-l mr10">
		<span class="fs-tbl-ttl">Postage Bands Lookup</span>
		<table class="style-tbl">
			<tr>
				<th class="w40">band</th>
				<th class="w40">cost</th>
				<th class="w50">max_weight</th>
				<th class="w180">example_packaging</th>
			</tr>
			<?php foreach( $lookup_postage_bands as $rec ){ ?>
			<tr>
				<td>
					<input type="text" class="product_name txtfld def" name="postage[<?= $rec['rowid'] ?>]" value="<?= $rec['band'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
				<td>
					<input type="text" class="product_name txtfld def" name="postage[<?= $rec['rowid'] ?>]" value="<?= $rec['cost'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
				<td>
					<input type="text" class="product_name txtfld def" name="postage[<?= $rec['rowid'] ?>]" value="<?= $rec['max_weight'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
				<td>
					<input type="text" class="product_name txtfld def" name="postage[<?= $rec['rowid'] ?>]" value="<?= $rec['example_packaging'] ?>" data-lpignore="true" autocomplete="off" required="">
				</td>
			</tr>
			<?php } ?>
		</table>
		
		<!-- Rooms -->
		<div class="fl-l mr10">
			<span class="fs-tbl-ttl">Rooms</span>
			<table class="style-tbl">
				<tr>
					<th>ID</th>
					<th class="w60">Room</th>
				</tr>
				<?php foreach( $rooms_lookup as $id => $room ){ ?>
				<tr>
					<td><?= $id ?></td>
					<td>
						<input type="text" class="product_name txtfld def" name="platform_fee[<?= $id ?>]" value="<?= $room ?>" data-lpignore="true" autocomplete="off" required="">
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td><?= ($id+1) ?></td>
					<td>
						<input type="text" class="product_name txtfld def" name="platform_fee[<?= ($id+1) ?>]" value="" placeholder="add new" data-lpignore="true" autocomplete="off">
					</td>
				</tr>
			</table>
		</div>
		
		<div class="fl-l mr10">
			<!-- 20% Projections -->
			<span class="fs-tbl-ttl">20% Projection</span>
			<table class="style-tbl">
				<tr>
					<th>Band</th>
					<th class="w100">20% Projection</th>
				</tr>
				<?php foreach( $projection_20perc as $i => $rec ){ ?>
				<tr>
					<td><?= $rec['id'] ?></td>
					<td>
						<input type="text" class="product_name txtfld def" name="projection_20perc[<?= $rec['id'] ?>]" value="<?= $rec['value'] ?>" data-lpignore="true" autocomplete="off" required="">
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td><?= ($i+2) ?></td>
					<td>
						<input type="text" class="product_name txtfld def" name="projection_20perc[<?= ($i+2) ?>]" value="" placeholder="add new" data-lpignore="true" autocomplete="off">
					</td>
				</tr>
			</table>
			
			<!-- Platform Fees -->
			<span class="fs-tbl-ttl">Platform Fee</span>
			<table class="style-tbl">
				<tr>
					<th>Band</th>
					<th class="w80">Platform Fee</th>
				</tr>
				<?php foreach( $platform_fees as $i => $rec ){ ?>
				<tr>
					<td><?= $rec['id'] ?></td>
					<td>
						<input type="text" class="product_name txtfld def" name="platform_fee[<?= $rec['id'] ?>]" value="<?= $rec['value'] ?>" data-lpignore="true" autocomplete="off" required="">
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td><?= ($i+2) ?></td>
					<td>
						<input type="text" class="product_name txtfld def" name="platform_fee[<?= ($i+2) ?>]" value="" placeholder="add new" data-lpignore="true" autocomplete="off">
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	<input type="submit" name="submit_update_config" class="btn w140" value="Update">
</form>

<style>
	.style-tbl th{
		color: black;
	}
</style>