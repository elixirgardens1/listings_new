<?php
/*
INFO:
Deletes records from `listings_prime` & `prime_couriers` tables.
Saves the deleted data to the `deletes` table.
*/

if( isset($_POST['delete_prime_from_db']) ){
	$id = $_POST['courier'];
	$c = $_POST['courier_lkup'];
	$timestamp = time();

	$sql = "SELECT * FROM `listings_prime` WHERE `id` = '$id'";
	$results = $db_listings->query($sql);
	$results_listings_prime = $results->fetchAll(PDO::FETCH_ASSOC)[0];

	$pp = $results_listings_prime['prev_price'];
	$np = $results_listings_prime['new_price'];
	$pa = $results_listings_prime['perc_advertising'];
	$n  = $results_listings_prime['notes'];

	$sql = "DELETE FROM `listings_prime` WHERE `id` = $id";
	$db_listings->query($sql);
	$sql = "DELETE FROM `prime_couriers` WHERE `id` = $id";
	$db_listings->query($sql);

	//=========================================================================
	// RECORD DELETES
	//=========================================================================
	$delete = "{c}$c";
	record_deletes_fnc([
		'db'  => $db_listings,
		'rec' => [
			$id,
			$delete,
			$_POST['user'],
			$timestamp
		],
	]);

	$delete = "{pp}$pp{np}$np{pa}$pa{n}$n";
	record_deletes_fnc([
		'db'  => $db_listings,
		'rec' => [
			$id,
			$delete,
			$_POST['user'],
			$timestamp
		],
	]);

	// Set variables for correct destination view
	unset($_POST['add_prime_listing_to_db']);
	unset($_POST['submit']);
	$_POST['view'] = 'Listings';
	$_POST['platform'] = 'p';
	$platform_post = 'p';
}
