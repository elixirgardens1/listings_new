<?php
//=========================================================================
// TO-DO
//=========================================================================
// * Check that this jumps to the correct view after inserting records
// * Probably need to update session data: $session['listings'], $session['variations_new'], $session['group_edit']

// Search for the following in 'listings.php': elseif( isset($_POST['add_new_listings']) )
if( isset($_POST['add_new_listings_to_db']) &&
	isset($_POST['cat_id']) &&
	'' != $_POST['product_name_listings'] &&
	'' != $_POST['variation'] &&
	'' != $_POST['lvw'] &&
	'' != $_POST['key']
){
	$files_used[] = 'incs/db_add_new_listings.php'; //DEBUG

	$db_modify = TRUE; // Comment out to stop database being updated

	//DEBUG
	// echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">'; print_r($_POST); echo '</pre>'; die();
	
	$timestamp = time();

	$_POST = sanitize_post_data_fnc($_POST, [
		'my_trim',
		'remove_multiple_whitespace',
		'my_htmlspecialchars',
		'ascii_translit'
	]);

	$product   = $_POST['product_name_listings'];
	$variation = $_POST['variation'];
	$lvw       = $_POST['lvw'];
	$cat       = $_POST['cat'];
	$cat_id    = $_POST['cat_id'];
	$key       = $_POST['key'];
	$packing   = 'n' == $_POST['packing'] ? '' : $_POST['packing'];

	$sql = "SELECT `key`,`product_cost` FROM `products`";
	$results = $db_stock->query($sql);
	$keys_costs = $results->fetchAll(PDO::FETCH_KEY_PAIR);

	$sql = "SELECT `cat_id`,`group_` FROM `listings`";
	$results = $db_listings->query($sql);
	$results = $results->fetchAll(PDO::FETCH_KEY_PAIR);

	$group = isset($results[$cat_id]) ? ++$results[$cat_id] : 'a';

	$cost_per_unit_ = $keys_costs[$key];

	// Required so that a new listing courier displays '- PLEASE SELECT -'
	// This is the last record in the 'lookup_courier' table - currently row 26.
	// If new records get added it will no longer be row 26, so its index needs
	// to be calculated by counting the rows.
	$please_select_courier_index = count($lookup_couriers_name_id);

	// Retrieve multi_cpu data if listing comprises of 2 or more products
	if( count($_POST['key_name']) > 1 ){
		$sql = "SELECT `key` FROM `multi_cpu`";
		$results = $db_listings->query($sql);
		$group_keys = $results->fetchAll(PDO::FETCH_COLUMN);

		$new_group_key = count($group_keys);
		$new_group_key = "g$new_group_key";
		$key = $new_group_key;

		$tmp1 = [];
		$tmp2 = [];
		foreach ($_POST['key_name'] as $i => $key_name) {
			$tmp1[] = $key_name;
			$tmp2[] = $_POST['key_amount'][$i];
		}
		$keys = implode(' ', $tmp1);
		$amounts = implode(' ', $tmp2);

		$insert = ["$new_group_key","$keys", "$amounts", $timestamp];

		$sql = "INSERT INTO `multi_cpu` VALUES (?,?,?,?)";		
		$stmt = $db_listings->prepare($sql);
		$db_listings->beginTransaction();

		if( isset($db_modify) ){
			//========================
			// key  |keys       |percs
			// g502 |fer34 wee0 |1 2
			//========================
			$stmt->execute($insert);
		}
		else{
			$insert_str = implode("','", $insert);
			echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">';
			print_r("INSERT INTO `multi_cpu` VALUES ('$insert_str')");
			echo '</pre>';
		}

		$db_listings->commit();
	}

	$packaging_band_ = 1;
	$lowest_var_wgt_default = $lvw;

	//=========================================================================
	// The 'listings' table no longer uses the _rowid_ to link to the platform table.
	// It now uses 'id_lkup'. The code needs to get the last value from the existing
	// 'listings' table to get calculate the value for the $id_lkup valiable below.
	// Simple increment by 1.

	$sql = "SELECT `id_lkup` FROM `listings` ORDER BY `id_lkup` DESC LIMIT 1";
	$results = $db_listings->query($sql);
	$last_id_lkup = $results->fetchAll(PDO::FETCH_COLUMN)[0];

	$last_id = $last_id_lkup+1;

	$insert = [];
	$id_lkups = [];
	foreach( explode(' ', $variation) as $var ){
		$id_lkups[] = $last_id;
		$insert[] = [$last_id++, $key, $cat_id, $group, $product, $packing, $packaging_band_, $lowest_var_wgt_default, $var, NULL, $timestamp];
	}

	$sql = "INSERT INTO `listings` VALUES (?,?,?,?,?,?,?,?,?,?,?)";
	$stmt = $db_listings->prepare($sql);
	$db_listings->beginTransaction();
	foreach( $insert as $rec ){
		if( isset($db_modify) ){
			//=========================================================================================================
			// id_lkup |key  |cat_id |group_ |product_name |packing |packaging_band |lowest_variation_weight |variation
			//   13241 |g502 |a9     |i      |TEST         |b       |             1 |                      3 |        1
			//   13242 |g502 |a9     |i      |TEST         |b       |             1 |                      3 |        2
			//   13243 |g502 |a9     |i      |TEST         |b       |             1 |                      3 |        3
			//   13244 |g502 |a9     |i      |TEST         |b       |             1 |                      3 |        4
			//   13245 |g502 |a9     |i      |TEST         |b       |             1 |                      3 |        5
			//=========================================================================================================
			$stmt->execute($rec);
		}
		else{
			$insert_str = implode("','", $rec);
			echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">';
			print_r("INSERT INTO `listings` VALUES ('$insert_str')");
			echo '</pre>';
		}
	}
	$db_listings->commit();

	//=========================================================================
	// Insert records to PLATFORM tables
	//=========================================================================
	foreach( $lookup_platform as $platform ){
		if( 'prime' == $platform ){ continue; }

		$sql = "INSERT INTO `listings_$platform` VALUES (?,?,?,?,?,?)";
		$stmt_listings_PLATFORM = $db_listings->prepare($sql);

		$sql = "INSERT INTO `listings_couriers` VALUES (?,?,?)";
		$stmt_couriers = $db_listings->prepare($sql);

		$db_listings->beginTransaction();
		foreach( $id_lkups as $id_lkup ){
			// 'id','courier','prev_price','new_price','perc_advertising','notes','timestamp'
			// Nb. $please_select_courier_index should display '- PLEASE SELECT -' because it's
			//     the end row in the 'lookup_courier' table.
			if( isset($db_modify) ){
				//========================================================
				// id    |courier |prev_price |new_price |perc_advertising
				// 13241 |     26 |           |          |               0
				// 13242 |     26 |           |          |               0
				// 13243 |     26 |           |          |               0
				// 13244 |     26 |           |          |               0
				// 13245 |     26 |           |          |               0
				//========================================================
				$stmt_listings_PLATFORM->execute([$id_lkup,'','','0','',$timestamp]);

				if( 'ebay' == $platform ){
					$stmt_couriers->execute([$id_lkup,$please_select_courier_index,$timestamp]);
				}
			}
			else{
				echo '<pre style="background:#111; color:#b5ce28; font-size:11px;">';
				print_r("INSERT INTO `listings_$platform` VALUES ('$id_lkup','','','0','',$timestamp)");
				if( 'ebay' == $platform ){
					print_r("INSERT INTO `listings_couriers` VALUES ('$id_lkup',$please_select_courier_index,$timestamp)");
				}
				echo '</pre>';
			}
		}
		$db_listings->commit();
	}


	//=========================================================================
	// Record changes
	//=========================================================================
	if( isset($db_modify) ){
		$changes_str = "{pn}{$product}{v}{$variation}";
		$id_lkups_str = implode(',', $id_lkups);
		//====================================================
		// id                            |changes
		// 13241,13242,13243,13244,13245 |{pn}TEST{v}1 2 3 4 5
		//====================================================
		record_changes_fnc([
			'db'  => $db_listings,
			'rec' => [$id_lkups_str, $changes_str, $_POST['user'], $timestamp],
		]);

		if( count($_POST['key_name']) > 1 ){
			$changes_str = "{mc}{$new_group_key}|{$keys}|{$amounts}";
			//============================
			// id |changes
			//    |{mc}g504|sal11 sal9|1 2
			//============================
			record_changes_fnc([
				'db'  => $db_listings,
				'rec' => ['', $changes_str, $_POST['user'], $timestamp],
			]);
		}
	}
}
