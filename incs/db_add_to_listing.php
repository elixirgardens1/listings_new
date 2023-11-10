<?php

/*
INFO:
When a new listing gets added to an existing group it also creates a new record in every
listings_{PLATFORM} table, but not the 'listings_prime' table.

This is also the case when a new group is created via the 'Add New Listings' button
on the Dashboard.
*/

if( isset($_POST['add_listing_to_db']) ){

	$files_used[] = 'incs/db_add_to_listing.php'; //DEBUG

	$_POST = sanitize_post_data_fnc($_POST, [
		'my_trim',
		'remove_multiple_whitespace',
		'my_htmlspecialchars',
		'ascii_translit'
	]);

	// $platform = $lookup_platform[ $_POST['platform'] ];

	$timestamp = time();

	// Get highest 'id_lkup' value - the new record's 'id_lkup' value will equal this +1.
	$sql = "SELECT `id_lkup` FROM `listings` ORDER BY `id_lkup` DESC LIMIT 1";
	$results = $db_listings->query($sql);
	$highest_id_lkup_plus1 = $results->fetchAll(PDO::FETCH_COLUMN)[0] +1; // ::FETCH_NUM ::FETCH_COLUMN ::FETCH_KEY_PAIR

	//=========================================================================
	// Insert new record to listings
	//=========================================================================
	$sql = "INSERT INTO `listings` (
		`id_lkup`,
		`key`,
		`cat_id`,
		`group_`,
		`product_name`,
		`packing`,
		`packaging_band`,
		`lowest_variation_weight`,
		`variation`,
		`remove`,
		`timestamp`
	) VALUES (?,?,?,?,?,?,?,?,?,?,?)";

	$sql_vals = [
		$highest_id_lkup_plus1,
		$_POST['key'],
		$_POST['cat_id'],
		$_POST['group_edit'],
		$_POST['product_name'],
		'',
		$_POST['packaging_band'],
		$_POST['lowest_variation_weight'],
		$_POST['variation'],
		NULL,
		$timestamp,
	];

	$stmt_listings = $db_listings->prepare($sql);
	$db_listings->beginTransaction();
	$stmt_listings->execute($sql_vals);
	$db_listings->commit();

	//=========================================================================
	// RECORD CHANGES
	//=========================================================================
	$changes_str = "{pn}{$_POST['product_name']}{pb}{$_POST['packaging_band']}{lv}{$_POST['lowest_variation_weight']}{v}{$_POST['variation']}{c}{$lookup_couriers_name_id[ $_POST['courier'] ]}";
	record_changes_fnc([
		'db'  => $db_listings,
		'rec' => [$highest_id_lkup_plus1, $changes_str, $_POST['user'], $timestamp],
	]);

	$sql = "INSERT INTO `listings_couriers` (
		`id`,
		`courier`,
		`timestamp`
	) VALUES (?,?,?)";
	$stmt_couriers = $db_listings->prepare($sql);

	//=========================================================================
	// Create blank records in listings_{PLATFORMS} tables
	//=========================================================================
	foreach( $lookup_platform as $key => $platform ){
		// Skip prime
		if( 'prime' == $platform ){ continue; }

		$sql = "INSERT INTO `listings_{$platform}` (
			`id`,
			-- `courier`,
			`prev_price`,
			`new_price`,
			`perc_advertising`,
			`notes`,
			`timestamp`
		) VALUES (?,?,?,?,?,?)";
		$stmt_listings_PLATFORM = $db_listings->prepare($sql);


		$db_listings->beginTransaction();

		$sql_vals = [ $highest_id_lkup_plus1,'','','0','',$timestamp ];
		$stmt_listings_PLATFORM->execute($sql_vals);

		$sql_vals = [ $highest_id_lkup_plus1,$lookup_couriers_name_id[ $_POST['courier'] ],$timestamp ];

		if( 'ebay' == $platform ){
			$stmt_couriers->execute($sql_vals);
		}

		$db_listings->commit();
	}

	// Update post variables to end up back on Listing Edit view
	unset($_POST['add_listing_to_db']);
	$_POST['view'] = 'Edit';
}
