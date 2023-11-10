<?php
$files_used[] = 'views/price_matrix.php'; //DEBUG

// INFO:
//=========================================================================
// The 'view price matrix' button only appears in 'Listings' view
// (not 'Edit', 'Add' or 'Add Prime').
// The price matrix appears when clicked. This also results in the
// 'update prices' button appearing next to the 'view price matrix' button.

// ┌───────────────────────────┬──────┬──────┬───────┬───────┬───────┐
// │                           │  1   │  5   │  10   │  15   │  20   │
// ├───────────────────────────┼──────┼──────┼───────┼───────┼───────┤
// │ Brown Rock Salt x 1kg bag │ 5.49 │ 7.99 │  9.99 │ 10.99 │ 12.75 │
// │ Brown Rock Salt x 1kg tub │ 6.29 │ 8.99 │ 10.49 │ 13.99 │ 15.99 │
// │ White Rock Salt x 1kg Bag │ 5.99 │ 7.89 │  8.99 │ 10.89 │ 12.69 │
// │ White Rock Salt x 1kg tub │ 6.99 │ 9.39 │ 10.99 │ 14.49 │ 16.69 │
// └───────────────────────────┴──────┴──────┴───────┴───────┴───────┘

// If 1 & 20 are entered for R1, the prices in between 5.49 (column 1)
// and 12.75 (column 20) are calculated automatically when the
// 'Calculate Prices' button is clicked.
// The prices get rounded up to the nearest 9p, as can be seen in the
// JavaScript code at the bottom of this page.
// Nb. The database prices only get updated when the 'update prices'
//     button is clicked in the header bar.
//     If only the first 'Affected rows' checkbox is checked only the
//     prices for the first row will be calculated.

// ┌───────────────────────────┬──────┬──────┬───────┬───────┬───────┐
// │                           │  1   │  5   │  10   │  15   │  20   │
// ├───────────────────────────┼──────┼──────┼───────┼───────┼───────┤
// │ Brown Rock Salt x 1kg bag │ 5.49 │ 7.39 │  9.19 │ 10.99 │ 12.75 │


$matrix = [];
$col_ids = [];
$title = $session['lookup_prod_cats'][ $cat ][$_POST['cat_id']];
$matrix[] = "<table id='price_matrix_tbl' style='border-collapse: collapse'>";
$matrix_original_prices = [];

foreach( $new_price_matrix as $i => $vals ){
	$row_colour = '';
	if( 0 == $i ){ $row_colour = '#ccc;'; }
	elseif( 0 == $i % 2 ){ $row_colour = '#e4eefa;'; }
	
	$matrix[] = "<tr style='background:".$row_colour."'>";
	foreach ($vals as $j => $val) {
		if( $i > 0 && $j > 0 && 'N/A' != $val ){
			$width = 4 == strlen($val['price']) ? '38px' : '44px';
			$weight = $new_price_matrix[0][$j];
			
			$packing = 't' == $val['packing'] ? '_t' : '';

			$input = "<input type='text' data-id='{$i}_$weight$packing' data-weight='$weight' style='width:$width; background:".$row_colour.";' name='{$val['id']}' class='matrix_prices' value='{$val['price']}' autocomplete='off'>";
			$matrix[] = "<td style='border: 1px solid #999; padding:0px;'>$input</td>";
			$col_ids[$j][] = $val['id'];

			$matrix_original_prices[] = $val['id'].':'.$val['price'];
		}
		// variations row
		else{
			$matrix[] = "<td style='border: 1px solid #999; padding:0px;'>$val</td>";
		}
	}
	$matrix[] = "</tr>";
}
$matrix[] = "</table>";
?>

<style>
	.modal-price-matrix{
		display: none;
		position: fixed;
		z-index: 1;
		left: 0;
		top: 0;
		width: 100%;
		height: 100%;
		overflow: auto;
		background-color: rgba(0,0,0,0.2);
	}
	.modal-price-matrix-content{
		position: fixed;
		bottom: 0;
		background-color: #fefefe;
		padding: 10px;
		border: 1px solid #888;
		width: 98%;
	}
</style>

<div id="modal-price-matrix" class="modal-price-matrix">
	<div class="modal-price-matrix-content">
		<form name="matrix_prices_form" method="post">
			<input type="hidden" name="user" value="<?= $_POST['user'] ?>">
			<input type="hidden" name="update_matrix_prices" value="1">
			<input type="hidden" name="cat" value="<?= $_POST['cat'] ?>">
			<input type="hidden" name="cat_id" value="<?= $_POST['cat_id'] ?>">
			<input type="hidden" name="platform" value="<?= $_POST['platform'] ?>">
			<input type="hidden" name="orig_prices" value="<?= implode(',', $matrix_original_prices) ?>">

			<input type="button" id="calc_matrix_btn" class="btn" style="float: left;" name="calc_matrix_btn" value="Calculate Prices">

			<div style="margin-left: 160px;">
				<table id="set_range" style="border-collapse: collapse; float: left;">
					<tr>
						<td>R1:</td>
						<td><input type="text" style="width:38px; background: #fff;" id="rs1">&ndash;<input type="text" style="width:38px; background: #fff;" id="re1"></td>
						<td>&nbsp;</td>
						<td>R2:</td>
						<td><input type="text" style="width:38px; background: #fff;" id="rs2">&ndash;<input type="text" style="width:38px; background: #fff;" id="re2"></td>
						<td>&nbsp;</td>
						<td>R3:</td>
						<td><input type="text" style="width:38px; background: #fff;" id="rs3">&ndash;<input type="text" style="width:38px; background: #fff;" id="re3"></td>
						<td>&nbsp;</td>
						<td>R4:</td>
						<td><input type="text" style="width:38px; background: #fff;" id="rs4">&ndash;<input type="text" style="width:38px; background: #fff;" id="re4"></td>
					</tr>
				</table>

				<table id="affected_rows" style="border-collapse: collapse; float: left;">
					<tr>
						<td>&nbsp;</td>
						<td>Affected rows:</td>
						<?php for( $i=0; $i < count($new_price_matrix)-1; $i++ ){ ?>
							<td><input id='cbx_ar<?= $i ?>' type='checkbox' checked="checked"><label class='cbx' for='cbx_ar<?= $i ?>'></label></td>
						<?php } ?>
					</tr>
				</table>

				<span style="float: right;">Esc to close</span>
			</div>

			<div style="clear:both"></div>

			<?= implode('', $matrix) ?>
		</form>
	</div>
