<?php
if( isset($_POST['save_listing_to_db']) ){

	//DEBUG
	// echo '<pre style="background:#111; color:#b5ce28; margin-bottom:-10px;">'; print_r($_POST); echo '</pre>'; die();

	$files_used[] = 'incs/db_save_listing.php'; //DEBUG

	$_POST = sanitize_post_data_fnc($_POST, [
		'my_trim',
		'remove_multiple_whitespace',
		'my_htmlspecialchars',
		'ascii_translit'
	]);

	//DEBUG
	// echo '<pre style="background:#002; color:#fff;">'; print_r($_POST['listings_data']); echo '</pre>'; die();

	// The Listings Edit page can update up to 3 tables, depending on what values have been modified.
	// In the case of the 'comps_ids' table, it doesn't necessarily update existing records.
	// Records can also be added or deleted (see relevant section in script below).
	// Editable tables/fields:
	// ┌─────────────────────────┬─────────────────────┬───────────┐
	// │        listings         │ listings_{PLATFORM} │ comps_ids │
	// ├─────────────────────────┼─────────────────────┼───────────┤
	// │ product_name            │ courier             │ comp1     │
	// │ packaging_band          │ prev_price          │ comp2     │
	// │ lowest_variation_weight │ new_price           │ comp3     │
	// │ variation               │ perc_advertising    │ id1       │
	// │ timestamp               │ timestamp           │ id2       │
	// │                         │                     │ id3       │
	// │                         │                     │ type1     │
	// │                         │                     │ type2     │
	// │                         │                     │ type3     │
	// │                         │                     │ timestamp │
	// └─────────────────────────┴─────────────────────┴───────────┘
	// * The listings_{PLATFORM} notes field cannot be added/edited via the Listings interface.
	//   They get added to the database table manually.

	// CPU (Cost per Unit) exists in the StockControl database?

	$timestamp = time();

	//=========================================================================
	// Retrieve table data to compare which posted values are different,
	// and require updating.
	//=========================================================================
	$id_lkup_arr = array_keys($_POST['listings_data']);
	$id_lkup_in = implode("','", $id_lkup_arr);

	$sql = "SELECT * FROM `listings` WHERE `id_lkup` IN ('$id_lkup_in')";
	$results = $db_listings->query($sql);
	$results_listings = $results->fetchAll(PDO::FETCH_ASSOC);
	$results_listings = assoc_multi_array_fnc($results_listings,'id_lkup');

	$post_platform = $_POST['platform'];

	$sql = "SELECT * FROM `listings_{$lookup_platform[$post_platform]}` WHERE `id` IN ('$id_lkup_in')";
	$results = $db_listings->query($sql);
	$results_listings_platform = $results->fetchAll(PDO::FETCH_ASSOC);
	$results_listings_platform = assoc_multi_array_fnc($results_listings_platform,'id');
	

	$tbl = 'p' != $post_platform ? 'listings_couriers' : 'prime_couriers';
	$sql = "SELECT id,courier FROM `$tbl` WHERE `id` IN ('$id_lkup_in')";
	$results = $db_listings->query($sql);
	$listings_couriers = $results->fetchAll(PDO::FETCH_KEY_PAIR);

	$sql = "SELECT * FROM `comps_ids` WHERE `id` IN ('$id_lkup_in') AND `source` = '{$post_platform}'";
	$results = $db_listings->query($sql);
	$results_comps_ids = $results->fetchAll(PDO::FETCH_ASSOC);
	$results_comps_ids = assoc_multi_array_fnc($results_comps_ids,'id');
	// $results_comps_ids = assoc_multi_array_fnc($results_comps_ids,'id','source'); // passing 2 key fields results in [9160_e]

	$lookup_courier_names_flip = array_flip($session['lookup_courier_names']);

	$sql_update_listings = [];
	$sql_update_listings_platform = [];
	$sql_update_listings_comps_ids = [];
	$sql_delete_listings_comps_ids = [];
	$sql_insert_listings_comps_ids = [];
	foreach( $_POST['listings_data'] as $id => $rec ){
		// listings
		$product_name            = $rec['product_name'];
		$packaging_band          = $rec['packaging_band'];
		$lowest_variation_weight = $rec['lowest_variation_weight'];
		$variation               = $rec['variation'];

		// listings_{PLATFORM}
		$courier           = $rec['courier'];
		$prev_price        = $rec['prev_price'];
		$new_price         = $rec['new_price'];
		$perc_advertising  = $rec['perc_advertising'];

		if( 'w' != $post_platform ){
			// comps_ids
			$comp1 = $rec['comp1'];
			$comp2 = $rec['comp2'];
			$comp3 = $rec['comp3'];
			$id1   = $rec['id1'];
			$id2   = $rec['id2'];
			$id3   = $rec['id3'];
			$type1 = $rec['type1'];
			$type2 = $rec['type2'];
			$type3 = $rec['type3'];
		}


		//=========================================================================
		// Only update 'listings' table if 1 or more fields have changed
		//=========================================================================
		if( // if 'product_name' form field is different to database 'product_name'
			(string)$product_name            !== (string)$results_listings[$id]['product_name'] ||
			(string)$packaging_band          !== (string)$results_listings[$id]['packaging_band'] ||
			(string)$lowest_variation_weight !== (string)$results_listings[$id]['lowest_variation_weight'] ||
			(string)$variation               !== (string)$results_listings[$id]['variation']
		){
			// DEBUG: Only for display purposes
			$sql_update_listings[] = "
				UPDATE `listings` SET
				`product_name`            = '$product_name',
				`packaging_band`          = '$packaging_band',
				`lowest_variation_weight` = '$lowest_variation_weight',
				`variation`               = '$variation',
				`timestamp`               = '$timestamp'
				WHERE `id_lkup`           = '$id'";

			$stmt = $db_listings->prepare("UPDATE `listings` SET
				`product_name` = ?,
				`packaging_band` = ?,
				`lowest_variation_weight` = ?,
				`variation` = ?,
				`timestamp` = ?
				WHERE `id_lkup` = ?");
			
			//=========================================================================================================================
			// id_lkup |key   |cat_id |group_ |product_name                |packing |packaging_band |lowest_variation_weight |variation
			//    9159 |agg11 |a244   |a      |Brown Rock Salt x 5kg bag T |b       |             5 |                      1 |        5
			//=========================================================================================================================
			$db_listings->beginTransaction();
			// $stmt->execute([
			// 	$product_name,
			// 	$packaging_band,
			// 	$lowest_variation_weight,
			// 	$variation,
			// 	$timestamp,
			// 	$id
			// ]);
			$db_listings->commit();

			//=========================================================================
			// RECORD CHANGES TO 'listings' TABLE
			//=========================================================================
			
			// The check_changes_fnc() compares array elements 2 & 3 and if different, adds
			// them to the changes array.
			// The first array element is shorthand for the field value name: eg. pn: product name
			$changes_str = check_changes_fnc([
				['{pn}', (string)$results_listings[$id]['product_name'], (string)$product_name],
				['{pb}', (string)$results_listings[$id]['packaging_band'], (string)$packaging_band],
				['{lv}', (string)$results_listings[$id]['lowest_variation_weight'], (string)$lowest_variation_weight],
				['{v}', (string)$results_listings[$id]['variation'], (string)$variation],
			]);
			
			//================================================================
			// id   |changes                                                  
			// 9159 |{pn}Brown Rock Salt x 5kg bag>Brown Rock Salt x 5kg bag T
			//================================================================
			if( $changes_str ){
				record_changes_fnc([
					'db'  => $db_listings,
					'rec' => [$id, $changes_str, $_POST['user'], $timestamp],
				]);
			}
		}

		// Only update 'listings_{PLATFORM}' table if 1 or more fields have changed
		if( (string)$lookup_courier_names_flip[$courier] !== (string)$listings_couriers[$id] ||
			(string)$prev_price                          !== (string)$results_listings_platform[$id]['prev_price'] ||
			(string)$new_price                           !== (string)$results_listings_platform[$id]['new_price'] ||
			(string)$perc_advertising                    !== (string)$results_listings_platform[$id]['perc_advertising']
		){
			// DEBUG: Only for display purposes
			$sql_update_listings_platform[] = "
				UPDATE `listings_{$lookup_platform[$post_platform]}` SET
				-- `courier`           = '{$lookup_courier_names_flip[$courier]}',
				`prev_price`        = '$prev_price',
				`new_price`         = '$new_price',
				`perc_advertising`  = '$perc_advertising',
				`timestamp`         = '$timestamp',
				WHERE `id`          = '$id'";

			$stmt = $db_listings->prepare("UPDATE `listings_{$lookup_platform[$post_platform]}` SET
				-- `courier` = ?,
				`prev_price` = ?,
				`new_price` = ?,
				`perc_advertising` = ?,
				`timestamp` = ?
				WHERE `id` = ?");
			
			$stmt_courier = $db_listings->prepare("UPDATE `$tbl` SET `courier` = ?, `timestamp` = ? WHERE `id` = ?");
			
			// If a listing shipped in a bag has an equivalent tub listing,
			// the price gets update automatically.
			// The 'key', 'cat_id' & 'variation' are retrieved for the original bag listing,
			// then used to find the 'id' of the equivalent tub listing (assuming their 'remove' values are both NULL).
			// The price and variation (weight) of the bag listing are passed to the calc_tub_price_fnc (see 'update tub listing price' below).
			// This adds a set amount on to the original price.
			if( 'b' == $rec['packing'] ){
				$sql = "SELECT `key`,`cat_id`,`variation` FROM `listings` WHERE `id_lkup` = '$id' AND `remove` IS NULL";
				$results = $db_listings->query($sql);
				$key_cat_var_id = $results->fetchAll(PDO::FETCH_ASSOC)[0];
				
				$sql = "SELECT `id_lkup` FROM `listings` WHERE
						`key` = '$key_cat_var_id[key]' AND
						`cat_id` = '$key_cat_var_id[cat_id]' AND
						`variation` = '$key_cat_var_id[variation]' AND
						`packing` = 't' AND
						`remove` IS NULL";
				$results = $db_listings->query($sql);
				
				if( $tub_id = $results->fetchAll(PDO::FETCH_COLUMN) ){
					$tub_id = $tub_id[0];
					
					$stmt_tub_price = $db_listings->prepare("UPDATE `listings_{$lookup_platform[$post_platform]}` SET
						`new_price` = ?,
						`timestamp` = ?
						WHERE `id` = ?");
				}
				// b: 3247 | t: 3257
				// Citric Acid in bag x 1kg : £10 | Citric Acid in tub x 1kg : £11
			}
			
			//=======================================================
			// id   |courier |prev_price |new_price |perc_advertising
			// 9159 |      9 |      7.99 |     8.99 |               1
			//=======================================================
			$db_listings->beginTransaction();
			
			$stmt->execute([
				$prev_price,
				$new_price,
				$perc_advertising,
				$timestamp,
				$id
			]);
			
			// update tub listing price.
			if( isset($stmt_tub_price) ){
				$stmt_tub_price->execute([
					calc_tub_price_fnc($key_cat_var_id['variation'], $new_price),
					$timestamp,
					$tub_id
				]);
			}
			
			$stmt_courier->execute([
				$lookup_courier_names_flip[$courier],
				$timestamp,
				$id
			]);
			
			$db_listings->commit();
			
			
			
			
			

			//=========================================================================
			// RECORD CHANGES TO 'listings_{PLATFORM}' TABLE
			//=========================================================================
			$changes_str = check_changes_fnc([
				['{c}', (string)$listings_couriers[$id], (string)$lookup_courier_names_flip[$courier] ],
				['{pp}', (string)$results_listings_platform[$id]['prev_price'], (string)$prev_price],
				['{np}', (string)$results_listings_platform[$id]['new_price'], (string)$new_price],
				['{pa}', (string)$results_listings_platform[$id]['perc_advertising'], (string)$perc_advertising],
			]);
			
			//============================
			// id   |changes                                                  
			// 9159 |e{np}7.99>8.99{pa}0>1
			//============================
			if( $changes_str ){
				record_changes_fnc([
					'db'  => $db_listings,
					'rec' => [$id, $post_platform.$changes_str, $_POST['user'], $timestamp],
				]);
			}
		}





		//=========================================================================
		// 'comps_ids' are different to the previous operations because if they don't
		// exist they need to be inserted.
		// If they do exist, but all submitted fields are empty, then record needs deleting.
		// Check to see if a record exists in the 'comps_ids' table.
		//=========================================================================
		if( 'w' != $post_platform ){
			if( isset($results_comps_ids[$id]) ){
				// comps_ids record exists so needs updating

				// Only update 'comps_ids' table if 1 or more fields have changed
				if(	(string)$comp1 !== (string)$results_comps_ids[$id]['comp1'] ||
					(string)$comp2 !== (string)$results_comps_ids[$id]['comp2'] ||
					(string)$comp3 !== (string)$results_comps_ids[$id]['comp3'] ||
					(string)$id1   !== (string)$results_comps_ids[$id]['id1'] ||
					(string)$id2   !== (string)$results_comps_ids[$id]['id2'] ||
					(string)$id3   !== (string)$results_comps_ids[$id]['id3'] ||
					(string)$type1 !== (string)$results_comps_ids[$id]['type1'] ||
					(string)$type2 !== (string)$results_comps_ids[$id]['type2'] ||
					(string)$type3 !== (string)$results_comps_ids[$id]['type3']
				){
					// Delete existing record if all submitted comp and id fields are empty
					if(
						'' == $comp1 && '' == $comp2 && '' == $comp3 &&
						'' == $id1 && '' == $id2 && '' == $id3
					){
						// DEBUG: Only for display purposes
						$sql_delete_listings_comps_ids[] = "DELETE FROM `comps_ids` WHERE `id` = '$id'";
						$db_listings->query("DELETE FROM `comps_ids` WHERE `id` = '$id'");
					}
					// Update record
					else{
						// DEBUG: Only for display purposes
						$sql_update_listings_comps_ids[] = "
							UPDATE `comps_ids` SET
							`comp1`     = '$comp1',
							`comp2`     = '$comp2',
							`comp3`     = '$comp3',
							`id1`       = '$id1',
							`id2`       = '$id2',
							`id3`       = '$id3',
							`type1`     = '$type1',
							`type2`     = '$type2',
							`type3`     = '$type3',
							`timestamp` = '$timestamp',
							WHERE `id` = '$id' AND `source` = '$post_platform'";

						$stmt = $db_listings->prepare("UPDATE `comps_ids` SET
							`comp1` = ?,
							`comp2` = ?,
							`comp3` = ?,
							`id1` = ?,
							`id2` = ?,
							`id3` = ?,
							`type1` = ?,
							`type2` = ?,
							`type3` = ?,
							`timestamp` = ?
							WHERE `id` = ? AND `source` = ?");

						//========================================================================================
						// id   |comp1 |comp2 |comp3 |id1          |id2          |id3 |type1 |type2 |type3 |source
						// 9159 | 9.99 | 9.99 |      |185102128915 |288923823842 |    |    1 |    2 |      |e
						//========================================================================================
						$db_listings->beginTransaction();
						$stmt->execute([
							$comp1,
							$comp2,
							$comp3,
							$id1,
							$id2,
							$id3,
							$type1,
							$type2,
							$type3,
							$timestamp,
							$id,
							$post_platform
						]);
						$db_listings->commit();
						
						
						//=========================================================================
						// RECORD CHANGES TO 'listings_{PLATFORM}' TABLE
						//=========================================================================
						$changes_str = check_changes_fnc([
							['{c1}', (string)$results_comps_ids[$id]['comp1'], (string)$comp1],
							['{c2}', (string)$results_comps_ids[$id]['comp2'], (string)$comp2],
							['{c3}', (string)$results_comps_ids[$id]['comp3'], (string)$comp3],
							['{id1}', (string)$results_comps_ids[$id]['id1'], (string)$id1],
							['{id2}', (string)$results_comps_ids[$id]['id2'], (string)$id2],
							['{id3}', (string)$results_comps_ids[$id]['id3'], (string)$id3],
							['{ty1}', (string)$results_comps_ids[$id]['type1'], (string)$type1],
							['{ty2}', (string)$results_comps_ids[$id]['type2'], (string)$type2],
							['{ty3}', (string)$results_comps_ids[$id]['type3'], (string)$type3],
						]);
						
						if( $changes_str ){
							record_changes_fnc([
								'db'  => $db_listings,
								'rec' => [$id, $post_platform.$changes_str, $_POST['user'], $timestamp],
							]);
						}
					}
				}
			}
			// comps_ids record doesn't exist so needs creating
			else{
				// Only insert a record if 'comp1' & 'id1' & 'type1' not empty
				// or 'comp2' & 'id2' & 'type2' not empty etc.
				if(
					('' != $comp1 && '' != $id1 && '' != $type1) ||
					('' != $comp2 && '' != $id2 && '' != $type2) ||
					('' != $comp3 && '' != $id3 && '' != $type3)
				){
					// DEBUG: Only for display purposes
					$sql_insert_listings_comps_ids[] = "
						INSERT INTO `comps_ids` (`id`,`comp1`,`comp2`,`comp3`,`id1`,`id2`,`id3`,`type1`,`type2`,`type3`,`source`,`timestamp`)
						VALUES ('$id','$comp1','$comp2','$comp3','$id1','$id2','$id3','$type1','$type2','$type3','$post_platform','$timestamp')";

					$stmt = $db_listings->prepare("INSERT INTO `comps_ids` (`id`,`comp1`,`comp2`,`comp3`,`id1`,`id2`,`id3`,`type1`,`type2`,`type3`,`source`,`timestamp`)
						VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

					$db_listings->beginTransaction();
					$stmt->execute([ $id, $comp1, $comp2, $comp3, $id1, $id2, $id3, $type1, $type2, $type3, $post_platform, $timestamp ]);
					$db_listings->commit();

					//=========================================================================
					// RECORD CHANGES TO 'listings_{PLATFORM}' TABLE
					//=========================================================================
					// By addding NULL, 'check_changes_fnc'() does not add '>'.
					// This shows that it's a newly created record, not an existing
					// one that has changed.
					$changes_str = check_changes_fnc([
						['{c1}', NULL, (string)$comp1],
						['{c2}', NULL, (string)$comp2],
						['{c3}', NULL, (string)$comp3],
						['{id1}', NULL, (string)$id1],
						['{id2}', NULL, (string)$id2],
						['{id3}', NULL, (string)$id3],
						['{ty1}', NULL, (string)$type1],
						['{ty2}', NULL, (string)$type2],
						['{ty3}', NULL, (string)$type3],
					]);

					//==========================================
					// id   |changes                                                  
					// 9159 |e{c2}>9.99{id2}>288923823842{ty2}>1
					//==========================================
					if( $changes_str ){
						record_changes_fnc([
							'db'  => $db_listings,
							'rec' => [$id, $post_platform.$changes_str, $_POST['user'], $timestamp],
						]);
					}
				}
			}
		}
	}

	// Set correct variables to jump to the Listings view.
	//=========================================================================
	unset($_POST['save_listing_to_db']);
	$_POST['view'] = 'Listings';
}