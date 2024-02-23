<?php
$files_used[] = 'js/js_form_fld_calculations.php'; //DEBUG

// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['listings']); echo '</pre>'; die();
// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($session['pp1_perc']); echo '</pre>'; die();

?>
<script>
// *** js_form_fld_calculations ***
var rowids = [];
<?php foreach ($session['listings'] as $rowid => $v): ?>
	rowids.push(<?= $rowid ?>);
<?php endforeach; ?>

var lookup_postage_bands = {
<?php
$i=0;
$arr_size = count($session['lookup_postage_bands']);
foreach( $session['lookup_postage_bands'] as $band => $rec ): ?>
"<?= $band ?>" : {
	"cost" : <?= $rec['cost'] ?>,
	"max_weight" : <?= $rec['max_weight'] ?>,
	"example_packaging" : "<?= $rec['example_packaging'] ?>"
}<?= $i++ < $arr_size-1 ? ',' : ''; ?>
<?php endforeach; ?>
}

var lookup_couriers = {
<?php
$i=0;
$arr_size = count($session['lookup_couriers']);
foreach( $session['lookup_couriers'] as $name => $rec ): ?>
"<?= $name ?>" : {
	"courier" : "<?= $rec['courier'] ?>",
	"cost" : "<?= $rec['cost'] ?>",
	"weight" : "<?= $rec['weight'] ?>"
}<?= $i++ < $arr_size-1 ? ',' : ''; ?>
<?php endforeach; ?>
}

<?php
$sql = "SELECT * FROM `products`";
$results = $db_stock->query($sql);
$lookup_key_cost = $results->fetchAll(PDO::FETCH_ASSOC);

// $args = [ 'table' => 'products' ];
// $lookup_key_cost = $crud_stock->read($args);
?>

var lookup_key_cost = {
<?php
$i=0;
$arr_size = count($lookup_key_cost);
foreach( $lookup_key_cost as $rec ): ?>
"<?= $rec['key'] ?>":"<?= $rec['product_cost'] ?>"<?= $i++ < $arr_size-1 ? ',' : ''; ?>
<?php endforeach; ?>
}


var vat_rate = {<?php
$i=0;
$arr_size = count($session['vat_rate']);
foreach( $session['vat_rate'] as $cat_id => $vat_rate ): ?>
"<?= $cat_id ?>":"<?= $vat_rate ?>"<?= $i++ < $arr_size-1 ? ',' : ''; ?>
<?php endforeach; ?>
};


