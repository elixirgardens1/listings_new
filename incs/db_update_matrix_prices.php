<?php
//=========================================================================
// ISSUES:
// When prices have been updated via the 'view price matrix' it needs
// to load the Listings view.
//=========================================================================
if( isset($_POST['update_matrix_prices']) ){
	$platform_name = $lookup_platform[ $_POST['platform'] ];
	
	$timestamp = time();

	$new_prices = [];
	$orig_prices = [];
	foreach( $_POST as $key => $val ){
		if( 'orig_prices' == $key ){
			$tmp = explode(',', $val);
			
			foreach( $tmp as $rec ){
				list($id,$orig_price) = explode(':', $rec);
				if( $orig_price != $_POST[$id] ){
					$new_prices[$id] = $_POST[$id];
					$orig_prices[$id] = $orig_price;
				}
			}
			break;
		}
	}
	
	$db = new PDO("sqlite:$listings_db_path");
	$stmt = $db->prepare("UPDATE `listings_$platform_name` SET `new_price` = ?, `timestamp` = ? WHERE `id` = ?");

	$db->beginTransaction();
	foreach ($new_prices as $id => $price) {
		//======================================
		// id    |courier |prev_price |new_price
		//  9165 |      9 |     10.49 |12.0
		// 13012 |      7 |           |6.0
		//======================================
		$stmt->execute([ $price, $timestamp, $id ]);
	}
	$db->commit();
	
	//=========================================================================
	// RECORD CHANGES TO 'price(s)'
	//=========================================================================
	$prices_arr = [];
	foreach ($new_prices as $id => $price) {
		$prices_arr[] = $orig_prices[$id] .'>'. $new_prices[$id];
	}
	$ids_str = implode(',', array_keys($new_prices) );
	$prices_str = implode(',', $prices_arr);

	//==================================
	// id         |changes
	// 13012,9165 |{e}5.49>6,11.99>12
	//==================================
	record_changes_fnc([
		'db'  => $db_listings,
		'rec' => [
			$ids_str,
			"{{$_POST['platform']}}$prices_str",
			$_POST['user'],
			$timestamp
		],
	]);

	// Set POSTs to return to correct view
	unset($_POST['update_matrix_prices']);
	$_POST['view'] = 'Listings';
}
