<?php
$files_used[] = 'views/listings_add_prime.php'; //DEBUG

require_once 'format_listing_for_view.php';

// Check which Prime listings have already been assigned couriers
// $sql = "SELECT listings_prime.id, listings_prime.courier FROM `listings_prime`
// INNER JOIN `listings` ON listings_prime.id = listings.id_lkup
// WHERE listings.key = '{$_POST['key']}' AND listings.cat_id = '{$_POST['cat_id']}' AND listings.group_ = '{$_POST['group_edit']}'";

$sql = "SELECT prime_couriers.id, prime_couriers.courier FROM `prime_couriers`
INNER JOIN `listings` ON prime_couriers.id = listings.id_lkup
WHERE listings.key = '{$_POST['key']}' AND listings.cat_id = '{$_POST['cat_id']}' AND listings.group_ = '{$_POST['group_edit']}'";



$results = $db_listings->query($sql);
$courier_lkup = $results->fetchAll(PDO::FETCH_KEY_PAIR);

$existing = [];
foreach( $courier_lkup as $key => $val ){
	$existing[] = "$key=>$val";
}
$existing_str = implode(',', $existing);

$inc = 0;
foreach( $session['listings'] as $id_lkup => $rec ){ ?>
	<tr data-id_lkup='<?= $id_lkup ?>'>
		<td><?= $rec['product_name'] ?></td>
		<td><?= $rec['packaging_band'] ?></td>
		<td>
			<select style="width:160px" class="courier sel-sheet" name="courier[<?= $id_lkup ?>]">
			<?php
			foreach( $session['lookup_prime_couriers'] as $key => $v ){
				$sel = '';
				if( isset($courier_lkup[$id_lkup]) ){
					$sel = $key == $session['lookup_courier_names'][ $courier_lkup[$id_lkup] ] ? ' selected' : '';
				}
			?>
				<!-- $lookup_couriers_name_id // convert courier description to courier number -->
				<option value="<?= '' != $key ? $lookup_couriers_name_id[$key] : '' ?>"<?= $sel ?>><?= add_pound_sign_fnc($key) ?></option>
			<?php } ?>
			</select>
		</td>
		<td><?= $rec['cost_per_unit'] ?></td>
		<td><?= $rec['lowest_variation_weight'] ?></td>
		<td><?= $rec['variation'] ?></td>
		<td style="width:110px;">
			<div class="fl"><?= $rec['lowest_variation_weight'] * $rec['variation'] + $session['lookup_postage_bands'][ $rec['packaging_band'] ]['max_weight'] ?></div>
			<?php if( isset( $courier_lkup[$id_lkup] ) ){ ?>
			<div class="fr"><input type="button" name="del_prime_rec" data-id="<?= $inc++ ?>" value="delete" class="btn" style="height: 28px;"></div>
		<?php } ?>
		</td>
	</tr>
<?php } ?>
</table>
	<input type="hidden" name="add_prime_listing_to_db">
	
	<input type="hidden" name="existing" value="<?= $existing_str ?>">
	
	<input type="hidden" name="posY" value="<?= $_POST['posY'] ?>">
	<input type="hidden" name="group_edit" value="<?= $_POST['group_edit'] ?>">
	<input type="hidden" name="view" value="<?= $_POST['view'] ?>">
	<input type="hidden" name="key" value="<?= $_POST['key'] ?>">
	
	<input type="hidden" name="cat" value="<?= $_POST['cat'] ?>">
	<input type="hidden" name="cat_id" value="<?= $_POST['cat_id'] ?>">
	<input type="hidden" name="platform" value="p">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
	
	<input type="submit" id="btn_sbmt" name="submit" value="Add Prime Courier(s)" class="btn w200 mb10">
</form>

<?php
$inc = 0;
foreach( $session['listings'] as $id_lkup => $na ){
	if( isset( $courier_lkup[$id_lkup] ) ){?>
<form id="form_del_prime_rec_<?= $inc++ ?>" method="post">
	<input type="hidden" name="delete_prime_from_db">
	
	<input type="hidden" name="courier" value="<?= $id_lkup ?>">
	<input type="hidden" name="courier_lkup" value="<?= $courier_lkup[$id_lkup] ?>">
	
	<input type="hidden" name="group_edit" value="<?= $_POST['group_edit'] ?>">
	<input type="hidden" name="view" value="<?= $_POST['view'] ?>">
	<input type="hidden" name="key" value="<?= $_POST['key'] ?>">
	<input type="hidden" name="cat" value="<?= $_POST['cat'] ?>">
	<input type="hidden" name="cat_id" value="<?= $_POST['cat_id'] ?>">
	<input type="hidden" name="platform" value="p">
	<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
</form>
<?php }} ?>

<?php require_once 'back_to_cat.php'; ?>

<script>
	$('input[name="del_prime_rec"]').on('click', function() {
		var id = $(this).attr('data-id');
		var form = document.getElementById("form_del_prime_rec_" + id);
		form.submit();
	});
</script>

<!-- 
 [
    [posY] => 456
    [group_edit] => a
    [view] => Add Prime
    [cat] => agg
    [cat_id] => a8
    [key] => agg0
    [platform] => e
    [user] => 6
]

 -->