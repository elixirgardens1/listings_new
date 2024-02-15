<?php $disable_display = 'Edit' == $view ? FALSE : TRUE; ?>

<?php
// Order category menu
asort($session['lookup_cats']);
$lookup_prod_cats = [];
foreach ($session['lookup_cats'] as $key => $_) {
	$lookup_prod_cats[$key] = $session['lookup_prod_cats'][$key];
}
?>

<header>
	<div style="position: absolute; left: 10px; top: 2px; z-index: 20;">
		<form method="post" style="float: left;">
			<select name="cat" class="large_select" onchange="this.form.submit()"<?= !$disable_display ? ' disabled="disabled"' : '' ?>>
			<?php foreach( $lookup_prod_cats as $key => $val ): ?>
				<?= sel_opt_fnc($key, $session['lookup_cats'][$key], isset($_POST['cat']) ? $_POST['cat'] : '') ?>
			<?php endforeach; ?>
			</select>

			<select name="cat_id" class="large_select" onchange="this.form.submit()"<?= !$disable_display ? ' disabled="disabled"' : '' ?>>
			<?php foreach( $lookup_prod_cats[ $cat ] as $key => $val ): ?>
				<?= sel_opt_fnc($key, $val, isset($_POST['cat_id']) ? $_POST['cat_id'] : '') ?>
			<?php endforeach; ?>
			</select>

			<select name="platform" class="large_select" onchange="this.form.submit()"<?= !$disable_display ? ' disabled="disabled"' : '' ?>>
			<?php foreach( $lookup_platform as $key => $val ): ?>
				<?= sel_opt_fnc($key, ucfirst($val), isset($_POST['platform']) ? $_POST['platform'] : '') ?>
			<?php endforeach; ?>
			</select>

			<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
			<input type="hidden" name="view" value="Listings">
		</form>

		<form method="post" style="float: left;">
			<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
			<input type="submit" name="view" value="Dashboard" class="btn" style="margin-left: 10px; margin-top: 2px;">
			<!-- NEW_PRICE_MATRIX -->
			<?php if( $disable_display ){ ?>
			<input type="button" id="price_matrix" value="view price matrix" style="height: 34px; cursor: pointer; display: none;" class="btn">
			<?php } ?>
			<input type="button" id="update_matrix_prices" data-id="0" name="update_matrix_prices" value="update prices" style="display: none" class="btn">
			<!-- / NEW_PRICE_MATRIX -->
		</form>
		
		<form method="post" style="float: left;">
			<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
			<!-- Needs hidden values to enable return to current listing -->
			<!-- 
			<input type="hidden" name="posY" value="0">
			<input type="hidden" name="group_edit" value="a">
			<input type="hidden" name="view" value="Edit">
			<input type="hidden" name="cat" value="agg">
			<input type="hidden" name="cat_id" value="a244">
			<input type="hidden" name="key" value="agg11">
			<input type="hidden" name="platform" value="e">
			<input type="hidden" name="user" value="1">
			<input type="hidden" name="cpu" value="0.146">
			<input type="hidden" name="lvw" value="1">
			 -->
			<input type="submit" name="view" value="Changes" class="btn" style="margin-left: 10px; margin-top: 2px;">
		</form>
	</div>

	<div style="float: right; padding-right: 20px;">
		<select name="export_remove_option" class="large_select" id="export_remove_option">
			<?= sel_opt_fnc('e', 'Export', isset($_POST['export_remove']) ? $_POST['export_remove'] : '') ?>
			<?= sel_opt_fnc('r', 'Remove', isset($_POST['export_remove']) ? $_POST['export_remove'] : '') ?>
		</select>

		<?php if( !isset($session['group_edit']) && !isset($_POST['add_to_group']) ): ?>
		<input type="button" name="export_remove" value="submit" class="btn">
		<?php endif; ?>
	</div>

	<div style="height: 30px"></div>
</header>

<!-- Clear Header -->
<div style="height: 40px"></div>

<!-- Display 'view price matrix button' on header bar -->
<?php
$no_price_matrix = FALSE;
if( 'Listings' == $view && isset($_POST['cat_id']) ){
	foreach( $lookup_dash_view_profit as $rec ){
		if( $lookup_platform[$_POST['platform']] == $rec['source'] && $rec['no_price_matrix'] ){
			$no_price_matrix = TRUE;
			break;
		}
	}
}
// if( 'Listings' == $view && isset($_POST['cat_id']) ){
if( !$no_price_matrix && 'Listings' == $view && isset($_POST['cat_id']) ){
?>
<script>$('#price_matrix').show();</script>
<?php } ?>