$(function() {
	var rowid;
	var element_class_name;
	var i;
	var total;
	var total_product_cost;
	var postage;
	var new_price;
	var lowest_variation_weight;
	var variation;
	var cpu_to_cust;
	var vat;
	var fees;
	// var pp1;
	// var pp2;
	// var pp1pp2;
	var perc_advertising;
	var packaging_band;
	var weight;
	var total_weight;
	var profit;
	var profit_perc;
	var profit_10off;
	var profit_10off_perc;

	//=========================================================================
	// Redisplay calculated values when drop-downs change / text fields lose focus
	//=========================================================================
	$('.sel-sheet').on('change', function() {
		rowid = $(this).parent().parent().attr('data-id_lkup');
		
		// console.log('rowid');
		
		element_class_name = $(this).attr("class").split(" ")[0];
		var select_option = $(this).children("option:selected").val();

		// Remove red background from Packaging Band / Courier drop-downs (Add New Listings) when changed.
		$(this).parent().removeClass("red");
		if( '- PLEASE SELECT -' == select_option ){
			$(this).parent().addClass("red");
		}

		// Calc 'total_weight' if Packaging Band drop-down changes:
		// = 'variation' * 'lowest_variation_weight' + 'max_weight' value for this 'packaging_band' [lookup].
		if( 'packaging_band' == element_class_name ){
			lowest_variation_weight = $('tr[data-id_lkup="'+rowid+'"] .lowest_variation_weight').val();
			variation = $('tr[data-id_lkup="'+rowid+'"] .variation').val();

			// var weight = lookup_postage_bands[select_option]['max_weight'];
			packaging_band = $('tr[data-id_lkup="'+rowid+'"] .packaging_band').val();
			weight = lookup_postage_bands[packaging_band]['max_weight'];
			total_weight = lowest_variation_weight * variation + weight;

			$('tr[data-id_lkup="'+rowid+'"] .total_weight').html(total_weight.toFixed(3) );

			fnc_display_pricing_suggestion_20perc(rowid);
		}

		// Calc 'postage' if Courier drop-down or Packaging Band drop-down changes:
		if( 'courier' == element_class_name || 'packaging_band' == element_class_name ){
			postage = fnc_postage(rowid);
			$('tr[data-id_lkup="'+rowid+'"] .postage').html(postage.toFixed(2) );

			fnc_display_pricing_suggestion_20perc(rowid);
		}


		// Calc 'total_weight' if Lowest variation Weight or Variation text fields get updated:
		// = 'variation' * 'lowest_variation_weight' + 'max_weight' value for this 'packaging_band' [lookup].
		if( 'lowest_variation_weight' == element_class_name || 'variation' == element_class_name ){
			total_weight = fnc_total_weight(rowid);
			$('tr[data-id_lkup="'+rowid+'"] .total_weight').html(total_weight.toFixed(3) );

			postage = fnc_postage(rowid);
			$('tr[data-id_lkup="'+rowid+'"] .postage').html(postage.toFixed(2) );

			fnc_display_pricing_suggestion_20perc(rowid);
		}

		// Calc 'total_product_cost' if Cost Per Unit or Variation text fields get updated:
		// = 'cost_per_unit' * 'variation'
		if( 'cost_per_unit' == element_class_name || 'variation' == element_class_name ){
			total_product_cost = fnc_total_product_cost(rowid);
			$('tr[data-id_lkup="'+rowid+'"] .total_product_cost').html(total_product_cost.toFixed(2) );

			fnc_display_pricing_suggestion_20perc(rowid);
		}



		fnc_calc_new_price(rowid,element_class_name);
	});


	$('.txtfld').on('keyup', function(){
		rowid = $(this).parent().parent().attr('data-id_lkup');
		element_class_name = $(this).attr("class").split(" ")[0];

		//DEBUG
		var str = $(this).val();

		if( -1 !== str.indexOf("_") && 'product_name' == element_class_name ){
			// console.log( 'No Underscores' );

			$('input[value="Update"]').attr("disabled", true);
			$('input[value="Update"]').removeClass('btn');
			$('input[value="Update"]').addClass('btn-disabled');

			$('#js_error_msg').html('ERROR! Product Name cannot contain underscores');
		}
		else{
			$('input[value="Update"]').attr("disabled", false);
			$('input[value="Update"]').removeClass('btn-disabled');
			$('input[value="Update"]').addClass('btn');

			$('#js_error_msg').html('');
		}
	});

	$('.txtfld').on('blur', function(){
		rowid = $(this).parent().parent().attr('data-id_lkup');
		element_class_name = $(this).attr("class").split(" ")[0];

		fnc_calc_new_price(rowid,element_class_name);
	});
});



