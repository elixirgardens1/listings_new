<?php
/*
INFO:
Adds new records to `listings_prime` table.

+------+---------+------------+-----------+------------------+-------+------------+
|  id  | courier | prev_price | new_price | perc_advertising | notes | timestamp  |
+------+---------+------------+-----------+------------------+-------+------------+
| 9195 |      21 |            |     0.0   |                0 |       | 1610725699 |
+------+---------+------------+-----------+------------------+-------+------------+

If the record already exists it just updates the courier number and timestamp.
If the record already exists, but POST has empty courier value, it deletes the record.

*/

$existing_ids_couriers = [];
if( isset($_POST['add_prime_listing_to_db']) ){
	$files_used[] = 'incs/db_add_prime_to_listings.php'; //DEBUG
	
	if( '' != $_POST['existing'] ){
		foreach( explode(',',  $_POST['existing']) as $key_val ){
			list($id, $courier_no) = explode('=>', $key_val); // Warning: Undefined array key 1
			$existing_ids_couriers[$id] = $courier_no;
		}
	}
	
	$timestamp = time();
	
	$sql = "INSERT INTO `listings_prime` (
		`id`,
		-- `courier`,
		`prev_price`,
		`new_price`,
		`perc_advertising`,
		`notes`,
		`timestamp`
	) VALUES (?,?,?,?,?,?)";
	$stmt_insert = $db_listings->prepare($sql);
	
	$sql = "INSERT INTO `prime_couriers` (
		`id`,
		`courier`,
		`timestamp`
	) VALUES (?,?,?)";
	$stmt_insert_prime_couriers = $db_listings->prepare($sql);
	
	$sql = "UPDATE `prime_couriers`
		SET `courier` = ?,
		`timestamp` = ?
		WHERE `id` = ?";
	$stmt_update = $db_listings->prepare($sql);

	$changes_inserts = [];
	$changes_updates = [];
	$db_listings->beginTransaction();
	foreach( $_POST['courier'] as $id => $val ){
		// Add new records
		if( '' != $val && !isset($existing_ids_couriers[$id]) ){
			$stmt_insert->execute([ $id, '','','0','', $timestamp ]);
			$stmt_insert_prime_couriers->execute([ $id, $val, $timestamp ]);
			
			//============================
			// Record Changes
			//============================
			// id   |changes                                                  
			// 9159 |{c}17
			//============================
			record_changes_fnc([
				'db'  => $db_listings,
				'rec' => [$id, "{c}$val", $_POST['user'], $timestamp],
				'no_trans' => 1,
			]);
		}
		// Update existing records
		elseif( '' != $val && $existing_ids_couriers[$id] != $val ){
			$stmt_update->execute([ $val, $timestamp, $id ]);
			
			//============================
			// Record Changes
			//============================
			// id   |changes                                                  
			// 9159 |{c}17>19
			//============================
			record_changes_fnc([
				'db'  => $db_listings,
				'rec' => [$id, "{c}{$existing_ids_couriers[$id]}>$val", $_POST['user'], $timestamp],
				'no_trans' => 1,
			]);
		}
		// Delete existing records
		// elseif( isset($_POST['delete_prime_courier']) ){
		// 	$sql = "DELETE FROM `listings_prime` WHERE `id` = $id";
		// 	$db_listings->query($sql);
		// 	$sql = "DELETE FROM `prime_couriers` WHERE `id` = $id";
		// 	$db_listings->query($sql);
			
		// 	// Record Deletes
		// 	record_deletes_fnc([
		// 		'db'  => $db_listings,
		// 		'rec' => [$id, "STR_HERE", $_POST['user'], $timestamp],
		// 	]);
		// }
	}
	$db_listings->commit();

	//=========================================================================
	// RECORD CHANGES
	//=========================================================================


	// Update post variables to end up back on Listing Edit view
	unset($_POST['add_prime_listing_to_db']);
	unset($_POST['submit']);
	$_POST['view'] = 'Edit';
	$_POST['platform'] = 'p';
	
	// $_POST = [
	// 	'posY' => '0',
	// 	'group_edit' => 'a',
	// 	'view' => 'Edit',
	// 	'cat' => 'agg',
	// 	'cat_id' => 'a8',
	// 	'key' => 'agg0',
	// 	'platform' => 'p',
	// 	'user' => '1',
	// 	'cpu' => '0.0825',
	// 	'lvw' => '1',
	// ];
	
	$platform_post = 'p';
}