</div>

<script>
	var price_matrix = [
	<?php
		// Create JScript matrix
		$ids_2d = [];
		foreach( $new_price_matrix as $i => $vals ){
			if( $i > 0 ){
				$ids = [];
				foreach( $vals as  $j =>  $val ){
					if( $j > 0 ){
						$ids[] = isset($val['id']) ? $val['id'] : "''";
					}
				}
				$ids_2d[] = implode(',', $ids);
			}
		}
		$cbx_ar_total = count($ids_2d);
		$ids_2d_str = implode("],\n[", $ids_2d);
		echo "[$ids_2d_str]";
	?>
	];

	$(function(){
		$('#calc_matrix_btn').on('click', function(){
			var col_array = [];
			<?php
			array_shift($new_price_matrix[0]);
			foreach( $new_price_matrix[0] as $i => $v ){ ?>col_array[<?= $v ?>]=<?= $i ?>;<?php } ?>
		
			var cbx_ar_total = <?= $cbx_ar_total ?>;

			var range_total = 1;
			if( '' != $('#rs'+2).val() && '' != $('#re'+2).val() && col_array[$('#rs'+2).val()] && col_array[$('#re'+2).val()] ){
				range_total = 2;

				if( '' != $('#rs'+3).val() && '' != $('#re'+3).val() && col_array[$('#rs'+3).val()] && col_array[$('#re'+3).val()] ){
					range_total = 3;

					if( '' != $('#rs'+4).val() && '' != $('#re'+4).val() && col_array[$('#rs'+4).val()] && col_array[$('#re'+4).val()] ){
						range_total = 4;
					}
				}
			}

			var range_sets_s = [];
			var range_sets_e = [];
			var total_cells_in_sets = [];
			<?php for( $i = 0; $i < $cbx_ar_total; $i++ ){ ?>range_sets_s[<?= $i ?>]=[];range_sets_e[<?= $i ?>]=[];total_cells_in_sets[<?= $i ?>]=[];<?php } ?>

			var rs_val = '';
			var re_val = '';

			for( var i=0; i<cbx_ar_total; i++ ){
				for( var j=0; j<range_total; j++ ){
					rs_val = $('#rs'+(j+1)).val();
					re_val = $('#re'+(j+1)).val();

					range_sets_s[i][j] = $('input[name="'+ price_matrix[i][col_array[ rs_val ] ] +'"]').val();
					range_sets_e[i][j] = $('input[name="'+ price_matrix[i][col_array[ re_val ] ] +'"]').val();
					total_cells_in_sets[i][j] = col_array[ re_val ] - col_array[ rs_val ];
				}
			}

			for( var i=0; i<cbx_ar_total; i++ ){
				if( $('#cbx_ar'+i).is(":checked") ){
					for( var j=0; j<range_total; j++ ){
						var cell_inc = (parseFloat(range_sets_e[i][j]) - parseFloat(range_sets_s[i][j]) ) / parseInt(total_cells_in_sets[i][j]);

						// Skip if left or right range is not a text field number (eg. N/A)
						if( cell_inc ){
							var prev_cell = 0;
							var prev_cell = parseFloat(range_sets_s[i][j]);
							for (var k=0; k<total_cells_in_sets[0][j]-1; k++) {
								offset = col_array[ $('#rs'+(j+1)).val() ] +1;

								var form_fld_name = price_matrix[i][k + offset];
								var form_fld_val = (prev_cell + cell_inc).toFixed(2);

								// Round up to 9p
								form_fld_val = form_fld_val.slice(0,-1) + '9';
								
								// Update form flds
								$('input[name="'+form_fld_name+'"]').val( form_fld_val );

								prev_cell = prev_cell + cell_inc;
							}
						}
					}
				}
			}
		});

		// Display Price Matrix modal when 'view price matrix' button clicked
		$('#price_matrix').click(function(){
			$('.modal-price-matrix').show(100);
			$('#update_matrix_prices').show();
		});

		// When the user clicks anywhere outside of the Price Matrix modal, close it
		var modal = document.getElementById("modal-price-matrix");
		var update_btn = document.getElementById("update_matrix_prices");
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
				update_btn.style.display = "none";
			}
		}

		$(document).keyup(function(e){
			if (e.keyCode === 27) { // esc
				modal.style.display = "none";
				update_btn.style.display = "none";
			}
		});
	});
</script>