//=========================================================================
// USER FUNCTIONS
//=========================================================================
function fnc_calc_new_price(rowid,element_class_name){
	var pp1;
	var pp2;
	// var pp2 = 0;
	var pp1pp2;
	
	// Calc 'total_weight' if Lowest variation Weight or Variation text fields get updated:
	// = 'variation' * 'lowest_variation_weight' + 'max_weight' value for this 'packaging_band' [lookup].
	if( 'lowest_variation_weight' == element_class_name || 'variation' == element_class_name ){
		total_weight = fnc_total_weight(rowid);
		$('tr[data-id_lkup="'+rowid+'"] .total_weight').html(total_weight.toFixed(3) );

		postage = fnc_postage(rowid);
		$('tr[data-id_lkup="'+rowid+'"] .postage').html(postage.toFixed(2) );

		fnc_display_pricing_suggestion_20perc(rowid);
	}

	// Calc 'total_product_cost' if Cost Per Unit or Variation text fields get updated:
	// = 'cost_per_unit' * 'variation'
	if( 'cost_per_unit' == element_class_name || 'variation' == element_class_name ){
		total_product_cost = fnc_total_product_cost(rowid);
		$('tr[data-id_lkup="'+rowid+'"] .total_product_cost').html(total_product_cost.toFixed(2) );

		fnc_display_pricing_suggestion_20perc(rowid);
	}

	// Calc 'new_price'
	if( 'comp1' == element_class_name ||
		'comp2' == element_class_name ||
		'comp3' == element_class_name ||
		'prev_price' == element_class_name ||
		'new_price' == element_class_name ||
		'cost_per_unit' == element_class_name ||
		'variation' == element_class_name ||
		'courier' == element_class_name ||
		'packaging_band' == element_class_name
	){
		new_price = $('tr[data-id_lkup="'+rowid+'"] .new_price').val();

		if( '' == new_price ){
			new_price = fnc_new_price_suggestion(rowid);
			$('tr[data-id_lkup="'+rowid+'"] .new_price_suggestion').html( new_price.toFixed(2) );
		}

		variation = parseFloat( $('tr[data-id_lkup="'+rowid+'"] .variation').val() );
		cpu_to_cust = new_price / variation;

		$('tr[data-id_lkup="'+rowid+'"] .cpu_to_cust').html( cpu_to_cust.toFixed(4) );

		vat = '0' != vat_rate['<?= $_POST['cat_id'] ?>'] ? new_price - new_price/(1 + vat_rate['<?= $_POST['cat_id'] ?>']/100) : 0;
        
        $('tr[data-id_lkup="'+rowid+'"] .vat').html( vat.toFixed(2) );

		perc_advertising = ($('tr[data-id_lkup="'+rowid+'"] .perc_advertising').val() );

		fees = 0;
		<?php if( 'w' != $platform_post ): ?>
		fees = new_price * <?= $fees_val ?> + new_price * (perc_advertising / 100);
		$('tr[data-id_lkup="'+rowid+'"] .fees').html( fees.toFixed(2) );
		<?php endif; ?>
		
		pp1 = new_price * <?= $session['pp1_perc'] ?> / 100;
		$('tr[data-id_lkup="'+rowid+'"] .pp1').html(pp1.toFixed(2))
		
		if (new_price < 8) {pp2 = 0.27;}
        else if (new_price < 15) {pp2 = 0.57;}
        else if (new_price < 20) {pp2 = 0.87;}
        else if (new_price < 25) {pp2 = 1.17;}
        else if (new_price < 33) {pp2 = 1.47;}
        else if (new_price < 39) {pp2 = 1.77;}
        else if (new_price < 55.3) {pp2 = 2.07;}
        else if (new_price < 59.2) {pp2 = 2.37;}
        else if (new_price < 72.25) {pp2 = 2.67;}
        else if (new_price < 100) {pp2 = 2.97;}
        else {pp2 = 3.27;}
		
		
		total_product_cost = fnc_total_product_cost(rowid);

		postage = fnc_postage(rowid);

		profit = new_price - total_product_cost - postage - vat - fees - pp1 - pp2;
		// profit = new_price - total_product_cost - postage - vat - fees - pp1;
		
		// Deduct 30 pence from profit ebay
		<?php if( 'e' == $platform_post ): ?>
		// profit = profit - 0.3;
		<?php endif; ?>

		$('tr[data-id_lkup="'+rowid+'"] .profit').html(profit.toFixed(2) );

		// Modify "Profit (£)" cell colour (green/red)
		if( profit < 0.8 ){ $('tr[data-id_lkup="'+rowid+'"] .profit').removeClass().addClass('profit red-bg'); }
		else{ $('tr[data-id_lkup="'+rowid+'"] .profit').removeClass().addClass('profit grn'); }

		profit_perc = (profit / new_price * 100);
		$('tr[data-id_lkup="'+rowid+'"] .profit_perc').html(profit_perc.toFixed(2) );

		// Modify "Profit %" cell colour
		switch (true) {
		    case profit_perc < 5:
                $('tr[data-id_lkup="'+rowid+'"] .profit_perc').removeClass().addClass('profit_perc red-bg');
                break;
            case profit_perc < 8:
                $('tr[data-id_lkup="'+rowid+'"] .profit_perc').removeClass().addClass('profit_perc orange');
                break;
            case profit_perc < 10:
                $('tr[data-id_lkup="'+rowid+'"] .profit_perc').removeClass().addClass('profit_perc yellow');
                break;
            case profit_perc < 34:
            // case profit_perc < 16:
                $('tr[data-id_lkup="'+rowid+'"] .profit_perc').removeClass().addClass('profit_perc grn');
                break;
            default:
            	$('tr[data-id_lkup="'+rowid+'"] .profit_perc').removeClass().addClass('profit_perc orange');
            	// $('tr[data-id_lkup="'+rowid+'"] .profit_perc').removeClass().addClass('profit_perc blue');
        }
        
        // if (price_calc < 8) {pp2 = 0.27;}
        // else if (new_price < 15) {pp2 = 0.57;}
        // else if (new_price < 20) {pp2 = 0.87;}
        // else if (new_price < 25) {pp2 = 1.17;}
        // else if (new_price < 33) {pp2 = 1.47;}
        // else if (new_price < 39) {pp2 = 1.77;}
        // else if (new_price < 55.3) {pp2 = 2.07;}
        // else if (new_price < 59.2) {pp2 = 2.37;}
        // else if (new_price < 72.25) {pp2 = 2.67;}
        // else if (new_price < 100) {pp2 = 2.97;}
        // else {pp2 = 3.27;}
        
        pp1pp2 = (pp1 + pp2 + profit) / new_price * 100;
        // pp1pp2 = 100;
        $('tr[data-id_lkup="'+rowid+'"] .pp1pp2').html(pp1pp2.toFixed(2) );
        
        // Modify "pp1pp2 %" cell colour
		switch (true) {
		    case pp1pp2 < 19:
                $('tr[data-id_lkup="'+rowid+'"] .pp1pp2').removeClass().addClass('pp1pp2 red-bg');
                break;
            case pp1pp2 < 21:
                $('tr[data-id_lkup="'+rowid+'"] .pp1pp2').removeClass().addClass('pp1pp2 orange');
                break;
            case pp1pp2 < 23:
                $('tr[data-id_lkup="'+rowid+'"] .pp1pp2').removeClass().addClass('pp1pp2 yellow');
                break;
            case pp1pp2 < 44:
            // case pp1pp2 < 33:
                $('tr[data-id_lkup="'+rowid+'"] .pp1pp2').removeClass().addClass('pp1pp2 grn');
                break;
            default:
            	$('tr[data-id_lkup="'+rowid+'"] .pp1pp2').removeClass().addClass('pp1pp2 orange');
            	// $('tr[data-id_lkup="'+rowid+'"] .pp1pp2').removeClass().addClass('pp1pp2 blue');
        }

        /*
        // Modify "Total Product Cost (£)" cell colour (green/red)
        switch (true) {
        	case total_product_cost / new_price < 0.15:
        		$('tr[data-id_lkup="'+rowid+'"] .total_product_cost').removeClass().addClass('total_product_cost blue');
        		break;
        	case total_product_cost / new_price < 0.20:
        		$('tr[data-id_lkup="'+rowid+'"] .total_product_cost').removeClass().addClass('total_product_cost grn');
        		break;
        	case total_product_cost / new_price < 0.30:
        		$('tr[data-id_lkup="'+rowid+'"] .total_product_cost').removeClass().addClass('total_product_cost orange');
        		break;
        	case total_product_cost / new_price > 0.29:
        		$('tr[data-id_lkup="'+rowid+'"] .total_product_cost').removeClass().addClass('total_product_cost red-bg');
        		break;
        }
        
        // Modify "Postage (£)" cell colour (green/red)
        switch (true) {
        	case postage / new_price < 0.15:
        		$('tr[data-id_lkup="'+rowid+'"] .postage').removeClass().addClass('postage blue');
        		break;
        	case postage / new_price < 0.20:
        		$('tr[data-id_lkup="'+rowid+'"] .postage').removeClass().addClass('postage grn');
        		break;
        	case postage / new_price < 0.30:
        		$('tr[data-id_lkup="'+rowid+'"] .postage').removeClass().addClass('postage orange');
        		break;
        	case postage / new_price < 0.29:
        		$('tr[data-id_lkup="'+rowid+'"] .postage').removeClass().addClass('postage red-bg');
        		break;
        }
        */



		// 10% Off Profit (£) / 10% Off Profit Perc Bob 07/02/2024
		<?php if( 'w' == $platform_post ): ?>
			profit_10off = new_price * 0.9 - new_price * 0.9 / 6 - total_product_cost - postage - new_price * 0.9 * 0.06 - pp2;
			$('tr[data-id_lkup="'+rowid+'"] .profit_10off').html(profit_10off.toFixed(2) );

			profit_10off_perc = profit_10off / (new_price * 0.9) * 100;
			$('tr[data-id_lkup="'+rowid+'"] .profit_10off_perc').html(profit_10off_perc.toFixed(2) );

			// modify cell colour (green/red)
			if( profit_10off < 1 ){ $('tr[data-id_lkup="'+rowid+'"] .profit_10off').removeClass().addClass('profit_10off red'); }
			// if( profit_10off < 1.25 ){ $('tr[data-id_lkup="'+rowid+'"] .profit_10off').removeClass().addClass('profit_10off red'); }
			else{ $('tr[data-id_lkup="'+rowid+'"] .profit_10off').removeClass().addClass('profit_10off grn'); }

			// modify cell colour (green/red)
			// if( profit_10off_perc < 20 ){ $('tr[data-id_lkup="'+rowid+'"] .profit_10off_perc').removeClass().addClass('profit_10off_perc red'); }
			// else{ $('tr[data-id_lkup="'+rowid+'"] .profit_10off_perc').removeClass().addClass('profit_10off_perc grn'); }

        switch (true) {
            case profit_10off_perc < 15:
                $('tr[data-id_lkup="'+rowid+'"] .profit_10off_perc').removeClass().addClass('profit_10off_perc red');
                break;

            case profit_10off_perc < 20:
                $('tr[data-id_lkup="'+rowid+'"] .profit_10off_perc').removeClass().addClass('profit_10off_perc orange');
                break;

            case profit_10off_perc < 30:
                $('tr[data-id_lkup="'+rowid+'"] .profit_10off_perc').removeClass().addClass('profit_10off_perc grn');
                break;

            case profit_10off_perc > 30:
                $('tr[data-id_lkup="'+rowid+'"] .profit_10off_perc').removeClass().addClass('profit_10off_perc blue');
                break;
        }
		<?php endif; ?>
	}
}



