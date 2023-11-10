<?php
if( isset($_POST['copy_couriers']) ){
	$files_used[] = 'incs/db_copy_couriers.php'; //DEBUG

	// Script uses the $lookup_platform array, located in 'incs/lookups.php'

	/*
	listings_amazon
	id    |courier
	10828 |     22 (11)
	10829 |     22 (11)
	12243 |      7 (8)
	12327 |      9 (8)
	*/


	$couriers = [];
	$stmt = [];
	foreach( $lookup_platform as $platform ){
		$sql = "SELECT id,courier FROM `listings_$platform`";
		$results = $db_listings->query($sql);
		$couriers[$platform] = $results->fetchAll(PDO::FETCH_KEY_PAIR);

		if( 'ebay' != $platform && 'prime' != $platform ){
			$stmt[$platform] = $db_listings->prepare("UPDATE `listings_$platform` SET `courier` = ? WHERE `id` = ?");
		}
	}

	$sql = [];
	$db_listings->beginTransaction();
	foreach( $couriers['ebay'] as $id => $courier ){
		foreach( $lookup_platform as $platform ){
			if( 'ebay' == $platform || 'prime' == $platform ){ continue; }
			
			if( isset($couriers[$platform][$id]) && $courier != $couriers[$platform][$id] ){
				// $sql[] = "UPDATE `listings_$platform` SET `courier` = '$courier' WHERE `id` = '$id'";
				$stmt[$platform]->execute([ $courier, $id ]);
			}
		}
	}
	$db_listings->commit();

	$_POST['view'] = 'Dashboard';
}