function fnc_display_pricing_suggestion_20perc(rowid){
	var pricing_suggestion_20perc = fnc_pricing_suggestion_20perc(rowid);
	$('tr[data-id_lkup="'+rowid+'"] .pricing_suggestion_20perc').html(pricing_suggestion_20perc.toFixed(2) );
}

function fnc_new_price_suggestion(rowid){
	var comp1 = $('tr[data-id_lkup="'+rowid+'"] .comp1').val();
	var comp2 = $('tr[data-id_lkup="'+rowid+'"] .comp2').val();
	var comp3 = $('tr[data-id_lkup="'+rowid+'"] .comp3').val();
	var prev_price = $('tr[data-id_lkup="'+rowid+'"] .prev_price').val();

	// Get Previous Price
	if( '' == comp1 && '' == comp2 && '' == comp3 ){
		new_price = prev_price;
	}
	// get lowest comp price
	else{
		var comp_arr = [ '' == comp1 ? 0: parseFloat(comp1),'' == comp2 ? 0: parseFloat(comp2),'' == comp3 ? 0: parseFloat(comp3)].sort();
		if( 0 !== comp_arr[0] ){ new_price = comp_arr[0]; }
		else if( 0 !== comp_arr[1] ){ new_price = comp_arr[1]; }
		else{ new_price = comp_arr[2]; }
	}

	// get pricing_suggestion_20perc
	if( '' == new_price ){ new_price = Math.ceil( fnc_pricing_suggestion_20perc(rowid) ) - 0.01; }

	new_price = parseFloat(new_price);

	return new_price;
}

function fnc_pricing_suggestion_20perc(rowid){
	var total_product_cost = parseFloat( fnc_total_product_cost(rowid) );
	var postage = parseFloat( fnc_postage(rowid) );
	var pricing_suggestion_20perc = (total_product_cost + postage + 0.3) * <?= $pricing_suggestion_val ?>; // ***** Value depends on the platform *****

	// console.log(pricing_suggestion_20perc);

	return pricing_suggestion_20perc;
}

function fnc_total_weight(rowid){
	var lowest_variation_weight = $('tr[data-id_lkup="'+rowid+'"] .lowest_variation_weight').val();
	var variation = $('tr[data-id_lkup="'+rowid+'"] .variation').val();
	var packaging_band = $('tr[data-id_lkup="'+rowid+'"] .packaging_band').val();
	var weight = lookup_postage_bands[packaging_band]['max_weight'];
	var total_weight = lowest_variation_weight * variation + weight;

	return total_weight;
}

function fnc_total_product_cost(rowid){
	var cost_per_unit = $('tr[data-id_lkup="'+rowid+'"] .cost_per_unit').val();
	var variation = $('tr[data-id_lkup="'+rowid+'"] .variation').val();
	var total_product_cost = cost_per_unit * variation;

	return total_product_cost;
}

function fnc_postage(rowid){
	var lowest_variation_weight = parseFloat( $('tr[data-id_lkup="'+rowid+'"] .lowest_variation_weight').val() );
	var variation = parseFloat( $('tr[data-id_lkup="'+rowid+'"] .variation').val() );

	var packaging_band = $('tr[data-id_lkup="'+rowid+'"] .packaging_band').val();
	var cost_pb = parseFloat( lookup_postage_bands[packaging_band]['cost'] );

	var courier = $('tr[data-id_lkup="'+rowid+'"] .courier').val();

	courier = courier.replace("£10", "&pound;10");
	
	if ('20-30kg DX Over 8ft' != courier) {
		var cost_c = parseFloat( lookup_couriers[courier]['cost'] );
		var postage = (cost_c + cost_pb) * Math.ceil(lowest_variation_weight * variation / 29.5);
	}
	else {
		var total_weight = $('tr[data-id_lkup="'+rowid+'"] .total_weight').html();
		var postage = (13.59+( (total_weight - 20) *0.3 )) *1.095 *1.1;
	}
	
	// console.log(['Postage: ' + postage, 'total_weight: ' + total_weight]);

	// console.table({
	// 	"lowest_variation_weight": lowest_variation_weight,
	// 	"variation": variation,
	// 	"packaging_band": packaging_band,
	// 	"cost_pb": cost_pb,
	// 	"courier": courier,
	// 	"cost_c": cost_c
	// })
	
	// If courier name contains 'pallet' or 'drop' (case insensitive check)
	if( courier.toLowerCase().includes('pallet') || courier.toLowerCase().includes('drop') ){
		postage = parseFloat( lookup_couriers[courier]['cost'] );
	}

	return postage;
}
</script>
